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

import {
    LoadingOverlay,
} from '@/Components/Feedback'

import {
    PlusIcon,
} from '@heroicons/vue/24/solid'

import {
    success,
    error,
} from '@/Utils'

import {
    formatDate,
} from '@/Utils'

import FlatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'

import PurchasePaymentForm from './Partials/PurchasePaymentForm.vue'
import PurchasePaymentViewModal from './Partials/PurchasePaymentViewModal.vue'
import PurchasePaymentApproveModal from './Partials/PurchasePaymentApproveModal.vue'
import PurchasePaymentSubmitModal from './Partials/PurchasePaymentSubmitModal.vue'
import PurchasePaymentRejectModal from './Partials/PurchasePaymentRejectModal.vue'
import PurchasePaymentCancelModal from './Partials/PurchasePaymentCancelModal.vue'
import PurchasePaymentPostModal from './Partials/PurchasePaymentPostModal.vue'

import ConfirmDeleteModal from '@/Components/Modal/ConfirmDeleteModal.vue'


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    purchasePayments: {

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

            rejected: 0,

            approved: 0,

            posted: 0,

            cancelled: 0,

        }),

    },

    companies: {

        type: Array,

        default: () => [],

    },

    branches: {

        type: Array,

        default: () => [],

    },

    suppliers: {

        type: Array,

        default: () => [],

    },

    paymentAccounts: {

        type: Array,

        default: () => [],

    },

    purchaseInvoices: {

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

    show: {

        type: Boolean,

        default: false,

    },

    purchasePayment: {

        type: Object,

        default: null,

    },

    loading: {

        type: Boolean,

        default: false,

    },

})


const {
    purchasePayments,
    statistics,
} = toRefs(props)


/*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/

const pageTitle = computed(
    () => 'Purchase Payment'
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

    supplier_id:
        props.filters?.supplier_id ?? '',

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


    if (filters.date_range) {

        const dates =
            filters.date_range.split(' to ')

        dateFrom =
            dates[0] ?? ''

        dateTo =
            dates[1]
            ?? dates[0]
            ?? ''

    }


    router.get(

        route(
            'purchase-payments.index'
        ),

        {

            ...filters,

            date_from:
                dateFrom,

            date_to:
                dateTo,

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

    () => filters.supplier_id,

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


function refresh()
{

    Object.assign(

        filters,

        {

            search:
                '',

            branch_id:
                '',

            supplier_id:
                '',

            status:
                '',

            per_page:
                10,

            date_range:
                '',

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
        purchasePayments
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
        purchasePayments
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
            purchasePayments
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

    purchase_invoice_header_id:
        null,

    invoice_amount:
        0,

    previous_paid_amount:
        0,

    previous_outstanding_amount:
        0,

    payment_amount:
        0,

    remarks:
        null,

})


const form = useForm({

    number:
        props.previewNumber ?? '',

    company_id:
        null,

    branch_id:
        null,

    supplier_id:
        null,

    payment_date:
        new Date()
            .toISOString()
            .slice(0, 10),

    payment_method:
        '',

    payment_account_id:
        null,

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

    form.number =
        props.previewNumber ?? ''

    form.payment_date =
        new Date()
            .toISOString()
            .slice(0, 10)

    form.company_id =
        null

    form.branch_id =
        null

    form.supplier_id =
        null

    form.payment_method =
        ''

    form.payment_account_id =
        null

    form.remarks =
        null

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
                'purchase-payments.store'
            ),

            {

                preserveScroll:
                    true,

                onSuccess: () => {

                    success(
                        'Success',
                        'Purchase payment created successfully.'
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
            'purchase-payments.update',
            editingItem.value.id
        ),

        {

            preserveScroll:
                true,

            onSuccess: () => {

                success(
                    'Success',
                    'Purchase payment updated successfully.'
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
                    'Failed to update purchase payment.'
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
            'purchase-payments.store'
        ),

        {

            preserveScroll:
                true,

            onSuccess: () => {

                success(
                    'Success',
                    'Purchase payment created successfully.'
                )

                form.reset()

                form.clearErrors()

                form.number =
                    props.previewNumber ?? ''

                form.payment_date =
                    new Date()
                        .toISOString()
                        .slice(0, 10)

                form.company_id =
                    null

                form.branch_id =
                    null

                form.supplier_id =
                    null

                form.payment_method =
                    ''

                form.payment_account_id =
                    null

                form.remarks =
                    null

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

function editPurchasePayment(item)
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
            'Only Draft or Rejected purchase payment can be edited.'
        )

        return

    }


    editingItem.value =
        item

    formMode.value =
        'edit'

    form.clearErrors()

    form.number =
        item.number

    form.company_id =
        item.company_id

    form.branch_id =
        item.branch_id

    form.supplier_id =
        item.supplier_id

    form.payment_date =
        item.payment_date
            ? String(
                item.payment_date
            ).slice(0, 10)
            : null

    form.payment_method =
        item.payment_method ?? ''

    form.payment_account_id =
        item.payment_account_id

    form.remarks =
        item.remarks


    form.details =
        item.details?.map(
            detail => ({

                purchase_invoice_header_id:
                    detail.purchase_invoice_header_id,

                invoice_amount:
                    detail.invoice_amount ?? 0,

                previous_paid_amount:
                    detail.previous_paid_amount ?? 0,

                previous_outstanding_amount:
                    detail.previous_outstanding_amount ?? 0,

                payment_amount:
                    detail.payment_amount ?? 0,

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
| Post
|--------------------------------------------------------------------------
*/

const postItem = ref(null)

const showPost = ref(false)


function openPost(
    purchasePayment
)
{

    postItem.value =
        purchasePayment

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


    router.post(

        route(
            'purchasing.purchase-payments.post',
            postItem.value.id
        ),

        {},

        {

            preserveScroll:
                true,

            onSuccess: () => {

                closePost()

                success(
                    'Success',
                    'Purchase payment posted successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to post purchase payment.'
                )

            },

        }

    )

}

/*
|--------------------------------------------------------------------------
| Show
|--------------------------------------------------------------------------
*/

const selectedItem =
    ref(null)


function showPurchasePayment(item)
{

    selectedItem.value =
        item

    view.value =
        'show'

}


/*
|--------------------------------------------------------------------------
| Back From Show
|--------------------------------------------------------------------------
*/

function backToList()
{

    selectedItem.value =
        null

    view.value =
        'list'

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
            'Only Draft purchase payment can be deleted.'
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
            'purchase-payments.destroy',
            deleteItem.value.id
        ),

        {

            preserveScroll:
                true,

            onSuccess: () => {

                closeDelete()

                success(
                    'Success',
                    'Purchase payment deleted successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to delete purchase payment.'
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


        return `Are you sure you want to delete ${total} selected Purchase Payment document(s)?`

    })


function bulkDelete()
{

    router.delete(

        route(
            'purchase-payments.bulk-delete'
        ),

        {

            data: {

                ids:
                    selectedRows.value,

            },

            preserveScroll:
                true,

            onSuccess: () => {

                showBulkDelete.value =
                    false

                selectedRows.value =
                    []

                success(
                    'Success',
                    'Purchase payments deleted successfully.'
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

const showSubmitModal =
    ref(false)

const selectedPurchasePayment =
    ref(null)

const submitLoading =
    ref(false)


const openSubmit =
    (purchasePayment) => {

        selectedPurchasePayment.value =
            purchasePayment

        showSubmitModal.value =
            true

    }


const closeSubmit =
    () => {

        showSubmitModal.value =
            false

        selectedPurchasePayment.value =
            null

    }


const confirmSubmit =
    () => {

        if (
            !selectedPurchasePayment.value
        ) {

            return

        }


        submitLoading.value =
            true


        router.post(

            route(
                'purchasing.purchase-payments.submit',
                selectedPurchasePayment.value.id
            ),

            {},

            {

                preserveScroll:
                    true,

                onSuccess: () => {

                    closeSubmit()

                },

                onError: (errors) => {

                    console.error(
                        'SUBMIT PURCHASE PAYMENT ERROR:',
                        errors
                    )

                },

                onFinish: () => {

                    submitLoading.value =
                        false

                },

            }

        )

    }


const submitMessage =
    computed(() => {

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

const approveItem =
    ref(null)

const showApprove =
    ref(false)


function openApprove(
    purchasePayment
)
{

    approveItem.value =
        purchasePayment

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
            'purchasing.purchase-payments.approve',
            approveItem.value.id
        ),

        {},

        {

            preserveScroll:
                true,

            onSuccess: () => {

                closeApprove()

                success(
                    'Success',
                    'Purchase payment approved successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to approve purchase payment.'
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

const rejectItem =
    ref(null)

const showReject =
    ref(false)

const rejectReason =
    ref('')


function openReject(
    purchasePayment
)
{

    rejectItem.value =
        purchasePayment

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
            'purchasing.purchase-payments.reject',
            rejectItem.value.id
        ),

        {

            reason:
                rejectReason.value.trim(),

        },

        {

            preserveScroll:
                true,

            onSuccess: () => {

                closeReject()

                success(
                    'Success',
                    'Purchase payment rejected successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to reject purchase payment.'
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

const showCancelModal =
    ref(false)

const cancelLoading =
    ref(false)

const cancelReason =
    ref('')


function openCancel(item)
{

    selectedPurchasePayment.value =
        item

    cancelReason.value =
        ''

    showCancelModal.value =
        true

}


function closeCancel()
{

    showCancelModal.value =
        false

    selectedPurchasePayment.value =
        null

    cancelReason.value =
        ''

}


function confirmCancel()
{

    if (
        !selectedPurchasePayment.value
    ) {

        return

    }


    if (
        !cancelReason.value.trim()
    ) {

        return

    }


    cancelLoading.value =
        true


    router.post(

        route(
            'purchasing.purchase-payments.cancel',
            selectedPurchasePayment.value.id
        ),

        {

            reason:
                cancelReason.value,

        },

        {

            preserveScroll:
                true,

            onFinish: () => {

                cancelLoading.value =
                    false

            },

            onSuccess: () => {

                closeCancel()

            },

        }

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
            'purchase-payments.index'
        ),

        {

            search:
                filters.search,

            branch_id:
                filters.branch_id,

            supplier_id:
                filters.supplier_id,

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


function canCancel(item)
{

    return item.status === 'Approved'

}


function canDelete(item)
{

    return item.status === 'Draft'

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
                    'purchasing.purchase-payments.data',
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
            'PURCHASE PAYMENT VIEW RESPONSE:',
            responseData
        )


        viewItem.value =
            responseData.data


    } catch (
        exception
    ) {

        console.error(
            'PURCHASE PAYMENT VIEW ERROR:',
            exception
        )


        showView.value =
            false

        viewItem.value =
            null


        error(
            'Failed to load purchase payment detail.'
        )


    } finally {

        viewLoading.value =
            false

    }

}


/*
|--------------------------------------------------------------------------
| Close View
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| Currency
|--------------------------------------------------------------------------
*/

const formatCurrency = (value) => {

    return new Intl.NumberFormat(
        'id-ID',
        {

            style:
                'currency',

            currency:
                'IDR',

            minimumFractionDigits:
                0,

            maximumFractionDigits:
                0,

        }
    ).format(
        Number(
            value || 0
        )
    )

}

</script>
<template>

<AppLayout>

    <Transition
        name="page"
        mode="out-in"
    >

        <!-- ========================================================= -->
        <!-- LIST -->
        <!-- ========================================================= -->

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
                        title="Total Purchase Payment"
                        :value="statistics?.total ?? 0"
                        icon="💳"
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
                        title="Rejected"
                        :value="statistics?.rejected ?? 0"
                        icon="❌"
                    />

                    <StatsCard
                        title="Approved"
                        :value="statistics?.approved ?? 0"
                        icon="✅"
                    />

                    <StatsCard
                        title="Posted"
                        :value="statistics?.posted ?? 0"
                        icon="📒"
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

                            <!-- Search -->

                            <input
                                v-model="filters.search"
                                type="text"
                                placeholder="Search payment number..."
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


                            <!-- Refresh -->

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

                            <!-- Date Range -->

                            <FlatPickr
                                v-model="filters.date_range"
                                :config="{
                                    mode: 'range',
                                    dateFormat: 'Y-m-d',
                                    allowInput: true,
                                }"
                                placeholder="Payment Date"
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

                                <!-- Add -->

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

                        <!-- Loading -->

                        <LoadingOverlay
                            :show="loading"
                            text="Loading Purchase Payment..."
                        />


                        <!-- Data -->

                        <DataTable
                            v-if="
                                purchasePayments?.data?.length
                            "
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
                                        :checked="
                                            isAllSelected
                                        "
                                        @change="
                                            toggleSelectAll
                                        "
                                        class="
                                            rounded
                                            border-gray-300
                                        "
                                    />

                                </DataTableHeaderCell>


                                <!-- Payment Number -->

                                <DataTableHeaderCell
                                    sortable
                                    column="number"
                                    :sort="sort"
                                    :direction="direction"
                                    @sort="sortBy"
                                    width="190px"
                                >
                                    Payment Number
                                </DataTableHeaderCell>


                                <!-- Payment Date -->

                                <DataTableHeaderCell
                                    sortable
                                    column="payment_date"
                                    :sort="sort"
                                    :direction="direction"
                                    @sort="sortBy"
                                    width="150px"
                                >
                                    Payment Date
                                </DataTableHeaderCell>


                                <!-- Supplier -->

                                <DataTableHeaderCell
                                    width="220px"
                                >
                                    Supplier
                                </DataTableHeaderCell>


                                <!-- Payment Method -->

                                <DataTableHeaderCell
                                    width="150px"
                                >
                                    Payment Method
                                </DataTableHeaderCell>


                                <!-- Payment Account -->

                                <DataTableHeaderCell
                                    width="220px"
                                >
                                    Payment Account
                                </DataTableHeaderCell>


                                <!-- Invoices -->

                                <DataTableHeaderCell
                                    width="90px"
                                    align="right"
                                >
                                    Invoices
                                </DataTableHeaderCell>


                                <!-- Total -->

                                <DataTableHeaderCell
                                    sortable
                                    column="total_amount"
                                    :sort="sort"
                                    :direction="direction"
                                    @sort="sortBy"
                                    width="170px"
                                    align="right"
                                >
                                    Total
                                </DataTableHeaderCell>


                                <!-- Status -->

                                <DataTableHeaderCell
                                    sortable
                                    column="status"
                                    :sort="sort"
                                    :direction="direction"
                                    @sort="sortBy"
                                    width="150px"
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
                                    v-for="
                                        item in (
                                            purchasePayments?.data
                                            ?? []
                                        )
                                    "
                                    :key="item.id"
                                >

                                    <!-- Checkbox -->

                                    <DataTableCell align="center">

                                        <input
                                            v-model="
                                                selectedRows
                                            "
                                            :value="item.id"
                                            type="checkbox"
                                            class="
                                                rounded
                                                border-gray-300
                                            "
                                        />

                                    </DataTableCell>


                                    <!-- Payment Number -->

                                    <DataTableCell>

                                        <span
                                            class="
                                                font-medium
                                            "
                                        >
                                            {{ item.number }}
                                        </span>

                                    </DataTableCell>


                                    <!-- Payment Date -->

                                    <DataTableCell>

                                        {{
                                            item.payment_date
                                                ? formatDate(
                                                    item.payment_date
                                                )
                                                : '-'
                                        }}

                                    </DataTableCell>


                                    <!-- Supplier -->

                                    <DataTableCell>

                                        <div
                                            class="font-medium"
                                        >
                                            {{
                                                item.supplier?.name
                                                ?? '-'
                                            }}
                                        </div>

                                        <div
                                            v-if="
                                                item.supplier
                                                    ?.supplier_code
                                            "
                                            class="
                                                text-xs
                                                text-gray-500
                                            "
                                        >
                                            {{
                                                item.supplier
                                                    .supplier_code
                                            }}
                                        </div>

                                    </DataTableCell>


                                    <!-- Payment Method -->

                                    <DataTableCell>

                                        {{
                                            item.payment_method
                                            ?? '-'
                                        }}

                                    </DataTableCell>


                                   <!-- Payment Account -->

                                    <DataTableCell>

                                        <div
                                            class="font-medium"
                                        >
                                            {{
                                                item
                                                    .paymentAccount
                                                    ?.code
                                                ??
                                                item
                                                    .payment_account
                                                    ?.code
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
                                                item
                                                    .paymentAccount
                                                    ?.name
                                                ??
                                                item
                                                    .payment_account
                                                    ?.name
                                                ?? ''
                                            }}
                                        </div>

                                    </DataTableCell>


                                    <!-- Invoices -->

                                    <DataTableCell align="right">

                                        {{
                                            item.total_items
                                            ?? item.details_count
                                            ?? item.details?.length
                                            ?? 0
                                        }}

                                    </DataTableCell>


                                    <!-- Total -->

                                    <DataTableCell align="right">

                                        <span
                                            class="
                                                font-semibold
                                            "
                                        >
                                            {{
                                                formatCurrency(
                                                    item.total_amount
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

                                            @view="
                                                openView(item)
                                            "

                                            @edit="
                                                editPurchasePayment(
                                                    item
                                                )
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

                                            @cancel="
                                                openCancel(item)
                                            "

                                            @delete="
                                                openDelete(item)
                                            "
                                             @post="
                                                    openPost(item)
                                                "

                                                :showPost="
                                                    item.status === 'Approved'
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
                            icon="💳"
                            title="No Purchase Payment Found"
                            description="
                                There are no Purchase Payment
                                transactions available.
                            "
                        >

                            <template #action>

                                <BaseButton
                                    @click="create"
                                >
                                    Create Purchase Payment
                                </BaseButton>

                            </template>

                        </TableEmpty>

                    </div>


                    <!-- ================================================= -->
                    <!-- Pagination -->
                    <!-- ================================================= -->

                    <div class="mt-6">

                        <TablePagination
                            :data="purchasePayments"
                            label="Purchase Payment"
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
                icon="💳"
                :title="
                    formMode === 'create'
                        ? 'Create Purchase Payment'
                        : 'Edit Purchase Payment'
                "
                :subtitle="
                    formMode === 'create'
                        ? 'Create a new purchase payment.'
                        : 'Update purchase payment.'
                "
            />


            <Card>

                <PurchasePaymentForm
                    :form="form"
                    :companies="companies"
                    :branches="branches"
                    :suppliers="suppliers"
                    :payment-accounts="paymentAccounts"
                    :purchase-invoices="purchaseInvoices"
                    :mode="formMode"
                    @submit="submit"
                    @submit-and-new="submitAndNew"
                    @cancel="cancelForm"
                />

            </Card>

        </div>


        <!-- ========================================================= -->
        <!-- SHOW -->
        <!-- ========================================================= -->

        <div
            v-else-if="view === 'show'"
            key="show"
            class="space-y-6"
        >

            <PageHeader
                icon="💳"
                title="Purchase Payment"
                subtitle="Purchase payment details."
            />
            
           
        </div>

    </Transition>

</AppLayout>



<!-- =============================================================== -->
<!-- Modals -->
<!-- =============================================================== -->

<ConfirmDeleteModal
    :show="showDelete"
    title="Delete Purchase Payment"
    :message="deleteMessage"
    confirm-text="Delete"
    @close="closeDelete"
    @confirm="confirmDelete"
/>
<PurchasePaymentViewModal
    :show="showView"
    :purchase-payment="viewItem"
    :loading="viewLoading"
    @close="closeView"
/>
<PurchasePaymentSubmitModal
    :show="showSubmitModal"
    :purchase-payment="selectedPurchasePayment"
    :loading="submitLoading"
    @close="closeSubmit"
    @confirm="confirmSubmit"
/>


<PurchasePaymentApproveModal
    :show="showApprove"
    :purchase-payment="approveItem"
    :loading="false"
    @close="closeApprove"
    @confirm="confirmApprove"
/>


<PurchasePaymentRejectModal
    :show="showReject"
    :purchase-payment="rejectItem"
    :reason="rejectReason"
    :loading="false"
    @close="closeReject"
    @update:reason="
        rejectReason = $event
    "
    @confirm="confirmReject"
/>


<PurchasePaymentPostModal
    :show="showPost"
    :purchase-payment="postItem"
    :loading="false"
     @close="closePost"
    @confirm="confirmPost"
   
/>
<PurchasePaymentCancelModal
    :show="showCancelModal"
    :purchase-payment="selectedPurchasePayment"
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