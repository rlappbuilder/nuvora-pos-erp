<script setup>
import { computed, ref, nextTick } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    report: {
        type: Object,
        required: true,
    },

    filters: {
        type: Object,
        default: () => ({}),
    },
})

const supplierId = ref(props.filters?.supplier_id ?? '')
const branchId = ref(props.filters?.branch_id ?? '')
const asOfDate = ref(
    props.filters?.as_of_date
        ?? props.report?.as_of_date
        ?? ''
)

const activeBucket = ref('outstanding')

const tableSection = ref(null)

const rows = computed(() => {
    return props.report?.rows ?? []
})

const totals = computed(() => {
    return props.report?.totals ?? {
        current: 0,
        days_1_30: 0,
        days_31_60: 0,
        days_61_90: 0,
        over_90: 0,
        total: 0,
    }
})

const filteredRows = computed(() => {
    if (activeBucket.value === 'outstanding') {
        return rows.value.filter(
            row => Number(row.total ?? 0) > 0
        )
    }

    return rows.value.filter(
        row => Number(row[activeBucket.value] ?? 0) > 0
    )
})

const filteredTotals = computed(() => {
    if (activeBucket.value === 'outstanding') {
        return {
            current: totals.value.current,
            days_1_30: totals.value.days_1_30,
            days_31_60: totals.value.days_31_60,
            days_61_90: totals.value.days_61_90,
            over_90: totals.value.over_90,
            total: totals.value.total,
        }
    }

    return {
        current:
            activeBucket.value === 'current'
                ? totals.value.current
                : 0,

        days_1_30:
            activeBucket.value === 'days_1_30'
                ? totals.value.days_1_30
                : 0,

        days_31_60:
            activeBucket.value === 'days_31_60'
                ? totals.value.days_31_60
                : 0,

        days_61_90:
            activeBucket.value === 'days_61_90'
                ? totals.value.days_61_90
                : 0,

        over_90:
            activeBucket.value === 'over_90'
                ? totals.value.over_90
                : 0,

        total:
            filteredRows.value.reduce(
                (sum, row) =>
                    sum + Number(row[activeBucket.value] ?? 0),
                0
            ),
    }
})

const summaryCards = computed(() => [
    {
        key: 'outstanding',
        label: 'Outstanding',
        value: totals.value.total,
        classes: 'bg-white border-gray-200 text-gray-900',
        accent: 'bg-gray-500',
    },
    {
        key: 'current',
        label: 'Current',
        value: totals.value.current,
        classes: 'bg-white border-gray-200 text-gray-900',
        accent: 'bg-emerald-500',
    },
    {
        key: 'days_1_30',
        label: '1–30 Days',
        value: totals.value.days_1_30,
        classes: 'bg-white border-gray-200 text-gray-900',
        accent: 'bg-blue-500',
    },
    {
        key: 'days_31_60',
        label: '31–60 Days',
        value: totals.value.days_31_60,
        classes: 'bg-white border-gray-200 text-gray-900',
        accent: 'bg-yellow-500',
    },
    {
        key: 'days_61_90',
        label: '61–90 Days',
        value: totals.value.days_61_90,
        classes: 'bg-white border-gray-200 text-gray-900',
        accent: 'bg-orange-500',
    },
    {
        key: 'over_90',
        label: '>90 Days',
        value: totals.value.over_90,
        classes: 'bg-white border-gray-200 text-gray-900',
        accent: 'bg-red-500',
    },
])

const formatAmount = (value) => {
    const amount = Number(value ?? 0)

    if (amount === 0) {
        return '—'
    }

    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(amount)
}

const formatDate = (value) => {
    if (!value) {
        return '-'
    }

    return new Intl.DateTimeFormat('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(new Date(value))
}

const selectBucket = async (bucket) => {
    activeBucket.value = bucket

    await nextTick()

    tableSection.value?.scrollIntoView({
        behavior: 'smooth',
        block: 'start',
    })
}

const applyFilter = () => {
    router.get(
        route('reports.ap-aging'),
        {
            branch_id: branchId.value || undefined,
            supplier_id: supplierId.value || undefined,
            as_of_date: asOfDate.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    )
}
const printReport = () => {
    window.print()
}

const exportPdf = () => {
    window.location.href = route(
        'reports.ap-aging.pdf',
        {
            branch_id: branchId.value || undefined,
            supplier_id: supplierId.value || undefined,
            as_of_date: asOfDate.value || undefined,
        }
    )
}

const exportExcel = () => {
    window.location.href = route(
        'reports.ap-aging.excel',
        {
            branch_id: branchId.value || undefined,
            supplier_id: supplierId.value || undefined,
            as_of_date: asOfDate.value || undefined,
        }
    )
}
</script><template>
    <AppLayout>
        <div class="space-y-6">        <!-- Page Header -->
        <div
            class="
                flex
                items-start
                justify-between
                gap-6
            "
        >
            <div>
                <h1
                    class="
                        text-2xl
                        font-semibold
                        tracking-tight
                        text-gray-900
                    "
                >
                    AP Aging
                </h1>

                <p class="mt-1 text-sm text-gray-500">
                    Accounts Payable Aging Report
                </p>
            </div>
            <!-- Report Actions -->
            <div
                class="
                    flex
                    items-center
                    gap-2
                    print:hidden
                "
            >
                <!-- Print -->
                <button
                    type="button"
                    class="
                        inline-flex
                        items-center
                        gap-2
                        rounded-lg
                        border
                        border-gray-200
                        bg-white
                        px-3
                        py-2
                        text-sm
                        font-medium
                        text-gray-700
                        transition
                        hover:bg-gray-50
                    "
                     @click="printReport"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        class="h-4 w-4 text-gray-600"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M6 9V3h12v6"
                        />
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"
                        />
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M6 14h12v7H6z"
                        />
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M18 12h.01"
                        />
                    </svg>

                    Print
                </button>

                <!-- PDF -->
                <button
                    type="button"
                    class="
                        inline-flex
                        items-center
                        gap-2
                        rounded-lg
                        border
                        border-gray-200
                        bg-white
                        px-3
                        py-2
                        text-sm
                        font-medium
                        text-gray-700
                        transition
                        hover:bg-gray-50
                    "
                    @click="exportPdf"
                >
                    <svg
                        viewBox="0 0 32 32"
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                    >
                        <path
                            fill="#E53935"
                            d="
                                M7 2h13l7 7v21H7z
                            "
                        />
                        <path
                            fill="#fff"
                            d="
                                M20 2v7h7
                            "
                        />
                        <path
                            fill="#fff"
                            d="
                                M10 18.5h2.3
                                c2.1 0 3.3-1.1 3.3-2.8
                                0-1.7-1.2-2.7-3.3-2.7H10zm2.2-1.8
                                h-.5v-1.9h.5
                                c.7 0 1.2.3 1.2.9
                                0 .7-.5 1-1.2 1z
                            "
                        />
                        <path
                            fill="#fff"
                            d="
                                M17 18.5h2.1
                                c2.4 0 3.8-1 3.8-3.7
                                0-2.5-1.4-3.8-3.8-3.8H17zm2.1-5.7
                                h.3c1.1 0 1.7.6 1.7 2
                                0 1.5-.6 2-1.7 2h-.3z
                            "
                        />
                    </svg>

                    PDF
                </button>

                <!-- Excel -->
                <button
                    type="button"
                    class="
                        inline-flex
                        items-center
                        gap-2
                        rounded-lg
                        border
                        border-gray-200
                        bg-white
                        px-3
                        py-2
                        text-sm
                        font-medium
                        text-gray-700
                        transition
                        hover:bg-gray-50
                    "
                     @click="exportExcel"
                >
                    <svg
                        viewBox="0 0 32 32"
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                    >
                        <path
                            fill="#21A366"
                            d="
                                M18 3h9a2 2 0 0 1 2 2v22
                                a2 2 0 0 1-2 2h-9z
                            "
                        />
                        <path
                            fill="#107C41"
                            d="
                                M3 7a2 2 0 0 1 2-2h13v22H5
                                a2 2 0 0 1-2-2z
                            "
                        />
                        <path
                            fill="#fff"
                            d="
                                M8.2 11h2.4l2.1 3.5
                                2-3.5h2.3l-3.1 5
                                3.2 5h-2.5l-2-3.6
                                -2.1 3.6H8.1l3.2-5z
                            "
                        />
                    </svg>

                    Excel
                </button>
            </div>
        </div>

        <!-- Filter -->
        <div
            class="
                flex
                flex-wrap
                items-end
                gap-4
            "
        >
            <div>
                <label
                    class="
                        mb-1
                        block
                        text-xs
                        font-medium
                        text-gray-500
                    "
                >
                    As of Date
                </label>

                <input
                    v-model="asOfDate"
                    type="date"
                    class="
                        rounded-lg
                        border
                        border-gray-200
                        bg-white
                        px-3
                        py-2
                        text-sm
                        outline-none
                        focus:border-gray-400
                    "
                />
            </div>

            <button
                type="button"
                class="
                    print:hidden
                    rounded-lg
                    bg-gray-900
                    px-4
                    py-2
                    text-sm
                    font-medium
                    text-white
                    transition
                    hover:bg-gray-800
                "
                @click="applyFilter"
            >
                Apply
            </button>
        </div>

        <!-- Summary -->
        <div
            class="
                grid
                grid-cols-2
                gap-3
                sm:grid-cols-3
                lg:grid-cols-6
            "
        >
            <button
                v-for="card in summaryCards"
                :key="card.key"
                type="button"
                class="
                    print-summary-item
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
                    activeBucket === card.key
                        ? 'ring-2 ring-gray-300 ring-offset-1'
                        : '',
                ]"
                @click="selectBucket(card.key)"
            >
                <!-- Accent -->
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
                    Rp {{ formatAmount(card.value) }}
                </div>
            </button>
        </div>

        <!-- Report -->
        <div
                ref="tableSection"
                class="
                    scroll-mt-6
                    overflow-hidden
                    rounded-xl
                    bg-white
                    print:overflow-visible
                    print:rounded-none
                "
            >
            <!-- Report Heading -->
            <div
                class="
                    border-b
                    border-gray-100
                    px-6
                    py-5
                "
            >
                <div>
                    <h2
                        class="
                            text-base
                            font-semibold
                            text-gray-900
                        "
                    >
                        Accounts Payable Aging
                    </h2>

                    <p
                        class="
                            mt-1
                            text-sm
                            text-gray-500
                        "
                    >
                        As of {{ formatDate(report.as_of_date) }}
                        <span
                            v-if="activeBucket !== 'outstanding'"
                            class="text-gray-400"
                        >
                            · {{ summaryCards.find(card => card.key === activeBucket)?.label }}
                        </span>
                    </p>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">

                <table
                    class="
                        print-table
                        w-full
                        border-collapse
                        text-sm
                    "
                >
                    <thead>
                        <tr
                            class="
                                border-b
                                border-gray-100
                            "
                        >
                            <th
                                class="
                                    px-6
                                    py-3
                                    text-left
                                    text-xs
                                    font-semibold
                                    uppercase
                                    tracking-wide
                                    text-gray-500
                                "
                            >
                                Supplier
                            </th>

                            <th
                                class="
                                    px-4
                                    py-3
                                    text-right
                                    text-xs
                                    font-semibold
                                    uppercase
                                    tracking-wide
                                    text-gray-500
                                "
                            >
                                Current
                            </th>

                            <th
                                class="
                                    px-4
                                    py-3
                                    text-right
                                    text-xs
                                    font-semibold
                                    uppercase
                                    tracking-wide
                                    text-gray-500
                                "
                            >
                                1–30
                            </th>

                            <th
                                class="
                                    px-4
                                    py-3
                                    text-right
                                    text-xs
                                    font-semibold
                                    uppercase
                                    tracking-wide
                                    text-gray-500
                                "
                            >
                                31–60
                            </th>

                            <th
                                class="
                                    px-4
                                    py-3
                                    text-right
                                    text-xs
                                    font-semibold
                                    uppercase
                                    tracking-wide
                                    text-gray-500
                                "
                            >
                                61–90
                            </th>

                            <th
                                class="
                                    px-4
                                    py-3
                                    text-right
                                    text-xs
                                    font-semibold
                                    uppercase
                                    tracking-wide
                                    text-gray-500
                                "
                            >
                                &gt;90
                            </th>

                            <th
                                class="
                                    px-6
                                    py-3
                                    text-right
                                    text-xs
                                    font-semibold
                                    uppercase
                                    tracking-wide
                                    text-gray-500
                                "
                            >
                                Total
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="row in filteredRows"
                            :key="row.supplier_id"
                            class="
                                border-b
                                border-gray-50
                                transition
                                hover:bg-gray-50/50
                            "
                        >
                            <td
                                class="
                                    px-6
                                    py-4
                                    font-medium
                                    text-gray-800
                                "
                            >
                                {{ row.supplier_name }}
                            </td>

                            <td
                                class="
                                    px-4
                                    py-4
                                    text-right
                                    tabular-nums
                                    text-gray-700
                                "
                            >
                                {{ formatAmount(
                                    activeBucket === 'outstanding'
                                        ? row.current
                                        : activeBucket === 'current'
                                            ? row.current
                                            : 0
                                ) }}
                            </td>

                            <td
                                class="
                                    px-4
                                    py-4
                                    text-right
                                    tabular-nums
                                    text-gray-700
                                "
                            >
                                {{ formatAmount(
                                    activeBucket === 'outstanding'
                                        ? row.days_1_30
                                        : activeBucket === 'days_1_30'
                                            ? row.days_1_30
                                            : 0
                                ) }}
                            </td>

                            <td
                                class="
                                    px-4
                                    py-4
                                    text-right
                                    tabular-nums
                                    text-gray-700
                                "
                            >
                                {{ formatAmount(
                                    activeBucket === 'outstanding'
                                        ? row.days_31_60
                                        : activeBucket === 'days_31_60'
                                            ? row.days_31_60
                                            : 0
                                ) }}
                            </td>

                            <td
                                class="
                                    px-4
                                    py-4
                                    text-right
                                    tabular-nums
                                    text-gray-700
                                "
                            >
                                {{ formatAmount(
                                    activeBucket === 'outstanding'
                                        ? row.days_61_90
                                        : activeBucket === 'days_61_90'
                                            ? row.days_61_90
                                            : 0
                                ) }}
                            </td>

                            <td
                                class="
                                    px-4
                                    py-4
                                    text-right
                                    tabular-nums
                                    text-gray-700
                                "
                            >
                                {{ formatAmount(
                                    activeBucket === 'outstanding'
                                        ? row.over_90
                                        : activeBucket === 'over_90'
                                            ? row.over_90
                                            : 0
                                ) }}
                            </td>

                            <td
                                class="
                                    px-6
                                    py-4
                                    text-right
                                    font-semibold
                                    tabular-nums
                                    text-gray-900
                                "
                            >
                                {{ formatAmount(
                                    activeBucket === 'outstanding'
                                        ? row.total
                                        : row[activeBucket]
                                ) }}
                            </td>
                        </tr>

                        <!-- Empty -->
                        <tr v-if="filteredRows.length === 0">
                            <td
                                colspan="7"
                                class="
                                    px-6
                                    py-12
                                    text-center
                                    text-sm
                                    text-gray-400
                                "
                            >
                                No outstanding accounts payable.
                            </td>
                        </tr>
                    </tbody>

                    <!-- Total -->
                    <tfoot>
                        <tr
                            class="
                                border-t
                                border-gray-200
                            "
                        >
                            <td
                                class="
                                    px-6
                                    py-4
                                    font-semibold
                                    text-gray-900
                                "
                            >
                                TOTAL
                            </td>

                            <td
                                class="
                                    px-4
                                    py-4
                                    text-right
                                    font-semibold
                                    tabular-nums
                                    text-gray-900
                                "
                            >
                                {{ formatAmount(filteredTotals.current) }}
                            </td>

                            <td
                                class="
                                    px-4
                                    py-4
                                    text-right
                                    font-semibold
                                    tabular-nums
                                    text-gray-900
                                "
                            >
                                {{ formatAmount(filteredTotals.days_1_30) }}
                            </td>

                            <td
                                class="
                                    px-4
                                    py-4
                                    text-right
                                    font-semibold
                                    tabular-nums
                                    text-gray-900
                                "
                            >
                                {{ formatAmount(filteredTotals.days_31_60) }}
                            </td>

                            <td
                                class="
                                    px-4
                                    py-4
                                    text-right
                                    font-semibold
                                    tabular-nums
                                    text-gray-900
                                "
                            >
                                {{ formatAmount(filteredTotals.days_61_90) }}
                            </td>

                            <td
                                class="
                                    px-4
                                    py-4
                                    text-right
                                    font-semibold
                                    tabular-nums
                                    text-gray-900
                                "
                            >
                                {{ formatAmount(filteredTotals.over_90) }}
                            </td>

                            <td
                                class="
                                    px-6
                                    py-4
                                    text-right
                                    font-bold
                                    tabular-nums
                                    text-gray-900
                                "
                            >
                                {{ formatAmount(filteredTotals.total) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>

            </div>
        </div>

    </div>
</AppLayout>

</template>
<style>
@media print {
    @page {
        size: A4 landscape;
        margin: 12mm;
    }

    body {
        background: white !important;
    }

    .print-report-header {
        display: block;
    }

    .print-summary {
        break-inside: avoid;
        page-break-inside: avoid;
    }

    .print-summary-item {
        break-inside: avoid;
        page-break-inside: avoid;
    }

    .print-table {
        width: 100% !important;
    }

    .print-table tr {
        break-inside: avoid;
        page-break-inside: avoid;
    }
}
</style>