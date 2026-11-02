<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    branches: {
        type: Array,
        default: () => [],
    },

    customers: {
        type: Array,
        default: () => [],
    },

    resellers: {
        type: Array,
        default: () => [],
    },

    cashiers: {
        type: Array,
        default: () => [],
    },

    paymentMethods: {
        type: Array,
        default: () => [],
    },

    filters: {
        type: Object,
        default: () => ({}),
    },

    permissions: {
        type: Object,
        default: () => ({}),
    },
})

const form = ref({
    date_from: props.filters.date_from ?? '',
    date_to: props.filters.date_to ?? '',
    branch_id: props.filters.branch_id ?? '',
    source: props.filters.source ?? 'all',
    customer_id: props.filters.customer_id ?? '',
    reseller_id: props.filters.reseller_id ?? '',
    cashier_id: props.filters.cashier_id ?? '',
    payment_method: props.filters.payment_method ?? '',
})

const processing = ref(false)
const errors = ref({})

const showPosFilters = () => {
    return ['all', 'pos'].includes(form.value.source)
}

const showConsignmentFilters = () => {
    return ['all', 'consignment'].includes(form.value.source)
}

const generateReport = () => {
    errors.value = {}

    if (!form.value.date_from) {
        errors.value.date_from = 'Start date is required.'
        return
    }

    if (!form.value.date_to) {
        errors.value.date_to = 'End date is required.'
        return
    }

    if (form.value.date_to < form.value.date_from) {
        errors.value.date_to =
            'End date must be after or equal to start date.'
        return
    }

    processing.value = true

    router.post(
        route('reports.sales.all.generate'),
        {
            date_from: form.value.date_from,
            date_to: form.value.date_to,
            branch_id: form.value.branch_id || null,
            source: form.value.source,
            customer_id: showPosFilters()
                ? form.value.customer_id || null
                : null,
            reseller_id: showConsignmentFilters()
                ? form.value.reseller_id || null
                : null,
            cashier_id: showPosFilters()
                ? form.value.cashier_id || null
                : null,
            payment_method: showPosFilters()
                ? form.value.payment_method || null
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

const resetFilters = () => {
    form.value = {
        date_from: props.filters.date_from ?? '',
        date_to: props.filters.date_to ?? '',
        branch_id: props.filters.branch_id ?? '',
        source: 'all',
        customer_id: '',
        reseller_id: '',
        cashier_id: '',
        payment_method: '',
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
                        All Sales
                    </span>

                    <span class="text-gray-400">/</span>

                    <span class="text-gray-500">
                        Parameters
                    </span>

                </div>

                <h1 class="mt-2 text-2xl font-bold text-gray-900">
                    All Sales
                </h1>

                <p class="mt-1 text-sm text-gray-500">
                    View and analyze posted POS and consignment sales transactions.
                </p>
            </div>

            <!-- Parameters Card -->
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">

                <div class="border-b border-gray-200 px-6 py-4">
                    <h2 class="font-semibold text-gray-900">
                        Report Parameters
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Select the reporting period, branch, and transaction filters.
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
                        </label>

                        <select
                            v-model="form.branch_id"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                        >
                            <option
                                v-if="permissions.viewAllBranches"
                                value=""
                            >
                                All Branches
                            </option>

                            <option
                                v-for="branch in branches"
                                :key="branch.id"
                                :value="String(branch.id)"
                            >
                                {{ branch.code }} - {{ branch.name }}
                            </option>
                        </select>

                        <p
                            v-if="errors.branch_id"
                            class="mt-1 text-xs text-red-500"
                        >
                            {{ errors.branch_id }}
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

                    <!-- Customer -->
                    <div v-if="showPosFilters()">
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Customer
                        </label>

                        <select
                            v-model="form.customer_id"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                        >
                            <option value="">
                                All Customers
                            </option>

                            <option
                                v-for="customer in customers"
                                :key="customer.id"
                                :value="String(customer.id)"
                            >
                                {{ customer.customer_code }} - {{ customer.name }}
                            </option>
                        </select>

                        <p
                            v-if="errors.customer_id"
                            class="mt-1 text-xs text-red-500"
                        >
                            {{ errors.customer_id }}
                        </p>
                    </div>

                    <!-- Reseller -->
                    <div v-if="showConsignmentFilters()">
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Reseller
                        </label>

                        <select
                            v-model="form.reseller_id"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                        >
                            <option value="">
                                All Resellers
                            </option>

                            <option
                                v-for="reseller in resellers"
                                :key="reseller.id"
                                :value="String(reseller.id)"
                            >
                                {{ reseller.reseller_code }} - {{ reseller.name }}
                            </option>
                        </select>

                        <p
                            v-if="errors.reseller_id"
                            class="mt-1 text-xs text-red-500"
                        >
                            {{ errors.reseller_id }}
                        </p>
                    </div>

                    <!-- Cashier -->
                    <div v-if="showPosFilters()">
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Cashier
                        </label>

                        <select
                            v-model="form.cashier_id"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                        >
                            <option value="">
                                All Cashiers
                            </option>

                            <option
                                v-for="cashier in cashiers"
                                :key="cashier.id"
                                :value="String(cashier.id)"
                            >
                                {{ cashier.name }}
                            </option>
                        </select>

                        <p
                            v-if="errors.cashier_id"
                            class="mt-1 text-xs text-red-500"
                        >
                            {{ errors.cashier_id }}
                        </p>
                    </div>

                    <!-- Payment Method -->
                    <div v-if="showPosFilters()">
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Payment Method
                        </label>

                        <select
                            v-model="form.payment_method"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                        >
                            <option value="">
                                All Payment Methods
                            </option>

                            <option
                                v-for="method in paymentMethods"
                                :key="method.value"
                                :value="method.value"
                            >
                                {{ method.label }}
                            </option>
                        </select>

                        <p
                            v-if="errors.payment_method"
                            class="mt-1 text-xs text-red-500"
                        >
                            {{ errors.payment_method }}
                        </p>
                    </div>

                </div>

                <!-- Actions -->
                <div class="flex flex-wrap justify-end gap-3 border-t border-gray-200 px-6 py-4">

                    <button
                        type="button"
                        @click="resetFilters"
                        class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50"
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
                            This report lists posted POS Sales and posted
                            Consignment Settlements. Customer, Cashier, and
                            Payment Method filters apply only to POS.
                            Reseller filters apply only to Consignment.
                        </p>
                    </div>

                </div>
            </div>

        </div>
    </AppLayout>
</template>