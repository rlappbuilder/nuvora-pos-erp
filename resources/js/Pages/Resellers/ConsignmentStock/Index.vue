<script setup>
import {computed,ref,nextTick,} from 'vue'
import axios from 'axios'
import { router } from '@inertiajs/vue3'
import SearchableSelect from '@/Components/Form/SearchableSelect.vue'
import AppLayout from '@/Layouts/AppLayout.vue'


const props = defineProps({

    consignmentStock: {
        type: Object,
        required: true,
    },

    statistics: {
        type: Object,
        default: () => ({}),
    },

    filters: {
        type: Object,
        default: () => ({}),
    },

    resellers: {
        type: Array,
        default: () => [],
    },

    branches: {
        type: Array,
        default: () => [],
    },

})


/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

const resellerId = ref(
    props.filters.reseller_id ?? ''
)

const branchId = ref(
    props.filters.branch_id ?? ''
)

const dateFrom = ref(
    props.filters.date_from ?? ''
)

const dateTo = ref(
    props.filters.date_to ?? ''
)


/*
|--------------------------------------------------------------------------
| Report Data
|--------------------------------------------------------------------------
*/

const rows = computed(() =>
    props.consignmentStock?.data ?? []
)
console.log(
    'CONSIGNMENT STOCK ROW:',
    JSON.parse(
        JSON.stringify(
            rows.value?.[0] ?? {}
        )
    )
)
const totalOnHand = computed(() =>
    Number(
        props.statistics?.total_on_hand ?? 0
    )
)

const totalAvailable = computed(() =>
    Number(
        props.statistics?.total_available ?? 0
    )
)

const totalStockValue = computed(() =>
    Number(
        props.statistics?.total_stock_value ?? 0
    )
)

const totalConsignmentValue = computed(() =>
    Number(
        props.statistics?.total_consignment_value ?? 0
    )
)


/*
|--------------------------------------------------------------------------
| Format
|--------------------------------------------------------------------------
*/

const formatAmount = (amount) => {

    return new Intl.NumberFormat(
        'id-ID',
        {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }
    ).format(
        Number(amount ?? 0)
    )
}


const formatQty = (qty) => {

    const value = Number(qty ?? 0)

    return new Intl.NumberFormat(
        'id-ID',
        {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2,
        }
    ).format(value)
}


const formatDate = (date) => {

    if (!date) {
        return '-'
    }

    return new Intl.DateTimeFormat(
        'id-ID',
        {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
        }
    ).format(
        new Date(date)
    )
}


/*
|--------------------------------------------------------------------------
| Filter
|--------------------------------------------------------------------------
*/

const applyFilter = () => {

    router.get(
        route('consignment-stock.index'),
        {
            date_from:
                dateFrom.value ||
                undefined,

            date_to:
                dateTo.value ||
                undefined,

            reseller_id:
                resellerId.value ||
                undefined,

            branch_id:
                branchId.value ||
                undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    )

}


/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

const activeSummary = ref(
    'total_consignment_value'
)

const tableSection = ref(null)


const summaryCards = computed(() => [

    {
        key: 'total_consignment_value',

        label: 'Consignment Value',

        value:
            totalConsignmentValue.value,

        classes:
            'bg-white border-gray-200 text-gray-900',

        accent:
            'bg-emerald-500',
    },

    {
        key: 'total_stock_value',

        label: 'Stock Value',

        value:
            totalStockValue.value,

        classes:
            'bg-white border-gray-200 text-gray-900',

        accent:
            'bg-blue-500',
    },

    {
        key: 'total_on_hand',

        label: 'Total On Hand',

        value:
            totalOnHand.value,

        classes:
            'bg-white border-gray-200 text-gray-900',

        accent:
            'bg-gray-500',

        isQuantity: true,
    },

    {
        key: 'total_available',

        label: 'Total Available',

        value:
            totalAvailable.value,

        classes:
            'bg-white border-gray-200 text-gray-900',

        accent:
            'bg-yellow-500',

        isQuantity: true,
    },

])


/*
|--------------------------------------------------------------------------
| Summary Selection
|--------------------------------------------------------------------------
*/

const selectSummary = async (
    key
) => {

    activeSummary.value =
        key

    await nextTick()

    tableSection.value?.scrollIntoView({
        behavior: 'smooth',
        block: 'start',
    })

}


/*
|--------------------------------------------------------------------------
| Print / Export
|--------------------------------------------------------------------------
*/
const printReport = () => {

    const query = new URLSearchParams({
        ...(dateFrom.value
            ? { date_from: dateFrom.value }
            : {}),

        ...(dateTo.value
            ? { date_to: dateTo.value }
            : {}),

        ...(resellerId.value
            ? { reseller_id: resellerId.value }
            : {}),

        ...(branchId.value
            ? { branch_id: branchId.value }
            : {}),
    })

    window.open(
        route('consignment-stock.print') +
        (query.toString()
            ? `?${query.toString()}`
            : ''),
        '_blank'
    )

}
const exportPdf = () => {

    const query = new URLSearchParams({
        ...(dateFrom.value
            ? { date_from: dateFrom.value }
            : {}),

        ...(dateTo.value
            ? { date_to: dateTo.value }
            : {}),

        ...(resellerId.value
            ? { reseller_id: resellerId.value }
            : {}),

        ...(branchId.value
            ? { branch_id: branchId.value }
            : {}),
    })

    window.location.href =
        route('consignment-stock.pdf') +
        (query.toString()
            ? `?${query.toString()}`
            : '')

}
const exportExcel = () => {

    const query = new URLSearchParams({
        ...(dateFrom.value
            ? { date_from: dateFrom.value }
            : {}),

        ...(dateTo.value
            ? { date_to: dateTo.value }
            : {}),

        ...(resellerId.value
            ? { reseller_id: resellerId.value }
            : {}),

        ...(branchId.value
            ? { branch_id: branchId.value }
            : {}),
    })

    window.location.href =
        route('consignment-stock.excel') +
        (query.toString()
            ? `?${query.toString()}`
            : '')

}
/*
|--------------------------------------------------------------------------
| Refresh
|--------------------------------------------------------------------------
*/

function refreshReport()
{

    dateFrom.value = ''

    dateTo.value = ''

    resellerId.value = ''

    branchId.value = ''

    applyFilter()

}
/*
|--------------------------------------------------------------------------
| Movement Modal
|--------------------------------------------------------------------------
*/

const showMovementModal = ref(false)
const movementLoading = ref(false)
const movementData = ref([])

const movementRow = ref(null)

const openMovement = async (row) => {

    movementRow.value = row
    movementData.value = []
    showMovementModal.value = true
    movementLoading.value = true

    try {

        const response = await axios.get(
            route('consignment-stock.movements'),
            {
                params: {
                    product_variant_id:
                        row.product_variant_id,

                    branch_id:
                        row.branch?.id ?? row.branch_id,

                    warehouse_id:
                        row.warehouse?.id ?? row.warehouse_id,

                    reseller_id:
                        row.reseller_id,

                    unit_id:
                        row.unit_id ?? undefined,
                },
            }
        )

        movementData.value =
            response.data?.data ?? []

    } catch (error) {

        console.error(
            'Failed to load consignment movements:',
            error
        )

        movementData.value = []

    } finally {

        movementLoading.value = false

    }

}

const closeMovement = () => {

    showMovementModal.value = false
    movementRow.value = null
    movementData.value = []

}
</script>
<template>

<AppLayout>

    <div class="space-y-4">


        <!-- Header -->

        <div
            class="
                flex
                flex-col
                gap-3
                sm:flex-row
                sm:items-center
                sm:justify-between
            "
        >

            <div>

                <h1
                    class="
                        text-xl
                        font-semibold
                        text-gray-900
                    "
                >
                    Consignment Stock
                </h1>


                <p
                    class="
                        mt-1
                        text-sm
                        text-gray-500
                    "
                >
                    Current Nuvora-owned stock
                    held at reseller locations
                </p>

            </div>


            <!-- Actions -->

            <div
                class="
                    flex
                    items-center
                    gap-2
                    print:hidden
                "
            >
                <!-- Refresh -->

<button
    type="button"
    class="
        inline-flex
        items-center
        gap-2
        rounded-lg
        border
        border-gray-200
        bg-white
        px-3
        py-2
        text-sm
        font-medium
        text-gray-700
        transition
        hover:bg-gray-50
    "
    @click="refreshReport"
>

    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.8"
        class="h-4 w-4 text-gray-600"
    >
        <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M20 11a8.1 8.1 0 0 0-14.9-4M4 5v4h4"
        />

        <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M4 13a8.1 8.1 0 0 0 14.9 4M20 19v-4h-4"
        />
    </svg>

    Refresh

</button>
                <!-- Print -->

                <button
                    type="button"
                    class="
                        inline-flex
                        items-center
                        gap-2
                        rounded-lg
                        border
                        border-gray-200
                        bg-white
                        px-3
                        py-2
                        text-sm
                        font-medium
                        text-gray-700
                        transition
                        hover:bg-gray-50
                    "
                    @click="printReport"
                >

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        class="h-4 w-4 text-gray-600"
                    >

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M6 9V3h12v6"
                        />

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5h-2"
                        />

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M6 14h12v7H6z"
                        />

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M18 12h.01"
                        />

                    </svg>

                    Print

                </button>


                <!-- PDF -->

                <button
                    type="button"
                    class="
                        inline-flex
                        items-center
                        gap-2
                        rounded-lg
                        border
                        border-gray-200
                        bg-white
                        px-3
                        py-2
                        text-sm
                        font-medium
                        text-gray-700
                        transition
                        hover:bg-gray-50
                    "
                    @click="exportPdf"
                >

                    <svg
                        viewBox="0 0 32 32"
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                    >

                        <path
                            fill="#E53935"
                            d="
                                M7 2h13l7 7v21H7z
                            "
                        />

                        <path
                            fill="#fff"
                            d="
                                M20 2v7h7
                            "
                        />

                        <path
                            fill="#fff"
                            d="
                                M10 18.5h2.3
                                c2.1 0 3.3-1.1 3.3-2.8
                                0-1.7-1.2-2.7-3.3-2.7H10zm2.2-1.8
                                h-.5v-1.9h.5
                                c.7 0 1.2.3 1.2.9
                                0 .7-.5 1-1.2 1z
                            "
                        />

                        <path
                            fill="#fff"
                            d="
                                M17 18.5h2.1
                                c2.4 0 3.8-1 3.8-3.7
                                0-2.5-1.4-3.8-3.8-3.8H17zm2.1-5.7
                                h.3c1.1 0 1.7.6 1.7 2
                                0 1.5-.6 2-1.7 2h-.3z
                            "
                        />

                    </svg>

                    PDF

                </button>


                <!-- Excel -->

                <button
                    type="button"
                    class="
                        inline-flex
                        items-center
                        gap-2
                        rounded-lg
                        border
                        border-gray-200
                        bg-white
                        px-3
                        py-2
                        text-sm
                        font-medium
                        text-gray-700
                        transition
                        hover:bg-gray-50
                    "
                    @click="exportExcel"
                >

                    <svg
                        viewBox="0 0 32 32"
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                    >

                        <path
                            fill="#21A366"
                            d="
                                M18 3h9a2 2 0 0 1 2 2v22
                                a2 2 0 0 1-2 2h-9z
                            "
                        />

                        <path
                            fill="#107C41"
                            d="
                                M3 7a2 2 0 0 1 2-2h13v22H5
                                a2 2 0 0 1-2-2z
                            "
                        />

                        <path
                            fill="#fff"
                            d="
                                M8.2 11h2.4l2.1 3.5
                                2-3.5h2.3l-3.1 5
                                3.2 5h-2.5l-2-3.6
                                -2.1 3.6H8.1l3.2-5z
                            "
                        />

                    </svg>

                    Excel

                </button>

            </div>

        </div>


        <!-- Consignment Info -->

        <div
            class="
                rounded-xl
                border
                border-gray-100
                bg-white
                px-4
                py-3
                shadow-sm
            "
        >

            <div
                class="
                    flex
                    flex-col
                    gap-1
                    sm:flex-row
                    sm:items-center
                    sm:justify-between
                "
            >

                <div>

                    <div
                        class="
                            text-xs
                            font-medium
                            text-gray-500
                        "
                    >
                        Consignment Stock
                    </div>

                    <div
                        class="
                            mt-0.5
                            text-sm
                            font-semibold
                            text-gray-900
                        "
                    >
                        All Data
                    </div>

                </div>


                <div
                    class="
                        text-sm
                        text-gray-500
                    "
                >

                    <template
                        v-if="dateFrom || dateTo"
                    >

                        {{ formatDate(dateFrom) }}
                        –
                        {{ formatDate(dateTo) }}

                    </template>

                    <template v-else>

                        All Data

                    </template>

                </div>

            </div>

        </div>


        <!-- Filter -->

        <div
            class="
                rounded-xl
                border
                border-gray-100
                bg-white
                p-4
                shadow-sm
                print:hidden
            "
        >

            <div
                class="
                    grid
                    grid-cols-1
                    gap-3
                    md:grid-cols-2
                    lg:grid-cols-5
                "
            >

                <!-- Date From -->

                <div>

                    <label
                        class="
                            mb-1
                            block
                            text-xs
                            font-medium
                            text-gray-600
                        "
                    >
                        Date From
                    </label>

                    <input
                        v-model="dateFrom"
                        type="date"
                        class="
                            w-full
                            rounded-lg
                            border
                            border-gray-200
                            px-3
                            py-2
                            text-sm
                        "
                    />

                </div>


                <!-- Date To -->

                <div>

                    <label
                        class="
                            mb-1
                            block
                            text-xs
                            font-medium
                            text-gray-600
                        "
                    >
                        Date To
                    </label>

                    <input
                        v-model="dateTo"
                        type="date"
                        class="
                            w-full
                            rounded-lg
                            border
                            border-gray-200
                            px-3
                            py-2
                            text-sm
                        "
                    />

                </div>


                <!-- Reseller -->

                <div>

                    <label
                        class="
                            mb-1
                            block
                            text-xs
                            font-medium
                            text-gray-600
                        "
                    >
                        Reseller
                    </label>

                    <SearchableSelect
                        v-model="resellerId"
                        :options="resellers"
                        label="label"
                        value-key="id"
                        placeholder="All Resellers"
                    />

                </div>


                <!-- Branch -->

                <div>

                    <label
                        class="
                            mb-1
                            block
                            text-xs
                            font-medium
                            text-gray-600
                        "
                    >
                        Branch
                    </label>

                    <SearchableSelect
                        v-model="branchId"
                        :options="branches"
                        label="label"
                        value-key="id"
                        placeholder="All Branches"
                    />

                </div>


                <!-- Apply -->

                <div
                    class="
                        flex
                        items-end
                    "
                >

                    <button
                        type="button"
                        class="
                            w-full
                            rounded-lg
                            bg-gray-900
                            px-4
                            py-2
                            text-sm
                            font-medium
                            text-white
                            transition
                            hover:bg-gray-800
                        "
                        @click="applyFilter"
                    >
                        Apply
                    </button>

                </div>

            </div>

        </div>


        <!-- Summary -->

        <div
            class="
                print-summary
                rounded-xl
                border
                border-gray-100
                bg-white
                p-3
                shadow-sm
                print:rounded-none
                print:border-0
                print:p-0
                print:shadow-none
            "
        >

            <div
                class="
                    grid
                    grid-cols-2
                    gap-2
                    sm:grid-cols-4
                "
            >

                <button
                    v-for="card in summaryCards"
                    :key="card.key"
                    type="button"
                    class="
                        print-summary-item
                        relative
                        overflow-hidden
                        rounded-lg
                        border
                        px-4
                        py-3
                        text-left
                        transition
                        hover:-translate-y-px
                        hover:shadow-sm
                    "
                    :class="[
                        card.classes,

                        activeSummary === card.key
                            ? 'ring-2 ring-gray-300 ring-offset-1'
                            : '',
                    ]"
                    @click="selectSummary(card.key)"
                >

                    <span
                        class="
                            absolute
                            inset-y-0
                            left-0
                            w-1
                        "
                        :class="card.accent"
                    ></span>


                    <div
                        class="
                            text-xs
                            font-medium
                            text-gray-500
                        "
                    >
                        {{ card.label }}
                    </div>


                    <div
                        class="
                            mt-1
                            text-base
                            font-semibold
                            tabular-nums
                            text-gray-900
                        "
                    >

                        <template
                            v-if="card.isQuantity"
                        >

                            {{ formatQty(card.value) }}

                        </template>

                        <template v-else>

                            Rp
                            {{ formatAmount(card.value) }}

                        </template>

                    </div>

                </button>

            </div>

        </div>


        <!-- Table -->

        <div
            ref="tableSection"
            class="
                overflow-hidden
                rounded-xl
                border
                border-gray-100
                bg-white
                shadow-sm
                print:overflow-visible
                print:rounded-none
                print:border-0
                print:shadow-none
            "
        >

            <div class="overflow-x-auto">

                <table
                    class="
                        print-table
                        w-full
                        text-sm
                    "
                >

                    <thead>

                        <tr
                            class="
                                border-b
                                border-gray-200
                            "
                        >

                            <!-- Reseller / Location -->

                            <th
                                class="
                                    min-w-[180px]
                                    px-4
                                    py-3
                                    text-left
                                    text-xs
                                    font-semibold
                                    leading-tight
                                    text-gray-500
                                "
                            >
                                Reseller /
                                <br>
                                Location
                            </th>


                            <!-- Product -->

                            <th
                                class="
                                    min-w-[190px]
                                    px-4
                                    py-3
                                    text-left
                                    text-xs
                                    font-semibold
                                    leading-tight
                                    text-gray-500
                                "
                            >
                                Product
                            </th>


                            <!-- Unit -->

                            <th
                                class="
                                    min-w-[90px]
                                    px-4
                                    py-3
                                    text-left
                                    text-xs
                                    font-semibold
                                    text-gray-500
                                "
                            >
                                Unit
                            </th>


                            <!-- On Hand -->

                            <th
                                class="
                                    min-w-[100px]
                                    px-4
                                    py-3
                                    text-right
                                    text-xs
                                    font-semibold
                                    leading-tight
                                    text-gray-500
                                "
                            >
                                On
                                <br>
                                Hand
                            </th>


                            <!-- Available -->

                            <th
                                class="
                                    min-w-[100px]
                                    px-4
                                    py-3
                                    text-right
                                    text-xs
                                    font-semibold
                                    leading-tight
                                    text-gray-500
                                "
                            >
                                Available
                            </th>


                            <!-- Average Cost -->

                            <th
                                class="
                                    min-w-[110px]
                                    px-4
                                    py-3
                                    text-right
                                    text-xs
                                    font-semibold
                                    leading-tight
                                    text-gray-500
                                "
                            >
                                Average
                                <br>
                                Cost
                            </th>


                            <!-- Stock Value -->

                            <th
                                class="
                                    min-w-[120px]
                                    px-4
                                    py-3
                                    text-right
                                    text-xs
                                    font-semibold
                                    leading-tight
                                    text-gray-500
                                "
                            >
                                Stock
                                <br>
                                Value
                            </th>


                            <!-- Consignment Price -->

                            <th
                                class="
                                    min-w-[125px]
                                    px-4
                                    py-3
                                    text-right
                                    text-xs
                                    font-semibold
                                    leading-tight
                                    text-gray-500
                                "
                            >
                                Consignment
                                <br>
                                Price
                            </th>


                            <!-- Consignment Value -->

                            <th
                                class="
                                    min-w-[130px]
                                    px-4
                                    py-3
                                    text-right
                                    text-xs
                                    font-semibold
                                    leading-tight
                                    text-gray-500
                                "
                            >
                                Consignment
                                <br>
                                Value
                            </th>


                            <!-- Actions -->

                            <th
                                class="
                                    min-w-[80px]
                                    px-4
                                    py-3
                                    text-center
                                    text-xs
                                    font-semibold
                                    text-gray-500
                                    print:hidden
                                "
                            >
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <!-- Rows -->

                        <tr
                            v-for="row in rows"
                            :key="row.id"
                            class="
                                border-b
                                border-gray-50
                                last:border-b-0
                            "
                        >

                            <!-- Reseller / Location -->

                            <td
                                class="
                                    px-4
                                    py-3
                                    align-top
                                "
                            >

                                <div
                                    class="
                                        font-medium
                                        text-gray-900
                                    "
                                >
                                    {{
                                        row.reseller?.name ??
                                        '-'
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
                                        row.branch?.name ??
                                        '-'
                                    }}

                                    ·

                                    {{
                                        row.warehouse?.name ??
                                        '-'
                                    }}

                                </div>

                            </td>


                            <!-- Product -->

                            <td
                                class="
                                    px-4
                                    py-3
                                    align-top
                                "
                            >

                                <div
                                    class="
                                        font-medium
                                        text-gray-900
                                    "
                                >
                                    {{
                                        row.product?.name ??
                                        row.variant?.product?.name ??
                                        '-'
                                    }}
                                </div>

                                <div
                                    class="
                                        mt-0.5
                                        text-xs
                                        text-gray-500
                                    "
                                >
                                    SKU:
                                    {{
                                        row.variant?.sku ??
                                        '-'
                                    }}
                                </div>

                            </td>


                            <!-- Unit -->

                            <td
                                class="
                                    px-4
                                    py-3
                                    align-top
                                    text-gray-700
                                "
                            >

                                <template
                                    v-if="
                                        row.units?.length
                                    "
                                >

                                    <div
                                        v-for="
                                            unit in row.units
                                        "
                                        :key="unit.id"
                                        class="text-xs"
                                    >              
                                       {{ unit.unit_name }}
                                    </div>

                                </template>

                                <template v-else>

                                    -

                                </template>

                            </td>


                            <!-- On Hand -->

                            <td
                                class="
                                    px-4
                                    py-3
                                    text-right
                                    align-top
                                    font-medium
                                    tabular-nums
                                    text-gray-900
                                "
                            >
                                {{ formatQty(row.on_hand_qty) }}
                            </td>


                            <!-- Available -->

                            <td
                                class="
                                    px-4
                                    py-3
                                    text-right
                                    align-top
                                    font-medium
                                    tabular-nums
                                    text-gray-900
                                "
                            >
                                {{ formatQty(row.available_qty) }}
                            </td>


                            <!-- Average Cost -->

                            <td
                                class="
                                    px-4
                                    py-3
                                    text-right
                                    align-top
                                    tabular-nums
                                    text-gray-900
                                "
                            >
                                
                               Rp {{ formatAmount(row.average_cost) }}
                            </td>


                            <!-- Stock Value -->

                            <td
                                class="
                                    px-4
                                    py-3
                                    text-right
                                    align-top
                                    tabular-nums
                                    text-gray-900
                                "
                            >
                                Rp
                                {{ formatAmount(row.stock_value) }}
                            </td>


                            <!-- Consignment Price -->

                            <td
                                class="
                                    px-4
                                    py-3
                                    text-right
                                    align-top
                                    tabular-nums
                                    text-gray-900
                                "
                            >
                                Rp
                                {{
                                    formatAmount(
                                        row.consignment_price
                                    )
                                }}
                            </td>


                            <!-- Consignment Value -->

                            <td
                                class="
                                    px-4
                                    py-3
                                    text-right
                                    align-top
                                    font-medium
                                    tabular-nums
                                    text-gray-900
                                "
                            >
                                Rp
                                {{
                                    formatAmount(
                                        row.consignment_value
                                    )
                                }}
                            </td>


                            <!-- Actions -->

                            <td
                                class="
                                    px-4
                                    py-3
                                    text-center
                                    align-top
                                    print:hidden
                                "
                            >

                                <button
                                    type="button"
                                    class="
                                        rounded-lg
                                        border
                                        border-gray-200
                                        bg-white
                                        px-3
                                        py-1.5
                                        text-xs
                                        font-medium
                                        text-gray-700
                                        hover:bg-gray-50
                                    "
                                    @click="openMovement(row)"
                                >
                                    Movement
                                </button>

                            </td>

                        </tr>


                        <!-- Empty -->

                        <tr
                            v-if="rows.length === 0"
                        >

                            <td
                                colspan="10"
                                class="
                                    px-4
                                    py-10
                                    text-center
                                    text-sm
                                    text-gray-500
                                "
                            >
                                No consignment stock found.
                            </td>

                        </tr>


                        <!-- Total -->

                        <tr
                            v-if="rows.length > 0"
                            class="
                                border-t
                                border-gray-300
                            "
                        >

                            <td
                                colspan="3"
                                class="
                                    px-4
                                    py-3
                                    font-semibold
                                    text-gray-900
                                "
                            >
                                Total
                            </td>


                            <td
                                class="
                                    px-4
                                    py-3
                                    text-right
                                    font-semibold
                                    tabular-nums
                                    text-gray-900
                                "
                            >
                                {{ formatQty(totalOnHand) }}
                            </td>


                            <td
                                class="
                                    px-4
                                    py-3
                                    text-right
                                    font-semibold
                                    tabular-nums
                                    text-gray-900
                                "
                            >
                                {{ formatQty(totalAvailable) }}
                            </td>


                            <td
                                class="
                                    px-4
                                    py-3
                                    text-right
                                    text-gray-500
                                "
                            >
                                -
                            </td>


                            <td
                                class="
                                    px-4
                                    py-3
                                    text-right
                                    font-semibold
                                    tabular-nums
                                    text-gray-900
                                "
                            >
                                Rp
                                {{ formatAmount(totalStockValue) }}
                            </td>


                            <td
                                class="
                                    px-4
                                    py-3
                                    text-right
                                    text-gray-500
                                "
                            >
                                -
                            </td>


                            <td
                                class="
                                    px-4
                                    py-3
                                    text-right
                                    font-semibold
                                    tabular-nums
                                    text-gray-900
                                "
                            >
                                Rp
                                {{
                                    formatAmount(
                                        totalConsignmentValue
                                    )
                                }}
                            </td>


                            <td
                                class="print:hidden"
                            ></td>

                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</AppLayout>
<!-- Movement Modal -->

<div
    v-if="showMovementModal"
    class="
        fixed
        inset-0
        z-50
        flex
        items-center
        justify-center
        bg-black/40
        p-4
    "
    @click.self="closeMovement"
>

    <div
        class="
            flex
            max-h-[90vh]
            w-full
            max-w-5xl
            flex-col
            overflow-hidden
            rounded-xl
            bg-white
            shadow-xl
        "
    >

        <!-- Header -->

        <div
            class="
                flex
                items-center
                justify-between
                border-b
                border-gray-100
                px-5
                py-4
            "
        >

            <div>

                <h2
                    class="
                        text-lg
                        font-semibold
                        text-gray-900
                    "
                >
                    Stock Movement
                </h2>

                <p
                    class="
                        mt-0.5
                        text-xs
                        text-gray-500
                    "
                >
                    Movement history of consignment stock
                </p>

            </div>

            <button
                type="button"
                class="
                    rounded-lg
                    p-2
                    text-gray-400
                    hover:bg-gray-100
                    hover:text-gray-600
                "
                @click="closeMovement"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.8"
                    stroke="currentColor"
                    class="h-5 w-5"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M6 18L18 6M6 6l12 12"
                    />
                </svg>
            </button>

        </div>


        <!-- Information -->

        <div
            v-if="movementRow"
            class="
                grid
                grid-cols-1
                gap-3
                border-b
                border-gray-100
                bg-gray-50
                px-5
                py-4
                sm:grid-cols-2
                lg:grid-cols-4
            "
        >

            <div>
                <div class="text-xs text-gray-500">
                    Reseller
                </div>

                <div class="mt-0.5 text-sm font-medium text-gray-900">
                    {{ movementRow.reseller?.name ?? '-' }}
                </div>
            </div>

            <div>
                <div class="text-xs text-gray-500">
                    Product
                </div>

                <div class="mt-0.5 text-sm font-medium text-gray-900">
                    {{
                        movementRow.product?.name ??
                        movementRow.variant?.product?.name ??
                        '-'
                    }}
                </div>

                <div class="text-xs text-gray-500">
                    SKU: {{ movementRow.variant?.sku ?? '-' }}
                </div>
            </div>

            <div>
                <div class="text-xs text-gray-500">
                    Branch
                </div>

                <div class="mt-0.5 text-sm font-medium text-gray-900">
                    {{ movementRow.branch?.name ?? '-' }}
                </div>
            </div>

            <div>
                <div class="text-xs text-gray-500">
                    Warehouse
                </div>

                <div class="mt-0.5 text-sm font-medium text-gray-900">
                    {{ movementRow.warehouse?.name ?? '-' }}
                </div>
            </div>

        </div>


        <!-- Body -->

        <div class="min-h-0 flex-1 overflow-auto">

            <!-- Loading -->

            <div
                v-if="movementLoading"
                class="
                    flex
                    items-center
                    justify-center
                    px-5
                    py-12
                    text-sm
                    text-gray-500
                "
            >
                Loading movement...
            </div>


            <!-- Empty -->

            <div
                v-else-if="movementData.length === 0"
                class="
                    px-5
                    py-12
                    text-center
                    text-sm
                    text-gray-500
                "
            >
                No movement found.
            </div>


            <!-- Movement Table -->

            <div
                v-else
                class="overflow-x-auto"
            >

                <table class="w-full text-sm">

                    <thead>

                        <tr
                            class="
                                border-b
                                border-gray-200
                                bg-gray-50
                            "
                        >

                            <th
                                class="
                                    whitespace-nowrap
                                    px-5
                                    py-3
                                    text-left
                                    text-xs
                                    font-semibold
                                    text-gray-500
                                "
                            >
                                Date
                            </th>

                            <th
                                class="
                                    whitespace-nowrap
                                    px-4
                                    py-3
                                    text-left
                                    text-xs
                                    font-semibold
                                    text-gray-500
                                "
                            >
                                Reference
                            </th>

                            <th
                                class="
                                    whitespace-nowrap
                                    px-4
                                    py-3
                                    text-right
                                    text-xs
                                    font-semibold
                                    text-gray-500
                                "
                            >
                                In
                            </th>

                            <th
                                class="
                                    whitespace-nowrap
                                    px-4
                                    py-3
                                    text-right
                                    text-xs
                                    font-semibold
                                    text-gray-500
                                "
                            >
                                Out
                            </th>

                            <th
                                class="
                                    whitespace-nowrap
                                    px-4
                                    py-3
                                    text-left
                                    text-xs
                                    font-semibold
                                    text-gray-500
                                "
                            >
                                Unit
                            </th>

                            <th
                                class="
                                    whitespace-nowrap
                                    px-4
                                    py-3
                                    text-right
                                    text-xs
                                    font-semibold
                                    text-gray-500
                                "
                            >
                                Unit Cost
                            </th>

                            <th
                                class="
                                    whitespace-nowrap
                                    px-5
                                    py-3
                                    text-right
                                    text-xs
                                    font-semibold
                                    text-gray-500
                                "
                            >
                                Total Cost
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        <tr
                            v-for="movement in movementData"
                            :key="movement.id"
                            class="
                                border-b
                                border-gray-50
                                last:border-b-0
                            "
                        >

                            <td
                                class="
                                    whitespace-nowrap
                                    px-5
                                    py-3
                                    text-gray-700
                                "
                            >
                                {{ formatDate(movement.date) }}
                            </td>

                            <td
                                class="
                                    px-4
                                    py-3
                                "
                            >

                                <div
                                    class="
                                        font-medium
                                        text-gray-900
                                    "
                                >
                                    {{
                                        movement.reference_number ??
                                        '-'
                                    }}
                                </div>

                                <div
                                    class="
                                        text-xs
                                        text-gray-500
                                    "
                                >
                                    {{
                                        movement.reference_type ??
                                        '-'
                                    }}
                                </div>

                            </td>

                            <td
                                class="
                                    px-4
                                    py-3
                                    text-right
                                    font-medium
                                    tabular-nums
                                    text-emerald-600
                                "
                            >
                                {{ formatQty(movement.qty_in) }}
                            </td>

                            <td
                                class="
                                    px-4
                                    py-3
                                    text-right
                                    font-medium
                                    tabular-nums
                                    text-red-600
                                "
                            >
                                {{ formatQty(movement.qty_out) }}
                            </td>

                            <td
                                class="
                                    px-4
                                    py-3
                                    text-gray-700
                                "
                            >
                                {{ movement.unit_name ?? '-' }}
                            </td>

                            <td
                                class="
                                    px-4
                                    py-3
                                    text-right
                                    tabular-nums
                                    text-gray-900
                                "
                            >
                                Rp
                                {{ formatAmount(movement.unit_cost) }}
                            </td>

                            <td
                                class="
                                    px-5
                                    py-3
                                    text-right
                                    font-medium
                                    tabular-nums
                                    text-gray-900
                                "
                            >
                                Rp
                                {{ formatAmount(movement.total_cost) }}
                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

        </div>


        <!-- Footer -->

        <div
            class="
                flex
                justify-end
                border-t
                border-gray-100
                px-5
                py-3
            "
        >

            <button
                type="button"
                class="
                    rounded-lg
                    border
                    border-gray-200
                    bg-white
                    px-4
                    py-2
                    text-sm
                    font-medium
                    text-gray-700
                    hover:bg-gray-50
                "
                @click="closeMovement"
            >
                Close
            </button>

        </div>

    </div>

</div>
</template>


<style>

@media print {

    @page {
        size: A4 landscape;
        margin: 12mm;
    }


    body {
        background: white !important;
    }


    .print-summary {
        break-inside: avoid;
        page-break-inside: avoid;
    }


    .print-summary-item {
        break-inside: avoid;
        page-break-inside: avoid;
    }


    .print-table {
        width: 100% !important;
    }


    .print-table tr {
        break-inside: avoid;
        page-break-inside: avoid;
    }

}

</style>