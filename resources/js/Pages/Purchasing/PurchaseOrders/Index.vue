<script setup>

import {
    ref,
    reactive,
    computed,
    watch,
    onMounted,
    onUnmounted,
    toRefs,
    nextTick,
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
    FunnelIcon,
    ArrowPathIcon,
} from '@heroicons/vue/24/outline'

import {
    success,
    error,
    formatDate,
} from '@/Utils'

import FlatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'

import PurchaseOrderForm from './Partials/PurchaseOrderForm.vue'
import PurchaseOrderViewModal from './Partials/PurchaseOrderViewModal.vue'
import PurchaseOrderApproveModal from './Partials/PurchaseOrderApproveModal.vue'
import PurchaseOrderSubmitModal from './Partials/PurchaseOrderSubmitModal.vue'
import PurchaseOrderRejectModal from './Partials/PurchaseOrderRejectModal.vue'
import PurchaseOrderSendModal from './Partials/PurchaseOrderSendModal.vue'
import PurchaseOrderConfirmModal from './Partials/PurchaseOrderConfirmModal.vue'
import ConfirmDeleteModal from '@/Components/Modal/ConfirmDeleteModal.vue'
import PurchaseOrderCancelModal from './Partials/PurchaseOrderCancelModal.vue'


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    purchaseOrders: {
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
            sent: 0,
            confirmed: 0,
            partially_received: 0,
            fully_received: 0,
            cancelled: 0,
            closed: 0,
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

    suppliers: {
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

    show: {
        type: Boolean,
        default: false,
    },

    purchaseOrder: {
        type: Object,
        default: null,
    },

    loading: {
        type: Boolean,
        default: false,
    },

})


const {
    purchaseOrders,
    statistics,
} = toRefs(props)


/*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/

const pageTitle = computed(
    () => 'Purchase Orders'
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
| Filter Visibility
|--------------------------------------------------------------------------
*/

const showFilters = ref(false)

const suspendFilterWatchers = ref(false)

const skipWarehouseWatch = ref(false)


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

})


const dateRange = ref(

    filters.date_from && filters.date_to

        ? `${filters.date_from} to ${filters.date_to}`

        : filters.date_from || filters.date_to || ''

)


let debounceTimer = null


/*
|--------------------------------------------------------------------------
| Active Filter
|--------------------------------------------------------------------------
*/

const hasActiveFilter = computed(() => {

    return Boolean(

        filters.date_from ||
        filters.date_to ||
        filters.branch_id ||
        filters.warehouse_id ||
        filters.supplier_id ||
        filters.status

    )

})


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
        value: 'Sent',
        label: 'Sent',
    },

    {
        value: 'Confirmed',
        label: 'Confirmed',
    },

    {
        value: 'Partially Received',
        label: 'Partially Received',
    },

    {
        value: 'Fully Received',
        label: 'Fully Received',
    },

    {
        value: 'Closed',
        label: 'Closed',
    },

]


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


/*
|--------------------------------------------------------------------------
| Load Data
|--------------------------------------------------------------------------
*/

function loadData() {

    router.get(

        route('purchase-orders.index'),

        {

            ...filters,

            sort_by:
                sort.value,

            sort_direction:
                direction.value,

            page: 1,

        },

        {

            preserveState: true,

            preserveScroll: true,

            replace: true,

            onStart: () => {
                loading.value = true
            },

            onFinish: () => {
                loading.value = false
            },

        }

    )

}


/*
|--------------------------------------------------------------------------
| Date Formatting
|--------------------------------------------------------------------------
*/

function formatDateForFilter(date) {

    const year = date.getFullYear()

    const month = String(
        date.getMonth() + 1
    ).padStart(2, '0')

    const day = String(
        date.getDate()
    ).padStart(2, '0')

    return `${year}-${month}-${day}`

}


/*
|--------------------------------------------------------------------------
| Date Range Change
|--------------------------------------------------------------------------
*/

function handleDateRangeChange(selectedDates) {

    if (!selectedDates.length) {

        filters.date_from = ''

        filters.date_to = ''

        loadData()

        return

    }

    filters.date_from = formatDateForFilter(
        selectedDates[0]
    )

    filters.date_to = selectedDates.length > 1

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
| Date Range Configuration
|--------------------------------------------------------------------------
*/

const dateRangeConfig = {

    mode: 'range',

    dateFormat: 'Y-m-d',

    allowInput: true,

    onChange: handleDateRangeChange,

}


/*
|--------------------------------------------------------------------------
| Filter Watchers
|--------------------------------------------------------------------------
*/

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

    (newValue, oldValue) => {

        if (

            suspendFilterWatchers.value ||

            newValue === oldValue

        ) {
            return
        }

        if (filters.warehouse_id) {

            skipWarehouseWatch.value = true

            filters.warehouse_id = ''

        }

        loadData()

    }

)


watch(

    () => filters.warehouse_id,

    (newValue, oldValue) => {

        if (

            suspendFilterWatchers.value ||

            newValue === oldValue

        ) {
            return
        }

        if (skipWarehouseWatch.value) {

            skipWarehouseWatch.value = false

            return

        }

        loadData()

    }

)


watch(

    () => filters.supplier_id,

    (newValue, oldValue) => {

        if (

            suspendFilterWatchers.value ||

            newValue === oldValue

        ) {
            return
        }

        loadData()

    }

)


watch(

    () => filters.status,

    (newValue, oldValue) => {

        if (

            suspendFilterWatchers.value ||

            newValue === oldValue

        ) {
            return
        }

        loadData()

    }

)


watch(

    () => filters.per_page,

    (newValue, oldValue) => {

        if (

            suspendFilterWatchers.value ||

            newValue === oldValue

        ) {
            return
        }

        loadData()

    }

)


/*
|--------------------------------------------------------------------------
| Filtered Warehouses
|--------------------------------------------------------------------------
*/

const filteredFilterWarehouses = computed(() => {

    if (!filters.branch_id) {

        return props.warehouses

    }

    return props.warehouses.filter(

        warehouse =>

            Number(warehouse.branch_id) ===

            Number(filters.branch_id)

    )

})


/*
|--------------------------------------------------------------------------
| Reset Filters
|--------------------------------------------------------------------------
*/

async function resetFilters() {

    clearTimeout(debounceTimer)

    suspendFilterWatchers.value = true

    skipWarehouseWatch.value = false

    Object.assign(filters, {

        search: '',

        branch_id: '',

        warehouse_id: '',

        supplier_id: '',

        status: '',

        per_page: 10,

        date_from: '',

        date_to: '',

    })

    dateRange.value = ''

    await nextTick()

    suspendFilterWatchers.value = false

    loadData()

}


/*
|--------------------------------------------------------------------------
| Refresh
|--------------------------------------------------------------------------
*/

function refresh() {

    resetFilters()

}


/*
|--------------------------------------------------------------------------
| Selection
|--------------------------------------------------------------------------
*/

const selectedRows = ref([])

const selectAllRef = ref(null)


const isAllSelected = computed(() => {

    const totalRows =
        purchaseOrders.value?.data?.length ?? 0

    return (

        totalRows > 0 &&

        selectedRows.value.length === totalRows

    )

})


const isIndeterminate = computed(() => {

    const totalRows =
        purchaseOrders.value?.data?.length ?? 0

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


function toggleSelectAll(event) {

    if (event.target.checked) {

        selectedRows.value =

            purchaseOrders.value?.data?.map(

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

    received_qty: 0,

    remaining_qty: 0,

    unit_price: 0,

    discount_rate: 0,

    discount_amount: 0,

    tax_rate: 0,

    tax_amount: 0,

    subtotal: 0,

    total: 0,

    description: null,

})


const form = useForm({

    number:
        props.previewNumber ?? '',

    company_id: null,

    branch_id: null,

    warehouse_id: null,

    supplier_id: null,

    purchase_request_id: null,

    order_date:
        new Date().toISOString().slice(0, 10),

    required_date: null,

    description: null,

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

function create() {

    isEditing.value = false

    formMode.value = 'create'

    editingItem.value = null

    form.reset()

    form.clearErrors()

    form.number =
        props.previewNumber ?? ''

    form.company_id =
        props.companyId ?? null

    form.order_date =
        new Date().toISOString().slice(0, 10)

    form.required_date = null

    form.supplier_id = null

    form.purchase_request_id = null

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

function cancelForm() {

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

function submit() {

    if (formMode.value === 'create') {

        form.post(

            route('purchase-orders.store'),

            {

                preserveScroll: true,

                onSuccess: () => {

                    success(
                        'Success',
                        'Purchase order created successfully.'
                    )

                    view.value = 'list'

                },

            }

        )

        return

    }


    form.put(

        route(
            'purchase-orders.update',
            editingItem.value.id
        ),

        {

            preserveScroll: true,

            onSuccess: () => {

                success(
                    'Success',
                    'Purchase order updated successfully.'
                )

                view.value = 'list'

            },

            onError: (errors) => {

                console.error(
                    'UPDATE ERRORS:',
                    errors
                )

                error(
                    'Failed to update purchase order.'
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

function submitAndNew() {

    form.post(

        route('purchase-orders.store'),

        {

            preserveScroll: true,

            onSuccess: () => {

                success(
                    'Success',
                    'Purchase order created successfully.'
                )

                form.reset()

                form.clearErrors()

                form.number =
                    props.previewNumber ?? ''

                form.company_id =
                    props.companyId

                form.order_date =
                    new Date().toISOString().slice(0, 10)

                form.required_date = null

                form.supplier_id = null

                form.purchase_request_id = null

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

function editPurchaseOrder(item) {

    if (

        ![
            'Draft',
            'Rejected',
        ].includes(item.status)

    ) {

        error(
            'Only Draft or Rejected purchase order can be edited.'
        )

        return

    }


    isEditing.value = true

    editingItem.value = item

    formMode.value = 'edit'

    form.clearErrors()

    form.number = item.number

    form.company_id = item.company_id

    form.branch_id = item.branch_id

    form.warehouse_id = item.warehouse_id

    form.supplier_id = item.supplier_id

    form.purchase_request_id = item.purchase_request_id


    form.order_date = item.order_date

        ? String(item.order_date).slice(0, 10)

        : null


    form.required_date = item.required_date

        ? String(item.required_date).slice(0, 10)

        : null


    form.description = item.description


    form.details = item.details?.map(

        detail => ({

            purchase_request_detail_id:
                detail.purchase_request_detail_id ?? null,

            product_variant_id:
                detail.product_variant_id,

            unit_id:
                detail.unit_id,

            qty:
                detail.qty,

            received_qty:
                detail.received_qty ?? 0,

            remaining_qty:
                detail.remaining_qty ?? detail.qty,

            unit_price:
                detail.unit_price ?? 0,

            discount_rate:
                detail.discount_rate ?? 0,

            discount_amount:
                detail.discount_amount ?? 0,

            tax_rate:
                detail.tax_rate ?? 0,

            tax_amount:
                detail.tax_amount ?? 0,

            subtotal:
                detail.subtotal ?? 0,

            total:
                detail.total ?? 0,

            description:
                detail.description,

        })

    ) ?? [

        createEmptyDetail(),

    ]


    view.value = 'form'


    nextTick(() => {

        form.warehouse_id = item.warehouse_id

        isEditing.value = false

    })

}


/*
|--------------------------------------------------------------------------
| Show
|--------------------------------------------------------------------------
*/

const selectedItem = ref(null)


function showPurchaseOrder(item) {

    selectedItem.value = item

    view.value = 'show'

}


/*
|--------------------------------------------------------------------------
| Back From Show
|--------------------------------------------------------------------------
*/

function backToList() {

    selectedItem.value = null

    view.value = 'list'

}


/*
|--------------------------------------------------------------------------
| Duplicate
|--------------------------------------------------------------------------
*/

function duplicate(item) {

    router.post(

        route(
            'purchase-orders.duplicate',
            item.id
        ),

        {},

        {

            preserveScroll: true,

            onSuccess: () => {

                success(
                    'Success',
                    'Purchase order duplicated successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to duplicate purchase order.'
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


function openDelete(item) {

    if (item.status !== 'Draft') {

        error(
            'Only Draft purchase order can be deleted.'
        )

        return

    }

    deleteItem.value = item

    showDelete.value = true

}


function closeDelete() {

    deleteItem.value = null

    showDelete.value = false

}


const deleteMessage = computed(() => {

    if (!deleteItem.value) {

        return ''

    }

    return `Are you sure you want to delete "${deleteItem.value.number}"?`

})


function confirmDelete() {

    if (!deleteItem.value) {

        return

    }

    router.delete(

        route(
            'purchase-orders.destroy',
            deleteItem.value.id
        ),

        {

            preserveScroll: true,

            onSuccess: () => {

                closeDelete()

                success(
                    'Success',
                    'Purchase order deleted successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to delete purchase order.'
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


function openBulkDelete() {

    if (!selectedRows.value.length) {

        return

    }

    showBulkDelete.value = true

}


const bulkDeleteMessage = computed(() => {

    const total = selectedRows.value.length

    if (!total) {

        return ''

    }

    return `Are you sure you want to delete ${total} selected Purchase Order document(s)?`

})


function bulkDelete() {

    router.delete(

        route('purchase-orders.bulk-delete'),

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
                    'Purchase orders deleted successfully.'
                )

            },

        }

    )

}


/*
|--------------------------------------------------------------------------
| Submit Modal
|--------------------------------------------------------------------------
*/

const submitItem = ref(null)

const showSubmitModal = ref(false)

const selectedPurchaseOrder = ref(null)

const submitLoading = ref(false)


const openSubmit = (purchaseOrder) => {

    selectedPurchaseOrder.value = purchaseOrder

    showSubmitModal.value = true

}


const closeSubmit = () => {

    showSubmitModal.value = false

    selectedPurchaseOrder.value = null

}


const confirmSubmit = () => {

    if (!selectedPurchaseOrder.value) {

        return

    }

    submitLoading.value = true

    router.post(

        route(
            'purchasing.purchase-orders.submit',
            selectedPurchaseOrder.value.id
        ),

        {},

        {

            preserveScroll: true,

            onSuccess: () => {

                closeSubmit()

            },

            onError: (errors) => {

                console.error(
                    'SUBMIT PO ERROR:',
                    errors
                )

            },

            onFinish: () => {

                submitLoading.value = false

            },

        }

    )

}


const submitMessage = computed(() => {

    if (!submitItem.value) {

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


function openApprove(purchaseOrder) {

    approveItem.value = purchaseOrder

    showApprove.value = true

}


function closeApprove() {

    approveItem.value = null

    showApprove.value = false

}


function confirmApprove() {

    if (!approveItem.value) {

        return

    }

    router.post(

        route(
            'purchase-orders.approve',
            approveItem.value.id
        ),

        {},

        {

            preserveScroll: true,

            onSuccess: () => {

                closeApprove()

                success(
                    'Success',
                    'Purchase order approved successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to approve purchase order.'
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


function openReject(purchaseOrder) {

    rejectItem.value = purchaseOrder

    rejectReason.value = ''

    showReject.value = true

}


function closeReject() {

    rejectItem.value = null

    rejectReason.value = ''

    showReject.value = false

}


function confirmReject() {

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
            'purchase-orders.reject',
            rejectItem.value.id
        ),

        {

            reason: rejectReason.value.trim(),

        },

        {

            preserveScroll: true,

            onSuccess: () => {

                closeReject()

                success(
                    'Success',
                    'Purchase order rejected successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to reject purchase order.'
                )

            },

        }

    )

}


/*
|--------------------------------------------------------------------------
| Send
|--------------------------------------------------------------------------
*/

const sendItem = ref(null)

const showSend = ref(false)


function openSend(purchaseOrder) {

    sendItem.value = purchaseOrder

    showSend.value = true

}


function closeSend() {

    sendItem.value = null

    showSend.value = false

}


function confirmSend() {

    if (!sendItem.value) {

        return

    }

    router.post(

        route(
            'purchase-orders.send',
            sendItem.value.id
        ),

        {},

        {

            preserveScroll: true,

            onSuccess: () => {

                closeSend()

                success(
                    'Success',
                    'Purchase order sent to supplier successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to send purchase order.'
                )

            },

        }

    )

}


const sendMessage = computed(() => {

    if (!sendItem.value) {

        return ''

    }

    return `Are you sure you want to send "${sendItem.value.number}" to supplier?`

})


/*
|--------------------------------------------------------------------------
| Confirm
|--------------------------------------------------------------------------
*/

const confirmItem = ref(null)

const showConfirm = ref(false)


function openConfirm(purchaseOrder) {

    confirmItem.value = purchaseOrder

    showConfirm.value = true

}


function closeConfirm() {

    confirmItem.value = null

    showConfirm.value = false

}


function confirmPurchaseOrder() {

    if (!confirmItem.value) {

        return

    }

    router.post(

        route(
            'purchase-orders.confirm',
            confirmItem.value.id
        ),

        {},

        {

            preserveScroll: true,

            onSuccess: () => {

                closeConfirm()

                success(
                    'Success',
                    'Purchase order confirmed successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to confirm purchase order.'
                )

            },

        }

    )

}


const confirmMessage = computed(() => {

    if (!confirmItem.value) {

        return ''

    }

    return `Are you sure you want to confirm "${confirmItem.value.number}"?`

})


/*
|--------------------------------------------------------------------------
| Sorting
|--------------------------------------------------------------------------
*/

function sortBy(column) {

    if (sort.value === column) {

        direction.value =
            direction.value === 'asc'
                ? 'desc'
                : 'asc'

    } else {

        sort.value = column

        direction.value = 'asc'

    }

    loadData()

}


/*
|--------------------------------------------------------------------------
| Filtered Warehouse For Form
|--------------------------------------------------------------------------
*/

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

        form.warehouse_id = null

    }

)


/*
|--------------------------------------------------------------------------
| Action Permissions
|--------------------------------------------------------------------------
*/

function canEdit(item) {

    return [

        'Draft',
        'Rejected',

    ].includes(item.status)

}


function canSubmit(item) {

    return item.status === 'Draft'

}


function canApprove(item) {

    return item.status === 'Submitted'

}


function canReject(item) {

    return item.status === 'Submitted'

}


function canSend(item) {

    return item.status === 'Approved'

}


function canConfirm(item) {

    return item.status === 'Sent'

}


function canCancel(item) {

    return [

        'Submitted',
        'Approved',
        'Sent',
        'Confirmed',

    ].includes(item.status)

}


function canDelete(item) {

    return [

        'Draft',
        'Rejected',

    ].includes(item.status)

}


/*
|--------------------------------------------------------------------------
| View Modal
|--------------------------------------------------------------------------
*/

const viewLoading = ref(false)

const showView = ref(false)

const viewItem = ref(null)


async function openView(item) {

    viewLoading.value = true

    viewItem.value = null

    showView.value = true

    try {

        const response = await fetch(

            route(
                'purchase-orders.data',
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


        const responseData = await response.json()


        console.log(
            'PURCHASE ORDER VIEW RESPONSE:',
            responseData
        )


        viewItem.value = responseData.data


    } catch (exception) {

        console.error(
            'PURCHASE ORDER VIEW ERROR:',
            exception
        )

        showView.value = false

        viewItem.value = null

        error(
            'Failed to load purchase order detail.'
        )

    } finally {

        viewLoading.value = false

    }

}


function closeView() {

    viewItem.value = null

    showView.value = false

}


/*
|--------------------------------------------------------------------------
| Export
|--------------------------------------------------------------------------
*/

function exportSelected() {

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

            style: 'currency',

            currency: 'IDR',

            minimumFractionDigits: 2,

            maximumFractionDigits: 2,

        }

    ).format(

        Number(value || 0)

    )

}


/*
|--------------------------------------------------------------------------
| Cancel
|--------------------------------------------------------------------------
*/

const showCancelModal = ref(false)

const cancelLoading = ref(false)

const cancelReason = ref('')


function openCancel(item) {

    selectedPurchaseOrder.value = item

    cancelReason.value = ''

    showCancelModal.value = true

}


function closeCancel() {

    showCancelModal.value = false

    selectedPurchaseOrder.value = null

    cancelReason.value = ''

}


function confirmCancel() {

    if (!selectedPurchaseOrder.value) {

        return

    }

    if (!cancelReason.value.trim()) {

        return

    }

    cancelLoading.value = true

    router.post(

        route(
            'purchasing.purchase-orders.cancel',
            selectedPurchaseOrder.value.id
        ),

        {

            reason: cancelReason.value,

        },

        {

            preserveScroll: true,

            onFinish: () => {

                cancelLoading.value = false

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

    removeStartListener = router.on(
        'start',
        startLoading
    )

    removeFinishListener = router.on(
        'finish',
        stopLoading
    )

})


onUnmounted(() => {

    clearTimeout(debounceTimer)

    removeStartListener?.()

    removeFinishListener?.()

})

</script>
<template>
    <AppLayout title="Purchase Orders">

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
                class="space-y-6"
            >

                <!-- ================================================= -->
                <!-- Breadcrumb -->
                <!-- ================================================= -->

                <div class="flex items-center gap-2 text-sm">

                    <span class="text-gray-500 dark:text-gray-400">
                        Purchasing
                    </span>

                    <span class="text-gray-400">/</span>

                    <span class="font-medium text-gray-900 dark:text-white">
                        Purchase Orders
                    </span>

                </div>


                <!-- ================================================= -->
                <!-- Page Header -->
                <!-- ================================================= -->

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

                    <div class="min-w-0">

                        <h1
                            class="
                                text-2xl
                                font-bold
                                text-gray-900
                                dark:text-white
                            "
                        >
                            Purchase Orders
                        </h1>

                        <p
                            class="
                                mt-1
                                text-sm
                                text-gray-600
                                dark:text-gray-400
                            "
                        >
                            Manage and monitor your purchase orders.
                        </p>

                    </div>


                    <!-- Header Actions -->

                    <div
                        class="
                            flex
                            w-full
                            flex-col
                            gap-2
                            sm:flex-row
                            lg:w-auto
                            sm:items-center
                        "
                    >

                        <!-- Create -->

                        <BaseButton
                            class="w-full shrink-0 sm:w-auto"
                            @click="create"
                        >

                            <template #icon>

                                <PlusIcon class="h-5 w-5" />

                            </template>

                            Create

                        </BaseButton>


                        <!-- Refresh -->

                        <BaseButton
                            variant="secondary"
                            class="w-full shrink-0 sm:w-auto"
                            @click="refresh"
                        >

                            <span
                                class="
                                    inline-flex
                                    items-center
                                    justify-center
                                    gap-2
                                    whitespace-nowrap
                                "
                            >

                                <ArrowPathIcon
                                    class="
                                        h-4
                                        w-4
                                        shrink-0
                                        text-emerald-600
                                        dark:text-emerald-400
                                    "
                                />

                                Refresh

                            </span>

                        </BaseButton>

                    </div>

                </div>


                <!-- ================================================= -->
                <!-- Statistics -->
                <!-- ================================================= -->

                <div
                    class="
                        grid
                        grid-cols-1
                        gap-4
                        sm:grid-cols-2
                        xl:grid-cols-4
                    "
                >

                    <!-- Total Purchase Orders -->

                    <StatsCard
                        title="Total Purchase Order"
                        :value="statistics?.total ?? 0"
                        icon="📋"
                        color="blue"
                    />


                    <!-- Draft -->

                    <StatsCard
                        title="Draft"
                        :value="statistics?.draft ?? 0"
                        icon="📝"
                        color="gray"
                    />


                    <!-- Submitted -->

                    <StatsCard
                        title="Submitted"
                        :value="statistics?.submitted ?? 0"
                        icon="📤"
                        color="indigo"
                    />


                    <!-- Approved -->

                    <StatsCard
                        title="Approved"
                        :value="statistics?.approved ?? 0"
                        icon="✅"
                        color="green"
                    />


                    <!-- Sent -->

                    <StatsCard
                        title="Sent"
                        :value="statistics?.sent ?? 0"
                        icon="📨"
                        color="purple"
                    />


                    <!-- Confirmed -->

                    <StatsCard
                        title="Confirmed"
                        :value="statistics?.confirmed ?? 0"
                        icon="🤝"
                        color="cyan"
                    />


                    <!-- Partially Received -->

                    <StatsCard
                        title="Partially Received"
                        :value="statistics?.partially_received ?? 0"
                        icon="📦"
                        color="yellow"
                    />


                    <!-- Fully Received -->

                    <StatsCard
                        title="Fully Received"
                        :value="statistics?.fully_received ?? 0"
                        icon="✔️"
                        color="emerald"
                    />

                </div>


                <!-- ================================================= -->
                <!-- Purchase Order List -->
                <!-- ================================================= -->

                <Card>

                    <!-- ============================================= -->
                    <!-- Toolbar -->
                    <!-- ============================================= -->

                    <div
                        class="
                            flex
                            flex-col
                            gap-3
                            border-b
                            border-gray-200
                            p-4
                            dark:border-gray-700
                            sm:flex-row
                            sm:items-center
                            sm:justify-between
                        "
                    >

                        <div class="min-w-0">

                            <h2
                                class="
                                    text-base
                                    font-semibold
                                    text-gray-900
                                    dark:text-white
                                "
                            >
                                Purchase Order List
                            </h2>

                            <p
                                class="
                                    mt-1
                                    text-sm
                                    text-gray-500
                                    dark:text-gray-400
                                "
                            >
                                Manage purchase order transactions.
                            </p>

                        </div>


                        <!-- Search, Filter & Bulk Actions -->

                        <div
                            class="
                                flex
                                w-full
                                flex-col
                                gap-2
                                sm:w-auto
                                sm:flex-row
                                sm:items-center
                            "
                        >

                            <!-- Search -->

                            <div class="relative w-full sm:w-64">

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    class="
                                        pointer-events-none
                                        absolute
                                        left-3
                                        top-1/2
                                        h-4
                                        w-4
                                        -translate-y-1/2
                                        text-gray-400
                                    "
                                >
                                    <circle cx="11" cy="11" r="7" />
                                    <path
                                        stroke-linecap="round"
                                        d="m20 20-4-4"
                                    />
                                </svg>

                                <input
                                    v-model="filters.search"
                                    type="text"
                                    placeholder="Search PO Number..."
                                    class="
                                        w-full
                                        rounded-lg
                                        border
                                        border-gray-300
                                        bg-white
                                        py-2
                                        pl-9
                                        pr-3
                                        text-sm
                                        shadow-sm
                                        transition
                                        placeholder:text-gray-400
                                        focus:border-blue-500
                                        focus:outline-none
                                        focus:ring-2
                                        focus:ring-blue-100
                                        dark:border-gray-700
                                        dark:bg-gray-800
                                        dark:text-white
                                        dark:placeholder:text-gray-500
                                    "
                                />

                            </div>


                            <!-- Filter -->

                            <BaseButton
                                variant="secondary"
                                class="w-full shrink-0 sm:w-auto"
                                @click="showFilters = !showFilters"
                            >

                                <span
                                    class="
                                        inline-flex
                                        items-center
                                        justify-center
                                        gap-2
                                        whitespace-nowrap
                                    "
                                >

                                    <FunnelIcon class="h-4 w-4 shrink-0" />

                                    Filter

                                    <span
                                        v-if="hasActiveFilter"
                                        class="
                                            h-2
                                            w-2
                                            shrink-0
                                            rounded-full
                                            bg-blue-500
                                        "
                                    ></span>

                                </span>

                            </BaseButton>


                            <!-- Bulk Actions -->

                            <BulkActionDropdown
                                :count="selectedRows.length"
                                :disabled="selectedRows.length === 0"
                                :actions="[
                                    'export',
                                    'delete',
                                ]"
                                @delete="openBulkDelete"
                                @export="exportSelected"
                                class="w-full sm:w-auto"
                            />

                        </div>

                    </div>


                   <!-- ============================================= -->
                    <!-- Filter Panel -->
                    <!-- ============================================= -->

                    <div
                        v-if="showFilters"
                        class="
                            border-b
                            border-gray-200
                            bg-gray-50
                            p-4
                            dark:border-gray-700
                            dark:bg-gray-800/50
                        "
                    >

                        <!-- Filter Header -->

                        <div
                            class="
                                mb-4
                                flex
                                flex-col
                                gap-3
                                sm:flex-row
                                sm:items-center
                                sm:justify-between
                            "
                        >

                            <div>

                                <h3
                                    class="
                                        text-sm
                                        font-semibold
                                        text-gray-900
                                        dark:text-white
                                    "
                                >
                                    Filter Purchase Orders
                                </h3>

                                <p
                                    class="
                                        mt-0.5
                                        text-xs
                                        text-gray-500
                                        dark:text-gray-400
                                    "
                                >
                                    Narrow down purchase orders using the filters below.
                                </p>

                            </div>


                            <!-- Reset Filter -->

                            <BaseButton
                                variant="secondary"
                                class="w-full shrink-0 sm:w-auto"
                                @click="resetFilters"
                            >

                                <span
                                    class="
                                        inline-flex
                                        items-center
                                        justify-center
                                        gap-2
                                    "
                                >

                                    <ArrowPathIcon
                                        class="
                                            h-4
                                            w-4
                                            text-emerald-600
                                            dark:text-emerald-400
                                        "
                                    />

                                    Reset Filter

                                </span>

                            </BaseButton>

                        </div>


                        <!-- Filter Fields -->

                        <div
                            class="
                                grid
                                grid-cols-1
                                gap-4
                                sm:grid-cols-2
                                xl:grid-cols-3
                            "
                        >

                            <!-- Date Range -->

                            <div class="min-w-0">

                                <label
                                    class="
                                        mb-1.5
                                        block
                                        text-sm
                                        font-medium
                                        text-gray-700
                                        dark:text-gray-300
                                    "
                                >
                                    Order Date
                                </label>

                                <FlatPickr
                                    v-model="dateRange"
                                    :config="dateRangeConfig"
                                    placeholder="Select date range"
                                    class="
                                        w-full
                                        rounded-lg
                                        border
                                        border-gray-300
                                        bg-white
                                        px-3
                                        py-2.5
                                        text-sm
                                        shadow-sm
                                        focus:border-blue-500
                                        focus:outline-none
                                        focus:ring-2
                                        focus:ring-blue-100
                                        dark:border-gray-700
                                        dark:bg-gray-800
                                        dark:text-white
                                    "
                                />

                            </div>


                            <!-- Branch -->

                            <div class="min-w-0">

                                <label
                                    class="
                                        mb-1.5
                                        block
                                        text-sm
                                        font-medium
                                        text-gray-700
                                        dark:text-gray-300
                                    "
                                >
                                    Branch
                                </label>

                                <SearchableSelect
                                    v-model="filters.branch_id"
                                    :options="branches"
                                    label="label"
                                    value-key="id"
                                    placeholder="All Branches"
                                />

                            </div>


                            <!-- Warehouse -->

                            <div class="min-w-0">

                                <label
                                    class="
                                        mb-1.5
                                        block
                                        text-sm
                                        font-medium
                                        text-gray-700
                                        dark:text-gray-300
                                    "
                                >
                                    Warehouse
                                </label>

                                <SearchableSelect
                                    v-model="filters.warehouse_id"
                                    :options="filteredFilterWarehouses"
                                    label="label"
                                    value-key="id"
                                    placeholder="All Warehouses"
                                />

                            </div>


                            <!-- Supplier -->

                            <div class="min-w-0">

                                <label
                                    class="
                                        mb-1.5
                                        block
                                        text-sm
                                        font-medium
                                        text-gray-700
                                        dark:text-gray-300
                                    "
                                >
                                    Supplier
                                </label>

                                <SearchableSelect
                                    v-model="filters.supplier_id"
                                    :options="suppliers"
                                    label="label"
                                    value-key="id"
                                    placeholder="All Suppliers"
                                />

                            </div>


                            <!-- Status -->

                            <div class="min-w-0">

                                <label
                                    class="
                                        mb-1.5
                                        block
                                        text-sm
                                        font-medium
                                        text-gray-700
                                        dark:text-gray-300
                                    "
                                >
                                    Status
                                </label>

                                <SearchableSelect
                                    v-model="filters.status"
                                    :options="statusOptions"
                                    label="label"
                                    value-key="value"
                                    placeholder="All Status"
                                />

                            </div>

                        </div>

                    </div>
                    <!-- ============================================= -->
                    <!-- Table -->
                    <!-- ============================================= -->

                    <div class="relative mt-6">

                        <!-- Loading -->

                        <LoadingOverlay
                            :show="loading"
                            text="Loading Purchase Orders..."
                        />


                        <!-- Data -->

                        <DataTable
                            v-if="purchaseOrders?.data?.length"
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


                                <!-- PO Number -->

                                <DataTableHeaderCell
                                    sortable
                                    column="number"
                                    :sort="sort"
                                    :direction="direction"
                                    @sort="sortBy"
                                    width="180px"
                                >
                                    PO Number
                                </DataTableHeaderCell>


                                <!-- Order Date -->

                                <DataTableHeaderCell
                                    sortable
                                    column="order_date"
                                    :sort="sort"
                                    :direction="direction"
                                    @sort="sortBy"
                                    width="150px"
                                >
                                    Order Date
                                </DataTableHeaderCell>


                                <!-- Supplier -->

                                <DataTableHeaderCell width="220px">
                                    Supplier
                                </DataTableHeaderCell>


                                <!-- Required Date -->

                                <DataTableHeaderCell
                                    sortable
                                    column="required_date"
                                    :sort="sort"
                                    :direction="direction"
                                    @sort="sortBy"
                                    width="150px"
                                >
                                    Required Date
                                </DataTableHeaderCell>


                                <!-- Location -->

                                <DataTableHeaderCell width="220px">
                                    Location
                                </DataTableHeaderCell>


                                <!-- Items -->

                                <DataTableHeaderCell
                                    width="90px"
                                    align="right"
                                >
                                    Items
                                </DataTableHeaderCell>


                                <!-- Total -->

                                <DataTableHeaderCell
                                    sortable
                                    column="grand_total"
                                    :sort="sort"
                                    :direction="direction"
                                    @sort="sortBy"
                                    width="160px"
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
                                    v-for="item in (purchaseOrders?.data ?? [])"
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


                                    <!-- PO Number -->

                                    <DataTableCell>

                                        <span class="font-medium">
                                            {{ item.number }}
                                        </span>

                                    </DataTableCell>


                                    <!-- Order Date -->

                                    <DataTableCell>

                                        {{
                                            item.order_date
                                                ? formatDate(item.order_date)
                                                : '-'
                                        }}

                                    </DataTableCell>


                                    <!-- Supplier -->

                                    <DataTableCell>

                                        <div class="font-medium">
                                            {{ item.supplier?.name ?? '-' }}
                                        </div>

                                        <div
                                            v-if="item.supplier?.supplier_code"
                                            class="text-xs text-gray-500"
                                        >
                                            {{ item.supplier.supplier_code }}
                                        </div>

                                    </DataTableCell>


                                    <!-- Required Date -->

                                    <DataTableCell>

                                        {{
                                            item.required_date
                                                ? formatDate(item.required_date)
                                                : '-'
                                        }}

                                    </DataTableCell>


                                    <!-- Location -->

                                    <DataTableCell>

                                        <div class="font-medium">
                                            {{ item.branch?.name ?? '-' }}
                                        </div>

                                        <div class="text-xs text-gray-500">
                                            {{ item.warehouse?.name ?? '-' }}
                                        </div>

                                    </DataTableCell>


                                    <!-- Items -->

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

                                        <span class="font-semibold">
                                            {{ formatCurrency(item.grand_total) }}
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
                                            @edit="editPurchaseOrder(item)"
                                            @duplicate="duplicate(item)"
                                            @submit="openSubmit(item)"
                                            @approve="openApprove(item)"
                                            @reject="openReject(item)"
                                            @send="openSend(item)"
                                            @confirm="openConfirm(item)"
                                            @cancel="openCancel(item)"
                                            @delete="openDelete(item)"

                                            :showEdit="canEdit(item)"
                                            :showDuplicate="true"
                                            :showSubmit="canSubmit(item)"
                                            :showApprove="canApprove(item)"
                                            :showReject="canReject(item)"
                                            :showSend="canSend(item)"
                                            :showConfirm="canConfirm(item)"
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
                            title="No Purchase Order Found"
                            description="There are no Purchase Order transactions available."
                        >

                            <template #action>

                                <BaseButton
                                    @click="create"
                                >
                                    Create Purchase Order
                                </BaseButton>

                            </template>

                        </TableEmpty>

                    </div>


                    <!-- ============================================= -->
                    <!-- Pagination -->
                    <!-- ============================================= -->

                    <div class="mt-6">

                        <TablePagination
                            :data="purchaseOrders"
                            label="Purchase Order"
                        />

                    </div>

                </Card>

            </div>


            <!-- ===================================================== -->
            <!-- FORM -->
            <!-- ===================================================== -->

            <div
                v-else-if="view === 'form'"
                key="form"
                class="space-y-6"
            >

                <!-- Breadcrumb -->

                <div class="flex flex-wrap items-center gap-2 text-sm">

                    <span class="text-gray-500 dark:text-gray-400">
                        Purchasing
                    </span>

                    <span class="text-gray-400">/</span>

                    <button
                        type="button"
                        @click="router.visit(route('purchase-orders.index'))"
                        class="text-gray-500 transition hover:text-blue-600"
                    >
                        Purchase Orders
                    </button>

                    <span class="text-gray-400">/</span>

                    <span class="font-medium text-gray-900 dark:text-white">
                        {{
                            formMode === 'create'
                                ? 'Create Purchase Order'
                                : 'Edit Purchase Order'
                        }}
                    </span>

                </div>


                <Card>

                    <PurchaseOrderForm
                        :form="form"
                        :branches="branches"
                        :filtered-warehouses="filteredWarehouses"
                        :suppliers="suppliers"
                        :filtered-variants="filteredVariants"
                        :purchase-requests="purchaseRequests"
                        :mode="formMode"
                        @submit="submit"
                        @submit-and-new="submitAndNew"
                        @cancel="cancelForm"
                    />

                </Card>

            </div>

        </Transition>

    </AppLayout>


    <!-- ============================================================= -->
    <!-- MODALS -->
    <!-- ============================================================= -->

    <ConfirmDeleteModal
        :show="showDelete"
        title="Delete Purchase Order"
        :message="deleteMessage"
        confirm-text="Delete"
        @close="closeDelete"
        @confirm="confirmDelete"
    />


    <PurchaseOrderViewModal
        :show="showView"
        :purchase-order="viewItem"
        :loading="viewLoading"
        @close="closeView"
    />


    <PurchaseOrderSubmitModal
        :show="showSubmitModal"
        :purchase-order="selectedPurchaseOrder"
        :loading="submitLoading"
        @close="closeSubmit"
        @confirm="confirmSubmit"
    />


    <PurchaseOrderApproveModal
        :show="showApprove"
        :purchase-order="approveItem"
        :loading="false"
        @close="closeApprove"
        @confirm="confirmApprove"
    />


    <PurchaseOrderRejectModal
        :show="showReject"
        :purchase-order="rejectItem"
        :reason="rejectReason"
        :loading="false"
        @close="closeReject"
        @update:reason="rejectReason = $event"
        @confirm="confirmReject"
    />


    <PurchaseOrderSendModal
        :show="showSend"
        :purchase-order="sendItem"
        :loading="false"
        @close="closeSend"
        @confirm="confirmSend"
    />


    <PurchaseOrderConfirmModal
        :show="showConfirm"
        :purchase-order="confirmItem"
        :loading="false"
        @close="closeConfirm"
        @confirm="confirmPurchaseOrder"
    />


    <PurchaseOrderCancelModal
        :show="showCancelModal"
        :purchase-order="selectedPurchaseOrder"
        :loading="cancelLoading"
        :reason="cancelReason"
        @close="closeCancel"
        @confirm="confirmCancel"
        @update:reason="cancelReason = $event"
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