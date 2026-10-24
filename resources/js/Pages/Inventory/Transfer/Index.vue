<script setup>
import {computed,ref,onMounted,onBeforeUnmount,} from 'vue'
import Swal from 'sweetalert2'
import { router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import FlatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'
import StockTransferIndex from './StockTransferIndex.vue'
import ViewStockTransferModal from './ViewStockTransferModal.vue'
//import StockTransferReturnIndex from './StockTransferReturnIndex.vue'
//import StockTransferInIndex from './StockTransferInIndex.vue'
//import StockTransferOutIndex from './StockTransferOutIndex.vue'
//import StockTransferHistoryIndex from './StockTransferHistoryIndex.vue'
import StockTransferFormModal from './StockTransferFormModal.vue'
/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({
    transfers: {
        type: Object,
        default: () => ({
            data: [],
            links: [],
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

/*
|--------------------------------------------------------------------------
| Dashboard Tabs
|--------------------------------------------------------------------------
*/

const tabs = [
    {
        key: 'stock-transfer',
        label: 'Stock Transfer',
        description: 'Manage stock transfer transactions.',
    },
    {
        key: 'transfer-return',
        label: 'Transfer Return',
        description: 'Manage returns against original stock transfers.',
    },
    {
        key: 'transfer-in',
        label: 'Transfer In',
        description: 'Review stock received from other branches.',
    },
    {
        key: 'transfer-out',
        label: 'Transfer Out',
        description: 'Review stock sent to other branches.',
    },
    {
        key: 'history',
        label: 'History',
        description: 'Review stock transfer transaction history.',
    },
]

function getInitialTab() {
    if (typeof window === 'undefined') {
        return 'stock-transfer'
    }

    const params = new URLSearchParams(window.location.search)
    const tab = params.get('tab')

    return tabs.some((item) => item.key === tab)
        ? tab
        : 'stock-transfer'
}

const activeTab = ref(getInitialTab())

const activeTabDetails = computed(() =>
    tabs.find((tab) => tab.key === activeTab.value) ?? tabs[0]
)

/*
|--------------------------------------------------------------------------
| Tab Navigation
|--------------------------------------------------------------------------
*/

function changeTab(tabKey) {
    if (!tabs.some((tab) => tab.key === tabKey)) {
        return
    }

    activeTab.value = tabKey

    const url = new URL(window.location.href)

    if (tabKey === 'stock-transfer') {
        url.searchParams.delete('tab')
    } else {
        url.searchParams.set('tab', tabKey)
    }

    window.history.replaceState(
        window.history.state,
        '',
        url,
    )
}
/*
|--------------------------------------------------------------------------
| Stock Transfer View Modal
|--------------------------------------------------------------------------
*/

const showViewModal = ref(false)
const viewTransfer = ref(null)
const viewLoading = ref(false)
const viewProcessing = ref(false)

let viewRequestId = 0

async function openView(item) {
    if (!item?.id) return

    const requestId = ++viewRequestId

    viewTransfer.value = item
    showViewModal.value = true
    viewLoading.value = true

    try {
        const response = await fetch(
            route('stock-transfers.data', item.id),
            {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            },
        )

        if (!response.ok) {
            throw new Error(
                `Failed to load stock transfer (${response.status})`,
            )
        }

        const result = await response.json()

        if (requestId !== viewRequestId) return

        viewTransfer.value = result.data ?? result
    } catch (error) {
        console.error(
            'Failed to load stock transfer details:',
            error,
        )
    } finally {
        if (requestId === viewRequestId) {
            viewLoading.value = false
        }
    }
}

function closeViewModal() {
    if (viewProcessing.value) return

    viewRequestId++
    showViewModal.value = false
    viewTransfer.value = null
    viewLoading.value = false
}

function editFromView(transfer) {
    if (!transfer) return

    closeViewModal()
    openEdit(transfer)
}

async function postFromView(transfer) {
    if (!transfer?.id || viewProcessing.value) return

    const result = await Swal.fire({
        title: 'Post Stock Transfer?',
        text: `Stock transfer ${transfer.number} will be posted. This action will update inventory and accounting.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Post Transfer',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        focusCancel: true,
    })

    if (!result.isConfirmed) return

    viewProcessing.value = true

    router.post(
        route('stock-transfers.post', transfer.id),
        {},
        {
            preserveScroll: true,

            onSuccess: () => {
                showViewModal.value = false
                viewTransfer.value = null

                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Stock transfer posted successfully.',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                })

                router.reload({
                    only: [
                        'transfers',
                        'statistics',
                        'filters',
                    ],
                    preserveScroll: true,
                })
            },

            onError: (errors) => {
                console.error(
                    'Post Stock Transfer Error:',
                    errors,
                )

                Swal.fire({
                    icon: 'error',
                    title: 'Post Failed',
                    text: 'Unable to post stock transfer. Please check the validation errors.',
                })
            },

            onFinish: () => {
                viewProcessing.value = false
            },
        },
    )
}
/*
|--------------------------------------------------------------------------
| Date Range
|--------------------------------------------------------------------------
*/

const showDateRangePicker = ref(false)
const dateRangePickerRef = ref(null)
const isMobile = ref(false)

const selectedPreset = ref('custom')

const dateFrom = ref(props.filters?.date_from ?? '')
const dateTo = ref(props.filters?.date_to ?? '')

const calendarRange = ref([])

const presets = [
    { key: 'today', label: 'Today' },
    { key: 'yesterday', label: 'Yesterday' },
    { key: 'last-7-days', label: 'Last 7 Days' },
    { key: 'last-30-days', label: 'Last 30 Days' },
    { key: 'this-month', label: 'This Month' },
    { key: 'last-month', label: 'Last Month' },
    { key: 'custom', label: 'Custom Range' },
]

function updateIsMobile() {
    isMobile.value = window.innerWidth < 640
}

function parseDate(value) {
    if (!value || typeof value !== 'string') {
        return null
    }

    const [year, month, day] = value.split('-').map(Number)

    if (!year || !month || !day) {
        return null
    }

    return new Date(year, month - 1, day)
}

function toDateString(date) {
    if (!(date instanceof Date) || Number.isNaN(date.getTime())) {
        return ''
    }

    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')

    return `${year}-${month}-${day}`
}

function startOfDay(date) {
    const result = new Date(date)
    result.setHours(0, 0, 0, 0)

    return result
}

function formatDisplayDate(value) {
    const date = parseDate(value)

    if (!date) {
        return ''
    }

    return new Intl.DateTimeFormat('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(date)
}

const dateRangeLabel = computed(() => {
    if (!dateFrom.value || !dateTo.value) {
        return 'All Dates'
    }

    return `${formatDisplayDate(dateFrom.value)} - ${formatDisplayDate(dateTo.value)}`
})

function handleClickOutsideDateRange(event) {
    if (
        showDateRangePicker.value &&
        dateRangePickerRef.value &&
        !dateRangePickerRef.value.contains(event.target)
    ) {
        showDateRangePicker.value = false
    }
}

function applyDateRange(from, to, preset = 'custom') {
    dateFrom.value = from
    dateTo.value = to
    selectedPreset.value = preset

    calendarRange.value = [
        parseDate(from),
        parseDate(to),
    ].filter(Boolean)

    showDateRangePicker.value = false

    loadDateFilteredData()
}

function applyPreset(preset) {
    if (preset === 'custom') {
        selectedPreset.value = 'custom'
        return
    }

    const today = startOfDay(new Date())

    let from
    let to

    switch (preset) {
        case 'today':
            from = today
            to = today
            break

        case 'yesterday':
            from = new Date(today)
            from.setDate(from.getDate() - 1)
            to = from
            break

        case 'last-7-days':
            from = new Date(today)
            from.setDate(from.getDate() - 6)
            to = today
            break

        case 'last-30-days':
            from = new Date(today)
            from.setDate(from.getDate() - 29)
            to = today
            break

        case 'this-month':
            from = new Date(
                today.getFullYear(),
                today.getMonth(),
                1,
            )
            to = today
            break

        case 'last-month':
            from = new Date(
                today.getFullYear(),
                today.getMonth() - 1,
                1,
            )
            to = new Date(
                today.getFullYear(),
                today.getMonth(),
                0,
            )
            break

        default:
            return
    }

    applyDateRange(
        toDateString(from),
        toDateString(to),
        preset,
    )
}

function handleCalendarChange(selectedDates) {
    if (selectedDates.length !== 2) {
        return
    }

    applyDateRange(
        toDateString(selectedDates[0]),
        toDateString(selectedDates[1]),
        'custom',
    )
}

/*
|--------------------------------------------------------------------------
| Date Filter Integration
|--------------------------------------------------------------------------
|
| Dashboard passes the selected range to the active tab.
| The active tab remains responsible for loading its own data.
|--------------------------------------------------------------------------
*/

function loadDateFilteredData() {
    if (activeTab.value === 'stock-transfer') {
        router.get(
            route('stock-transfers.index'),
            {
                ...props.filters,
                date_from: dateFrom.value,
                date_to: dateTo.value,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
                only: ['transfers', 'statistics', 'filters'],
            },
        )
    }
}

function resetDateRange() {
    dateFrom.value = ''
    dateTo.value = ''
    calendarRange.value = []
    selectedPreset.value = 'custom'
    showDateRangePicker.value = false

    loadDateFilteredData()
}

/*
|--------------------------------------------------------------------------
| Lifecycle
|--------------------------------------------------------------------------
*/

onMounted(() => {
    updateIsMobile()

    window.addEventListener('resize', updateIsMobile)
    document.addEventListener('mousedown', handleClickOutsideDateRange)
})

onBeforeUnmount(() => {
    window.removeEventListener('resize', updateIsMobile)
    document.removeEventListener('mousedown', handleClickOutsideDateRange)
})
/*
|--------------------------------------------------------------------------
| Stock Transfer Form Modal
|--------------------------------------------------------------------------
*/

const showCreateModal = ref(false)
const showEditModal = ref(false)
const selectedTransfer = ref(null)

const form = useForm({
    number: '',
    transaction_date: '',
    from_branch_id: null,
    from_warehouse_id: null,
    to_branch_id: null,
    to_warehouse_id: null,
    description: '',
    details: [],
})

function resetTransferForm() {
    form.reset()

    form.number = props.previewNumber ?? ''
    form.transaction_date = new Date().toISOString().slice(0, 10)

    form.from_branch_id = null
    form.from_warehouse_id = null
    form.to_branch_id = null
    form.to_warehouse_id = null

    form.description = ''
    form.details = []
    form.clearErrors()
}

function openCreate() {
    selectedTransfer.value = null

    showEditModal.value = false

    resetTransferForm()

    showCreateModal.value = true
}

function openEdit(transfer) {
    selectedTransfer.value = transfer

    showCreateModal.value = false
    showEditModal.value = true
}

function closeFormModal() {
    showCreateModal.value = false
    showEditModal.value = false
    selectedTransfer.value = null

    resetTransferForm()
}

function handleCreateSuccess() {
    closeFormModal()

    router.reload({
        only: [
            'transfers',
            'statistics',
            'filters',
            'previewNumber',
        ],
        preserveScroll: true,
    })
}

/*
|--------------------------------------------------------------------------
| Sync Date Filters
|--------------------------------------------------------------------------
*/

function syncDateFilters() {
    dateFrom.value = props.filters?.date_from ?? ''
    dateTo.value = props.filters?.date_to ?? ''

    calendarRange.value = [
        parseDate(dateFrom.value),
        parseDate(dateTo.value),
    ].filter(Boolean)
}
async function rejectFromView(transfer) {
    if (!transfer?.id || viewProcessing.value) return

    const result = await Swal.fire({
        title: 'Reject Stock Transfer?',
        text: `Are you sure you want to reject stock transfer ${transfer.number}?`,
        icon: 'warning',
        input: 'textarea',
        inputLabel: 'Rejection Reason',
        inputPlaceholder: 'Enter the reason for rejection...',
        inputAttributes: {
            'aria-label': 'Rejection reason',
        },
        inputValidator: (value) => {
            if (!value || !value.trim()) {
                return 'Rejection reason is required.'
            }

            return undefined
        },
        showCancelButton: true,
        confirmButtonText: 'Yes, Reject',
        cancelButtonText: 'Back',
        confirmButtonColor: '#dc2626',
        reverseButtons: true,
        focusCancel: true,
    })

    if (!result.isConfirmed) return

    viewProcessing.value = true

    router.post(
        route('stock-transfers.cancel', transfer.id),
        {
            reason: result.value.trim(),
        },
        {
            preserveScroll: true,

            onSuccess: () => {
                showViewModal.value = false
                viewTransfer.value = null

                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Stock transfer rejected successfully.',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                })

                router.reload({
                    only: [
                        'transfers',
                        'statistics',
                        'filters',
                    ],
                    preserveScroll: true,
                })
            },

            onError: (errors) => {
                console.error(
                    'Reject Stock Transfer Error:',
                    errors,
                )
            },

            onFinish: () => {
                viewProcessing.value = false
            },
        },
    )
}
async function cancelFromView(transfer) {
    if (!transfer?.id || viewProcessing.value) return

    const result = await Swal.fire({
        title: 'Cancel Stock Transfer?',
        text: `Are you sure you want to cancel stock transfer ${transfer.number}?`,
        icon: 'warning',
        input: 'textarea',
        inputLabel: 'Cancellation Reason',
        inputPlaceholder: 'Enter the reason for cancellation...',
        inputAttributes: {
            'aria-label': 'Cancellation reason',
        },
        inputValidator: (value) => {
            if (!value || !value.trim()) {
                return 'Cancellation reason is required.'
            }

            return undefined
        },
        showCancelButton: true,
        confirmButtonText: 'Yes, Cancel Transfer',
        cancelButtonText: 'Back',
        confirmButtonColor: '#dc2626',
        reverseButtons: true,
        focusCancel: true,
    })

    if (!result.isConfirmed) return

    viewProcessing.value = true

    router.post(
        route('stock-transfers.cancelled', transfer.id),
        {
            reason: result.value.trim(),
        },
        {
            preserveScroll: true,

            onSuccess: () => {
            showViewModal.value = false
            viewTransfer.value = null

            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Stock transfer cancelled successfully.',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            })

            router.reload({
                only: [
                    'transfers',
                    'statistics',
                    'filters',
                ],
                preserveScroll: true,
            })
        },

            onError: (errors) => {
                console.error(
                    'Cancel Stock Transfer Error:',
                    errors,
                )
            },

            onFinish: () => {
                viewProcessing.value = false
            },
        },
    )
}
function refreshViewModal() {
    if (!viewTransfer.value) return

    openView(viewTransfer.value)
}

function printFromView(transfer) {
    if (!transfer) return

    window.print()
}
async function submitFromView(transfer) {
    if (!transfer?.id || viewProcessing.value) return

    viewProcessing.value = true

    router.post(
        route('stock-transfers.submit', transfer.id),
        {},
        {
            preserveScroll: true,

            onSuccess: () => {
                showViewModal.value = false
                viewTransfer.value = null

                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Stock transfer submitted successfully.',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                })

                router.reload({
                    only: [
                        'transfers',
                        'statistics',
                        'filters',
                    ],
                    preserveScroll: true,
                })
            },

            onError: (errors) => {
                console.error(
                    'Submit Stock Transfer Error:',
                    errors,
                )

                Swal.fire({
                    icon: 'error',
                    title: 'Submit Failed',
                    text: 'Unable to submit stock transfer.',
                })
            },

            onFinish: () => {
                viewProcessing.value = false
            },
        },
    )
}

async function approveFromView(transfer) {
    if (!transfer?.id || viewProcessing.value) return

    viewProcessing.value = true

    router.post(
        route('stock-transfers.approve', transfer.id),
        {},
        {
            preserveScroll: true,

            onSuccess: () => {
                showViewModal.value = false
                viewTransfer.value = null

                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Stock transfer approved successfully.',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                })

                router.reload({
                    only: [
                        'transfers',
                        'statistics',
                        'filters',
                    ],
                    preserveScroll: true,
                })
            },

            onError: (errors) => {
                console.error(
                    'Approve Stock Transfer Error:',
                    errors,
                )

                Swal.fire({
                    icon: 'error',
                    title: 'Approval Failed',
                    text: 'Unable to approve stock transfer.',
                })
            },

            onFinish: () => {
                viewProcessing.value = false
            },
        },
    )
}
</script>

<template>
    <AppLayout title="Stock Transfer">
        <div class="min-h-screen bg-slate-50">
            <!-- PAGE HEADER -->
            <header class="border-b border-slate-200 bg-white">
                <div
                    class="mx-auto flex max-w-[1600px] flex-col gap-4 px-4 py-5 sm:px-6 lg:flex-row lg:items-center lg:justify-between"
                >
                    <div class="flex min-w-0 items-center gap-3">
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm shadow-blue-600/20"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-6 w-6"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M4 7h11m0 0-3-3m3 3-3 3M20 17H9m0 0 3 3m-3-3 3-3"
                                />
                                <rect
                                    x="3"
                                    y="12"
                                    width="5"
                                    height="5"
                                    rx="1"
                                />
                                <rect
                                    x="16"
                                    y="4"
                                    width="5"
                                    height="5"
                                    rx="1"
                                />
                            </svg>
                        </div>

                        <div class="min-w-0">
                            <h1
                                class="text-xl font-semibold tracking-tight text-slate-900"
                            >
                                Stock Transfer
                            </h1>

                            <p class="mt-1 text-sm text-slate-500">
                                Manage stock transfers, returns, incoming and outgoing stock.
                            </p>
                        </div>
                    </div>

                    <div
                        v-if="activeTab === 'stock-transfer'"
                        class="flex w-full flex-col gap-3 sm:flex-row lg:w-auto"
                    >
                        <!-- DATE RANGE -->
                        <div
                            ref="dateRangePickerRef"
                            class="relative w-full sm:w-auto"
                        >
                            <button
                                type="button"
                                class="flex w-full items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm transition hover:bg-slate-50 sm:min-w-[270px]"
                                @click="showDateRangePicker = !showDateRangePicker"
                            >
                                <span class="flex min-w-0 items-center gap-2">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4 shrink-0 text-slate-500"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    >
                                        <rect
                                            x="3"
                                            y="4"
                                            width="18"
                                            height="17"
                                            rx="2"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            d="M16 2v4M8 2v4M3 10h18"
                                        />
                                    </svg>

                                    <span class="truncate text-slate-700">
                                        {{ dateRangeLabel }}
                                    </span>
                                </span>

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4 shrink-0 text-slate-400"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m6 9 6 6 6-6"
                                    />
                                </svg>
                            </button>

                            <!-- DATE RANGE POPUP -->
                            <div
                                v-if="showDateRangePicker"
                                class="absolute right-0 z-50 mt-2 w-[min(760px,calc(100vw-2rem))] max-w-[calc(100vw-2rem)] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl"
                            >
                                <div class="flex flex-col sm:flex-row">
                                    <!-- PRESETS -->
                                    <div
                                        class="flex w-full shrink-0 gap-1 overflow-x-auto border-b border-slate-200 p-2 sm:w-[170px] sm:flex-col sm:gap-0 sm:border-b-0 sm:border-r sm:p-2"
                                    >
                                        <button
                                            v-for="preset in presets"
                                            :key="preset.key"
                                            type="button"
                                            class="shrink-0 rounded-lg px-3 py-2.5 text-left text-sm transition sm:w-full"
                                            :class="
                                                selectedPreset === preset.key
                                                    ? 'bg-blue-600 font-medium text-white'
                                                    : 'text-slate-600 hover:bg-slate-50'
                                            "
                                            @click="applyPreset(preset.key)"
                                        >
                                            {{ preset.label }}
                                        </button>

                                        <button
                                            v-if="dateFrom || dateTo"
                                            type="button"
                                            class="shrink-0 rounded-lg px-3 py-2.5 text-left text-sm text-red-600 transition hover:bg-red-50 sm:mt-2"
                                            @click="resetDateRange"
                                        >
                                            Clear Dates
                                        </button>
                                    </div>

                                    <!-- CALENDAR -->
                                    <div class="min-w-0 flex-1 overflow-x-auto p-3 sm:p-4">
                                        <FlatPickr
                                            :model-value="calendarRange"
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

                        <!-- NEW TRANSFER -->
                        <button
                            type="button"
                            class="inline-flex w-full shrink-0 items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-600/20 sm:w-auto"
                            @click="openCreate"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    d="M12 5v14M5 12h14"
                                />
                            </svg>

                            New Transfer
                        </button>
                    </div>
                </div>
            </header>

            <!-- MAIN CONTENT -->
            <main class="mx-auto max-w-[1600px] px-4 py-6 sm:px-6">
                <!-- TABS -->
                <nav
                    class="mb-7 flex overflow-x-auto border-b border-slate-200"
                    aria-label="Stock Transfer Sections"
                >
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        type="button"
                        class="relative whitespace-nowrap px-4 pb-3 text-sm font-medium transition"
                        :class="
                            activeTab === tab.key
                                ? 'text-blue-600'
                                : 'text-slate-500 hover:text-slate-800'
                        "
                        @click="changeTab(tab.key)"
                    >
                        {{ tab.label }}

                        <span
                            v-if="activeTab === tab.key"
                            class="absolute inset-x-3 -bottom-px h-0.5 rounded-full bg-blue-600"
                        />
                    </button>
                </nav>

                <!-- ACTIVE TAB -->
                <section>
                    <div
                        v-if="activeTab !== 'stock-transfer'"
                        class="mb-5"
                    >
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-700">
                            {{ activeTabDetails.label }}
                        </h2>

                        <p class="mt-1 text-xs text-slate-500">
                            {{ activeTabDetails.description }}
                        </p>
                    </div>

               <StockTransferIndex
                    v-if="activeTab === 'stock-transfer'"
                    :transfers="transfers"
                    :statistics="statistics"
                    :branches="branches"
                    :warehouses="warehouses"
                    :variants="variants"
                    :units="units"
                    :filters="filters"
                    :preview-number="previewNumber"
                    :date-from="dateFrom"
                    :date-to="dateTo"
                    @create="openCreate"
                    @view="openView"
                />

                    <StockTransferReturnIndex
                        v-else-if="activeTab === 'transfer-return'"
                    />

                    <StockTransferInIndex
                        v-else-if="activeTab === 'transfer-in'"
                    />

                    <StockTransferOutIndex
                        v-else-if="activeTab === 'transfer-out'"
                    />

                    <StockTransferHistoryIndex
                        v-else-if="activeTab === 'history'"
                    />
                </section>
            </main>
        </div>

        <!-- CREATE MODAL INTEGRATION -->
        <!-- StockTransferFormModal will be connected in the next step. -->
    </AppLayout>
         <StockTransferFormModal
            :show="showCreateModal || showEditModal"
            :transfer="selectedTransfer"
            :form="form"
            :branches="branches"
            :warehouses="warehouses"
            :variants="variants"
            :units="units"
            :preview-number="previewNumber"
            :mode="showEditModal ? 'edit' : 'create'"
            @close="closeFormModal"
            @success="handleCreateSuccess"
        />
        <!-- VIEW STOCK TRANSFER MODAL -->
        <ViewStockTransferModal
            :show="showViewModal"
            :transfer="viewTransfer"
            :loading="viewLoading"
            :processing="viewProcessing"
            @close="closeViewModal"
            @edit="editFromView"
            @submit="submitFromView"
            @approve="approveFromView"
            @post="postFromView"
            @reject="rejectFromView"
            @cancel="cancelFromView"
            @print="printFromView"
            @refresh="refreshViewModal"
        />
</template>