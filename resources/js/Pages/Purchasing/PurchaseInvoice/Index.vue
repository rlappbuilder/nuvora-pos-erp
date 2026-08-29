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
import { success, error, formatDate,} from '@/Utils'
import FlatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'
import PurchaseInvoiceForm from './Partials/PurchaseInvoiceForm.vue'
import PurchaseInvoiceViewModal from './Partials/PurchaseInvoiceViewModal.vue'
import PurchaseInvoiceSubmitModal from './Partials/PurchaseInvoiceSubmitModal.vue'
import PurchaseInvoiceApproveModal from './Partials/PurchaseInvoiceApproveModal.vue'
import PurchaseInvoiceRejectModal from './Partials/PurchaseInvoiceRejectModal.vue'
import PurchaseInvoicePostModal from './Partials/PurchaseInvoicePostModal.vue'

import ConfirmDeleteModal from '@/Components/Modal/ConfirmDeleteModal.vue'


const props = defineProps({

    purchaseInvoices: {

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

    branches: {

        type: Array,

        default: () => [],

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

    goodsReceipts: {

        type: Array,

        default: () => [],

    },

    paymentTerms: {

        type: Array,

        default: () => [],

    },

    currencies: {

        type: Array,

        default: () => [],

    },

    taxes: {

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
    purchaseInvoices,
    statistics,
} = toRefs(props)

const defaultCurrencyId = computed(() => {

    return props.currencies?.find(
        currency =>
            String(
                currency.code ?? ''
            ).toUpperCase() === 'IDR'
    )?.id ?? null

})
const defaultTaxId = computed(() => {

    const tax =
        props.taxes?.find(
            tax =>
                String(
                    tax.code ?? ''
                ).toUpperCase() === 'TAX0004'
        )

    return tax
        ? tax.id
        : null

})

/*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/

const pageTitle = computed(
    () => 'Purchase Invoice'
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
            'purchase-invoices.index'
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
        purchaseInvoices
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
        purchaseInvoices
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
            purchaseInvoices
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

    goods_receipt_detail_id:
        null,

    product_variant_id:
        null,

    unit_id:
        null,

    ordered_qty:
        0,

    received_qty:
        0,

    invoiced_qty:
        0,

    unit_price:
        0,

    discount_amount:
        0,

    tax_amount:
        0,

    subtotal:
        0,

    total_amount:
        0,

    remarks:
        null,

})


const form = useForm({
    number:props.previewNumber ??'',

    invoice_number:
        '',

    purchase_order_id:
        null,

    goods_receipt_id:
        null,

    supplier_id:
        null,

    warehouse_id:
        null,
    branch_id:
    null,
    payment_term_id:
        null,

    currency_id:
    defaultCurrencyId.value,

    tax_id:
        defaultTaxId.value,

    invoice_date:
        new Date()
            .toISOString()
            .slice(0, 10),

    due_date:
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


    form.number =
        props.previewNumber ?? ''


    form.invoice_number =
        ''


    form.branch_id =
        null


    form.goods_receipt_id =
        null


    form.purchase_order_id =
        null


    form.supplier_id =
        null


    form.warehouse_id =
        null


    form.invoice_date =
        new Date()
            .toISOString()
            .slice(0, 10)


    form.due_date =
        null


    form.payment_term_id =
        null


    form.currency_id =
        defaultCurrencyId.value


    form.tax_id =
        defaultTaxId.value


    form.status =
        'Draft'


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
                'purchase-invoices.store'
            ),

            {

                preserveScroll: true,

                onSuccess: () => {

                    success(
                        'Success',
                        'Purchase invoice created successfully.'
                    )


                    view.value =
                        'list'

                },

               onError: (errors) => {

                    console.error(
                        'CREATE PURCHASE INVOICE ERRORS:',
                        JSON.stringify(
                            errors,
                            null,
                            2
                        )
                    )

                },

            }

        )


        return

    }


    form.put(

        route(

            'purchase-invoices.update',

            editingItem.value.id

        ),

        {

            preserveScroll: true,

            onSuccess: () => {

                success(
                    'Success',
                    'Purchase invoice updated successfully.'
                )


                view.value =
                    'list'

            },

            onError: (errors) => {

                console.error(
                    'UPDATE PURCHASE INVOICE ERRORS:',
                    errors
                )


                error(
                    'Failed to update purchase invoice.'
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

function editPurchaseInvoice(item)
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
            'Only Draft or Rejected purchase invoice can be edited.'
        )

        return

    }


    formMode.value =
        'edit'


    editingItem.value =
        item


    form.clearErrors()


    form.invoice_number =
        item.invoice_number


    form.purchase_order_id =
        item.purchase_order_id


    form.goods_receipt_id =
        item.goods_receipt_id


    form.supplier_id =
        item.supplier_id


    form.warehouse_id =
        item.warehouse_id
        
    form.company_id =
    item.company_id ?? null

    form.branch_id =
    item.branch_id ?? null

    form.payment_term_id =
        item.payment_term_id


    form.currency_id =
        item.currency_id


    form.tax_id =
        item.tax_id


    form.invoice_date =
        item.invoice_date
            ? String(
                item.invoice_date
            ).slice(0, 10)
            : null


    form.due_date =
        item.due_date
            ? String(
                item.due_date
            ).slice(0, 10)
            : null


    form.remarks =
        item.remarks


    form.details =
        item.details?.map(
            detail => ({

                purchase_order_detail_id:
                    detail.purchase_order_detail_id,

                goods_receipt_detail_id:
                    detail.goods_receipt_detail_id,

                product_variant_id:
                    detail.product_variant_id,

                unit_id:
                    detail.unit_id,

                ordered_qty:
                    detail.ordered_qty ?? 0,

                received_qty:
                    detail.received_qty ?? 0,

                invoiced_qty:
                    detail.invoiced_qty ?? 0,

                unit_price:
                    detail.unit_price ?? 0,

                discount_amount:
                    detail.discount_amount ?? 0,

                tax_amount:
                    detail.tax_amount ?? 0,

                subtotal:
                    detail.subtotal ?? 0,

                total_amount:
                    detail.total_amount ?? 0,

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
                    'purchase-invoices.data',
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
            'PURCHASE INVOICE VIEW RESPONSE:',
            responseData
        )


        viewItem.value =
            responseData.data


    } catch (
        exception
    ) {

        console.error(
            'PURCHASE INVOICE VIEW ERROR:',
            exception
        )


        showView.value =
            false


        viewItem.value =
            null


        error(
            'Failed to load purchase invoice detail.'
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
            'Only Draft or Rejected purchase invoice can be deleted.'
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
            'purchase-invoices.destroy',
            deleteItem.value.id
        ),

        {

            preserveScroll: true,

            onSuccess: () => {

                closeDelete()


                success(
                    'Success',
                    'Purchase invoice deleted successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to delete purchase invoice.'
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


        return `Are you sure you want to delete ${total} selected Purchase Invoice document(s)?`

    })


function bulkDelete()
{

    router.delete(

        route(
            'purchase-invoices.bulk-delete'
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
                    'Purchase invoices deleted successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to delete purchase invoices.'
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
            'purchase-invoices.submit',
            submitItem.value.id
        ),

        {},

        {

            preserveScroll: true,

            onSuccess: () => {

                closeSubmit()


                success(
                    'Success',
                    'Purchase invoice submitted successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to submit purchase invoice.'
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
            'purchase-invoices.approve',
            approveItem.value.id
        ),

        {},

        {

            preserveScroll: true,

            onSuccess: () => {

                closeApprove()


                success(
                    'Success',
                    'Purchase invoice approved successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to approve purchase invoice.'
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
            'purchase-invoices.reject',
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
                    'Purchase invoice rejected successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to reject purchase invoice.'
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
                    'purchase-invoices.data',
                    item.id
                )

            )


        postItem.value =
            response.data.data


        showPost.value =
            true


    } catch (err) {

        console.error(
            'FAILED TO LOAD PURCHASE INVOICE:',
            err
        )


        error(
            'Failed to load purchase invoice details.'
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
            'purchase-invoices.post',
            postItem.value.id
        ),

        {},

        {

            preserveScroll: true,

            onSuccess: () => {

                closePost()


                success(
                    'Success',
                    'Purchase invoice posted successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to post purchase invoice.'
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
            'purchase-invoices.index'
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
                        title="Total Purchase Invoice"
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
                                placeholder="Search internal number, supplier invoice number, PO number, GRN number..."
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

                            <!-- Invoice Date -->

                            <FlatPickr
                                v-model="filters.date_range"
                                :config="{
                                    mode: 'range',
                                    dateFormat: 'Y-m-d',
                                    allowInput: true,
                                }"
                                placeholder="Invoice Date"
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
                            text="Loading Purchase Invoice..."
                        />


                        <DataTable
                            v-if="purchaseInvoices?.data?.length"
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


                                <!-- Internal Number -->

                                <DataTableHeaderCell
                                    sortable
                                    column="number"
                                    :sort="sort"
                                    :direction="direction"
                                    @sort="sortBy"
                                    width="180px"
                                >
                                    Internal Number
                                </DataTableHeaderCell>


                                <!-- Supplier Invoice Number -->

                                <DataTableHeaderCell
                                    sortable
                                    column="invoice_number"
                                    :sort="sort"
                                    :direction="direction"
                                    @sort="sortBy"
                                    width="200px"
                                >
                                    Supplier Invoice Number
                                </DataTableHeaderCell>


                                <!-- Invoice Date -->

                                <DataTableHeaderCell
                                    sortable
                                    column="invoice_date"
                                    :sort="sort"
                                    :direction="direction"
                                    @sort="sortBy"
                                    width="150px"
                                >
                                    Invoice Date
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


                                <!-- Grand Total -->

                                <DataTableHeaderCell
                                    width="150px"
                                    align="right"
                                >
                                    Grand Total
                                </DataTableHeaderCell>


                                <!-- Outstanding -->

                                <DataTableHeaderCell
                                    width="150px"
                                    align="right"
                                >
                                    Outstanding
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
                                        purchaseInvoices?.data ?? []
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


                                    <!-- Internal Number -->

                                    <DataTableCell>

                                        <span class="font-medium">
                                            {{
                                                item.number
                                                ?? '-'
                                            }}
                                        </span>

                                    </DataTableCell>


                                    <!-- Supplier Invoice Number -->

                                    <DataTableCell>

                                        <span class="font-medium">
                                            {{
                                                item.invoice_number
                                                ?? '-'
                                            }}
                                        </span>

                                    </DataTableCell>


                                    <!-- Invoice Date -->

                                    <DataTableCell>

                                        {{
                                            item.invoice_date
                                                ? formatDate(
                                                    item.invoice_date
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


                                    <!-- Grand Total -->

                                    <DataTableCell align="right">

                                        {{
                                            formatCurrency(
                                                item.grand_total ?? 0
                                            )
                                        }}

                                    </DataTableCell>


                                    <!-- Outstanding -->

                                    <DataTableCell align="right">

                                        {{
                                            formatCurrency(
                                                item.outstanding_amount ?? 0
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
                                                editPurchaseInvoice(item)
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

                                            :showCancel="false"

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
                            icon="🧾"
                            title="No Purchase Invoice Found"
                            description="There are no Purchase Invoice transactions available."
                        >

                            <template #action>

                                <BaseButton
                                    @click="create"
                                >
                                    Create Purchase Invoice
                                </BaseButton>

                            </template>

                        </TableEmpty>

                    </div>


                    <!-- ================================================= -->
                    <!-- Pagination -->
                    <!-- ================================================= -->

                    <div class="mt-6">

                        <TablePagination
                            :data="purchaseInvoices"
                            label="Purchase Invoice"
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
                icon="🧾"
                :title="
                    formMode === 'create'
                        ? 'Create Purchase Invoice'
                        : 'Edit Purchase Invoice'
                "
                :subtitle="
                    formMode === 'create'
                        ? 'Create a new purchase invoice.'
                        : 'Update purchase invoice.'
                "
            />


            <Card>

                <PurchaseInvoiceForm
                    :form="form"
                    :goods-receipts="goodsReceipts"
                    :suppliers="suppliers"
                    :warehouses="warehouses"
                    :payment-terms="paymentTerms"
                    :currencies="currencies"
                    :taxes="taxes"
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
    title="Delete Purchase Invoice"
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
    title="Delete Purchase Invoices"
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

<PurchaseInvoiceViewModal
    :show="showView"
    :purchase-invoice="viewItem"
    :loading="viewLoading"
    @close="closeView"
/>


<!-- =============================================================== -->
<!-- Submit -->
<!-- =============================================================== -->

<PurchaseInvoiceSubmitModal
    :show="showSubmit"
    :purchase-invoice="submitItem"
    :loading="submitLoading"
    @close="closeSubmit"
    @confirm="confirmSubmit"
/>


<!-- =============================================================== -->
<!-- Approve -->
<!-- =============================================================== -->

<PurchaseInvoiceApproveModal
    :show="showApprove"
    :purchase-invoice="approveItem"
    :loading="approveLoading"
    @close="closeApprove"
    @confirm="confirmApprove"
/>


<!-- =============================================================== -->
<!-- Reject -->
<!-- =============================================================== -->

<PurchaseInvoiceRejectModal
    :show="showReject"
    :purchase-invoice="rejectItem"
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

<PurchaseInvoicePostModal
    :show="showPost"
    :purchase-invoice="postItem"
    :loading="postLoading"
    @close="closePost"
    @confirm="confirmPost"
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