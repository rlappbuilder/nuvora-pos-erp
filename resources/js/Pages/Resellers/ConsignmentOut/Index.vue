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

import { LoadingOverlay } from '@/Components/Feedback'

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

import ConsignmentOutForm from './Partials/ConsignmentOutForm.vue'
import ConsignmentOutViewModal from './Partials/ConsignmentOutViewModal.vue'
import ConsignmentOutSubmitModal from './Partials/ConsignmentOutSubmitModal.vue'
import ConsignmentOutApproveModal from './Partials/ConsignmentOutApproveModal.vue'
import ConsignmentOutRejectModal from './Partials/ConsignmentOutRejectModal.vue'
import ConsignmentOutCancelModal from './Partials/ConsignmentOutCancelModal.vue'
import ConsignmentOutPostModal from './Partials/ConsignmentOutPostModal.vue'
import ConfirmDeleteModal from '@/Components/Modal/ConfirmDeleteModal.vue'


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    consignmentOuts: {

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

            total_transaction: 0,

            total_items: 0,

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


    resellers: {

        type: Array,

        default: () => [],

    },

    resellerPrices: {
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


    consignmentOut: {

        type: Object,

        default: null,

    },


    loading: {

        type: Boolean,

        default: false,

    },

})


const {
    consignmentOuts,
    statistics,
} = toRefs(props)


/*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/

const pageTitle = computed(
    () => 'Consignment Out'
)
const filteredVariants = computed(() => {

    if (!form.reseller_id) {
        return []
    }

    const productIds = props.resellerPrices
        .filter(
            price =>
                Number(price.reseller_id) ===
                Number(form.reseller_id)
        )
        .map(
            price =>
                Number(price.product_id)
        )

    return props.variants.filter(
        variant =>
            productIds.includes(
                Number(variant.product_id)
            )
    )

})

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

    reseller_id:
        props.filters?.reseller_id ?? '',

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
            'consignment-outs.index'
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


/*
|--------------------------------------------------------------------------
| Search Watcher
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
| Branch Watcher
|--------------------------------------------------------------------------
*/

watch(

    () => filters.branch_id,

    () => {

        loadData()

    }

)


/*
|--------------------------------------------------------------------------
| Warehouse Watcher
|--------------------------------------------------------------------------
*/

watch(

    () => filters.warehouse_id,

    () => {

        loadData()

    }

)


/*
|--------------------------------------------------------------------------
| Reseller Watcher
|--------------------------------------------------------------------------
*/

watch(

    () => filters.reseller_id,

    () => {

        loadData()

    }

)


/*
|--------------------------------------------------------------------------
| Status Watcher
|--------------------------------------------------------------------------
*/

watch(

    () => filters.status,

    () => {

        loadData()

    }

)


/*
|--------------------------------------------------------------------------
| Per Page Watcher
|--------------------------------------------------------------------------
*/

watch(

    () => filters.per_page,

    () => {

        loadData()

    }

)


/*
|--------------------------------------------------------------------------
| Date Range Watcher
|--------------------------------------------------------------------------
*/

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

            branch_id: '',

            warehouse_id: '',

            reseller_id: '',

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
        consignmentOuts
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
        consignmentOuts
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
            consignmentOuts
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

    unit_price:
        0,

    total_price:
        0,

})


const form = useForm({

    company_id:
        null,

    branch_id:
        null,

    warehouse_id:
        null,

    reseller_id:
        null,

    consignment_out_number:
        null,

    transaction_date:
        new Date()
            .toISOString()
            .slice(0, 10),

    reference_number:
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
function create() {

    form.reset()

    form.company_id =
        props.companyId

    form.consignment_out_number =
        props.previewNumber

    form.branch_id =
        null

    form.warehouse_id =
        null

    form.reseller_id =
        null

    form.transaction_date =
        new Date()
            .toISOString()
            .slice(0, 10)

    form.reference_number =
        null

    form.remarks =
        null

    form.details = [
        createEmptyDetail(),
    ]

    form.clearErrors()

    formMode.value =
        'create'

    editingItem.value =
        null

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
                'consignment-outs.store'
            ),

            {

                preserveScroll:
                    true,

                onSuccess: () => {

                    success(

                        'Success',

                        'Consignment Out created successfully.'

                    )

                    view.value =
                        'list'

                },

                onError: (errors) => {

                    console.error(

                        'CREATE CONSIGNMENT OUT ERRORS:',

                        errors

                    )

                    error(

                        'Failed to create consignment out.'

                    )

                },

            }

        )

        return

    }


    form.put(

        route(

            'consignment-outs.update',

            editingItem.value.id

        ),

        {

            preserveScroll:
                true,

            onSuccess: () => {

                success(

                    'Success',

                    'Consignment Out updated successfully.'

                )

                view.value =
                    'list'

            },

            onError: (errors) => {

                console.error(

                    'UPDATE CONSIGNMENT OUT ERRORS:',

                    errors

                )

                error(

                    'Failed to update consignment out.'

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
const submitForm = () => {

    if (formMode.value === 'edit' && editingItem.value) {

        form.put(
            route(
                'consignment-outs.update',
                editingItem.value.id
            ),
            {
                preserveScroll: true,

                onSuccess: () => {

                    view.value = 'list'

                    editingItem.value = null

                    success(
                        'Consignment Out updated successfully.'
                    )

                },

            }
        )

        return

    }


    form.post(
        route(
            'consignment-outs.store'
        ),
        {
            preserveScroll: true,

            onSuccess: () => {

                view.value = 'list'

                editingItem.value = null

                success(
                    'Consignment Out created successfully.'
                )

            },

        }
    )

}
function submitAndNew()
{

    form.post(

        route(
            'consignment-outs.store'
        ),

        {

            preserveScroll:
                true,

            onSuccess: () => {

                success(

                    'Success',

                    'Consignment Out created successfully.'

                )

                form.reset()

                form.clearErrors()

                form.company_id =
                    props.companyId ?? null

                form.branch_id =
                    null

                form.warehouse_id =
                    null

                form.reseller_id =
                    null

                form.transaction_date =
                    new Date()
                        .toISOString()
                        .slice(0, 10)

                form.reference_number =
                    null

                form.remarks =
                    null

                form.details = [

                    createEmptyDetail(),

                ]

            },

            onError: (errors) => {

                console.error(

                    'SAVE AND NEW CONSIGNMENT OUT ERRORS:',

                    errors

                )

                error(

                    'Failed to create consignment out.'

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

function editConsignmentOut(item)
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

            'Only Draft or Rejected consignment out can be edited.'

        )

        return

    }


    editingItem.value =
        item

    formMode.value =
        'edit'

    form.clearErrors()

    form.company_id =
        item.company_id

    form.branch_id =
        item.branch_id

    form.warehouse_id =
        item.warehouse_id

    form.reseller_id =
        item.reseller_id

    form.consignment_out_number =
        item.consignment_out_number
        
    form.transaction_date =
        item.transaction_date
            ? String(
                item.transaction_date
            ).slice(0, 10)
            : null


    form.reference_number =
        item.reference_number


    form.remarks =
        item.remarks


    form.details =
        item.details?.map(
            detail => ({

                product_variant_id:
                    detail.product_variant_id,

                unit_id:
                    detail.unit_id,

                qty:
                    detail.qty,

                unit_price:
                    detail.unit_price ?? 0,

                total_price:
                    detail.total_price ?? 0,

            })
        ) ?? [

            createEmptyDetail(),

        ]


    view.value =
        'form'


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

const selectedItem =
    ref(null)


function showConsignmentOut(item)
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
| Duplicate
|--------------------------------------------------------------------------
*/

function duplicate(item)
{

    router.get(

        route(
            'consignment-outs.duplicate',
            item.id
        ),

        {},

        {

            preserveScroll:
                true,

            onSuccess: () => {

                success(

                    'Success',

                    'Consignment Out duplicated successfully.'

                )

            },

            onError: () => {

                error(

                    'Failed to duplicate consignment out.'

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

            'Only Draft or Rejected consignment out can be deleted.'

        )

        return

    }


    deleteItem.value =
        item

    showDelete.value =
        true

}


/*
|--------------------------------------------------------------------------
| Close Delete
|--------------------------------------------------------------------------
*/

function closeDelete()
{

    deleteItem.value =
        null

    showDelete.value =
        false

}


/*
|--------------------------------------------------------------------------
| Delete Message
|--------------------------------------------------------------------------
*/

const deleteMessage =
    computed(() => {

        if (
            !deleteItem.value
        ) {

            return ''

        }


        return `Are you sure you want to delete "${deleteItem.value.consignment_out_number}"?`

    })


/*
|--------------------------------------------------------------------------
| Confirm Delete
|--------------------------------------------------------------------------
*/

function confirmDelete()
{

    if (
        !deleteItem.value
    ) {

        return

    }


    router.delete(

        route(

            'consignment-outs.destroy',

            deleteItem.value.id

        ),

        {

            preserveScroll:
                true,

            onSuccess: () => {

                closeDelete()

                success(

                    'Success',

                    'Consignment Out deleted successfully.'

                )

            },

            onError: () => {

                error(

                    'Failed to delete consignment out.'

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
const printConsignmentOut = (item) => {
    window.open(
        route('consignment-outs.print', item.id),
        '_blank'
    )
}

/*
|--------------------------------------------------------------------------
| Bulk Delete Message
|--------------------------------------------------------------------------
*/

const bulkDeleteMessage =
    computed(() => {

        const total =
            selectedRows.value.length


        if (!total) {

            return ''

        }


        return `Are you sure you want to delete ${total} selected Consignment Out document(s)?`

    })


/*
|--------------------------------------------------------------------------
| Confirm Bulk Delete
|--------------------------------------------------------------------------
*/

function bulkDelete()
{

    router.delete(

        route(
            'consignment-outs.bulk-delete'
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

                    'Consignment Out documents deleted successfully.'

                )

            },

            onError: () => {

                error(

                    'Failed to delete consignment out documents.'

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

const selectedConsignmentOut =
    ref(null)

const submitLoading =
    ref(false)


function openSubmit(item)
{

    selectedConsignmentOut.value =
        item

    submitItem.value =
        item

    showSubmitModal.value =
        true

}


function closeSubmit()
{

    showSubmitModal.value =
        false

    selectedConsignmentOut.value =
        null

    submitItem.value =
        null

}


function confirmSubmit()
{

    if (
        !selectedConsignmentOut.value
    ) {

        return

    }


    submitLoading.value =
        true


    router.post(

        route(

            'consignment-outs.submit',

            selectedConsignmentOut.value.id

        ),

        {},

        {

            preserveScroll:
                true,

            onSuccess: () => {

                closeSubmit()

                success(

                    'Success',

                    'Consignment Out submitted successfully.'

                )

            },

            onError: (errors) => {

                console.error(

                    'SUBMIT CONSIGNMENT OUT ERROR:',

                    errors

                )

                error(

                    'Failed to submit consignment out.'

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


        return `Are you sure you want to submit "${submitItem.value.consignment_out_number}"?`

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

            'consignment-outs.approve',

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

                    'Consignment Out approved successfully.'

                )

            },

            onError: (errors) => {

                console.error(

                    'APPROVE CONSIGNMENT OUT ERROR:',

                    errors

                )

                error(

                    'Failed to approve consignment out.'

                )

            },

            onFinish: () => {

                approveLoading.value =
                    false

            },

        }

    )

}


const approveMessage =
    computed(() => {

        if (
            !approveItem.value
        ) {

            return ''

        }


        return `Are you sure you want to approve "${approveItem.value.consignment_out_number}"?`

    })


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

            'consignment-outs.reject',

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

                    'Consignment Out rejected successfully.'

                )

            },

            onError: (errors) => {

                console.error(

                    'REJECT CONSIGNMENT OUT ERROR:',

                    errors

                )

                error(

                    'Failed to reject consignment out.'

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


const showPostModal =
    ref(false)


const postLoading =
    ref(false)


function openPost(item)
{
    selectedConsignmentOut.value =
        item

    showPostModal.value =
        true
}


function closePost()
{
    if (
        postLoading.value
    ) {

        return

    }


    showPostModal.value =
        false

    selectedConsignmentOut.value =
        null
}


function confirmPost()
{
    if (
        !selectedConsignmentOut.value
    ) {

        return

    }


    postLoading.value =
        true


    router.post(

        route(

            'consignment-outs.post',

            selectedConsignmentOut.value.id

        ),

        {},

        {

            preserveScroll:
                true,

            onSuccess: () => {

                success(

                    'Success',

                    'Consignment Out posted successfully.'

                )

                showPostModal.value =
                    false

                selectedConsignmentOut.value =
                    null

            },

            onError: (errors) => {

                console.error(

                    'POST CONSIGNMENT OUT ERROR:',

                    errors

                )

                error(

                    'Failed to post consignment out.'

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

const showCancelModal =
    ref(false)

const cancelLoading =
    ref(false)

const cancelReason =
    ref('')


function openCancel(item)
{

    selectedConsignmentOut.value =
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

    selectedConsignmentOut.value =
        null

    cancelReason.value =
        ''

}


function confirmCancel()
{

    if (
        !selectedConsignmentOut.value
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

            'consignment-outs.cancel',

            selectedConsignmentOut.value.id

        ),

        {

            reason:
                cancelReason.value.trim(),

        },

        {

            preserveScroll:
                true,

            onSuccess: () => {

                closeCancel()

                success(

                    'Success',

                    'Consignment Out cancelled successfully.'

                )

            },

            onError: (errors) => {

                console.error(

                    'CANCEL CONSIGNMENT OUT ERROR:',

                    errors

                )

                error(

                    'Failed to cancel consignment out.'

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

                    'consignment-outs.data',

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

            'CONSIGNMENT OUT VIEW RESPONSE:',

            responseData

        )


        viewItem.value =
            responseData.data


    } catch (
        exception
    ) {

        console.error(

            'CONSIGNMENT OUT VIEW ERROR:',

            exception

        )


        showView.value =
            false

        viewItem.value =
            null


        error(

            'Failed to load consignment out detail.'

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
            'consignment-outs.index'
        ),

        {

            search:
                filters.search,

            branch_id:
                filters.branch_id,

            warehouse_id:
                filters.warehouse_id,

            reseller_id:
                filters.reseller_id,

            status:
                filters.status,

            per_page:
                filters.per_page,

            date_from:
                filters.date_from,

            date_to:
                filters.date_to,

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

        'Submitted',
        'Approved',
        'Posted',

    ].includes(
        item.status
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

        Number(value || 0)

    )

}


/*
|--------------------------------------------------------------------------
| Consignment Summary
|--------------------------------------------------------------------------
*/

const summaryCards = computed(() => [

    {
        key: 'total_transaction',

        label: 'Total Transaksi',

        value:
            statistics.value?.total_transaction ?? 0,

        classes:
            'border-gray-100 bg-white',

        accent:
            'bg-gray-400',
    },

    {
        key: 'total_items',

        label: 'Total Items',

        value:
            statistics.value?.total_items ?? 0,

        classes:
            'border-gray-100 bg-white',

        accent:
            'bg-gray-400',
    },

    {
        key: 'submitted',

        label: 'Submitted',

        value:
            statistics.value?.submitted ?? 0,

        classes:
            'border-gray-100 bg-white',

        accent:
            'bg-gray-400',
    },

    {
        key: 'posted',

        label: 'Posted',

        value:
            statistics.value?.posted ?? 0,

        classes:
            'border-gray-100 bg-white',

        accent:
            'bg-gray-400',
    },

])


const formatAmount = (value) => {

    return new Intl.NumberFormat(

        'id-ID',

        {

            minimumFractionDigits:
                0,

            maximumFractionDigits:
                0,

        }

    ).format(

        Number(value || 0)

    )

}


/*
|--------------------------------------------------------------------------
| Summary Selection
|--------------------------------------------------------------------------
*/

const activeSummary =
    ref(null)


function selectSummary(key)
{

    activeSummary.value =
        activeSummary.value === key
            ? null
            : key


    if (
        key === 'submitted'
    ) {

        filters.status =
            activeSummary.value
                ? 'Submitted'
                : ''

        return

    }


    if (
        key === 'posted'
    ) {

        filters.status =
            activeSummary.value
                ? 'Posted'
                : ''

        return

    }


    if (
        key === 'total_transaction' ||
        key === 'total_items'
    ) {

        filters.status =
            ''

    }

}


const exportPdf = (item) => {

    window.location.href =
        route(
            'consignment-outs.pdf',
            item.id
        )

}


const exportExcel = (item) => {

    window.location.href =
        route(
            'consignment-outs.excel',
            item.id
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
            class="space-y-4"
        >

            <!-- ===================================================== -->
            <!-- Header -->
            <!-- ===================================================== -->

            <div
                class="
                    flex
                    flex-col
                    gap-3
                    sm:flex-row
                    sm:items-center
                    sm:justify-between
                "
            >

                <div>

                    <h1
                        class="
                            text-xl
                            font-semibold
                            text-gray-900
                        "
                    >
                        Consignment Out
                    </h1>


                    <p
                        class="
                            mt-1
                            text-sm
                            text-gray-500
                        "
                    >
                        Manage consignment out transactions.
                    </p>

                </div>


                <!-- Header Actions -->

                <div
                    class="
                        flex
                        items-center
                        gap-2
                    "
                >

                    <BaseButton
                        variant="secondary"
                        @click="refresh"
                    >
                        Refresh
                    </BaseButton>


                    <BaseButton
                        @click="create"
                    >

                        <template #icon>

                            <PlusIcon
                                class="h-5 w-5"
                            />

                        </template>

                        Add

                    </BaseButton>

                </div>

            </div>


            <!-- ===================================================== -->
            <!-- Filter -->
            <!-- ===================================================== -->

            <div
                class="
                    rounded-xl
                    border
                    border-gray-100
                    bg-white
                    p-4
                    shadow-sm
                "
            >

                <div
                    class="
                        grid
                        grid-cols-1
                        gap-3
                        md:grid-cols-2
                        lg:grid-cols-5
                    "
                >

                    <!-- Date Range -->

                    <div>

                        <label
                            class="
                                mb-1
                                block
                                text-xs
                                font-medium
                                text-gray-600
                            "
                        >
                            Transaction Date
                        </label>


                        <FlatPickr
                            v-model="filters.date_range"
                            :config="{
                                mode: 'range',
                                dateFormat: 'Y-m-d',
                                allowInput: true,
                            }"
                            placeholder="Transaction Date"
                            class="
                                w-full
                                rounded-lg
                                border
                                border-gray-200
                                px-3
                                py-2
                                text-sm
                            "
                        />

                    </div>


                    <!-- Reseller -->

                    <div>

                        <label
                            class="
                                mb-1
                                block
                                text-xs
                                font-medium
                                text-gray-600
                            "
                        >
                            Reseller
                        </label>


                        <SearchableSelect
                            v-model="filters.reseller_id"
                            :options="resellers"
                            label="label"
                            value-key="id"
                            placeholder="All Resellers"
                        />

                    </div>


                    <!-- Branch -->

                    <div>

                        <label
                            class="
                                mb-1
                                block
                                text-xs
                                font-medium
                                text-gray-600
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

                    <div>

                        <label
                            class="
                                mb-1
                                block
                                text-xs
                                font-medium
                                text-gray-600
                            "
                        >
                            Warehouse
                        </label>


                        <SearchableSelect
                            v-model="filters.warehouse_id"
                            :options="warehouses"
                            label="label"
                            value-key="id"
                            placeholder="All Warehouses"
                        />

                    </div>


                    <!-- Status -->

                    <div>

                        <label
                            class="
                                mb-1
                                block
                                text-xs
                                font-medium
                                text-gray-600
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


                <!-- Search -->

                <div
                    class="
                        mt-3
                        flex
                        flex-col
                        gap-1
                        sm:flex-row
                    "
                >

                    <input
                        v-model="filters.search"
                        type="text"
                        placeholder="Search document, reference, reseller..."
                        class="
                            min-w-0
                            flex-1
                            rounded-lg
                            border
                            border-gray-200
                            px-3
                            py-2
                            text-sm
                        "
                    />
                    
                </div>

            </div>


            <!-- ===================================================== -->
            <!-- Summary -->
            <!-- ===================================================== -->

            <div
                class="
                    rounded-xl
                    border
                    border-gray-100
                    bg-white
                    p-3
                    shadow-sm
                "
            >

                <div
                    class="
                        grid
                        grid-cols-2
                        gap-2
                        sm:grid-cols-4
                    "
                >

                    <button
                        v-for="card in summaryCards"
                        :key="card.key"
                        type="button"
                        class="
                            relative
                            overflow-hidden
                            rounded-lg
                            border
                            px-4
                            py-3
                            text-left
                            transition
                            hover:-translate-y-px
                            hover:shadow-sm
                        "
                        :class="[
                            card.classes,

                            activeSummary === card.key
                                ? 'ring-2 ring-gray-300 ring-offset-1'
                                : '',
                        ]"
                        @click="selectSummary(card.key)"
                    >

                        <span
                            class="
                                absolute
                                inset-y-0
                                left-0
                                w-1
                            "
                            :class="card.accent"
                        ></span>


                        <div
                            class="
                                text-xs
                                font-medium
                                text-gray-500
                            "
                        >
                            {{ card.label }}
                        </div>


                        <div
                            class="
                                mt-1
                                text-base
                                font-semibold
                                tabular-nums
                                text-gray-900
                            "
                        >

                            <template
                                v-if="
                                    card.key ===
                                    'total_transaction'
                                "
                            >
                                Rp
                                {{
                                    formatAmount(
                                        card.value
                                    )
                                }}
                            </template>


                            <template
                                v-else
                            >
                                {{
                                    formatAmount(
                                        card.value
                                    )
                                }}
                            </template>

                        </div>

                    </button>

                </div>

            </div>
            <!-- ===================================================== -->
            <!-- Table Container -->
            <!-- ===================================================== -->

            <div
                class="
                    overflow-hidden
                    rounded-xl
                    border
                    border-gray-100
                    bg-white
                    shadow-sm
                "
            >
                
                <!-- Table Toolbar -->

                <div
                    class="
                        flex
                        flex-col
                        gap-2
                        border-b
                        border-gray-100
                        px-4
                        py-3
                        sm:flex-row
                        sm:items-center
                        sm:justify-between
                    "
                >

                    <div
                        class="
                            text-xs
                            text-gray-500
                        "
                    >
                        Consignment Out Transactions
                    </div>


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
                    />

                </div>


                <!-- Loading -->

                <LoadingOverlay
                    :show="loading"
                    text="Loading Consignment Out..."
                />


                <!-- DataTable -->

                <div
                    v-if="
                        consignmentOuts?.data?.length
                    "
                    class="overflow-x-auto"
                >

                    <DataTable
                        sticky-header
                        max-height="650px"
                    >

                        <DataTableHead sticky>

                            <!-- Select All -->

                            <DataTableHeaderCell
                                width="50px"
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


                            <!-- Date -->

                            <DataTableHeaderCell
                                sortable
                                column="transaction_date"
                                :sort="sort"
                                :direction="direction"
                                @sort="sortBy"
                                width="140px"
                            >
                                Date
                            </DataTableHeaderCell>


                            <!-- Document -->

                            <DataTableHeaderCell
                                sortable
                                column="consignment_out_number"
                                :sort="sort"
                                :direction="direction"
                                @sort="sortBy"
                                width="180px"
                            >
                                Document
                            </DataTableHeaderCell>


                            <!-- Reseller -->

                            <DataTableHeaderCell
                                width="260px"
                            >
                                Reseller
                            </DataTableHeaderCell>


                            <!-- Items -->

                            <DataTableHeaderCell
                                width="100px"
                                align="right"
                            >
                                Items
                            </DataTableHeaderCell>


                            <!-- Consignment Value -->

                            <DataTableHeaderCell
                                width="170px"
                                align="right"
                            >
                                Consignment Value
                            </DataTableHeaderCell>


                            <!-- Status -->

                            <DataTableHeaderCell
                                width="130px"
                                align="center"
                            >
                                Status
                            </DataTableHeaderCell>


                            <!-- Actions -->

                            <DataTableHeaderCell
                                width="90px"
                                align="center"
                            >
                                Actions
                            </DataTableHeaderCell>

                        </DataTableHead>


                        <DataTableBody>

                            <DataTableRow
                                v-for="
                                    item in
                                    (
                                        consignmentOuts?.data
                                        ?? []
                                    )
                                "
                                :key="item.id"
                            >

                                <!-- Checkbox -->

                                <DataTableCell
                                    align="center"
                                >

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


                                <!-- Date -->

                                <DataTableCell>

                                    <span
                                        class="
                                            text-gray-700
                                        "
                                    >
                                        {{
                                            item.transaction_date
                                                ? formatDate(
                                                    item.transaction_date
                                                )
                                                : '-'
                                        }}
                                    </span>

                                </DataTableCell>


                                <!-- Document -->

                                <DataTableCell>

                                    <div
                                        class="
                                            font-medium
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            item.consignment_out_number
                                            ?? '-'
                                        }}
                                    </div>


                                    <div
                                        v-if="
                                            item.reference_number
                                        "
                                        class="
                                            mt-0.5
                                            text-xs
                                            text-gray-500
                                        "
                                    >
                                        Ref:
                                        {{
                                            item.reference_number
                                        }}
                                    </div>

                                </DataTableCell>


                                <!-- Reseller -->

                                <DataTableCell>

                                    <div
                                        class="
                                            font-medium
                                            text-gray-900
                                        "
                                    >
                                        <span
                                            v-if="
                                                item.reseller?.reseller_code
                                            "
                                        >
                                            {{
                                                item.reseller.reseller_code
                                            }}
                                            -
                                        </span>

                                        {{
                                            item.reseller?.name
                                            ?? '-'
                                        }}
                                    </div>


                                    <div
                                        class="
                                            mt-0.5
                                            text-xs
                                            text-gray-500
                                        "
                                    >
                                        {{
                                            item.branch?.name
                                            ?? '-'
                                        }}

                                        <span>
                                            •
                                        </span>

                                        {{
                                            item.warehouse?.name
                                            ?? '-'
                                        }}
                                    </div>

                                </DataTableCell>


                                <!-- Items -->

                                <DataTableCell
                                    align="right"
                                >

                                    <span
                                        class="
                                            tabular-nums
                                            text-gray-700
                                        "
                                    >
                                        {{
                                            item.total_items
                                            ?? 0
                                        }}
                                    </span>

                                </DataTableCell>


                                <!-- Consignment Value -->

                                <DataTableCell
                                    align="right"
                                >

                                    <span
                                        class="
                                            font-medium
                                            tabular-nums
                                            text-gray-900
                                        "
                                    >
                                        Rp
                                        {{
                                            formatAmount(
                                                item.total_transaction
                                            )
                                        }}
                                    </span>

                                </DataTableCell>


                                <!-- Status -->

                                <DataTableCell
                                    align="center"
                                >

                                    <StatusBadge
                                        :status="
                                            item.status
                                        "
                                    />

                                </DataTableCell>


                                <!-- Actions -->

                                <DataTableCell
                                    align="center"
                                >

                                    <ActionDropdown

                                        @view="
                                            openView(item)
                                        "

                                        @edit="
                                            editConsignmentOut(item)
                                        "

                                        @duplicate="
                                            duplicate(item)
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

                                        @print="
                                            printConsignmentOut(item)
                                        "

                                        @pdf="
                                            exportPdf(item)
                                        "

                                        @excel="
                                            exportExcel(item)
                                        "


                                        :showEdit="
                                            canEdit(item)
                                        "

                                        :showDuplicate="
                                            true
                                        "

                                        :showSubmit="
                                            canSubmit(item)
                                        "

                                        :showApprove="
                                            canApprove(item)
                                        "

                                        :showReject="
                                            canReject(item)
                                        "

                                        :showPost="
                                            canPost(item)
                                        "

                                        :showCancel="
                                            canCancel(item)
                                        "

                                        :showExport="
                                            false
                                        "

                                        :showPrint="
                                            true
                                        "

                                        :showPdf="
                                            true
                                        "

                                        :showExcel="
                                            true
                                        "

                                        :showHistory="
                                            false
                                        "

                                        :showDelete="
                                            canDelete(item)
                                        "

                                    />

                                </DataTableCell>

                            </DataTableRow>

                        </DataTableBody>

                    </DataTable>

                </div>


                <!-- ================================================= -->
                <!-- Empty State -->
                <!-- ================================================= -->

                <div
                    v-else
                    class="
                        px-4
                        py-10
                        text-center
                    "
                >

                    <div
                        class="
                            text-sm
                            font-medium
                            text-gray-700
                        "
                    >
                        No Consignment Out
                    </div>


                    <div
                        class="
                            mt-1
                            text-sm
                            text-gray-500
                        "
                    >
                        No consignment out transactions found.
                    </div>

                </div>

            </div>


            <!-- ===================================================== -->
            <!-- Period / Filtered Total -->
            <!-- ===================================================== -->

            <div
                class="
                    flex
                    items-center
                    justify-between
                    rounded-xl
                    border
                    border-gray-100
                    bg-white
                    px-4
                    py-3
                    shadow-sm
                "
            >

                <span
                    class="
                        text-sm
                        font-semibold
                        text-gray-900
                    "
                >
                    Total Consignment Value
                </span>


                <span
                    class="
                        text-sm
                        font-semibold
                        tabular-nums
                        text-gray-900
                    "
                >
                    Rp
                    {{
                        formatAmount(
                            statistics?.total_transaction
                            ?? 0
                        )
                    }}
                </span>

            </div>


            <!-- ===================================================== -->
            <!-- Pagination -->
            <!-- ===================================================== -->

            <div>

                <TablePagination
                    :data="consignmentOuts"
                    label="Consignment Out"
                />

            </div>

        </div>

        <!-- ========================================================= -->
        <!-- FORM -->
        <!-- ========================================================= -->

        <div
            v-else-if="view === 'form'"
            key="form"
            class="space-y-4"
        >

            <div
                class="
                    flex
                    flex-col
                    gap-1
                    sm:flex-row
                    sm:items-center
                    sm:justify-between
                "
            >

                <div>

                    <h1
                        class="
                            text-xl
                            font-semibold
                            text-gray-900
                        "
                    >
                        {{
                            formMode === 'create'
                                ? 'Create Consignment Out'
                                : 'Edit Consignment Out'
                        }}
                    </h1>


                    <p
                        class="
                            mt-1
                            text-sm
                            text-gray-500
                        "
                    >
                        {{
                            formMode === 'create'
                                ? 'Create a new consignment out transaction.'
                                : 'Update consignment out transaction.'
                        }}
                    </p>

                </div>

            </div>


            <Card>

              <ConsignmentOutForm
                    :form="form"
                    :branches="branches"
                    :filteredWarehouses="filteredWarehouses"
                    :resellers="resellers"
                    :filteredVariants="filteredVariants"
                    :resellerPrices="resellerPrices"
                    :mode="formMode"
                    @submit="submitForm"
                    @submitAndNew="submitAndNew"
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
            class="space-y-4"
        >

            <div
                class="
                    flex
                    flex-col
                    gap-1
                    sm:flex-row
                    sm:items-center
                    sm:justify-between
                "
            >

                <div>

                    <h1
                        class="
                            text-xl
                            font-semibold
                            text-gray-900
                        "
                    >
                        Consignment Out
                    </h1>


                    <p
                        class="
                            mt-1
                            text-sm
                            text-gray-500
                        "
                    >
                        Consignment out transaction detail.
                    </p>

                </div>


                <BaseButton
                    variant="secondary"
                    @click="backToList"
                >
                    Back
                </BaseButton>

            </div>


            <Card>

                <ConsignmentOutViewModal
                    :show="true"
                    :consignment-out="selectedItem"
                    :loading="false"
                    @close="backToList"
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
    title="Delete Consignment Out"
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
    title="Delete Consignment Out"
    :message="bulkDeleteMessage"
    confirm-text="Delete"
    @close="
        showBulkDelete = false
    "
    @confirm="bulkDelete"
/>


<!-- =============================================================== -->
<!-- View Modal -->
<!-- =============================================================== -->

<ConsignmentOutViewModal
    :show="showView"
    :consignment-out="viewItem"
    :loading="viewLoading"
    @close="closeView"
/>


<!-- =============================================================== -->
<!-- Submit -->
<!-- =============================================================== -->

<ConsignmentOutSubmitModal
    :show="showSubmitModal"
    :consignment-out="selectedConsignmentOut"
    :loading="submitLoading"
    @close="closeSubmit"
    @confirm="confirmSubmit"
/>


<!-- =============================================================== -->
<!-- Approve -->
<!-- =============================================================== -->

<ConsignmentOutApproveModal
    :show="showApprove"
    :consignment-out="approveItem"
    :loading="approveLoading"
    @close="closeApprove"
    @confirm="confirmApprove"
/>


<!-- =============================================================== -->
<!-- Reject -->
<!-- =============================================================== -->

<ConsignmentOutRejectModal
    :show="showReject"
    :consignment-out="rejectItem"
    :reason="rejectReason"
    :loading="rejectLoading"
    @close="closeReject"
    @update:reason="
        rejectReason = $event
    "
    @confirm="confirmReject"
/>


<!-- =============================================================== -->
<!-- Cancel -->
<!-- =============================================================== -->

<ConsignmentOutCancelModal
    :show="showCancelModal"
    :consignment-out="selectedConsignmentOut"
    :loading="cancelLoading"
    :reason="cancelReason"
    @close="closeCancel"
    @confirm="confirmCancel"
    @update:reason="
        cancelReason = $event
    "
/>
<ConsignmentOutPostModal
    :show="showPostModal"
    :consignment-out="selectedConsignmentOut"
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


@media print {

    @page {

        size:
            A4 landscape;

        margin:
            12mm;

    }


    body {

        background:
            white !important;

    }


    .print-table {

        width:
            100% !important;

    }


    .print-table tr {

        break-inside:
            avoid;

        page-break-inside:
            avoid;

    }

}

</style>