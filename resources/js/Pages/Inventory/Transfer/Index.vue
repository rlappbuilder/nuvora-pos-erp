<script setup>
import {ref,reactive,computed,watch,onMounted,onUnmounted,toRefs,nextTick,} from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/Layout/PageHeader.vue'
import Card from '@/Components/Layout/Card.vue'
import StatsCard from '@/Components/Card/StatsCard.vue'
import BaseButton from '@/Components/Button/BaseButton.vue'
import ActionDropdown from '@/Components/Action/ActionDropdown.vue'
import BulkActionDropdown from '@/Components/Bulk/BulkActionDropdown.vue'
import DataTable from '@/Components/Table/DataTable.vue'
import DataTableHead from '@/Components/Table/DataTableHead.vue'
import DataTableBody from '@/Components/Table/DataTableBody.vue'
import DataTableHeaderCell from '@/Components/Table/DataTableHeaderCell.vue'
import DataTableRow from '@/Components/Table/DataTableRow.vue'
import DataTableCell from '@/Components/Table/DataTableCell.vue'
import TablePagination from '@/Components/Table/TablePagination.vue'
import TableEmpty from '@/Components/Table/TableEmpty.vue'
import StatusBadge from '@/Components/Display/StatusBadge.vue'
import SearchableSelect from '@/Components/Form/SearchableSelect.vue'
import { LoadingOverlay,} from '@/Components/Feedback'
import {PlusIcon,} from '@heroicons/vue/24/solid'
import {success,error,currency,formatDate,} from '@/Utils'
import ConfirmDeleteModal from '@/Components/Modal/ConfirmDeleteModal.vue'
import { formatCurrency } from '@/Utils/currency'
import StockTransferForm  from './Partials/StockTransferForm.vue'
import StockTransferPostModal from './Partials/StockTransferPostModal.vue'
import StockTransferRejectModal from './Partials/StockTransferRejectModal.vue'
/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    transfers: {
    type: Object,
    default: () => ({
        data: [],
        current_page: 1,
        last_page: 1,
        per_page: 10,
        total: 0,
        }),
    },

    statistics: {
        type: Object,
        default: () => ({
            total: 0,
            draft: 0,
            rejected: 0,
            posted: 0,
        }),
    },

    companyId: {
    type: [Number, String],
    default: null,
    },

    branches: {
        type: Array,
        default: () => [],
    },

    warehouses: {
        type: Array,
        default: () => [],
    },

    variants: {
        type: Array,
        default: () => [],
    },

    units: {
        type: Array,
        default: () => [],
    },

    filters: {
        type: Object,
        default: () => ({}),
    },

    previewNumber: {
        type: String,
        default: '',
    },

})



const {
    transfers,
    statistics,
    branches,
    warehouses,
    variants,
    units,
    previewNumber,
} = toRefs(props)

/*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/

const pageTitle = computed(() => 'Transfer Stock')


/*
|--------------------------------------------------------------------------
| Loading
|--------------------------------------------------------------------------
*/

const loading = ref(false)

let removeStartListener
let removeFinishListener

const startLoading = () => {
    loading.value = true
}

const stopLoading = () => {
    loading.value = false
}


/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

const filters = reactive({

    search:
        props.filters?.search ?? '',

    from_branch_id:
        props.filters?.from_branch_id ?? '',

    from_warehouse_id:
        props.filters?.from_warehouse_id ?? '',

    to_branch_id:
        props.filters?.to_branch_id ?? '',

    to_warehouse_id:
        props.filters?.to_warehouse_id ?? '',

    status:
        props.filters?.status ?? '',

    date_from:
        props.filters?.date_from ?? '',

    date_to:
        props.filters?.date_to ?? '',

    per_page:
        props.filters?.per_page ?? 10,

})

let debounceTimer = null


function loadData()
{
    router.get(
        route('stock-transfers.index'),
        filters,
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    )
}


watch(
    () => filters.search,
    () => {

        clearTimeout(debounceTimer)

        debounceTimer = setTimeout(() => {

            loadData()

        }, 500)

    }
)


watch(
    () => filters.from_branch_id,
    () => {

        loadData()

    }
)

watch(
    () => filters.from_warehouse_id,
    () => {

        loadData()

    }
)

watch(
    () => filters.to_branch_id,
    () => {

        loadData()

    }
)

watch(
    () => filters.to_warehouse_id,
    () => {

        loadData()

    }
)

watch(
    () => filters.status,
    () => {

        loadData()

    }
)

watch(
    () => filters.date_from,
    () => {

        loadData()

    }
)

watch(
    () => filters.date_to,
    () => {

        loadData()

    }
)

watch(
    () => filters.per_page,
    () => {

        loadData()

    }
)


function refresh()
{
    Object.assign(filters, {

        search: '',

        from_branch_id: '',
        from_warehouse_id: '',

        to_branch_id: '',
        to_warehouse_id: '',

        status: '',

        date_from: '',
        date_to: '',

        per_page: 10,

    })

    loadData()
}


/*
|--------------------------------------------------------------------------
| Status
|--------------------------------------------------------------------------
*/

const statusOptions = [

    {
        value: '',
        label: 'All Status',
    },

    {
        value: 'Draft',
        label: 'Draft',
    },

    {
        value: 'Rejected',
        label: 'Rejected',
    },

    {
        value: 'Posted',
        label: 'Posted',
    },

]
/*
|--------------------------------------------------------------------------
| Selection
|--------------------------------------------------------------------------
*/

const selectedRows = ref([])

const selectAllRef = ref(null)


const isAllSelected = computed(() => {

    const totalRows =
        transfers.value?.data?.length ?? 0

    return (
        totalRows > 0 &&
        selectedRows.value.length === totalRows
    )

})


const isIndeterminate = computed(() => {

    const totalRows =
        transfers.value?.data?.length ?? 0

    return (
        selectedRows.value.length > 0 &&
        selectedRows.value.length < totalRows
    )

})


watch(
    isIndeterminate,
    (value) => {

        if (selectAllRef.value) {

            selectAllRef.value.indeterminate = value

        }

    },
    {
        immediate: true,
    }
)


function toggleSelectAll(event)
{
    if (event.target.checked) {

        selectedRows.value =
            transfers.value?.data?.map(
                item => item.id
            ) ?? []

    } else {

        selectedRows.value = []

    }
}


/*
|--------------------------------------------------------------------------
| View State
|--------------------------------------------------------------------------
*/

const view = ref('list')

const formMode = ref('create')

const editingItem = ref(null)


/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/
const createEmptyDetail = () => ({
    product_variant_id: null,
    unit_id: null,

    qty: 0,

    unit_cost: 0,
    total_cost: 0,

    description: null,
})

const form = useForm({

    number:
        props.previewNumber ?? '',

    company_id:
        props.companyId ?? null,

    transaction_date:
        new Date()
            .toISOString()
            .slice(0, 10),

    from_branch_id:
        null,

    from_warehouse_id:
        null,

    to_branch_id:
        null,

    to_warehouse_id:
        null,

    description:
        null,

    details: [
        createEmptyDetail(),
    ],

})
watch(
    () => form.from_warehouse_id,
    (newValue, oldValue) => {

        console.log(
            '🔥 PARENT FROM WAREHOUSE:',
            oldValue,
            '=>',
            newValue
        )

    }
)


watch(
    () => form.to_warehouse_id,
    (newValue, oldValue) => {

        console.log(
            '🔥 PARENT TO WAREHOUSE:',
            oldValue,
            '=>',
            newValue
        )

    }
)

/*
|--------------------------------------------------------------------------
| Filtered Options
|--------------------------------------------------------------------------
*/

const filteredVariants = computed(() => {

    return props.variants ?? []

})


const filteredUnits = computed(() => {

    return props.units ?? []

})


/*
|--------------------------------------------------------------------------
| Create
|--------------------------------------------------------------------------
*/

function create()
{
      console.log(
        '🚨 CREATE() DIPANGGIL',
        new Error().stack
    )
    isEditing.value = false

    formMode.value = 'create'

    editingItem.value = null

    form.reset()

    form.clearErrors()

    form.number =
        props.previewNumber ?? ''

    form.company_id =
        props.companyId ?? null

    form.transaction_date =
        new Date()
            .toISOString()
            .slice(0, 10)

    form.from_branch_id =
        null

    form.from_warehouse_id =
        null

    form.to_branch_id =
        null

    form.to_warehouse_id =
        null

    form.description =
        null

    form.details = [
        createEmptyDetail(),
    ]

    view.value = 'form'
}

/*
|--------------------------------------------------------------------------
| Cancel Form
|--------------------------------------------------------------------------
*/

function cancelForm()
{
    form.reset()

    form.clearErrors()

    editingItem.value = null

    view.value = 'list'
}


/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/

function submit()
{
    if (
        formMode.value === 'create'
    ) {

        console.log(
            'SUBMIT STOCK TRANSFER:',
            JSON.parse(
                JSON.stringify(
                    form.data()
                )
            )
        )

        form.post(
            route(
                'stock-transfers.store'
            ),
            {
                preserveScroll: true,

                onSuccess: () => {

                    success(
                        'Success',
                        'Stock Transfer created successfully.'
                    )

                    view.value =
                        'list'

                },

                onError: (errors) => {

                    console.error(
                        'CREATE STOCK TRANSFER ERRORS:',
                        errors
                    )

                    error(
                        'Failed to create Stock Transfer.'
                    )

                },
            }
        )

        return
    }


    form.put(
        route(
            'stock-transfers.update',
            editingItem.value.id
        ),
        {
            preserveScroll: true,

            onSuccess: () => {

                success(
                    'Success',
                    'Stock Transfer updated successfully.'
                )

                view.value =
                    'list'

            },

            onError: (errors) => {

                console.error(
                    'UPDATE STOCK TRANSFER ERRORS:',
                    errors
                )

                error(
                    'Failed to update Stock Transfer.'
                )

            },
        }
    )
}

/*
|--------------------------------------------------------------------------
| Save & New
|--------------------------------------------------------------------------
*/

function submitAndNew()
{
    form.post(
        route(
            'stock-transfers.store'
        ),
        {
            preserveScroll: true,

            onSuccess: () => {

                success(
                    'Success',
                    'Stock Transfer created successfully.'
                )

                form.reset()

                form.clearErrors()

                form.number =
                    props.previewNumber ?? ''

                form.company_id =
                    props.companyId ?? null

                form.transaction_date =
                    new Date()
                        .toISOString()
                        .slice(0, 10)

                form.from_branch_id =
                    null

                form.from_warehouse_id =
                    null

                form.to_branch_id =
                    null

                form.to_warehouse_id =
                    null

                form.description =
                    null

                form.details = [
                    createEmptyDetail(),
                ]

            },

            onError: (errors) => {

                console.error(
                    'SAVE & NEW STOCK TRANSFER ERRORS:',
                    errors
                )

            },
        }
    )
}

/*
|--------------------------------------------------------------------------
| Edit
|--------------------------------------------------------------------------
*/

function editTransfer(item)
{
    /*
    |--------------------------------------------------------------------------
    | DEBUG — Edit Stock Transfer
    |--------------------------------------------------------------------------
    */

    console.log(
        '========== EDIT STOCK TRANSFER ITEM =========='
    )

    console.log(
        'EDIT ITEM:',
        item
    )

    console.log(
        'FROM BRANCH ID:',
        item?.from_branch_id
    )

    console.log(
        'FROM WAREHOUSE ID:',
        item?.from_warehouse_id
    )

    console.log(
        'FROM WAREHOUSE:',
        item?.from_warehouse
    )

    console.log(
        'TO BRANCH ID:',
        item?.to_branch_id
    )

    console.log(
        'TO WAREHOUSE ID:',
        item?.to_warehouse_id
    )

    console.log(
        'TO WAREHOUSE:',
        item?.to_warehouse
    )


    /*
    |--------------------------------------------------------------------------
    | Validate Status
    |--------------------------------------------------------------------------
    */

    if (
        ![
            'Draft',
            'Rejected',
        ].includes(item.status)
    ) {

        error(
            'Posted Stock Transfer cannot be edited.'
        )

        return

    }


    /*
    |--------------------------------------------------------------------------
    | Edit State
    |--------------------------------------------------------------------------
    */

    isEditing.value =
        true

    editingItem.value =
        item

    formMode.value =
        'edit'

    form.clearErrors()


    /*
    |--------------------------------------------------------------------------
    | Header
    |--------------------------------------------------------------------------
    */

    form.number =
        item.number


    form.company_id =
        item.company_id


    form.transaction_date =
        item.transaction_date
            ? String(
                item.transaction_date
            ).slice(0, 10)
            : null


    /*
    |--------------------------------------------------------------------------
    | Source Location
    |--------------------------------------------------------------------------
    */

    form.from_branch_id =
    item?.from_branch_id ?? null


/*
|--------------------------------------------------------------------------
| Destination Location
|--------------------------------------------------------------------------
*/

form.to_branch_id =
    item?.to_branch_id ?? null
    /*
    |--------------------------------------------------------------------------
    | Description
    |--------------------------------------------------------------------------
    */

    form.description =
        item.description


    /*
    |--------------------------------------------------------------------------
    | Details
    |--------------------------------------------------------------------------
    */

    form.details =
    item.details?.map(
        detail => ({

            product_variant_id:
                detail.product_variant_id,

            unit_id:
                detail.unit_id,

            available_qty:
                0,

            qty:
                detail.qty,

            unit_cost:
                0,

            total_cost:
                detail.total_cost,

            description:
                detail.description,

        })
    ) ?? [
        createEmptyDetail(),
    ]


    view.value =
    'form'


/*
|--------------------------------------------------------------------------
| Hydrate Warehouses After Form Mount
|--------------------------------------------------------------------------
*/

nextTick(() => {

    form.from_warehouse_id =
        item.from_warehouse_id

    form.to_warehouse_id =
        item.to_warehouse_id


    console.log(
        '========== EDIT WAREHOUSE HYDRATED =========='
    )

    console.log(
        'FORM FROM WAREHOUSE:',
        form.from_warehouse_id
    )

    console.log(
        'FORM TO WAREHOUSE:',
        form.to_warehouse_id
    )

})

}/*
|--------------------------------------------------------------------------
| Show
|--------------------------------------------------------------------------
*/

const selectedItem = ref(null)

function showTransfer(item)
{
    selectedItem.value = item

    view.value = 'show'
}

/*
|--------------------------------------------------------------------------
| Back From Show
|--------------------------------------------------------------------------
*/

function backToList()
{
    selectedItem.value = null

    view.value = 'list'
}


/*
|--------------------------------------------------------------------------
| Duplicate
|--------------------------------------------------------------------------
*/

function duplicate(item)
{
    router.post(
        route(
            'stock-transfers.duplicate',
            item.id
        ),
        {},
        {
            preserveScroll: true,

            onSuccess: () => {

                success(
                    'Success',
                    'Stock Transfer duplicated successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to duplicate Stock Transfer.'
                )

            },
        }
    )
}

/*
|--------------------------------------------------------------------------
| Delete
|--------------------------------------------------------------------------
*/

const deleteItem = ref(null)

const showDelete = ref(false)


function openDelete(item)
{
    if (
        ![
            'Draft',
            'Rejected',
        ].includes(item.status)
    ) {

        error(
            'Only Draft or Rejected Stock Transfer can be deleted.'
        )

        return
    }

    deleteItem.value =
        item

    showDelete.value =
        true
}


function closeDelete()
{
    deleteItem.value = null

    showDelete.value = false
}


const deleteMessage = computed(() => {

    if (!deleteItem.value) {

        return ''

    }

    return `Are you sure you want to delete "${deleteItem.value.number}"?`

})


function confirmDelete()
{
    if (!deleteItem.value) {

        return

    }

    router.delete(
        route(
            'stock-transfers.destroy',
            deleteItem.value.id
        ),
        {
            preserveScroll: true,

            onSuccess: () => {

                closeDelete()

                success(
                    'Success',
                    'Stock Transfer deleted successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to delete Stock transfer.'
                )

            },
        }
    )
}

/*
|--------------------------------------------------------------------------
| Bulk Delete
|--------------------------------------------------------------------------
*/

const showBulkDelete = ref(false)


function openBulkDelete()
{
    if (!selectedRows.value.length) {

        return

    }

    showBulkDelete.value = true
}


const bulkDeleteMessage = computed(() => {

    const total =
        selectedRows.value.length

    if (!total) {

        return ''

    }

    return `Are you sure you want to delete ${total} selected Stock Transfer document(s)?`

})


function bulkDelete()
{
    router.delete(
        route('Stock Transfer.bulk-delete'),
        {
            data: {
                ids: selectedRows.value,
            },

            preserveScroll: true,

            onSuccess: () => {

                showBulkDelete.value = false

                selectedRows.value = []

                success(
                    'Success',
                    'Stock Transfer deleted successfully.'
                )

            },

        }
    )
}
/*
|--------------------------------------------------------------------------
| Post
|--------------------------------------------------------------------------
*/

const postItem = ref(null)

const showPost = ref(false)


/*
|--------------------------------------------------------------------------
| Reject
|--------------------------------------------------------------------------
*/

const rejectItem = ref(null)

const showReject = ref(false)

const rejectReason = ref('')

function openPost(transfer)
{
    postItem.value = transfer

    showPost.value = true
}
function closePost()
{
    postItem.value = null

    showPost.value = false
}
function openReject(transfer)
{
    rejectItem.value = transfer

    rejectReason.value = ''

    showReject.value = true
}
function closeReject()
{
    rejectItem.value = null

    rejectReason.value = ''

    showReject.value = false
}
function confirmPost()
{
    if (!postItem.value) {

        return

    }

    router.post(

        route(
            'stock-transfers.post',
            postItem.value.id
        ),

        {},

        {

            preserveScroll: true,

            onSuccess: () => {

                closePost()

                success(
                    'Success',
                    'Stock Transfers posted successfully.'
                )

            },

            onError: (errors) => {

                   error(
                    'Failed to post stock Transer.'
                )

            },

        }

    )
}
function confirmReject()
{
    if (!rejectItem.value) {

        return

    }

    if (!rejectReason.value.trim()) {

        error(
            'Cancellation reason is required.'
        )

        return

    }

    router.post(
        route(
            'stock-transfers.cancel',
            rejectItem.value.id
        ),
        {
            reason:
                rejectReason.value.trim(),
        },
        {
            preserveScroll: true,

            onSuccess: () => {

                closeReject()

                success(
                    'Success',
                    'Stock Transfer cancelled successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to cancel Stock Transfer.'
                )

            },
        }
    )
}

const postMessage = computed(() => {

    if (!postItem.value) {
        return ''
    }

    return `Are you sure you want to post "${postItem.value.number}"? Once posted, Stock Transer will be updated.`
})
/*
|--------------------------------------------------------------------------
| Lifecycle
|--------------------------------------------------------------------------
*/

onMounted(() => {

    removeStartListener =
        router.on(
            'start',
            startLoading
        )

    removeFinishListener =
        router.on(
            'finish',
            stopLoading
        )

})


onUnmounted(() => {

    removeStartListener?.()

    removeFinishListener?.()

})

/*
|--------------------------------------------------------------------------
| Sorting
|--------------------------------------------------------------------------
*/

const sort = ref(
    props.filters?.sort_by ?? 'id'
)

const direction = ref(
    props.filters?.sort_direction ?? 'desc'
)

function sortBy(column)
{
    if (sort.value === column) {

        direction.value =
            direction.value === 'asc'
                ? 'desc'
                : 'asc'

    } else {

        sort.value = column

        direction.value = 'asc'

    }

    router.get(
        route('stock-transfers.index'),
        {
            search:
                filters.search,

            from_branch_id:
                filters.from_branch_id,

            from_warehouse_id:
                filters.from_warehouse_id,

            to_branch_id:
                filters.to_branch_id,

            to_warehouse_id:
                filters.to_warehouse_id,

            date_from:
                filters.date_from,

            date_to:
                filters.date_to,

            status:
                filters.status,

            per_page:
                filters.per_page,

            sort_by:
                sort.value,

            sort_direction:
                direction.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    )
}
/** filtered warehouse */
const isEditing = ref(false)
const filteredFromWarehouses = computed(() => {

    if (!form.from_branch_id) {
        return []
    }

    return props.warehouses.filter(
        warehouse =>
            Number(warehouse.branch_id) ===
            Number(form.from_branch_id)
    )

})


const filteredToWarehouses = computed(() => {

    if (!form.to_branch_id) {
        return []
    }

    return props.warehouses.filter(
        warehouse =>
            Number(warehouse.branch_id) ===
            Number(form.to_branch_id)
    )

})
watch(
    () => form.from_branch_id,
    (newBranch, oldBranch) => {

        /*
        |--------------------------------------------------------------------------
        | Initial Edit Load
        |--------------------------------------------------------------------------
        */

        if (
            props.mode === 'edit' &&
            oldBranch === null &&
            newBranch !== null
        ) {

            return

        }


        /*
        |--------------------------------------------------------------------------
        | No Actual Change
        |--------------------------------------------------------------------------
        */

        if (
            newBranch === oldBranch
        ) {

            return

        }


        /*
        |--------------------------------------------------------------------------
        | Reset Source Warehouse
        |--------------------------------------------------------------------------
        */

        form.from_warehouse_id =
            null

        warehouseStocks.value =
            []

    }
)


watch(
    () => form.to_branch_id,
    (newBranch, oldBranch) => {

        /*
        |--------------------------------------------------------------------------
        | Initial Edit Load
        |--------------------------------------------------------------------------
        */

        if (
            props.mode === 'edit' &&
            oldBranch === null &&
            newBranch !== null
        ) {

            return

        }


        /*
        |--------------------------------------------------------------------------
        | No Actual Change
        |--------------------------------------------------------------------------
        */

        if (
            newBranch === oldBranch
        ) {

            return

        }


        /*
        |--------------------------------------------------------------------------
        | Reset Destination Warehouse
        |--------------------------------------------------------------------------
        */

        form.to_warehouse_id =
            null

    }
)

function canEdit(item)
{
    return [
        'Draft',
        'Rejected',
    ].includes(item.status)
}

function canPost(item)
{
    return item.status === 'Draft'
}

function canReject(item)
{
    return item.status === 'Draft'
}

function canDelete(item)
{
    return [
        'Draft',
        'Rejected',
    ].includes(item.status)
}
/** view modal */
const viewLoading = ref(false)
const showView = ref(false)
const viewItem = ref(null)

async function openView(item)
{
    viewLoading.value = true

    viewItem.value = null

    showView.value = true

    try {

        const response = await fetch(
            route(
                'stock-transfers.data',
                item.id
            ),
            {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            }
        )

        if (!response.ok) {

            throw new Error(
                `HTTP ${response.status}`
            )

        }

        const responseData =
            await response.json()

        console.log(
            'Transfer STOCK VIEW RESPONSE:',
            responseData
        )

        viewItem.value =
            responseData.data

    } catch (exception) {

        console.error(
            'Transfers STOCK VIEW ERROR:',
            exception
        )

        showView.value = false

        viewItem.value = null

        error(
            'Failed to load Transfers stock detail.'
        )

    } finally {

        viewLoading.value = false

    }
}

function closeView()
{
    viewItem.value = null

    showView.value = false
}
/** view */

function exportSelected()
{
    console.log(
        'EXPORT SELECTED:',
        selectedRows.value
    )
}
function formatNumber(value)
{
    return new Intl.NumberFormat(
        'id-ID',
        {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2,
        }
    ).format(
        Number(value ?? 0)
    )
}

const sameWarehouse = computed(() => {

    return (
        form.from_warehouse_id &&
        form.to_warehouse_id &&
        Number(
            form.from_warehouse_id
        ) ===
        Number(
            form.to_warehouse_id
        )
    )

})
const warehouseStocks = ref([])

const warehouseStockLoading = ref(false)

const availableWarehouseStocks = computed(() => {

    return warehouseStocks.value ?? []

})
const datePreset = ref(
    props.filters?.date_preset ?? ''
)


const datePresetOptions = [

    {
        value: '',
        label: 'All Dates',
    },

    {
        value: 'today',
        label: 'Today',
    },

    {
        value: 'this_week',
        label: 'This Week',
    },

    {
        value: 'this_month',
        label: 'This Month',
    },

    {
        value: 'this_year',
        label: 'This Year',
    },

    {
        value: 'custom',
        label: 'Custom',
    },

]
</script>
<template>
<AppLayout>
   
    <Transition
        name="page"
        mode="out-in"
    >

        <!-- LIST part b-->
        <div
            v-if="view === 'list'"
            key="list"
        >

        <div class="space-y-6">

            <!-- ===================================================== -->
            <!-- Page Header -->
            <!-- ===================================================== -->

            <PageHeader
                icon="📦"
                title="Stock Transfer"
                subtitle="Manage Stock Transfer transactions."
            />


            <!-- ===================================================== -->
            <!-- Statistics -->
            <!-- ===================================================== -->

            <div
                class="
                    grid
                    grid-cols-1
                    gap-4
                    md:grid-cols-4
                "
            >

               <StatsCard
                    title="Total Transfers Stock"
                    :value="statistics?.total ?? 0"
                    icon="📦"
                />

                <StatsCard
                    title="Draft"
                    :value="statistics?.draft ?? 0"
                    icon="📝"
                />

                <StatsCard
                    title="Rejected"
                    :value="statistics?.rejected ?? 0"
                    icon="❌"
                />

                <StatsCard
                    title="Posted"
                    :value="statistics?.posted ?? 0"
                    icon="✅"
                />
            </div>

        </div>


        <!-- ========================================================= -->
        <!-- List Card -->
        <!-- ========================================================= -->

        <Card class="mt-4">

            <!-- ===================================================== -->
            <!-- Toolbar -->
            <!-- ===================================================== -->

            <div
                class="
                    flex
                    flex-col
                    gap-4
                    lg:flex-row
                    lg:items-center
                    lg:justify-between
                "
            >

                <!-- Left -->
                <div
                    class="
                        flex
                        flex-1
                        flex-col
                        gap-3
                        lg:flex-row
                        lg:items-center
                    "
                >

                    <!-- Search -->

                    <input
                        v-model="filters.search"
                        type="text"
                        placeholder="Search number..."
                        class="
                            w-full
                            lg:w-80
                            rounded-xl
                            border
                            border-gray-300
                            px-4
                            py-2.5
                        "
                    />


                    <!-- Branch -->

                    <SearchableSelect
                        v-model="filters.from_branch_id"
                        :options="branches"
                        label="label"
                        value-key="id"
                        placeholder="From Branch"
                    />


                    <SearchableSelect
                        v-model="filters.from_warehouse_id"
                        :options="warehouses"
                        label="label"
                        value-key="id"
                        placeholder="From Warehouse"
                    />
                    <SearchableSelect
                        v-model="filters.to_branch_id"
                        :options="branches"
                        label="label"
                        value-key="id"
                        placeholder="To Branch"
                    />
                    <SearchableSelect
                        v-model="filters.to_warehouse_id"
                        :options="warehouses"
                        label="label"
                        value-key="id"
                        placeholder="To Warehouse"
                    />
                    <SearchableSelect
                    v-model="filters.status"
                    :options="statusOptions"
                    label="label"
                    value-key="value"
                    placeholder="All Status"
                    />
                </div>
                <!-- Right -->

                <div
                    class="
                        flex
                        flex-col
                        gap-2
                        w-full
                        lg:w-auto
                        lg:flex-row
                        lg:items-center
                    "
                >

                    <!-- Refresh -->

                    <BaseButton
                        variant="secondary"
                        class="
                            w-full
                            shrink-0
                            whitespace-nowrap
                            lg:w-auto
                        "
                        @click="refresh"
                    >
                        Refresh
                    </BaseButton>


                    <!-- Add -->

                    <BaseButton
                        class="
                            w-full
                            shrink-0
                            whitespace-nowrap
                            lg:w-auto
                        "
                        @click="create"
                    >

                        <template #icon>

                            <PlusIcon class="h-5 w-5" />

                        </template>

                        Add

                    </BaseButton>


                    <!-- Bulk -->

                    <BulkActionDropdown
                        :count="selectedRows.length"
                        :disabled="
                            selectedRows.length === 0
                        "
                        :actions="[
                            'export',
                            'delete',
                        ]"
                        @delete="openBulkDelete"
                        @export="exportSelected"
                        class="w-full lg:w-auto"
                    />

                </div>               

            </div>


            <!-- ===================================================== -->
            <!-- Table -->
            <!-- ===================================================== -->

            <div class="mt-6">

                <!-- Loading -->

                <LoadingOverlay
                    :show="loading"
                    text="Loading Transfer Stock..."
                />


                <!-- Data -->

                <DataTable
                    v-if="transfers?.data?.length"
                    sticky-header
                    max-height="650px"
                >

                    <DataTableHead sticky>

                        <!-- Select All -->

                        <DataTableHeaderCell
                            width="60px"
                            align="center"
                        >

                            <input
                                ref="selectAllRef"
                                type="checkbox"
                                :checked="isAllSelected"
                                @change="toggleSelectAll"
                                class="rounded border-gray-300"
                            />

                        </DataTableHeaderCell>


                        <!-- Number -->

                        <DataTableHeaderCell
                            sortable
                            column="number"
                            :sort="sort"
                            :direction="direction"
                            @sort="sortBy"
                            width="180px"
                        >
                            Number
                        </DataTableHeaderCell>


                        <!-- Date -->

                        <DataTableHeaderCell
                            sortable
                            column="transaction_date"
                            :sort="sort"
                            :direction="direction"
                            @sort="sortBy"
                            width="160px"
                        >
                            Transaction Date
                        </DataTableHeaderCell>


                        <!-- From -->

                        <DataTableHeaderCell
                            width="260px"
                        >
                            From
                        </DataTableHeaderCell>


                        <!-- To -->

                        <DataTableHeaderCell
                            width="260px"
                        >
                            To
                        </DataTableHeaderCell>


                        <!-- Items -->

                        <DataTableHeaderCell
                            width="100px"
                            align="right"
                        >
                            Items
                        </DataTableHeaderCell>


                        <!-- Total Qty -->

                        <DataTableHeaderCell
                            width="120px"
                            align="right"
                        >
                            Total Qty
                        </DataTableHeaderCell>


                        <!-- Total Cost -->

                        <DataTableHeaderCell
                            sortable
                            column="total_cost"
                            :sort="sort"
                            :direction="direction"
                            @sort="sortBy"
                            width="180px"
                            align="right"
                        >
                            Total Cost
                        </DataTableHeaderCell>


                        <!-- Status -->

                        <DataTableHeaderCell
                            sortable
                            column="status"
                            :sort="sort"
                            :direction="direction"
                            @sort="sortBy"
                            width="120px"
                            align="center"
                        >
                            Status
                        </DataTableHeaderCell>


                        <!-- Actions -->

                        <DataTableHeaderCell
                            width="100px"
                            align="center"
                        >
                            Actions
                        </DataTableHeaderCell>

                    </DataTableHead>


                    <DataTableBody>

                        <DataTableRow
                            v-for="item in transfers.data"
                            :key="item.id"
                        >

                            <!-- Checkbox -->

                            <DataTableCell align="center">

                                <input
                                    v-model="selectedRows"
                                    :value="item.id"
                                    type="checkbox"
                                    class="rounded border-gray-300"
                                />

                            </DataTableCell>


                            <!-- Number -->

                            <DataTableCell>

                                <span class="font-medium">
                                    {{ item.number }}
                                </span>

                            </DataTableCell>


                            <!-- Transaction Date -->

                            <DataTableCell>
                                {{ formatDate(item.transaction_date) }}

                            </DataTableCell>


                            <!-- From Branch warehouse -->

                            <DataTableCell>

                                <div
                                    class="
                                        font-medium
                                        text-gray-900
                                    "
                                >
                                    {{ item.from_branch?.name ?? '-' }}
                                </div>

                                <div
                                    class="
                                        text-xs
                                        text-gray-500
                                    "
                                >
                                    {{ item.from_warehouse?.name ?? '-' }}
                                </div>

                            </DataTableCell>

                            <!-- Warehouse -->

                            <DataTableCell>

                                <div
                                    class="
                                        font-medium
                                        text-gray-900
                                    "
                                >
                                    {{ item.to_branch?.name ?? '-' }}
                                </div>

                                <div
                                    class="
                                        text-xs
                                        text-gray-500
                                    "
                                >
                                    {{ item.to_warehouse?.name ?? '-' }}
                                </div>

                            </DataTableCell>

                            <!-- Items -->
                            <DataTableCell align="right">

                                {{
                                    item.details_count
                                    ?? item.details?.length
                                    ?? 0
                                }}

                            </DataTableCell>
                            


                            <!-- Total qty -->

                            <DataTableCell align="right">

                                {{
                                    formatNumber(
                                        item.total_qty
                                        ??
                                        item.details?.reduce(
                                            (
                                                total,
                                                detail
                                            ) =>
                                                total +
                                                Number(
                                                    detail.qty ?? 0
                                                ),
                                            0
                                        )
                                    )
                                }}

                            </DataTableCell>
                            <DataTableCell align="right">

                                {{
                                    currency(
                                        item.total_cost ?? 0
                                    )
                                }}

                            </DataTableCell>

                            <!-- Status -->

                            <DataTableCell align="center">

                                <StatusBadge
                                    :status="item.status"
                                />

                            </DataTableCell>


                            <!-- Actions -->
                            <DataTableCell align="center">

                                <ActionDropdown
                                    @view="showTransfer(item)"
                                    @edit="editTransfer(item)"
                                    @duplicate="duplicate(item)"
                                    @post="openPost(item)"
                                    @reject="openReject(item)"
                                    @delete="openDelete(item)"

                                    :showEdit="canEdit(item)"
                                    :showDuplicate="true"
                                    :showPost="canPost(item)"
                                    :showReject="canReject(item)"
                                    :showExport="false"
                                    :showHistory="false"
                                    :showDelete="canDelete(item)"
                                />

                            </DataTableCell>

                        </DataTableRow>

                    </DataTableBody>

                </DataTable>


                <!-- Empty -->

                <TableEmpty
                    v-else
                    icon="🔄"
                    title="No Stock Transfer Found"
                    description="There are no stock transfer transactions available."
                >

                    <template #action>

                        <BaseButton
                            @click="create"
                        >
                            Create Stock Transfer
                        </BaseButton>

                    </template>

                </TableEmpty>

            </div>


            <!-- ===================================================== -->
            <!-- Pagination -->
            <!-- ===================================================== -->

            <div class="mt-6">

                <TablePagination
                    :data="transfers"
                    label="Transfers Stock"
                />

            </div>

        </Card>
            
        </div>
        <!-- end part b-->

        <!-- FORM -->
        <div
            v-else-if="view === 'form'"
            key="form"
            class="space-y-6"
        >

            <PageHeader
                icon="📦"
                :title="
                    formMode === 'create'
                        ? 'Create Stock Transfer'
                        : 'Edit Strock Transfer'
                "
                :subtitle="
                    formMode === 'create'
                        ? 'Create a new Transfer Stock transaction.'
                        : 'Update Transfer Stock transaction.'
                "
            />

        <Card>

               <StockTransferForm
                    :form="form"
                    :branches="branches"
                    :warehouses="warehouses"
                    :filtered-variants="filteredVariants"
                    :mode="formMode"
                    @submit="submit"
                    @submit-and-new="submitAndNew"
                    @cancel="cancelForm"
                />
            </Card>
          

        </div>

    </Transition>

</AppLayout>
  <ConfirmDeleteModal
    :show="showDelete"
    title="Delete Stock Transfer"
    :message="deleteMessage"
    confirm-text="Delete"
    @close="closeDelete"
    @confirm="confirmDelete"
/>
<StockTransferPostModal
    :show="showPost"
    :transfer="postItem"
    :loading="postLoading"
    @close="closePost"
    @confirm="confirmPost"
/>
<StockTransferRejectModal
    :show="showReject"
    :transfer="rejectItem"
    :reason="rejectReason"
    @close="closeReject"
    @confirm="confirmReject"
    @update:reason="rejectReason = $event"
/>
</template>
<style scoped>

.page-enter-active,
.page-leave-active {
    transition:
        opacity 0.2s ease,
        transform 0.2s ease;
}

.page-enter-from {
    opacity: 0;
    transform: translateY(8px);
}

.page-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}
</style>