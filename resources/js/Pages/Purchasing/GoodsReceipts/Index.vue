<script setup>

import {
    ref,
    reactive,
    computed,
    watch,
    onMounted,
    onUnmounted,
    toRefs,
} from 'vue'

import {
    router,
    useForm,
} from '@inertiajs/vue3'

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

import {
    PlusIcon,
} from '@heroicons/vue/24/solid'

import {
    success,
    error,
    formatDate,
} from '@/Utils'

import FlatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'

import GoodsReceiptForm from './Partials/GoodsReceiptForm.vue'
import GoodsReceiptViewModal from './Partials/GoodsReceiptViewModal.vue'
import GoodsReceiptSubmitModal from './Partials/GoodsReceiptSubmitModal.vue'
import GoodsReceiptApproveModal from './Partials/GoodsReceiptApproveModal.vue'
import GoodsReceiptRejectModal from './Partials/GoodsReceiptRejectModal.vue'
import GoodsReceiptPostModal from './Partials/GoodsReceiptPostModal.vue'
import GoodsReceiptCancelModal from './Partials/GoodsReceiptCancelModal.vue'

import ConfirmDeleteModal from '@/Components/Modal/ConfirmDeleteModal.vue'


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    goodsReceipts: {

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

    purchaseOrders: {

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
    goodsReceipts,
    statistics,
} = toRefs(props)


/*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/

const pageTitle = computed(
    () => 'Goods Receipt'
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
            'goods-receipts.index'
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
        goodsReceipts
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
        goodsReceipts
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
            goodsReceipts
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

    purchase_order_detail_id:
        null,

    product_variant_id:
        null,

    unit_id:
        null,

    received_qty:
        0,

    rejected_qty:
        0,

    unit_cost:
        0,

    line_total:
        0,

    description:
        null,

})


const form = useForm({

    grn_number:
        props.previewNumber ?? '',

    purchase_order_id:
        null,

    supplier_id:
        null,

    warehouse_id:
        null,

    receipt_date:
        new Date()
            .toISOString()
            .slice(0, 10),

    supplier_do_number:
        null,

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


    form.grn_number =
        props.previewNumber ?? ''


    form.receipt_date =
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
                'goods-receipts.store'
            ),

            {

                preserveScroll: true,

                onSuccess: () => {

                    success(
                        'Success',
                        'Goods receipt created successfully.'
                    )


                    view.value =
                        'list'

                },

                onError: (errors) => {

                    console.error(
                        'CREATE GRN ERRORS:',
                        errors
                    )

                },

            }

        )


        return

    }


    form.put(

        route(

            'goods-receipts.update',

            editingItem.value.id

        ),

        {

            preserveScroll: true,

            onSuccess: () => {

                success(
                    'Success',
                    'Goods receipt updated successfully.'
                )


                view.value =
                    'list'

            },

            onError: (errors) => {

                console.error(
                    'UPDATE GRN ERRORS:',
                    errors
                )


                error(
                    'Failed to update goods receipt.'
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

function editGoodsReceipt(item)
{

    if (
        item.status !== 'Draft'
    ) {

        error(
            'Only Draft goods receipt can be edited.'
        )

        return

    }


    formMode.value =
        'edit'


    editingItem.value =
        item


    form.clearErrors()


    form.grn_number =
        item.grn_number


    form.purchase_order_id =
        item.purchase_order_id


    form.supplier_id =
        item.supplier_id


    form.warehouse_id =
        item.warehouse_id


    form.receipt_date =
        item.receipt_date
            ? String(
                item.receipt_date
            ).slice(0, 10)
            : null


    form.supplier_do_number =
        item.supplier_do_number


    form.remarks =
        item.remarks


    form.details =
        item.details?.map(
            detail => ({

                purchase_order_detail_id:
                    detail.purchase_order_detail_id,

                product_variant_id:
                    detail.product_variant_id,

                unit_id:
                    detail.unit_id,

                received_qty:
                    detail.received_qty ?? 0,

                rejected_qty:
                    detail.rejected_qty ?? 0,

                unit_cost:
                    detail.unit_cost ?? 0,

                line_total:
                    detail.line_total ?? 0,

                description:
                    detail.description,

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
                    'goods-receipts.data',
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
            'GOODS RECEIPT VIEW RESPONSE:',
            responseData
        )


        viewItem.value =
            responseData.data


    } catch (
        exception
    ) {

        console.error(
            'GOODS RECEIPT VIEW ERROR:',
            exception
        )


        showView.value =
            false


        viewItem.value =
            null


        error(
            'Failed to load goods receipt detail.'
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
        item.status !== 'Draft'
    ) {

        error(
            'Only Draft goods receipt can be deleted.'
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


        return `Are you sure you want to delete "${deleteItem.value.grn_number}"?`

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
            'goods-receipts.destroy',
            deleteItem.value.id
        ),

        {

            preserveScroll: true,

            onSuccess: () => {

                closeDelete()


                success(
                    'Success',
                    'Goods receipt deleted successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to delete goods receipt.'
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


        return `Are you sure you want to delete ${total} selected Goods Receipt document(s)?`

    })


function bulkDelete()
{

    router.delete(

        route(
            'goods-receipts.bulk-delete'
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
                    'Goods receipts deleted successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to delete goods receipts.'
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
            'goods-receipts.submit',
            submitItem.value.id
        ),

        {},

        {

            preserveScroll: true,

            onSuccess: () => {

                closeSubmit()


                success(
                    'Success',
                    'Goods receipt submitted successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to submit goods receipt.'
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
            'goods-receipts.approve',
            approveItem.value.id
        ),

        {},

        {

            preserveScroll: true,

            onSuccess: () => {

                closeApprove()


                success(
                    'Success',
                    'Goods receipt approved successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to approve goods receipt.'
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
            'goods-receipts.reject',
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
                    'Goods receipt rejected successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to reject goods receipt.'
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


function openPost(item)
{

    postItem.value =
        item


    showPost.value =
        true

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
            'goods-receipts.post',
            postItem.value.id
        ),

        {},

        {

            preserveScroll: true,

            onSuccess: () => {

                closePost()


                success(
                    'Success',
                    'Goods receipt posted successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to post goods receipt.'
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
            'goods-receipts.cancel',
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
                    'Goods receipt cancelled successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to cancel goods receipt.'
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

    return item.status === 'Draft'

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

        'Approved',

        'Posted',

    ].includes(
        item.status
    )

}


function canDelete(item)
{

    return item.status === 'Draft'

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
            'goods-receipts.index'
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
                        title="Total Goods Receipt"
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
                                placeholder="Search GRN number, PO number, DO number..."
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

                            <!-- Receipt Date -->

                            <FlatPickr
                                v-model="filters.date_range"
                                :config="{
                                    mode: 'range',
                                    dateFormat: 'Y-m-d',
                                    allowInput: true,
                                }"
                                placeholder="Receipt Date"
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
                            text="Loading Goods Receipt..."
                        />


                        <DataTable
                            v-if="goodsReceipts?.data?.length"
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


                                <!-- GRN -->

                                <DataTableHeaderCell
                                    sortable
                                    column="grn_number"
                                    :sort="sort"
                                    :direction="direction"
                                    @sort="sortBy"
                                    width="180px"
                                >
                                    GRN Number
                                </DataTableHeaderCell>


                                <!-- Receipt Date -->

                                <DataTableHeaderCell
                                    sortable
                                    column="receipt_date"
                                    :sort="sort"
                                    :direction="direction"
                                    @sort="sortBy"
                                    width="150px"
                                >
                                    Receipt Date
                                </DataTableHeaderCell>


                                <!-- PO -->

                                <DataTableHeaderCell
                                    width="180px"
                                >
                                    PO Number
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


                                <!-- Supplier DO -->

                                <DataTableHeaderCell
                                    width="180px"
                                >
                                    Supplier DO
                                </DataTableHeaderCell>


                                <!-- Items -->

                                <DataTableHeaderCell
                                    width="80px"
                                    align="right"
                                >
                                    Items
                                </DataTableHeaderCell>


                                <!-- Received -->

                                <DataTableHeaderCell
                                    width="110px"
                                    align="right"
                                >
                                    Received
                                </DataTableHeaderCell>


                                <!-- Rejected -->

                                <DataTableHeaderCell
                                    width="110px"
                                    align="right"
                                >
                                    Rejected
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
                                        goodsReceipts?.data ?? []
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


                                    <!-- GRN -->

                                    <DataTableCell>

                                        <span class="font-medium">
                                            {{ item.grn_number }}
                                        </span>

                                    </DataTableCell>


                                    <!-- Receipt Date -->

                                    <DataTableCell>

                                        {{
                                            item.receipt_date
                                                ? formatDate(
                                                    item.receipt_date
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


                                    <!-- Supplier DO -->

                                    <DataTableCell>

                                        {{
                                            item.supplier_do_number
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


                                    <!-- Received -->

                                    <DataTableCell align="right">

                                        {{
                                            Number(
                                                item.total_received
                                                ?? 0
                                            )
                                        }}

                                    </DataTableCell>


                                    <!-- Rejected -->

                                    <DataTableCell align="right">

                                        {{
                                            Number(
                                                item.total_rejected
                                                ?? 0
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

                                            @view="
                                                openView(item)
                                            "

                                            @edit="
                                                editGoodsReceipt(item)
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
                            icon="📦"
                            title="No Goods Receipt Found"
                            description="There are no Goods Receipt transactions available."
                        >

                            <template #action>

                                <BaseButton
                                    @click="create"
                                >
                                    Create Goods Receipt
                                </BaseButton>

                            </template>

                        </TableEmpty>

                    </div>


                    <!-- ================================================= -->
                    <!-- Pagination -->
                    <!-- ================================================= -->

                    <div class="mt-6">

                        <TablePagination
                            :data="goodsReceipts"
                            label="Goods Receipt"
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
                icon="📦"
                :title="
                    formMode === 'create'
                        ? 'Create Goods Receipt'
                        : 'Edit Goods Receipt'
                "
                :subtitle="
                    formMode === 'create'
                        ? 'Create a new goods receipt.'
                        : 'Update goods receipt.'
                "
            />


            <Card>

                <GoodsReceiptForm
                    :form="form"
                    :purchase-orders="purchaseOrders"
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
    title="Delete Goods Receipt"
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
    title="Delete Goods Receipts"
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

<GoodsReceiptViewModal
    :show="showView"
    :goods-receipt="viewItem"
    :loading="viewLoading"
    @close="closeView"
/>


<!-- =============================================================== -->
<!-- Submit -->
<!-- =============================================================== -->

<GoodsReceiptSubmitModal
    :show="showSubmit"
    :goods-receipt="submitItem"
    :loading="submitLoading"
    @close="closeSubmit"
    @confirm="confirmSubmit"
/>


<!-- =============================================================== -->
<!-- Approve -->
<!-- =============================================================== -->

<GoodsReceiptApproveModal
    :show="showApprove"
    :goods-receipt="approveItem"
    :loading="approveLoading"
    @close="closeApprove"
    @confirm="confirmApprove"
/>


<!-- =============================================================== -->
<!-- Reject -->
<!-- =============================================================== -->

<GoodsReceiptRejectModal
    :show="showReject"
    :goods-receipt="rejectItem"
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

<GoodsReceiptPostModal
    :show="showPost"
    :goods-receipt="postItem"
    :loading="postLoading"
    @close="closePost"
    @confirm="confirmPost"
/>


<!-- =============================================================== -->
<!-- Cancel -->
<!-- =============================================================== -->

<GoodsReceiptCancelModal
    :show="showCancel"
    :goods-receipt="cancelItem"
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