<script setup>
import {
    ref,
    computed,
    onMounted,
    onBeforeUnmount,
} from 'vue'

import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({
    branches: {
        type: Array,
        default: () => [],
    },

    resellers: {
        type: Array,
        default: () => [],
    },

    filters: {
        type: Object,
        default: () => ({}),
    },

    canViewAllBranches: {
        type: Boolean,
        default: false,
    },
})

/*
|--------------------------------------------------------------------------
| Initial Branch
|--------------------------------------------------------------------------
*/

const initialBranch = () => {

    const selectedIds = (
        props.filters.branch_ids ?? []
    ).map(id => String(id))

    const accessibleIds = props.branches.map(
        branch => String(branch.id)
    )

    if (props.canViewAllBranches) {

        const allSelected =
            selectedIds.length === 0 ||
            (
                selectedIds.length === accessibleIds.length &&
                accessibleIds.every(id => selectedIds.includes(id))
            )

        if (allSelected) {
            return 'all'
        }
    }

    return selectedIds[0] ?? ''

}

/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/

const form = ref({

    date_from:
        props.filters.date_from ?? '',

    date_to:
        props.filters.date_to ?? '',

    branch_id:
        initialBranch(),

    reseller_id:
        props.filters.reseller_id
            ? String(props.filters.reseller_id)
            : '',

})

const processing = ref(false)

const errors = ref({})

/*
|--------------------------------------------------------------------------
| Branch Options
|--------------------------------------------------------------------------
*/

const branchOptions = computed(() => {

    if (!props.canViewAllBranches) {
        return props.branches
    }

    return [

        {
            id: 'all',
            code: '',
            name: 'All Branches',
        },

        ...props.branches,

    ]

})

/*
|--------------------------------------------------------------------------
| Branch Search
|--------------------------------------------------------------------------
*/

const branchSearch = ref('')

const branchDropdownOpen = ref(false)

const branchWrapper = ref(null)

/*
|--------------------------------------------------------------------------
| Selected Branch
|--------------------------------------------------------------------------
*/

const selectedBranch = computed(() => {

    return branchOptions.value.find(
        branch =>
            String(branch.id) ===
            String(form.value.branch_id)
    ) ?? null

})

const branchInputValue = computed(() => {

    if (branchDropdownOpen.value) {
        return branchSearch.value
    }

    if (!selectedBranch.value) {
        return ''
    }

    if (selectedBranch.value.id === 'all') {
        return 'All Branches'
    }

    return `${selectedBranch.value.code} - ${selectedBranch.value.name}`

})

/*
|--------------------------------------------------------------------------
| Filtered Branches
|--------------------------------------------------------------------------
*/

const filteredBranches = computed(() => {

    const keyword = branchSearch.value
        .trim()
        .toLowerCase()

    return branchOptions.value.filter(branch => {

        if (!keyword) {
            return true
        }

        const code = String(
            branch.code ?? ''
        ).toLowerCase()

        const name = String(
            branch.name ?? ''
        ).toLowerCase()

        return (
            code.includes(keyword) ||
            name.includes(keyword) ||
            `${code} ${name}`.includes(keyword)
        )

    })

})

/*
|--------------------------------------------------------------------------
| Branch Methods
|--------------------------------------------------------------------------
*/

const openBranchDropdown = () => {

    if (!props.canViewAllBranches) {
        return
    }

    branchSearch.value = ''

    branchDropdownOpen.value = true

}

const searchBranch = (event) => {

    branchSearch.value = event.target.value

    branchDropdownOpen.value = true

}

const selectBranch = (branch) => {

    form.value.branch_id = String(branch.id)

    branchSearch.value = ''

    branchDropdownOpen.value = false

}

const toggleBranchDropdown = () => {

    if (!props.canViewAllBranches) {
        return
    }

    if (branchDropdownOpen.value) {

        branchDropdownOpen.value = false

    } else {

        openBranchDropdown()

    }

}

/*
|--------------------------------------------------------------------------
| Reseller Search
|--------------------------------------------------------------------------
*/

const resellerSearch = ref('')

const resellerDropdownOpen = ref(false)

const resellerWrapper = ref(null)

/*
|--------------------------------------------------------------------------
| Selected Reseller
|--------------------------------------------------------------------------
*/

const selectedReseller = computed(() => {

    if (!form.value.reseller_id) {
        return null
    }

    return props.resellers.find(
        reseller =>
            String(reseller.id) ===
            String(form.value.reseller_id)
    ) ?? null

})

const resellerInputValue = computed(() => {

    if (resellerDropdownOpen.value) {
        return resellerSearch.value
    }

    if (!selectedReseller.value) {
        return ''
    }

    return `${selectedReseller.value.reseller_code} - ${selectedReseller.value.name}`

})

/*
|--------------------------------------------------------------------------
| Filtered Resellers
|--------------------------------------------------------------------------
*/

const filteredResellers = computed(() => {

    const keyword = resellerSearch.value
        .trim()
        .toLowerCase()

    return props.resellers.filter(reseller => {

        if (!keyword) {
            return true
        }

        const code = String(
            reseller.reseller_code ?? ''
        ).toLowerCase()

        const name = String(
            reseller.name ?? ''
        ).toLowerCase()

        return (
            code.includes(keyword) ||
            name.includes(keyword) ||
            `${code} ${name}`.includes(keyword)
        )

    })

})

/*
|--------------------------------------------------------------------------
| Reseller Methods
|--------------------------------------------------------------------------
*/

const openResellerDropdown = () => {

    resellerSearch.value = ''

    resellerDropdownOpen.value = true

}

const searchReseller = (event) => {

    resellerSearch.value = event.target.value

    resellerDropdownOpen.value = true

}

const selectReseller = (reseller) => {

    form.value.reseller_id = String(
        reseller.id
    )

    resellerSearch.value = ''

    resellerDropdownOpen.value = false

}

const removeReseller = () => {

    form.value.reseller_id = ''

    resellerSearch.value = ''

    resellerDropdownOpen.value = false

}

const toggleResellerDropdown = () => {

    if (resellerDropdownOpen.value) {

        resellerDropdownOpen.value = false

    } else {

        openResellerDropdown()

    }

}

/*
|--------------------------------------------------------------------------
| Click Outside Dropdowns
|--------------------------------------------------------------------------
*/

const handleClickOutside = (event) => {

    if (
        branchWrapper.value &&
        !branchWrapper.value.contains(event.target)
    ) {

        branchDropdownOpen.value = false

    }

    if (
        resellerWrapper.value &&
        !resellerWrapper.value.contains(event.target)
    ) {

        resellerDropdownOpen.value = false

    }

}

/*
|--------------------------------------------------------------------------
| Generate Report
|--------------------------------------------------------------------------
*/

const generateReport = () => {

    errors.value = {}

    /*
    |--------------------------------------------------------------------------
    | Date Validation
    |--------------------------------------------------------------------------
    */

    if (!form.value.date_from) {

        errors.value.date_from =
            'Start date is required.'

        return

    }

    if (!form.value.date_to) {

        errors.value.date_to =
            'End date is required.'

        return

    }

    if (form.value.date_to < form.value.date_from) {

        errors.value.date_to =
            'End date must be after or equal to start date.'

        return

    }

    /*
    |--------------------------------------------------------------------------
    | Branch Validation
    |--------------------------------------------------------------------------
    */

    if (!form.value.branch_id) {

        errors.value.branch_ids =
            'Branch is required.'

        return

    }

    /*
    |--------------------------------------------------------------------------
    | Resolve Branch IDs
    |--------------------------------------------------------------------------
    */

    const branchIds = form.value.branch_id === 'all'

        ? props.branches.map(branch => branch.id)

        : [Number(form.value.branch_id)]

    if (!branchIds.length) {

        errors.value.branch_ids =
            'No accessible branch available.'

        return

    }

    /*
    |--------------------------------------------------------------------------
    | Close Dropdowns
    |--------------------------------------------------------------------------
    */

    branchDropdownOpen.value = false

    resellerDropdownOpen.value = false

    /*
    |--------------------------------------------------------------------------
    | Generate Request
    |--------------------------------------------------------------------------
    */

    processing.value = true

    router.post(

        route('reports.sales.consignment-sales.generate'),

        {

            date_from:
                form.value.date_from,

            date_to:
                form.value.date_to,

            branch_ids:
                branchIds,

            reseller_id:
                form.value.reseller_id
                    ? Number(form.value.reseller_id)
                    : null,

        },

        {

            preserveScroll: true,

            onError: (response) => {

                errors.value = response

            },

            onFinish: () => {

                processing.value = false

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

    form.value = {

        date_from:
            props.filters.date_from ?? '',

        date_to:
            props.filters.date_to ?? '',

        branch_id:
            initialBranch(),

        reseller_id: '',

    }

    branchSearch.value = ''

    resellerSearch.value = ''

    branchDropdownOpen.value = false

    resellerDropdownOpen.value = false

    errors.value = {}

}

/*
|--------------------------------------------------------------------------
| Lifecycle
|--------------------------------------------------------------------------
*/

onMounted(() => {

    document.addEventListener(
        'mousedown',
        handleClickOutside
    )

})

onBeforeUnmount(() => {

    document.removeEventListener(
        'mousedown',
        handleClickOutside
    )

})

</script>

<template>

    <AppLayout>

        <div class="space-y-6 p-6">

            <!-- Header -->
<div class="space-y-2">

    <!-- Breadcrumb -->
    <div class="flex flex-wrap items-center gap-2 text-sm">

        <button
            type="button"
            @click="router.visit(route('reports.index'))"
            class="text-gray-500 transition hover:text-blue-600"
        >
            Reports
        </button>

        <span class="text-gray-400">/</span>

        <button
            type="button"
            @click="router.visit(route('reports.index', { category: 'Sales' }))"
            class="text-gray-500 transition hover:text-blue-600"
        >
            Sales
        </button>

        <span class="text-gray-400">/</span>

        <span class="font-medium text-gray-900">
            Consignment Sales
        </span>

        <span class="text-gray-400">/</span>

        <span class="text-gray-500">
            Parameters
        </span>

    </div>

    <!-- Page Title -->
    <div>
        <h1 class="text-2xl font-bold text-gray-900">
            Consignment Sales
        </h1>

        <p class="mt-1 text-sm text-gray-500">
            Analyze consignment sales performance, cost of goods sold,
            and gross profit by reseller.
        </p>
    </div>

</div>

            <!-- Parameters Card -->
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">

                <div class="border-b border-gray-200 px-6 py-4">

                    <h2 class="font-semibold text-gray-900">
                        Report Parameters
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Select the reporting period, branch, and reseller.
                    </p>

                </div>

                <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">

                    <!-- Date From -->
                    <div>

                        <label class="mb-2 block text-sm font-medium text-gray-700">

                            Date From

                            <span class="text-red-500">*</span>

                        </label>

                        <input
                            v-model="form.date_from"
                            type="date"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                        />

                        <p
                            v-if="errors.date_from"
                            class="mt-1 text-xs text-red-500"
                        >
                            {{ errors.date_from }}
                        </p>

                    </div>

                    <!-- Date To -->
                    <div>

                        <label class="mb-2 block text-sm font-medium text-gray-700">

                            Date To

                            <span class="text-red-500">*</span>

                        </label>

                        <input
                            v-model="form.date_to"
                            type="date"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                        />

                        <p
                            v-if="errors.date_to"
                            class="mt-1 text-xs text-red-500"
                        >
                            {{ errors.date_to }}
                        </p>

                    </div>

                    <!-- Branch Searchable -->
                    <div
                        ref="branchWrapper"
                        class="relative"
                    >

                        <label class="mb-2 block text-sm font-medium text-gray-700">

                            Branch

                            <span class="text-red-500">*</span>

                        </label>

                        <div class="relative">

                            <input
                                :value="branchInputValue"
                                type="text"
                                autocomplete="off"
                                :readonly="!canViewAllBranches"
                                :placeholder="canViewAllBranches
                                    ? 'Search branch by code or name...'
                                    : 'Assigned branch'"
                                @focus="openBranchDropdown"
                                @input="searchBranch"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 pr-10 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 read-only:bg-gray-100"
                            />

                            <button
                                type="button"
                                :disabled="!canViewAllBranches"
                                @click="toggleBranchDropdown"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 transition hover:text-blue-600 disabled:cursor-not-allowed"
                            >
                                {{ branchDropdownOpen ? '▲' : '▼' }}
                            </button>

                        </div>

                        <!-- Branch Dropdown -->
                        <div
                            v-if="branchDropdownOpen"
                            class="absolute left-0 right-0 z-30 mt-2 max-h-64 overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-xl"
                        >

                            <button
                                v-for="branch in filteredBranches"
                                :key="branch.id"
                                type="button"
                                @click="selectBranch(branch)"
                                class="flex w-full items-center justify-between px-4 py-3 text-left text-sm text-slate-700 transition hover:bg-blue-600 hover:text-white"
                            >

                                <span>

                                    <template v-if="branch.id === 'all'">

                                        {{ branch.name }}

                                    </template>

                                    <template v-else>

                                        <span class="font-semibold">
                                            {{ branch.code }}
                                        </span>

                                        <span class="mx-1 text-gray-400">
                                            -
                                        </span>

                                        {{ branch.name }}

                                    </template>

                                </span>

                                <span class="text-xs">
                                    Select
                                </span>

                            </button>

                            <div
                                v-if="filteredBranches.length === 0"
                                class="px-4 py-6 text-center"
                            >

                                <div class="mb-1 text-2xl">
                                    🔍
                                </div>

                                <p class="text-sm font-medium text-gray-700">
                                    No branch found
                                </p>

                                <p class="mt-1 text-xs text-gray-400">
                                    Try another keyword.
                                </p>

                            </div>

                        </div>

                        <p class="mt-2 text-xs text-gray-500">

                            <template v-if="canViewAllBranches">

                                Search and select All Branches or a specific branch.

                            </template>

                            <template v-else>

                                Your assigned branch is selected automatically.

                            </template>

                        </p>

                        <p
                            v-if="errors.branch_ids"
                            class="mt-1 text-xs text-red-500"
                        >
                            {{ errors.branch_ids }}
                        </p>

                    </div>

                    <!-- Reseller Searchable -->
                    <div
                        ref="resellerWrapper"
                        class="relative"
                    >

                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Reseller
                        </label>

                        <div class="relative">

                            <input
                                :value="resellerInputValue"
                                type="text"
                                autocomplete="off"
                                placeholder="Search reseller by code or name..."
                                @focus="openResellerDropdown"
                                @input="searchReseller"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 pr-16 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                            />

                            <button
                                v-if="selectedReseller"
                                type="button"
                                @click="removeReseller"
                                class="absolute right-10 top-1/2 -translate-y-1/2 text-gray-400 transition hover:text-red-600"
                                title="Clear reseller"
                            >
                                ×
                            </button>

                            <button
                                type="button"
                                @click="toggleResellerDropdown"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 transition hover:text-blue-600"
                            >
                                {{ resellerDropdownOpen ? '▲' : '▼' }}
                            </button>

                        </div>

                        <!-- Reseller Dropdown -->
                        <div
                            v-if="resellerDropdownOpen"
                            class="absolute left-0 right-0 z-30 mt-2 max-h-64 overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-xl"
                        >

                            <button
                                v-for="reseller in filteredResellers"
                                :key="reseller.id"
                                type="button"
                                @click="selectReseller(reseller)"
                                class="flex w-full items-center justify-between px-4 py-3 text-left text-sm text-slate-700 transition hover:bg-blue-600 hover:text-white"
                            >

                                <span>

                                    <span class="font-semibold">
                                        {{ reseller.reseller_code }}
                                    </span>

                                    <span class="mx-1 text-gray-400">
                                        -
                                    </span>

                                    {{ reseller.name }}

                                </span>

                                <span class="text-xs">
                                    Select
                                </span>

                            </button>

                            <div
                                v-if="filteredResellers.length === 0"
                                class="px-4 py-6 text-center"
                            >

                                <div class="mb-1 text-2xl">
                                    🔍
                                </div>

                                <p class="text-sm font-medium text-gray-700">
                                    No reseller found
                                </p>

                                <p class="mt-1 text-xs text-gray-400">
                                    Try another keyword.
                                </p>

                            </div>

                        </div>

                        <p class="mt-2 text-xs text-gray-500">

                            Search and select one reseller.
                            Leave empty to include all resellers.

                        </p>

                        <p
                            v-if="errors.reseller_id"
                            class="mt-1 text-xs text-red-500"
                        >
                            {{ errors.reseller_id }}
                        </p>

                    </div>

                </div>

                <!-- Actions -->
                <div class="flex flex-wrap justify-end gap-3 border-t border-gray-200 px-6 py-4">

                    <button
                        type="button"
                        @click="resetFilters"
                        :disabled="processing"
                        class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        Reset
                    </button>

                    <button
                        type="button"
                        @click="generateReport"
                        :disabled="processing"
                        class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >

                        {{ processing ? 'Generating...' : 'Generate Report' }}

                    </button>

                </div>

            </div>

            <!-- Information -->
            <div class="rounded-lg border border-blue-200 bg-blue-50 p-4">

                <div class="flex gap-3">

                    <div class="text-blue-600">

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >

                            <path
                                fill-rule="evenodd"
                                d="M18 10A8 8 0 112 10a8 8 0 0116 0zm-8-4a1 1 0 100 2 1 1 0 000-2zm-1 4a1 1 0 000 2h.01a1 1 0 100-2H9z"
                                clip-rule="evenodd"
                            />

                        </svg>

                    </div>

                    <div>

                        <p class="text-sm font-semibold text-blue-900">
                            Report Information
                        </p>

                        <p class="mt-1 text-sm text-blue-800">

                            This report includes posted Consignment Settlements
                            within the selected reporting period and branches.

                            Sales are grouped by reseller. Gross Sales are
                            calculated from settlement details, while COGS
                            uses the actual inventory movement costs.

                            Gross Profit is calculated as Gross Sales minus
                            COGS. Gross Profit Margin and Sales Contribution
                            are calculated using the complete filtered data,
                            not only the currently displayed table page.

                        </p>

                    </div>

                </div>

            </div>

        </div>

    </AppLayout>

</template>