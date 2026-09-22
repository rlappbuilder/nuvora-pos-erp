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

import { router } from '@inertiajs/vue3'

import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/Layout/PageHeader.vue'
import Card from '@/Components/Layout/Card.vue'
import BaseButton from '@/Components/Button/BaseButton.vue'
import SearchableSelect from '@/Components/Form/SearchableSelect.vue'
import { LoadingOverlay } from '@/Components/Feedback'
import { currency } from '@/Utils'

import FlatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    title: {
        type: String,
        default: 'Income Statement',
    },

    report: {
        type: Object,
        default: () => ({
            revenue: [],
            cost_of_goods_sold: [],
            operating_expenses: [],
            other_income: [],
            other_expenses: [],
            gross_profit: 0,
            operating_income: 0,
            net_income: 0,
        }),
    },

    statistics: {
        type: Object,
        default: () => ({
            total_revenue: 0,
            total_cost_of_goods_sold: 0,
            gross_profit: 0,
            total_operating_expenses: 0,
            operating_income: 0,
            total_other_income: 0,
            total_other_expenses: 0,
            net_income: 0,
        }),
    },

    branches: {
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

    filters: {
        type: Object,
        default: () => ({}),
    },

})


const {
    report,
    statistics,
} = toRefs(props)


/*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/

const pageTitle =
    computed(
        () =>
            props.title
            || 'Income Statement'
    )


/*
|--------------------------------------------------------------------------
| Loading
|--------------------------------------------------------------------------
*/

const loading =
    ref(false)

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
| Default Dates
|--------------------------------------------------------------------------
*/

const getDefaultDateFrom = () => {

    const now = new Date()

    return [
        now.getFullYear(),
        String(
            now.getMonth() + 1
        ).padStart(2, '0'),
        '01',
    ].join('-')

}


const getDefaultDateTo = () => {

    const now = new Date()

    return [
        now.getFullYear(),
        String(
            now.getMonth() + 1
        ).padStart(2, '0'),
        String(
            now.getDate()
        ).padStart(2, '0'),
    ].join('-')

}


/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

const filters =
    reactive({

        branch_id:
            props.filters?.branch_id
            ?? '',

        fiscal_year_id:
            props.filters?.fiscal_year_id
            ?? '',

        accounting_period_id:
            props.filters?.accounting_period_id
            ?? '',

        date_from:
            props.filters?.date_from
            || getDefaultDateFrom(),

        date_to:
            props.filters?.date_to
            || getDefaultDateTo(),

    })


/*
|--------------------------------------------------------------------------
| Show Zero Account
|--------------------------------------------------------------------------
*/

const showZeroAccount =
    ref(false)


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
| Fiscal Year Change
|--------------------------------------------------------------------------
*/

const handleFiscalYearChange = () => {

    const exists =
        filteredAccountingPeriods.value.some(
            period =>
                Number(period.id) ===
                Number(
                    filters.accounting_period_id
                )
        )

    if (
        filters.accounting_period_id
        &&
        !exists
    ) {

        filters.accounting_period_id = ''

    }

}


/*
|--------------------------------------------------------------------------
| Account Visibility
|--------------------------------------------------------------------------
*/

const hasBalance = (account) => {

    if (
        showZeroAccount.value
    ) {

        return true

    }

    return Math.abs(
        Number(
            account.balance
            ?? 0
        )
    ) >= 0.005

}


/*
|--------------------------------------------------------------------------
| Report Groups
|--------------------------------------------------------------------------
*/

function groupAccounts(
    accounts = []
) {

    const filteredAccounts =
        accounts.filter(
            hasBalance
        )

    const grouped = {}


    filteredAccounts.forEach(
        account => {

            const typeName =
                account.account_type
                || 'Uncategorized'

            const categoryName =
                account.account_category
                || 'Uncategorized'


            if (
                !grouped[typeName]
            ) {

                grouped[typeName] = {}

            }


            if (
                !grouped[typeName][categoryName]
            ) {

                grouped[typeName][categoryName] = []

            }


            grouped[typeName][categoryName]
                .push(account)

        }
    )


    return Object.entries(
        grouped
    ).map(
        ([type, categories]) => ({

            type,

            categories:
                Object.entries(
                    categories
                ).map(
                    ([category, accounts]) => ({

                        category,

                        accounts,

                    })
                ),

        })
    )

}


/*
|--------------------------------------------------------------------------
| Computed Groups
|--------------------------------------------------------------------------
*/

const revenueGroups =
    computed(() =>
        groupAccounts(
            report.value?.revenue
        )
    )


const cogsGroups =
    computed(() =>
        groupAccounts(
            report.value?.cost_of_goods_sold
        )
    )


const operatingExpenseGroups =
    computed(() =>
        groupAccounts(
            report.value?.operating_expenses
        )
    )


const otherIncomeGroups =
    computed(() =>
        groupAccounts(
            report.value?.other_income
        )
    )


const otherExpenseGroups =
    computed(() =>
        groupAccounts(
            report.value?.other_expenses
        )
    )


/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function hasAccounts(
    groups
) {

    return groups.some(
        group =>
            group.categories.some(
                category =>
                    category.accounts.length
            )
    )

}


function categoryTotal(
    category
) {

    return category.accounts
        .reduce(
            (
                total,
                account
            ) =>
                total +
                Number(
                    account.balance
                    ?? 0
                ),
            0
        )

}


function typeTotal(
    type
) {

    return type.categories
        .reduce(
            (
                total,
                category
            ) =>
                total +
                categoryTotal(
                    category
                ),
            0
        )

}


/*
|--------------------------------------------------------------------------
| Date Labels
|--------------------------------------------------------------------------
*/

const formatDateLabel = (
    value
) => {

    if (!value) {
        return ''
    }

    const date =
        new Date(
            `${value}T00:00:00`
        )

    if (
        Number.isNaN(
            date.getTime()
        )
    ) {

        return value

    }

    return new Intl.DateTimeFormat(
        'en-GB',
        {
            day: '2-digit',
            month: 'long',
            year: 'numeric',
        }
    ).format(date)

}


const dateFromLabel =
    computed(() =>
        formatDateLabel(
            filters.date_from
        )
    )


const dateToLabel =
    computed(() =>
        formatDateLabel(
            filters.date_to
        )
    )


/*
|--------------------------------------------------------------------------
| Selected Filters
|--------------------------------------------------------------------------
*/

const selectedBranch =
    computed(() => {

        return props.branches.find(
            branch =>
                String(branch.id) ===
                String(filters.branch_id)
        )

    })


const selectedFiscalYear =
    computed(() => {

        return props.fiscalYears.find(
            year =>
                String(year.id) ===
                String(filters.fiscal_year_id)
        )

    })


const selectedPeriod =
    computed(() => {

        return props.accountingPeriods.find(
            period =>
                String(period.id) ===
                String(filters.accounting_period_id)
        )

    })


/*
|--------------------------------------------------------------------------
| Apply Filters
|--------------------------------------------------------------------------
*/

const applyFilters = () => {

    loading.value = true

    router.get(
        route(
            'income-statement.index'
        ),
        {

            branch_id:
                filters.branch_id
                || undefined,

            fiscal_year_id:
                filters.fiscal_year_id
                || undefined,

            accounting_period_id:
                filters.accounting_period_id
                || undefined,

            date_from:
                filters.date_from
                || undefined,

            date_to:
                filters.date_to
                || undefined,

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


/*
|--------------------------------------------------------------------------
| Reset Filters
|--------------------------------------------------------------------------
*/

const resetFilters = () => {

    filters.branch_id = ''

    filters.fiscal_year_id = ''

    filters.accounting_period_id = ''

    filters.date_from =
        getDefaultDateFrom()

    filters.date_to =
        getDefaultDateTo()

    applyFilters()

}


/*
|--------------------------------------------------------------------------
| Print
|--------------------------------------------------------------------------
*/

const printReport = () => {

    const params =
        new URLSearchParams()


    if (
        filters.branch_id
    ) {

        params.set(
            'branch_id',
            filters.branch_id
        )

    }


    if (
        filters.fiscal_year_id
    ) {

        params.set(
            'fiscal_year_id',
            filters.fiscal_year_id
        )

    }


    if (
        filters.accounting_period_id
    ) {

        params.set(
            'accounting_period_id',
            filters.accounting_period_id
        )

    }


    if (
        filters.date_from
    ) {

        params.set(
            'date_from',
            filters.date_from
        )

    }


    if (
        filters.date_to
    ) {

        params.set(
            'date_to',
            filters.date_to
        )

    }


    const url =
        `${route(
            'income-statement.print'
        )}?${params.toString()}`


    window.open(
        url,
        '_blank'
    )

}


/*
|--------------------------------------------------------------------------
| Watchers
|--------------------------------------------------------------------------
*/

watch(
    () =>
        filters.fiscal_year_id,
    () => {

        handleFiscalYearChange()

    }
)


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

    <div
        class="
            space-y-6
            p-6
        "
    >

        <!-- ========================================================= -->
        <!-- PAGE HEADER -->
        <!-- ========================================================= -->

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

            <div>

                <h1
                    class="
                        text-2xl
                        font-semibold
                        tracking-tight
                        text-gray-900
                        dark:text-white
                    "
                >
                    {{ pageTitle }}
                </h1>

                <p
                    class="
                        mt-1
                        text-sm
                        text-gray-500
                        dark:text-gray-400
                    "
                >
                    Statement of Profit or Loss
                </p>

                <p
                    v-if="
                        dateFromLabel &&
                        dateToLabel
                    "
                    class="
                        mt-1
                        text-sm
                        text-gray-500
                        dark:text-gray-400
                    "
                >
                    For the period
                    {{ dateFromLabel }}
                    to
                    {{ dateToLabel }}
                </p>

            </div>


            <div
                class="
                    flex
                    items-center
                    gap-2
                "
            >

                <button
                    type="button"
                    class="
                        inline-flex
                        items-center
                        gap-2
                        rounded-lg
                        border
                        border-gray-300
                        bg-white
                        px-4
                        py-2
                        text-sm
                        font-medium
                        text-gray-700
                        shadow-sm
                        transition
                        hover:bg-gray-50
                        dark:border-gray-700
                        dark:bg-gray-800
                        dark:text-gray-200
                        dark:hover:bg-gray-700
                    "
                    @click="
                        printReport
                    "
                >

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        class="h-4 w-4"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M6.75 9V4.5h10.5V9"
                        />

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M6 18H4.5A1.5 1.5 0 0 1 3 16.5v-5A1.5 1.5 0 0 1 4.5 10h15a1.5 1.5 0 0 1 1.5 1.5v5a1.5 1.5 0 0 1-1.5 1.5H18"
                        />

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M6.75 14.25h10.5v5.25H6.75z"
                        />

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M17.25 12.75h.008v.008h-.008z"
                        />
                    </svg>

                    Print

                </button>

            </div>

        </div>


<!-- ========================================================= -->
<!-- FILTER CARD -->
<!-- ========================================================= -->

<Card>

    <!-- ===================================================== -->
    <!-- FILTER HEADER -->
    <!-- ===================================================== -->

    <div
        class="
            mb-4
            flex
            flex-col
            gap-3
            lg:flex-row
            lg:items-center
            lg:justify-between
        "
    >

        <div>

            <h2
                class="
                    text-sm
                    font-semibold
                    text-gray-900
                    dark:text-white
                "
            >
                Report Filters
            </h2>

            <p
                class="
                    mt-1
                    text-xs
                    text-gray-500
                    dark:text-gray-400
                "
            >
                Select the reporting scope and date.
            </p>

        </div>


        <!-- Show Zero Account -->

        <label
            class="
                flex
                cursor-pointer
                items-center
                gap-2
                text-sm
                text-gray-600
                dark:text-gray-300
            "
        >

            <input
                v-model="showZeroAccount"
                type="checkbox"
                class="
                    rounded
                    border-gray-300
                    text-indigo-600
                    focus:ring-indigo-500
                "
            />

            <span>
                Show Zero Account
            </span>

        </label>

    </div>


    <!-- ===================================================== -->
    <!-- ROW 1 : DATE FROM | DATE TO | RESET | APPLY -->
    <!-- ===================================================== -->

    <div
        class="
            grid
            grid-cols-1
            gap-4
            lg:grid-cols-4
        "
    >

        <!-- ================================================= -->
        <!-- DATE FROM -->
        <!-- ================================================= -->

        <div class="w-full">

            <label
                class="
                    mb-1.5
                    block
                    text-xs
                    font-medium
                    text-gray-600
                    dark:text-gray-300
                "
            >
                Date From
            </label>

            <FlatPickr
                v-model="filters.date_from"
                :config="{
                    dateFormat: 'Y-m-d',
                    allowInput: true,
                }"
                class="
                    w-full
                    rounded-lg
                    border
                    border-gray-300
                    bg-white
                    px-3
                    py-2.5
                    text-sm
                    text-gray-700
                    outline-none
                    transition
                    focus:border-indigo-500
                    focus:ring-2
                    focus:ring-indigo-500/20
                    dark:border-gray-600
                    dark:bg-gray-900
                    dark:text-gray-200
                "
            />

        </div>


        <!-- ================================================= -->
        <!-- DATE TO -->
        <!-- ================================================= -->

        <div class="w-full">

            <label
                class="
                    mb-1.5
                    block
                    text-xs
                    font-medium
                    text-gray-600
                    dark:text-gray-300
                "
            >
                Date To
            </label>

            <FlatPickr
                v-model="filters.date_to"
                :config="{
                    dateFormat: 'Y-m-d',
                    allowInput: true,
                }"
                class="
                    w-full
                    rounded-lg
                    border
                    border-gray-300
                    bg-white
                    px-3
                    py-2.5
                    text-sm
                    text-gray-700
                    outline-none
                    transition
                    focus:border-indigo-500
                    focus:ring-2
                    focus:ring-indigo-500/20
                    dark:border-gray-600
                    dark:bg-gray-900
                    dark:text-gray-200
                "
            />

        </div>


        <!-- ================================================= -->
        <!-- RESET -->
        <!-- ================================================= -->

        <div class="w-full">

            <label
                class="
                    mb-1.5
                    block
                    text-xs
                    font-medium
                    text-transparent
                "
            >
                Action
            </label>

            <BaseButton
                variant="secondary"
                class="w-full"
                :disabled="loading"
                @click="resetFilters"
            >
                Reset
            </BaseButton>

        </div>


        <!-- ================================================= -->
        <!-- APPLY -->
        <!-- ================================================= -->

        <div class="w-full">

            <label
                class="
                    mb-1.5
                    block
                    text-xs
                    font-medium
                    text-transparent
                "
            >
                Action
            </label>

            <BaseButton
                variant="primary"
                class="w-full"
                :disabled="loading"
                @click="applyFilters"
            >
                {{
                    loading
                        ? 'Loading...'
                        : 'Apply'
                }}
            </BaseButton>

        </div>

    </div>


    <!-- ===================================================== -->
    <!-- ROW 2 : BRANCH | FISCAL YEAR | PERIOD -->
    <!-- ===================================================== -->

    <div
        class="
            mt-4
            grid
            grid-cols-1
            gap-4
            lg:grid-cols-3
        "
    >

        <!-- ================================================= -->
        <!-- BRANCH -->
        <!-- ================================================= -->

        <div class="w-full">

            <label
                class="
                    mb-1.5
                    block
                    text-xs
                    font-medium
                    text-gray-600
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
                class="w-full"
            />

        </div>


        <!-- ================================================= -->
        <!-- FISCAL YEAR -->
        <!-- ================================================= -->

        <div class="w-full">

            <label
                class="
                    mb-1.5
                    block
                    text-xs
                    font-medium
                    text-gray-600
                    dark:text-gray-300
                "
            >
                Fiscal Year
            </label>

            <SearchableSelect
                v-model="filters.fiscal_year_id"
                :options="fiscalYears"
                label="label"
                value-key="id"
                placeholder="All Fiscal Years"
                class="w-full"
                @update:model-value="
                    handleFiscalYearChange
                "
            />

        </div>


        <!-- ================================================= -->
        <!-- PERIOD -->
        <!-- ================================================= -->

        <div class="w-full">

            <label
                class="
                    mb-1.5
                    block
                    text-xs
                    font-medium
                    text-gray-600
                    dark:text-gray-300
                "
            >
                Period
            </label>

            <SearchableSelect
                v-model="filters.accounting_period_id"
                :options="filteredAccountingPeriods"
                label="label"
                value-key="id"
                placeholder="All Periods"
                class="w-full"
            />

        </div>

    </div>

</Card>


        <!-- ========================================================= -->
        <!-- REPORT -->
        <!-- ========================================================= -->

        <Card>

            <LoadingOverlay
                :show="loading"
                text="Loading Income Statement..."
            />


            <!-- ===================================================== -->
            <!-- REPORT HEADER -->
            <!-- ===================================================== -->

            <div
                class="
                    border-b
                    border-gray-200
                    pb-6
                    dark:border-gray-700
                "
            >

                <div
                    class="
                        flex
                        flex-col
                        gap-2
                        md:flex-row
                        md:items-end
                        md:justify-between
                    "
                >

                    <div>

                        <div
                            class="
                                text-lg
                                font-semibold
                                text-gray-900
                                dark:text-white
                            "
                        >
                            {{ pageTitle }}
                        </div>

                        <div
                            class="
                                mt-1
                                text-sm
                                text-gray-500
                                dark:text-gray-400
                            "
                        >
                            Statement of Profit or Loss
                        </div>

                    </div>


                    <div
                        class="
                            text-left
                            text-sm
                            md:text-right
                        "
                    >

                        <div
                            v-if="
                                selectedBranch
                            "
                            class="
                                font-medium
                                text-gray-800
                                dark:text-gray-200
                            "
                        >
                            {{ selectedBranch.label }}
                        </div>

                        <div
                            v-if="
                                selectedFiscalYear
                            "
                            class="
                                text-gray-500
                                dark:text-gray-400
                            "
                        >
                            Fiscal Year
                            {{
                                selectedFiscalYear.label
                            }}
                        </div>

                        <div
                            v-if="
                                selectedPeriod
                            "
                            class="
                                text-gray-500
                                dark:text-gray-400
                            "
                        >
                            {{
                                selectedPeriod.label
                            }}
                        </div>

                        <div
                            v-if="
                                dateFromLabel &&
                                dateToLabel
                            "
                            class="
                                text-gray-500
                                dark:text-gray-400
                            "
                        >
                            For the period
                            {{ dateFromLabel }}
                            to
                            {{ dateToLabel }}
                        </div>

                    </div>

                </div>

            </div>


            <!-- ===================================================== -->
            <!-- REVENUE -->
            <!-- ===================================================== -->

            <div class="mt-8">

                <div
                    class="
                        flex
                        items-center
                        justify-between
                        border-b
                        border-gray-300
                        pb-2
                        dark:border-gray-600
                    "
                >

                    <h2
                        class="
                            text-sm
                            font-bold
                            uppercase
                            tracking-wide
                            text-gray-900
                            dark:text-white
                        "
                    >
                        Revenue
                    </h2>

                    <span
                        class="
                            text-sm
                            font-bold
                            text-gray-900
                            dark:text-white
                        "
                    >
                        {{
                            currency(
                                statistics?.total_revenue
                                ?? 0
                            )
                        }}
                    </span>

                </div>


                <div
                    v-if="
                        hasAccounts(
                            revenueGroups
                        )
                    "
                    class="mt-4"
                >

                    <div
                        v-for="
                            type in revenueGroups
                        "
                        :key="
                            `revenue-type-${type.type}`
                        "
                        class="mb-5"
                    >

                        <div
                            class="
                                mb-2
                                text-sm
                                font-semibold
                                text-gray-700
                                dark:text-gray-300
                            "
                        >
                            {{ type.type }}
                        </div>


                        <div
                            v-for="
                                category in type.categories
                            "
                            :key="
                                `revenue-category-${category.category}`
                            "
                            class="mb-3"
                        >

                            <div
                                class="
                                    mb-1
                                    text-xs
                                    font-medium
                                    uppercase
                                    tracking-wide
                                    text-gray-500
                                    dark:text-gray-400
                                "
                            >
                                {{ category.category }}
                            </div>


                            <div class="space-y-1">

                                <div
                                    v-for="
                                        account in category.accounts
                                    "
                                    :key="
                                        account.id
                                    "
                                    class="
                                        flex
                                        items-center
                                        justify-between
                                        gap-4
                                        rounded-md
                                        px-2
                                        py-1.5
                                        text-sm
                                        hover:bg-gray-50
                                        dark:hover:bg-gray-700/40
                                    "
                                >

                                    <div
                                        class="
                                            min-w-0
                                            truncate
                                            pl-4
                                            text-gray-600
                                            dark:text-gray-300
                                        "
                                    >

                                        <span
                                            class="
                                                mr-2
                                                font-mono
                                                text-xs
                                                text-gray-400
                                                dark:text-gray-500
                                            "
                                        >
                                            {{ account.code }}
                                        </span>

                                        {{ account.name }}

                                    </div>


                                    <span
                                        class="
                                            shrink-0
                                            font-mono
                                            tabular-nums
                                            text-gray-800
                                            dark:text-gray-200
                                        "
                                    >
                                        {{
                                            currency(
                                                account.balance
                                                ?? 0
                                            )
                                        }}
                                    </span>

                                </div>

                            </div>


                            <div
                                class="
                                    mt-2
                                    flex
                                    items-center
                                    justify-between
                                    border-t
                                    border-gray-100
                                    pt-2
                                    dark:border-gray-700
                                "
                            >

                                <span
                                    class="
                                        pl-2
                                        text-xs
                                        font-medium
                                        text-gray-500
                                        dark:text-gray-400
                                    "
                                >
                                    Total
                                    {{ category.category }}
                                </span>

                                <span
                                    class="
                                        font-mono
                                        text-sm
                                        font-semibold
                                        tabular-nums
                                        text-gray-700
                                        dark:text-gray-200
                                    "
                                >
                                    {{
                                        currency(
                                            categoryTotal(
                                                category
                                            )
                                        )
                                    }}
                                </span>

                            </div>

                        </div>


                        <div
                            class="
                                flex
                                justify-between
                                border-t
                                border-gray-200
                                pt-2
                                text-sm
                                font-bold
                                text-gray-800
                                dark:border-gray-700
                                dark:text-gray-200
                            "
                        >

                            <span>
                                Total {{ type.type }}
                            </span>

                            <span>
                                {{
                                    currency(
                                        typeTotal(
                                            type
                                        )
                                    )
                                }}
                            </span>

                        </div>

                    </div>

                </div>


                <div
                    v-else
                    class="
                        py-4
                        text-sm
                        text-gray-500
                        dark:text-gray-400
                    "
                >
                    No revenue recorded.
                </div>

            </div>


            <!-- ===================================================== -->
            <!-- COGS -->
            <!-- ===================================================== -->

            <div class="mt-10">

                <div
                    class="
                        flex
                        items-center
                        justify-between
                        border-b
                        border-gray-300
                        pb-2
                        dark:border-gray-600
                    "
                >

                    <h2
                        class="
                            text-sm
                            font-bold
                            uppercase
                            tracking-wide
                            text-gray-900
                            dark:text-white
                        "
                    >
                        Cost of Goods Sold
                    </h2>

                    <span
                        class="
                            text-sm
                            font-bold
                            text-gray-900
                            dark:text-white
                        "
                    >
                        {{
                            currency(
                                statistics?.total_cost_of_goods_sold
                                ?? 0
                            )
                        }}
                    </span>

                </div>


                <div
                    v-if="
                        hasAccounts(
                            cogsGroups
                        )
                    "
                    class="mt-4"
                >

                    <div
                        v-for="
                            type in cogsGroups
                        "
                        :key="
                            `cogs-type-${type.type}`
                        "
                        class="mb-5"
                    >

                        <div
                            class="
                                mb-2
                                text-sm
                                font-semibold
                                text-gray-700
                                dark:text-gray-300
                            "
                        >
                            {{ type.type }}
                        </div>


                        <div
                            v-for="
                                category in type.categories
                            "
                            :key="
                                `cogs-category-${category.category}`
                            "
                            class="mb-3"
                        >

                            <div
                                class="
                                    mb-1
                                    text-xs
                                    font-medium
                                    uppercase
                                    tracking-wide
                                    text-gray-500
                                    dark:text-gray-400
                                "
                            >
                                {{ category.category }}
                            </div>


                            <div class="space-y-1">

                                <div
                                    v-for="
                                        account in category.accounts
                                    "
                                    :key="
                                        account.id
                                    "
                                    class="
                                        flex
                                        items-center
                                        justify-between
                                        gap-4
                                        rounded-md
                                        px-2
                                        py-1.5
                                        text-sm
                                        hover:bg-gray-50
                                        dark:hover:bg-gray-700/40
                                    "
                                >

                                    <div
                                        class="
                                            min-w-0
                                            truncate
                                            pl-4
                                            text-gray-600
                                            dark:text-gray-300
                                        "
                                    >

                                        <span
                                            class="
                                                mr-2
                                                font-mono
                                                text-xs
                                                text-gray-400
                                                dark:text-gray-500
                                            "
                                        >
                                            {{ account.code }}
                                        </span>

                                        {{ account.name }}

                                    </div>


                                    <span
                                        class="
                                            shrink-0
                                            font-mono
                                            tabular-nums
                                            text-gray-800
                                            dark:text-gray-200
                                        "
                                    >
                                        {{
                                            currency(
                                                account.balance
                                                ?? 0
                                            )
                                        }}
                                    </span>

                                </div>

                            </div>


                            <div
                                class="
                                    mt-2
                                    flex
                                    items-center
                                    justify-between
                                    border-t
                                    border-gray-100
                                    pt-2
                                    dark:border-gray-700
                                "
                            >

                                <span
                                    class="
                                        pl-2
                                        text-xs
                                        font-medium
                                        text-gray-500
                                        dark:text-gray-400
                                    "
                                >
                                    Total
                                    {{ category.category }}
                                </span>

                                <span
                                    class="
                                        font-mono
                                        text-sm
                                        font-semibold
                                        tabular-nums
                                        text-gray-700
                                        dark:text-gray-200
                                    "
                                >
                                    {{
                                        currency(
                                            categoryTotal(
                                                category
                                            )
                                        )
                                    }}
                                </span>

                            </div>

                        </div>


                        <div
                            class="
                                flex
                                justify-between
                                border-t
                                border-gray-200
                                pt-2
                                text-sm
                                font-bold
                                text-gray-800
                                dark:border-gray-700
                                dark:text-gray-200
                            "
                        >

                            <span>
                                Total {{ type.type }}
                            </span>

                            <span>
                                {{
                                    currency(
                                        typeTotal(
                                            type
                                        )
                                    )
                                }}
                            </span>

                        </div>

                    </div>

                </div>


                <div
                    v-else
                    class="
                        py-4
                        text-sm
                        text-gray-500
                        dark:text-gray-400
                    "
                >
                    No cost of goods sold recorded.
                </div>

            </div>


            <!-- ===================================================== -->
            <!-- GROSS PROFIT -->
            <!-- ===================================================== -->

            <div
                class="
                    mt-8
                    flex
                    items-center
                    justify-between
                    border-y
                    border-gray-300
                    py-4
                    dark:border-gray-600
                "
            >

                <span
                    class="
                        text-sm
                        font-bold
                        uppercase
                        tracking-wide
                        text-gray-900
                        dark:text-white
                    "
                >
                    Gross Profit
                </span>

                <span
                    class="
                        font-mono
                        text-base
                        font-bold
                        tabular-nums
                        text-gray-900
                        dark:text-white
                    "
                >
                    {{
                        currency(
                            statistics?.gross_profit
                            ?? 0
                        )
                    }}
                </span>

            </div>


            <!-- ===================================================== -->
            <!-- OPERATING EXPENSES -->
            <!-- ===================================================== -->

            <div class="mt-10">

                <div
                    class="
                        flex
                        items-center
                        justify-between
                        border-b
                        border-gray-300
                        pb-2
                        dark:border-gray-600
                    "
                >

                    <h2
                        class="
                            text-sm
                            font-bold
                            uppercase
                            tracking-wide
                            text-gray-900
                            dark:text-white
                        "
                    >
                        Operating Expenses
                    </h2>

                    <span
                        class="
                            text-sm
                            font-bold
                            text-gray-900
                            dark:text-white
                        "
                    >
                        {{
                            currency(
                                statistics?.total_operating_expenses
                                ?? 0
                            )
                        }}
                    </span>

                </div>


                <div
                    v-if="
                        hasAccounts(
                            operatingExpenseGroups
                        )
                    "
                    class="mt-4"
                >

                    <div
                        v-for="
                            type in operatingExpenseGroups
                        "
                        :key="
                            `expense-type-${type.type}`
                        "
                        class="mb-5"
                    >

                        <div
                            class="
                                mb-2
                                text-sm
                                font-semibold
                                text-gray-700
                                dark:text-gray-300
                            "
                        >
                            {{ type.type }}
                        </div>


                        <div
                            v-for="
                                category in type.categories
                            "
                            :key="
                                `expense-category-${category.category}`
                            "
                            class="mb-3"
                        >

                            <div
                                class="
                                    mb-1
                                    text-xs
                                    font-medium
                                    uppercase
                                    tracking-wide
                                    text-gray-500
                                    dark:text-gray-400
                                "
                            >
                                {{ category.category }}
                            </div>


                            <div class="space-y-1">

                                <div
                                    v-for="
                                        account in category.accounts
                                    "
                                    :key="
                                        account.id
                                    "
                                    class="
                                        flex
                                        items-center
                                        justify-between
                                        gap-4
                                        rounded-md
                                        px-2
                                        py-1.5
                                        text-sm
                                        hover:bg-gray-50
                                        dark:hover:bg-gray-700/40
                                    "
                                >

                                    <div
                                        class="
                                            min-w-0
                                            truncate
                                            pl-4
                                            text-gray-600
                                            dark:text-gray-300
                                        "
                                    >

                                        <span
                                            class="
                                                mr-2
                                                font-mono
                                                text-xs
                                                text-gray-400
                                                dark:text-gray-500
                                            "
                                        >
                                            {{ account.code }}
                                        </span>

                                        {{ account.name }}

                                    </div>


                                    <span
                                        class="
                                            shrink-0
                                            font-mono
                                            tabular-nums
                                            text-gray-800
                                            dark:text-gray-200
                                        "
                                    >
                                        {{
                                            currency(
                                                account.balance
                                                ?? 0
                                            )
                                        }}
                                    </span>

                                </div>

                            </div>


                            <div
                                class="
                                    mt-2
                                    flex
                                    items-center
                                    justify-between
                                    border-t
                                    border-gray-100
                                    pt-2
                                    dark:border-gray-700
                                "
                            >

                                <span
                                    class="
                                        pl-2
                                        text-xs
                                        font-medium
                                        text-gray-500
                                        dark:text-gray-400
                                    "
                                >
                                    Total
                                    {{ category.category }}
                                </span>

                                <span
                                    class="
                                        font-mono
                                        text-sm
                                        font-semibold
                                        tabular-nums
                                        text-gray-700
                                        dark:text-gray-200
                                    "
                                >
                                    {{
                                        currency(
                                            categoryTotal(
                                                category
                                            )
                                        )
                                    }}
                                </span>

                            </div>

                        </div>


                        <div
                            class="
                                flex
                                justify-between
                                border-t
                                border-gray-200
                                pt-2
                                text-sm
                                font-bold
                                text-gray-800
                                dark:border-gray-700
                                dark:text-gray-200
                            "
                        >

                            <span>
                                Total {{ type.type }}
                            </span>

                            <span>
                                {{
                                    currency(
                                        typeTotal(
                                            type
                                        )
                                    )
                                }}
                            </span>

                        </div>

                    </div>

                </div>


                <div
                    v-else
                    class="
                        py-4
                        text-sm
                        text-gray-500
                        dark:text-gray-400
                    "
                >
                    No operating expenses recorded.
                </div>

            </div>


            <!-- ===================================================== -->
            <!-- OPERATING INCOME -->
            <!-- ===================================================== -->

            <div
                class="
                    mt-8
                    flex
                    items-center
                    justify-between
                    border-y
                    border-gray-300
                    py-4
                    dark:border-gray-600
                "
            >

                <span
                    class="
                        text-sm
                        font-bold
                        uppercase
                        tracking-wide
                        text-gray-900
                        dark:text-white
                    "
                >
                    Operating Income
                </span>

                <span
                    class="
                        font-mono
                        text-base
                        font-bold
                        tabular-nums
                        text-gray-900
                        dark:text-white
                    "
                >
                    {{
                        currency(
                            statistics?.operating_income
                            ?? 0
                        )
                    }}
                </span>

            </div>

<!-- ===================================================== -->
            <!-- OTHER INCOME -->
            <!-- ===================================================== -->

            <div class="mt-10">

                <div
                    class="
                        flex
                        items-center
                        justify-between
                        border-b
                        border-gray-300
                        pb-2
                        dark:border-gray-600
                    "
                >

                    <h2
                        class="
                            text-sm
                            font-bold
                            uppercase
                            tracking-wide
                            text-gray-900
                            dark:text-white
                        "
                    >
                        Other Income
                    </h2>

                    <span
                        class="
                            text-sm
                            font-bold
                            text-gray-900
                            dark:text-white
                        "
                    >
                        {{
                            currency(
                                statistics?.total_other_income
                                ?? 0
                            )
                        }}
                    </span>

                </div>


                <div
                    v-if="
                        hasAccounts(
                            otherIncomeGroups
                        )
                    "
                    class="mt-4"
                >

                    <div
                        v-for="
                            type in otherIncomeGroups
                        "
                        :key="
                            `other-income-type-${type.type}`
                        "
                        class="mb-5"
                    >

                        <div
                            class="
                                mb-2
                                text-sm
                                font-semibold
                                text-gray-700
                                dark:text-gray-300
                            "
                        >
                            {{ type.type }}
                        </div>


                        <div
                            v-for="
                                category in type.categories
                            "
                            :key="
                                `other-income-category-${category.category}`
                            "
                            class="mb-3"
                        >

                            <div
                                class="
                                    mb-1
                                    text-xs
                                    font-medium
                                    uppercase
                                    tracking-wide
                                    text-gray-500
                                    dark:text-gray-400
                                "
                            >
                                {{ category.category }}
                            </div>


                            <div class="space-y-1">

                                <div
                                    v-for="
                                        account in category.accounts
                                    "
                                    :key="
                                        account.id
                                    "
                                    class="
                                        flex
                                        items-center
                                        justify-between
                                        gap-4
                                        rounded-md
                                        px-2
                                        py-1.5
                                        text-sm
                                        hover:bg-gray-50
                                        dark:hover:bg-gray-700/40
                                    "
                                >

                                    <div
                                        class="
                                            min-w-0
                                            truncate
                                            pl-4
                                            text-gray-600
                                            dark:text-gray-300
                                        "
                                    >

                                        <span
                                            class="
                                                mr-2
                                                font-mono
                                                text-xs
                                                text-gray-400
                                                dark:text-gray-500
                                            "
                                        >
                                            {{ account.code }}
                                        </span>

                                        {{ account.name }}

                                    </div>


                                    <span
                                        class="
                                            shrink-0
                                            font-mono
                                            tabular-nums
                                            text-gray-800
                                            dark:text-gray-200
                                        "
                                    >
                                        {{
                                            currency(
                                                account.balance
                                                ?? 0
                                            )
                                        }}
                                    </span>

                                </div>

                            </div>


                            <div
                                class="
                                    mt-2
                                    flex
                                    items-center
                                    justify-between
                                    border-t
                                    border-gray-100
                                    pt-2
                                    dark:border-gray-700
                                "
                            >

                                <span
                                    class="
                                        pl-2
                                        text-xs
                                        font-medium
                                        text-gray-500
                                        dark:text-gray-400
                                    "
                                >
                                    Total
                                    {{ category.category }}
                                </span>

                                <span
                                    class="
                                        font-mono
                                        text-sm
                                        font-semibold
                                        tabular-nums
                                        text-gray-700
                                        dark:text-gray-200
                                    "
                                >
                                    {{
                                        currency(
                                            categoryTotal(
                                                category
                                            )
                                        )
                                    }}
                                </span>

                            </div>

                        </div>


                        <div
                            class="
                                flex
                                justify-between
                                border-t
                                border-gray-200
                                pt-2
                                text-sm
                                font-bold
                                text-gray-800
                                dark:border-gray-700
                                dark:text-gray-200
                            "
                        >

                            <span>
                                Total {{ type.type }}
                            </span>

                            <span>
                                {{
                                    currency(
                                        typeTotal(
                                            type
                                        )
                                    )
                                }}
                            </span>

                        </div>

                    </div>

                </div>


                <div
                    v-else
                    class="
                        py-4
                        text-sm
                        text-gray-500
                        dark:text-gray-400
                    "
                >
                    No other income recorded.
                </div>

            </div>


            <!-- ===================================================== -->
            <!-- OTHER EXPENSES -->
            <!-- ===================================================== -->

            <div class="mt-10">

                <div
                    class="
                        flex
                        items-center
                        justify-between
                        border-b
                        border-gray-300
                        pb-2
                        dark:border-gray-600
                    "
                >

                    <h2
                        class="
                            text-sm
                            font-bold
                            uppercase
                            tracking-wide
                            text-gray-900
                            dark:text-white
                        "
                    >
                        Other Expenses
                    </h2>

                    <span
                        class="
                            text-sm
                            font-bold
                            text-gray-900
                            dark:text-white
                        "
                    >
                        {{
                            currency(
                                statistics?.total_other_expenses
                                ?? 0
                            )
                        }}
                    </span>

                </div>


                <div
                    v-if="
                        hasAccounts(
                            otherExpenseGroups
                        )
                    "
                    class="mt-4"
                >

                    <div
                        v-for="
                            type in otherExpenseGroups
                        "
                        :key="
                            `other-expense-type-${type.type}`
                        "
                        class="mb-5"
                    >

                        <div
                            class="
                                mb-2
                                text-sm
                                font-semibold
                                text-gray-700
                                dark:text-gray-300
                            "
                        >
                            {{ type.type }}
                        </div>


                        <div
                            v-for="
                                category in type.categories
                            "
                            :key="
                                `other-expense-category-${category.category}`
                            "
                            class="mb-3"
                        >

                            <div
                                class="
                                    mb-1
                                    text-xs
                                    font-medium
                                    uppercase
                                    tracking-wide
                                    text-gray-500
                                    dark:text-gray-400
                                "
                            >
                                {{ category.category }}
                            </div>


                            <div class="space-y-1">

                                <div
                                    v-for="
                                        account in category.accounts
                                    "
                                    :key="
                                        account.id
                                    "
                                    class="
                                        flex
                                        items-center
                                        justify-between
                                        gap-4
                                        rounded-md
                                        px-2
                                        py-1.5
                                        text-sm
                                        hover:bg-gray-50
                                        dark:hover:bg-gray-700/40
                                    "
                                >

                                    <div
                                        class="
                                            min-w-0
                                            truncate
                                            pl-4
                                            text-gray-600
                                            dark:text-gray-300
                                        "
                                    >

                                        <span
                                            class="
                                                mr-2
                                                font-mono
                                                text-xs
                                                text-gray-400
                                                dark:text-gray-500
                                            "
                                        >
                                            {{ account.code }}
                                        </span>

                                        {{ account.name }}

                                    </div>


                                    <span
                                        class="
                                            shrink-0
                                            font-mono
                                            tabular-nums
                                            text-gray-800
                                            dark:text-gray-200
                                        "
                                    >
                                        {{
                                            currency(
                                                account.balance
                                                ?? 0
                                            )
                                        }}
                                    </span>

                                </div>

                            </div>


                            <div
                                class="
                                    mt-2
                                    flex
                                    items-center
                                    justify-between
                                    border-t
                                    border-gray-100
                                    pt-2
                                    dark:border-gray-700
                                "
                            >

                                <span
                                    class="
                                        pl-2
                                        text-xs
                                        font-medium
                                        text-gray-500
                                        dark:text-gray-400
                                    "
                                >
                                    Total
                                    {{ category.category }}
                                </span>

                                <span
                                    class="
                                        font-mono
                                        text-sm
                                        font-semibold
                                        tabular-nums
                                        text-gray-700
                                        dark:text-gray-200
                                    "
                                >
                                    {{
                                        currency(
                                            categoryTotal(
                                                category
                                            )
                                        )
                                    }}
                                </span>

                            </div>

                        </div>


                        <div
                            class="
                                flex
                                justify-between
                                border-t
                                border-gray-200
                                pt-2
                                text-sm
                                font-bold
                                text-gray-800
                                dark:border-gray-700
                                dark:text-gray-200
                            "
                        >

                            <span>
                                Total {{ type.type }}
                            </span>

                            <span>
                                {{
                                    currency(
                                        typeTotal(
                                            type
                                        )
                                    )
                                }}
                            </span>

                        </div>

                    </div>

                </div>


                <div
                    v-else
                    class="
                        py-4
                        text-sm
                        text-gray-500
                        dark:text-gray-400
                    "
                >
                    No other expenses recorded.
                </div>

            </div>


            <!-- ===================================================== -->
            <!-- NET INCOME -->
            <!-- ===================================================== -->

            <div
                class="
                    mt-10
                    border-y-2
                    border-gray-900
                    py-5
                    dark:border-gray-300
                "
            >

                <div
                    class="
                        flex
                        items-center
                        justify-between
                    "
                >

                    <span
                        class="
                            text-base
                            font-bold
                            uppercase
                            tracking-wide
                            text-gray-900
                            dark:text-white
                        "
                    >
                        Net Income
                    </span>

                    <span
                        class="
                            font-mono
                            text-xl
                            font-bold
                            tabular-nums
                            text-gray-900
                            dark:text-white
                        "
                    >
                        {{
                            currency(
                                statistics?.net_income
                                ?? 0
                            )
                        }}
                    </span>

                </div>

            </div>

        </Card>

    </div>

</AppLayout>

</template>


<style>

@media print {

    body {
        background: white !important;
    }

    button,
    select,
    input,
    .no-print {
        display: none !important;
    }

}

</style>