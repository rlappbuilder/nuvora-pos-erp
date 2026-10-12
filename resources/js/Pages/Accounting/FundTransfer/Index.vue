<script setup>
import {
    computed,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from 'vue'

import { router } from '@inertiajs/vue3'

import FlatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'

import AppLayout from '@/Layouts/AppLayout.vue'
import FundTransferForm from './Partials/FundTransferForm.vue'
import FundTransferViewModal from './Partials/FundTransferViewModal.vue'
import StatusBadge from '@/Components/Display/StatusBadge.vue'
import { success, error } from '@/Utils'

import Swal from 'sweetalert2'

/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({
    fundTransfers: {
        type: Object,
        default: () => ({
            data: [],
            links: [],
            meta: {},
        }),
    },

    filters: {
        type: Object,
        default: () => ({}),
    },

    statistics: {
        type: Object,
        default: () => ({}),
    },

    previewNumber: {
        type: String,
        default: '',
    },

    branches: {
        type: Array,
        default: () => [],
    },

    statuses: {
        type: Array,
        default: () => [],
    },
})

/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

const search = ref(props.filters?.search ?? '')
const branchId = ref(props.filters?.branch_id ?? '')
const status = ref(props.filters?.status ?? '')

const loading = ref(false)

let searchTimeout = null

/*
|--------------------------------------------------------------------------
| Date Range
|--------------------------------------------------------------------------
*/

const showDateRangePicker = ref(false)
const dateRangePickerRef = ref(null)
const isMobile = ref(false)

const selectedPreset = ref('custom')

const reportDateFrom = ref(
    props.filters?.date_from ?? ''
)

const reportDateTo = ref(
    props.filters?.date_to ?? ''
)

const calendarRange = ref([])

const updateIsMobile = () => {
    isMobile.value = window.innerWidth < 640
}

const handleClickOutsideDateRange = (event) => {
    if (!showDateRangePicker.value) {
        return
    }

    if (
        dateRangePickerRef.value &&
        !dateRangePickerRef.value.contains(event.target)
    ) {
        showDateRangePicker.value = false
    }
}

onMounted(() => {
    updateIsMobile()

    window.addEventListener('resize', updateIsMobile)

    document.addEventListener(
        'mousedown',
        handleClickOutsideDateRange
    )
})

onBeforeUnmount(() => {
    window.removeEventListener('resize', updateIsMobile)

    document.removeEventListener(
        'mousedown',
        handleClickOutsideDateRange
    )

    clearTimeout(searchTimeout)
})

/*
|--------------------------------------------------------------------------
| Date Presets
|--------------------------------------------------------------------------
*/

const presets = [
    { key: 'today', label: 'Today' },
    { key: 'yesterday', label: 'Yesterday' },
    { key: 'last-7-days', label: 'Last 7 Days' },
    { key: 'last-30-days', label: 'Last 30 Days' },
    { key: 'this-month', label: 'This Month' },
    { key: 'last-month', label: 'Last Month' },
    { key: 'custom', label: 'Custom Range' },
]

/*
|--------------------------------------------------------------------------
| Date Helpers
|--------------------------------------------------------------------------
*/

const parseDate = (value) => {
    if (!value || typeof value !== 'string') {
        return null
    }

    const [year, month, day] = value
        .split('-')
        .map(Number)

    if (!year || !month || !day) {
        return null
    }

    return new Date(year, month - 1, day)
}

const toDateString = (date) => {
    if (!(date instanceof Date) || Number.isNaN(date.getTime())) {
        return ''
    }

    const year = date.getFullYear()

    const month = String(
        date.getMonth() + 1
    ).padStart(2, '0')

    const day = String(
        date.getDate()
    ).padStart(2, '0')

    return `${year}-${month}-${day}`
}

const startOfDay = (date) => {
    const result = new Date(date)

    result.setHours(0, 0, 0, 0)

    return result
}

/*
|--------------------------------------------------------------------------
| Display
|--------------------------------------------------------------------------
*/

const formatReportDate = (value) => {
    if (!value) {
        return '-'
    }

    const date = parseDate(value)

    if (!date) {
        return '-'
    }

    return new Intl.DateTimeFormat('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(date)
}

const reportDateRangeLabel = computed(() => {
    if (!reportDateFrom.value || !reportDateTo.value) {
        return 'Select Date Range'
    }

    return `${formatReportDate(reportDateFrom.value)} - ${formatReportDate(reportDateTo.value)}`
})

/*
|--------------------------------------------------------------------------
| Formatters
|--------------------------------------------------------------------------
*/

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(Number(value || 0))
}

const formatNumber = (value) => {
    return new Intl.NumberFormat('id-ID').format(
        Number(value || 0)
    )
}

const formatDate = (value) => {
    if (!value) {
        return '-'
    }

    const date = new Date(value)

    if (Number.isNaN(date.getTime())) {
        return '-'
    }

    return new Intl.DateTimeFormat('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(date)
}

/*
|--------------------------------------------------------------------------
| Filter Options
|--------------------------------------------------------------------------
*/

const branchOptions = computed(() =>
    props.branches.map((branch) => ({
        value: branch.id,
        label: branch.name,
    }))
)

const statusOptions = computed(() =>
    props.statuses.map((item) => {
        if (typeof item === 'string') {
            return {
                value: item,
                label: item,
            }
        }

        return {
            value: item.value ?? item.id ?? item.name,
            label: item.label ?? item.name ?? item.value,
        }
    })
)

/*
|--------------------------------------------------------------------------
| Statistics
|--------------------------------------------------------------------------
*/

const statCards = computed(() => [
    {
        key: 'total',
        label: 'Total Transfers',
        value: formatNumber(props.statistics?.total ?? 0),
        icon: 'transfer',
    },
    {
        key: 'draft',
        label: 'Draft',
        value: formatNumber(props.statistics?.draft ?? 0),
        icon: 'document',
    },
    {
        key: 'submitted',
        label: 'Submitted',
        value: formatNumber(props.statistics?.submitted ?? 0),
        icon: 'clock',
    },
    {
        key: 'posted',
        label: 'Posted',
        value: formatNumber(props.statistics?.posted ?? 0),
        icon: 'check',
    },
])

/*
|--------------------------------------------------------------------------
| Load Fund Transfers
|--------------------------------------------------------------------------
*/

function loadData() {
    loading.value = true

    router.get(
        route('fund-transfers.index'),
        {
            search: search.value,
            branch_id: branchId.value,
            status: status.value,
            date_from: reportDateFrom.value,
            date_to: reportDateTo.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,

            onFinish: () => {
                loading.value = false
            },
        }
    )
}

function refreshData() {
    selectedRows.value = []

    loadData()
}

function refresh() {
    clearTimeout(searchTimeout)

    search.value = ''
    branchId.value = ''
    status.value = ''

    reportDateFrom.value = ''
    reportDateTo.value = ''
    calendarRange.value = []

    selectedPreset.value = 'custom'
    showDateRangePicker.value = false
    selectedRows.value = []

    loadData()
}

/*
|--------------------------------------------------------------------------
| Filter Watchers
|--------------------------------------------------------------------------
*/

watch(search, () => {
    clearTimeout(searchTimeout)

    searchTimeout = setTimeout(() => {
        selectedRows.value = []
        loadData()
    }, 500)
})

watch([branchId, status], () => {
    selectedRows.value = []
    loadData()
})

/*
|--------------------------------------------------------------------------
| Apply Date Range
|--------------------------------------------------------------------------
*/

function applyReportRange(dateFrom, dateTo, preset = null) {
    reportDateFrom.value = dateFrom
    reportDateTo.value = dateTo

    calendarRange.value = [
        parseDate(dateFrom),
        parseDate(dateTo),
    ].filter(Boolean)

    if (preset) {
        selectedPreset.value = preset
    }

    showDateRangePicker.value = false
    selectedRows.value = []

    loadData()
}

/*
|--------------------------------------------------------------------------
| Apply Preset
|--------------------------------------------------------------------------
*/

function applyReportPreset(preset) {
    const today = startOfDay(new Date())

    let dateFrom
    let dateTo

    switch (preset) {
        case 'today':
            dateFrom = today
            dateTo = today
            break

        case 'yesterday': {
            const yesterday = new Date(today)
            yesterday.setDate(yesterday.getDate() - 1)

            dateFrom = yesterday
            dateTo = yesterday
            break
        }

        case 'last-7-days': {
            const from = new Date(today)
            from.setDate(from.getDate() - 6)

            dateFrom = from
            dateTo = today
            break
        }

        case 'last-30-days': {
            const from = new Date(today)
            from.setDate(from.getDate() - 29)

            dateFrom = from
            dateTo = today
            break
        }

        case 'this-month':
            dateFrom = new Date(
                today.getFullYear(),
                today.getMonth(),
                1
            )
            dateTo = today
            break

        case 'last-month':
            dateFrom = new Date(
                today.getFullYear(),
                today.getMonth() - 1,
                1
            )
            dateTo = new Date(
                today.getFullYear(),
                today.getMonth(),
                0
            )
            break

        case 'custom':
            selectedPreset.value = 'custom'
            return

        default:
            return
    }

    applyReportRange(
        toDateString(dateFrom),
        toDateString(dateTo),
        preset
    )
}

/*
|--------------------------------------------------------------------------
| Calendar
|--------------------------------------------------------------------------
*/

function handleCalendarChange(selectedDates) {
    if (selectedDates.length !== 2) {
        return
    }

    applyReportRange(
        toDateString(selectedDates[0]),
        toDateString(selectedDates[1]),
        'custom'
    )
}

/*
|--------------------------------------------------------------------------
| Sync Inertia Props
|--------------------------------------------------------------------------
*/

watch(
    () => props.filters,
    (filters) => {
        if (!filters) {
            return
        }

        reportDateFrom.value = filters.date_from ?? ''
        reportDateTo.value = filters.date_to ?? ''

        calendarRange.value = [
            parseDate(reportDateFrom.value),
            parseDate(reportDateTo.value),
        ].filter(Boolean)

        search.value = filters.search ?? ''
        branchId.value = filters.branch_id ?? ''
        status.value = filters.status ?? ''
    },
    {
        deep: true,
        immediate: true,
    }
)

/*
|--------------------------------------------------------------------------
| Selection
|--------------------------------------------------------------------------
*/

const selectedRows = ref([])
const selectAllRef = ref(null)

const currentRows = computed(
    () => props.fundTransfers?.data ?? []
)

const isAllSelected = computed(() =>
    currentRows.value.length > 0 &&
    currentRows.value.every((row) =>
        selectedRows.value.includes(row.id)
    )
)

const isIndeterminate = computed(() => {
    const selectedCount = currentRows.value.filter((row) =>
        selectedRows.value.includes(row.id)
    ).length

    return selectedCount > 0 &&
        selectedCount < currentRows.value.length
})

function toggleSelectAll(event) {
    selectedRows.value = event.target.checked
        ? currentRows.value.map((row) => row.id)
        : []
}

function toggleSelectRow(id, event) {
    if (event.target.checked) {
        if (!selectedRows.value.includes(id)) {
            selectedRows.value.push(id)
        }
    } else {
        selectedRows.value = selectedRows.value.filter(
            (selectedId) => selectedId !== id
        )
    }
}

watch(
    [isAllSelected, isIndeterminate],
    () => {
        if (selectAllRef.value) {
            selectAllRef.value.indeterminate =
                isIndeterminate.value
        }
    },
    { flush: 'post' }
)

/*
|--------------------------------------------------------------------------
| Transfer Display Helpers
|--------------------------------------------------------------------------
*/

function getTransferType(transfer) {
    const fromBranchId = transfer.from_branch_id
    const toBranchId = transfer.to_branch_id

    if (fromBranchId && toBranchId) {
        return Number(fromBranchId) === Number(toBranchId)
            ? 'Same Branch'
            : 'Inter-Branch'
    }

    if (fromBranchId && !toBranchId) {
        return 'Branch to Company'
    }

    return 'Fund Transfer'
}

function getBranchName(transfer, direction) {
    const branch = direction === 'from'
        ? transfer.from_branch
        : transfer.to_branch

    return branch?.name ?? 'Company'
}

function getAccountName(transfer, direction) {
    const account = direction === 'from'
        ? transfer.from_account
        : transfer.to_account

    if (!account) {
        return '-'
    }

    return [
        account.code,
        account.name,
    ].filter(Boolean).join(' - ')
}

/*
|--------------------------------------------------------------------------
| Create / Edit Modal
|--------------------------------------------------------------------------
*/

const showFormModal = ref(false)
const formMode = ref('create')
const selectedTransfer = ref(null)

function openCreate() {
    formMode.value = 'create'
    selectedTransfer.value = null
    showFormModal.value = true
}

function openEdit(transfer) {
    const transferStatus = String(
        transfer?.status ?? ''
    ).toLowerCase()

    if (!['draft', 'rejected'].includes(transferStatus)) {
        error('Only Draft or Rejected transfers can be edited.')
        return
    }

    selectedTransfer.value = transfer
    formMode.value = 'edit'
    showFormModal.value = true
}

function closeFormModal() {
    showFormModal.value = false
    selectedTransfer.value = null
}

function handleFormSuccess() {
    success(
        'Success',
        'Fund transfer saved successfully.'
    )

    closeFormModal()
    refreshData()
}

/*
|--------------------------------------------------------------------------
| View / Workflow Modal
|--------------------------------------------------------------------------
*/

const showViewModal = ref(false)
const viewLoading = ref(false)

async function openView(transfer) {
    selectedTransfer.value = transfer
    showViewModal.value = true
    viewLoading.value = true

    try {
        const response = await fetch(
            route('fund-transfers.data', transfer.id),
            {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            }
        )

        if (!response.ok) {
            throw new Error(
                'Failed to load fund transfer details.'
            )
        }

        selectedTransfer.value = await response.json()
    } catch (error) {
        console.error(
            'Failed to load fund transfer:',
            error
        )
    } finally {
        viewLoading.value = false
    }
}
function closeViewModal() {
    showViewModal.value = false
    selectedTransfer.value = null
}

function handleViewEdit(transfer) {
    showViewModal.value = false

    openEdit(
        transfer ?? selectedTransfer.value
    )
}

function handleViewRefresh() {
    showViewModal.value = false
    refreshData()
}
/*
|--------------------------------------------------------------------------
| Format Amount
|--------------------------------------------------------------------------
*/

const formatAmount = (amount) => {
    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(Number(amount ?? 0))
}

/*
|--------------------------------------------------------------------------
| Workflow Actions
|--------------------------------------------------------------------------
*/

async function handleSubmit() {
    if (!selectedTransfer.value) return

    const result = await Swal.fire({
        title: 'Submit Fund Transfer?',
        text: 'This transfer will be submitted for approval.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Submit',
        cancelButtonText: 'Back',
        reverseButtons: true,
    })

    if (!result.isConfirmed) return

    loading.value = true

    router.post(
        route('fund-transfers.submit', selectedTransfer.value.id),
        {},
        {
            preserveScroll: true,

            onSuccess: () => {
                success('Fund transfer submitted successfully.')
                handleViewRefresh()
            },

            onError: (errors) => {
                error(
                    errors?.fund_transfer ??
                    'Failed to submit fund transfer.'
                )
            },

            onFinish: () => {
                loading.value = false
            },
        }
    )
}
async function handleApprove() {
    if (!selectedTransfer.value) return

    const result = await Swal.fire({
        title: 'Approve Fund Transfer?',
        text: 'This transfer will be approved.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Approve',
        cancelButtonText: 'Back',
        reverseButtons: true,
    })

    if (!result.isConfirmed) return

    loading.value = true

    router.post(
        route('fund-transfers.approve', selectedTransfer.value.id),
        {},
        {
            preserveScroll: true,

            onSuccess: () => {
                success('Fund transfer approved successfully.')
                handleViewRefresh()
            },

            onError: (errors) => {
                error(
                    errors?.fund_transfer ??
                    'Failed to approve fund transfer.'
                )
            },

            onFinish: () => {
                loading.value = false
            },
        }
    )
}
async function handleReject() {
    if (!selectedTransfer.value) return

    const result = await Swal.fire({
        title: 'Reject Fund Transfer?',
        text: 'Please provide the reason for rejection.',
        icon: 'warning',
        input: 'textarea',
        inputLabel: 'Rejection Reason',
        inputPlaceholder: 'Enter rejection reason...',
        inputAttributes: {
            'aria-label': 'Rejection reason',
        },
        inputValidator: (value) => {
            if (!value || !value.trim()) {
                return 'Rejection reason is required.'
            }

            if (value.trim().length < 3) {
                return 'Rejection reason must be at least 3 characters.'
            }

            if (value.trim().length > 1000) {
                return 'Rejection reason cannot exceed 1000 characters.'
            }

            return undefined
        },
        showCancelButton: true,
        confirmButtonText: 'Yes, Reject',
        cancelButtonText: 'Back',
        confirmButtonColor: '#dc2626',
        reverseButtons: true,
    })

    if (!result.isConfirmed) return

    loading.value = true

    router.post(
        route('fund-transfers.reject', selectedTransfer.value.id),
        {
            reason: result.value.trim(),
        },
        {
            preserveScroll: true,

            onSuccess: () => {
                success('Fund transfer rejected successfully.')
                handleViewRefresh()
            },

            onError: (errors) => {
                error(
                    errors?.reason ??
                    errors?.fund_transfer ??
                    'Failed to reject fund transfer.'
                )
            },

            onFinish: () => {
                loading.value = false
            },
        }
    )
}
async function handlePost() {
    if (!selectedTransfer.value) return

    const result = await Swal.fire({
        title: 'Post Fund Transfer?',
        text: 'Posting will create the accounting journal entry. This action cannot be undone through this workflow.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Post',
        cancelButtonText: 'Back',
        confirmButtonColor: '#2563eb',
        reverseButtons: true,
    })

    if (!result.isConfirmed) return

    loading.value = true

    router.post(
        route('fund-transfers.post', selectedTransfer.value.id),
        {},
        {
            preserveScroll: true,

            onSuccess: () => {
                success('Fund transfer posted successfully.')
                handleViewRefresh()
            },

            onError: (errors) => {
                error(
                    errors?.fund_transfer ??
                    'Failed to post fund transfer.'
                )
            },

            onFinish: () => {
                loading.value = false
            },
        }
    )
}
/*
|--------------------------------------------------------------------------
| Cancel
|--------------------------------------------------------------------------
*/

async function handleCancel() {
    if (!selectedTransfer.value) return

    const result = await Swal.fire({
        title: 'Cancel Fund Transfer?',
        text: 'Please provide the reason for cancellation.',
        icon: 'warning',
        input: 'textarea',
        inputLabel: 'Cancellation Reason',
        inputPlaceholder: 'Enter cancellation reason...',
        inputAttributes: {
            'aria-label': 'Cancellation reason',
        },
        inputValidator: (value) => {
            if (!value || !value.trim()) {
                return 'Cancellation reason is required.'
            }

            if (value.trim().length < 3) {
                return 'Cancellation reason must be at least 3 characters.'
            }

            if (value.trim().length > 1000) {
                return 'Cancellation reason cannot exceed 1000 characters.'
            }

            return undefined
        },
        showCancelButton: true,
        confirmButtonText: 'Yes, Cancel Transfer',
        cancelButtonText: 'Back',
        confirmButtonColor: '#dc2626',
        reverseButtons: true,
    })

    if (!result.isConfirmed) return

    loading.value = true

    router.post(
        route(
            'fund-transfers.cancel',
            selectedTransfer.value.id
        ),
        {
            reason: result.value.trim(),
        },
        {
            preserveScroll: true,

            onSuccess: () => {
                success('Fund transfer cancelled successfully.')
                handleViewRefresh()
            },

            onError: (errors) => {
                error(
                    errors?.reason ??
                    errors?.fund_transfer ??
                    'Failed to cancel fund transfer.'
                )
            },

            onFinish: () => {
                loading.value = false
            },
        }
    )
}
/*
|--------------------------------------------------------------------------
| Row Actions
|--------------------------------------------------------------------------
*/

function getRowActions(transfer) {
    const transferStatus = String(
        transfer?.status ?? ''
    ).toLowerCase()

    const actions = [
        {
            label: 'View',
            value: 'view',
        },
    ]

    if (['draft', 'rejected'].includes(transferStatus)) {
        actions.push({
            label: 'Edit',
            value: 'edit',
        })
    }

    if (
        ['draft', 'rejected', 'cancelled'].includes(transferStatus) &&
        !transfer.journal_entry_id
    ) {
        actions.push({
            label: 'Delete',
            value: 'delete',
            danger: true,
        })
    }

    return actions
}

function handleRowAction(action, transfer) {
    const value = typeof action === 'string'
        ? action
        : action?.value ?? action?.action

    switch (value) {
        case 'view':
            openView(transfer)
            break

        case 'edit':
            openEdit(transfer)
            break

        case 'delete':
            confirmDelete(transfer)
            break
    }
}

/*
|--------------------------------------------------------------------------
| Delete
|--------------------------------------------------------------------------
*/

async function confirmDelete(transfer) {
    const result = await Swal.fire({
        title: 'Delete Fund Transfer?',
        text: `Transfer ${transfer.transfer_number ?? transfer.code ?? ''} will be deleted.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Back',
        confirmButtonColor: '#dc2626',
        reverseButtons: true,
    })

    if (!result.isConfirmed) {
        return
    }

    loading.value = true

    router.delete(
        route('fund-transfers.destroy', transfer.id),
        {
            preserveScroll: true,

            onSuccess: () => {
                success('Fund transfer deleted successfully.')
                refreshData()
            },

            onError: (errors) => {
                error(
                    errors?.message ??
                    'Failed to delete fund transfer.'
                )
            },

            onFinish: () => {
                loading.value = false
            },
        }
    )
}

async function bulkDelete() {
    if (!selectedRows.value.length) {
        return
    }

    const result = await Swal.fire({
        title: 'Delete Selected Transfers?',
        text: `${selectedRows.value.length} transfer(s) will be deleted.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Back',
        confirmButtonColor: '#dc2626',
        reverseButtons: true,
    })

    if (!result.isConfirmed) {
        return
    }

    loading.value = true

    router.post(
        route('fund-transfers.bulk-delete'),
        {
            ids: selectedRows.value,
        },
        {
            preserveScroll: true,

            onSuccess: () => {
                selectedRows.value = []

                success('Selected transfers deleted successfully.')
                refreshData()
            },

            onError: (errors) => {
                error(
                    errors?.message ??
                    'Failed to delete selected transfers.'
                )
            },

            onFinish: () => {
                loading.value = false
            },
        }
    )
}
</script>

<template>
    <AppLayout title="Fund Transfer">
        <section class="space-y-6">

            <!-- HEADER -->
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-start gap-3">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h14m0 0-4-4m4 4-4 4M17 17H3m0 0 4 4m-4-4 4-4" />
                        </svg>
                    </div>

                    <div>
                        <h1 class="text-xl font-bold text-gray-900">
                            Fund Transfer
                        </h1>
                        <p class="mt-1 text-sm text-gray-500">
                            Kelola pemindahan dana antar rekening dan cabang.
                        </p>
                    </div>
                </div>

                <!-- DATE RANGE + NEW TRANSFER -->
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div ref="dateRangePickerRef" class="relative">
                        <button
                            type="button"
                            @click="showDateRangePicker = !showDateRangePicker"
                            class="flex w-full items-center justify-between gap-3 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm transition hover:bg-gray-50 sm:min-w-[260px]"
                        >
                            <span class="flex min-w-0 items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <rect x="3" y="4" width="18" height="17" rx="2" />
                                    <path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18" />
                                </svg>

                                <span class="truncate text-gray-700">
                                    {{ reportDateRangeLabel }}
                                </span>
                            </span>

                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                            </svg>
                        </button>

                        <!-- DATE RANGE POPUP -->
                        <div
                            v-if="showDateRangePicker"
                            class="absolute right-0 z-50 mt-2 max-w-[calc(100vw-2rem)] overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl"
                        >
                            <div class="flex flex-col sm:flex-row">
                                <!-- PRESETS -->
                                <div class="w-full shrink-0 border-b border-gray-200 py-2 sm:w-[170px] sm:border-b-0 sm:border-r">
                                    <button
                                        v-for="preset in presets"
                                        :key="preset.key"
                                        type="button"
                                        class="block w-full px-4 py-2.5 text-left text-sm transition"
                                        :class="selectedPreset === preset.key
                                            ? 'bg-blue-600 font-medium text-white'
                                            : 'text-gray-600 hover:bg-gray-50'"
                                        @click="applyReportPreset(preset.key)"
                                    >
                                        {{ preset.label }}
                                    </button>
                                </div>

                                <!-- INLINE CALENDAR -->
                                <div class="max-w-full overflow-x-auto p-4">
                                    <FlatPickr
                                        v-model="calendarRange"
                                        :config="{
                                            mode: 'range',
                                            inline: true,
                                            showMonths: isMobile ? 1 : 2,
                                            dateFormat: 'Y-m-d',
                                            allowInput: false,
                                            clickOpens: true,
                                            onChange: handleCalendarChange,
                                        }"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="openCreate"
                        class="inline-flex w-full shrink-0 items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 sm:w-auto"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14" />
                        </svg>
                        New Transfer
                    </button>
                </div>
            </div>

            <!-- STATISTICS -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div
                    v-for="stat in statCards"
                    :key="stat.label"
                    class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm"
                >
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-medium text-gray-500">
                                {{ stat.label }}
                            </p>
                            <p class="mt-2 text-2xl font-bold text-gray-900">
                                {{ stat.value }}
                            </p>
                        </div>

                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl"
                            :class="{
                                'bg-blue-100 text-blue-700': stat.icon === 'transfer',
                                'bg-gray-100 text-gray-700': stat.icon === 'document',
                                'bg-amber-100 text-amber-700': stat.icon === 'clock',
                                'bg-green-100 text-green-700': stat.icon === 'check',
                            }"
                        >
                            <svg v-if="stat.icon === 'transfer'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h14m0 0-4-4m4 4-4 4M17 17H3m0 0 4 4m-4-4 4-4" />
                            </svg>

                            <svg v-else-if="stat.icon === 'document'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h8M8 11h8M8 15h5m-8 6h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H7L3 7v12a2 2 0 0 0 2 2Z" />
                            </svg>

                            <svg v-else-if="stat.icon === 'clock'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <circle cx="12" cy="12" r="9" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2" />
                            </svg>

                            <svg v-else-if="stat.icon === 'check'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                                <circle cx="12" cy="12" r="9" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FILTERS -->
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <div class="space-y-4">

                    <!-- SEARCH FULL WIDTH -->
                    <div>
                        <label for="fund-transfer-search" class="mb-1.5 block text-sm font-medium text-gray-700">
                            Search
                        </label>

                        <div class="relative">
                            <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <circle cx="11" cy="11" r="7" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="m20 20-4-4" />
                            </svg>

                            <input
                                id="fund-transfer-search"
                                v-model="search"
                                type="text"
                                placeholder="Search transfer number..."
                                class="w-full rounded-lg border border-gray-300 py-2.5 pl-9 pr-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                            />
                        </div>
                    </div>

                    <!-- BRANCH, STATUS, RESET -->
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-12 sm:items-end">
                        <div class="sm:col-span-5">
                            <label for="fund-transfer-branch" class="mb-1.5 block text-sm font-medium text-gray-700">
                                Branch
                            </label>

                            <select
                                id="fund-transfer-branch"
                                v-model="branchId"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                            >
                                <option value="">All Branches</option>
                                <option
                                    v-for="branch in branchOptions"
                                    :key="branch.value"
                                    :value="branch.value"
                                >
                                    {{ branch.label }}
                                </option>
                            </select>
                        </div>

                        <div class="sm:col-span-5">
                            <label for="fund-transfer-status" class="mb-1.5 block text-sm font-medium text-gray-700">
                                Status
                            </label>

                            <select
                                id="fund-transfer-status"
                                v-model="status"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                            >
                                <option value="">All Statuses</option>
                                <option
                                    v-for="item in statusOptions"
                                    :key="item.value"
                                    :value="item.value"
                                >
                                    {{ item.label }}
                                </option>
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <button
                                type="button"
                                @click="refresh"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7v5h-5M4 17v-5h5m11-3a8 8 0 0 0-14-3L4 12m16 0v3a8 8 0 0 1-14 3" />
                                </svg>
                                Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABLE -->
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="flex flex-col gap-3 border-b border-gray-200 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">
                            Fund Transfers
                        </h2>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ fundTransfers.meta?.total ?? fundTransfers.data?.length ?? 0 }}
                            transfer records
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <span v-if="selectedRows.length" class="text-sm text-gray-600">
                            {{ selectedRows.length }} selected
                        </span>

                        <button
                            v-if="selectedRows.length"
                            type="button"
                            @click="bulkDelete"
                            class="inline-flex items-center gap-2 rounded-lg border border-red-200 px-3 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M10 11v6m4-6v6M5 7l1 14h12l1-14M9 7V4h6v3" />
                            </svg>
                            Delete Selected
                        </button>
                    </div>
                </div>

                <div class="relative">
                    <div class="overflow-x-auto">
                        <table class="min-w-[1050px] w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="w-12 px-4 py-3 text-left">
                                        <input
                                            ref="selectAllRef"
                                            type="checkbox"
                                            :checked="isAllSelected"
                                            :disabled="!fundTransfers.data?.length"
                                            @change="toggleSelectAll"
                                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        />
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                        Transfer Information
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                        Transfer Accounts
                                    </th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">
                                        Amount
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                                        Status
                                    </th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">
                                        Actions
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100 bg-white">
                                <tr
                                    v-for="transfer in fundTransfers.data"
                                    :key="transfer.id"
                                    class="transition hover:bg-gray-50"
                                >
                                    <td class="px-4 py-4 align-top">
                                        <input
                                            type="checkbox"
                                            :checked="selectedRows.includes(transfer.id)"
                                            @change="toggleSelectRow(transfer.id)"
                                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        />
                                    </td>

                                    <td class="px-4 py-4 align-top">
                                        <button
                                            type="button"
                                            @click="openView(transfer)"
                                            class="text-left text-sm font-semibold text-blue-600 hover:text-blue-800 hover:underline"
                                        >
                                            {{ transfer.transfer_number ?? transfer.code ?? '-' }}
                                        </button>

                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ formatDate(transfer.transfer_date ?? transfer.date) }}
                                        </p>

                                        <span class="mt-2 inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700">
                                            {{ getTransferType(transfer) }}
                                        </span>

                                        <p
                                            v-if="transfer.description"
                                            class="mt-2 max-w-xs truncate text-xs text-gray-500"
                                            :title="transfer.description"
                                        >
                                            {{ transfer.description }}
                                        </p>
                                    </td>

                                    <td class="px-4 py-4 align-top">
                                        <div class="space-y-3">
                                            <div>
                                                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">From</p>
                                                <p class="mt-1 text-sm font-medium text-gray-800">
                                                    {{ getBranchName(transfer, 'from') }}
                                                </p>
                                                <p class="mt-0.5 text-xs text-gray-500">
                                                    {{ getAccountName(transfer, 'from') }}
                                                </p>
                                            </div>

                                            <div>
                                                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">To</p>
                                                <p class="mt-1 text-sm font-medium text-gray-800">
                                                    {{ getBranchName(transfer, 'to') }}
                                                </p>
                                                <p class="mt-0.5 text-xs text-gray-500">
                                                    {{ getAccountName(transfer, 'to') }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-4 text-right align-top">
                                        <span class="text-sm font-semibold text-gray-900">
                                            {{ formatAmount(transfer.amount ?? 0) }}
                                        </span>
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-4 text-center align-top">
                                       <StatusBadge
                                            :status="transfer.status"
                                        />
                                    </td>

                                    <td class="px-4 py-4 text-right align-top">
                                        <div class="inline-flex items-center gap-1">
                                            <button
                                                type="button"
                                                @click="openView(transfer)"
                                                title="View"
                                                class="rounded-lg p-2 text-gray-500 transition hover:bg-blue-50 hover:text-blue-700"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-7 9.75-7 9.75 7 9.75 7-3.5 7-9.75 7-9.75-7-9.75-7Z" />
                                                    <circle cx="12" cy="12" r="3" />
                                                </svg>
                                            </button>

                                            <button
                                                v-if="['draft', 'rejected'].includes(transfer.status)"
                                                type="button"
                                                @click="openEdit(transfer)"
                                                title="Edit"
                                                class="rounded-lg p-2 text-gray-500 transition hover:bg-amber-50 hover:text-amber-700"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 2.651 2.651M7.5 20.25H3.75V16.5L16.9 3.35a1.875 1.875 0 0 1 2.652 2.652L7.5 20.25Z" />
                                                </svg>
                                            </button>

                                            <button
                                                v-if="['draft', 'rejected', 'cancelled'].includes(transfer.status) && !transfer.journal_entry_id"
                                                type="button"
                                                @click="handleRowAction({ value: 'delete' }, transfer)"
                                                title="Delete"
                                                class="rounded-lg p-2 text-gray-500 transition hover:bg-red-50 hover:text-red-700"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M10 11v6m4-6v6M5 7l1 14h12l1-14M9 7V4h6v3" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <tr v-if="!fundTransfers.data?.length">
                                    <td colspan="6" class="px-6 py-16 text-center">
                                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 text-gray-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h14m0 0-4-4m4 4-4 4M17 17H3m0 0 4 4m-4-4 4-4" />
                                            </svg>
                                        </div>

                                        <h3 class="mt-4 text-sm font-semibold text-gray-900">
                                            No fund transfers found
                                        </h3>
                                        <p class="mt-1 text-sm text-gray-500">
                                            Belum ada data transfer yang sesuai dengan filter.
                                        </p>

                                        <button
                                            type="button"
                                            @click="refresh"
                                            class="mt-4 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                        >
                                            Reset Filters
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- LOADING -->
                    <div
                        v-if="loading"
                        class="absolute inset-0 z-20 flex items-center justify-center bg-white/70"
                    >
                        <div class="flex items-center gap-3 rounded-lg bg-white px-4 py-3 shadow-md">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 animate-spin text-blue-600" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z" />
                            </svg>
                            <span class="text-sm font-medium text-gray-700">Loading...</span>
                        </div>
                    </div>
                </div>

                <!-- PAGINATION -->
                <div
                    v-if="fundTransfers.links?.length"
                    class="flex flex-col gap-3 border-t border-gray-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <p class="text-sm text-gray-500">
                        Showing
                        <span class="font-medium text-gray-700">{{ fundTransfers.meta?.from ?? 0 }}</span>
                        to
                        <span class="font-medium text-gray-700">{{ fundTransfers.meta?.to ?? 0 }}</span>
                        of
                        <span class="font-medium text-gray-700">{{ fundTransfers.meta?.total ?? 0 }}</span>
                        results
                    </p>

                    <div class="flex flex-wrap items-center gap-1">
                        <button
                            v-for="(link, index) in fundTransfers.links"
                            :key="`${index}-${link.label}`"
                            type="button"
                            :disabled="!link.url || loading"
                            @click="link.url && router.visit(link.url, {
                                preserveState: true,
                                preserveScroll: true,
                            })"
                            class="min-w-9 rounded-lg border px-3 py-2 text-sm transition"
                            :class="{
                                'border-blue-600 bg-blue-600 text-white': link.active,
                                'border-gray-200 bg-white text-gray-700 hover:bg-gray-50': !link.active && link.url,
                                'cursor-not-allowed border-gray-100 bg-gray-50 text-gray-300': !link.url,
                            }"
                        >
                            <span v-html="link.label" />
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- CREATE / EDIT MODAL -->
        <FundTransferForm
            v-if="showFormModal"
            :show="showFormModal"
            :transfer="selectedTransfer"
            :preview-number="previewNumber"
            :branches="branches"
            @close="closeFormModal"
            @success="handleFormSuccess"
        />

        <!-- VIEW / WORKFLOW MODAL -->
       <FundTransferViewModal
            v-if="showViewModal"
            :show="showViewModal"
            :transfer="selectedTransfer"
            :loading="viewLoading"
            :processing="processing"
            @close="closeViewModal"
            @edit="handleViewEdit"
            @refresh="handleViewRefresh"
            @submit="handleSubmit"
            @approve="handleApprove"
            @reject="handleReject"
            @post="handlePost"
            @cancel="handleCancel"
        />

    </AppLayout>
</template>