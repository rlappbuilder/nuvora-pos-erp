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

/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    stockCard: {

        type: Object,

        default: () => ({

            summary: {

                opening_qty: 0,

                total_qty_in: 0,

                total_qty_out: 0,

                closing_qty: 0,

            },

            rows: [],

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
    stockCard,
} = toRefs(props)


/*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/

const pageTitle =
    computed(
        () => 'Stock Card'
    )


/*
|--------------------------------------------------------------------------
| Loading
|--------------------------------------------------------------------------
*/

const loading =
    ref(false)


/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

const filters =
    reactive({

        date_from:
            props.filters?.date_from
            ?? '',

        date_to:
            props.filters?.date_to
            ?? '',

        branch_id:
            props.filters?.branch_id
            ?? '',

        warehouse_id:
            props.filters?.warehouse_id
            ?? '',

        product_variant_id:
            props.filters?.product_variant_id
            ?? '',

        unit_id:
            props.filters?.unit_id
            ?? '',

    })


/*
|--------------------------------------------------------------------------
| Date Range
|--------------------------------------------------------------------------
*/

const dateRange =
    ref('')


/*
|--------------------------------------------------------------------------
| Load Data
|--------------------------------------------------------------------------
*/

function loadData()
{

    router.get(

        route(
            'stock-card.index'
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


/*
|--------------------------------------------------------------------------
| Branch
|--------------------------------------------------------------------------
*/

watch(

    () =>
        filters.branch_id,

    () => {

        /*
        |--------------------------------------------------------------------------
        | Reset Warehouse
        |--------------------------------------------------------------------------
        */

        filters.warehouse_id = ''

        loadData()

    }

)


/*
|--------------------------------------------------------------------------
| Warehouse
|--------------------------------------------------------------------------
*/

watch(

    () =>
        filters.warehouse_id,

    () => {

        loadData()

    }

)


/*
|--------------------------------------------------------------------------
| Product Variant
|--------------------------------------------------------------------------
*/

watch(

    () =>
        filters.product_variant_id,

    () => {

        loadData()

    }

)


/*
|--------------------------------------------------------------------------
| Unit
|--------------------------------------------------------------------------
*/

watch(

    () =>
        filters.unit_id,

    () => {

        loadData()

    }

)


/*
|--------------------------------------------------------------------------
| Filtered Warehouses
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| Date Formatting
|--------------------------------------------------------------------------
*/

function formatDateForFilter(
    date
) {

    const year =
        date.getFullYear()


    const month =
        String(
            date.getMonth() + 1
        )
        .padStart(
            2,
            '0'
        )


    const day =
        String(
            date.getDate()
        )
        .padStart(
            2,
            '0'
        )


    return `${year}-${month}-${day}`

}


/*
|--------------------------------------------------------------------------
| Date Range Change
|--------------------------------------------------------------------------
*/

function handleDateRangeChange(
    selectedDates
) {

    if (
        ! selectedDates.length
    ) {

        filters.date_from =
            ''

        filters.date_to =
            ''

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

            date_from:
                '',

            date_to:
                '',

            branch_id:
                '',

            warehouse_id:
                '',

            product_variant_id:
                '',

            unit_id:
                '',

        }

    )


    dateRange.value =
        ''


    loadData()

}


/*
|--------------------------------------------------------------------------
| Number Formatting
|--------------------------------------------------------------------------
*/

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


const summary =
    computed(
        () =>

            stockCard.value?.summary
            ?? {

                opening_qty: 0,

                total_qty_in: 0,

                total_qty_out: 0,

                closing_qty: 0,

            }

    )


const rows =
    computed(
        () =>
            stockCard.value?.data
            ?? []
    )
const hasData =
    computed(
        () =>
            rows.value.length > 0
    )

const sort =
    ref(
        props.filters?.sort_by
        ?? 'date'
    )

const direction =
    ref(
        props.filters?.sort_direction
        ?? 'desc'
    )
function sortBy(column)
{

    if (
        column !== 'date'
    ) {

        return

    }


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
            'stock-card.index'
        ),

        {

            ...filters,

            page: 1,

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
                    title="Opening Balance"
                    :value="
                        formatNumber(
                            summary?.opening_qty
                            ?? 0
                        )
                    "
                    icon="↩️"
                />


                <StatsCard
                    title="Total In"
                    :value="
                        formatNumber(
                            summary?.total_qty_in
                            ?? 0
                        )
                    "
                    icon="📥"
                />


                <StatsCard
                    title="Total Out"
                    :value="
                        formatNumber(
                            summary?.total_qty_out
                            ?? 0
                        )
                    "
                    icon="📤"
                />


                <StatsCard
                    title="Closing Balance"
                    :value="
                        formatNumber(
                            summary?.closing_qty
                            ?? 0
                        )
                    "
                    icon="📦"
                />

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
                    text="Loading Stock Card ..."
                />
               
                <!-- ===================================================== -->
                <!-- Stock Card DataTable -->
                <!-- ===================================================== -->

                <DataTable
                    v-if="rows.length"
                    sticky-header
                    max-height="650px"
                >

                    <DataTableHead sticky>

                        <!-- Date -->

                        <DataTableHeaderCell
                             sortable
                            column="date"
                            :sort="sort"
                            :direction="direction"
                            @sort="sortBy"
                            width="120px"
                            align="right"
                        >
                            Date
                        </DataTableHeaderCell>


                        <!-- Reference -->

                        <DataTableHeaderCell
                            width="220px"
                        >
                            Reference
                        </DataTableHeaderCell>


                        <!-- Description -->

                        <DataTableHeaderCell
                            width="280px"
                        >
                            Description
                        </DataTableHeaderCell>


                        <!-- Opening -->

                        <DataTableHeaderCell
                            width="140px"
                            align="right"
                        >
                            Stock Awal
                        </DataTableHeaderCell>


                        <!-- Qty In -->

                        <DataTableHeaderCell
                            width="140px"
                            align="right"
                        >
                            Qty In
                        </DataTableHeaderCell>


                        <!-- Qty Out -->

                        <DataTableHeaderCell
                            width="140px"
                            align="right"
                        >
                            Qty Out
                        </DataTableHeaderCell>


                        <!-- Balance -->

                        <DataTableHeaderCell
                            width="140px"
                            align="right"
                        >
                            Balance
                        </DataTableHeaderCell>


                        <!-- Unit Cost -->

                        <DataTableHeaderCell
                            width="160px"
                            align="right"
                        >
                            Unit Cost
                        </DataTableHeaderCell>


                        <!-- Total Cost -->

                        <DataTableHeaderCell
                            width="180px"
                            align="right"
                        >
                            Total Cost
                        </DataTableHeaderCell>

                    </DataTableHead>


                    <DataTableBody>

                        <DataTableRow
                            v-for="row in rows"
                            :key="
                                row.id
                                ??
                                `opening-${row.date}`
                            "
                        >

                            <!-- ================================================= -->
                            <!-- Date -->
                            <!-- ================================================= -->

                            <DataTableCell>

                                <span
                                    class="
                                        whitespace-nowrap
                                        text-gray-900
                                    "
                                >
                                    {{
                                        row.date
                                        ?? '-'
                                    }}
                                </span>

                            </DataTableCell>


                            <!-- ================================================= -->
                            <!-- Reference -->
                            <!-- ================================================= -->

                            <DataTableCell>

                                <div
                                    class="
                                        font-medium
                                        text-gray-900
                                    "
                                >

                                    {{
                                        row.reference_number
                                        ??
                                        'Opening Balance'
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
                                        row.reference_type
                                        ?? '-'
                                    }}

                                </div>

                            </DataTableCell>


                            <!-- ================================================= -->
                            <!-- Description -->
                            <!-- ================================================= -->

                            <DataTableCell>

                                <span
                                    class="text-gray-600"
                                >

                                    {{
                                        row.description
                                        ?? '-'
                                    }}

                                </span>

                            </DataTableCell>


                            <!-- ================================================= -->
                            <!-- Opening -->
                            <!-- ================================================= -->

                            <DataTableCell align="right">

                                <span
                                    class="
                                        font-medium
                                        text-gray-700
                                    "
                                >

                                    {{
                                        formatNumber(
                                            row.opening_qty
                                        )
                                    }}

                                </span>

                            </DataTableCell>


                            <!-- ================================================= -->
                            <!-- Qty In -->
                            <!-- ================================================= -->

                            <DataTableCell align="right">

                                <span
                                    v-if="
                                        Number(row.qty_in)
                                        > 0
                                    "
                                    class="
                                        font-semibold
                                        text-green-600
                                    "
                                >

                                    +{{
                                        formatNumber(
                                            row.qty_in
                                        )
                                    }}

                                </span>

                                <span
                                    v-else
                                    class="text-gray-400"
                                >
                                    -
                                </span>

                            </DataTableCell>


                            <!-- ================================================= -->
                            <!-- Qty Out -->
                            <!-- ================================================= -->

                            <DataTableCell align="right">

                                <span
                                    v-if="
                                        Number(row.qty_out)
                                        > 0
                                    "
                                    class="
                                        font-semibold
                                        text-red-600
                                    "
                                >

                                    -{{
                                        formatNumber(
                                            row.qty_out
                                        )
                                    }}

                                </span>

                                <span
                                    v-else
                                    class="text-gray-400"
                                >
                                    -
                                </span>

                            </DataTableCell>


                            <!-- ================================================= -->
                            <!-- Balance -->
                            <!-- ================================================= -->

                            <DataTableCell align="right">

                                <span
                                    class="
                                        font-bold
                                        text-gray-900
                                    "
                                >

                                    {{
                                        formatNumber(
                                            row.balance_qty
                                        )
                                    }}

                                </span>

                            </DataTableCell>


                            <!-- ================================================= -->
                            <!-- Unit Cost -->
                            <!-- ================================================= -->

                            <DataTableCell align="right">

                                <span
                                    v-if="
                                        Number(row.unit_cost)
                                        > 0
                                    "
                                >

                                    {{
                                        formatCurrency(
                                            row.unit_cost
                                        )
                                    }}

                                </span>

                                <span
                                    v-else
                                    class="text-gray-400"
                                >
                                    -
                                </span>

                            </DataTableCell>


                            <!-- ================================================= -->
                            <!-- Total Cost -->
                            <!-- ================================================= -->

                            <DataTableCell align="right">

                                <span
                                    v-if="
                                        Number(row.total_cost)
                                        > 0
                                    "
                                    class="
                                        font-semibold
                                        text-gray-900
                                    "
                                >

                                    {{
                                        formatCurrency(
                                            row.total_cost
                                        )
                                    }}

                                </span>

                                <span
                                    v-else
                                    class="text-gray-400"
                                >
                                    -
                                </span>

                            </DataTableCell>

                        </DataTableRow>

                    </DataTableBody>

                </DataTable>
                <!-- Empty -->

              <TableEmpty
                    v-else
                    icon="📋 Stock Card "
                    title="No Stock Card Transaction Found"
                    description="
                        Select Product, Branch, Warehouse and Unit
                        to view stock card transactions.
                    "
                />

                </div>

            <!-- ===================================================== -->
            <!-- 
              Pagination -->
            <!-- ===================================================== -->

            <div class="mt-6">

               <TablePagination
                :data="stockCard"
                label="Stock Card"
                />
            </div>

        </Card>
    </div>    
</AppLayout>
</template>
