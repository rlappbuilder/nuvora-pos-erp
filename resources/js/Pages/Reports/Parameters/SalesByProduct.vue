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

    products: {
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

    product_ids: (
        props.filters.product_ids ?? []
    ).map(String),

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
| Product Search
|--------------------------------------------------------------------------
*/

const productSearch = ref('')

const productDropdownOpen = ref(false)

const productWrapper = ref(null)

const selectedProducts = computed(() => {

    return form.value.product_ids
        .map(id => {

            return props.products.find(
                product => String(product.id) === String(id)
            )

        })
        .filter(Boolean)

})

const filteredProducts = computed(() => {

    const keyword = productSearch.value
        .trim()
        .toLowerCase()

    return props.products.filter(product => {

        const alreadySelected = form.value.product_ids.includes(
            String(product.id)
        )

        if (alreadySelected) {
            return false
        }

        if (!keyword) {
            return true
        }

        const code = String(
            product.code ?? ''
        ).toLowerCase()

        const sku = String(
            product.sku ?? ''
        ).toLowerCase()

        const name = String(
            product.name ?? ''
        ).toLowerCase()

        return (
            code.includes(keyword) ||
            sku.includes(keyword) ||
            name.includes(keyword) ||
            `${code} ${sku} ${name}`.includes(keyword)
        )

    })

})

/*
|--------------------------------------------------------------------------
| Product Methods
|--------------------------------------------------------------------------
*/

const openProductDropdown = () => {

    productDropdownOpen.value = true

}

const selectProduct = (product) => {

    const productId = String(product.id)

    if (
        !form.value.product_ids.includes(productId)
    ) {

        form.value.product_ids.push(productId)

    }

    productSearch.value = ''

    productDropdownOpen.value = true

}

const removeProduct = (productId) => {

    form.value.product_ids =
        form.value.product_ids.filter(
            id => String(id) !== String(productId)
        )

}

const clearProducts = () => {

    form.value.product_ids = []

    productSearch.value = ''

}

/*
|--------------------------------------------------------------------------
| Click Outside Product Dropdown
|--------------------------------------------------------------------------
*/

const handleClickOutside = (event) => {

    if (
        productWrapper.value &&
        !productWrapper.value.contains(event.target)
    ) {

        productDropdownOpen.value = false

    }

}

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

        route('reports.sales.by-product.generate'),

        {
            date_from: form.value.date_from,

            date_to: form.value.date_to,

            branch_ids: branchIds,

            source: form.value.source,

            product_ids: form.value.product_ids,
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

        product_ids: [],

    }

    productSearch.value = ''

    productDropdownOpen.value = false

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
                        Sales by Product
                    </span>

                    <span class="text-gray-400">/</span>

                    <span class="text-gray-500">
                        Parameters
                    </span>

                </div>

                <h1 class="mt-2 text-2xl font-bold text-gray-900">
                    Sales by Product
                </h1>

                <p class="mt-1 text-sm text-gray-500">
                    Analyze sales performance, discounts, cost of goods sold,
                    and gross profit by product variant.
                </p>

            </div>

            <!-- Parameters Card -->
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">

                <div class="border-b border-gray-200 px-6 py-4">

                    <h2 class="font-semibold text-gray-900">
                        Report Parameters
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Select the reporting period, branch, sales source,
                        and products.
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

                    <!-- Product -->
                    <div
                        ref="productWrapper"
                        class="relative md:col-span-2"
                    >

                        <label class="mb-2 block text-sm font-medium text-gray-700">

                            Product

                        </label>

                        <!-- Selected Product Badges -->
                        <div
                            v-if="selectedProducts.length"
                            class="mb-3 flex flex-wrap gap-2"
                        >

                            <span
                                v-for="product in selectedProducts"
                                :key="product.id"
                                class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-3 py-1.5 text-sm font-medium text-blue-700"
                            >

                                {{ product.code }} - {{ product.name }}

                                <button
                                    type="button"
                                    @click="removeProduct(product.id)"
                                    class="flex h-4 w-4 items-center justify-center rounded-full text-blue-500 transition hover:bg-blue-200 hover:text-red-600"
                                    :aria-label="`Remove ${product.name}`"
                                >
                                    ×
                                </button>

                            </span>

                            <button
                                type="button"
                                @click="clearProducts"
                                class="px-2 text-xs font-medium text-red-500 transition hover:text-red-700"
                            >
                                Clear All
                            </button>

                        </div>

                        <!-- Search Input -->
                        <div class="relative">

                            <input
                                v-model="productSearch"
                                type="text"
                                autocomplete="off"
                                placeholder="Search product by code, SKU, or name..."
                                @focus="openProductDropdown"
                                @input="openProductDropdown"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 pr-10 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                            />

                            <button
                                type="button"
                                @click="productDropdownOpen = !productDropdownOpen"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 transition hover:text-blue-600"
                            >

                                {{ productDropdownOpen ? '▲' : '▼' }}

                            </button>

                        </div>

                        <!-- Product Dropdown -->
                        <div
                            v-if="productDropdownOpen"
                            class="absolute left-0 right-0 z-30 mt-2 max-h-64 overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-xl"
                        >

                            <button
                                v-for="product in filteredProducts"
                                :key="product.id"
                                type="button"
                                @click="selectProduct(product)"
                                class="flex w-full items-center justify-between px-4 py-3 text-left text-sm text-slate-700 transition hover:bg-blue-600 hover:text-white"
                            >

                                <span>

                                    <span class="font-semibold">
                                        {{ product.code }}
                                    </span>

                                    <span class="mx-1 text-gray-400">
                                        -
                                    </span>

                                    {{ product.name }}

                                    <span
                                        v-if="product.sku"
                                        class="ml-2 text-xs text-gray-400"
                                    >
                                        SKU: {{ product.sku }}
                                    </span>

                                </span>

                                <span class="text-xs">
                                    Select
                                </span>

                            </button>

                            <div
                                v-if="filteredProducts.length === 0"
                                class="px-4 py-6 text-center"
                            >

                                <div class="mb-1 text-2xl">
                                    🔍
                                </div>

                                <p class="text-sm font-medium text-gray-700">
                                    No product found
                                </p>

                                <p class="mt-1 text-xs text-gray-400">
                                    Try another keyword.
                                </p>

                            </div>

                        </div>

                        <p class="mt-2 text-xs text-gray-500">

                            Search and select multiple products by code, SKU,
                            or name. Selecting a product includes all its
                            variants in the report. Leave empty to include
                            all products.

                        </p>

                        <p
                            v-if="errors.product_ids"
                            class="mt-1 text-xs text-red-500"
                        >
                            {{ errors.product_ids }}
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
                            Consignment Settlements. Sales are grouped by
                            Product Variant, with each variant displayed as
                            a separate row using its SKU.

                            Discounts, net sales, COGS, and gross profit are
                            calculated for each product variant.

                            Selecting a product includes all its variants.
                            You can select multiple products or leave the
                            selection empty to include all products.

                        </p>

                    </div>

                </div>

            </div>

        </div>

    </AppLayout>

</template>