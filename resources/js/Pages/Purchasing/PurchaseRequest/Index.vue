<script setup>

import { ref,reactive,computed,watch, onMounted,onUnmounted,toRefs,nextTick,} from 'vue'
import {router,useForm,} from '@inertiajs/vue3'
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
import {LoadingOverlay,} from '@/Components/Feedback'
import { PlusIcon,} from '@heroicons/vue/24/solid'
import {success,error,} from '@/Utils'
import {formatDate,} from '@/Utils'
import PurchaseRequestForm  from './Partials/PurchaseRequestForm.vue'
import PurchaseRequestViewModal from './Partials/PurchaseRequestViewModal.vue'
import PurchaseRequestApproveModal from './Partials/PurchaseRequestApproveModal.vue'
import PurchaseRequestSubmitModal from './Partials/PurchaseRequestSubmitModal.vue'
import PurchaseRequestCancelModal  from './Partials/PurchaseRequestCancelModal.vue'
import ConfirmDeleteModal  from '@/Components/Modal/ConfirmDeleteModal.vue'

/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    purchaseRequests: {

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

            submitted: 0,

            approved: 0,

            rejected: 0,

            cancelled: 0,

        }),

    },

    companyId: {

        type: [
            Number,
            String,
        ],

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

    priorities: {

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
    purchaseRequests,
    statistics,
} = toRefs(props)


/*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/

const pageTitle = computed(
    () => 'Purchase Request'
)


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

        route(
            'purchase-requests.index'
        ),

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

        clearTimeout(
            debounceTimer
        )

        debounceTimer =
            setTimeout(

                () => {

                    loadData()

                },

                500

            )

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

    Object.assign(

        filters,

        {

            search: '',

            branch_id: '',

            warehouse_id: '',

            status: '',

            per_page: 10,

        }

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
        value: 'Submitted',
        label: 'Submitted',
    },

    {
        value: 'Approved',
        label: 'Approved',
    },

    {
        value: 'Rejected',
        label: 'Rejected',
    },

    {
        value: 'Cancelled',
        label: 'Cancelled',
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
        purchaseRequests
            .value
            ?.data
            ?.length ?? 0

    return (

        totalRows > 0 &&

        selectedRows.value.length ===
            totalRows

    )

})


const isIndeterminate = computed(() => {

    const totalRows =
        purchaseRequests
            .value
            ?.data
            ?.length ?? 0

    return (

        selectedRows.value.length > 0 &&

        selectedRows.value.length <
            totalRows

    )

})


watch(

    isIndeterminate,

    (value) => {

        if (
            selectAllRef.value
        ) {

            selectAllRef.value.indeterminate =
                value

        }

    },

    {
        immediate: true,
    }

)


function toggleSelectAll(
    event
)
{

    if (
        event.target.checked
    ) {

        selectedRows.value =
            purchaseRequests
                .value
                ?.data
                ?.map(
                    item => item.id
                )
                ?? []

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

    product_variant_id:
        null,

    unit_id:
        null,

    qty:
        1,

    description:
        null,

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

    request_date:
        new Date()
            .toISOString()
            .slice(0, 10),

    required_date:
        null,

    priority:
        'Normal',

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

    form.request_date =
        new Date()
            .toISOString()
            .slice(0, 10)

    form.required_date =
        null

    form.priority =
        'Normal'

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

        form.post(

            route(
                'purchase-requests.store'
            ),

            {

                preserveScroll: true,

                onSuccess: () => {

                    success(

                        'Success',

                        'Purchase request created successfully.'

                    )

                    view.value =
                        'list'

                },

            }

        )

        return

    }


    form.put(

        route(

            'purchase-requests.update',

            editingItem.value.id

        ),

        {

            preserveScroll: true,

            onSuccess: () => {

                success(

                    'Success',

                    'Purchase request updated successfully.'

                )

                view.value =
                    'list'

            },

            onError: (errors) => {

                console.error(

                    'UPDATE ERRORS:',

                    errors

                )

                error(

                    'Failed to update purchase request.'

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
        route('purchase-requests.store'),
        {
            preserveScroll: true,

            onSuccess: () => {

                success(
                    'Success',
                    'Purchase request created successfully.'
                )

                form.reset()

                form.clearErrors()

                form.number =
                    props.previewNumber ?? ''

                form.company_id =
                    props.companyId

                form.request_date =
                    new Date()
                        .toISOString()
                        .slice(0, 10)

                form.required_date =
                    null

                form.priority =
                    'Normal'

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

function editPurchaseRequest(item)
{
    if (
        ![
            'Draft',
            'Rejected',
        ].includes(item.status)
    ) {

        error(
            'Only Draft or Rejected purchase request can be edited.'
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

    form.request_date =
        item.request_date
            ? String(
                item.request_date
            ).slice(0, 10)
            : null

    form.required_date =
        item.required_date
            ? String(
                item.required_date
            ).slice(0, 10)
            : null

    form.priority =
        item.priority ?? 'Normal'

    form.description =
        item.description

    form.details =
        item.details?.map(
            detail => ({

                product_variant_id:
                    detail.product_variant_id,

                unit_id:
                    detail.unit_id,

                qty:
                    detail.qty,

                description:
                    detail.description,

            })
        ) ?? [

            createEmptyDetail(),

        ]

    view.value = 'form'

    nextTick(() => {

        form.warehouse_id =
            item.warehouse_id

        isEditing.value = false

    })
}


/*
|--------------------------------------------------------------------------
| Show
|--------------------------------------------------------------------------
*/

const selectedItem = ref(null)

function showPurchaseRequest(item)
{
    selectedItem.value =
        item

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
            'purchase-requests.duplicate',
            item.id
        ),
        {},
        {
            preserveScroll: true,

            onSuccess: () => {

                success(
                    'Success',
                    'Purchase request duplicated successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to duplicate purchase request.'
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
        item.status !== 'Draft'
    ) {

        error(
            'Only Draft purchase request can be deleted.'
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
    deleteItem.value =
        null

    showDelete.value =
        false
}


const deleteMessage = computed(() => {

    if (
        !deleteItem.value
    ) {

        return ''

    }

    return `Are you sure you want to delete "${deleteItem.value.number}"?`

})


function confirmDelete()
{
    if (
        !deleteItem.value
    ) {

        return

    }

    router.delete(
        route(
            'purchase-requests.destroy',
            deleteItem.value.id
        ),
        {
            preserveScroll: true,

            onSuccess: () => {

                closeDelete()

                success(
                    'Success',
                    'Purchase request deleted successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to delete purchase request.'
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
    if (
        !selectedRows.value.length
    ) {

        return

    }

    showBulkDelete.value =
        true
}


const bulkDeleteMessage = computed(() => {

    const total =
        selectedRows.value.length

    if (!total) {

        return ''

    }

    return `Are you sure you want to delete ${total} selected Purchase Request document(s)?`

})


function bulkDelete()
{
    router.delete(
        route(
            'purchase-requests.bulk-delete'
        ),
        {
            data: {
                ids:
                    selectedRows.value,
            },

            preserveScroll: true,

            onSuccess: () => {

                showBulkDelete.value =
                    false

                selectedRows.value =
                    []

                success(
                    'Success',
                    'Purchase requests deleted successfully.'
                )

            },

        }
    )
}


/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/

const submitItem = ref(null)

const showSubmit = ref(false)


function openSubmit(
    purchaseRequest
)
{
    console.log(
        'PURCHASE REQUEST SUBMIT ITEM:',
        JSON.stringify(
            purchaseRequest,
            null,
            2
        )
    )

    console.log(
        'PURCHASE REQUEST DETAILS:',
        JSON.stringify(
            purchaseRequest?.details,
            null,
            2
        )
    )

    submitItem.value =
        purchaseRequest

    showSubmit.value =
        true
}

function closeSubmit()
{
    submitItem.value =
        null

    showSubmit.value =
        false
}


function confirmSubmit()
{
    if (
        !submitItem.value
    ) {

        return

    }

    router.post(
        route(
            'purchase-requests.submit',
            submitItem.value.id
        ),
        {},
        {
            preserveScroll: true,

            onSuccess: () => {

                closeSubmit()

                success(
                    'Success',
                    'Purchase request submitted successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to submit purchase request.'
                )

            },

        }
    )
}


const submitMessage = computed(() => {

    if (
        !submitItem.value
    ) {

        return ''

    }

    return `Are you sure you want to submit "${submitItem.value.number}"?`

})


/*
|--------------------------------------------------------------------------
| Approve
|--------------------------------------------------------------------------
*/

const approveItem = ref(null)

const showApprove = ref(false)


function openApprove(
    purchaseRequest
)
{
    approveItem.value =
        purchaseRequest

    showApprove.value =
        true
}


function closeApprove()
{
    approveItem.value =
        null

    showApprove.value =
        false
}


function confirmApprove()
{
    if (
        !approveItem.value
    ) {

        return

    }

    router.post(
        route(
            'purchase-requests.approve',
            approveItem.value.id
        ),
        {},
        {
            preserveScroll: true,

            onSuccess: () => {

                closeApprove()

                success(
                    'Success',
                    'Purchase request approved successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to approve purchase request.'
                )

            },

        }
    )
}


/*
|--------------------------------------------------------------------------
| Reject
|--------------------------------------------------------------------------
*/

const rejectItem = ref(null)

const showReject = ref(false)

const rejectReason = ref('')


function openReject(
    purchaseRequest
)
{
    rejectItem.value =
        purchaseRequest

    rejectReason.value =
        ''

    showReject.value =
        true
}


function closeReject()
{
    rejectItem.value =
        null

    rejectReason.value =
        ''

    showReject.value =
        false
}


function confirmReject()
{
    if (
        !rejectItem.value
    ) {

        return

    }

    if (
        !rejectReason.value.trim()
    ) {

        error(
            'Rejection reason is required.'
        )

        return

    }

    router.post(
        route(
            'purchase-requests.reject',
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
                    'Purchase request rejected successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to reject purchase request.'
                )

            },

        }
    )
}


/*
|--------------------------------------------------------------------------
| Cancel
|--------------------------------------------------------------------------
*/

const cancelItem = ref(null)

const showCancel = ref(false)
const submitLoading = ref(false)
const cancelReason = ref('')
function openCancel(
    purchaseRequest
)
{
    cancelItem.value =
        purchaseRequest

    showCancel.value =
        true
}


function closeCancel()
{
    cancelItem.value =
        null

    showCancel.value =
        false
}


function confirmCancel()
{
    if (
        !cancelItem.value
    ) {

        return

    }

    router.post(
        route(
            'purchase-requests.cancel',
            cancelItem.value.id
        ),
        {},
        {
            preserveScroll: true,

            onSuccess: () => {

                closeCancel()

                success(
                    'Success',
                    'Purchase request cancelled successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to cancel purchase request.'
                )

            },

        }
    )
}


const cancelMessage = computed(() => {

    if (
        !cancelItem.value
    ) {

        return ''

    }

    return `Are you sure you want to cancel "${cancelItem.value.number}"?`

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

    if (
        sort.value === column
    ) {

        direction.value =
            direction.value === 'asc'
                ? 'desc'
                : 'asc'

    } else {

        sort.value =
            column

        direction.value =
            'asc'

    }

    router.get(

        route(
            'purchase-requests.index'
        ),

        {

            search:
                filters.search,

            branch_id:
                filters.branch_id,

            warehouse_id:
                filters.warehouse_id,

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

            preserveState:
                true,

            preserveScroll:
                true,

            replace:
                true,

        }

    )

}


/*
|--------------------------------------------------------------------------
| Filtered Warehouse
|--------------------------------------------------------------------------
*/

const isEditing =
    ref(false)


const filteredWarehouses =
    computed(() => {

        if (
            !form.branch_id
        ) {

            return []

        }

        return props.warehouses.filter(

            warehouse =>

                Number(
                    warehouse.branch_id
                ) ===

                Number(
                    form.branch_id
                )

        )

    })


watch(

    () => form.branch_id,

    (
        newBranch,
        oldBranch
    ) => {

        if (

            oldBranch === undefined ||

            newBranch === oldBranch

        ) {

            return

        }

        form.warehouse_id =
            null

    }

)


/*
|--------------------------------------------------------------------------
| Action Permissions
|--------------------------------------------------------------------------
*/

function canEdit(item)
{

    return [

        'Draft',

        'Rejected',

    ].includes(
        item.status
    )

}


function canSubmit(item)
{

    return item.status ===
        'Draft'

}


function canApprove(item)
{

    return item.status ===
        'Submitted'

}


function canReject(item)
{

    return item.status ===
        'Submitted'

}


function canCancel(item)
{

    return [

       
        'Submitted',

        'Approved',

    ].includes(
        item.status
    )

}


function canDelete(item)
{

    return item.status ===
        'Draft'

}


/*
|--------------------------------------------------------------------------
| View Modal
|--------------------------------------------------------------------------
*/

const viewLoading =
    ref(false)

const showView =
    ref(false)

const viewItem =
    ref(null)


async function openView(item)
{

    viewLoading.value =
        true

    viewItem.value =
        null

    showView.value =
        true

    try {

        const response =
            await fetch(

                route(
                    'purchase-requests.data',
                    item.id
                ),

                {

                    headers: {

                        Accept:
                            'application/json',

                        'X-Requested-With':
                            'XMLHttpRequest',

                    },

                }

            )


        if (
            !response.ok
        ) {

            throw new Error(

                `HTTP ${response.status}`

            )

        }


        const responseData =
            await response.json()


        console.log(

            'PURCHASE REQUEST VIEW RESPONSE:',

            responseData

        )


        viewItem.value =
            responseData.data


    } catch (
        exception
    ) {

        console.error(

            'PURCHASE REQUEST VIEW ERROR:',

            exception

        )


        showView.value =
            false

        viewItem.value =
            null


        error(

            'Failed to load purchase request detail.'

        )


    } finally {

        viewLoading.value =
            false

    }

}


function closeView()
{

    viewItem.value =
        null

    showView.value =
        false

}


/*
|--------------------------------------------------------------------------
| Export
|--------------------------------------------------------------------------
*/

function exportSelected()
{

    console.log(

        'EXPORT SELECTED:',

        selectedRows.value

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

            <!-- <PageHeader
                icon="📦"
                title="Purchae Request"
                subtitle="Manage Purchase Request transactions."
            />===================================================== -->

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
                    title="Total Purchase Request"
                    :value="statistics?.total ?? 0"
                    icon="📋"
                />

                <StatsCard
                    title="Draft"
                    :value="statistics?.draft ?? 0"
                    icon="📝"
                />

                <StatsCard
                    title="Submitted"
                    :value="statistics?.submitted ?? 0"
                    icon="📤"
                />

                <StatsCard
                    title="Approved"
                    :value="statistics?.approved ?? 0"
                    icon="✅"
                />

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
                    text="Loading Purchase Request..."
                />


                <!-- Data -->

                <DataTable
                    v-if="purchaseRequests?.data?.length"
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


                        <!-- Request Date -->

                        <DataTableHeaderCell
                            sortable
                            column="request_date"
                            :sort="sort"
                            :direction="direction"
                            @sort="sortBy"
                            width="160px"
                        >
                            Request Date
                        </DataTableHeaderCell>


                        <!-- Required Date -->

                        <DataTableHeaderCell
                            sortable
                            column="required_date"
                            :sort="sort"
                            :direction="direction"
                            @sort="sortBy"
                            width="160px"
                        >
                            Required Date
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
                        v-for="item in purchaseRequests.data"
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


                        <!-- Request Date -->

                        <DataTableCell>

                            {{ formatDate(item.request_date) }}

                        </DataTableCell>


                        <!-- Required Date -->

                        <DataTableCell>

                            {{
                                item.required_date
                                    ? formatDate(item.required_date)
                                    : '-'
                            }}

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

                            {{
                                item.details_count
                                ?? item.details?.length
                                ?? 0
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

                                @view="openView(item)"
                                @edit="editPurchaseRequest(item)"
                                @duplicate="duplicate(item)"
                                @submit="openSubmit(item)"
                                @approve="openApprove(item)"
                                @reject="openReject(item)"
                                @cancel="openCancel(item)"
                                @delete="openDelete(item)"
                                :showEdit="canEdit(item)"
                                :showDuplicate="true"
                                :showSubmit="canSubmit(item)"
                                :showApprove="canApprove(item)"
                                :showReject="canReject(item)"
                                :showCancel="canCancel(item)"
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
                    title="No Purchase Request Found"
                    description="There are no Purchase Request transactions available."
                >

                    <template #action>

                        <BaseButton
                            @click="create"
                        >
                            Create Purchase Request
                        </BaseButton>

                    </template>

                </TableEmpty>

            </div>


            <!-- ===================================================== -->
            <!-- Pagination -->
            <!-- ===================================================== -->

            <div class="mt-6">

                <TablePagination
                    :data="purchaseRequests"
                    label="Purchase Request"
                />

            </div>

        </Card>
            
        </div>
    </div>
        <!-- end part b-->

       <!-- FORM -->

        <div
            v-else-if="view === 'form'"
            key="form"
            class="space-y-6"
        >

            <PageHeader
                icon="📋"
                :title="
                    formMode === 'create'
                        ? 'Create Purchase Request'
                        : 'Edit Purchase Request'
                "
                :subtitle="
                    formMode === 'create'
                        ? 'Create a new purchase request.'
                        : 'Update purchase request.'
                "
            />


            <Card>

                <PurchaseRequestForm
                    :form="form"
                    :branches="branches"
                    :filtered-warehouses="filteredWarehouses"
                    :filtered-variants="filteredVariants"
                    :filtered-units="filteredUnits"
                    :priorities="priorities"
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
    title="Delete Purchase Request"
    :message="deleteMessage"
    confirm-text="Delete"
    @close="closeDelete"
    @confirm="confirmDelete"
/>
<PurchaseRequestViewModal
    :show="showView"
    :purchase-request="viewItem"
    :loading="viewLoading"
    @close="closeView"
/>
<PurchaseRequestSubmitModal
    :show="showSubmit"
    :purchase-request="submitItem"
    :loading="submitLoading"
    @close="closeSubmit"
    @confirm="confirmSubmit"
/>
<PurchaseRequestApproveModal
    :show="showApprove"
    :purchase-request="approveItem"
    :loading="false"
    @close="closeApprove"
    @confirm="confirmApprove"
/>

<PurchaseRequestCancelModal
    :show="showCancel"
    :purchase-request="cancelItem"
    :reason="cancelReason"
    :loading="false"
    @close="closeCancel"
    @update:reason="cancelReason = $event"
    @confirm="confirmCancel"
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