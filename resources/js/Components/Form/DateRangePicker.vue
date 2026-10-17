<script setup>
import {
    computed,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from 'vue'

import FlatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'

const props = defineProps({
    dateFrom: {
        type: String,
        default: '',
    },

    dateTo: {
        type: String,
        default: '',
    },

    loading: {
        type: Boolean,
        default: false,
    },

    placeholder: {
        type: String,
        default: 'Select date range',
    },

    disabled: {
        type: Boolean,
        default: false,
    },

    defaultPreset: {
        type: String,
        default: '',
    },
})

const emit = defineEmits([
    'update:dateFrom',
    'update:dateTo',
    'apply',
    'clear',
])

/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const showDateRangePicker = ref(false)
const dateRangePickerRef = ref(null)
const isMobile = ref(false)

const selectedPreset = ref('')
const calendarRange = ref([])

const draftDateFrom = ref('')
const draftDateTo = ref('')

/*
|--------------------------------------------------------------------------
| Date Helpers
|--------------------------------------------------------------------------
*/

const pad = (value) => {
    return String(value).padStart(2, '0')
}

const formatDateValue = (date) => {
    return [
        date.getFullYear(),
        pad(date.getMonth() + 1),
        pad(date.getDate()),
    ].join('-')
}

const parseDate = (value) => {
    if (!value) {
        return null
    }

    const parts = value.split('-').map(Number)

    if (parts.length !== 3 || parts.some(Number.isNaN)) {
        return null
    }

    const [year, month, day] = parts

    const date = new Date(year, month - 1, day)

    if (
        date.getFullYear() !== year ||
        date.getMonth() !== month - 1 ||
        date.getDate() !== day
    ) {
        return null
    }

    return date
}

const formatDisplayDate = (value) => {
    const date = parseDate(value)

    if (!date) {
        return ''
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(date)
}

const startOfMonth = (date) => {
    return new Date(
        date.getFullYear(),
        date.getMonth(),
        1,
    )
}

const endOfMonth = (date) => {
    return new Date(
        date.getFullYear(),
        date.getMonth() + 1,
        0,
    )
}

/*
|--------------------------------------------------------------------------
| Date Range Label
|--------------------------------------------------------------------------
*/

const dateRangeLabel = computed(() => {
    if (!props.dateFrom && !props.dateTo) {
        return props.placeholder
    }

    if (props.dateFrom && !props.dateTo) {
        return formatDisplayDate(props.dateFrom)
    }

    if (!props.dateFrom && props.dateTo) {
        return formatDisplayDate(props.dateTo)
    }

    return [
        formatDisplayDate(props.dateFrom),
        formatDisplayDate(props.dateTo),
    ].join(' - ')
})

const draftRangeLabel = computed(() => {
    if (!draftDateFrom.value && !draftDateTo.value) {
        return 'No date range selected'
    }

    if (draftDateFrom.value && !draftDateTo.value) {
        return formatDisplayDate(draftDateFrom.value)
    }

    if (!draftDateFrom.value && draftDateTo.value) {
        return formatDisplayDate(draftDateTo.value)
    }

    return [
        formatDisplayDate(draftDateFrom.value),
        formatDisplayDate(draftDateTo.value),
    ].join(' - ')
})

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
        !dateRangePickerRef.value.contains(event.target)
    ) {
        closeDateRangePicker()
    }
}

/*
|--------------------------------------------------------------------------
| Calendar Configuration
|--------------------------------------------------------------------------
*/

const calendarConfig = computed(() => ({
    mode: 'range',
    inline: true,

    showMonths: isMobile.value ? 1 : 2,

    dateFormat: 'Y-m-d',
    allowInput: false,
    clickOpens: true,

    locale: {
        firstDayOfWeek: 1,
    },

    onChange: handleCalendarChange,
}))

/*
|--------------------------------------------------------------------------
| Calendar Change
|--------------------------------------------------------------------------
*/

function handleCalendarChange(selectedDates) {
    if (!selectedDates || selectedDates.length === 0) {
        draftDateFrom.value = ''
        draftDateTo.value = ''

        return
    }

    draftDateFrom.value = formatDateValue(
        selectedDates[0],
    )

    draftDateTo.value = selectedDates[1]
        ? formatDateValue(selectedDates[1])
        : ''
}

/*
|--------------------------------------------------------------------------
| Open / Close
|--------------------------------------------------------------------------
*/

const openDateRangePicker = () => {
    if (props.disabled || props.loading) {
        return
    }

    draftDateFrom.value = props.dateFrom
    draftDateTo.value = props.dateTo

    calendarRange.value = [
        props.dateFrom,
        props.dateTo,
    ].filter(Boolean)

    selectedPreset.value = ''

    showDateRangePicker.value = true
}

const closeDateRangePicker = () => {
    showDateRangePicker.value = false
}

/*
|--------------------------------------------------------------------------
| Presets
|--------------------------------------------------------------------------
*/

const selectPreset = (preset) => {
    const today = new Date()

    today.setHours(0, 0, 0, 0)

    let from = null
    let to = null

    switch (preset) {
        case 'today':
            from = today
            to = today
            break

        case 'yesterday':
            from = new Date(today)
            from.setDate(from.getDate() - 1)

            to = new Date(from)
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
            from = startOfMonth(today)
            to = today
            break

        case 'last-month': {
            const previousMonth = new Date(
                today.getFullYear(),
                today.getMonth() - 1,
                1,
            )

            from = startOfMonth(previousMonth)
            to = endOfMonth(previousMonth)

            break
        }

        case 'custom':
            selectedPreset.value = 'custom'
            return

        default:
            return
    }

    draftDateFrom.value = formatDateValue(from)
    draftDateTo.value = formatDateValue(to)

    calendarRange.value = [
        draftDateFrom.value,
        draftDateTo.value,
    ]

    selectedPreset.value = preset
}

/*
|--------------------------------------------------------------------------
| Apply / Clear
|--------------------------------------------------------------------------
*/

const applyDateRange = () => {
    if (props.loading) {
        return
    }

    if (
        draftDateFrom.value &&
        draftDateTo.value &&
        draftDateFrom.value > draftDateTo.value
    ) {
        const originalFrom = draftDateFrom.value

        draftDateFrom.value = draftDateTo.value
        draftDateTo.value = originalFrom
    }

    emit('update:dateFrom', draftDateFrom.value)
    emit('update:dateTo', draftDateTo.value)

    emit('apply', {
        dateFrom: draftDateFrom.value,
        dateTo: draftDateTo.value,
    })

    closeDateRangePicker()
}

const clearDateRange = () => {
    if (props.loading) {
        return
    }

    draftDateFrom.value = ''
    draftDateTo.value = ''

    calendarRange.value = []
    selectedPreset.value = ''

    emit('update:dateFrom', '')
    emit('update:dateTo', '')

    emit('clear')

    closeDateRangePicker()
}

const cancelDateRange = () => {
    draftDateFrom.value = props.dateFrom
    draftDateTo.value = props.dateTo

    closeDateRangePicker()
}

/*
|--------------------------------------------------------------------------
| Watchers
|--------------------------------------------------------------------------
*/

watch(
    () => [props.dateFrom, props.dateTo],
    ([from, to]) => {
        if (!showDateRangePicker.value) {
            draftDateFrom.value = from
            draftDateTo.value = to

            calendarRange.value = [
                from,
                to,
            ].filter(Boolean)
        }
    },
    {
        immediate: true,
    },
)

watch(
    () => props.defaultPreset,
    (preset) => {
        if (preset) {
            selectPreset(preset)
        }
    },
)

/*
|--------------------------------------------------------------------------
| Lifecycle
|--------------------------------------------------------------------------
*/

onMounted(() => {
    updateIsMobile()

    window.addEventListener(
        'resize',
        updateIsMobile,
    )

    document.addEventListener(
        'mousedown',
        handleClickOutsideDateRange,
    )

    document.addEventListener(
        'touchstart',
        handleClickOutsideDateRange,
    )
})

onBeforeUnmount(() => {
    window.removeEventListener(
        'resize',
        updateIsMobile,
    )

    document.removeEventListener(
        'mousedown',
        handleClickOutsideDateRange,
    )

    document.removeEventListener(
        'touchstart',
        handleClickOutsideDateRange,
    )
})
</script>

<template>
    <div
        ref="dateRangePickerRef"
        class="relative min-w-0"
    >
        <!-- TRIGGER -->
        <button
            type="button"
            :disabled="disabled || loading"
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
                py-2.5
                text-left
                shadow-sm
                transition
                hover:border-slate-300
                hover:bg-slate-50
                disabled:cursor-not-allowed
                disabled:opacity-50
                sm:min-w-[270px]
            "
            @click="
                showDateRangePicker
                    ? closeDateRangePicker()
                    : openDateRangePicker()
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
                    {{ dateRangeLabel }}
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

        <!-- POPUP -->
        <div
            v-if="showDateRangePicker"
            class="
                absolute
                right-0
                z-50
                mt-2
                w-max
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
                            { value: 'today', label: 'Today' },
                            { value: 'yesterday', label: 'Yesterday' },
                            { value: 'last-7-days', label: 'Last 7 Days' },
                            { value: 'last-30-days', label: 'Last 30 Days' },
                            { value: 'this-month', label: 'This Month' },
                            { value: 'last-month', label: 'Last Month' },
                            { value: 'custom', label: 'Custom' },
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
                        @click="selectPreset(preset.value)"
                    >
                        {{ preset.label }}
                    </button>

                    <button
                        v-if="dateFrom || dateTo || draftDateFrom || draftDateTo"
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
                <div class="min-w-0 overflow-auto p-3">
                    <FlatPickr
                        v-model="calendarRange"
                        :config="calendarConfig"
                    />
                </div>
            </div>

            <!-- FOOTER -->
            <div
                class="
                    flex
                    flex-col
                    gap-3
                    border-t
                    border-slate-100
                    px-4
                    py-3
                    sm:flex-row
                    sm:items-center
                    sm:justify-between
                "
            >
                <p class="text-xs text-slate-500">
                    {{ draftRangeLabel }}
                </p>

                <div class="flex items-center justify-end gap-2">
                    <button
                        type="button"
                        class="
                            rounded-lg
                            border
                            border-slate-200
                            px-3
                            py-2
                            text-xs
                            font-medium
                            text-slate-600
                            transition
                            hover:bg-slate-50
                        "
                        @click="cancelDateRange"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        :disabled="
                            loading ||
                            (!draftDateFrom && !draftDateTo) ||
                            (!!draftDateFrom && !draftDateTo)
                        "
                        class="
                            rounded-lg
                            bg-blue-600
                            px-4
                            py-2
                            text-xs
                            font-semibold
                            text-white
                            transition
                            hover:bg-blue-700
                            disabled:cursor-not-allowed
                            disabled:opacity-50
                        "
                        @click="applyDateRange"
                    >
                        Apply
                    </button>
                </div>
            </div>

            <div
                v-if="loading"
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
                Loading data...
            </div>
        </div>
    </div>
</template>