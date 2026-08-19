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
import StockIssueForm from './Partials/StockIssueForm.vue'
import StockIssueViewModal from './Partials/StockIssueViewModal.vue'
import StockIssuePostModal from './Partials/StockIssuePostModal.vue'
import StockIssueRejectModal from './Partials/StockIssueRejectModal.vue'
import ConfirmDeleteModal from '@/Components/Modal/ConfirmDeleteModal.vue'
import { formatCurrency } from '@/Utils/currency'
import FlatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'
/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    issues: {
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

    issueTypeOptions: {
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
    issues,
    statistics,
} = toRefs(props)


/*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/

const pageTitle = computed(() => 'Stock Issues')


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

    branch_id:
        props.filters?.branch_id ?? '',

    warehouse_id:
        props.filters?.warehouse_id ?? '',

    issue_type:
        props.filters?.issue_type ?? '',

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
        route('stock-issues.index'),
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
    () => filters.branch_id,
    () => {

        loadData()

    }
)


watch(
    () => filters.warehouse_id,
    () => {

        loadData()

    }
)
watch(
    () => filters.issue_type,
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

        branch_id: '',

        warehouse_id: '',

        issue_type: '',

        status: '',

        date_from: '',

        date_to: '',

        per_page: 10,

    })

    dateRange.value = ''

    loadData()
}
const dateRange = ref('')
function formatDateForFilter(date)
{
    const year =
        date.getFullYear()

    const month =
        String(
            date.getMonth() + 1
        ).padStart(2, '0')

    const day =
        String(
            date.getDate()
        ).padStart(2, '0')

    return `${year}-${month}-${day}`
}
function handleDateRangeChange(selectedDates)
{
    if (!selectedDates.length) {

        filters.date_from = ''

        filters.date_to = ''

        loadData()

        return
    }


    filters.date_from =
        formatDateForFilter(
            selectedDates[0]
        )


    filters.date_to =
        selectedDates.length > 1
            ? formatDateForFilter(
                selectedDates[1]
            )
            : formatDateForFilter(
                selectedDates[0]
            )


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
        issues.value?.data?.length ?? 0

    return (
        totalRows > 0 &&
        selectedRows.value.length === totalRows
    )

})


const isIndeterminate = computed(() => {

    const totalRows =
        issues.value?.data?.length ?? 0

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
            issues.value?.data?.map(
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

    available_qty: 0,

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

    branch_id:
        null,

    warehouse_id:
        null,

    issue_type:
        null,

    transaction_date:
        new Date()
            .toISOString()
            .slice(0, 10),

    description:
        null,

    details: [
        createEmptyDetail(),
    ],

})
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
    isEditing.value = false

    formMode.value = 'create'

    editingItem.value = null

    form.reset()

    form.clearErrors()

    form.number =
        props.previewNumber ?? ''

    form.company_id =
        props.companyId ?? null

    form.branch_id =
        null

    form.warehouse_id =
        null

    form.issue_type =
        null

    form.transaction_date =
        new Date()
            .toISOString()
            .slice(0, 10)

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
    if (formMode.value === 'create') {

        console.log(
            'SUBMIT STOCK ISSUE:',
            JSON.parse(JSON.stringify(form.data()))
        )

        form.post(
            route('stock-issues.store'),
            {
                preserveScroll: true,

                onSuccess: () => {

                    console.log(
                        'CREATE SUCCESS'
                    )

                    success(
                        'Success',
                        'Stock Issues created successfully.'
                    )

                    view.value = 'list'
                },

                onError: (errors) => {

                    console.error(
                        'CREATE ERRORS:',
                        errors
                    )

                },

                onFinish: () => {

                    console.log(
                        'CREATE FINISHED'
                    )

                },
            }
        )

        return
    }

    form.put(
        route(
            'stock-issues.update',
            editingItem.value.id
        ),
        {
            preserveScroll: true,

            onSuccess: () => {

                success(
                    'Success',
                    'Stock Issue updated successfully.'
                )

                view.value = 'list'

            },

            onError: (errors) => {

                console.error(
                    'UPDATE ERRORS:',
                    errors
                )

                error(
                    'Failed to update Stock Issue.'
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
        route('stock-issues.store'),
        {
            preserveScroll: true,

            onSuccess: () => {

                success(
                    'Success',
                    'Stock Issue created successfully.'
                )

                form.reset()

                form.clearErrors()

                form.number =
                    props.previewNumber ?? ''

                form.company_id =
                    props.companyId

                form.transaction_date =
                    new Date()
                        .toISOString()
                        .slice(0, 10)

                form.details = [
                    createEmptyDetail(),
                ]

            },
        }
    )
}


/*
|--------------------------------------------------------------------------
| Edit
|--------------------------------------------------------------------------
*/
function editStockIssue(item)
{
    /*
    |--------------------------------------------------------------------------
    | DEBUG — Edit Stock Issue
    |--------------------------------------------------------------------------
    */

    console.log(
        '========== EDIT STOCK ISSUE ITEM =========='
    )

    console.log(
        'EDIT ITEM:',
        item
    )

    console.log(
        'BRANCH ID:',
        item?.branch_id
    )

    console.log(
        'WAREHOUSE ID:',
        item?.warehouse_id
    )

    console.log(
        'WAREHOUSE:',
        item?.warehouse
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
            'Posted Stock Issue cannot be edited.'
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

    form.issue_type =
        item.issue_type

    form.branch_id =
        item?.branch_id ?? null

    form.description =
        item.description ?? null


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
                 Number(detail.qty ?? 0),

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


    /*
    |--------------------------------------------------------------------------
    | Show Form
    |--------------------------------------------------------------------------
    */

    view.value =
        'form'


    /*
    |--------------------------------------------------------------------------
    | Hydrate Warehouse After Form Mount
    |--------------------------------------------------------------------------
    */

    nextTick(() => {

        form.warehouse_id =
            item.warehouse_id

        console.log(
            '========== EDIT WAREHOUSE HYDRATED =========='
        )

        console.log(
            'FORM WAREHOUSE:',
            form.warehouse_id
        )

    })

}
/*
|--------------------------------------------------------------------------
| Show
|--------------------------------------------------------------------------
*/

const selectedItem = ref(null)

function showaIssue(item)
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
            'stock-issues.duplicate',
            item.id
        ),
        {},
        {
            preserveScroll: true,

            onSuccess: () => {

                success(
                    'Success',
                    'Stock Issues duplicated successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to duplicate Stock Issues.'
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
            'Only Draft or Rejected Stock Issue can be deleted.'
        )

        return
    }

    deleteItem.value = item

    showDelete.value = true
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
            'stock-issues.destroy',
            deleteItem.value.id
        ),
        {
            preserveScroll: true,

            onSuccess: () => {

                closeDelete()

                success(
                    'Success',
                    'Stock Issue deleted successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to delete Stock Issue.'
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

    return `Are you sure you want to delete ${total} selected Stock Issue document(s)?`

})


function bulkDelete()
{
    router.delete(
        route('stock-issues.bulk-delete'),
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
                    'Stock Issue deleted successfully.'
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
const showRejectModal = ref(false)

const rejectItem = ref(null)

const showReject = ref(false)

const rejectReason = ref('')

function openPost(issue)
{
    postItem.value = issue

    showPost.value = true
}
function closePost()
{
    postItem.value = null

    showPost.value = false
}

function closeReject()
{
    showRejectModal.value =
        false

    rejectItem.value =
        null

    rejectReason.value =
        ''
}
function confirmPost()
{
    if (!postItem.value) {

        return

    }

    router.post(

        route(
            'stock-issues.post',
            postItem.value.id
        ),

        {},

        {

            preserveScroll: true,

            onSuccess: () => {

                closePost()

                success(
                    'Success',
                    'Stock Issue posted successfully.'
                )

            },

            onError: (errors) => {

                   error(
                    'Failed to post Stock Issue.'
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
            'Rejection reason is required.'
        )

        return

    }

    router.post(

        route(
            'stock-issues.cancel',
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
                    'Stock Issue rejected successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to reject Stock Issue.'
                )

            },

        }

    )
}

const postMessage = computed(() => {

    if (!postItem.value) {
        return ''
    }

    return `Are you sure you want to post "${postItem.value.number}"? Once posted, Stockt Issue will be updated.`
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
        route('stock-issues.index'),
        {
             search: filters.search,

            branch_id:
                filters.branch_id,

            warehouse_id:
                filters.warehouse_id,

            issue_type:
                filters.issue_type,

            status:
                filters.status,

            date_from:
                filters.date_from,

            date_to:
                filters.date_to,

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
const filteredWarehouses = computed(() => {

    if (!form.branch_id) {
        return []
    }

    return props.warehouses.filter(
        warehouse =>
            Number(warehouse.branch_id) ===
            Number(form.branch_id)
    )

})
watch(
    () => form.branch_id,
    (newBranch, oldBranch) => {

        if (
            oldBranch === undefined ||
            newBranch === oldBranch
        ) {
            return
        }

        form.warehouse_id = null
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
                'stock-issues.data',
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
            'stock issue VIEW RESPONSE:',
            responseData
        )

        viewItem.value =
            responseData.data

    } catch (exception) {

        console.error(
            'stock issue VIEW ERROR:',
            exception
        )

        showView.value = false

        viewItem.value = null

        error(
            'Failed to load Stock Issue detail.'
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
function openReject(item)
{
    if (
        item.status !== 'Draft'
    ) {

        error(
            'Only Draft stock issue can be rejected.'
        )

        return
    }

    rejectItem.value =
        item

    rejectReason.value =
        ''

    showRejectModal.value =
        true
}
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
                    title="Total Issues Stock"
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

<!-- ========================================================= -->
<!-- Toolbar -->
<!-- ========================================================= -->

<div>

    <!-- ===================================================== -->
    <!-- TOP ROW -->
    <!-- Search + Date Range + Actions -->
    <!-- ===================================================== -->

    <div
        class="
            flex
            flex-col
            gap-3
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
                    rounded-xl
                    border
                    border-gray-300
                    px-4
                    py-2.5
                    lg:w-80
                "
            />


            <!-- Date Range -->

        <FlatPickr
            v-model="dateRange"
            :config="{
                mode: 'range',
                dateFormat: 'Y-m-d',
            }"
            placeholder="Date Range"
            class="
                w-full
                lg:w-56
                rounded-xl
                border
                border-gray-300
                px-4
                py-2.5
                text-sm
            "
            @on-change="handleDateRangeChange"
        />
        </div>


        <!-- Right Actions -->

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
    <!-- BOTTOM ROW -->
    <!-- Searchable Filters -->
    <!-- ===================================================== -->

    <div
        class="
            mt-3
            flex
            flex-col
            gap-3
            lg:flex-row
            lg:items-center
        "
    >

        <!-- Issue Type -->

        <SearchableSelect
            v-model="filters.issue_type"
            :options="issueTypeOptions"
            label="label"
            value-key="value"
            placeholder="All Issue Types"
            class="w-full lg:w-48"
        />


        <!-- Branch -->

        <SearchableSelect
            v-model="filters.branch_id"
            :options="branches"
            label="label"
            value-key="id"
            placeholder="All Branches"
            class="w-full lg:w-48"
        />


        <!-- Warehouse -->

        <SearchableSelect
            v-model="filters.warehouse_id"
            :options="warehouses"
            label="label"
            value-key="id"
            placeholder="All Warehouses"
            class="w-full lg:w-52"
        />


        <!-- Status -->

        <SearchableSelect
            v-model="filters.status"
            :options="statusOptions"
            label="label"
            value-key="value"
            placeholder="All Status"
            class="w-full lg:w-40"
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
                    text="Loading Stock Issue..."
                />


                <!-- Data -->

                <DataTable
                    v-if="issues?.data?.length"
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


                        <!-- Branch -->

                        <DataTableHeaderCell
                            width="180px"
                        >
                            Branch
                        </DataTableHeaderCell>


                        <!-- Warehouse -->

                        <DataTableHeaderCell
                            width="200px"
                        >
                            Issue Type
                        </DataTableHeaderCell>


                        <!-- Items -->

                        <DataTableHeaderCell
                            width="100px"
                            align="right"
                        >
                            Items
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
                        v-for="item in issues.data"
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

                            <span class="font-medium text-gray-900">
                                {{ item.number }}
                            </span>

                        </DataTableCell>


                        <!-- Transaction Date -->

                        <DataTableCell>

                            <span class="text-sm text-gray-700">
                                {{ formatDate(item.transaction_date) }}
                            </span>

                        </DataTableCell>


                        <!-- Location -->

                        <DataTableCell>

                            <div
                                class="
                                    font-semibold
                                    text-gray-900
                                "
                            >
                                {{ item.branch?.name ?? '-' }}
                            </div>

                            <div
                                class="
                                    mt-0.5
                                    text-xs
                                    text-gray-500
                                "
                            >
                                {{ item.warehouse?.name ?? '-' }}
                            </div>

                        </DataTableCell>


                        <!-- Issue Type -->

                        <DataTableCell>

                            <span
                                class="
                                    text-sm
                                    text-gray-700
                                "
                            >
                                {{ item.issue_type ?? '-' }}
                            </span>

                        </DataTableCell>


                        <!-- Items -->

                        <DataTableCell align="right">

                            <span
                                class="
                                    text-sm
                                    font-medium
                                    text-gray-900
                                "
                            >
                                {{
                                    item.details_count
                                    ?? item.details?.length
                                    ?? 0
                                }}
                            </span>

                        </DataTableCell>


                        <!-- Total Cost -->

                        <DataTableCell align="right">

                            <span
                                class="
                                    text-sm
                                    font-medium
                                    text-gray-900
                                "
                            >
                                {{
                                    currency(
                                        item.total_cost ?? 0
                                    )
                                }}
                            </span>

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

                                @view="openView(item)"
                                @edit="editStockIssue(item)"
                                @duplicate="duplicate(item)"
                                @post="openPost(item) "
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
                    icon="📦"
                    title="No Stock Issues Found"
                    description="There are no Stock Issue transactions available."
                >

                    <template #action>

                        <BaseButton
                            @click="create"
                        >
                            Create Stock Issues
                        </BaseButton>

                    </template>

                </TableEmpty>

            </div>


            <!-- ===================================================== -->
            <!-- Pagination -->
            <!-- ===================================================== -->

            <div class="mt-6">

                <TablePagination
                    :data="issues"
                    label="Stock Issue"
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
                        ? 'Create Stock Issue'
                        : 'Edit Stock Issue'
                "
                :subtitle="
                    formMode === 'create'
                        ? 'Create a new Stock Issue transaction.'
                        : 'Update Stock Issue transaction.'
                "
            />


            <Card>

                <StockIssueForm
                    :form="form"
                    :branches="branches"
                    :warehouses="warehouses"
                    :filtered-variants="filteredVariants"
                    :issue-type-options="issueTypeOptions"
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
    title="Delete Stock Issue"
    :message="deleteMessage"
    confirm-text="Delete"
    @close="closeDelete"
    @confirm="confirmDelete"
/>

<StockIssueViewModal
    :show="showView"
    :issue="viewItem"
    :loading="viewLoading"
    @close="closeView"
/>
<StockIssuePostModal
    :show="showPost"
    :issue="postItem"
    :loading="postLoading"
    @close="closePost"
    @confirm="confirmPost"
/>
<StockIssueRejectModal
    :show="showRejectModal"
    :issue="rejectItem"
    :reason="rejectReason"
    @close="closeReject"
    @confirm="confirmReject"
    @update:reason="
        rejectReason = $event
    "
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