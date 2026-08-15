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
import InventoryAdjustmentForm    from './Partials/InventoryAdjustmentForm.vue'
import InventoryAdjustmentViewModal    from './Partials/InventoryAdjustmentViewModal.vue'
import ConfirmDeleteModal from '@/Components/Modal/ConfirmDeleteModal.vue'
import { formatCurrency } from '@/Utils/currency'
/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    adjustments: {
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
    adjustments,
    statistics,
} = toRefs(props)


/*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/

const pageTitle = computed(() => 'Adsjustment Stock')


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

    status:
        props.filters?.status ?? '',

    per_page:
        props.filters?.per_page ?? 10,

})


let debounceTimer = null


function loadData()
{
    router.get(
        route('inventory-adjustments.index'),
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
    () => filters.status,
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
        status: '',
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
        adjustments.value?.data?.length ?? 0

    return (
        totalRows > 0 &&
        selectedRows.value.length === totalRows
    )

})


const isIndeterminate = computed(() => {

    const totalRows =
        adjustments.value?.data?.length ?? 0

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
            adjustments.value?.data?.map(
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

    system_qty: 0,
    actual_qty: 0,
    difference_qty: 0,

    unit_cost: 0,
    total_cost: 0,

    description: null,
})


const form = useForm({

    number:
        props.previewNumber ?? '',

    company_id:
        null,

    branch_id:
        null,

    warehouse_id:
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
            'SUBMIT INVENTORY ADJUSTMENT:',
            JSON.parse(JSON.stringify(form.data()))
        )

        form.post(
            route('inventory-adjustments.store'),
            {
                preserveScroll: true,

                onSuccess: () => {

                    console.log(
                        'CREATE SUCCESS'
                    )

                    success(
                        'Success',
                        'Inventory Adjustment created successfully.'
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
            'inventory-adjustments.update',
            editingItem.value.id
        ),
        {
            preserveScroll: true,

            onSuccess: () => {

                success(
                    'Success',
                    'Inventory Adjustment updated successfully.'
                )

                view.value = 'list'

            },

            onError: (errors) => {

                console.error(
                    'UPDATE ERRORS:',
                    errors
                )

                error(
                    'Failed to update Inventory Adjustment.'
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
        route('inventory-adjustments.store'),
        {
            preserveScroll: true,

            onSuccess: () => {

                success(
                    'Success',
                    'Inventory Adjustments created successfully.'
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

function editAdjustments(item)
{
    if (item.status === 'Posted') {

        error(
            'Inventory Adjustment that has been posted cannot be edited.'
        )

        return
    }

    isEditing.value = true

    editingItem.value = item

    formMode.value = 'edit'

    form.clearErrors()

    form.number =
        item.number

    form.company_id =
        item.company_id

    form.branch_id =
        item.branch_id

    form.transaction_date =
        item.transaction_date
            ? String(item.transaction_date).slice(0, 10)
            : null

    form.description =
        item.description

    form.details =
        item.details?.map(detail => ({

            product_variant_id:
                detail.product_variant_id,

            unit_id:
                detail.unit_id,

            /*
            |--------------------------------------------------------------------------
            | Existing Adjustment Snapshot
            |--------------------------------------------------------------------------
            */

            system_qty:
                detail.system_qty,

            actual_qty:
                detail.actual_qty,

            difference_qty:
                detail.difference_qty,

            unit_cost:
                detail.unit_cost,

            total_cost:
                detail.total_cost,

            description:
                detail.description,

        })) ?? [
            createEmptyDetail(),
        ]

    view.value = 'form'

    nextTick(() => {

        form.warehouse_id =
            item.warehouse_id

    })
}
/*
|--------------------------------------------------------------------------
| Show
|--------------------------------------------------------------------------
*/

const selectedItem = ref(null)

function showaAdjustment(item)
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
            'inventory-adjustments.duplicate',
            item.id
        ),
        {},
        {
            preserveScroll: true,

            onSuccess: () => {

                success(
                    'Success',
                    'Inventory Adjustments duplicated successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to duplicate Inventory adjustments.'
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
            'Only Draft or Rejected inventory adjustment can be deleted.'
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
            'inventory-adjustments.destroy',
            deleteItem.value.id
        ),
        {
            preserveScroll: true,

            onSuccess: () => {

                closeDelete()

                success(
                    'Success',
                    'Inventory Adjustments deleted successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to delete Inventory adjustment.'
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

    return `Are you sure you want to delete ${total} selected Inventory Adjustment document(s)?`

})


function bulkDelete()
{
    router.delete(
        route('inventory-adjustments.bulk-delete'),
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
                    'Inventory Adjustment deleted successfully.'
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

function openPost(adjustment)
{
    postItem.value = adjustment

    showPost.value = true
}
function closePost()
{
    postItem.value = null

    showPost.value = false
}
function openReject(adjustment)
{
    rejectItem.value = adjustment

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
            'inventory-adjustments.post',
            postItem.value.id
        ),

        {},

        {

            preserveScroll: true,

            onSuccess: () => {

                closePost()

                success(
                    'Success',
                    'Inventory Adjustments posted successfully.'
                )

            },

            onError: (errors) => {

                   error(
                    'Failed to post Inventory Adjustment.'
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
            'inventory-adjustments.cancel',
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
                    'Inventory Adjustment rejected successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to reject Inventory Adjustment.'
                )

            },

        }

    )
}

const postMessage = computed(() => {

    if (!postItem.value) {
        return ''
    }

    return `Are you sure you want to post "${postItem.value.number}"? Once posted, Ajustment inventory will be updated.`
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
        route('inventory-adjustments.index'),
        {
            search: filters.search,
            branch_id: filters.branch_id,
            warehouse_id: filters.warehouse_id,
            status: filters.status,
            per_page: filters.per_page,
            sort_by: sort.value,
            sort_direction: direction.value,
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
                'inventory-adjustments.data',
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
            'adjustments STOCK VIEW RESPONSE:',
            responseData
        )

        viewItem.value =
            responseData.data

    } catch (exception) {

        console.error(
            'adjustments STOCK VIEW ERROR:',
            exception
        )

        showView.value = false

        viewItem.value = null

        error(
            'Failed to load Adjustment stock detail.'
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
                title="Inventory Adjustment"
                subtitle="Manage Inventory adjustment transactions."
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
                    title="Total Adjustments Stock"
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
                        v-model="filters.branch_id"
                        :options="branches"
                        label="label"
                        value-key="id"
                        placeholder="All Branches"
                    />


                    <!-- Warehouse -->

                    <SearchableSelect
                        v-model="filters.warehouse_id"
                        :options="warehouses"
                        label="label"
                        value-key="id"
                        placeholder="All Warehouses"
                    />


                    <!-- Status -->

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
                    text="Loading Adjustment Stock..."
                />


                <!-- Data -->

                <DataTable
                    v-if="adjustments?.data?.length"
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
                            Warehouse
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
                            v-for="item in adjustments.data"
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


                            <!-- Branch -->

                            <DataTableCell>

                                {{ item.branch?.name ?? '-' }}

                            </DataTableCell>


                            <!-- Warehouse -->

                            <DataTableCell>

                                {{ item.warehouse?.name ?? '-' }}

                            </DataTableCell>


                            <!-- Items -->

                            <DataTableCell align="right">

                                {{ item.details_count ?? item.details?.length ?? 0 }}

                            </DataTableCell>


                            <!-- Total Cost -->

                            <DataTableCell align="right">

                                {{ currency(item.total_cost ?? 0) }}

                                
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
                                    @edit="editAdjustments(item)"
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
                    icon="📦"
                    title="No Inventory Adjustment Found"
                    description="There are no Inventory adjustment transactions available."
                >

                    <template #action>

                        <BaseButton
                            @click="create"
                        >
                            Create Adjustments Stock
                        </BaseButton>

                    </template>

                </TableEmpty>

            </div>


            <!-- ===================================================== -->
            <!-- Pagination -->
            <!-- ===================================================== -->

            <div class="mt-6">

                <TablePagination
                    :data="adjustments"
                    label="Adjustments Stock"
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
                        ? 'Create Inventory Adjustment'
                        : 'Edit Inventory Adjustment'
                "
                :subtitle="
                    formMode === 'create'
                        ? 'Create a new Inventory Adjustment transaction.'
                        : 'Update Inventory Adjustment transaction.'
                "
            />


            <Card>

                <InventoryAdjustmentForm
                    :form="form"
                    :branches="branches"
                    :filtered-warehouses="filteredWarehouses"
                    :filtered-variants="filteredVariants"
                    :filtered-units="filteredUnits"
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
    title="Delete Inventory Adjustment"
    :message="deleteMessage"
    confirm-text="Delete"
    @close="closeDelete"
    @confirm="confirmDelete"
/>
<InventoryAdjustmentViewModal
    :show="showView"
    :adjustment="viewItem"
    :loading="viewLoading"
    @close="closeView"
/>
<!-- ========================================================= -->
<!-- Post Inventory Adjustment Review -->
<!-- ========================================================= -->

<Teleport to="body">

    <div
        v-if="showPost"
        class="
            fixed
            inset-0
            z-[100]
            flex
            items-center
            justify-center
            bg-black/40
            px-4
            py-6
        "
        @click.self="closePost"
    >

        <div
            class="
                flex
                max-h-[90vh]
                w-full
                max-w-5xl
                flex-col
                overflow-hidden
                rounded-2xl
                bg-white
                shadow-2xl
            "
        >

            <!-- ================================================= -->
            <!-- Header -->
            <!-- ================================================= -->

            <div
                class="
                    flex
                    items-center
                    justify-between
                    border-b
                    border-gray-200
                    px-6
                    py-5
                "
            >

                <div>

                    <h2
                        class="
                            text-xl
                            font-semibold
                            text-gray-900
                        "
                    >
                        Review Inventory Adjustment
                    </h2>

                    <p
                        class="
                            mt-1
                            text-sm
                            text-gray-500
                        "
                    >
                        Review the transaction before posting it
                        to inventory.
                    </p>

                </div>


                <button
                    type="button"
                    class="
                        rounded-lg
                        p-2
                        text-gray-400
                        transition
                        hover:bg-gray-100
                        hover:text-gray-700
                    "
                    @click="closePost"
                >
                    ✕
                </button>

            </div>


            <!-- ================================================= -->
            <!-- Body -->
            <!-- ================================================= -->

            <div
                class="
                    flex-1
                    overflow-y-auto
                    p-6
                "
            >

                <template v-if="postItem">

                    <!-- ========================================= -->
                    <!-- Header Information -->
                    <!-- ========================================= -->

                    <div
                        class="
                            grid
                            grid-cols-1
                            gap-4
                            rounded-xl
                            border
                            border-gray-200
                            bg-gray-50
                            p-5
                            md:grid-cols-2
                            lg:grid-cols-4
                        "
                    >

                        <div>

                            <div
                                class="
                                    text-xs
                                    font-medium
                                    uppercase
                                    tracking-wide
                                    text-gray-500
                                "
                            >
                                Number
                            </div>

                            <div
                                class="
                                    mt-1
                                    font-semibold
                                    text-gray-900
                                "
                            >
                                {{ postItem.number }}
                            </div>

                        </div>


                        <div>

                            <div
                                class="
                                    text-xs
                                    font-medium
                                    uppercase
                                    tracking-wide
                                    text-gray-500
                                "
                            >
                                Transaction Date
                            </div>

                            <div
                                class="
                                    mt-1
                                    font-semibold
                                    text-gray-900
                                "
                            >
                            {{ formatDate(postItem.transaction_date) }}
                                
                            </div>

                        </div>


                        <div>

                            <div
                                class="
                                    text-xs
                                    font-medium
                                    uppercase
                                    tracking-wide
                                    text-gray-500
                                "
                            >
                                Branch
                            </div>

                            <div
                                class="
                                    mt-1
                                    font-semibold
                                    text-gray-900
                                "
                            >
                                {{ postItem.branch?.name ?? '-' }}
                            </div>

                        </div>


                        <div>

                            <div
                                class="
                                    text-xs
                                    font-medium
                                    uppercase
                                    tracking-wide
                                    text-gray-500
                                "
                            >
                                Warehouse
                            </div>

                            <div
                                class="
                                    mt-1
                                    font-semibold
                                    text-gray-900
                                "
                            >
                                {{ postItem.warehouse?.name ?? '-' }}
                            </div>

                        </div>

                    </div>


                    <!-- ========================================= -->
                    <!-- Details -->
                    <!-- ========================================= -->

                    <div class="mt-6">

                        <div
                            class="
                                mb-3
                                text-base
                                font-semibold
                                text-gray-900
                            "
                        >
                            Inventory Adjustment Details
                        </div>


                        <div
                            class="
                                overflow-x-auto
                                rounded-xl
                                border
                                border-gray-200
                            "
                        >

                            <table
                                class="
                                    min-w-full
                                    divide-y
                                    divide-gray-200
                                "
                            >

                                <thead
                                    class="bg-gray-50"
                                >

                                    <tr>

                                        <th
                                            class="
                                                px-4
                                                py-3
                                                text-left
                                                text-xs
                                                font-semibold
                                                uppercase
                                                tracking-wide
                                                text-gray-500
                                            "
                                        >
                                            Product Variant
                                        </th>

                                        <th
                                            class="
                                                px-4
                                                py-3
                                                text-left
                                                text-xs
                                                font-semibold
                                                uppercase
                                                tracking-wide
                                                text-gray-500
                                            "
                                        >
                                            Unit
                                        </th>

                                        <th
                                            class="
                                                px-4
                                                py-3
                                                text-right
                                                text-xs
                                                font-semibold
                                                uppercase
                                                tracking-wide
                                                text-gray-500
                                            "
                                        >
                                            System Qty
                                        </th>

                                        <th
                                            class="
                                                px-4
                                                py-3
                                                text-right
                                                text-xs
                                                font-semibold
                                                uppercase
                                                tracking-wide
                                                text-gray-500
                                            "
                                        >
                                            Actual Qty
                                        </th>

                                        <th
                                            class="
                                                px-4
                                                py-3
                                                text-right
                                                text-xs
                                                font-semibold
                                                uppercase
                                                tracking-wide
                                                text-gray-500
                                            "
                                        >
                                            Difference
                                        </th>

                                        <th
                                            class="
                                                px-4
                                                py-3
                                                text-right
                                                text-xs
                                                font-semibold
                                                uppercase
                                                tracking-wide
                                                text-gray-500
                                            "
                                        >
                                            Unit Cost
                                        </th>

                                        <th
                                            class="
                                                px-4
                                                py-3
                                                text-right
                                                text-xs
                                                font-semibold
                                                uppercase
                                                tracking-wide
                                                text-gray-500
                                            "
                                        >
                                            Total Cost
                                        </th>

                                    </tr>

                                </thead>


                                <tbody
                                    class="
                                        divide-y
                                        divide-gray-100
                                        bg-white
                                    "
                                >

                                    <tr
                                        v-for="
                                            (detail, index)
                                            in postItem.details
                                        "
                                        :key="detail.id ?? index"
                                    >

                                        <td
                                            class="
                                                whitespace-nowrap
                                                px-4
                                                py-3
                                            "
                                        >

                                            <div
                                                class="
                                                    font-medium
                                                    text-gray-900
                                                "
                                            >
                                                {{
                                                    detail.variant?.sku
                                                    ?? '-'
                                                }}
                                            </div>

                                            <div
                                                class="
                                                    text-xs
                                                    text-gray-500
                                                "
                                            >
                                                {{
                                                    detail.variant?.product?.name
                                                    ?? detail.variant?.name
                                                    ?? '-'
                                                }}
                                            </div>

                                        </td>


                                        <td
                                            class="
                                                whitespace-nowrap
                                                px-4
                                                py-3
                                                text-sm
                                                text-gray-700
                                            "
                                        >
                                            {{
                                                detail.unit?.name
                                                ?? '-'
                                            }}
                                        </td>


                                        <td
                                            class="
                                                whitespace-nowrap
                                                px-4
                                                py-3
                                                text-right
                                                text-sm
                                                text-gray-700
                                            "
                                        >
                                            {{ formatNumber(detail.system_qty) }}
                                        </td>

                                        <td
                                            class="
                                                whitespace-nowrap
                                                px-4
                                                py-3
                                                text-right
                                                text-sm
                                                font-medium
                                                text-gray-900
                                            "
                                        >
                                            {{ formatNumber(detail.actual_qty) }}
                                        </td>

                                        <td
                                            class="
                                                whitespace-nowrap
                                                px-4
                                                py-3
                                                text-right
                                                text-sm
                                                font-semibold
                                                text-gray-900
                                            "
                                        >
                                            {{ formatNumber(detail.difference_qty) }}
                                        </td>


                                        <td
                                            class="
                                                whitespace-nowrap
                                                px-4
                                                py-3
                                                text-right
                                                text-sm
                                                text-gray-700
                                            "
                                        >
                                            {{
                                                formatCurrency(
                                                    detail.unit_cost
                                                )
                                            }}
                                        </td>


                                        <td
                                            class="
                                                whitespace-nowrap
                                                px-4
                                                py-3
                                                text-right
                                                text-sm
                                                font-semibold
                                                text-gray-900
                                            "
                                        >
                                            {{
                                                formatCurrency(
                                                    detail.total_cost
                                                )
                                            }}
                                        </td>

                                    </tr>

                                </tbody>

                            </table>

                        </div>

                    </div>


                    <!-- ========================================= -->
                    <!-- Summary -->
                    <!-- ========================================= -->

                    <div
                        class="
                            mt-6
                            flex
                            justify-end
                        "
                    >

                        <div
                            class="
                                w-full
                                max-w-md
                                rounded-xl
                                border
                                border-gray-200
                                bg-gray-50
                                p-5
                            "
                        >

                            <div
                                class="
                                    flex
                                    justify-between
                                    py-2
                                    text-sm
                                "
                            >

                                <span class="text-gray-600">
                                    Total Items
                                </span>

                                <span class="font-semibold">
                                    {{
                                        postItem.details?.length
                                        ?? 0
                                    }}
                                </span>

                            </div>


                            <div
                                class="
                                    flex
                                    justify-between
                                    py-2
                                    text-sm
                                "
                            >

                                <div
                                        class="
                                            flex
                                            justify-between
                                            py-2
                                            text-sm
                                        "
                                    >
                                        <span class="text-gray-600">
                                            Total Increase
                                        </span>

                                        <span class="font-semibold">
                                            {{
                                                formatNumber(
                                                    postItem.details?.reduce(
                                                        (total, detail) =>
                                                            total +
                                                            Math.max(
                                                                Number(
                                                                    detail.difference_qty ?? 0
                                                                ),
                                                                0
                                                            ),
                                                        0
                                                    )
                                                )
                                            }}
                                        </span>
                                    </div>


                                    <div
                                        class="
                                            flex
                                            justify-between
                                            py-2
                                            text-sm
                                        "
                                    >
                                        <span class="text-gray-600">
                                            Total Decrease
                                        </span>

                                        <span class="font-semibold">
                                            {{
                                                formatNumber(
                                                    postItem.details?.reduce(
                                                        (total, detail) =>
                                                            total +
                                                            Math.abs(
                                                                Math.min(
                                                                    Number(
                                                                        detail.difference_qty ?? 0
                                                                    ),
                                                                    0
                                                                )
                                                            ),
                                                        0
                                                    )
                                                )
                                            }}
                                        </span>
                                    </div>

                            </div>


                            <div
                                class="
                                    mt-2
                                    flex
                                    justify-between
                                    border-t
                                    border-gray-200
                                    pt-3
                                "
                            >

                                <span
                                    class="
                                        font-semibold
                                        text-gray-900
                                    "
                                >
                                    Total Cost
                                </span>

                                <span
                                    class="
                                        text-lg
                                        font-bold
                                        text-gray-900
                                    "
                                >
                                    {{
                                        formatCurrency(
                                            postItem.details?.reduce(
                                                (
                                                    total,
                                                    detail
                                                ) =>
                                                    total +
                                                    Number(
                                                        detail.total_cost
                                                        ?? 0
                                                    ),
                                                0
                                            )
                                        )
                                    }}
                                </span>

                            </div>

                        </div>

                    </div>


                    <!-- ========================================= -->
                    <!-- Warning -->
                    <!-- ========================================= -->

                    <div
                        class="
                            mt-6
                            rounded-xl
                            border
                            border-amber-200
                            bg-amber-50
                            p-4
                        "
                    >

                        <div
                            class="
                                text-sm
                                font-semibold
                                text-amber-800
                            "
                        >
                            Before posting
                        </div>

                        <p
                            class="
                                mt-1
                                text-sm
                                text-amber-700
                            "
                        >
                           Please make sure the system quantity,
                            actual quantity, and adjustment cost
                            are correct. Posting this transaction
                            will update the inventory stock and
                            create an inventory movement.
                        </p>

                    </div>

                </template>

            </div>


            <!-- ================================================= -->
            <!-- Footer -->
            <!-- ================================================= -->

            <div
                class="
                    flex
                    items-center
                    justify-end
                    gap-3
                    border-t
                    border-gray-200
                    bg-gray-50
                    px-6
                    py-4
                "
            >

                <BaseButton
                    type="button"
                    variant="secondary"
                    @click="closePost"
                >
                    Cancel
                </BaseButton>


                <BaseButton
                    type="button"
                    variant="success"
                    :loading="false"
                    @click="confirmPost"
                >
                    Post Inventory Adjustment
                </BaseButton>

            </div>

        </div>

    </div>

</Teleport>
 <!-- confirm delete-->
    <Teleport to="body">

        <div
            v-if="showReject"
            class="
                fixed
                inset-0
                z-[100]
                flex
                items-center
                justify-center
                bg-black/40
                px-4
            "
        >

            <div
                class="
                    w-full
                    max-w-lg
                    rounded-2xl
                    bg-white
                    p-6
                    shadow-xl
                "
            >

                <h2
                    class="
                        text-lg
                        font-semibold
                        text-gray-900
                    "
                >
                    Reject Inventory Adjustment
                </h2>

                <p
                    class="
                        mt-2
                        text-sm
                        text-gray-500
                    "
                >
                    {{
                        rejectItem
                            ? `Reject "${rejectItem.number}".`
                            : ''
                    }}
                </p>


                <div class="mt-5">

                    <label
                        class="
                            mb-2
                            block
                            text-sm
                            font-medium
                            text-gray-700
                        "
                    >
                        Rejection Reason
                        <span class="text-red-500">*</span>
                    </label>

                    <textarea
                        v-model="rejectReason"
                        rows="4"
                        class="
                            w-full
                            rounded-xl
                            border
                            border-gray-300
                            px-4
                            py-3
                            text-sm
                            outline-none
                            focus:border-red-400
                            focus:ring-2
                            focus:ring-red-100
                        "
                        placeholder="Enter rejection reason..."
                    ></textarea>

                </div>


                <div
                    class="
                        mt-6
                        flex
                        justify-end
                        gap-3
                    "
                >

                    <BaseButton
                        type="button"
                        variant="secondary"
                        @click="closeReject"
                    >
                        Cancel
                    </BaseButton>

                    <BaseButton
                        type="button"
                        variant="danger"
                        @click="confirmReject"
                    >
                        Reject
                    </BaseButton>

                </div>

            </div>

        </div>

    </Teleport>
    <!-- end confirm delete-->
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