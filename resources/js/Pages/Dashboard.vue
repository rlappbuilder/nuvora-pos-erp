<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">
                        Dashboard
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        Welcome to Nuvora ERP
                    </p>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

                <!-- Statistik -->
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">

                    <div class="rounded-2xl bg-white p-6 shadow-sm border border-gray-100">
                        <p class="text-sm text-gray-500">
                            Total Products
                        </p>

                        <h3 class="mt-3 text-3xl font-bold text-blue-600">
                            0
                        </h3>

                        <p class="mt-2 text-xs text-gray-400">
                            Registered Products
                        </p>
                    </div>

                    <div class="rounded-2xl bg-white p-6 shadow-sm border border-gray-100">
                        <p class="text-sm text-gray-500">
                            Total Resellers
                        </p>

                        <h3 class="mt-3 text-3xl font-bold text-emerald-600">
                            0
                        </h3>

                        <p class="mt-2 text-xs text-gray-400">
                            Active Resellers
                        </p>
                    </div>

                    <div class="rounded-2xl bg-white p-6 shadow-sm border border-gray-100">
                        <p class="text-sm text-gray-500">
                            Warehouses
                        </p>

                        <h3 class="mt-3 text-3xl font-bold text-indigo-600">
                            0
                        </h3>

                        <p class="mt-2 text-xs text-gray-400">
                            Active Warehouses
                        </p>
                    </div>

                    <div class="rounded-2xl bg-white p-6 shadow-sm border border-gray-100">
                        <p class="text-sm text-gray-500">
                            Employees
                        </p>

                        <h3 class="mt-3 text-3xl font-bold text-orange-500">
                            0
                        </h3>

                        <p class="mt-2 text-xs text-gray-400">
                            Registered Employees
                        </p>
                    </div>

                </div>

                <!-- Welcome -->
                <div class="mt-6 rounded-2xl bg-white p-6 shadow-sm border border-gray-100">

                    <h3 class="text-lg font-semibold text-gray-800">
                        Welcome to Nuvora ERP
                    </h3>

                    <p class="mt-3 text-gray-600">
                        Integrated ERP, POS, Inventory, Reseller, Consignment,
                        Accounting and Financial Management System.
                    </p>

                </div>

                <!-- Quick Menu -->
                <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">

                    <div class="rounded-2xl bg-white p-6 shadow-sm border border-gray-100 hover:shadow-md transition">
                        <h4 class="font-semibold text-gray-800">
                            Master Data
                        </h4>

                        <p class="mt-2 text-sm text-gray-500">
                            Categories, Brands, Units, Colors, Sizes
                        </p>
                    </div>

                    <div class="rounded-2xl bg-white p-6 shadow-sm border border-gray-100 hover:shadow-md transition">
                        <h4 class="font-semibold text-gray-800">
                            Inventory
                        </h4>

                        <p class="mt-2 text-sm text-gray-500">
                            Stock, Goods Receipt, Transfers
                        </p>
                    </div>

                    <div class="rounded-2xl bg-white p-6 shadow-sm border border-gray-100 hover:shadow-md transition">
                        <h4 class="font-semibold text-gray-800">
                            Sales
                        </h4>

                        <p class="mt-2 text-sm text-gray-500">
                            Cash Sales, Credit Sales, Reseller Sales
                        </p>
                    </div>

                    <div class="rounded-2xl bg-white p-6 shadow-sm border border-gray-100 hover:shadow-md transition">
                        <h4 class="font-semibold text-gray-800">
                            Accounting
                        </h4>

                        <p class="mt-2 text-sm text-gray-500">
                            Journal, Ledger, Profit & Loss, Balance Sheet
                        </p>
                    </div>

                </div>

            </div>
        </div>
    </AppLayout>
</template>