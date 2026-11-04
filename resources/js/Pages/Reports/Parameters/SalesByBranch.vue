<script setup>
import {
    ref,
    computed,
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

    date_from: props.filters.date_from ?? '',

    date_to: props.filters.date_to ?? '',

    branch_id: initialBranch(),

    source: props.filters.source ?? 'all',

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
| Generate Report
|--------------------------------------------------------------------------
*/

const generateReport = () => {

    errors.value = {}

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

    processing.value = true

    router.post(

        route('reports.sales.by-branch.generate'),

        {
            date_from: form.value.date_from,

            date_to: form.value.date_to,

            branch_ids: branchIds,

            source: form.value.source,
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

        date_from: props.filters.date_from ?? '',

        date_to: props.filters.date_to ?? '',

        branch_id: initialBranch(),

        source: 'all',

    }

    errors.value = {}

}

</script>

<template>

    <AppLayout>

        <div class="space-y-6 p-6">

            <!-- Header -->
            <div>

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

                    <span class="font-semibold text-gray-900">
                        Sales by Branch
                    </span>

                    <span class="text-gray-400">/</span>

                    <span class="text-gray-500">
                        Parameters
                    </span>

                </div>

                <h1 class="mt-2 text-2xl font-bold text-gray-900">
                    Sales by Branch
                </h1>

                <p class="mt-1 text-sm text-gray-500">
                    Analyze sales performance across branches, including
                    POS sales, consignment sales, discounts, cost of goods
                    sold, and gross profit.
                </p>

            </div>

            <!-- Parameters Card -->
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">

                <div class="border-b border-gray-200 px-6 py-4">

                    <h2 class="font-semibold text-gray-900">
                        Report Parameters
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Select the reporting period, branch, and sales source.
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

                    <!-- Branch -->
                    <div>

                        <label class="mb-2 block text-sm font-medium text-gray-700">

                            Branch

                            <span class="text-red-500">*</span>

                        </label>

                        <select
                            v-model="form.branch_id"
                            :disabled="!canViewAllBranches"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 disabled:bg-gray-100"
                        >

                            <option value="" disabled>
                                Select Branch
                            </option>

                            <option
                                v-for="branch in branchOptions"
                                :key="branch.id"
                                :value="String(branch.id)"
                            >

                                {{ branch.id === 'all'
                                    ? branch.name
                                    : `${branch.code} - ${branch.name}`
                                }}

                            </option>

                        </select>

                        <p class="mt-1 text-xs text-gray-500">

                            <template v-if="canViewAllBranches">

                                Select All Branches or a specific branch.

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

                    <!-- Sales Source -->
                    <div>

                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Sales Source
                        </label>

                        <select
                            v-model="form.source"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                        >

                            <option value="all">
                                All Sources
                            </option>

                            <option value="pos">
                                POS
                            </option>

                            <option value="consignment">
                                Consignment
                            </option>

                        </select>

                        <p
                            v-if="errors.source"
                            class="mt-1 text-xs text-red-500"
                        >
                            {{ errors.source }}
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

                            This report includes posted POS Sales and posted
                            Consignment Settlements within the selected
                            reporting period.

                            Sales are grouped by branch, with POS Sales and
                            Consignment Sales displayed separately.
                            Consignment Sales are further grouped by reseller.

                            Each branch includes a subtotal, followed by the
                            overall Grand Total.

                            Gross sales, discounts, net sales, COGS, and
                            gross profit are calculated for each sales group.

                        </p>

                    </div>

                </div>

            </div>

        </div>

    </AppLayout>

</template>