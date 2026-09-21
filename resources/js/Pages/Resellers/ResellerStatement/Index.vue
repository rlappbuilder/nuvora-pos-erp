<script setup>
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import SearchableSelect from '@/Components/Form/SearchableSelect.vue'
import AppLayout from '@/Layouts/AppLayout.vue'


const props = defineProps({

    statement: {
        type: Object,
        required: true,
    },

    statistics: {
        type: Object,
        default: () => ({}),
    },

    filters: {
        type: Object,
        default: () => ({}),
    },

    resellers: {
        type: Array,
        default: () => [],
    },

    branches: {
        type: Array,
        default: () => [],
    },

})


/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

const resellerId = ref(
    props.filters.reseller_id ?? ''
)

const branchId = ref(
    props.filters.branch_id ?? ''
)

const dateFrom = ref(
    props.filters.date_from ?? ''
)

const dateTo = ref(
    props.filters.date_to ?? ''
)


/*
|--------------------------------------------------------------------------
| Report Data
|--------------------------------------------------------------------------
*/

const overview = computed(() =>
    props.statement?.overview ?? {}
)

const stock = computed(() =>
    props.statement?.stock ?? {}
)

const receivable = computed(() =>
    props.statement?.receivable ?? {}
)

const settlements = computed(() =>
    props.statement?.settlements ?? []
)

const payments = computed(() =>
    props.statement?.payments ?? []
)


/*
|--------------------------------------------------------------------------
| Active Tab
|--------------------------------------------------------------------------
*/

const activeTab = ref('overview')


const tabs = [
    {
        key: 'overview',
        label: 'Overview',
    },
    {
        key: 'stock',
        label: 'Stock',
    },
    {
        key: 'receivable',
        label: 'Receivable',
    },
    {
        key: 'settlement',
        label: 'Settlement',
    },
    {
        key: 'payment',
        label: 'Payment',
    },
]


/*
|--------------------------------------------------------------------------
| Format
|--------------------------------------------------------------------------
*/

const formatAmount = (amount) => {

    return new Intl.NumberFormat(
        'id-ID',
        {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }
    ).format(
        Number(amount ?? 0)
    )

}


const formatQty = (qty) => {

    return new Intl.NumberFormat(
        'id-ID',
        {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2,
        }
    ).format(
        Number(qty ?? 0)
    )

}


const formatDate = (date) => {

    if (!date) {
        return '-'
    }

    return new Intl.DateTimeFormat(
        'id-ID',
        {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
        }
    ).format(
        new Date(date)
    )

}


/*
|--------------------------------------------------------------------------
| Filter
|--------------------------------------------------------------------------
*/

const applyFilter = () => {

    router.get(
        route('reseller-statements.index'),
        {
            date_from:
                dateFrom.value ||
                undefined,

            date_to:
                dateTo.value ||
                undefined,

            reseller_id:
                resellerId.value ||
                undefined,

            branch_id:
                branchId.value ||
                undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    )

}


/*
|--------------------------------------------------------------------------
| Refresh
|--------------------------------------------------------------------------
*/

const refreshReport = () => {

    dateFrom.value = ''

    dateTo.value = ''

    resellerId.value = ''

    branchId.value = ''

    activeTab.value = 'overview'

    applyFilter()

}


/*
|--------------------------------------------------------------------------
| Print / Export
|--------------------------------------------------------------------------
*/

const buildQuery = () => {

    return new URLSearchParams({

        ...(dateFrom.value
            ? {
                date_from:
                    dateFrom.value
            }
            : {}),

        ...(dateTo.value
            ? {
                date_to:
                    dateTo.value
            }
            : {}),

        ...(resellerId.value
            ? {
                reseller_id:
                    resellerId.value
            }
            : {}),

        ...(branchId.value
            ? {
                branch_id:
                    branchId.value
            }
            : {}),

    })

}


const printReport = () => {

    const query = buildQuery()

    window.open(
        route('reseller-statements.print') +
        (
            query.toString()
                ? `?${query.toString()}`
                : ''
        ),
        '_blank'
    )

}


const exportPdf = () => {

    const query = buildQuery()

    window.location.href =
        route('reseller-statements.pdf') +
        (
            query.toString()
                ? `?${query.toString()}`
                : ''
        )

}


const exportExcel = () => {

    const query = buildQuery()

    window.location.href =
        route('reseller-statements.excel') +
        (
            query.toString()
                ? `?${query.toString()}`
                : ''
        )

}
</script>


<template>

<AppLayout>

    <div class="space-y-4">


        <!-- Header -->

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
                    Reseller Statement
                </h1>


                <p
                    class="
                        mt-1
                        text-sm
                        text-gray-500
                    "
                >
                    Reseller stock and receivable activity
                </p>

            </div>


            <!-- Actions -->

            <div
                class="
                    flex
                    items-center
                    gap-2
                    print:hidden
                "
            >

                <!-- Refresh -->

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
                    @click="refreshReport"
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
                            d="M20 11a8.1 8.1 0 0 0-14.9-4M4 5v4h4"
                        />

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M4 13a8.1 8.1 0 0 0 14.9 4M20 19v-4h-4"
                        />
                    </svg>

                    Refresh

                </button>


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
                            d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5h-2"
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
                            d="M7 2h13l7 7v21H7z"
                        />

                        <path
                            fill="#fff"
                            d="M20 2v7h7"
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


        <!-- Statement Info -->

        <div
            class="
                rounded-xl
                border
                border-gray-100
                bg-white
                px-4
                py-3
                shadow-sm
            "
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

                    <div
                        class="
                            text-xs
                            font-medium
                            text-gray-500
                        "
                    >
                        Reseller Statement
                    </div>

                    <div
                        class="
                            mt-0.5
                            text-sm
                            font-semibold
                            text-gray-900
                        "
                    >
                        {{
                            resellerId
                                ? (
                                    resellers.find(
                                        item =>
                                            String(item.id) ===
                                            String(resellerId)
                                    )?.label ??
                                    'Selected Reseller'
                                )
                                : 'All Resellers'
                        }}
                    </div>

                </div>


                <div
                    class="
                        text-sm
                        text-gray-500
                    "
                >

                    <template
                        v-if="dateFrom || dateTo"
                    >

                        {{ formatDate(dateFrom) }}
                        –
                        {{ formatDate(dateTo) }}

                    </template>

                    <template v-else>

                        All Data

                    </template>

                </div>

            </div>

        </div>


        <!-- Filter -->

        <div
            class="
                rounded-xl
                border
                border-gray-100
                bg-white
                p-4
                shadow-sm
                print:hidden
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

                <!-- Date From -->

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
                        Date From
                    </label>

                    <input
                        v-model="dateFrom"
                        type="date"
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


                <!-- Date To -->

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
                        Date To
                    </label>

                    <input
                        v-model="dateTo"
                        type="date"
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
                        v-model="resellerId"
                        :options="resellers"
                        label="label"
                        value-key="id"
                        placeholder="Select Reseller"
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
                        v-model="branchId"
                        :options="branches"
                        label="label"
                        value-key="id"
                        placeholder="All Branches"
                    />

                </div>


                <!-- Apply -->

                <div
                    class="
                        flex
                        items-end
                    "
                >

                    <button
                        type="button"
                        class="
                            w-full
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

            </div>

        </div>
        <!-- Summary -->

        <div
            class="
                print-summary
                rounded-xl
                border
                border-gray-100
                bg-white
                p-3
                shadow-sm
                print:rounded-none
                print:border-0
                print:p-0
                print:shadow-none
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

                <!-- Opening Stock -->

                <div
                    class="
                        print-summary-item
                        relative
                        overflow-hidden
                        rounded-lg
                        border
                        border-gray-200
                        bg-white
                        px-4
                        py-3
                    "
                >

                    <span
                        class="
                            absolute
                            inset-y-0
                            left-0
                            w-1
                            bg-gray-500
                        "
                    ></span>

                    <div
                        class="
                            text-xs
                            font-medium
                            text-gray-500
                        "
                    >
                        Opening Stock
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
                        {{
                            formatQty(
                                overview.stock?.opening_stock
                            )
                        }}
                    </div>

                </div>


                <!-- Closing Stock -->

                <div
                    class="
                        print-summary-item
                        relative
                        overflow-hidden
                        rounded-lg
                        border
                        border-gray-200
                        bg-white
                        px-4
                        py-3
                    "
                >

                    <span
                        class="
                            absolute
                            inset-y-0
                            left-0
                            w-1
                            bg-blue-500
                        "
                    ></span>

                    <div
                        class="
                            text-xs
                            font-medium
                            text-gray-500
                        "
                    >
                        Closing Stock
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
                        {{
                            formatQty(
                                overview.stock?.closing_stock
                            )
                        }}
                    </div>

                </div>


                <!-- New Settlement -->

                <div
                    class="
                        print-summary-item
                        relative
                        overflow-hidden
                        rounded-lg
                        border
                        border-gray-200
                        bg-white
                        px-4
                        py-3
                    "
                >

                    <span
                        class="
                            absolute
                            inset-y-0
                            left-0
                            w-1
                            bg-emerald-500
                        "
                    ></span>

                    <div
                        class="
                            text-xs
                            font-medium
                            text-gray-500
                        "
                    >
                        New Settlement
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
                        Rp
                        {{
                            formatAmount(
                                overview.receivable?.new_settlement
                            )
                        }}
                    </div>

                </div>


                <!-- Outstanding -->

                <div
                    class="
                        print-summary-item
                        relative
                        overflow-hidden
                        rounded-lg
                        border
                        border-gray-200
                        bg-white
                        px-4
                        py-3
                    "
                >

                    <span
                        class="
                            absolute
                            inset-y-0
                            left-0
                            w-1
                            bg-yellow-500
                        "
                    ></span>

                    <div
                        class="
                            text-xs
                            font-medium
                            text-gray-500
                        "
                    >
                        Outstanding
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
                        Rp
                        {{
                            formatAmount(
                                overview.receivable?.outstanding
                            )
                        }}
                    </div>

                </div>

            </div>

        </div>


        <!-- Tabs -->

        <div
            class="
                overflow-x-auto
                rounded-xl
                border
                border-gray-100
                bg-white
                shadow-sm
                print:hidden
            "
        >

            <div
                class="
                    flex
                    min-w-max
                    border-b
                    border-gray-100
                "
            >

                <button
                    v-for="tab in tabs"
                    :key="tab.key"
                    type="button"
                    class="
                        relative
                        px-4
                        py-3
                        text-sm
                        font-medium
                        transition
                    "
                    :class="
                        activeTab === tab.key
                            ? 'text-gray-900'
                            : 'text-gray-500 hover:text-gray-700'
                    "
                    @click="activeTab = tab.key"
                >

                    {{ tab.label }}

                    <span
                        v-if="
                            activeTab === tab.key
                        "
                        class="
                            absolute
                            inset-x-0
                            bottom-0
                            h-0.5
                            bg-gray-900
                        "
                    ></span>

                </button>

            </div>

        </div>


        <!-- Overview -->

        <div
            v-if="activeTab === 'overview'"
            class="space-y-4"
        >

            <!-- Stock Position -->

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

                <div
                    class="
                        border-b
                        border-gray-100
                        px-5
                        py-4
                    "
                >

                    <div
                        class="
                            text-sm
                            font-semibold
                            text-gray-900
                        "
                    >
                        Stock Position
                    </div>

                    <div
                        class="
                            mt-0.5
                            text-xs
                            text-gray-500
                        "
                    >
                        Reseller stock movement within the selected period
                    </div>

                </div>


                <div
                    class="
                        grid
                        grid-cols-2
                        divide-x
                        divide-gray-100
                        sm:grid-cols-5
                    "
                >

                    <!-- Opening -->

                    <div
                        class="
                            px-4
                            py-4
                        "
                    >

                        <div
                            class="
                                text-xs
                                text-gray-500
                            "
                        >
                            Opening Stock
                        </div>

                        <div
                            class="
                                mt-1
                                text-sm
                                font-semibold
                                tabular-nums
                                text-gray-900
                            "
                        >
                            {{
                                formatQty(
                                    overview.stock?.opening_stock
                                )
                            }}
                        </div>

                    </div>


                    <!-- Consignment Out -->

                    <div
                        class="
                            px-4
                            py-4
                        "
                    >

                        <div
                            class="
                                text-xs
                                text-gray-500
                            "
                        >
                            Consignment Out
                        </div>

                        <div
                            class="
                                mt-1
                                text-sm
                                font-semibold
                                tabular-nums
                                text-emerald-600
                            "
                        >
                            {{
                                formatQty(
                                    overview.stock?.consignment_out
                                )
                            }}
                        </div>

                    </div>


                    <!-- Sold -->

                    <div
                        class="
                            px-4
                            py-4
                        "
                    >

                        <div
                            class="
                                text-xs
                                text-gray-500
                            "
                        >
                            Sold
                        </div>

                        <div
                            class="
                                mt-1
                                text-sm
                                font-semibold
                                tabular-nums
                                text-red-600
                            "
                        >
                            {{
                                formatQty(
                                    overview.stock?.sold
                                )
                            }}
                        </div>

                    </div>


                    <!-- Return -->

                    <div
                        class="
                            px-4
                            py-4
                        "
                    >

                        <div
                            class="
                                text-xs
                                text-gray-500
                            "
                        >
                            Return
                        </div>

                        <div
                            class="
                                mt-1
                                text-sm
                                font-semibold
                                tabular-nums
                                text-red-600
                            "
                        >
                            {{
                                formatQty(
                                    overview.stock?.return
                                )
                            }}
                        </div>

                    </div>


                    <!-- Closing -->

                    <div
                        class="
                            px-4
                            py-4
                        "
                    >

                        <div
                            class="
                                text-xs
                                text-gray-500
                            "
                        >
                            Closing Stock
                        </div>

                        <div
                            class="
                                mt-1
                                text-sm
                                font-semibold
                                tabular-nums
                                text-gray-900
                            "
                        >
                            {{
                                formatQty(
                                    overview.stock?.closing_stock
                                )
                            }}
                        </div>

                    </div>

                </div>

            </div>


            <!-- Receivable Position -->

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

                <div
                    class="
                        border-b
                        border-gray-100
                        px-5
                        py-4
                    "
                >

                    <div
                        class="
                            text-sm
                            font-semibold
                            text-gray-900
                        "
                    >
                        Receivable Position
                    </div>

                    <div
                        class="
                            mt-0.5
                            text-xs
                            text-gray-500
                        "
                    >
                        Settlement receivable activity within the selected period
                    </div>

                </div>


                <div
                    class="
                        grid
                        grid-cols-2
                        divide-x
                        divide-gray-100
                        sm:grid-cols-4
                    "
                >

                    <!-- Opening AR -->

                    <div
                        class="
                            px-4
                            py-4
                        "
                    >

                        <div
                            class="
                                text-xs
                                text-gray-500
                            "
                        >
                            Opening AR
                        </div>

                        <div
                            class="
                                mt-1
                                text-sm
                                font-semibold
                                tabular-nums
                                text-gray-900
                            "
                        >
                            Rp
                            {{
                                formatAmount(
                                    overview.receivable?.opening_ar
                                )
                            }}
                        </div>

                    </div>


                    <!-- New Settlement -->

                    <div
                        class="
                            px-4
                            py-4
                        "
                    >

                        <div
                            class="
                                text-xs
                                text-gray-500
                            "
                        >
                            New Settlement
                        </div>

                        <div
                            class="
                                mt-1
                                text-sm
                                font-semibold
                                tabular-nums
                                text-emerald-600
                            "
                        >
                            Rp
                            {{
                                formatAmount(
                                    overview.receivable?.new_settlement
                                )
                            }}
                        </div>

                    </div>


                    <!-- Payment -->

                    <div
                        class="
                            px-4
                            py-4
                        "
                    >

                        <div
                            class="
                                text-xs
                                text-gray-500
                            "
                        >
                            Payment
                        </div>

                        <div
                            class="
                                mt-1
                                text-sm
                                font-semibold
                                tabular-nums
                                text-blue-600
                            "
                        >
                            Rp
                            {{
                                formatAmount(
                                    overview.receivable?.payment
                                )
                            }}
                        </div>

                    </div>


                    <!-- Outstanding -->

                    <div
                        class="
                            px-4
                            py-4
                        "
                    >

                        <div
                            class="
                                text-xs
                                text-gray-500
                            "
                        >
                            Outstanding
                        </div>

                        <div
                            class="
                                mt-1
                                text-sm
                                font-semibold
                                tabular-nums
                                text-gray-900
                            "
                        >
                            Rp
                            {{
                                formatAmount(
                                    overview.receivable?.outstanding
                                )
                            }}
                        </div>

                    </div>

                </div>

            </div>


            <!-- Overview Note -->

            <div
                class="
                    rounded-xl
                    border
                    border-gray-100
                    bg-white
                    px-5
                    py-4
                    shadow-sm
                "
            >

                <div
                    class="
                        text-xs
                        font-medium
                        text-gray-500
                    "
                >
                    Statement Basis
                </div>

                <div
                    class="
                        mt-1
                        text-sm
                        text-gray-700
                    "
                >
                    Stock is based on posted reseller inventory movements,
                    while receivable is based on posted settlements and
                    posted receivable payments.
                </div>

            </div>

        </div>
        <!-- Stock -->

        <div
            v-if="activeTab === 'stock'"
            class="space-y-4"
        >

            <!-- Stock Movement -->

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

                <div
                    class="
                        border-b
                        border-gray-100
                        px-5
                        py-4
                    "
                >

                    <div
                        class="
                            text-sm
                            font-semibold
                            text-gray-900
                        "
                    >
                        Stock Movement
                    </div>

                    <div
                        class="
                            mt-0.5
                            text-xs
                            text-gray-500
                        "
                    >
                        Movement of Nuvora-owned stock at reseller locations
                    </div>

                </div>


                <div class="overflow-x-auto">

                    <table class="w-full text-sm">

                        <thead>

                            <tr
                                class="
                                    border-b
                                    border-gray-200
                                "
                            >

                                <!-- Date -->

                                <th
                                    class="
                                        min-w-[110px]
                                        whitespace-nowrap
                                        px-5
                                        py-3
                                        text-left
                                        text-xs
                                        font-semibold
                                        text-gray-500
                                    "
                                >
                                    Date
                                </th>


                                <!-- Document -->

                                <th
                                    class="
                                        min-w-[150px]
                                        px-4
                                        py-3
                                        text-left
                                        text-xs
                                        font-semibold
                                        text-gray-500
                                    "
                                >
                                    Document
                                </th>


                                <!-- Description -->

                                <th
                                    class="
                                        min-w-[220px]
                                        px-4
                                        py-3
                                        text-left
                                        text-xs
                                        font-semibold
                                        text-gray-500
                                    "
                                >
                                    Description
                                </th>


                                <!-- In -->

                                <th
                                    class="
                                        min-w-[100px]
                                        px-4
                                        py-3
                                        text-right
                                        text-xs
                                        font-semibold
                                        text-gray-500
                                    "
                                >
                                    In
                                </th>


                                <!-- Out -->

                                <th
                                    class="
                                        min-w-[100px]
                                        px-4
                                        py-3
                                        text-right
                                        text-xs
                                        font-semibold
                                        text-gray-500
                                    "
                                >
                                    Out
                                </th>


                                <!-- Balance -->

                                <th
                                    class="
                                        min-w-[110px]
                                        px-5
                                        py-3
                                        text-right
                                        text-xs
                                        font-semibold
                                        text-gray-500
                                    "
                                >
                                    Balance
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <tr
                                v-for="
                                    movement in (
                                        stock.movements ?? []
                                    )
                                "
                                :key="movement.id"
                                class="
                                    border-b
                                    border-gray-50
                                    last:border-b-0
                                "
                            >

                                <!-- Date -->

                                <td
                                    class="
                                        whitespace-nowrap
                                        px-5
                                        py-3
                                        text-gray-700
                                    "
                                >
                                    {{
                                        formatDate(
                                            movement.date
                                        )
                                    }}
                                </td>


                                <!-- Document -->

                                <td
                                    class="
                                        px-4
                                        py-3
                                    "
                                >

                                    <div
                                        class="
                                            font-medium
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            movement.document ??
                                            movement.reference_number ??
                                            '-'
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
                                            movement.reference_type ??
                                            '-'
                                        }}
                                    </div>

                                </td>


                                <!-- Description -->

                                <td
                                    class="
                                        px-4
                                        py-3
                                        text-gray-700
                                    "
                                >
                                    {{
                                        movement.description ??
                                        '-'
                                    }}
                                </td>


                                <!-- In -->

                                <td
                                    class="
                                        px-4
                                        py-3
                                        text-right
                                        font-medium
                                        tabular-nums
                                        text-emerald-600
                                    "
                                >

                                    <template
                                        v-if="
                                            Number(
                                                movement.qty_in ?? 0
                                            ) > 0
                                        "
                                    >
                                        {{
                                            formatQty(
                                                movement.qty_in
                                            )
                                        }}
                                    </template>

                                    <template v-else>
                                        -
                                    </template>

                                </td>


                                <!-- Out -->

                                <td
                                    class="
                                        px-4
                                        py-3
                                        text-right
                                        font-medium
                                        tabular-nums
                                        text-red-600
                                    "
                                >

                                    <template
                                        v-if="
                                            Number(
                                                movement.qty_out ?? 0
                                            ) > 0
                                        "
                                    >
                                        {{
                                            formatQty(
                                                movement.qty_out
                                            )
                                        }}
                                    </template>

                                    <template v-else>
                                        -
                                    </template>

                                </td>


                                <!-- Balance -->

                                <td
                                    class="
                                        px-5
                                        py-3
                                        text-right
                                        font-medium
                                        tabular-nums
                                        text-gray-900
                                    "
                                >
                                    {{
                                        formatQty(
                                            movement.balance
                                        )
                                    }}
                                </td>

                            </tr>


                            <!-- Empty -->

                            <tr
                                v-if="
                                    (
                                        stock.movements ?? []
                                    ).length === 0
                                "
                            >

                                <td
                                    colspan="6"
                                    class="
                                        px-5
                                        py-10
                                        text-center
                                        text-sm
                                        text-gray-500
                                    "
                                >
                                    No stock movement found.
                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>


            <!-- Stock Detail -->

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

                <div
                    class="
                        border-b
                        border-gray-100
                        px-5
                        py-4
                    "
                >

                    <div
                        class="
                            text-sm
                            font-semibold
                            text-gray-900
                        "
                    >
                        Stock Detail
                    </div>

                    <div
                        class="
                            mt-0.5
                            text-xs
                            text-gray-500
                        "
                    >
                        Stock position by product, variant and unit
                    </div>

                </div>


                <div class="overflow-x-auto">

                    <table class="w-full text-sm">

                        <thead>

                            <tr
                                class="
                                    border-b
                                    border-gray-200
                                "
                            >

                                <!-- Product -->

                                <th
                                    class="
                                        min-w-[190px]
                                        px-5
                                        py-3
                                        text-left
                                        text-xs
                                        font-semibold
                                        text-gray-500
                                    "
                                >
                                    Product
                                </th>


                                <!-- Variant -->

                                <th
                                    class="
                                        min-w-[130px]
                                        px-4
                                        py-3
                                        text-left
                                        text-xs
                                        font-semibold
                                        text-gray-500
                                    "
                                >
                                    Variant
                                </th>


                                <!-- Unit -->

                                <th
                                    class="
                                        min-w-[90px]
                                        px-4
                                        py-3
                                        text-left
                                        text-xs
                                        font-semibold
                                        text-gray-500
                                    "
                                >
                                    Unit
                                </th>


                                <!-- Opening -->

                                <th
                                    class="
                                        min-w-[105px]
                                        px-4
                                        py-3
                                        text-right
                                        text-xs
                                        font-semibold
                                        text-gray-500
                                    "
                                >
                                    Opening
                                </th>


                                <!-- In -->

                                <th
                                    class="
                                        min-w-[90px]
                                        px-4
                                        py-3
                                        text-right
                                        text-xs
                                        font-semibold
                                        text-gray-500
                                    "
                                >
                                    In
                                </th>


                                <!-- Sold -->

                                <th
                                    class="
                                        min-w-[90px]
                                        px-4
                                        py-3
                                        text-right
                                        text-xs
                                        font-semibold
                                        text-gray-500
                                    "
                                >
                                    Sold
                                </th>


                                <!-- Return -->

                                <th
                                    class="
                                        min-w-[90px]
                                        px-4
                                        py-3
                                        text-right
                                        text-xs
                                        font-semibold
                                        text-gray-500
                                    "
                                >
                                    Return
                                </th>


                                <!-- Closing -->

                                <th
                                    class="
                                        min-w-[105px]
                                        px-5
                                        py-3
                                        text-right
                                        text-xs
                                        font-semibold
                                        text-gray-500
                                    "
                                >
                                    Closing
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <tr
                                v-for="
                                    detail in (
                                        stock.details ?? []
                                    )
                                "
                                :key="detail.key ?? detail.id"
                                class="
                                    border-b
                                    border-gray-50
                                    last:border-b-0
                                "
                            >

                                <!-- Product -->

                                <td
                                    class="
                                        px-5
                                        py-3
                                        align-top
                                    "
                                >

                                    <div
                                        class="
                                            font-medium
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            detail.product_name ??
                                            detail.product?.name ??
                                            '-'
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
                                            detail.reseller_name ??
                                            ''
                                        }}
                                    </div>

                                </td>


                                <!-- Variant -->

                                <td
                                    class="
                                        px-4
                                        py-3
                                        align-top
                                    "
                                >

                                    <div
                                        class="
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            detail.variant_name ??
                                            detail.variant?.name ??
                                            '-'
                                        }}
                                    </div>

                                    <div
                                        class="
                                            mt-0.5
                                            text-xs
                                            text-gray-500
                                        "
                                    >
                                        SKU:
                                        {{
                                            detail.sku ??
                                            detail.variant?.sku ??
                                            '-'
                                        }}
                                    </div>

                                </td>


                                <!-- Unit -->

                                <td
                                    class="
                                        px-4
                                        py-3
                                        align-top
                                        text-gray-700
                                    "
                                >
                                    {{
                                        detail.unit_name ??
                                        detail.unit?.name ??
                                        '-'
                                    }}
                                </td>


                                <!-- Opening -->

                                <td
                                    class="
                                        px-4
                                        py-3
                                        text-right
                                        align-top
                                        font-medium
                                        tabular-nums
                                        text-gray-900
                                    "
                                >
                                    {{
                                        formatQty(
                                            detail.opening
                                        )
                                    }}
                                </td>


                                <!-- In -->

                                <td
                                    class="
                                        px-4
                                        py-3
                                        text-right
                                        align-top
                                        font-medium
                                        tabular-nums
                                        text-emerald-600
                                    "
                                >
                                    {{
                                        formatQty(
                                            detail.in
                                        )
                                    }}
                                </td>


                                <!-- Sold -->

                                <td
                                    class="
                                        px-4
                                        py-3
                                        text-right
                                        align-top
                                        font-medium
                                        tabular-nums
                                        text-red-600
                                    "
                                >
                                    {{
                                        formatQty(
                                            detail.sold
                                        )
                                    }}
                                </td>


                                <!-- Return -->

                                <td
                                    class="
                                        px-4
                                        py-3
                                        text-right
                                        align-top
                                        font-medium
                                        tabular-nums
                                        text-red-600
                                    "
                                >
                                    {{
                                        formatQty(
                                            detail.return
                                        )
                                    }}
                                </td>


                                <!-- Closing -->

                                <td
                                    class="
                                        px-5
                                        py-3
                                        text-right
                                        align-top
                                        font-semibold
                                        tabular-nums
                                        text-gray-900
                                    "
                                >
                                    {{
                                        formatQty(
                                            detail.closing
                                        )
                                    }}
                                </td>

                            </tr>


                            <!-- Empty -->

                            <tr
                                v-if="
                                    (
                                        stock.details ?? []
                                    ).length === 0
                                "
                            >

                                <td
                                    colspan="9"
                                    class="
                                        px-5
                                        py-10
                                        text-center
                                        text-sm
                                        text-gray-500
                                    "
                                >
                                    No stock detail found.
                                </td>

                            </tr>


                            <!-- Total -->

                            <tr
                                v-if="
                                    (
                                        stock.details ?? []
                                    ).length > 0
                                "
                                class="
                                    border-t
                                    border-gray-300
                                "
                            >

                                <td
                                    colspan="3"
                                    class="
                                        px-5
                                        py-3
                                        font-semibold
                                        text-gray-900
                                    "
                                >
                                    Total
                                </td>


                                <td
                                    class="
                                        px-4
                                        py-3
                                        text-right
                                        font-semibold
                                        tabular-nums
                                        text-gray-900
                                    "
                                >
                                    {{
                                        formatQty(
                                            stock.details.reduce(
                                                (
                                                    total,
                                                    item
                                                ) =>
                                                    total +
                                                    Number(
                                                        item.opening ?? 0
                                                    ),
                                                0
                                            )
                                        )
                                    }}
                                </td>


                                <td
                                    class="
                                        px-4
                                        py-3
                                        text-right
                                        font-semibold
                                        tabular-nums
                                        text-emerald-600
                                    "
                                >
                                    {{
                                        formatQty(
                                            stock.details.reduce(
                                                (
                                                    total,
                                                    item
                                                ) =>
                                                    total +
                                                    Number(
                                                        item.in ?? 0
                                                    ),
                                                0
                                            )
                                        )
                                    }}
                                </td>


                                <td
                                    class="
                                        px-4
                                        py-3
                                        text-right
                                        font-semibold
                                        tabular-nums
                                        text-red-600
                                    "
                                >
                                    {{
                                        formatQty(
                                            stock.details.reduce(
                                                (
                                                    total,
                                                    item
                                                ) =>
                                                    total +
                                                    Number(
                                                        item.sold ?? 0
                                                    ),
                                                0
                                            )
                                        )
                                    }}
                                </td>


                                <td
                                    class="
                                        px-4
                                        py-3
                                        text-right
                                        font-semibold
                                        tabular-nums
                                        text-red-600
                                    "
                                >
                                    {{
                                        formatQty(
                                            stock.details.reduce(
                                                (
                                                    total,
                                                    item
                                                ) =>
                                                    total +
                                                    Number(
                                                        item.return ?? 0
                                                    ),
                                                0
                                            )
                                        )
                                    }}
                                </td>


                                <td
                                    class="
                                        px-5
                                        py-3
                                        text-right
                                        font-semibold
                                        tabular-nums
                                        text-gray-900
                                    "
                                >
                                    {{
                                        formatQty(
                                            stock.details.reduce(
                                                (
                                                    total,
                                                    item
                                                ) =>
                                                    total +
                                                    Number(
                                                        item.closing ?? 0
                                                    ),
                                                0
                                            )
                                        )
                                    }}
                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>
        <!-- Receivable -->

        <div
            v-if="activeTab === 'receivable'"
            class="space-y-4"
        >

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

                <!-- Header -->

                <div
                    class="
                        border-b
                        border-gray-100
                        px-5
                        py-4
                    "
                >

                    <div
                        class="
                            text-sm
                            font-semibold
                            text-gray-900
                        "
                    >
                        Receivable Ledger
                    </div>

                    <div
                        class="
                            mt-0.5
                            text-xs
                            text-gray-500
                        "
                    >
                        Reseller receivable activity within the selected period
                    </div>

                </div>


                <!-- Table -->

                <div class="overflow-x-auto">

                    <table class="w-full text-sm">

                        <thead>

                            <tr
                                class="
                                    border-b
                                    border-gray-200
                                "
                            >

                                <th
                                    class="
                                        min-w-[110px]
                                        px-5
                                        py-3
                                        text-left
                                        text-xs
                                        font-semibold
                                        text-gray-500
                                    "
                                >
                                    Date
                                </th>

                                <th
                                    class="
                                        min-w-[160px]
                                        px-4
                                        py-3
                                        text-left
                                        text-xs
                                        font-semibold
                                        text-gray-500
                                    "
                                >
                                    Document
                                </th>

                                <th
                                    class="
                                        min-w-[220px]
                                        px-4
                                        py-3
                                        text-left
                                        text-xs
                                        font-semibold
                                        text-gray-500
                                    "
                                >
                                    Description
                                </th>

                                <th
                                    class="
                                        min-w-[120px]
                                        px-4
                                        py-3
                                        text-right
                                        text-xs
                                        font-semibold
                                        text-gray-500
                                    "
                                >
                                    Debit
                                </th>

                                <th
                                    class="
                                        min-w-[120px]
                                        px-4
                                        py-3
                                        text-right
                                        text-xs
                                        font-semibold
                                        text-gray-500
                                    "
                                >
                                    Credit
                                </th>

                                <th
                                    class="
                                        min-w-[120px]
                                        px-5
                                        py-3
                                        text-right
                                        text-xs
                                        font-semibold
                                        text-gray-500
                                    "
                                >
                                    Balance
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <tr
                                v-for="
                                    item in (
                                        receivable.ledger ?? []
                                    )
                                "
                                :key="item.id"
                                class="
                                    border-b
                                    border-gray-50
                                    last:border-b-0
                                "
                            >

                                <td
                                    class="
                                        whitespace-nowrap
                                        px-5
                                        py-3
                                        text-gray-700
                                    "
                                >
                                    {{
                                        formatDate(
                                            item.date
                                        )
                                    }}
                                </td>


                                <td
                                    class="
                                        px-4
                                        py-3
                                    "
                                >

                                    <div
                                        class="
                                            font-medium
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            item.document ??
                                            item.reference_number ??
                                            '-'
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
                                            item.reference_type ??
                                            '-'
                                        }}
                                    </div>

                                </td>


                                <td
                                    class="
                                        px-4
                                        py-3
                                        text-gray-700
                                    "
                                >
                                    {{
                                        item.description ??
                                        '-'
                                    }}
                                </td>


                                <td
                                    class="
                                        px-4
                                        py-3
                                        text-right
                                        font-medium
                                        tabular-nums
                                        text-gray-900
                                    "
                                >

                                    <template
                                        v-if="
                                            Number(
                                                item.debit ?? 0
                                            ) !== 0
                                        "
                                    >
                                        Rp
                                        {{
                                            formatAmount(
                                                item.debit
                                            )
                                        }}
                                    </template>

                                    <template v-else>
                                        -
                                    </template>

                                </td>


                                <td
                                    class="
                                        px-4
                                        py-3
                                        text-right
                                        font-medium
                                        tabular-nums
                                        text-gray-900
                                    "
                                >

                                    <template
                                        v-if="
                                            Number(
                                                item.credit ?? 0
                                            ) !== 0
                                        "
                                    >
                                        Rp
                                        {{
                                            formatAmount(
                                                item.credit
                                            )
                                        }}
                                    </template>

                                    <template v-else>
                                        -
                                    </template>

                                </td>


                                <td
                                    class="
                                        px-5
                                        py-3
                                        text-right
                                        font-semibold
                                        tabular-nums
                                        text-gray-900
                                    "
                                >
                                    Rp
                                    {{
                                        formatAmount(
                                            item.balance
                                        )
                                    }}
                                </td>

                            </tr>


                            <tr
                                v-if="
                                    (
                                        receivable.ledger ?? []
                                    ).length === 0
                                "
                            >

                                <td
                                    colspan="6"
                                    class="
                                        px-5
                                        py-10
                                        text-center
                                        text-sm
                                        text-gray-500
                                    "
                                >
                                    No receivable activity found.
                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>


        <!-- Settlement -->

        <div
            v-if="activeTab === 'settlement'"
            class="space-y-4"
        >

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

                <!-- Header -->

                <div
                    class="
                        border-b
                        border-gray-100
                        px-5
                        py-4
                    "
                >

                    <div
                        class="
                            text-sm
                            font-semibold
                            text-gray-900
                        "
                    >
                        Settlement Transactions
                    </div>

                    <div
                        class="
                            mt-0.5
                            text-xs
                            text-gray-500
                        "
                    >
                        Posted reseller sales settlements within the selected period
                    </div>

                </div>


                <!-- Table -->

                <div class="overflow-x-auto">

                    <table class="w-full text-sm">

                        <thead>

                            <tr
                                class="
                                    border-b
                                    border-gray-200
                                "
                            >

                                <th
                                    class="
                                        min-w-[110px]
                                        px-5
                                        py-3
                                        text-left
                                        text-xs
                                        font-semibold
                                        text-gray-500
                                    "
                                >
                                    Date
                                </th>

                                <th
                                    class="
                                        min-w-[145px]
                                        px-4
                                        py-3
                                        text-left
                                        text-xs
                                        font-semibold
                                        text-gray-500
                                    "
                                >
                                    Settlement No
                                </th>

                                <th
                                    class="
                                        min-w-[170px]
                                        px-4
                                        py-3
                                        text-left
                                        text-xs
                                        font-semibold
                                        text-gray-500
                                    "
                                >
                                    Period
                                </th>

                                <th
                                    class="
                                        min-w-[100px]
                                        px-4
                                        py-3
                                        text-right
                                        text-xs
                                        font-semibold
                                        text-gray-500
                                    "
                                >
                                    Qty Sold
                                </th>

                                <th
                                    class="
                                        min-w-[135px]
                                        px-4
                                        py-3
                                        text-right
                                        text-xs
                                        font-semibold
                                        text-gray-500
                                    "
                                >
                                    Sales Amount
                                </th>

                                <th
                                    class="
                                        min-w-[125px]
                                        px-4
                                        py-3
                                        text-right
                                        text-xs
                                        font-semibold
                                        text-gray-500
                                    "
                                >
                                    Paid
                                </th>

                                <th
                                    class="
                                        min-w-[135px]
                                        px-5
                                        py-3
                                        text-right
                                        text-xs
                                        font-semibold
                                        text-gray-500
                                    "
                                >
                                    Receivable
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <tr
                                v-for="
                                    settlement in settlements
                                "
                                :key="
                                    settlement.id ??
                                    settlement.settlement_number
                                "
                                class="
                                    border-b
                                    border-gray-50
                                    last:border-b-0
                                "
                            >

                                <td
                                    class="
                                        whitespace-nowrap
                                        px-5
                                        py-3
                                        text-gray-700
                                    "
                                >
                                    {{
                                        formatDate(
                                            settlement.settlement_date ??
                                            settlement.date
                                        )
                                    }}
                                </td>


                                <td
                                    class="
                                        px-4
                                        py-3
                                    "
                                >

                                    <div
                                        class="
                                            font-medium
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            settlement.settlement_number ??
                                            settlement.number ??
                                            '-'
                                        }}
                                    </div>

                                </td>


                                <td
                                    class="
                                        px-4
                                        py-3
                                        text-gray-700
                                    "
                                >

                                    <template
                                        v-if="
                                            settlement.period_from ||
                                            settlement.period_to
                                        "
                                    >

                                        {{
                                            formatDate(
                                                settlement.period_from
                                            )
                                        }}

                                        –

                                        {{
                                            formatDate(
                                                settlement.period_to
                                            )
                                        }}

                                    </template>

                                    <template v-else>
                                        -
                                    </template>

                                </td>


                                <td
                                    class="
                                        px-4
                                        py-3
                                        text-right
                                        font-medium
                                        tabular-nums
                                        text-gray-900
                                    "
                                >
                                    {{
                                        formatQty(
                                            settlement.qty_sold
                                        )
                                    }}
                                </td>


                                <td
                                    class="
                                        px-4
                                        py-3
                                        text-right
                                        font-medium
                                        tabular-nums
                                        text-gray-900
                                    "
                                >
                                    Rp
                                    {{
                                        formatAmount(
                                            settlement.grand_total ??
                                            settlement.sales_amount
                                        )
                                    }}
                                </td>


                                <td
                                    class="
                                        px-4
                                        py-3
                                        text-right
                                        font-medium
                                        tabular-nums
                                        text-blue-600
                                    "
                                >
                                    Rp
                                    {{
                                        formatAmount(
                                            settlement.payment_amount ??
                                            settlement.paid
                                        )
                                    }}
                                </td>


                                <td
                                    class="
                                        px-5
                                        py-3
                                        text-right
                                        font-semibold
                                        tabular-nums
                                        text-gray-900
                                    "
                                >
                                    Rp
                                    {{
                                        formatAmount(
                                            settlement.receivable_amount ??
                                            settlement.receivable
                                        )
                                    }}
                                </td>

                            </tr>


                            <!-- Empty -->

                            <tr
                                v-if="
                                    settlements.length === 0
                                "
                            >

                                <td
                                    colspan="7"
                                    class="
                                        px-5
                                        py-10
                                        text-center
                                        text-sm
                                        text-gray-500
                                    "
                                >
                                    No settlement found.
                                </td>

                            </tr>


                            <!-- Total -->

                            <tr
                                v-if="
                                    settlements.length > 0
                                "
                                class="
                                    border-t
                                    border-gray-300
                                "
                            >

                                <td
                                    colspan="3"
                                    class="
                                        px-5
                                        py-3
                                        font-semibold
                                        text-gray-900
                                    "
                                >
                                    Total
                                </td>


                                <td
                                    class="
                                        px-4
                                        py-3
                                        text-right
                                        font-semibold
                                        tabular-nums
                                        text-gray-900
                                    "
                                >
                                    {{
                                        formatQty(
                                            settlements.reduce(
                                                (
                                                    total,
                                                    item
                                                ) =>
                                                    total +
                                                    Number(
                                                        item.qty_sold ?? 0
                                                    ),
                                                0
                                            )
                                        )
                                    }}
                                </td>


                                <td
                                    class="
                                        px-4
                                        py-3
                                        text-right
                                        font-semibold
                                        tabular-nums
                                        text-gray-900
                                    "
                                >
                                    Rp
                                    {{
                                        formatAmount(
                                            settlements.reduce(
                                                (
                                                    total,
                                                    item
                                                ) =>
                                                    total +
                                                    Number(
                                                        item.grand_total ??
                                                        item.sales_amount ??
                                                        0
                                                    ),
                                                0
                                            )
                                        )
                                    }}
                                </td>


                                <td
                                    class="
                                        px-4
                                        py-3
                                        text-right
                                        font-semibold
                                        tabular-nums
                                        text-blue-600
                                    "
                                >
                                    Rp
                                    {{
                                        formatAmount(
                                            settlements.reduce(
                                                (
                                                    total,
                                                    item
                                                ) =>
                                                    total +
                                                    Number(
                                                        item.payment_amount ??
                                                        item.paid ??
                                                        0
                                                    ),
                                                0
                                            )
                                        )
                                    }}
                                </td>


                                <td
                                    class="
                                        px-5
                                        py-3
                                        text-right
                                        font-semibold
                                        tabular-nums
                                        text-gray-900
                                    "
                                >
                                    Rp
                                    {{
                                        formatAmount(
                                            settlements.reduce(
                                                (
                                                    total,
                                                    item
                                                ) =>
                                                    total +
                                                    Number(
                                                        item.receivable_amount ??
                                                        item.receivable ??
                                                        0
                                                    ),
                                                0
                                            )
                                        )
                                    }}
                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>


        <!-- Payment -->

        <div
            v-if="activeTab === 'payment'"
            class="space-y-4"
        >

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

                <!-- Header -->

                <div
                    class="
                        border-b
                        border-gray-100
                        px-5
                        py-4
                    "
                >

                    <div
                        class="
                            text-sm
                            font-semibold
                            text-gray-900
                        "
                    >
                        Receivable Payments
                    </div>

                    <div
                        class="
                            mt-0.5
                            text-xs
                            text-gray-500
                        "
                    >
                        Posted reseller receivable payments within the selected period
                    </div>

                </div>


                <!-- Table -->

                <div class="overflow-x-auto">

                    <table class="w-full text-sm">

                        <thead>

                            <tr
                                class="
                                    border-b
                                    border-gray-200
                                "
                            >

                                <th
                                    class="
                                        min-w-[110px]
                                        px-5
                                        py-3
                                        text-left
                                        text-xs
                                        font-semibold
                                        text-gray-500
                                    "
                                >
                                    Date
                                </th>

                                <th
                                    class="
                                        min-w-[145px]
                                        px-4
                                        py-3
                                        text-left
                                        text-xs
                                        font-semibold
                                        text-gray-500
                                    "
                                >
                                    Payment No
                                </th>

                                <th
                                    class="
                                        min-w-[160px]
                                        px-4
                                        py-3
                                        text-left
                                        text-xs
                                        font-semibold
                                        text-gray-500
                                    "
                                >
                                    Settlement
                                </th>

                                <th
                                    class="
                                        min-w-[150px]
                                        px-4
                                        py-3
                                        text-left
                                        text-xs
                                        font-semibold
                                        text-gray-500
                                    "
                                >
                                    Payment Method
                                </th>

                                <th
                                    class="
                                        min-w-[140px]
                                        px-5
                                        py-3
                                        text-right
                                        text-xs
                                        font-semibold
                                        text-gray-500
                                    "
                                >
                                    Amount
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <tr
                                v-for="
                                    payment in payments
                                "
                                :key="
                                    payment.id ??
                                    payment.number
                                "
                                class="
                                    border-b
                                    border-gray-50
                                    last:border-b-0
                                "
                            >

                                <td
                                    class="
                                        whitespace-nowrap
                                        px-5
                                        py-3
                                        text-gray-700
                                    "
                                >
                                    {{
                                        formatDate(
                                            payment.payment_date ??
                                            payment.date
                                        )
                                    }}
                                </td>


                                <td
                                    class="
                                        px-4
                                        py-3
                                    "
                                >

                                    <div
                                        class="
                                            font-medium
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            payment.payment_number ??
                                            payment.number ??
                                            '-'
                                        }}
                                    </div>

                                </td>


                                <td
                                    class="
                                        px-4
                                        py-3
                                    "
                                >

                                    <template
                                        v-if="
                                            payment.details?.length
                                        "
                                    >

                                        <div
                                            v-for="
                                                detail in
                                                payment.details
                                            "
                                            :key="
                                                detail.id ??
                                                detail.settlement_header_id
                                            "
                                            class="
                                                text-sm
                                                text-gray-700
                                            "
                                        >
                                            {{
                                                detail.settlement_number ??
                                                detail.settlement?.settlement_number ??
                                                '-'
                                            }}
                                        </div>

                                    </template>

                                    <template v-else>

                                        -

                                    </template>

                                </td>


                                <td
                                    class="
                                        px-4
                                        py-3
                                        text-gray-700
                                    "
                                >
                                    {{
                                        payment.payment_method ??
                                        '-'
                                    }}
                                </td>


                                <td
                                    class="
                                        px-5
                                        py-3
                                        text-right
                                        font-semibold
                                        tabular-nums
                                        text-gray-900
                                    "
                                >
                                    Rp
                                    {{
                                        formatAmount(
                                            payment.amount ??
                                            payment.total_amount
                                        )
                                    }}
                                </td>

                            </tr>


                            <!-- Empty -->

                            <tr
                                v-if="
                                    payments.length === 0
                                "
                            >

                                <td
                                    colspan="5"
                                    class="
                                        px-5
                                        py-10
                                        text-center
                                        text-sm
                                        text-gray-500
                                    "
                                >
                                    No payment found.
                                </td>

                            </tr>


                            <!-- Total -->

                            <tr
                                v-if="
                                    payments.length > 0
                                "
                                class="
                                    border-t
                                    border-gray-300
                                "
                            >

                                <td
                                    colspan="4"
                                    class="
                                        px-5
                                        py-3
                                        font-semibold
                                        text-gray-900
                                    "
                                >
                                    Total
                                </td>

                                <td
                                    class="
                                        px-5
                                        py-3
                                        text-right
                                        font-semibold
                                        tabular-nums
                                        text-gray-900
                                    "
                                >
                                    Rp
                                    {{
                                        formatAmount(
                                            payments.reduce(
                                                (
                                                    total,
                                                    item
                                                ) =>
                                                    total +
                                                    Number(
                                                        item.amount ??
                                                        item.total_amount ??
                                                        0
                                                    ),
                                                0
                                            )
                                        )
                                    }}
                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

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


    /*
    |--------------------------------------------------------------------------
    | Hide non-print elements
    |--------------------------------------------------------------------------
    */

    .print\:hidden {
        display: none !important;
    }


    /*
    |--------------------------------------------------------------------------
    | Summary
    |--------------------------------------------------------------------------
    */

    .print-summary {
        break-inside: avoid;
        page-break-inside: avoid;
    }


    .print-summary-item {
        break-inside: avoid;
        page-break-inside: avoid;
    }


    /*
    |--------------------------------------------------------------------------
    | Tables
    |--------------------------------------------------------------------------
    */

    table {
        width: 100% !important;
    }


    tr {
        break-inside: avoid;
        page-break-inside: avoid;
    }


    /*
    |--------------------------------------------------------------------------
    | General print cleanup
    |--------------------------------------------------------------------------
    */

    body {
        color: #111827 !important;
    }


    .shadow-sm {
        box-shadow: none !important;
    }

}

</style>