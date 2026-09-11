<script setup>
import { ref, computed } from 'vue'

import { router } from '@inertiajs/vue3'

import AppLayout from '@/Layouts/AppLayout.vue'
import SearchableSelect from '@/Components/Form/SearchableSelect.vue'
import DataTable from '@/Components/Table/DataTable.vue'
import DataTableHead from '@/Components/Table/DataTableHead.vue'
import DataTableBody from '@/Components/Table/DataTableBody.vue'
import DataTableHeaderCell from '@/Components/Table/DataTableHeaderCell.vue'
import DataTableRow from '@/Components/Table/DataTableRow.vue'
import DataTableCell from '@/Components/Table/DataTableCell.vue'
import TableEmpty from '@/Components/Table/TableEmpty.vue'

import StatusBadge from '@/Components/Display/StatusBadge.vue'


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({
    reseller: {
        type: Object,
        required: true,
    },
})


/*
|--------------------------------------------------------------------------
| Tabs
|--------------------------------------------------------------------------
*/

const activeTab = ref('current')

const tabs = [
    {
        key: 'current',
        label: 'Current Consignment Prices',
    },
    {
        key: 'history',
        label: 'Price History',
    },
]


/*
|--------------------------------------------------------------------------
| Data
|--------------------------------------------------------------------------
*/

const currentPrices = computed(() =>
    props.reseller?.prices ?? []
)

const priceHistories = computed(() =>
    props.reseller?.price_histories ?? []
)
const selectedHistoryProduct = ref(null)
const historyProductOptions = computed(() => {

    const products = priceHistories.value
        .map(history => history.product)
        .filter(Boolean)

    const uniqueProducts = new Map()

    products.forEach(product => {
        uniqueProducts.set(
            product.id,
            product
        )
    })

    return [
        {
            id: null,
            name: 'All Products',
            code: '',
        },
        ...uniqueProducts.values(),
    ]
})
const filteredPriceHistories = computed(() => {

    if (!selectedHistoryProduct.value) {
        return priceHistories.value
    }

    return priceHistories.value.filter(
        history =>
            history.product_id ===
            selectedHistoryProduct.value
    )
})
/*
|--------------------------------------------------------------------------
| Navigation
|--------------------------------------------------------------------------
*/

function back() {
    router.visit(
        route('resellers.index')
    )
}


function edit() {
    router.visit(
        route(
            'resellers.edit',
            props.reseller.id
        )
    )
}
function formatRupiah(value) {
    return new Intl.NumberFormat(
        'id-ID',
        {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }
    ).format(value ?? 0)
}
function formatDateTime(value) {
    if (!value) {
        return '-'
    }

    return new Intl.DateTimeFormat(
        'id-ID',
        {
            dateStyle: 'medium',
            timeStyle: 'short',
        }
    ).format(new Date(value))
}
</script>


<template>

<AppLayout>

    <div class="space-y-4">

        <!-- =========================================================
             Header
        ========================================================== -->

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
                    Reseller Details
                </h1>

                <p
                    class="
                        mt-1
                        text-sm
                        font-medium
                        text-gray-700
                    "
                >
                    {{ reseller.reseller_code }}
                    ·
                    {{ reseller.name }}
                </p>

                <p
                    class="
                        mt-0.5
                        text-sm
                        text-gray-500
                    "
                >
                    View reseller information and consignment
                    price configuration.
                </p>

            </div>


            <!-- Actions -->

            <div
                class="
                    flex
                    items-center
                    gap-2
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
                        py-2
                        text-sm
                        font-medium
                        text-gray-700
                        transition
                        hover:bg-gray-50
                    "
                    @click="back"
                >
                    Back
                </button>


                <button
                    type="button"
                    class="
                        rounded-lg
                        bg-gray-900
                        px-3
                        py-2
                        text-sm
                        font-medium
                        text-white
                        transition
                        hover:bg-gray-800
                    "
                    @click="edit"
                >
                    Edit
                </button>

            </div>

        </div>


        <!-- =========================================================
             General Information
        ========================================================== -->

        <div
            class="
                rounded-xl
                border
                border-gray-100
                bg-white
                p-4
                shadow-sm
            "
        >

            <div
                class="
                    mb-4
                    text-sm
                    font-semibold
                    text-gray-900
                "
            >
                General Information
            </div>


            <div
                class="
                    grid
                    grid-cols-1
                    gap-x-8
                    gap-y-4
                    sm:grid-cols-2
                "
            >

                <!-- Code -->

                <div>

                    <div class="text-xs text-gray-500">
                        Code
                    </div>

                    <div
                        class="
                            mt-1
                            text-sm
                            font-medium
                            text-gray-900
                        "
                    >
                        {{ reseller.reseller_code }}
                    </div>

                </div>


                <!-- Name -->

                <div>

                    <div class="text-xs text-gray-500">
                        Name
                    </div>

                    <div
                        class="
                            mt-1
                            text-sm
                            font-medium
                            text-gray-900
                        "
                    >
                        {{ reseller.name }}
                    </div>

                </div>


                <!-- Contact Person -->

                <div>

                    <div class="text-xs text-gray-500">
                        Contact Person
                    </div>

                    <div class="mt-1 text-sm text-gray-900">
                        {{ reseller.contact_person || '-' }}
                    </div>

                </div>


                <!-- Phone -->

                <div>

                    <div class="text-xs text-gray-500">
                        Phone
                    </div>

                    <div class="mt-1 text-sm text-gray-900">
                        {{ reseller.phone || '-' }}
                    </div>

                </div>


                <!-- Email -->

                <div>

                    <div class="text-xs text-gray-500">
                        Email
                    </div>

                    <div class="mt-1 text-sm text-gray-900">
                        {{ reseller.email || '-' }}
                    </div>

                </div>


                <!-- City -->

                <div>

                    <div class="text-xs text-gray-500">
                        City
                    </div>

                    <div class="mt-1 text-sm text-gray-900">
                        {{ reseller.city || '-' }}
                    </div>

                </div>


                <!-- Tax Number -->

                <div>

                    <div class="text-xs text-gray-500">
                        Tax Number
                    </div>

                    <div class="mt-1 text-sm text-gray-900">
                        {{ reseller.tax_number || '-' }}
                    </div>

                </div>


                <!-- Status -->

                <div>

                    <div class="text-xs text-gray-500">
                        Status
                    </div>

                    <div class="mt-1">
                        <StatusBadge
                            :status="reseller.status"
                        />
                    </div>

                </div>


                <!-- Address -->

                <div class="sm:col-span-2">

                    <div class="text-xs text-gray-500">
                        Address
                    </div>

                    <div
                        class="
                            mt-1
                            whitespace-pre-line
                            text-sm
                            text-gray-900
                        "
                    >
                        {{ reseller.address || '-' }}
                    </div>

                </div>

            </div>

        </div>


        <!-- =========================================================
             Consignment Prices
        ========================================================== -->

       <div
    class="
        overflow-hidden
        rounded-xl
        border
        border-gray-100
        bg-white
        shadow-sm
    "
>

    <!-- =========================================================
         Tabs
    ========================================================== -->

    <div
        class="
            border-b
            border-gray-100
            px-4
        "
    >

        <div
            class="
                flex
                gap-6
                overflow-x-auto
            "
        >

            <button
                v-for="tab in tabs"
                :key="tab.key"
                type="button"
                class="
                    relative
                    whitespace-nowrap
                    py-3
                    text-sm
                    font-medium
                    transition
                "
                :class="
                    activeTab === tab.key
                        ? 'text-gray-900'
                        : 'text-gray-500 hover:text-gray-700'
                "
                @click="activeTab = tab.key"
            >

                {{ tab.label }}

                <span
                    v-if="activeTab === tab.key"
                    class="
                        absolute
                        inset-x-0
                        bottom-0
                        h-0.5
                        rounded-full
                        bg-gray-900
                    "
                ></span>

            </button>

        </div>

    </div>


    <!-- =========================================================
         Current Consignment Prices
    ========================================================== -->

    <div
        v-if="activeTab === 'current'"
        class="p-0"
    >

        <DataTable>

            <DataTableHead>

                <DataTableHeaderCell>
                    Product
                </DataTableHeaderCell>

                <DataTableHeaderCell
                    width="180px"
                    align="right"
                >
                    Current Price
                </DataTableHeaderCell>

            </DataTableHead>


            <DataTableBody>

                <DataTableRow
                    v-for="price in currentPrices"
                    :key="price.id"
                >

                   <DataTableCell>

                    <div
                        class="
                            font-medium
                            text-gray-900
                        "
                    >
                        {{ price.product?.name || '-' }}
                    </div>

                    <div
                        class="
                            mt-0.5
                            text-xs
                            text-gray-500
                        "
                    >
                        {{ price.product?.code || '-' }}
                    </div>

                </DataTableCell>

                    <DataTableCell
                        align="right"
                        class="tabular-nums"
                    >
                        {{ formatRupiah(price.price) }}
                    </DataTableCell>

                </DataTableRow>


                <DataTableRow
                    v-if="!currentPrices.length"
                >

                    <DataTableCell :colspan="3">

                        <TableEmpty
                            title="No Consignment Prices"
                            description="No current consignment prices are configured for this reseller."
                        />

                    </DataTableCell>

                </DataTableRow>

            </DataTableBody>

        </DataTable>

    </div>


                       <!-- =========================================================
                            Price History
                        ========================================================== -->

                        <div
                            v-else
                            class="p-0"
                        >

                            <!-- Product Filter -->

                            <div
                            class="
                                flex
                                flex-col
                                gap-3
                                border-b
                                border-gray-100
                                px-4
                                py-3
                                sm:flex-row
                                sm:items-center
                                sm:justify-between
                            "
                        >

                            <!-- Product Filter -->

                            <div
                                class="
                                    w-full
                                    sm:max-w-sm
                                "
                            >

                                <SearchableSelect
                                    v-model="selectedHistoryProduct"
                                    :options="historyProductOptions"
                                    placeholder="All Products"
                                />

                            </div>


                            <!-- Reseller -->

                            <div
                                class="
                                    text-sm
                                    font-medium
                                    text-gray-700
                                    sm:text-right
                                "
                            >

                                <span>
                                    {{ reseller.reseller_code }}
                                </span>

                                <span class="mx-1 text-gray-400">
                                    ·
                                </span>

                                <span>
                                    {{ reseller.name }}
                                </span>

                            </div>

                        </div>


    <!-- Price History Table -->

    <DataTable>

        <DataTableHead>

            <DataTableHeaderCell>
                Product
            </DataTableHeaderCell>

            <DataTableHeaderCell
                width="150px"
                align="right"
            >
                Price
            </DataTableHeaderCell>

            <DataTableHeaderCell width="190px">
                Effective From
            </DataTableHeaderCell>

            <DataTableHeaderCell width="190px">
                Effective To
            </DataTableHeaderCell>

        </DataTableHead>


        <DataTableBody>

            <DataTableRow
                v-for="history in filteredPriceHistories"
                :key="history.id"
            >

                <!-- Product -->

                <DataTableCell>

                    <div
                        class="
                            font-medium
                            text-gray-900
                        "
                    >
                        {{ history.product?.name || '-' }}
                    </div>

                    <div
                        class="
                            mt-0.5
                            text-xs
                            text-gray-500
                        "
                    >
                        {{ history.product?.code || '-' }}
                    </div>

                </DataTableCell>


                <!-- Price -->

                <DataTableCell
                    align="right"
                    class="tabular-nums"
                >
                    {{ formatRupiah(history.price) }}
                </DataTableCell>


                <!-- Effective From -->

                <DataTableCell>
                    {{ formatDateTime(history.effective_from) }}
                </DataTableCell>


                <!-- Effective To -->

                <DataTableCell>

                    <span
                        v-if="history.effective_to"
                    >
                        {{ formatDateTime(history.effective_to) }}
                    </span>

                    <span
                        v-else
                        class="
                            text-sm
                            font-medium
                            text-gray-700
                        "
                    >
                        Current
                    </span>

                </DataTableCell>

            </DataTableRow>


            <!-- Empty -->

            <DataTableRow
                v-if="!filteredPriceHistories.length"
            >

                <DataTableCell :colspan="4">

                    <TableEmpty
                        title="No Price History"
                        description="No consignment price history is available for this reseller."
                    />

                </DataTableCell>

            </DataTableRow>

        </DataTableBody>

    </DataTable>

</div>

</div>

    </div>

</AppLayout>
</template>