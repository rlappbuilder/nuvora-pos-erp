<script setup>

import {ref,reactive,computed,watch,onMounted,onUnmounted,toRefs,} from 'vue'
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
import { LoadingOverlay } from '@/Components/Feedback'
import { PlusIcon,} from '@heroicons/vue/24/solid'
import { success,error,formatDate,} from '@/Utils'
import FlatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'
import PurchaseReturnForm from './Partials/PurchaseReturnForm.vue'
import PurchaseReturnViewModal from './Partials/PurchaseReturnViewModal.vue'
import PurchaseReturnSubmitModal from './Partials/PurchaseReturnSubmitModal.vue'
import PurchaseReturnApproveModal from './Partials/PurchaseReturnApproveModal.vue'
import PurchaseReturnRejectModal from './Partials/PurchaseReturnRejectModal.vue'
import PurchaseReturnPostModal from './Partials/PurchaseReturnPostModal.vue'
import PurchaseReturnCancelModal from './Partials/PurchaseReturnCancelModal.vue'
import ConfirmDeleteModal from '@/Components/Modal/ConfirmDeleteModal.vue'


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    purchaseReturns: {

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

            posted: 0,

            cancelled: 0,

        }),

    },

    warehouses: {

        type: Array,

        default: () => [],

    },

    suppliers: {

        type: Array,

        default: () => [],

    },

    goodsReceipts: {

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
    purchaseReturns,
    statistics,
} = toRefs(props)


/*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/

const pageTitle = computed(
    () => 'Purchase Return'
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

    supplier_id:
        props.filters?.supplier_id ?? '',

    warehouse_id:
        props.filters?.warehouse_id ?? '',

    status:
        props.filters?.status ?? '',

    per_page:
        props.filters?.per_page ?? 10,

    date_from:
        props.filters?.date_from ?? '',

    date_to:
        props.filters?.date_to ?? '',

    date_range:
        props.filters?.date_from
        && props.filters?.date_to

            ? `${props.filters.date_from} to ${props.filters.date_to}`

            : props.filters?.date_from ?? '',

})


let debounceTimer = null


function loadData()
{

    let dateFrom = ''
    let dateTo = ''


    if (
        filters.date_range
    ) {

        const dates =
            filters.date_range.split(
                ' to '
            )


        dateFrom =
            dates[0] ?? ''


        dateTo =
            dates[1]
            ?? dates[0]
            ?? ''

    }


    router.get(

        route(
            'purchase-returns.index'
        ),

        {

            ...filters,

            date_from:
                dateFrom,

            date_to:
                dateTo,

        },

        {

            preserveState: true,

            preserveScroll: true,

            replace: true,

        }

    )

}


/*
|--------------------------------------------------------------------------
| Search Watch
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| Filter Watch
|--------------------------------------------------------------------------
*/

watch(

    () => filters.supplier_id,

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


watch(

    () => filters.date_range,

    () => {

        loadData()

    }

)


/*
|--------------------------------------------------------------------------
| Refresh
|--------------------------------------------------------------------------
*/

function refresh()
{

    Object.assign(

        filters,

        {

            search: '',

            supplier_id: '',

            warehouse_id: '',

            status: '',

            per_page: 10,

            date_range: '',

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
        value: 'Rejected',
        label: 'Rejected',
    },

    {
        value: 'Approved',
        label: 'Approved',
    },

    {
        value: 'Posted',
        label: 'Posted',
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
        purchaseReturns
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
        purchaseReturns
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
            purchaseReturns
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

    goods_receipt_detail_id:
        null,

    purchase_order_detail_id:
        null,

    product_variant_id:
        null,

    unit_id:
        null,

    returned_qty:
        0,

    unit_cost:
        0,

    total_cost:
        0,

    remarks:
        null,

})


const form = useForm({

    return_number:
        props.previewNumber ?? '',

    goods_receipt_id:
        null,

    purchase_order_id:
        null,

    supplier_id:
        null,

    warehouse_id:
        null,

    return_date:
        new Date()
            .toISOString()
            .slice(0, 10),

    status:
        'Draft',

    remarks:
        null,

    details: [

        createEmptyDetail(),

    ],

})


/*
|--------------------------------------------------------------------------
| Create
|--------------------------------------------------------------------------
*/

function create()
{

    formMode.value =
        'create'


    editingItem.value =
        null


    form.reset()

    form.clearErrors()


    form.return_number =
        props.previewNumber ?? ''


    form.return_date =
        new Date()
            .toISOString()
            .slice(0, 10)


    form.status =
        'Draft'


    form.details = [

        createEmptyDetail(),

    ]


    view.value =
        'form'

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


    editingItem.value =
        null


    view.value =
        'list'

}


/*
|--------------------------------------------------------------------------
| Submit Form
|--------------------------------------------------------------------------
*/

function submit()
{

    if (
        formMode.value === 'create'
    ) {

        form.post(

            route(
                'purchase-returns.store'
            ),

            {

                preserveScroll: true,

                onSuccess: () => {

                    success(
                        'Success',
                        'Purchase return created successfully.'
                    )


                    view.value =
                        'list'

                },

                onError: (errors) => {

                    console.error(
                        'CREATE PURCHASE RETURN ERRORS:',
                        errors
                    )

                },

            }

        )


        return

    }


    form.put(

        route(

            'purchase-returns.update',

            editingItem.value.id

        ),

        {

            preserveScroll: true,

            onSuccess: () => {

                success(
                    'Success',
                    'Purchase return updated successfully.'
                )


                view.value =
                    'list'

            },

            onError: (errors) => {

                console.error(
                    'UPDATE PURCHASE RETURN ERRORS:',
                    errors
                )


                error(
                    'Failed to update purchase return.'
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

function editPurchaseReturn(item)
{

    if (
        ![
            'Draft',
            'Rejected',
        ].includes(
            item.status
        )
    ) {

        error(
            'Only Draft or Rejected purchase return can be edited.'
        )

        return

    }


    formMode.value =
        'edit'


    editingItem.value =
        item


    form.clearErrors()


    form.return_number =
        item.return_number


    form.goods_receipt_id =
        item.goods_receipt_id


    form.purchase_order_id =
        item.purchase_order_id


    form.supplier_id =
        item.supplier_id


    form.warehouse_id =
        item.warehouse_id


    form.return_date =
        item.return_date
            ? String(
                item.return_date
            ).slice(0, 10)
            : null


    form.remarks =
        item.remarks


    form.details =
        item.details?.map(
            detail => ({

                goods_receipt_detail_id:
                    detail.goods_receipt_detail_id,

                purchase_order_detail_id:
                    detail.purchase_order_detail_id,

                product_variant_id:
                    detail.product_variant_id,

                unit_id:
                    detail.unit_id,

                received_qty:
                    detail.received_qty ?? 0,

                returned_qty:
                    detail.returned_qty ?? 0,

                unit_cost:
                    detail.unit_cost ?? 0,

                total_cost:
                    detail.total_cost ?? 0,

                remarks:
                    detail.remarks,

            })
        ) ?? [

            createEmptyDetail(),

        ]


    view.value =
        'form'

}


/*
|--------------------------------------------------------------------------
| Show
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
                    'purchase-returns.data',
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
            'PURCHASE RETURN VIEW RESPONSE:',
            responseData
        )


        viewItem.value =
            responseData.data


    } catch (
        exception
    ) {

        console.error(
            'PURCHASE RETURN VIEW ERROR:',
            exception
        )


        showView.value =
            false


        viewItem.value =
            null


        error(
            'Failed to load purchase return detail.'
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
| Delete
|--------------------------------------------------------------------------
*/

const deleteItem =
    ref(null)

const showDelete =
    ref(false)


function openDelete(item)
{

    if (
        ![
            'Draft',
            'Rejected',
        ].includes(
            item.status
        )
    ) {

        error(
            'Only Draft or Rejected purchase return can be deleted.'
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


const deleteMessage =
    computed(() => {

        if (
            !deleteItem.value
        ) {

            return ''

        }


        return `Are you sure you want to delete "${deleteItem.value.return_number}"?`

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
            'purchase-returns.destroy',
            deleteItem.value.id
        ),

        {

            preserveScroll: true,

            onSuccess: () => {

                closeDelete()


                success(
                    'Success',
                    'Purchase return deleted successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to delete purchase return.'
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

const showBulkDelete =
    ref(false)


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


const bulkDeleteMessage =
    computed(() => {

        const total =
            selectedRows.value.length


        if (!total) {

            return ''

        }


        return `Are you sure you want to delete ${total} selected Purchase Return document(s)?`

    })


function bulkDelete()
{

    router.delete(

        route(
            'purchase-returns.bulk-delete'
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
                    'Purchase returns deleted successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to delete purchase returns.'
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

const submitItem =
    ref(null)

const showSubmit =
    ref(false)

const submitLoading =
    ref(false)


function openSubmit(item)
{

    submitItem.value =
        item


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


    submitLoading.value =
        true


    router.post(

        route(
            'purchase-returns.submit',
            submitItem.value.id
        ),

        {},

        {

            preserveScroll: true,

            onSuccess: () => {

                closeSubmit()


                success(
                    'Success',
                    'Purchase return submitted successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to submit purchase return.'
                )

            },

            onFinish: () => {

                submitLoading.value =
                    false

            },

        }

    )

}


/*
|--------------------------------------------------------------------------
| Approve
|--------------------------------------------------------------------------
*/

const approveItem =
    ref(null)

const showApprove =
    ref(false)

const approveLoading =
    ref(false)


function openApprove(item)
{

    approveItem.value =
        item


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


    approveLoading.value =
        true


    router.post(

        route(
            'purchase-returns.approve',
            approveItem.value.id
        ),

        {},

        {

            preserveScroll: true,

            onSuccess: () => {

                closeApprove()


                success(
                    'Success',
                    'Purchase return approved successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to approve purchase return.'
                )

            },

            onFinish: () => {

                approveLoading.value =
                    false

            },

        }

    )

}


/*
|--------------------------------------------------------------------------
| Reject
|--------------------------------------------------------------------------
*/

const rejectItem =
    ref(null)

const showReject =
    ref(false)

const rejectReason =
    ref('')

const rejectLoading =
    ref(false)


function openReject(item)
{

    rejectItem.value =
        item


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


    rejectLoading.value =
        true


    router.post(

        route(
            'purchase-returns.reject',
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
                    'Purchase return rejected successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to reject purchase return.'
                )

            },

            onFinish: () => {

                rejectLoading.value =
                    false

            },

        }

    )

}


/*
|--------------------------------------------------------------------------
| Post
|--------------------------------------------------------------------------
*/

const postItem =
    ref(null)

const showPost =
    ref(false)

const postLoading =
    ref(false)
const postDataLoading =
    ref(false)

async function openPost(item)
{
    postDataLoading.value =
        true


    try {

        const response =
            await axios.get(

                route(
                    'purchase-returns.data',
                    item.id
                )

            )


        postItem.value =
            response.data.data


        showPost.value =
            true


    } catch (err) {

        console.error(
            'FAILED TO LOAD PURCHASE RETURN:',
            err
        )


        error(
            'Failed to load purchase return details.'
        )


    } finally {

        postDataLoading.value =
            false

    }

}
function closePost()
{

    postItem.value =
        null


    showPost.value =
        false

}


function confirmPost()
{

    if (
        !postItem.value
    ) {

        return

    }


    postLoading.value =
        true


    router.post(

        route(
            'purchase-returns.post',
            postItem.value.id
        ),

        {},

        {

            preserveScroll: true,

            onSuccess: () => {

                closePost()


                success(
                    'Success',
                    'Purchase return posted successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to post purchase return.'
                )

            },

            onFinish: () => {

                postLoading.value =
                    false

            },

        }

    )

}


/*
|--------------------------------------------------------------------------
| Cancel
|--------------------------------------------------------------------------
*/

const cancelItem =
    ref(null)

const showCancel =
    ref(false)

const cancelReason =
    ref('')

const cancelLoading =
    ref(false)


function openCancel(item)
{

    if (
        ![
            'Approved',
            'Posted',
        ].includes(
            item.status
        )
    ) {

        error(
            'Only Approved or Posted purchase return can be cancelled.'
        )

        return

    }


    cancelItem.value =
        item


    cancelReason.value =
        ''


    showCancel.value =
        true

}


function closeCancel()
{

    cancelItem.value =
        null


    cancelReason.value =
        ''


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


    if (
        !cancelReason.value.trim()
    ) {

        error(
            'Cancellation reason is required.'
        )

        return

    }


    cancelLoading.value =
        true


    router.post(

        route(
            'purchase-returns.cancel',
            cancelItem.value.id
        ),

        {

            reason:
                cancelReason.value.trim(),

        },

        {

            preserveScroll: true,

            onSuccess: () => {

                closeCancel()


                success(
                    'Success',
                    'Purchase return cancelled successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to cancel purchase return.'
                )

            },

            onFinish: () => {

                cancelLoading.value =
                    false

            },

        }

    )

}


/*
|--------------------------------------------------------------------------
| Permissions
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

    return item.status === 'Draft'

}


function canApprove(item)
{

    return item.status === 'Submitted'

}


function canReject(item)
{

    return item.status === 'Submitted'

}


function canPost(item)
{

    return item.status === 'Approved'

}


function canCancel(item)
{
    return [
        'Draft',
        'Submitted',
    ].includes(
        item?.status
    )
}


function canDelete(item)
{

    return [

        'Draft',
        'Rejected',

    ].includes(
        item.status
    )

}


/*
|--------------------------------------------------------------------------
| Sorting
|--------------------------------------------------------------------------
*/

const sort =
    ref(
        props.filters?.sort_by ?? 'id'
    )


const direction =
    ref(
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
            'purchase-returns.index'
        ),

        {

            search:
                filters.search,

            supplier_id:
                filters.supplier_id,

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

            date_from:
                filters.date_from,

            date_to:
                filters.date_to,

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
| Currency
|--------------------------------------------------------------------------
*/

const formatCurrency = (value) => {

    return new Intl.NumberFormat(
        'id-ID',
        {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }
    ).format(
        Number(value || 0)
    )

}
</script>


<template>

<AppLayout>

    <Transition
        name="page"
        mode="out-in"
    >

        <!-- ===================================================== -->
        <!-- LIST -->
        <!-- ===================================================== -->

        <div
            v-if="view === 'list'"
            key="list"
        >

            <div class="space-y-6">

                <!-- ================================================= -->
                <!-- Statistics -->
                <!-- ================================================= -->

                <div
                    class="
                        grid
                        grid-cols-1
                        gap-4
                        md:grid-cols-2
                        xl:grid-cols-4
                    "
                >

                    <StatsCard
                        title="Total Purchase Return"
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

                    <StatsCard
                        title="Rejected"
                        :value="statistics?.rejected ?? 0"
                        icon="❌"
                    />

                    <StatsCard
                        title="Posted"
                        :value="statistics?.posted ?? 0"
                        icon="📦"
                    />

                    <StatsCard
                        title="Cancelled"
                        :value="statistics?.cancelled ?? 0"
                        icon="🚫"
                    />

                </div>


                <!-- ================================================= -->
                <!-- List Card -->
                <!-- ================================================= -->

                <Card class="mt-4">

                    <!-- ============================================= -->
                    <!-- Toolbar -->
                    <!-- ============================================= -->

                    <div class="space-y-3">

                        <!-- Top Row -->

                        <div
                            class="
                                flex
                                flex-col
                                gap-2
                                sm:flex-row
                                sm:items-center
                            "
                        >

                            <input
                                v-model="filters.search"
                                type="text"
                                placeholder="Search return number, PO number, GRN number..."
                                class="
                                    min-w-0
                                    flex-1
                                    rounded-xl
                                    border
                                    border-gray-300
                                    px-4
                                    py-2.5
                                "
                            />

                            <BaseButton
                                variant="secondary"
                                class="
                                    w-full
                                    shrink-0
                                    whitespace-nowrap
                                    sm:w-auto
                                "
                                @click="refresh"
                            >
                                Refresh
                            </BaseButton>

                        </div>


                        <!-- Bottom Row -->

                        <div
                            class="
                                grid
                                grid-cols-1
                                gap-2
                                sm:grid-cols-2
                                lg:flex
                                lg:flex-wrap
                                lg:items-center
                            "
                        >

                            <!-- Return Date -->

                            <FlatPickr
                                v-model="filters.date_range"
                                :config="{
                                    mode: 'range',
                                    dateFormat: 'Y-m-d',
                                    allowInput: true,
                                }"
                                placeholder="Return Date"
                                class="
                                    w-full
                                    rounded-xl
                                    border
                                    border-gray-300
                                    px-3
                                    py-2.5
                                    lg:w-56
                                "
                            />


                            <!-- Supplier -->

                            <div class="w-full lg:w-56">

                                <SearchableSelect
                                    v-model="filters.supplier_id"
                                    :options="suppliers"
                                    label="label"
                                    value-key="id"
                                    placeholder="All Suppliers"
                                />

                            </div>


                            <!-- Warehouse -->

                            <div class="w-full lg:w-56">

                                <SearchableSelect
                                    v-model="filters.warehouse_id"
                                    :options="warehouses"
                                    label="label"
                                    value-key="id"
                                    placeholder="All Warehouses"
                                />

                            </div>


                            <!-- Status -->

                            <div class="w-full lg:w-44">

                                <SearchableSelect
                                    v-model="filters.status"
                                    :options="statusOptions"
                                    label="label"
                                    value-key="value"
                                    placeholder="All Status"
                                />

                            </div>


                            <!-- Actions -->

                            <div
                                class="
                                    grid
                                    grid-cols-2
                                    gap-2
                                    sm:col-span-2
                                    lg:flex
                                    lg:items-center
                                "
                            >

                                <BaseButton
                                    class="
                                        w-full
                                        whitespace-nowrap
                                        lg:w-auto
                                    "
                                    @click="create"
                                >

                                    <template #icon>

                                        <PlusIcon
                                            class="h-5 w-5"
                                        />

                                    </template>

                                    Add

                                </BaseButton>


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
                                    class="
                                        w-full
                                        lg:w-auto
                                    "
                                />

                            </div>

                        </div>

                    </div>


                    <!-- ================================================= -->
                    <!-- Table -->
                    <!-- ================================================= -->

                    <div class="mt-6">

                        <LoadingOverlay
                            :show="loading"
                            text="Loading Purchase Return..."
                        />


                        <DataTable
                            v-if="purchaseReturns?.data?.length"
                            sticky-header
                            max-height="650px"
                        >

                            <DataTableHead sticky>

                                <!-- Select -->

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


                                <!-- Return Number -->

                                <DataTableHeaderCell
                                    sortable
                                    column="return_number"
                                    :sort="sort"
                                    :direction="direction"
                                    @sort="sortBy"
                                    width="180px"
                                >
                                    Return Number
                                </DataTableHeaderCell>


                                <!-- Return Date -->

                                <DataTableHeaderCell
                                    sortable
                                    column="return_date"
                                    :sort="sort"
                                    :direction="direction"
                                    @sort="sortBy"
                                    width="150px"
                                >
                                    Return Date
                                </DataTableHeaderCell>


                                <!-- PO -->

                                <DataTableHeaderCell
                                    width="180px"
                                >
                                    PO Number
                                </DataTableHeaderCell>


                                <!-- GRN -->

                                <DataTableHeaderCell
                                    width="180px"
                                >
                                    GRN Number
                                </DataTableHeaderCell>


                                <!-- Supplier -->

                                <DataTableHeaderCell
                                    width="220px"
                                >
                                    Supplier
                                </DataTableHeaderCell>


                                <!-- Warehouse -->

                                <DataTableHeaderCell
                                    width="200px"
                                >
                                    Warehouse
                                </DataTableHeaderCell>


                                <!-- Items -->

                                <DataTableHeaderCell
                                    width="80px"
                                    align="right"
                                >
                                    Items
                                </DataTableHeaderCell>


                                <!-- Returned -->

                                <DataTableHeaderCell
                                    width="110px"
                                    align="right"
                                >
                                    Returned
                                </DataTableHeaderCell>


                                <!-- Total Cost -->

                                <DataTableHeaderCell
                                    width="140px"
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
                                    width="140px"
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
                                    v-for="item in (
                                        purchaseReturns?.data ?? []
                                    )"
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


                                    <!-- Return Number -->

                                    <DataTableCell>

                                        <span class="font-medium">
                                            {{ item.return_number }}
                                        </span>

                                    </DataTableCell>


                                    <!-- Return Date -->

                                    <DataTableCell>

                                        {{
                                            item.return_date
                                                ? formatDate(
                                                    item.return_date
                                                )
                                                : '-'
                                        }}

                                    </DataTableCell>


                                    <!-- PO -->

                                    <DataTableCell>

                                        <span class="font-medium">
                                            {{
                                                item.purchase_order?.number
                                                ?? '-'
                                            }}
                                        </span>

                                    </DataTableCell>


                                    <!-- GRN -->

                                    <DataTableCell>

                                        <span class="font-medium">
                                            {{
                                                item.goods_receipt?.grn_number
                                                ?? '-'
                                            }}
                                        </span>

                                    </DataTableCell>


                                    <!-- Supplier -->

                                    <DataTableCell>

                                        <div class="font-medium">
                                            {{
                                                item.supplier?.name
                                                ?? '-'
                                            }}
                                        </div>

                                        <div
                                            v-if="
                                                item.supplier?.supplier_code
                                            "
                                            class="text-xs text-gray-500"
                                        >
                                            {{
                                                item.supplier.supplier_code
                                            }}
                                        </div>

                                    </DataTableCell>


                                    <!-- Warehouse -->

                                    <DataTableCell>

                                        {{
                                            item.warehouse?.name
                                            ?? '-'
                                        }}

                                    </DataTableCell>


                                    <!-- Items -->

                                    <DataTableCell align="right">

                                        {{
                                            item.total_items
                                            ?? item.details?.length
                                            ?? 0
                                        }}

                                    </DataTableCell>


                                    <!-- Returned -->

                                    <DataTableCell align="right">

                                        {{
                                            Number(
                                                item.total_returned
                                                ?? 0
                                            )
                                        }}

                                    </DataTableCell>


                                    <!-- Total Cost -->

                                    <DataTableCell align="right">
                                    {{ formatCurrency(item.total_cost?? 0) }}
                                       

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

                                            @view="
                                                openView(item)
                                            "

                                            @edit="
                                                editPurchaseReturn(item)
                                            "

                                            @submit="
                                                openSubmit(item)
                                            "

                                            @approve="
                                                openApprove(item)
                                            "

                                            @reject="
                                                openReject(item)
                                            "

                                            @post="
                                                openPost(item)
                                            "

                                            @cancel="
                                                openCancel(item)
                                            "

                                            @delete="
                                                openDelete(item)
                                            "

                                            :showEdit="
                                                canEdit(item)
                                            "

                                            :showDuplicate="false"

                                            :showSubmit="
                                                canSubmit(item)
                                            "

                                            :showApprove="
                                                canApprove(item)
                                            "

                                            :showReject="
                                                canReject(item)
                                            "

                                            :showSend="false"

                                            :showConfirm="false"

                                            :showPost="
                                                canPost(item)
                                            "

                                            :showCancel="
                                                canCancel(item)
                                            "

                                            :showExport="false"

                                            :showHistory="false"

                                            :showDelete="
                                                canDelete(item)
                                            "

                                        />

                                    </DataTableCell>

                                </DataTableRow>

                            </DataTableBody>

                        </DataTable>


                        <!-- Empty -->

                        <TableEmpty
                            v-else
                            icon="↩️"
                            title="No Purchase Return Found"
                            description="There are no Purchase Return transactions available."
                        >

                            <template #action>

                                <BaseButton
                                    @click="create"
                                >
                                    Create Purchase Return
                                </BaseButton>

                            </template>

                        </TableEmpty>

                    </div>


                    <!-- ================================================= -->
                    <!-- Pagination -->
                    <!-- ================================================= -->

                    <div class="mt-6">

                        <TablePagination
                            :data="purchaseReturns"
                            label="Purchase Return"
                        />

                    </div>

                </Card>

            </div>

        </div>


        <!-- ========================================================= -->
        <!-- FORM -->
        <!-- ========================================================= -->

        <div
            v-else-if="view === 'form'"
            key="form"
            class="space-y-6"
        >

            <PageHeader
                icon="↩️"
                :title="
                    formMode === 'create'
                        ? 'Create Purchase Return'
                        : 'Edit Purchase Return'
                "
                :subtitle="
                    formMode === 'create'
                        ? 'Create a new purchase return.'
                        : 'Update purchase return.'
                "
            />


            <Card>

                <PurchaseReturnForm
                    :form="form"
                    :goods-receipts="goodsReceipts"
                    :suppliers="suppliers"
                    :warehouses="warehouses"
                    :mode="formMode"
                    @submit="submit"
                    @cancel="cancelForm"
                />

            </Card>

        </div>

    </Transition>

</AppLayout>


<!-- =============================================================== -->
<!-- Delete -->
<!-- =============================================================== -->

<ConfirmDeleteModal
    :show="showDelete"
    title="Delete Purchase Return"
    :message="deleteMessage"
    confirm-text="Delete"
    @close="closeDelete"
    @confirm="confirmDelete"
/>


<!-- =============================================================== -->
<!-- Bulk Delete -->
<!-- =============================================================== -->

<ConfirmDeleteModal
    :show="showBulkDelete"
    title="Delete Purchase Returns"
    :message="bulkDeleteMessage"
    confirm-text="Delete"
    @close="
        showBulkDelete = false
    "
    @confirm="bulkDelete"
/>


<!-- =============================================================== -->
<!-- View -->
<!-- =============================================================== -->

<PurchaseReturnViewModal
    :show="showView"
    :purchase-return="viewItem"
    :loading="viewLoading"
    @close="closeView"
/>


<!-- =============================================================== -->
<!-- Submit -->
<!-- =============================================================== -->

<PurchaseReturnSubmitModal
    :show="showSubmit"
    :purchase-return="submitItem"
    :loading="submitLoading"
    @close="closeSubmit"
    @confirm="confirmSubmit"
/>


<!-- =============================================================== -->
<!-- Approve -->
<!-- =============================================================== -->

<PurchaseReturnApproveModal
    :show="showApprove"
    :purchase-return="approveItem"
    :loading="approveLoading"
    @close="closeApprove"
    @confirm="confirmApprove"
/>


<!-- =============================================================== -->
<!-- Reject -->
<!-- =============================================================== -->

<PurchaseReturnRejectModal
    :show="showReject"
    :purchase-return="rejectItem"
    :reason="rejectReason"
    :loading="rejectLoading"
    @close="closeReject"
    @update:reason="
        rejectReason = $event
    "
    @confirm="confirmReject"
/>


<!-- =============================================================== -->
<!-- Post -->
<!-- =============================================================== -->

<PurchaseReturnPostModal
    :show="showPost"
    :purchase-return="postItem"
    :loading="postLoading"
    @close="closePost"
    @confirm="confirmPost"
/>


<!-- =============================================================== -->
<!-- Cancel -->
<!-- =============================================================== -->

<PurchaseReturnCancelModal
    :show="showCancel"
    :purchase-return="cancelItem"
    :loading="cancelLoading"
    :reason="cancelReason"
    @close="closeCancel"
    @confirm="confirmCancel"
    @update:reason="
        cancelReason = $event
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

    transform:
        translateY(8px);

}


.page-leave-to {

    opacity: 0;

    transform:
        translateY(-8px);

}

</style>