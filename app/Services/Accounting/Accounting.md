<script setup>
import {
    computed,
    onBeforeUnmount,
    onMounted,
    ref,
} from 'vue'

import { router } from '@inertiajs/vue3'
import FlatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'

import StatusBadge from '@/Components/Display/StatusBadge.vue'
import ActionDropdown from '@/Components/Action/ActionDropdown.vue'
import SettlementForm from './Form.vue'
import SettlementView from './View.vue'
import {
    success,
    error,
} from '@/Utils'

import Swal from 'sweetalert2'

/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({
    settlements: {
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

    branches: {
        type: Array,
        default: () => [],
    },

    statistics: {
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
| State
|--------------------------------------------------------------------------
*/
const search = ref(props.filters.search ?? '')
const branchId = ref(props.filters.branch_id ?? '')
const status = ref(props.filters.status ?? '')
const processingId = ref(null)
const handleEditRequest = (settlement) => {
closeView()
openEdit(settlement)
}
const showDateRangePicker = ref(false)
const dateRangePickerRef = ref(null)
const isMobile = ref(false)

const today = new Date()

const firstDayOfMonth = new Date(
    today.getFullYear(),
    today.getMonth(),
    1
)

const toDateString = (date) => {
    if (!date) {
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

const defaultDateFrom = toDateString(
    firstDayOfMonth
)

const defaultDateTo = toDateString(
    today
)

const depositDateFrom = ref(
    props.filters.date_from || defaultDateFrom
)

const depositDateTo = ref(
    props.filters.date_to || defaultDateTo
)

const calendarRange = ref([
    depositDateFrom.value,
    depositDateTo.value,
])

const selectedPreset = ref('this-month')

const settlementLoading = ref(false)

/*
|--------------------------------------------------------------------------
| Responsive
|--------------------------------------------------------------------------
*/

const updateIsMobile = () => {
    isMobile.value = window.innerWidth < 640
}

const handleClickOutsideDateRange = (event) => {
    if (!showDateRangePicker.value) {
        return
    }

    if (
        dateRangePickerRef.value &&
        !dateRangePickerRef.value.contains(
            event.target
        )
    ) {
        showDateRangePicker.value = false
    }
}

onMounted(() => {
    updateIsMobile()

    window.addEventListener(
        'resize',
        updateIsMobile
    )

    document.addEventListener(
        'mousedown',
        handleClickOutsideDateRange
    )
})

onBeforeUnmount(() => {
    window.removeEventListener(
        'resize',
        updateIsMobile
    )

    document.removeEventListener(
        'mousedown',
        handleClickOutsideDateRange
    )
})

/*
|--------------------------------------------------------------------------
| Computed
|--------------------------------------------------------------------------
*/

const settlementList = computed(
    () => props.settlements?.data ?? []
)

const settlementPagination = computed(
    () => props.settlements?.meta ?? {}
)

const settlementDateRangeLabel = computed(() => {
    if (
        !depositDateFrom.value ||
        !depositDateTo.value
    ) {
        return 'Select Date Range'
    }

    return `${formatSettlementDate(
        depositDateFrom.value
    )} - ${formatSettlementDate(
        depositDateTo.value
    )}`
})

/*
|--------------------------------------------------------------------------
| Date Helpers
|--------------------------------------------------------------------------
*/

const parseDate = (value) => {
    if (!value) {
        return null
    }

    const [year, month, day] =
        value.split('-').map(Number)

    return new Date(
        year,
        month - 1,
        day
    )
}

const startOfDay = (date) => {
    const result = new Date(date)

    result.setHours(
        0,
        0,
        0,
        0
    )

    return result
}

const formatSettlementDate = (value) => {
    if (!value) {
        return '-'
    }

    const date = parseDate(value)

    if (!date || Number.isNaN(date.getTime())) {
        return '-'
    }

    return new Intl.DateTimeFormat(
        'en-GB',
        {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        }
    ).format(date)
}

/*
|--------------------------------------------------------------------------
| Date Range
|--------------------------------------------------------------------------
*/

const loadSettlements = (
    dateFrom = depositDateFrom.value,
    dateTo = depositDateTo.value,
    additionalFilters = {}
) => {
    settlementLoading.value = true

    router.get(
        window.location.pathname,
        {
            ...props.filters,
            ...additionalFilters,
            date_from: dateFrom || null,
            date_to: dateTo || null,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,

            onFinish: () => {
                settlementLoading.value = false
            },
        }
    )
}

const applyDateRange = (
    dateFrom,
    dateTo
) => {
    depositDateFrom.value = dateFrom
    depositDateTo.value = dateTo

    calendarRange.value = [
        dateFrom,
        dateTo,
    ]

    showDateRangePicker.value = false

    loadSettlements(
        dateFrom,
        dateTo
    )
}

const selectPreset = (preset) => {
    const today = startOfDay(new Date())

    let from = null
    let to = null

    switch (preset) {
        case 'today':
            from = today
            to = today
            break

        case 'yesterday': {
            const yesterday = new Date(today)

            yesterday.setDate(
                yesterday.getDate() - 1
            )

            from = yesterday
            to = yesterday
            break
        }

        case 'last-7-days':
            from = new Date(today)

            from.setDate(
                from.getDate() - 6
            )

            to = today
            break

        case 'last-30-days':
            from = new Date(today)

            from.setDate(
                from.getDate() - 29
            )

            to = today
            break

        case 'this-month':
            from = new Date(
                today.getFullYear(),
                today.getMonth(),
                1
            )

            to = today
            break

        case 'last-month':
            from = new Date(
                today.getFullYear(),
                today.getMonth() - 1,
                1
            )

            to = new Date(
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

    selectedPreset.value = preset

    applyDateRange(
        toDateString(from),
        toDateString(to)
    )
}

const handleCalendarChange = (
    selectedDates
) => {
    if (
        !selectedDates ||
        selectedDates.length === 0
    ) {
        return
    }

    if (selectedDates.length === 1) {
        depositDateFrom.value = toDateString(
            selectedDates[0]
        )

        depositDateTo.value = ''

        selectedPreset.value = 'custom'

        return
    }

    if (selectedDates.length >= 2) {
        let from = selectedDates[0]
        let to = selectedDates[1]

        if (from > to) {
            [from, to] = [to, from]
        }

        selectedPreset.value = 'custom'

        applyDateRange(
            toDateString(from),
            toDateString(to)
        )
    }
}

/*
|--------------------------------------------------------------------------
| Clear Date Range
|--------------------------------------------------------------------------
*/

const clearDateRange = () => {
    selectedPreset.value = 'custom'

    depositDateFrom.value = ''
    depositDateTo.value = ''
    calendarRange.value = []

    showDateRangePicker.value = false

    loadSettlements('', '')
}

/*
|--------------------------------------------------------------------------
| Formatters
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

const formatDateTime = (value) => {
    if (!value) {
        return '-'
    }

    return new Intl.DateTimeFormat(
        'id-ID',
        {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        }
    ).format(
        new Date(value)
    )
}

/*
|--------------------------------------------------------------------------
| Modal State
|--------------------------------------------------------------------------
*/

const showFormModal = ref(false)
const showViewModal = ref(false)
const selectedSettlement = ref(null)
const formMode = ref('create')

const openCreate = () => {
    formMode.value = 'create'
    selectedSettlement.value = null
    showFormModal.value = true
}

const openEdit = (settlement) => {
    formMode.value = 'edit'
    selectedSettlement.value = settlement
    showFormModal.value = true
}

const closeForm = () => {
    showFormModal.value = false
    selectedSettlement.value = null
}

const handleFormSuccess = () => {
    closeForm()

    router.reload({
        preserveScroll: true,
    })
}


/*
|--------------------------------------------------------------------------
| View Actions
|--------------------------------------------------------------------------
*/

const openView = (settlement) => {
    selectedSettlement.value = settlement
    showViewModal.value = true
}

const closeView = () => {
    showViewModal.value = false
    selectedSettlement.value = null
}

/*
|--------------------------------------------------------------------------
| Workflow Actions
|--------------------------------------------------------------------------
*/

const runSettlementAction = (
    settlement,
    action,
    payload = {},
    successMessage,
    errorMessage
) => {
    if (processingId.value) {
        return
    }

    processingId.value = settlement.id

    router.post(
        route(
            `accounting.inter-branch-settlements.${action}`,
            settlement.id
        ),
        payload,
        {
            preserveScroll: true,

            onSuccess: () => {
                success(
                    'Success',
                    successMessage
                )
            },

            onError: () => {
                error(
                    'Failed',
                    errorMessage
                )
            },

            onFinish: () => {
                processingId.value = null
            },
        }
    )
}

const submitSettlement = (settlement) => {
    runSettlementAction(
        settlement,
        'submit',
        {},
        'Settlement submitted successfully.',
        'Failed to submit settlement.'
    )
}

const approveSettlement = (settlement) => {
    runSettlementAction(
        settlement,
        'approve',
        {},
        'Settlement approved successfully.',
        'Failed to approve settlement.'
    )
}

const rejectSettlement = async (settlement) => {
    if (processingId.value) {
        return
    }

    const result = await Swal.fire({
        title: 'Reject Settlement',
        input: 'textarea',
        inputLabel: 'Rejection Reason',
        inputPlaceholder: 'Enter rejection reason...',
        inputAttributes: {
            'aria-label': 'Rejection reason',
        },
        showCancelButton: true,
        confirmButtonText: 'Reject',
        cancelButtonText: 'Back',
        inputValidator: (value) => {
            if (!value?.trim()) {
                return 'Rejection reason is required.'
            }

            return undefined
        },
    })

    if (!result.isConfirmed) {
        return
    }

    runSettlementAction(
        settlement,
        'reject',
        {
            reason: result.value.trim(),
        },
        'Settlement rejected successfully.',
        'Failed to reject settlement.'
    )
}

const postSettlement = async (settlement) => {
    if (processingId.value) {
        return
    }

    const result = await Swal.fire({
        icon: 'question',
        title: 'Post Settlement?',
        text: 'Posting creates the payment journal for the payer branch.',
        showCancelButton: true,
        confirmButtonText: 'Post Settlement',
        cancelButtonText: 'Back',
        reverseButtons: true,
    })

    if (!result.isConfirmed) {
        return
    }

    processingId.value = settlement.id

    router.post(
        route(
            'accounting.inter-branch-settlements.post',
            settlement.id
        ),
        {},
        {
            preserveScroll: true,

            onSuccess: async () => {
                const printResult = await Swal.fire({
                    icon: 'success',
                    title: 'Settlement Posted',
                    text: 'Payment journal created successfully.',
                    showCancelButton: false,
                    confirmButtonText: 'OK',
                })

                if (printResult.isConfirmed) {
                    closeView()
                }
            },

            onError: () => {
                error(
                    'Failed',
                    'Failed to post settlement.'
                )
            },

            onFinish: () => {
                processingId.value = null
            },
        }
    )
}

const receiveSettlement = (settlement) => {
    openView(settlement)
}

const cancelSettlement = async (settlement) => {
    if (processingId.value) {
        return
    }

    const result = await Swal.fire({
        title: 'Cancel Settlement',
        input: 'textarea',
        inputLabel: 'Cancellation Reason',
        inputPlaceholder: 'Enter cancellation reason...',
        inputAttributes: {
            'aria-label': 'Cancellation reason',
        },
        showCancelButton: true,
        confirmButtonText: 'Cancel Settlement',
        cancelButtonText: 'Back',
        inputValidator: (value) => {
            if (!value?.trim()) {
                return 'Cancellation reason is required.'
            }

            return undefined
        },
    })

    if (!result.isConfirmed) {
        return
    }

    runSettlementAction(
        settlement,
        'cancel',
        {
            reason: result.value.trim(),
        },
        'Settlement cancelled successfully.',
        'Failed to cancel settlement.'
    )
}

const deleteSettlement = async (settlement) => {
    if (processingId.value) {
        return
    }

    const result = await Swal.fire({
        icon: 'warning',
        title: 'Delete Settlement?',
        text: `Delete ${settlement.settlement_number}?`,
        showCancelButton: true,
        confirmButtonText: 'Delete',
        cancelButtonText: 'Back',
        reverseButtons: true,
    })

    if (!result.isConfirmed) {
        return
    }

    processingId.value = settlement.id

    router.delete(
        route(
            'accounting.inter-branch-settlements.destroy',
            settlement.id
        ),
        {
            preserveScroll: true,

            onSuccess: () => {
                success(
                    'Success',
                    'Settlement deleted successfully.'
                )
            },

            onError: () => {
                error(
                    'Failed',
                    'Failed to delete settlement.'
                )
            },

            onFinish: () => {
                processingId.value = null
            },
        }
    )
}

/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/

const visitPage = (url) => {
    if (!url || settlementLoading.value) {
        return
    }

    const targetUrl = new URL(url, window.location.origin)

    targetUrl.pathname = window.location.pathname

    settlementLoading.value = true

    router.get(
        targetUrl.pathname + targetUrl.search,
        {
            tab: 'payment',
        },
        {
            preserveState: true,
            preserveScroll: true,

            onFinish: () => {
                settlementLoading.value = false
            },
        }
    )
}
</script>
<template>

    <section>
        <!-- ========================================================= -->
        <!-- HEADER -->
        <!-- ========================================================= -->

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
                <h2
                    class="
                        text-sm
                        font-semibold
                        uppercase
                        tracking-wide
                        text-slate-700
                    "
                >
                    Inter-Branch Settlements
                </h2>

                <p class="mt-0.5 text-xs text-slate-500">
                    Manage payments and receipts between branches.
                </p>
            </div>

            <!-- DATE RANGE & CREATE SETTLEMENT -->
            <div
                class="
                    flex
                    flex-col
                    gap-2
                    sm:flex-row
                    sm:items-center
                "
            >
                <!-- DATE RANGE -->
                <div
                    ref="dateRangePickerRef"
                    class="relative shrink-0"
                >
                    <button
                        type="button"
                        class="
                            flex
                            min-w-0
                            w-full
                            items-center
                            justify-between
                            gap-3
                            rounded-xl
                            border
                            border-slate-200
                            bg-white
                            px-3
                            py-2
                            text-left
                            shadow-sm
                            transition
                            hover:border-slate-300
                            hover:bg-slate-50
                            sm:min-w-[270px]
                        "
                        @click="
                            showDateRangePicker =
                                !showDateRangePicker
                        "
                    >
                        <div class="flex min-w-0 items-center gap-2">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 shrink-0 text-slate-400"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <rect
                                    width="18"
                                    height="18"
                                    x="3"
                                    y="4"
                                    rx="2"
                                />

                                <path
                                    stroke-linecap="round"
                                    d="M16 2v4M8 2v4M3 10h18"
                                />
                            </svg>

                            <span
                                class="
                                    truncate
                                    text-xs
                                    font-medium
                                    text-slate-600
                                "
                            >
                                {{ settlementDateRangeLabel }}
                            </span>
                        </div>

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="
                                h-4
                                w-4
                                shrink-0
                                text-slate-400
                                transition
                            "
                            :class="{
                                'rotate-180': showDateRangePicker,
                            }"
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

                    <!-- DATE PICKER POPUP -->
                    <div
                        v-if="showDateRangePicker"
                        class="
                            absolute
                            right-0
                            z-50
                            mt-2
                            max-w-[calc(100vw-2rem)]
                            overflow-hidden
                            rounded-2xl
                            border
                            border-slate-200
                            bg-white
                            shadow-xl
                        "
                    >
                        <div class="flex flex-col sm:flex-row">
                            <!-- PRESETS -->
                            <div
                                class="
                                    w-full
                                    border-b
                                    border-slate-100
                                    p-2
                                    sm:w-[170px]
                                    sm:border-b-0
                                    sm:border-r
                                "
                            >
                                <button
                                    v-for="preset in [
                                        {
                                            value: 'today',
                                            label: 'Today',
                                        },
                                        {
                                            value: 'yesterday',
                                            label: 'Yesterday',
                                        },
                                        {
                                            value: 'last-7-days',
                                            label: 'Last 7 Days',
                                        },
                                        {
                                            value: 'last-30-days',
                                            label: 'Last 30 Days',
                                        },
                                        {
                                            value: 'this-month',
                                            label: 'This Month',
                                        },
                                        {
                                            value: 'last-month',
                                            label: 'Last Month',
                                        },
                                        {
                                            value: 'custom',
                                            label: 'Custom',
                                        },
                                    ]"
                                    :key="preset.value"
                                    type="button"
                                    class="
                                        w-full
                                        rounded-lg
                                        px-3
                                        py-2
                                        text-left
                                        text-xs
                                        font-medium
                                        transition
                                    "
                                    :class="
                                        selectedPreset === preset.value
                                            ? 'bg-blue-50 text-blue-700'
                                            : 'text-slate-600 hover:bg-slate-50'
                                    "
                                    @click="
                                        selectPreset(preset.value)
                                    "
                                >
                                    {{ preset.label }}
                                </button>

                                <button
                                    v-if="
                                        depositDateFrom ||
                                        depositDateTo
                                    "
                                    type="button"
                                    class="
                                        mt-1
                                        w-full
                                        rounded-lg
                                        px-3
                                        py-2
                                        text-left
                                        text-xs
                                        font-medium
                                        text-slate-400
                                        transition
                                        hover:bg-slate-50
                                        hover:text-slate-600
                                    "
                                    @click="clearDateRange"
                                >
                                    Clear
                                </button>
                            </div>

                            <!-- CALENDAR -->
                            <div class="overflow-auto p-3">
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

                        <div
                            v-if="settlementLoading"
                            class="
                                border-t
                                border-slate-100
                                px-4
                                py-2
                                text-center
                                text-xs
                                text-slate-400
                            "
                        >
                            Loading settlements...
                        </div>
                    </div>
                </div>

                <!-- CREATE SETTLEMENT -->
                <button
                    type="button"
                    @click="openCreate"
                    class="
                        inline-flex
                        shrink-0
                        items-center
                        justify-center
                        gap-2
                        rounded-xl
                        bg-blue-600
                        px-4
                        py-2.5
                        text-sm
                        font-semibold
                        text-white
                        shadow-sm
                        transition
                        hover:bg-blue-700
                        focus:outline-none
                        focus:ring-2
                        focus:ring-blue-500/20
                    "
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
                            stroke-linejoin="round"
                            d="M12 5v14M5 12h14"
                        />
                    </svg>

                    Create Settlement
                </button>
            </div>
        </div>

        <!-- ========================================================= -->
        <!-- FILTERS -->
        <!-- ========================================================= -->

        <section class="mt-6">
            <div class="mb-4">
                <h2
                    class="
                        text-sm
                        font-semibold
                        uppercase
                        tracking-wide
                        text-slate-700
                    "
                >
                    Settlement Records
                </h2>

                <p class="mt-0.5 text-xs text-slate-500">
                    Search and filter inter-branch settlement transactions.
                </p>
            </div>

            <div
                class="
                    rounded-2xl
                    border
                    border-slate-200
                    bg-white
                    p-5
                    shadow-sm
                "
            >
                <div class="space-y-4">
                    <!-- SEARCH -->
                    <div>
                        <label
                            for="settlement-search"
                            class="
                                mb-1.5
                                block
                                text-xs
                                font-medium
                                text-slate-600
                            "
                        >
                            Search
                        </label>

                        <div class="relative">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="
                                    absolute
                                    left-3
                                    top-1/2
                                    h-4
                                    w-4
                                    -translate-y-1/2
                                    text-slate-400
                                "
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <circle
                                    cx="11"
                                    cy="11"
                                    r="7"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m20 20-4-4"
                                />
                            </svg>

                            <input
                                id="settlement-search"
                                v-model="search"
                                type="text"
                                placeholder="Search settlement number..."
                                class="
                                    w-full
                                    rounded-xl
                                    border
                                    border-slate-200
                                    bg-white
                                    py-3
                                    pl-9
                                    pr-3
                                    text-sm
                                    text-slate-700
                                    transition
                                    placeholder:text-slate-400
                                    focus:border-blue-500
                                    focus:outline-none
                                    focus:ring-2
                                    focus:ring-blue-500/10
                                "
                                @keyup.enter="applyFilters"
                            />
                        </div>
                    </div>

                    <!-- BRANCH, STATUS, REFRESH, APPLY -->
                    <div
                        class="
                            grid
                            grid-cols-1
                            gap-4
                            sm:grid-cols-12
                            sm:items-end
                        "
                    >
                        <!-- BRANCH -->
                        <div class="sm:col-span-4">
                            <label
                                for="settlement-branch"
                                class="
                                    mb-1.5
                                    block
                                    text-xs
                                    font-medium
                                    text-slate-600
                                "
                            >
                                Branch
                            </label>

                            <select
                            v-model="branchId"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                        >
                            <option value="">All Branch</option>

                            <option
                                v-for="branch in branches"
                                :key="branch.id"
                                :value="String(branch.id)"
                            >
                                {{ branch.name }}
                            </option>
                        </select>
                        </div>

                        <!-- STATUS -->
                        <div class="sm:col-span-4">
                            <label
                                for="settlement-status"
                                class="
                                    mb-1.5
                                    block
                                    text-xs
                                    font-medium
                                    text-slate-600
                                "
                            >
                                Status
                            </label>

                            <select
                                id="settlement-status"
                                v-model="status"
                                class="
                                    w-full
                                    rounded-xl
                                    border
                                    border-slate-200
                                    bg-white
                                    px-3
                                    py-3
                                    text-sm
                                    text-slate-700
                                    transition
                                    focus:border-blue-500
                                    focus:outline-none
                                    focus:ring-2
                                    focus:ring-blue-500/10
                                "
                            >
                                <option value="">
                                    All Statuses
                                </option>

                                <option value="Draft">
                                    Draft
                                </option>

                                <option value="Submitted">
                                    Submitted
                                </option>

                                <option value="Approved">
                                    Approved
                                </option>

                                <option value="Rejected">
                                    Rejected
                                </option>

                                <option value="Posted">
                                    Posted
                                </option>

                                <option value="Cancelled">
                                    Cancelled
                                </option>
                            </select>
                        </div>

                        <!-- REFRESH -->
                        <div class="sm:col-span-2">
                            <button
                                type="button"
                                :disabled="settlementLoading"
                                @click="refresh"
                                class="
                                    inline-flex
                                    w-full
                                    items-center
                                    justify-center
                                    gap-2
                                    rounded-xl
                                    border
                                    border-slate-200
                                    bg-white
                                    px-3
                                    py-3
                                    text-sm
                                    font-medium
                                    text-slate-600
                                    transition
                                    hover:bg-slate-50
                                    hover:text-slate-900
                                    disabled:cursor-not-allowed
                                    disabled:opacity-50
                                "
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4 shrink-0"
                                    :class="{
                                        'animate-spin': settlementLoading,
                                    }"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M20 7v5h-5M4 17v-5h5m11-3a8 8 0 0 0-14-3L4 12m16 0v3a8 8 0 0 1-14 3"
                                    />
                                </svg>

                                Refresh
                            </button>
                        </div>

                        <!-- APPLY FILTERS -->
                        <div class="sm:col-span-2">
                            <button
                                type="button"
                                :disabled="settlementLoading"
                                @click="applyFilters"
                                class="
                                    inline-flex
                                    w-full
                                    items-center
                                    justify-center
                                    rounded-xl
                                    bg-blue-600
                                    px-3
                                    py-3
                                    text-sm
                                    font-semibold
                                    text-white
                                    transition
                                    hover:bg-blue-700
                                    disabled:cursor-not-allowed
                                    disabled:opacity-50
                                "
                            >
                                Apply Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========================================================= -->
        <!-- STATISTICS -->
        <!-- ========================================================= -->

        <section
            v-if="Object.keys(statistics).length"
            class="
                mt-6
                mb-4
                grid
                grid-cols-2
                gap-3
                lg:grid-cols-4
            "
        >
            <div
                v-for="item in [
                    {
                        label: 'Total Settlements',
                        value: statistics.total ?? 0,
                    },
                    {
                        label: 'Pending Approval',
                        value: statistics.pending_approval ?? 0,
                    },
                    {
                        label: 'Pending Receipt',
                        value: statistics.pending_receipt ?? 0,
                    },
                    {
                        label: 'Received',
                        value: statistics.received ?? 0,
                    },
                ]"
                :key="item.label"
                class="
                    rounded-2xl
                    border
                    border-slate-200
                    bg-white
                    px-4
                    py-4
                    shadow-sm
                "
            >
                <p
                    class="
                        text-[11px]
                        font-semibold
                        uppercase
                        tracking-wide
                        text-slate-400
                    "
                >
                    {{ item.label }}
                </p>

                <p
                    class="
                        mt-2
                        text-xl
                        font-semibold
                        tabular-nums
                        text-slate-800
                    "
                >
                    {{ item.value }}
                </p>
            </div>
        </section>

        <!-- ========================================================= -->
        <!-- SETTLEMENT TABLE -->
        <!-- ========================================================= -->

        <div
            class="
                mt-6
                overflow-hidden
                rounded-2xl
                border
                border-slate-200
                bg-white
                shadow-sm
            "
        >
            <!-- TABLE HEADER -->
            <div
                class="
                    flex
                    flex-col
                    gap-3
                    border-b
                    border-slate-100
                    px-5
                    py-4
                    sm:flex-row
                    sm:items-center
                    sm:justify-between
                    sm:px-6
                "
            >
                <div>
                    <h2 class="text-base font-semibold text-slate-900">
                        Settlements
                    </h2>

                    <p class="mt-1 text-xs text-slate-500">
                        {{ settlements.total ?? settlementList.length }}
                        settlement records
                    </p>
                </div>
            </div>

            <!-- EMPTY STATE -->
            <div
                v-if="settlementList.length === 0"
                class="
                    flex
                    min-h-[240px]
                    items-center
                    justify-center
                    px-6
                    py-10
                    text-center
                "
            >
                <div>
                    <div
                        class="
                            mx-auto
                            flex
                            h-11
                            w-11
                            items-center
                            justify-center
                            rounded-xl
                            bg-slate-100
                            text-slate-400
                        "
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M6 3h9l3 3v15H6z"
                            />

                            <path
                                stroke-linecap="round"
                                d="M15 3v4h4M9 12h6M9 16h4"
                            />
                        </svg>
                    </div>

                    <p
                        class="
                            mt-3
                            text-sm
                            font-semibold
                            text-slate-700
                        "
                    >
                        No settlements found
                    </p>

                    <p class="mt-1 text-xs text-slate-500">
                        Settlements for the selected period will appear here.
                    </p>
                </div>
            </div>

            <!-- ===================================================== -->
            <!-- DESKTOP TABLE -->
            <!-- ===================================================== -->

            <div
                v-else
                class="hidden overflow-x-auto md:block"
            >
                <table
                    class="
                        min-w-full
                        divide-y
                        divide-slate-100
                    "
                >
                    <thead class="bg-slate-50">
                        <tr>
                            <th
                                class="
                                    px-5
                                    py-3
                                    text-left
                                    text-[11px]
                                    font-semibold
                                    uppercase
                                    tracking-wide
                                    text-slate-400
                                "
                            >
                                Settlement
                            </th>

                            <th
                                class="
                                    px-5
                                    py-3
                                    text-left
                                    text-[11px]
                                    font-semibold
                                    uppercase
                                    tracking-wide
                                    text-slate-400
                                "
                            >
                                Payer Branch
                            </th>

                            <th
                                class="
                                    px-5
                                    py-3
                                    text-left
                                    text-[11px]
                                    font-semibold
                                    uppercase
                                    tracking-wide
                                    text-slate-400
                                "
                            >
                                Recipient Branch
                            </th>

                            <th
                                class="
                                    px-5
                                    py-3
                                    text-right
                                    text-[11px]
                                    font-semibold
                                    uppercase
                                    tracking-wide
                                    text-slate-400
                                "
                            >
                                Amount
                            </th>

                            <th
                                class="
                                    px-5
                                    py-3
                                    text-center
                                    text-[11px]
                                    font-semibold
                                    uppercase
                                    tracking-wide
                                    text-slate-400
                                "
                            >
                                Status
                            </th>

                            <th
                                class="
                                    px-5
                                    py-3
                                    text-center
                                    text-[11px]
                                    font-semibold
                                    uppercase
                                    tracking-wide
                                    text-slate-400
                                "
                            >
                                Receipt
                            </th>

                            <th
                                class="
                                    px-5
                                    py-3
                                    text-right
                                    text-[11px]
                                    font-semibold
                                    uppercase
                                    tracking-wide
                                    text-slate-400
                                "
                            >
                                Date
                            </th>

                            <th
                                class="
                                    px-5
                                    py-3
                                    text-right
                                    text-[11px]
                                    font-semibold
                                    uppercase
                                    tracking-wide
                                    text-slate-400
                                "
                            >
                                Action
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        <tr
                            v-for="settlement in settlementList"
                            :key="settlement.id"
                            class="transition hover:bg-slate-50"
                        >
                            <!-- SETTLEMENT -->
                            <td class="px-5 py-4">
                                <button
                                    type="button"
                                    class="
                                        text-left
                                        text-sm
                                        font-semibold
                                        text-blue-600
                                        transition
                                        hover:text-blue-700
                                        hover:underline
                                    "
                                    @click="openView(settlement)"
                                >
                                    {{ settlement.settlement_number }}
                                </button>

                                <div
                                    class="
                                        mt-0.5
                                        text-xs
                                        text-slate-400
                                    "
                                >
                                    {{ settlement.details?.length ?? 0 }}
                                    linked transfer(s)
                                </div>
                            </td>

                            <!-- PAYER -->
                            <td class="px-5 py-4">
                                <div
                                    class="
                                        text-sm
                                        font-medium
                                        text-slate-700
                                    "
                                >
                                    {{ settlement.from_branch?.name ?? '-' }}
                                </div>
                            </td>

                            <!-- RECIPIENT -->
                            <td class="px-5 py-4">
                                <div
                                    class="
                                        text-sm
                                        font-medium
                                        text-slate-700
                                    "
                                >
                                    {{ settlement.to_branch?.name ?? '-' }}
                                </div>
                            </td>

                            <!-- AMOUNT -->
                            <td class="px-5 py-4 text-right">
                                <span
                                    class="
                                        text-sm
                                        font-semibold
                                        tabular-nums
                                        text-slate-800
                                    "
                                >
                                    {{ formatCurrency(settlement.amount) }}
                                </span>
                            </td>

                            <!-- STATUS -->
                            <td class="px-5 py-4 text-center">
                                <StatusBadge :status="settlement.status" />
                            </td>

                            <!-- RECEIPT -->
                            <td class="px-5 py-4 text-center">
                                <StatusBadge
                                    :status="
                                        settlement.receipt_status ?? 'Pending'
                                    "
                                />
                            </td>

                            <!-- DATE -->
                            <td
                                class="
                                    px-5
                                    py-4
                                    text-right
                                    text-xs
                                    text-slate-500
                                "
                            >
                                {{
                                    formatDateTime(
                                        settlement.settlement_date
                                    )
                                }}
                            </td>

                            <!-- ACTIONS -->
                            <td class="px-5 py-4 text-right">
                                <div
                                    class="
                                        flex
                                        items-center
                                        justify-end
                                    "
                                >
                                   <ActionDropdown
                                        :show-edit="false"
                                        :show-duplicate="false"
                                        :show-export="false"
                                        :show-delete="false"
                                        :show-history="false"
                                        :show-submit="false"
                                        :show-approve="false"
                                        :show-reject="false"
                                        :show-receive="false"
                                        :show-post="false"
                                        :show-cancel="false"
                                        :show-print="['Posted', 'Received'].includes(settlement.status)
                                            || settlement.receipt_status === 'Received'"
                                        @view="openView(settlement)"
                                        @print="printSettlement(settlement)"
                                    />
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- ===================================================== -->
            <!-- MOBILE CARDS -->
            <!-- ===================================================== -->

            <div
                v-if="settlementList.length > 0"
                class="
                    divide-y
                    divide-slate-100
                    md:hidden
                "
            >
                <div
                    v-for="settlement in settlementList"
                    :key="settlement.id"
                    class="px-5 py-4"
                >
                    <!-- NUMBER, AMOUNT, STATUS -->
                    <div
                        class="
                            flex
                            items-start
                            justify-between
                            gap-4
                        "
                    >
                        <div class="min-w-0">
                            <button
                                type="button"
                                class="
                                    truncate
                                    text-left
                                    text-sm
                                    font-semibold
                                    text-blue-600
                                    hover:text-blue-700
                                    hover:underline
                                "
                                @click="openView(settlement)"
                            >
                                {{ settlement.settlement_number }}
                            </button>

                            <div
                                class="
                                    mt-0.5
                                    text-xs
                                    text-slate-400
                                "
                            >
                                {{
                                    formatDateTime(
                                        settlement.settlement_date
                                    )
                                }}
                            </div>
                        </div>

                        <div class="shrink-0 text-right">
                            <div
                                class="
                                    text-sm
                                    font-semibold
                                    tabular-nums
                                    text-slate-800
                                "
                            >
                                {{ formatCurrency(settlement.amount) }}
                            </div>

                            <div class="mt-1">
                                <StatusBadge :status="settlement.status" />
                            </div>
                        </div>
                    </div>

                    <!-- BRANCH DETAILS -->
                    <div class="mt-3 space-y-2">
                        <div>
                            <div
                                class="
                                    text-[11px]
                                    font-medium
                                    uppercase
                                    tracking-wide
                                    text-slate-400
                                "
                            >
                                Payer
                            </div>

                            <div
                                class="
                                    mt-0.5
                                    text-xs
                                    font-medium
                                    text-slate-700
                                "
                            >
                                {{ settlement.from_branch?.name ?? '-' }}
                            </div>
                        </div>

                        <div>
                            <div
                                class="
                                    text-[11px]
                                    font-medium
                                    uppercase
                                    tracking-wide
                                    text-slate-400
                                "
                            >
                                Recipient
                            </div>

                            <div
                                class="
                                    mt-0.5
                                    text-xs
                                    font-medium
                                    text-slate-700
                                "
                            >
                                {{ settlement.to_branch?.name ?? '-' }}
                            </div>
                        </div>

                        <!-- RECEIPT & ACTIONS -->
                        <div
                            class="
                                flex
                                items-center
                                justify-between
                                gap-3
                                border-t
                                border-slate-100
                                pt-3
                            "
                        >
                            <div>
                                <div
                                    class="
                                        text-[11px]
                                        font-medium
                                        uppercase
                                        tracking-wide
                                        text-slate-400
                                    "
                                >
                                    Receipt
                                </div>

                                <div class="mt-1">
                                    <StatusBadge
                                        :status="
                                            settlement.receipt_status ?? 'Pending'
                                        "
                                    />
                                </div>
                            </div>

                            <ActionDropdown
                            :show-edit="false"
                            :show-duplicate="false"
                            :show-export="false"
                            :show-delete="false"
                            :show-history="false"
                            :show-submit="false"
                            :show-approve="false"
                            :show-reject="false"
                            :show-receive="false"
                            :show-post="false"
                            :show-cancel="false"
                            :show-print="['Posted', 'Received'].includes(settlement.status)
                                || settlement.receipt_status === 'Received'"
                            @view="openView(settlement)"
                            @print="printSettlement(settlement)"
                        />
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===================================================== -->
            <!-- PAGINATION -->
            <!-- ===================================================== -->

            <div
                v-if="settlements.links?.length"
                class="
                    flex
                    flex-col
                    gap-3
                    border-t
                    border-slate-100
                    px-5
                    py-4
                    sm:flex-row
                    sm:items-center
                    sm:justify-between
                "
            >
                <p class="text-xs text-slate-500">
                    Showing
                    <span class="font-medium text-slate-700">
                        {{ settlements.from ?? 0 }}
                    </span>
                    to
                    <span class="font-medium text-slate-700">
                        {{ settlements.to ?? 0 }}
                    </span>
                    of
                    <span class="font-medium text-slate-700">
                        {{ settlements.total ?? 0 }}
                    </span>
                    settlements
                </p>

                <div class="flex flex-wrap items-center gap-1">
                    <button
                        v-for="(link, index) in settlements.links"
                        :key="index"
                        type="button"
                        class="
                            rounded-lg
                            border
                            px-3
                            py-1.5
                            text-xs
                            transition
                        "
                        :class="[
                            link.active
                                ? 'border-blue-600 bg-blue-600 text-white'
                                : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50',
                            !link.url
                                ? 'cursor-not-allowed opacity-40'
                                : '',
                        ]"
                        :disabled="!link.url || settlementLoading"
                        @click="visitPage(link.url)"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </section>
  <SettlementForm
    :show="showFormModal"
    :settlement="selectedSettlement"
    :preview-number="previewNumber"
    :branches="branches"
    @close="closeForm"
    @success="handleFormSuccess"
/>

<SettlementView
    :show="showViewModal"
    :settlement="selectedSettlement"
    :processing="processingId !== null"
    @close="closeView"
    @edit="handleEditRequest"
    @submit="submitSettlement"
    @approve="approveSettlement"
    @reject="rejectSettlement"
    @post="postSettlement"
    @cancel="cancelSettlement"
    @receive="handleReceiveRequest"
/>
</template>