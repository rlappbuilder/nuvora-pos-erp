<script setup>

import {
    ref,
    reactive,
    computed,
    watch,
    onMounted,
    onUnmounted,
    toRefs,
} from 'vue'

import {
    router,
} from '@inertiajs/vue3'

import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/Layout/PageHeader.vue'
import Card from '@/Components/Layout/Card.vue'
import StatsCard from '@/Components/Card/StatsCard.vue'
import BaseButton from '@/Components/Button/BaseButton.vue'
import DataTable from '@/Components/Table/DataTable.vue'
import DataTableHead from '@/Components/Table/DataTableHead.vue'
import DataTableBody from '@/Components/Table/DataTableBody.vue'
import DataTableHeaderCell from '@/Components/Table/DataTableHeaderCell.vue'
import DataTableRow from '@/Components/Table/DataTableRow.vue'
import DataTableCell from '@/Components/Table/DataTableCell.vue'
import TablePagination from '@/Components/Table/TablePagination.vue'
import TableEmpty from '@/Components/Table/TableEmpty.vue'
import SearchableSelect from '@/Components/Form/SearchableSelect.vue'

import {
    LoadingOverlay,
} from '@/Components/Feedback'

import {
    formatDate,
    currency,
} from '@/Utils'

import FlatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    generalLedgers: {
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
            debit: 0,
            credit: 0,
        }),
    },

    branches: {
        type: Array,
        default: () => [],
    },

    accountingJournals: {
        type: Array,
        default: () => [],
    },

    fiscalYears: {
        type: Array,
        default: () => [],
    },

    accountingPeriods: {
        type: Array,
        default: () => [],
    },

    chartOfAccounts: {
        type: Array,
        default: () => [],
    },

    filters: {
        type: Object,
        default: () => ({}),
    },

})


const {
    generalLedgers,
    statistics,
} = toRefs(props)


/*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/

const pageTitle = computed(
    () => 'General Ledger'
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

    branch_id:
        props.filters?.branch_id ?? '',

    account_id:
        props.filters?.account_id ?? '',

    accounting_journal_id:
        props.filters?.accounting_journal_id ?? '',

    fiscal_year_id:
        props.filters?.fiscal_year_id ?? '',

    accounting_period_id:
        props.filters?.accounting_period_id ?? '',

    date_from:
        props.filters?.date_from ?? '',

    date_to:
        props.filters?.date_to ?? '',

    per_page:
        props.filters?.per_page ?? 10,

})


let debounceTimer = null


/*
|--------------------------------------------------------------------------
| Load Data
|--------------------------------------------------------------------------
*/

function loadData()
{

    router.get(

        route(
            'general-ledgers.index'
        ),

        filters,

        {

            preserveState: true,

            preserveScroll: true,

            replace: true,

        }

    )

}


/*
|--------------------------------------------------------------------------
| Filter Watchers
|--------------------------------------------------------------------------
*/

watch(

    () => filters.search,

    () => {

        clearTimeout(
            debounceTimer
        )

        debounceTimer =
            setTimeout(() => {

                loadData()

            }, 500)

    }

)


watch(

    () => filters.branch_id,

    () => {

        loadData()

    }

)


watch(

    () => filters.account_id,

    () => {

        loadData()

    }

)


watch(

    () => filters.accounting_journal_id,

    () => {

        loadData()

    }

)


watch(

    () => filters.fiscal_year_id,

    () => {

        loadData()

    }

)


watch(

    () => filters.accounting_period_id,

    () => {

        loadData()

    }

)


watch(

    () => filters.date_from,

    () => {

        loadData()

    }

)


watch(

    () => filters.date_to,

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

            account_id: '',

            accounting_journal_id: '',

            fiscal_year_id: '',

            accounting_period_id: '',

            date_from: '',

            date_to: '',

            per_page: 10,

        }

    )

    dateRange.value = ''

    loadData()

}


/*
|--------------------------------------------------------------------------
| Date Range
|--------------------------------------------------------------------------
*/

const dateRange = ref('')


function formatDateForFilter(date)
{

    const year =
        date.getFullYear()


    const month =
        String(
            date.getMonth() + 1
        ).padStart(
            2,
            '0'
        )


    const day =
        String(
            date.getDate()
        ).padStart(
            2,
            '0'
        )


    return `${year}-${month}-${day}`

}


function handleDateRangeChange(
    selectedDates
)
{

    if (!selectedDates.length) {

        filters.date_from = ''
        filters.date_to = ''

        return

    }


    filters.date_from =
        formatDateForFilter(
            selectedDates[0]
        )


    filters.date_to =
        selectedDates.length > 1

            ? formatDateForFilter(
                selectedDates[1]
            )

            : formatDateForFilter(
                selectedDates[0]
            )

}


/*
|--------------------------------------------------------------------------
| Sorting
|--------------------------------------------------------------------------
*/

const sort = ref(

    props.filters?.sort_by ??
        'entry_date'

)


const direction = ref(

    props.filters?.sort_direction ??
        'asc'

)


function sortBy(column)
{

    if (
        sort.value ===
        column
    ) {

        direction.value =

            direction.value ===
            'asc'

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
            'general-ledgers.index'
        ),

        {

            search:
                filters.search,

            branch_id:
                filters.branch_id,

            account_id:
                filters.account_id,

            accounting_journal_id:
                filters.accounting_journal_id,

            fiscal_year_id:
                filters.fiscal_year_id,

            accounting_period_id:
                filters.accounting_period_id,

            date_from:
                filters.date_from,

            date_to:
                filters.date_to,

            per_page:
                filters.per_page,

            sort_by:
                sort.value,

            sort_direction:
                direction.value,

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
| Filtered Accounting Periods
|--------------------------------------------------------------------------
*/

const filteredAccountingPeriods =
    computed(() => {

        if (
            !filters.fiscal_year_id
        ) {

            return props.accountingPeriods

        }


        return props.accountingPeriods
            .filter(

                period =>
                    Number(
                        period.fiscal_year_id
                    ) ===
                    Number(
                        filters.fiscal_year_id
                    )

            )

    })


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

</script>
<template>

<AppLayout>

    <div class="space-y-6">

        <!-- ===================================================== -->
        <!-- Page Header -->
        <!-- ===================================================== -->

        <PageHeader
            icon="📊"
            :title="pageTitle"
            subtitle="General Ledger account transaction history."
        />


        <!-- ===================================================== -->
        <!-- Statistics -->
        <!-- ===================================================== -->

        <div
            class="
                grid
                grid-cols-1
                gap-4
                md:grid-cols-3
            "
        >

            <StatsCard
                title="Total Entries"
                :value="
                    statistics?.total ?? 0
                "
                icon="📒"
            />

            <StatsCard
                title="Total Debit"
                :value="
                    statistics?.debit ?? 0
                "
                icon="↘️"
                format="currency"
            />

            <StatsCard
                title="Total Credit"
                :value="
                    statistics?.credit ?? 0
                "
                icon="↗️"
                format="currency"
            />

        </div>


        <!-- ===================================================== -->
        <!-- List Card -->
        <!-- ===================================================== -->

        <Card>


            <!-- ================================================= -->
            <!-- Toolbar -->
            <!-- ================================================= -->

            <div>


                <!-- TOP ROW -->

                <div
                    class="
                        flex
                        flex-col
                        gap-3
                        lg:flex-row
                        lg:items-center
                        lg:justify-between
                    "
                >


                    <!-- Search + Date -->

                    <div
                        class="
                            flex
                            flex-1
                            flex-col
                            gap-3
                            lg:flex-row
                            lg:items-center
                        "
                    >

                        <input
                            v-model="
                                filters.search
                            "
                            type="text"
                            placeholder="Search account, journal, reference..."
                            class="
                                w-full
                                rounded-xl
                                border
                                border-gray-300
                                px-4
                                py-2.5
                                lg:w-80
                            "
                        />


                        <FlatPickr
                            v-model="
                                dateRange
                            "
                            :config="{
                                mode: 'range',
                                dateFormat: 'Y-m-d',
                            }"
                            placeholder="Date Range"
                            class="
                                w-full
                                rounded-xl
                                border
                                border-gray-300
                                px-4
                                py-2.5
                                text-sm
                                lg:w-56
                            "
                            @on-change="
                                handleDateRangeChange
                            "
                        />

                    </div>


                    <!-- Actions -->

                    <div
                        class="
                            flex
                            w-full
                            lg:w-auto
                        "
                    >

                        <BaseButton
                            variant="secondary"
                            class="
                                w-full
                                shrink-0
                                whitespace-nowrap
                                lg:w-auto
                            "
                            @click="refresh"
                        >
                            Refresh
                        </BaseButton>

                    </div>

                </div>


                <!-- ================================================= -->
                <!-- FILTER ROW -->
                <!-- ================================================= -->

                <div
                    class="
                        mt-3
                        flex
                        flex-col
                        gap-3
                        lg:flex-row
                        lg:items-center
                    "
                >


                    <!-- Branch -->

                    <SearchableSelect
                        v-model="
                            filters.branch_id
                        "
                        :options="
                            branches
                        "
                        label="label"
                        value-key="id"
                        placeholder="All Branches"
                        class="w-full lg:w-48"
                    />


                    <!-- Account -->

                    <SearchableSelect
                        v-model="
                            filters.account_id
                        "
                        :options="
                            chartOfAccounts
                        "
                        label="label"
                        value-key="id"
                        placeholder="All Accounts"
                        class="w-full lg:w-56"
                    />


                    <!-- Accounting Journal -->

                    <SearchableSelect
                        v-model="
                            filters.accounting_journal_id
                        "
                        :options="
                            accountingJournals
                        "
                        label="label"
                        value-key="id"
                        placeholder="All Journals"
                        class="w-full lg:w-52"
                    />


                    <!-- Fiscal Year -->

                    <SearchableSelect
                        v-model="
                            filters.fiscal_year_id
                        "
                        :options="
                            fiscalYears
                        "
                        label="label"
                        value-key="id"
                        placeholder="All Fiscal Years"
                        class="w-full lg:w-48"
                    />


                    <!-- Period -->

                    <SearchableSelect
                        v-model="
                            filters.accounting_period_id
                        "
                        :options="
                            filteredAccountingPeriods
                        "
                        label="label"
                        value-key="id"
                        placeholder="All Periods"
                        class="w-full lg:w-48"
                    />

                </div>

            </div>


            <!-- ================================================= -->
            <!-- Table -->
            <!-- ================================================= -->

            <div class="mt-6">


                <LoadingOverlay
                    :show="loading"
                    text="Loading General Ledger..."
                />


                <DataTable
                    v-if="
                        generalLedgers?.data?.length
                    "
                    sticky-header
                    max-height="650px"
                >

                    <DataTableHead sticky>


                        <!-- Date -->

                        <DataTableHeaderCell
                            sortable
                            column="entry_date"
                            :sort="sort"
                            :direction="direction"
                            @sort="sortBy"
                            width="130px"
                        >
                            Date
                        </DataTableHeaderCell>


                        <!-- Journal Entry -->

                        <DataTableHeaderCell
                            sortable
                            column="id"
                            :sort="sort"
                            :direction="direction"
                            @sort="sortBy"
                            width="180px"
                        >
                            Journal Entry
                        </DataTableHeaderCell>


                        <!-- Account -->

                        <DataTableHeaderCell
                            width="220px"
                        >
                            Account
                        </DataTableHeaderCell>


                        <!-- Branch -->

                        <DataTableHeaderCell
                            width="160px"
                        >
                            Branch
                        </DataTableHeaderCell>


                        <!-- Reference -->

                        <DataTableHeaderCell
                            width="160px"
                        >
                            Reference
                        </DataTableHeaderCell>


                        <!-- Description -->

                        <DataTableHeaderCell
                            width="250px"
                        >
                            Description
                        </DataTableHeaderCell>


                        <!-- Debit -->

                        <DataTableHeaderCell
                            sortable
                            column="debit"
                            :sort="sort"
                            :direction="direction"
                            @sort="sortBy"
                            width="170px"
                            align="right"
                        >
                            Debit
                        </DataTableHeaderCell>


                        <!-- Credit -->

                        <DataTableHeaderCell
                            sortable
                            column="credit"
                            :sort="sort"
                            :direction="direction"
                            @sort="sortBy"
                            width="170px"
                            align="right"
                        >
                            Credit
                        </DataTableHeaderCell>


                        <!-- Running Balance -->

                        <DataTableHeaderCell
                            sortable
                            column="running_balance"
                            :sort="sort"
                            :direction="direction"
                            @sort="sortBy"
                            width="190px"
                            align="right"
                        >
                            Running Balance
                        </DataTableHeaderCell>

                    </DataTableHead>


                    <DataTableBody>

                        <DataTableRow
                            v-for="
                                item in generalLedgers.data
                            "
                            :key="item.id"
                        >


                            <!-- Date -->

                            <DataTableCell>

                                <span
                                    class="
                                        whitespace-nowrap
                                        text-sm
                                        text-gray-700
                                    "
                                >
                                    {{
                                        formatDate(
                                            item.entry_date
                                        )
                                    }}
                                </span>

                            </DataTableCell>


                            <!-- Journal Entry -->

                            <DataTableCell>

                                <div
                                    class="
                                        font-medium
                                        text-gray-900
                                    "
                                >
                                    {{
                                        item.journal_entry?.code
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
                                        item.accounting_journal?.code
                                        ?? '-'
                                    }}
                                </div>

                            </DataTableCell>


                            <!-- Account -->

                            <DataTableCell>

                                <div
                                    class="
                                        font-semibold
                                        text-gray-900
                                    "
                                >
                                    {{
                                        item.account?.code
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
                                        item.account?.name
                                        ?? '-'
                                    }}
                                </div>

                            </DataTableCell>


                            <!-- Branch -->

                            <DataTableCell>

                                {{
                                    item.branch?.name
                                    ?? '-'
                                }}

                            </DataTableCell>


                            <!-- Reference -->

                            <DataTableCell>

                                {{
                                    item.reference
                                    ?? '-'
                                }}

                            </DataTableCell>


                            <!-- Description -->

                            <DataTableCell>

                                <span
                                    class="
                                        text-sm
                                        text-gray-700
                                    "
                                >
                                    {{
                                        item.description
                                        ?? '-'
                                    }}
                                </span>

                            </DataTableCell>


                            <!-- Debit -->

                            <DataTableCell
                                align="right"
                            >

                                <span
                                    class="
                                        whitespace-nowrap
                                        text-sm
                                        font-medium
                                        text-gray-900
                                    "
                                >
                                    {{
                                        currency(
                                            item.debit ?? 0
                                        )
                                    }}
                                </span>

                            </DataTableCell>


                            <!-- Credit -->

                            <DataTableCell
                                align="right"
                            >

                                <span
                                    class="
                                        whitespace-nowrap
                                        text-sm
                                        font-medium
                                        text-gray-900
                                    "
                                >
                                    {{
                                        currency(
                                            item.credit ?? 0
                                        )
                                    }}
                                </span>

                            </DataTableCell>


                            <!-- Running Balance -->

                            <DataTableCell
                                align="right"
                            >

                                <span
                                    class="
                                        whitespace-nowrap
                                        text-sm
                                        font-semibold
                                        text-gray-900
                                    "
                                >
                                    {{
                                        currency(
                                            item.running_balance
                                            ?? 0
                                        )
                                    }}
                                </span>

                            </DataTableCell>

                        </DataTableRow>

                    </DataTableBody>

                </DataTable>


                <!-- ================================================= -->
                <!-- Empty -->
                <!-- ================================================= -->

                <TableEmpty
                    v-else
                    icon="📊"
                    title="No General Ledger Entries Found"
                    description="There are no posted General Ledger transactions matching the selected filters."
                />

            </div>


            <!-- ================================================= -->
            <!-- Pagination -->
            <!-- ================================================= -->

            <div class="mt-6">

                <TablePagination
                    :data="
                        generalLedgers
                    "
                    label="General Ledger Entry"
                />

            </div>

        </Card>

    </div>

</AppLayout>

</template>