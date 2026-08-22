<script setup>

import { ref, reactive, computed,watch,toRefs,} from 'vue'
import {router,} from '@inertiajs/vue3'
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
import { currency, formatDate,} from '@/Utils'
import FlatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'
import { LoadingOverlay,} from '@/Components/Feedback'
const props = defineProps({

    stockBalance: {

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

            total_products: 0,

            total_on_hand: 0,

            total_reserved: 0,

            total_available: 0,

            total_stock_value: 0,

        }),

    },


    branches: {

        type: Array,

        default: () => [],

    },


    warehouses: {

        type: Array,

        default: () => [],

    },


    variants: {

        type: Array,

        default: () => [],

    },


    units: {

        type: Array,

        default: () => [],

    },


    filters: {

        type: Object,

        default: () => ({}),

    },

})


const {
    stockBalance,
    statistics,
} = toRefs(props)


const pageTitle =
    computed(
        () => 'Stock Balance'
    )

const loading =
    ref(false)

const filters = reactive({

    search:
        props.filters?.search ?? '',

    date_from:
        props.filters?.date_from ?? '',

    date_to:
        props.filters?.date_to ?? '',

    branch_id:
        props.filters?.branch_id ?? '',

    warehouse_id:
        props.filters?.warehouse_id ?? '',

    product_variant_id:
        props.filters?.product_variant_id ?? '',

    unit_id:
        props.filters?.unit_id ?? '',

    per_page:
        props.filters?.per_page ?? 10,

})

let debounceTimer =
    null


function loadData()
{

    router.get(

        route(
            'stock-balance.index'
        ),

        filters,

        {

            preserveState:
                true,

            preserveScroll:
                true,

            replace:
                true,

        }

    )

}

watch(

    () =>
        filters.search,

    () => {

        clearTimeout(
            debounceTimer
        )


        debounceTimer =
            setTimeout(

                () => {

                    loadData()

                },

                500

            )

    }

)


watch(

    () =>
        filters.branch_id,

    () => {

        loadData()

    }

)


watch(

    () =>
        filters.warehouse_id,

    () => {

        loadData()

    }

)

watch(

    () =>
        filters.product_variant_id,

    () => {

        loadData()

    }

)

watch(

    () =>
        filters.unit_id,

    () => {

        loadData()

    }

)

watch(

    () =>
        filters.per_page,

    () => {

        loadData()

    }

)


function refresh()
{

    Object.assign(
    filters,
    {
        search: '',
        date_from: '',
        date_to: '',
        branch_id: '',
        warehouse_id: '',
        product_variant_id: '',
        unit_id: '',
        per_page: 10,
    }
)

dateRange.value = ''

loadData()

}

const sort =
    ref(

        props.filters?.sort_by
        ?? 'id'

    )


const direction =
    ref(

        props.filters?.sort_direction
        ?? 'desc'

    )


function sortBy(
    column
)
{

    if (
        sort.value === column
    ) {

        direction.value =

            direction.value === 'asc'

                ? 'desc'

                : 'asc'

    }

    else {

        sort.value =
            column

        direction.value =
            'asc'

    }


    router.get(

        route(
            'stock-balance.index'
        ),

        {

            search:
                filters.search,

            branch_id:
                filters.branch_id,

            warehouse_id:
                filters.warehouse_id,

            product_variant_id:
                filters.product_variant_id,

            unit_id:
                filters.unit_id,

            per_page:
                filters.per_page,

            sort_by:
                sort.value,

            sort_direction:
                direction.value,

        },

        {

            preserveState:
                true,

            preserveScroll:
                true,

            replace:
                true,

        }

    )

}

const filteredWarehouses =
    computed(
        () => {

            if (
                !filters.branch_id
            ) {

                return props.warehouses

            }


            return props.warehouses.filter(

                warehouse =>

                    Number(
                        warehouse.branch_id
                    )

                    ===

                    Number(
                        filters.branch_id
                    )

            )

        }
    )


function formatNumber(
    value
)
{

    return new Intl.NumberFormat(

        'id-ID',

        {

            minimumFractionDigits:
                0,

            maximumFractionDigits:
                2,

        }

    ).format(

        Number(
            value
            ?? 0
        )

    )

}

function formatCurrency(
    value
)
{

    return currency(
        Number(
            value
            ?? 0
        )
    )

}


const selectedItem =
    ref(null)


const showViewModal =
    ref(false)

const activeViewTab = ref('overview')
const movementLoading = ref(false)

const movements = ref([])
async function loadMovements()
{
    if (!selectedItem.value) {
        return
    }

    movementLoading.value = true

    try {

        const params = new URLSearchParams({

            product_variant_id:
                selectedItem.value.product_variant_id,

            branch_id:
                selectedItem.value.branch.id,

            warehouse_id:
                selectedItem.value.warehouse.id,

        })


        const unitId =
            selectedItem.value.units?.[0]?.unit_id


        if (unitId) {

            params.append(
                'unit_id',
                unitId
            )

        }


        const response =
            await fetch(
                `${route('stock-balance.movements')}?${params.toString()}`,
                {
                    headers: {
                        'Accept':
                            'application/json',
                    },
                }
            )


        if (!response.ok) {

            throw new Error(
                'Failed to load inventory movements.'
            )

        }


        const result =
            await response.json()


        movements.value =
            result.data ?? []

    }
    catch (error) {

        console.error(
            'Failed to load movements:',
            error
        )

        movements.value = []

    }
    finally {

        movementLoading.value =
            false

    }
}
function openMovementTab()
{
    activeViewTab.value = 'movement'

    if (!movements.value.length) {
        loadMovements()
    }
}
function openView(item)
{
    selectedItem.value = item
    activeViewTab.value = 'overview'
    showViewModal.value = true
}


function closeView()
{
    showViewModal.value = false
    selectedItem.value = null
    activeViewTab.value = 'overview'
}
const dateRange = ref('')

function formatDateForFilter(date) {

    const year =
        date.getFullYear()

    const month =
        String(
            date.getMonth() + 1
        ).padStart(2, '0')

    const day =
        String(
            date.getDate()
        ).padStart(2, '0')

    return `${year}-${month}-${day}`
}


function handleDateRangeChange(
    selectedDates
) {

    if (!selectedDates.length) {

        filters.date_from = ''

        filters.date_to = ''

        loadData()

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


    loadData()

}

</script>

<template>
<AppLayout>

    <div class="space-y-6">

           <!-- ===================================================== -->
            <!-- Statistics -->
            <!-- ===================================================== -->

            <div
                class="
                    grid
                    grid-cols-1
                    gap-4
                    md:grid-cols-2
                    xl:grid-cols-4
                "
            >

                <StatsCard
                    title="Total Products"
                    :value="
                        statistics?.total_products
                        ?? 0
                    "
                    icon="📦"
                />


                <StatsCard
                    title="Total On Hand"
                    :value="
                        formatNumber(
                            statistics?.total_on_hand
                            ?? 0
                        )
                    "
                    icon="📊"
                />


                <StatsCard
                    title="Total Available"
                    :value="
                        formatNumber(
                            statistics?.total_available
                            ?? 0
                        )
                    "
                    icon="✅"
                />


                <StatsCard
                    title="Stock Value"
                    :value="
                        formatCurrency(
                            statistics?.total_stock_value
                            ?? 0
                        )
                    "
                    icon="💰"
                />

            </div>

        </div>

        <Card class="mt-4">
       <!-- ===================================================== -->
        <!-- Toolbar -->
    <!-- ===================================================== -->

<div
    class="
        flex
        flex-col
        gap-3
    "
>

    <!-- ================================================= -->
    <!-- Row 1 -->
    <!-- ================================================= -->

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

        <!-- Left -->

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

            <!-- Search -->

            <input
                v-model="filters.search"
                type="text"
                placeholder="Search product, SKU..."
                class="
                    w-full
                    rounded-xl
                    border
                    border-gray-300
                    px-4
                    py-2.5
                    lg:w-90
                "
            />


            <!-- Date Range -->

            <div
                class="
                    w-full
                    lg:w-72
                "
            >

                <FlatPickr
                    v-model="dateRange"
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
                    "
                    @on-change="
                        handleDateRangeChange
                    "
                />

            </div>

        </div>


        <!-- Right -->

        <div
            class="
                flex
                w-full
                flex-col
                gap-2
                lg:w-auto
                lg:flex-row
                lg:items-center
            "
        >

            <!-- Refresh -->

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
    <!-- Row 2 -->
    <!-- ================================================= -->

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

        <!-- Left Filters -->

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

            <!-- Branch -->

            <SearchableSelect
                v-model="filters.branch_id"
                :options="branches"
                label="label"
                value-key="id"
                placeholder="All Branches"
            />


            <!-- Warehouse -->

            <SearchableSelect
                v-model="filters.warehouse_id"
                :options="filteredWarehouses"
                label="label"
                value-key="id"
                placeholder="All Warehouses"
            />


            <!-- Product / Variant -->

            <SearchableSelect
                v-model="
                    filters.product_variant_id
                "
                :options="variants"
                label="label"
                value-key="id"
                placeholder="All Products"
            />


            <!-- Unit -->

            <SearchableSelect
                v-model="filters.unit_id"
                :options="units"
                label="label"
                value-key="id"
                placeholder="All Units"
            />

        </div>

    </div>

</div>
           

            <!-- ===================================================== -->
            <!-- Table -->
            <!-- ===================================================== -->

            <div class="mt-6">

                <!-- Loading -->

                <LoadingOverlay
                    :show="loading"
                    text="Loading Balance Stock..."
                />
<!-- ===================================================== -->
<!-- Data -->
<!-- ===================================================== -->

<DataTable
    v-if="stockBalance?.data?.length"
    sticky-header
    max-height="650px"
>

    <DataTableHead sticky>

        <!-- Product -->

        <DataTableHeaderCell
            sortable
            column="product"
            :sort="sort"
            :direction="direction"
            @sort="sortBy"
            width="280px"
        >
            Product
        </DataTableHeaderCell>


        <!-- Location -->

        <DataTableHeaderCell
            width="220px"
        >
            Branch / Warehouse
        </DataTableHeaderCell>


        <!-- Units -->

        <DataTableHeaderCell
            width="110px"
            align="center"
        >
            Units
        </DataTableHeaderCell>


        <!-- On Hand -->

        <DataTableHeaderCell
            sortable
            column="on_hand_qty"
            :sort="sort"
            :direction="direction"
            @sort="sortBy"
            width="140px"
            align="right"
        >
            On Hand
        </DataTableHeaderCell>


        <!-- Reserved -->

        <DataTableHeaderCell
            sortable
            column="reserved_qty"
            :sort="sort"
            :direction="direction"
            @sort="sortBy"
            width="140px"
            align="right"
        >
            Reserved
        </DataTableHeaderCell>


        <!-- Available -->

        <DataTableHeaderCell
            sortable
            column="available_qty"
            :sort="sort"
            :direction="direction"
            @sort="sortBy"
            width="140px"
            align="right"
        >
            Available
        </DataTableHeaderCell>


        <!-- Average Cost -->

        <DataTableHeaderCell
            width="160px"
            align="right"
        >
            Average Cost
        </DataTableHeaderCell>


        <!-- Stock Value -->

        <DataTableHeaderCell
            sortable
            column="stock_value"
            :sort="sort"
            :direction="direction"
            @sort="sortBy"
            width="180px"
            align="right"
        >
            Stock Value
        </DataTableHeaderCell>
        <DataTableHeaderCell
            width="160px"
            align="right"
        >
            Retail Price
        </DataTableHeaderCell>

        <DataTableHeaderCell
            sortable
            column="sales_value"
            :sort="sort"
            :direction="direction"
            @sort="sortBy"
            width="180px"
            align="right"
        >
            Sales Value
        </DataTableHeaderCell>

        <!-- Actions -->

        <DataTableHeaderCell
            width="90px"
            align="center"
        >
            Actions
        </DataTableHeaderCell>

    </DataTableHead>


    <DataTableBody>

        <DataTableRow
            v-for="item in stockBalance.data"
            :key="item.id"
        >

            <!-- ================================================= -->
            <!-- Product -->
            <!-- ================================================= -->

            <DataTableCell>

                <div
                    class="
                        font-medium
                        text-gray-900
                    "
                >
                    {{
                        item.product?.name
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

                    <span>
                        SKU:
                        {{
                            item.variant?.sku
                            ?? '-'
                        }}
                    </span>


                    <span
                        v-if="
                            item.variant?.name
                        "
                    >
                        ·
                        {{
                            item.variant.name
                        }}
                    </span>

                </div>

            </DataTableCell>


            <!-- ================================================= -->
            <!-- Branch / Warehouse -->
            <!-- ================================================= -->

            <DataTableCell>

                <div
                    class="
                        font-medium
                        text-gray-900
                    "
                >
                    {{
                        item.branch?.name
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
                        item.warehouse?.name
                        ?? '-'
                    }}
                </div>

            </DataTableCell>


            <!-- ================================================= -->
            <!-- Units -->
            <!-- ================================================= -->

            <DataTableCell align="center">

                <button
                    type="button"
                    class="
                        inline-flex
                        items-center
                        rounded-lg
                        border
                        border-gray-200
                        bg-gray-50
                        px-3
                        py-1.5
                        text-xs
                        font-medium
                        text-gray-700
                        hover:bg-gray-100
                    "
                    @click="
                        openView(item)
                    "
                >

                    {{
                        item.unit_count
                        ?? 0
                    }}

                    {{
                        (
                            item.unit_count
                            ?? 0
                        ) === 1
                            ? 'Unit'
                            : 'Units'
                    }}

                </button>

            </DataTableCell>


            <!-- ================================================= -->
            <!-- On Hand -->
            <!-- ================================================= -->

            <DataTableCell align="right">

                <span
                    class="
                        font-medium
                        text-gray-900
                    "
                >
                    {{
                        formatNumber(
                            item.on_hand_qty
                        )
                    }}
                </span>

            </DataTableCell>


            <!-- ================================================= -->
            <!-- Reserved -->
            <!-- ================================================= -->

            <DataTableCell align="right">

                <span
                    class="text-gray-600"
                >
                    {{
                        formatNumber(
                            item.reserved_qty
                        )
                    }}
                </span>

            </DataTableCell>


            <!-- ================================================= -->
            <!-- Available -->
            <!-- ================================================= -->

            <DataTableCell align="right">

                <span
                    class="
                        font-semibold
                        text-gray-900
                    "
                >
                    {{
                        formatNumber(
                            item.available_qty
                        )
                    }}
                </span>

            </DataTableCell>


            <!-- ================================================= -->
            <!-- Average Cost -->
            <!-- ================================================= -->

              <DataTableCell align="right">

                {{
                    formatCurrency(
                        item.average_cost ?? 0
                    )
                }}

             </DataTableCell>

            <!-- ================================================= -->
            <!-- Stock Value -->
            <!-- ================================================= -->

            <DataTableCell align="right">

                <span
                    class="
                        font-semibold
                        text-gray-900
                    "
                >
                    {{
                        formatCurrency(
                            item.stock_value
                            ?? 0
                        )
                    }}
                </span>

            </DataTableCell>
            <!-- Retail Price -->

            <DataTableCell align="right">

                {{
                    formatCurrency(
                        item.selling_price ?? 0
                        
                    )
                }}

            </DataTableCell>


            <!-- Sales Value -->

            <DataTableCell align="right">

                <span
                    class="
                        font-semibold
                        text-gray-900
                    "
                >
                    {{
                        formatCurrency(
                            item.sales_value ?? 0
                        )
                    }}
                </span>

            </DataTableCell>

            <!-- ================================================= -->
            <!-- Actions -->
            <!-- ================================================= -->

            <DataTableCell align="center">

               <button
                type="button"
                class="text-blue-600 hover:text-blue-800"
                @click="openView(item)"
            >
                View
            </button>

            </DataTableCell>

        </DataTableRow>

    </DataTableBody>

</DataTable>
                <!-- Empty -->

                <TableEmpty
                    v-else
                    icon="📦"
                    title="No Stock Balance Found"
                    description="
                        There are no stock balances matching
                        the selected filters.
                    "
                />

            </div>


            <!-- ===================================================== -->
            <!-- Pagination -->
            <!-- ===================================================== -->

           <div class="mt-6">

               <TablePagination
                :data="stockBalance"
                label="Stock Balance"
                />

             </div>

        </Card>
</AppLayout>
  <!--
|--------------------------------------------------------------------------
| View Stock Detail Modal
|--------------------------------------------------------------------------
-->

<div
    v-if="showViewModal && selectedItem"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
    @click.self="closeView"
>
    <div
        class="flex max-h-[90vh] w-full max-w-5xl flex-col overflow-hidden rounded-xl bg-white shadow-xl"
    >

        <!-- Header -->
        <div class="flex items-center justify-between border-b px-6 py-4">

            <div>
                <h2 class="text-lg font-semibold text-gray-900">
                    {{ selectedItem.product?.name ?? '-' }}
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    {{ selectedItem.variant?.sku ?? '-' }}
                </p>
            </div>

            <button
                type="button"
                class="rounded-lg px-3 py-2 text-gray-500 hover:bg-gray-100"
                @click="closeView"
            >
                ✕
            </button>

        </div>


        <!-- Location -->
     <div class="min-h-0 flex-1 overflow-y-auto">    
        <div class="grid grid-cols-1 gap-4 border-b px-6 py-4 md:grid-cols-3">

            <div>
                <div class="text-xs text-gray-500">
                    Branch
                </div>

                <div class="mt-1 font-medium">
                    {{ selectedItem.branch?.name ?? '-' }}
                </div>
            </div>


            <div>
                <div class="text-xs text-gray-500">
                    Warehouse
                </div>

                <div class="mt-1 font-medium">
                    {{ selectedItem.warehouse?.name ?? '-' }}
                </div>
            </div>


            <div>
                <div class="text-xs text-gray-500">
                    Variant
                </div>

                <div class="mt-1 font-medium">
                    {{ selectedItem.variant?.name ?? '-' }}
                </div>
            </div>

        </div>


        <!-- Summary -->
        <div class="grid grid-cols-2 gap-4 border-b px-6 py-5 md:grid-cols-4">

            <div class="rounded-lg border p-4">

                <div class="text-xs text-gray-500">
                    On Hand
                </div>

                <div class="mt-1 text-xl font-semibold">
                    {{ formatNumber(selectedItem.on_hand_qty) }}
                </div>

            </div>


            <div class="rounded-lg border p-4">

                <div class="text-xs text-gray-500">
                    Reserved
                </div>

                <div class="mt-1 text-xl font-semibold">
                    {{ formatNumber(selectedItem.reserved_qty) }}
                </div>

            </div>


            <div class="rounded-lg border p-4">

                <div class="text-xs text-gray-500">
                    Available
                </div>

                <div class="mt-1 text-xl font-semibold">
                    {{ formatNumber(selectedItem.available_qty) }}
                </div>

            </div>


            <div class="rounded-lg border p-4">

                <div class="text-xs text-gray-500">
                    Stock Value
                </div>

                <div class="mt-1 text-xl font-semibold">
                    {{ formatCurrency(selectedItem.stock_value) }}
                </div>

            </div>

        </div>
        <!-- tab-->
         <div class="border-b px-6">

            <div class="flex gap-6">

                <button
                    type="button"
                    class="border-b-2 px-1 py-3 text-sm font-medium"
                    :class="
                        activeViewTab === 'overview'
                            ? 'border-blue-600 text-blue-600'
                            : 'border-transparent text-gray-500 hover:text-gray-700'
                    "
                    @click="activeViewTab = 'overview'"
                >
                    Overview
                </button>

                <button
                    type="button"
                    class="border-b-2 px-1 py-3 text-sm font-medium"
                    :class="
                        activeViewTab === 'movement'
                            ? 'border-blue-600 text-blue-600'
                            : 'border-transparent text-gray-500 hover:text-gray-700'
                    "
                    @click="openMovementTab"
                >
                    Movement
                </button>

            </div>

        </div>
        <!-- end tabe movement-->
       <div
                v-if="activeViewTab === 'movement'"
                class="px-6 py-5"
            >

                <div class="mb-4">

                    <h3 class="text-sm font-semibold text-gray-900">
                        Inventory Movement
                    </h3>

                    <p class="mt-1 text-xs text-gray-500">
                        Stock movement history for this product and warehouse.
                    </p>

                </div>


                <!-- Loading -->

                <div
                    v-if="movementLoading"
                    class="rounded-lg border px-4 py-10 text-center text-sm text-gray-500"
                >
                    Loading movement...
                </div>


                <!-- Empty -->

                <div
                    v-else-if="!movements.length"
                    class="rounded-lg border px-4 py-10 text-center text-sm text-gray-500"
                >
                    No inventory movement found.
                </div>


                <!-- Data -->

                <div
                    v-else
                    class="overflow-hidden rounded-lg border"
                >

                    <div class="overflow-x-auto">

                        <table class="min-w-full text-sm">

                            <thead class="bg-gray-50">

                                <tr>

                                    <th class="px-4 py-3 text-left">
                                        Date
                                    </th>

                                    <th class="px-4 py-3 text-left">
                                        Reference
                                    </th>

                                    <th class="px-4 py-3 text-left">
                                        Type
                                    </th>

                                    <th class="px-4 py-3 text-left">
                                        Unit
                                    </th>

                                    <th class="px-4 py-3 text-right">
                                        Qty In
                                    </th>

                                    <th class="px-4 py-3 text-right">
                                        Qty Out
                                    </th>

                                    <th class="px-4 py-3 text-right">
                                        Unit Cost
                                    </th>

                                    <th class="px-4 py-3 text-right">
                                        Total Cost
                                    </th>

                                </tr>

                            </thead>


                            <tbody class="divide-y">

                                <tr
                                    v-for="movement in movements"
                                    :key="movement.id"
                                    class="hover:bg-gray-50"
                                >

                                    <td class="whitespace-nowrap px-4 py-3">
                                        {{ movement.date }}
                                    </td>


                                    <td class="px-4 py-3">

                                        <div class="font-medium text-gray-900">
                                            {{ movement.reference_number }}
                                        </div>

                                        <div
                                            v-if="movement.description"
                                            class="mt-0.5 text-xs text-gray-500"
                                        >
                                            {{ movement.description }}
                                        </div>

                                    </td>


                                    <td class="px-4 py-3">

                                        <span
                                            class="inline-flex rounded-full px-2 py-1 text-xs font-medium"
                                            :class="
                                                Number(movement.qty_in) > 0
                                                    ? 'bg-green-50 text-green-700'
                                                    : 'bg-red-50 text-red-700'
                                            "
                                        >
                                            {{ movement.reference_type }}
                                        </span>

                                    </td>


                                    <td class="px-4 py-3">
                                        {{ movement.unit_name ?? '-' }}
                                    </td>


                                    <td class="px-4 py-3 text-right">

                                        <span
                                            v-if="Number(movement.qty_in) > 0"
                                            class="font-medium text-green-600"
                                        >
                                            +{{ formatNumber(movement.qty_in) }}
                                        </span>

                                        <span v-else>
                                            -
                                        </span>

                                    </td>


                                    <td class="px-4 py-3 text-right">

                                        <span
                                            v-if="Number(movement.qty_out) > 0"
                                            class="font-medium text-red-600"
                                        >
                                            -{{ formatNumber(movement.qty_out) }}
                                        </span>

                                        <span v-else>
                                            -
                                        </span>

                                    </td>


                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        {{ formatCurrency(movement.unit_cost) }}
                                    </td>


                                    <td class="px-4 py-3 text-right whitespace-nowrap font-medium">
                                        {{ formatCurrency(movement.total_cost) }}
                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>
            <!-- end tabe movement-->
        <!-- Multi Unit -->
        <div  v-if="activeViewTab === 'overview'"
        class="px-6 py-5">

            <h3 class="text-sm font-semibold text-gray-900">
                Stock by Unit
            </h3>

            <div class="mt-3 overflow-hidden rounded-lg border">

                <div class="overflow-x-auto">

                    <table class="min-w-full text-sm">

                        <thead class="bg-gray-50">

                            <tr>

                                <th class="px-4 py-3 text-left">
                                    Unit
                                </th>

                                <th class="px-4 py-3 text-right">
                                    On Hand
                                </th>

                                <th class="px-4 py-3 text-right">
                                    Reserved
                                </th>

                                <th class="px-4 py-3 text-right">
                                    Available
                                </th>

                                <th class="px-4 py-3 text-right">
                                    Average Cost
                                </th>

                                <th class="px-4 py-3 text-right">
                                    Stock Value
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <tr
                                v-for="unit in selectedItem.units"
                                :key="unit.id"
                                class="border-t"
                            >

                                <td class="px-4 py-3 font-medium">
                                    {{ unit.unit_name }}
                                </td>

                                <td class="px-4 py-3 text-right">
                                    {{ formatNumber(unit.on_hand_qty) }}
                                </td>

                                <td class="px-4 py-3 text-right">
                                    {{ formatNumber(unit.reserved_qty) }}
                                </td>

                                <td class="px-4 py-3 text-right">
                                    {{ formatNumber(unit.available_qty) }}
                                </td>

                                <td class="px-4 py-3 text-right">
                                    {{ formatCurrency(unit.average_cost) }}
                                </td>

                                <td class="px-4 py-3 text-right font-medium">
                                    {{ formatCurrency(unit.stock_value) }}
                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>
    </div>

        <!-- Footer -->
        <div class="flex justify-end border-t px-6 py-4">

            <button
                type="button"
                class="rounded-lg border px-4 py-2 text-sm"
                @click="closeView"
            >
                Close
            </button>

        </div>

    </div>
</div>
</template>
