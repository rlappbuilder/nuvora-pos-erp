<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-gray-800">
                    Dashboard
                </h2>
                <p class="text-sm text-gray-500">
                    Welcome to Nufora ERP
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Statistic Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <p class="text-gray-500 text-sm">Products</p>
                    <h3 class="text-3xl font-bold text-blue-600 mt-2">
                        0
                    </h3>
                </div>

                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <p class="text-gray-500 text-sm">Resellers</p>
                    <h3 class="text-3xl font-bold text-green-600 mt-2">
                        0
                    </h3>
                </div>

                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <p class="text-gray-500 text-sm">Warehouses</p>
                    <h3 class="text-3xl font-bold text-indigo-600 mt-2">
                        0
                    </h3>
                </div>

                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <p class="text-gray-500 text-sm">Employees</p>
                    <h3 class="text-3xl font-bold text-orange-500 mt-2">
                        0
                    </h3>
                </div>

            </div>

            <!-- Welcome -->
            <div class="mt-6 bg-white rounded-2xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800">
                    Welcome to Nufora ERP
                </h3>

                <p class="mt-2 text-gray-600">
                    Integrated ERP, POS, Inventory, Consignment,
                    Accounting and Reseller Management System.
                </p>
            </div>

        </div>
    </div>
</x-app-layout>