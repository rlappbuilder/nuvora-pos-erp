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
            ?? '',

        date_to:
            props.filters?.date_to
            ?? '',

    })


/*
|--------------------------------------------------------------------------
| Load Data
|--------------------------------------------------------------------------
*/

function loadData()
{

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
| Fiscal Year Change
|--------------------------------------------------------------------------
*/

function handleFiscalYearChange()
{

    if (
        filters.accounting_period_id
        &&
        !filteredAccountingPeriods.value.some(

            period =>
                Number(period.id) ===
                Number(
                    filters.accounting_period_id
                )

        )
    ) {

        filters.accounting_period_id = ''

    }

}


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

            branch_id: '',
            fiscal_year_id: '',
            accounting_period_id: '',
            date_from: '',
            date_to: '',

        }

    )

    loadData()

}


/*
|--------------------------------------------------------------------------
| Date Labels
|--------------------------------------------------------------------------
*/

const dateFromLabel =
    computed(() => {

        if (!filters.date_from) {

            return 'Beginning'

        }

        return filters.date_from

    })


const dateToLabel =
    computed(() => {

        if (!filters.date_to) {

            return new Date()
                .toISOString()
                .slice(0, 10)

        }

        return filters.date_to

    })


/*
|--------------------------------------------------------------------------
| Report Groups
|--------------------------------------------------------------------------
*/

function groupAccounts(accounts)
{

    const grouped = {}


    ;(accounts || [])
        .forEach(account => {

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

        })


    return Object.entries(grouped)
        .map(
            ([type, categories]) => ({

                type,

                categories:
                    Object.entries(categories)
                        .map(
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

function formatAmount(value)
{

    return Number(
        value ?? 0
    ).toLocaleString(
        'en-US',
        {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }
    )

}


function hasAccounts(groups)
{

    return groups.some(
        group =>
            group.categories.some(
                category =>
                    category.accounts.length
            )
    )

}


function categoryTotal(category)
{

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


function typeTotal(type)
{

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
| Print
|--------------------------------------------------------------------------
*/

function printReport()
{

    window.print()

}


/*
|--------------------------------------------------------------------------
| Watchers
|--------------------------------------------------------------------------
*/

watch(
    () => filters.branch_id,
    () => {

        loadData()

    }
)


watch(
    () => filters.fiscal_year_id,
    () => {

        handleFiscalYearChange()

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
        <!-- PAGE HEADER -->
        <!-- ===================================================== -->

        <PageHeader
            icon="📈"
            :title="pageTitle"
            subtitle="Income statement summary of revenue, expenses, and net income."
        />


        <!-- ===================================================== -->
        <!-- FILTER CARD -->
        <!-- ===================================================== -->

        <Card>

            <div class="space-y-4">


                <!-- DATE ROW -->

                <div
                    class="
                        flex
                        flex-col
                        gap-3
                        lg:flex-row
                        lg:items-center
                    "
                >

                    <!-- Date From -->

                    <FlatPickr
                        v-model="
                            filters.date_from
                        "
                        :config="{
                            dateFormat: 'Y-m-d',
                            allowInput: true,
                        }"
                        placeholder="Date From"
                        class="
                            w-full
                            rounded-xl
                            border
                            border-gray-300
                            px-4
                            py-2.5
                            text-sm
                            lg:w-52
                        "
                    />


                    <!-- Date To -->

                    <FlatPickr
                        v-model="
                            filters.date_to
                        "
                        :config="{
                            dateFormat: 'Y-m-d',
                            allowInput: true,
                        }"
                        placeholder="Date To"
                        class="
                            w-full
                            rounded-xl
                            border
                            border-gray-300
                            px-4
                            py-2.5
                            text-sm
                            lg:w-52
                        "
                    />


                    <div
                        class="
                            flex
                            w-full
                            lg:ml-auto
                            lg:w-auto
                        "
                    >

                        <BaseButton
                            variant="secondary"
                            class="
                                w-full
                                whitespace-nowrap
                                lg:w-auto
                            "
                            @click="refresh"
                        >
                            Refresh
                        </BaseButton>

                    </div>

                </div>


                <!-- FILTER ROW -->

                <div
                    class="
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
                        @update:model-value="
                            handleFiscalYearChange
                        "
                    />


                    <!-- Accounting Period -->

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

        </Card>


        <!-- ===================================================== -->
        <!-- REPORT -->
        <!-- ===================================================== -->

        <Card>

            <LoadingOverlay
                :show="loading"
                text="Loading Income Statement..."
            />


            <!-- ================================================= -->
            <!-- REPORT HEADER -->
            <!-- ================================================= -->

            <div
                class="
                    border-b
                    border-gray-200
                    pb-6
                    text-center
                "
            >

                <div
                    class="
                        text-xl
                        font-bold
                        text-gray-900
                    "
                >
                    {{ pageTitle }}
                </div>

                <div
                    class="
                        mt-1
                        text-sm
                        font-medium
                        text-gray-600
                    "
                >
                    Statement of Profit or Loss
                </div>

                <div
                    class="
                        mt-2
                        text-xs
                        text-gray-500
                    "
                >
                    For the period
                    {{ dateFromLabel }}
                    to
                    {{ dateToLabel }}
                </div>

            </div>


            <!-- ================================================= -->
            <!-- REVENUE -->
            <!-- ================================================= -->

            <div class="mt-8">


                <div
                    class="
                        flex
                        items-center
                        justify-between
                        border-b
                        border-gray-300
                        pb-2
                    "
                >

                    <h2
                        class="
                            text-sm
                            font-bold
                            uppercase
                            tracking-wide
                            text-gray-900
                        "
                    >
                        Revenue
                    </h2>

                    <span
                        class="
                            text-sm
                            font-bold
                            text-gray-900
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
                                "
                            >
                                {{ category.category }}
                            </div>


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
                                    py-1
                                    pl-5
                                    text-sm
                                "
                            >

                                <div
                                    class="
                                        min-w-0
                                        text-gray-700
                                    "
                                >

                                    <span
                                        class="
                                            font-medium
                                            text-gray-900
                                        "
                                    >
                                        {{ account.code }}
                                    </span>

                                    <span class="ml-2">
                                        {{ account.name }}
                                    </span>

                                </div>

                                <span
                                    class="
                                        shrink-0
                                        font-medium
                                        text-gray-900
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


                            <div
                                class="
                                    mt-1
                                    flex
                                    justify-between
                                    border-t
                                    border-gray-100
                                    pt-1
                                    pl-5
                                    text-sm
                                    font-semibold
                                    text-gray-700
                                "
                            >

                                <span>
                                    Total {{ category.category }}
                                </span>

                                <span>
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
                    "
                >
                    No revenue recorded.
                </div>

            </div>


            <!-- ================================================= -->
            <!-- COGS -->
            <!-- ================================================= -->

            <div class="mt-10">


                <div
                    class="
                        flex
                        items-center
                        justify-between
                        border-b
                        border-gray-300
                        pb-2
                    "
                >

                    <h2
                        class="
                            text-sm
                            font-bold
                            uppercase
                            tracking-wide
                            text-gray-900
                        "
                    >
                        Cost of Goods Sold
                    </h2>

                    <span
                        class="
                            text-sm
                            font-bold
                            text-gray-900
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
                                "
                            >
                                {{ category.category }}
                            </div>


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
                                    py-1
                                    pl-5
                                    text-sm
                                "
                            >

                                <div
                                    class="
                                        min-w-0
                                        text-gray-700
                                    "
                                >

                                    <span
                                        class="
                                            font-medium
                                            text-gray-900
                                        "
                                    >
                                        {{ account.code }}
                                    </span>

                                    <span class="ml-2">
                                        {{ account.name }}
                                    </span>

                                </div>

                                <span
                                    class="
                                        shrink-0
                                        font-medium
                                        text-gray-900
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


                            <div
                                class="
                                    mt-1
                                    flex
                                    justify-between
                                    border-t
                                    border-gray-100
                                    pt-1
                                    pl-5
                                    text-sm
                                    font-semibold
                                    text-gray-700
                                "
                            >

                                <span>
                                    Total {{ category.category }}
                                </span>

                                <span>
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
                    "
                >
                    No cost of goods sold recorded.
                </div>

            </div>


            <!-- ================================================= -->
            <!-- GROSS PROFIT -->
            <!-- ================================================= -->

            <div
                class="
                    mt-8
                    flex
                    items-center
                    justify-between
                    border-y
                    border-gray-300
                    py-4
                "
            >

                <span
                    class="
                        text-sm
                        font-bold
                        uppercase
                        tracking-wide
                        text-gray-900
                    "
                >
                    Gross Profit
                </span>

                <span
                    class="
                        text-base
                        font-bold
                        text-gray-900
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


            <!-- ================================================= -->
            <!-- OPERATING EXPENSES -->
            <!-- ================================================= -->

            <div class="mt-10">


                <div
                    class="
                        flex
                        items-center
                        justify-between
                        border-b
                        border-gray-300
                        pb-2
                    "
                >

                    <h2
                        class="
                            text-sm
                            font-bold
                            uppercase
                            tracking-wide
                            text-gray-900
                        "
                    >
                        Operating Expenses
                    </h2>

                    <span
                        class="
                            text-sm
                            font-bold
                            text-gray-900
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
                                "
                            >
                                {{ category.category }}
                            </div>


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
                                    py-1
                                    pl-5
                                    text-sm
                                "
                            >

                                <div
                                    class="
                                        min-w-0
                                        text-gray-700
                                    "
                                >

                                    <span
                                        class="
                                            font-medium
                                            text-gray-900
                                        "
                                    >
                                        {{ account.code }}
                                    </span>

                                    <span class="ml-2">
                                        {{ account.name }}
                                    </span>

                                </div>

                                <span
                                    class="
                                        shrink-0
                                        font-medium
                                        text-gray-900
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


                            <div
                                class="
                                    mt-1
                                    flex
                                    justify-between
                                    border-t
                                    border-gray-100
                                    pt-1
                                    pl-5
                                    text-sm
                                    font-semibold
                                    text-gray-700
                                "
                            >

                                <span>
                                    Total {{ category.category }}
                                </span>

                                <span>
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
                    "
                >
                    No operating expenses recorded.
                </div>

            </div>


            <!-- ================================================= -->
            <!-- OPERATING INCOME -->
            <!-- ================================================= -->

            <div
                class="
                    mt-8
                    flex
                    items-center
                    justify-between
                    border-y
                    border-gray-300
                    py-4
                "
            >

                <span
                    class="
                        text-sm
                        font-bold
                        uppercase
                        tracking-wide
                        text-gray-900
                    "
                >
                    Operating Income
                </span>

                <span
                    class="
                        text-base
                        font-bold
                        text-gray-900
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


            <!-- ================================================= -->
            <!-- OTHER INCOME -->
            <!-- ================================================= -->

            <div class="mt-10">


                <div
                    class="
                        flex
                        items-center
                        justify-between
                        border-b
                        border-gray-300
                        pb-2
                    "
                >

                    <h2
                        class="
                            text-sm
                            font-bold
                            uppercase
                            tracking-wide
                            text-gray-900
                        "
                    >
                        Other Income
                    </h2>

                    <span
                        class="
                            text-sm
                            font-bold
                            text-gray-900
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
                                "
                            >
                                {{ category.category }}
                            </div>


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
                                    py-1
                                    pl-5
                                    text-sm
                                "
                            >

                                <div
                                    class="
                                        min-w-0
                                        text-gray-700
                                    "
                                >

                                    <span
                                        class="
                                            font-medium
                                            text-gray-900
                                        "
                                    >
                                        {{ account.code }}
                                    </span>

                                    <span class="ml-2">
                                        {{ account.name }}
                                    </span>

                                </div>

                                <span
                                    class="
                                        shrink-0
                                        font-medium
                                        text-gray-900
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


                            <div
                                class="
                                    mt-1
                                    flex
                                    justify-between
                                    border-t
                                    border-gray-100
                                    pt-1
                                    pl-5
                                    text-sm
                                    font-semibold
                                    text-gray-700
                                "
                            >

                                <span>
                                    Total {{ category.category }}
                                </span>

                                <span>
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
                    "
                >
                    No other income recorded.
                </div>

            </div>


            <!-- ================================================= -->
            <!-- OTHER EXPENSES -->
            <!-- ================================================= -->

            <div class="mt-10">


                <div
                    class="
                        flex
                        items-center
                        justify-between
                        border-b
                        border-gray-300
                        pb-2
                    "
                >

                    <h2
                        class="
                            text-sm
                            font-bold
                            uppercase
                            tracking-wide
                            text-gray-900
                        "
                    >
                        Other Expenses
                    </h2>

                    <span
                        class="
                            text-sm
                            font-bold
                            text-gray-900
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
                                "
                            >
                                {{ category.category }}
                            </div>


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
                                    py-1
                                    pl-5
                                    text-sm
                                "
                            >

                                <div
                                    class="
                                        min-w-0
                                        text-gray-700
                                    "
                                >

                                    <span
                                        class="
                                            font-medium
                                            text-gray-900
                                        "
                                    >
                                        {{ account.code }}
                                    </span>

                                    <span class="ml-2">
                                        {{ account.name }}
                                    </span>

                                </div>

                                <span
                                    class="
                                        shrink-0
                                        font-medium
                                        text-gray-900
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


                            <div
                                class="
                                    mt-1
                                    flex
                                    justify-between
                                    border-t
                                    border-gray-100
                                    pt-1
                                    pl-5
                                    text-sm
                                    font-semibold
                                    text-gray-700
                                "
                            >

                                <span>
                                    Total {{ category.category }}
                                </span>

                                <span>
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
                    "
                >
                    No other expenses recorded.
                </div>

            </div>


            <!-- ================================================= -->
            <!-- NET INCOME -->
            <!-- ================================================= -->

            <div
                class="
                    mt-10
                    border-y-2
                    border-gray-900
                    py-5
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
                        "
                    >
                        Net Income
                    </span>

                    <span
                        class="
                            text-xl
                            font-bold
                            text-gray-900
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


            <!-- ================================================= -->
            <!-- PRINT -->
            <!-- ================================================= -->

            <div
                class="
                    mt-8
                    flex
                    justify-end
                    border-t
                    border-gray-200
                    pt-5
                "
            >

                <BaseButton
                    variant="secondary"
                    @click="printReport"
                >
                    Print
                </BaseButton>

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

    button {
        display: none !important;
    }

    input,
    select {
        display: none !important;
    }

    .no-print {
        display: none !important;
    }

}

</style>