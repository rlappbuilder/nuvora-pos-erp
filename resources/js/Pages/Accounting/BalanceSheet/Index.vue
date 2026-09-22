<script setup>
import { computed, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import FlatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'
import AppLayout from '@/Layouts/AppLayout.vue'
const props = defineProps({
    title: {
        type: String,
        default: 'Balance Sheet',
    },

    report: {
        type: Object,
        default: () => ({
            assets: [],
            liabilities: [],
            equity: [],
            current_year_earnings: {
                revenue: 0,
                cost_of_goods_sold: 0,
                expense: 0,
                other_income: 0,
                other_expense: 0,
                net_income: 0,
            },
        }),
    },

    statistics: {
        type: Object,
        default: () => ({
            total_assets: 0,
            total_liabilities: 0,
            total_equity: 0,
            current_year_earnings: 0,
            total_liabilities_equity: 0,
            difference: 0,
            is_balanced: false,
        }),
    },

    filters: {
        type: Object,
        default: () => ({
            branch_id: '',
            fiscal_year_id: '',
            accounting_period_id: '',
            as_of_date: '',
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
})

const form = reactive({
    branch_id: props.filters.branch_id || '',
    fiscal_year_id: props.filters.fiscal_year_id || '',
    accounting_period_id: props.filters.accounting_period_id || '',
    as_of_date: props.filters.as_of_date || '',
})

const showZeroBalance = reactive({
    value: false,
})

const isLoading = reactive({
    value: false,
})


/*
|--------------------------------------------------------------------------
| Formatting
|--------------------------------------------------------------------------
*/

const formatAmount = (value) => {
    const amount = Number(value || 0)

    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount)
}

const formatCurrency = (value) => {
    return `Rp ${formatAmount(value)}`
}


/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

const hasBalance = (account) => {
    if (showZeroBalance.value) {
        return true
    }

    return Math.abs(Number(account.balance || 0)) >= 0.005
}


/*
|--------------------------------------------------------------------------
| Group Accounts
|--------------------------------------------------------------------------
|
| Account hierarchy:
|
| Account Type
|     Account Category
|         Posting Account
|
*/

const groupAccounts = (accounts = []) => {
    const filteredAccounts =
        accounts.filter(hasBalance)

    const typeMap = new Map()

    filteredAccounts.forEach((account) => {
        const typeName =
            account.account_type || 'Uncategorized'

        const categoryName =
            account.account_category || 'Uncategorized'

        if (!typeMap.has(typeName)) {
            typeMap.set(typeName, {
                name: typeName,
                categories: new Map(),
            })
        }

        const type =
            typeMap.get(typeName)

        if (!type.categories.has(categoryName)) {
            type.categories.set(categoryName, {
                name: categoryName,
                accounts: [],
            })
        }

        type.categories
            .get(categoryName)
            .accounts
            .push(account)
    })

    return Array.from(typeMap.values()).map((type) => ({
        name: type.name,

        categories:
            Array.from(
                type.categories.values()
            ).map((category) => ({
                name: category.name,

                accounts:
                    category.accounts,
            })),
    }))
}


/*
|--------------------------------------------------------------------------
| Computed Groups
|--------------------------------------------------------------------------
*/

const assetGroups = computed(() => {
    return groupAccounts(
        props.report.assets
    )
})

const liabilityGroups = computed(() => {
    return groupAccounts(
        props.report.liabilities
    )
})

const equityGroups = computed(() => {
    return groupAccounts(
        props.report.equity
    )
})


/*
|--------------------------------------------------------------------------
| Group Totals
|--------------------------------------------------------------------------
*/

const categoryTotal = (category) => {
    return category.accounts.reduce(
        (total, account) =>
            total + Number(account.balance || 0),
        0
    )
}

const typeTotal = (type) => {
    return type.categories.reduce(
        (total, category) =>
            total + categoryTotal(category),
        0
    )
}


/*
|--------------------------------------------------------------------------
| Filter Actions
|--------------------------------------------------------------------------
*/

const applyFilters = () => {
    isLoading.value = true

    router.get(
        route('balance-sheet.index'),
        {
            branch_id:
                form.branch_id || undefined,

            fiscal_year_id:
                form.fiscal_year_id || undefined,

            accounting_period_id:
                form.accounting_period_id || undefined,

            as_of_date:
                form.as_of_date || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            onFinish: () => {
                isLoading.value = false
            },
        }
    )
}

const resetFilters = () => {
    form.branch_id = ''
    form.fiscal_year_id = ''
    form.accounting_period_id = ''
    form.as_of_date = ''

    applyFilters()
}


/*
|--------------------------------------------------------------------------
| Fiscal Year / Period
|--------------------------------------------------------------------------
*/

const filteredPeriods = computed(() => {
    if (!form.fiscal_year_id) {
        return props.accountingPeriods
    }

    return props.accountingPeriods.filter(
        (period) =>
            String(period.fiscal_year_id) ===
            String(form.fiscal_year_id)
    )
})

const selectedBranch = computed(() => {
    return props.branches.find(
        (branch) =>
            String(branch.id) ===
            String(form.branch_id)
    )
})

const selectedFiscalYear = computed(() => {
    return props.fiscalYears.find(
        (year) =>
            String(year.id) ===
            String(form.fiscal_year_id)
    )
})

const selectedPeriod = computed(() => {
    return props.accountingPeriods.find(
        (period) =>
            String(period.id) ===
            String(form.accounting_period_id)
    )
})

const reportDateLabel = computed(() => {
    if (!form.as_of_date) {
        return ''
    }

    const date =
        new Date(form.as_of_date)

    if (Number.isNaN(date.getTime())) {
        return form.as_of_date
    }

    return new Intl.DateTimeFormat(
        'en-GB',
        {
            day: '2-digit',
            month: 'long',
            year: 'numeric',
        }
    ).format(date)
})


/*
|--------------------------------------------------------------------------
| Keep Period Consistent
|--------------------------------------------------------------------------
*/

const onFiscalYearChange = () => {
    const exists =
        filteredPeriods.value.some(
            (period) =>
                String(period.id) ===
                String(form.accounting_period_id)
        )

    if (!exists) {
        form.accounting_period_id = ''
    }
}

const printReport = () => {
    const params = new URLSearchParams()

    if (form.branch_id) {
        params.set(
            'branch_id',
            form.branch_id
        )
    }

    if (form.fiscal_year_id) {
        params.set(
            'fiscal_year_id',
            form.fiscal_year_id
        )
    }

    if (form.accounting_period_id) {
        params.set(
            'accounting_period_id',
            form.accounting_period_id
        )
    }

    if (form.as_of_date) {
        params.set(
            'as_of_date',
            form.as_of_date
        )
    }

    params.set(
        'show_zero_balance',
        showZeroBalance.value ? '1' : '0'
    )

    const url =
        `${route('balance-sheet.print')}?${params.toString()}`

    window.open(
        url,
        '_blank'
    )
}
</script>

<template>
    <AppLayout>
    <div class="space-y-6 p-6">

        <!-- ========================================================= -->
        <!-- Header -->
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
                    {{ title }}
                </h1>

                <p
                    class="
                        mt-1
                        text-sm
                        text-gray-500
                        dark:text-gray-400
                    "
                >
                    Statement of Financial Position
                </p>

                <p
                    v-if="reportDateLabel"
                    class="
                        mt-1
                        text-sm
                        text-gray-500
                        dark:text-gray-400
                    "
                >
                    As of {{ reportDateLabel }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    class="
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
                    @click="printReport"
                >
                    Print
                </button>
            </div>
        </div>


        <!-- ========================================================= -->
        <!-- Filters -->
        <!-- ========================================================= -->

        <div
            class="
                rounded-xl
                border
                border-gray-200
                bg-white
                p-5
                shadow-sm
                dark:border-gray-700
                dark:bg-gray-800
            "
        >
            <div
                class="
                    mb-4
                    flex
                    items-center
                    justify-between
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
                        v-model="showZeroBalance.value"
                        type="checkbox"
                        class="
                            rounded
                            border-gray-300
                            text-indigo-600
                            focus:ring-indigo-500
                        "
                    />

                    Show Zero Balance
                </label>
            </div>


            <div
                class="
                    grid
                    grid-cols-1
                    gap-4
                    md:grid-cols-2
                    xl:grid-cols-4
                "
            >

                <!-- Branch -->

                <div>
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

                    <select
                        v-model="form.branch_id"
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
                    >
                        <option value="">
                            All Branches
                        </option>

                        <option
                            v-for="branch in branches"
                            :key="branch.id"
                            :value="branch.id"
                        >
                            {{ branch.label }}
                        </option>
                    </select>
                </div>


                <!-- Fiscal Year -->

                <div>
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

                    <select
                        v-model="form.fiscal_year_id"
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
                        @change="onFiscalYearChange"
                    >
                        <option value="">
                            All Fiscal Years
                        </option>

                        <option
                            v-for="year in fiscalYears"
                            :key="year.id"
                            :value="year.id"
                        >
                            {{ year.label }}
                        </option>
                    </select>
                </div>


                <!-- Period -->

                <div>
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

                    <select
                        v-model="form.accounting_period_id"
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
                    >
                        <option value="">
                            All Periods
                        </option>

                        <option
                            v-for="period in filteredPeriods"
                            :key="period.id"
                            :value="period.id"
                        >
                            {{ period.label }}
                        </option>
                    </select>
                </div>


                <!-- As Of Date -->

                <div>
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
                        As Of Date
                    </label>

                    <FlatPickr
                        v-model="form.as_of_date"
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
                        :config="{
                            dateFormat: 'Y-m-d',
                            allowInput: true,
                        }"
                    />
                </div>

            </div>


            <div
                class="
                    mt-5
                    flex
                    justify-end
                    gap-2
                "
            >
                <button
                    type="button"
                    class="
                        rounded-lg
                        border
                        border-gray-300
                        bg-white
                        px-4
                        py-2
                        text-sm
                        font-medium
                        text-gray-700
                        transition
                        hover:bg-gray-50
                        dark:border-gray-600
                        dark:bg-gray-900
                        dark:text-gray-200
                        dark:hover:bg-gray-700
                    "
                    :disabled="isLoading.value"
                    @click="resetFilters"
                >
                    Reset
                </button>

                <button
                    type="button"
                    class="
                        rounded-lg
                        bg-indigo-600
                        px-5
                        py-2
                        text-sm
                        font-medium
                        text-white
                        shadow-sm
                        transition
                        hover:bg-indigo-700
                        disabled:cursor-not-allowed
                        disabled:opacity-60
                    "
                    :disabled="isLoading.value"
                    @click="applyFilters"
                >
                    {{ isLoading.value ? 'Loading...' : 'Apply' }}
                </button>
            </div>
        </div>


        <!-- ========================================================= -->
        <!-- Report Header -->
        <!-- ========================================================= -->

        <div
            class="
                rounded-xl
                border
                border-gray-200
                bg-white
                px-6
                py-5
                shadow-sm
                dark:border-gray-700
                dark:bg-gray-800
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
                        Balance Sheet
                    </div>

                    <div
                        class="
                            mt-1
                            text-sm
                            text-gray-500
                            dark:text-gray-400
                        "
                    >
                        Statement of Financial Position
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
                        v-if="selectedBranch"
                        class="
                            font-medium
                            text-gray-800
                            dark:text-gray-200
                        "
                    >
                        {{ selectedBranch.label }}
                    </div>

                    <div
                        v-if="selectedFiscalYear"
                        class="
                            text-gray-500
                            dark:text-gray-400
                        "
                    >
                        Fiscal Year {{ selectedFiscalYear.label }}
                    </div>

                    <div
                        v-if="selectedPeriod"
                        class="
                            text-gray-500
                            dark:text-gray-400
                        "
                    >
                        {{ selectedPeriod.label }}
                    </div>

                    <div
                        v-if="reportDateLabel"
                        class="
                            text-gray-500
                            dark:text-gray-400
                        "
                    >
                        As of {{ reportDateLabel }}
                    </div>
                </div>
            </div>
        </div>


        <!-- ========================================================= -->
        <!-- Financial Statement -->
        <!-- ========================================================= -->

        <div
            class="
                grid
                grid-cols-1
                gap-6
                xl:grid-cols-2
            "
        >

            <!-- ===================================================== -->
            <!-- ASSETS -->
            <!-- ===================================================== -->

            <section
                class="
                    overflow-hidden
                    rounded-xl
                    border
                    border-gray-200
                    bg-white
                    shadow-sm
                    dark:border-gray-700
                    dark:bg-gray-800
                "
            >
                <div
                    class="
                        border-b
                        border-gray-200
                        bg-gray-50
                        px-6
                        py-4
                        dark:border-gray-700
                        dark:bg-gray-900/50
                    "
                >
                    <h2
                        class="
                            text-sm
                            font-bold
                            uppercase
                            tracking-wider
                            text-gray-900
                            dark:text-white
                        "
                    >
                        Assets
                    </h2>
                </div>

                <div class="px-6 py-5">

                    <template
                        v-for="type in assetGroups"
                        :key="type.name"
                    >

                        <div class="mb-6 last:mb-0">

                            <div
                                class="
                                    mb-3
                                    flex
                                    items-center
                                    justify-between
                                    border-b
                                    border-gray-100
                                    pb-2
                                    dark:border-gray-700
                                "
                            >
                                <span
                                    class="
                                        text-xs
                                        font-bold
                                        uppercase
                                        tracking-wide
                                        text-gray-500
                                        dark:text-gray-400
                                    "
                                >
                                    {{ type.name }}
                                </span>

                                <span
                                    class="
                                        text-sm
                                        font-semibold
                                        text-gray-800
                                        dark:text-gray-200
                                    "
                                >
                                    {{ formatCurrency(typeTotal(type)) }}
                                </span>
                            </div>


                            <div
                                v-for="category in type.categories"
                                :key="category.name"
                                class="mb-5 last:mb-0"
                            >

                                <div
                                    class="
                                        mb-2
                                        text-sm
                                        font-semibold
                                        text-gray-800
                                        dark:text-gray-200
                                    "
                                >
                                    {{ category.name }}
                                </div>


                                <div class="space-y-1">

                                    <div
                                        v-for="account in category.accounts"
                                        :key="account.id"
                                        class="
                                            flex
                                            items-center
                                            justify-between
                                            gap-4
                                            rounded-md
                                            px-2
                                            py-1.5
                                            text-sm
                                            transition
                                            hover:bg-gray-50
                                            dark:hover:bg-gray-700/40
                                        "
                                    >
                                        <span
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
                                        </span>

                                        <span
                                            class="
                                                shrink-0
                                                font-mono
                                                tabular-nums
                                                text-gray-800
                                                dark:text-gray-200
                                            "
                                        >
                                            {{ formatCurrency(account.balance) }}
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
                                        Total {{ category.name }}
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
                                        {{ formatCurrency(categoryTotal(category)) }}
                                    </span>
                                </div>

                            </div>

                        </div>

                    </template>


                    <div
                        class="
                            mt-6
                            flex
                            items-center
                            justify-between
                            border-t-2
                            border-gray-300
                            pt-4
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
                            Total Assets
                        </span>

                        <span
                            class="
                                font-mono
                                text-lg
                                font-bold
                                tabular-nums
                                text-gray-900
                                dark:text-white
                            "
                        >
                            {{ formatCurrency(statistics.total_assets) }}
                        </span>
                    </div>

                </div>
            </section>


            <!-- ===================================================== -->
            <!-- LIABILITIES & EQUITY -->
            <!-- ===================================================== -->

            <section
                class="
                    overflow-hidden
                    rounded-xl
                    border
                    border-gray-200
                    bg-white
                    shadow-sm
                    dark:border-gray-700
                    dark:bg-gray-800
                "
            >
                <div
                    class="
                        border-b
                        border-gray-200
                        bg-gray-50
                        px-6
                        py-4
                        dark:border-gray-700
                        dark:bg-gray-900/50
                    "
                >
                    <h2
                        class="
                            text-sm
                            font-bold
                            uppercase
                            tracking-wider
                            text-gray-900
                            dark:text-white
                        "
                    >
                        Liabilities & Equity
                    </h2>
                </div>

                <div class="px-6 py-5">

                    <!-- Liabilities -->

                    <div class="mb-8">

                        <div
                            class="
                                mb-4
                                text-xs
                                font-bold
                                uppercase
                                tracking-wide
                                text-gray-500
                                dark:text-gray-400
                            "
                        >
                            Liabilities
                        </div>


                        <template
                            v-for="type in liabilityGroups"
                            :key="type.name"
                        >

                            <div class="mb-6 last:mb-0">

                                <div
                                    class="
                                        mb-3
                                        flex
                                        items-center
                                        justify-between
                                        border-b
                                        border-gray-100
                                        pb-2
                                        dark:border-gray-700
                                    "
                                >
                                    <span
                                        class="
                                            text-sm
                                            font-semibold
                                            text-gray-800
                                            dark:text-gray-200
                                        "
                                    >
                                        {{ type.name }}
                                    </span>

                                    <span
                                        class="
                                            font-mono
                                            text-sm
                                            font-semibold
                                            tabular-nums
                                            text-gray-800
                                            dark:text-gray-200
                                        "
                                    >
                                        {{ formatCurrency(typeTotal(type)) }}
                                    </span>
                                </div>


                                <div
                                    v-for="category in type.categories"
                                    :key="category.name"
                                    class="mb-5 last:mb-0"
                                >

                                    <div
                                        class="
                                            mb-2
                                            text-sm
                                            font-semibold
                                            text-gray-800
                                            dark:text-gray-200
                                        "
                                    >
                                        {{ category.name }}
                                    </div>

                                    <div class="space-y-1">

                                        <div
                                            v-for="account in category.accounts"
                                            :key="account.id"
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
                                            <span
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
                                            </span>

                                            <span
                                                class="
                                                    shrink-0
                                                    font-mono
                                                    tabular-nums
                                                    text-gray-800
                                                    dark:text-gray-200
                                                "
                                            >
                                                {{ formatCurrency(account.balance) }}
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
                                            Total {{ category.name }}
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
                                            {{ formatCurrency(categoryTotal(category)) }}
                                        </span>
                                    </div>

                                </div>

                            </div>

                        </template>


                        <div
                            class="
                                mt-5
                                flex
                                items-center
                                justify-between
                                border-t-2
                                border-gray-300
                                pt-3
                                dark:border-gray-600
                            "
                        >
                            <span
                                class="
                                    text-sm
                                    font-bold
                                    text-gray-800
                                    dark:text-gray-200
                                "
                            >
                                Total Liabilities
                            </span>

                            <span
                                class="
                                    font-mono
                                    text-base
                                    font-bold
                                    tabular-nums
                                    text-gray-800
                                    dark:text-gray-200
                                "
                            >
                                {{ formatCurrency(statistics.total_liabilities) }}
                            </span>
                        </div>

                    </div>


                    <!-- Equity -->

                    <div>

                        <div
                            class="
                                mb-4
                                text-xs
                                font-bold
                                uppercase
                                tracking-wide
                                text-gray-500
                                dark:text-gray-400
                            "
                        >
                            Equity
                        </div>


                        <template
                            v-for="type in equityGroups"
                            :key="type.name"
                        >

                            <div class="mb-6 last:mb-0">

                                <div
                                    v-for="category in type.categories"
                                    :key="category.name"
                                    class="mb-5 last:mb-0"
                                >

                                    <div
                                        class="
                                            mb-2
                                            text-sm
                                            font-semibold
                                            text-gray-800
                                            dark:text-gray-200
                                        "
                                    >
                                        {{ category.name }}
                                    </div>

                                    <div class="space-y-1">

                                        <div
                                            v-for="account in category.accounts"
                                            :key="account.id"
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
                                            <span
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
                                            </span>

                                            <span
                                                class="
                                                    shrink-0
                                                    font-mono
                                                    tabular-nums
                                                    text-gray-800
                                                    dark:text-gray-200
                                                "
                                            >
                                                {{ formatCurrency(account.balance) }}
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
                                            Total {{ category.name }}
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
                                            {{ formatCurrency(categoryTotal(category)) }}
                                        </span>
                                    </div>

                                </div>

                            </div>

                        </template>


                        <!-- Current Year Earnings -->

                        <div
                            class="
                                mt-4
                                rounded-lg
                                border
                                border-gray-200
                                bg-gray-50
                                px-4
                                py-3
                                dark:border-gray-700
                                dark:bg-gray-900/50
                            "
                        >
                            <div
                                class="
                                    flex
                                    items-center
                                    justify-between
                                    gap-4
                                "
                            >
                                <div>
                                    <div
                                        class="
                                            text-sm
                                            font-semibold
                                            text-gray-800
                                            dark:text-gray-200
                                        "
                                    >
                                        Current Year Earnings
                                    </div>

                                    <div
                                        class="
                                            mt-0.5
                                            text-xs
                                            text-gray-500
                                            dark:text-gray-400
                                        "
                                    >
                                        Net income
                                    </div>
                                </div>

                                <span
                                    class="
                                        font-mono
                                        text-sm
                                        font-bold
                                        tabular-nums
                                        text-gray-900
                                        dark:text-white
                                    "
                                >
                                    {{ formatCurrency(statistics.current_year_earnings) }}
                                </span>
                            </div>
                        </div>

                    </div>


                    <!-- Total Liabilities & Equity -->

                    <div
                        class="
                            mt-8
                            flex
                            items-center
                            justify-between
                            border-t-2
                            border-gray-300
                            pt-4
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
                            Total Liabilities & Equity
                        </span>

                        <span
                            class="
                                font-mono
                                text-lg
                                font-bold
                                tabular-nums
                                text-gray-900
                                dark:text-white
                            "
                        >
                            {{ formatCurrency(statistics.total_liabilities_equity) }}
                        </span>
                    </div>

                </div>
            </section>

        </div>


        <!-- ========================================================= -->
        <!-- Balance Status -->
        <!-- ========================================================= -->

        <div
            class="
                rounded-xl
                border
                px-6
                py-4
                shadow-sm
            "
            :class="
                statistics.is_balanced
                    ? `
                        border-emerald-200
                        bg-emerald-50
                        dark:border-emerald-900
                        dark:bg-emerald-950/30
                    `
                    : `
                        border-amber-200
                        bg-amber-50
                        dark:border-amber-900
                        dark:bg-amber-950/30
                    `
            "
        >
            <div
                class="
                    flex
                    flex-col
                    gap-2
                    md:flex-row
                    md:items-center
                    md:justify-between
                "
            >
                <div
                    class="
                        flex
                        items-center
                        gap-3
                    "
                >
                    <span
                        class="
                            flex
                            h-8
                            w-8
                            shrink-0
                            items-center
                            justify-center
                            rounded-full
                            text-sm
                            font-bold
                        "
                        :class="
                            statistics.is_balanced
                                ? `
                                    bg-emerald-100
                                    text-emerald-700
                                    dark:bg-emerald-900/50
                                    dark:text-emerald-300
                                `
                                : `
                                    bg-amber-100
                                    text-amber-700
                                    dark:bg-amber-900/50
                                    dark:text-amber-300
                                `
                        "
                    >
                        {{ statistics.is_balanced ? '✓' : '!' }}
                    </span>

                    <div>
                        <div
                            class="
                                text-sm
                                font-semibold
                            "
                            :class="
                                statistics.is_balanced
                                    ? `
                                        text-emerald-800
                                        dark:text-emerald-200
                                    `
                                    : `
                                        text-amber-800
                                        dark:text-amber-200
                                    `
                            "
                        >
                            {{
                                statistics.is_balanced
                                    ? 'Balance Sheet is balanced'
                                    : 'Balance Sheet is not balanced'
                            }}
                        </div>

                        <div
                            class="
                                text-xs
                            "
                            :class="
                                statistics.is_balanced
                                    ? `
                                        text-emerald-600
                                        dark:text-emerald-400
                                    `
                                    : `
                                        text-amber-600
                                        dark:text-amber-400
                                    `
                            "
                        >
                            Difference:
                            {{ formatCurrency(statistics.difference) }}
                        </div>
                    </div>
                </div>

                <div
                    class="
                        text-xs
                        text-gray-500
                        dark:text-gray-400
                    "
                >
                    Assets =
                    Liabilities + Equity
                </div>
            </div>
        </div>

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