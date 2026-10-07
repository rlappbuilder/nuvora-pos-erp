<script setup>
import {
    computed,
    onBeforeUnmount,
    onMounted,
    ref,
} from 'vue'

import { router } from '@inertiajs/vue3'
import ViewDepositModal
    from './ViewDepositModal.vue'
import FlatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'

import StatusBadge from '@/Components/Display/StatusBadge.vue'
import ActionDropdown from '@/Components/Action/ActionDropdown.vue'
import {
    success,
    error,
} from '@/Utils'
import Swal from 'sweetalert2'

const props = defineProps({
    deposits: {
        type: Array,
        default: () => [],
    },

    activeSession: {
        type: Object,
        default: null,
    },
})

/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const processingId = ref(null)

const showDateRangePicker = ref(false)
const dateRangePickerRef = ref(null)
const isMobile = ref(false)

const selectedPreset = ref('custom')

const depositDateFrom = ref('')
const depositDateTo = ref('')

const calendarRange = ref([])
const depositLoading = ref(false)

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

const deposits = computed(
    () => props.deposits ?? []
)

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

const toDateString = (date) => {
    if (!date) {
        return ''
    }

    const year =
        date.getFullYear()

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

    result.setHours(
        0,
        0,
        0,
        0
    )

    return result
}

const formatDepositDate = (value) => {
    if (!value) {
        return '-'
    }

    const date = parseDate(value)

    if (!date) {
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

const depositDateRangeLabel = computed(() => {
    if (
        !depositDateFrom.value ||
        !depositDateTo.value
    ) {
        return 'Select Date Range'
    }

    return `${formatDepositDate(
        depositDateFrom.value
    )} - ${formatDepositDate(
        depositDateTo.value
    )}`
})

/*
|--------------------------------------------------------------------------
| Date Range
|--------------------------------------------------------------------------
*/

const loadDeposits = (
    dateFrom,
    dateTo
) => {
    depositLoading.value = true

    router.get(
        window.location.pathname,
        {
            deposit_date_from:
                dateFrom || null,

            deposit_date_to:
                dateTo || null,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,

            only: [
                'cashierDeposits',
            ],

            onFinish: () => {
                depositLoading.value = false
            },
        }
    )
}

const applyDateRange = (
    dateFrom,
    dateTo
) => {
    depositDateFrom.value =
        dateFrom

    depositDateTo.value =
        dateTo

    calendarRange.value = [
        dateFrom,
        dateTo,
    ]

    showDateRangePicker.value = false

    loadDeposits(
        dateFrom,
        dateTo
    )
}

const selectPreset = (preset) => {
    const today =
        startOfDay(new Date())

    let from = null
    let to = null

    switch (preset) {
        case 'today':
            from = today
            to = today
            break

        case 'yesterday': {
            const yesterday =
                new Date(today)

            yesterday.setDate(
                yesterday.getDate() - 1
            )

            from = yesterday
            to = yesterday

            break
        }

        case 'last-7-days': {
            from = new Date(today)

            from.setDate(
                from.getDate() - 6
            )

            to = today

            break
        }

        case 'last-30-days': {
            from = new Date(today)

            from.setDate(
                from.getDate() - 29
            )

            to = today

            break
        }

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
            selectedPreset.value =
                'custom'

            return
    }

    selectedPreset.value =
        preset

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
        depositDateFrom.value =
            toDateString(
                selectedDates[0]
            )

        depositDateTo.value = ''

        selectedPreset.value =
            'custom'

        return
    }

    if (selectedDates.length >= 2) {
        const firstDate =
            selectedDates[0]

        const secondDate =
            selectedDates[1]

        let from = firstDate
        let to = secondDate

        if (from > to) {
            [from, to] = [
                to,
                from,
            ]
        }

        selectedPreset.value =
            'custom'

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
    selectedPreset.value =
        'custom'

    depositDateFrom.value = ''
    depositDateTo.value = ''
    calendarRange.value = []

    showDateRangePicker.value = false

    loadDeposits(
        '',
        ''
    )
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
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
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
| Actions
|--------------------------------------------------------------------------
*/

const showViewDepositModal = ref(false)
const selectedDeposit = ref(null)

const submitDeposit = (deposit) => {
    if (processingId.value) {
        return
    }

    processingId.value =
        deposit.id

    router.post(
        route(
            'pos.cashier-deposits.submit',
            deposit.id
        ),
        {},
        {
            preserveScroll: true,

            onSuccess: () => {
                success(
                    'Success',
                    'Cash deposit resubmitted successfully.'
                )
            },

            onError: () => {
                error(
                    'Failed',
                    'Failed to resubmit cash deposit.'
                )
            },

            onFinish: () => {
                processingId.value = null
            },
        }
    )
}


const approveDeposit = (deposit) => {
    if (processingId.value) {
        return
    }

    processingId.value =
        deposit.id

    router.post(
        route(
            'pos.cashier-deposits.approve',
            deposit.id
        ),
        {},
        {
            preserveScroll: true,

            onSuccess: () => {
                success(
                    'Success',
                    'Cash deposit approved successfully.'
                )
            },

            onError: () => {
                error(
                    'Failed',
                    'Failed to approve cash deposit.'
                )
            },

            onFinish: () => {
                processingId.value = null
            },
        }
    )
}


const rejectDeposit = (deposit) => {
    if (processingId.value) {
        return
    }

    const reason = window.prompt(
        'Rejection Reason'
    )

    if (reason === null) {
        return
    }

    const trimmedReason =
        reason.trim()

    if (!trimmedReason) {
        return
    }

    processingId.value =
        deposit.id

    router.post(
        route(
            'pos.cashier-deposits.reject',
            deposit.id
        ),
        {
            reason: trimmedReason,
        },
        {
            preserveScroll: true,

            onSuccess: () => {
                success(
                    'Success',
                    'Cash deposit rejected successfully.'
                )
            },

            onError: () => {
                error(
                    'Failed',
                    'Failed to reject cash deposit.'
                )
            },

            onFinish: () => {
                processingId.value = null
            },
        }
    )
}


const receiveDeposit = (deposit) => {
    if (processingId.value) {
        return
    }

    processingId.value =
        deposit.id

    router.post(
        route(
            'pos.cashier-deposits.receive',
            deposit.id
        ),
        {},
        {
            preserveScroll: true,

            onSuccess: () => {
                success(
                    'Success',
                    'Cash deposit received successfully.'
                )
            },

            onError: () => {
                error(
                    'Failed',
                    'Failed to receive cash deposit.'
                )
            },

            onFinish: () => {
                processingId.value = null
            },
        }
    )
}


const postDeposit = (deposit) => {
    if (processingId.value) {
        return
    }

    processingId.value =
        deposit.id

    router.post(
        route(
            'pos.cashier-deposits.post',
            deposit.id
        ),
        {},
        {
            preserveScroll: true,

            onSuccess: async () => {
                showViewDepositModal.value = false
                selectedDeposit.value = null

                // Swal Success
                const result = await Swal.fire({
                    icon: 'success',
                    title: 'Save Success',
                    text: 'Cash deposit posted successfully.',
                    showCancelButton: true,
                    confirmButtonText: 'Print Preview',
                    cancelButtonText: 'Back',
                    reverseButtons: true,
                })

                if (result.isConfirmed) {
                    printDeposit(deposit)
                }
            },

            onError: () => {
                error(
                    'Failed',
                    'Failed to post cash deposit.'
                )
            },

            onFinish: () => {
                processingId.value = null
            },
        }
    )
}


const cancelDeposit = (deposit) => {
    if (processingId.value) {
        return
    }

    const reason = window.prompt(
        'Cancellation Reason'
    )

    if (reason === null) {
        return
    }

    const trimmedReason =
        reason.trim()

    if (!trimmedReason) {
        return
    }

    processingId.value =
        deposit.id

    router.post(
        route(
            'pos.cashier-deposits.cancel',
            deposit.id
        ),
        {
            reason: trimmedReason,
        },
        {
            preserveScroll: true,

            onSuccess: () => {
                success(
                    'Success',
                    'Cash deposit cancelled successfully.'
                )
            },

            onError: () => {
                error(
                    'Failed',
                    'Failed to cancel cash deposit.'
                )
            },

            onFinish: () => {
                processingId.value = null
            },
        }
    )
}


const viewDeposit = (deposit) => {
    selectedDeposit.value = deposit

    showViewDepositModal.value = true
}


const closeViewDeposit = () => {
    showViewDepositModal.value = false

    selectedDeposit.value = null
}
const printDeposit = (deposit) => {
    if (!deposit?.id) {
        return
    }

    window.open(
        route(
            'pos.cashier-deposits.print',
            deposit.id
        ),
        '_blank'
    )
}
</script>

<template>

    <section>

        <!-- ========================================================= -->
        <!-- Header -->
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
                    Cash Deposit Receipts
                </h2>

                <p
                    class="
                        mt-0.5
                        text-xs
                        text-slate-500
                    "
                >
                    Manage cash deposits created from your cashier session.
                </p>

            </div>

            <!-- Date Range -->

            <div
                ref="dateRangePickerRef"
                class="relative shrink-0"
            >

                <button
                    type="button"
                    class="
                        flex
                        min-w-0
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

                    <div
                        class="
                            flex
                            min-w-0
                            items-center
                            gap-2
                        "
                    >

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="
                                h-4
                                w-4
                                shrink-0
                                text-slate-400
                            "
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
                            {{
                                depositDateRangeLabel
                            }}
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
                            'rotate-180':
                                showDateRangePicker,
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

                <!-- Date Picker Popup -->

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

                    <div
                        class="
                            flex
                            flex-col
                            sm:flex-row
                        "
                    >

                        <!-- Presets -->

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
                                :key="
                                    preset.value
                                "
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
                                    selectedPreset ===
                                    preset.value
                                        ? 'bg-blue-50 text-blue-700'
                                        : 'text-slate-600 hover:bg-slate-50'
                                "
                                @click="
                                    selectPreset(
                                        preset.value
                                    )
                                "
                            >
                                {{
                                    preset.label
                                }}
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
                                @click="
                                    clearDateRange
                                "
                            >
                                Clear
                            </button>

                        </div>

                        <!-- Calendar -->

                        <div
                            class="overflow-auto p-3"
                        >

                            <FlatPickr
                                v-model="
                                    calendarRange
                                "
                                :config="{
                                    mode: 'range',
                                    inline: true,
                                    showMonths: isMobile ? 1 : 2,
                                    dateFormat: 'Y-m-d',
                                    allowInput: false,
                                    clickOpens: true,
                                    onChange:
                                        handleCalendarChange,
                                }"
                            />

                        </div>

                    </div>

                    <!-- Loading -->

                    <div
                        v-if="
                            depositLoading
                        "
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
                        Loading deposits...
                    </div>

                </div>

            </div>

        </div>

        <!-- ========================================================= -->
        <!-- Content -->
        <!-- ========================================================= -->

        <div
            class="
                overflow-hidden
                rounded-2xl
                border
                border-slate-200
                bg-white
                shadow-sm
            "
        >

            <!-- Empty -->

            <div
                v-if="deposits.length === 0"
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
                        No cash deposits yet
                    </p>

                    <p
                        class="
                            mt-1
                            text-xs
                            text-slate-500
                        "
                    >
                        Cash deposits will appear here.
                    </p>

                </div>

            </div>

            <!-- ===================================================== -->
            <!-- Desktop -->
            <!-- ===================================================== -->

            <div
                v-else
                class="
                    hidden
                    overflow-x-auto
                    md:block
                "
            >

                <table
                    class="
                        min-w-full
                        divide-y
                        divide-slate-100
                    "
                >

                    <thead
                        class="bg-slate-50"
                    >

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
                                Deposit
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
                                Destination
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

                    <tbody
                        class="
                            divide-y
                            divide-slate-100
                        "
                    >

                        <tr
                            v-for="deposit in deposits"
                            :key="deposit.id"
                            class="
                                transition
                                hover:bg-slate-50
                            "
                        >

                            <td
                                class="px-5 py-4"
                            >

                                <div
                                    class="
                                        text-sm
                                        font-semibold
                                        text-slate-800
                                    "
                                >
                                    {{
                                        deposit.deposit_number
                                    }}
                                </div>

                                <div
                                    class="
                                        mt-0.5
                                        text-xs
                                        text-slate-400
                                    "
                                >
                                    {{
                                        deposit.session_number ??
                                        '-'
                                    }}
                                </div>

                            </td>

                            <td
                                class="px-5 py-4"
                            >

                                <div
                                    v-if="
                                        deposit.destination_account
                                    "
                                >

                                    <div
                                        class="
                                            text-sm
                                            font-medium
                                            text-slate-700
                                        "
                                    >
                                        {{
                                            deposit
                                                .destination_account
                                                .name
                                        }}
                                    </div>

                                    <div
                                        class="
                                            mt-0.5
                                            text-xs
                                            text-slate-400
                                        "
                                    >
                                        {{
                                            deposit
                                                .destination_account
                                                .code
                                        }}
                                    </div>

                                </div>

                                <span
                                    v-else
                                    class="
                                        text-sm
                                        text-slate-400
                                    "
                                >
                                    -
                                </span>

                            </td>

                            <td
                                class="
                                    px-5
                                    py-4
                                    text-right
                                "
                            >

                                <span
                                    class="
                                        text-sm
                                        font-semibold
                                        tabular-nums
                                        text-slate-800
                                    "
                                >
                                    {{
                                        formatCurrency(
                                            deposit.amount
                                        )
                                    }}
                                </span>

                            </td>

                            <td
                                class="
                                    px-5
                                    py-4
                                    text-center
                                "
                            >

                                <StatusBadge
                                    :status="
                                        deposit.status
                                    "
                                />

                            </td>

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
                                        deposit.created_at
                                    )
                                }}

                            </td>

                            <td
                                class="
                                    px-5
                                    py-4
                                    text-right
                                "
                            >

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

                                        :show-print="true"

                                        @view="
                                            viewDeposit(
                                                deposit
                                            )
                                        "

                                        @print="
                                            printDeposit(
                                                deposit
                                            )
                                        "
                                    />

                                </div>

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

            <!-- ===================================================== -->
            <!-- Mobile -->
            <!-- ===================================================== -->

            <div
                v-if="
                    deposits.length > 0
                "
                class="
                    divide-y
                    divide-slate-100
                    md:hidden
                "
            >

                <div
                    v-for="deposit in deposits"
                    :key="deposit.id"
                    class="
                        px-5
                        py-4
                    "
                >

                    <div
                        class="
                            flex
                            items-start
                            justify-between
                            gap-4
                        "
                    >

                        <div
                            class="
                                min-w-0
                            "
                        >

                            <div
                                class="
                                    truncate
                                    text-sm
                                    font-semibold
                                    text-slate-800
                                "
                            >
                                {{
                                    deposit.deposit_number
                                }}
                            </div>

                            <div
                                class="
                                    mt-0.5
                                    text-xs
                                    text-slate-400
                                "
                            >
                                {{
                                    deposit.session_number ??
                                    '-'
                                }}
                            </div>

                        </div>

                        <div
                            class="
                                shrink-0
                                text-right
                            "
                        >

                            <div
                                class="
                                    text-sm
                                    font-semibold
                                    tabular-nums
                                    text-slate-800
                                "
                            >
                                {{
                                    formatCurrency(
                                        deposit.amount
                                    )
                                }}
                            </div>

                            <div
                                class="mt-1"
                            >

                                <StatusBadge
                                    :status="
                                        deposit.status
                                    "
                                />

                            </div>

                        </div>

                    </div>

                    <div
                        class="mt-3"
                    >

                        <div
                            class="
                                text-xs
                                font-medium
                                text-slate-600
                            "
                        >
                            {{
                                deposit
                                    .destination_account
                                    ?.name ??
                                '-'
                            }}
                        </div>

                        <div
                            class="
                                mt-0.5
                                text-xs
                                text-slate-400
                            "
                        >
                            {{
                                deposit
                                    .destination_account
                                    ?.code ??
                                '-'
                            }}
                        </div>

                        <div
                            class="
                                mt-1
                                text-xs
                                text-slate-400
                            "
                        >
                            {{
                                formatDateTime(
                                    deposit.created_at
                                )
                            }}
                        </div>

                    </div>

                    <div
                        class="
                            mt-3
                            flex
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

                            :show-print="true"

                            @view="
                                viewDeposit(
                                    deposit
                                )
                            "

                            @print="
                                printDeposit(
                                    deposit
                                )
                            "
                         />
                    </div>

                </div>

            </div>

        </div>

    </section>
<ViewDepositModal
    :show="showViewDepositModal"
    :deposit="selectedDeposit"
    :processing="processingId === selectedDeposit?.id"
    @close="closeViewDeposit"
    @approve="approveDeposit(selectedDeposit)"
    @reject="rejectDeposit(selectedDeposit)"
    @submit="submitDeposit(selectedDeposit)"
    @receive="receiveDeposit(selectedDeposit)"
    @post="postDeposit(selectedDeposit)"
    @cancel="cancelDeposit(selectedDeposit)"
/>
</template>
