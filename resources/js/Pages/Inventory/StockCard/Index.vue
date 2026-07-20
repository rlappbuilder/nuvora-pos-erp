<script setup>

import { Head, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import FlatPickr
from 'vue-flatpickr-component'

import 'flatpickr/dist/flatpickr.css'

import SearchableSelect
from '@/Components/Form/SearchableSelect.vue'
import AppLayout from '@/Layouts/AppLayout.vue'
const props = defineProps({

    movements: Object,

    products: Array,

    warehouses: Array,

    filters: Object,

     summary: Object,

})

const selectedProduct = ref(
    props.filters.product_id || null
    
)
const selectedWarehouse = ref(
    props.filters.warehouse_id || null
)
const dateFrom = ref(
    props.filters.date_from || null
)

const dateTo = ref(
    props.filters.date_to || null
)
watch(

    [

        selectedProduct,

        selectedWarehouse,

        dateFrom,

        dateTo

    ],

    () => {

        router.get(

            route(
                'stock-card.index'
            ),

            {

                product_id:
                    selectedProduct.value,

                warehouse_id:
                    selectedWarehouse.value,

                date_from:
                    dateFrom.value,

                date_to:
                    dateTo.value,

            },

            {

                preserveState: true,

                replace: true,

            }

        )

    }

)


</script>
<template>

    <Head title="Stock Card" />

    <AppLayout>

        <template #header>

            <div>

                <h2
                    class="text-3xl font-bold text-gray-800"
                >
                    Stock Card
                </h2>

                <p
                    class="mt-1 text-sm text-gray-500"
                >
                    Inventory movement history.
                </p>

            </div>

        </template>

        <div
            class="rounded-2xl bg-white p-6 shadow-sm"
        >
        <!-- summery card-->
                            <div
                        class="
                            mb-6
                            grid
                            grid-cols-4
                            gap-4
                        "
                    >

                        <div
                            class="
                                rounded-xl
                                bg-white
                                p-4
                                shadow
                            "
                        >

                            <p
                                class="
                                    text-sm
                                    text-gray-500
                                "
                            >

                                Current Stock

                            </p>

                            <p
                                class="
                                    mt-2
                                    text-2xl
                                    font-bold
                                    text-blue-600
                                "
                            >

                                {{ summary.current_stock }}

                            </p>

                        </div>

                        <div
                            class="
                                rounded-xl
                                bg-white
                                p-4
                                shadow
                            "
                        >

                            <p
                                class="
                                    text-sm
                                    text-gray-500
                                "
                            >

                                Total In

                            </p>

                            <p
                                class="
                                    mt-2
                                    text-2xl
                                    font-bold
                                    text-green-600
                                "
                            >

                                {{ summary.total_in }}

                            </p>

                        </div>

                        <div
                            class="
                                rounded-xl
                                bg-white
                                p-4
                                shadow
                            "
                        >

                            <p
                                class="
                                    text-sm
                                    text-gray-500
                                "
                            >

                                Total Out

                            </p>

                            <p
                                class="
                                    mt-2
                                    text-2xl
                                    font-bold
                                    text-red-600
                                "
                            >

                                {{ summary.total_out }}

                            </p>

                        </div>

                        <div
                            class="
                                rounded-xl
                                bg-white
                                p-4
                                shadow
                            "
                        >

                            <p
                                class="
                                    text-sm
                                    text-gray-500
                                "
                            >

                                Transactions

                            </p>

                            <p
                                class="
                                    mt-2
                                    text-2xl
                                    font-bold
                                "
                            >

                                {{ summary.transactions }}

                            </p>

                        </div>

                    </div>
        <!-- end Summary Card -->
        <!-- filter area-->
          <div class="
                        mb-6
                        flex
                        gap-4
                    ">
            <!-- div product-->
            <div class="w-80">
                <label
                    class="
                        mb-2
                        block
                        text-sm
                        font-medium
                    "
                >

                    Product

                </label>

                <SearchableSelect

                    v-model="
                        selectedProduct
                    "

                    :options="
                        products
                    "

                    label="name"

                    value-key="id"

                    placeholder="
                        Cari Produk...
                    "

                />

            </div>
             <!-- filter warehous-->
              <div class="w-80">

                    <label
                        class="
                            mb-2
                            block
                            text-sm
                            font-medium
                        "
                    >

                        Warehouse

                    </label>

                    <SearchableSelect

                        v-model="
                            selectedWarehouse
                        "

                        :options="
                            warehouses
                        "

                        label="name"

                        value-key="id"

                        placeholder="
                            Cari Warehouse...
                        "

                    />

                </div>
              <!-- end filter warehouste-->
               <!--  start filter date range-->
                 <div class="w-52">

                        <label
                            class="
                                mb-2
                                block
                                text-sm
                                font-medium
                            "
                        >

                            Date From

                        </label>

                        <FlatPickr

                            v-model="
                                dateFrom
                            "

                            class="
                                w-full
                                rounded-lg
                                border
                                px-3
                                py-2
                            "

                        />

                    </div>

                    <div class="w-52">

                        <label
                            class="
                                mb-2
                                block
                                text-sm
                                font-medium
                            "
                        >

                            Date To

                        </label>

                        <FlatPickr

                            v-model="
                                dateTo
                            "

                            class="
                                w-full
                                rounded-lg
                                border
                                px-3
                                py-2
                            "

                        />

                    </div>
                <!-- end filter date range-->
            </div>
         <!-- end filter area-->
         
            <table
                class="min-w-full"
            >
                    <!-- table head-->
                   <thead>

                    <tr class="border-b bg-gray-50">

                        <th class="p-3 text-left">
                            Date
                        </th>

                        <th class="p-3 text-left">
                            Reference
                        </th>

                        <th class="p-3 text-left">
                            Type
                        </th>

                        <th class="p-3 text-left">
                            Product
                        </th>

                        <th class="p-3 text-left">
                            Warehouse
                        </th>

                        <th class="p-3 text-right">
                            Qty In
                        </th>

                        <th class="p-3 text-right">
                            Qty Out
                        </th>

                        <th class="p-3 text-right">
                            Balance
                        </th>

                    </tr>

                    </thead>
                    <!-- end table head-->

                <tbody>

                        <tr

                            v-for="
                                movement
                                in
                                movements.data
                            "

                            :key="
                                movement.id
                            "

                            class="
                                border-b
                                hover:bg-gray-50
                            "

                        >

                            <td class="p-3">

                                {{
                                    movement.transaction_date
                                }}

                            </td>

                            <td class="p-3 font-medium">

                                {{
                                    movement.reference_number
                                }}

                            </td>

                            <td class="p-3">

                                <span
                                    class="
                                        rounded-full
                                        bg-blue-100
                                        px-2
                                        py-1
                                        text-xs
                                        font-medium
                                        text-blue-700
                                    "
                                >

                                    {{
                                        movement.reference_type
                                    }}

                                </span>

                            </td>

                            <td class="p-3">

                                {{
                                    movement.product?.name
                                }}

                            </td>

                            <td class="p-3">

                                {{
                                    movement.warehouse?.name
                                }}

                            </td>

                            <td
                                class="
                                    p-3
                                    text-right
                                    font-medium
                                    text-green-600
                                "
                            >

                                {{
                                    movement.qty_in
                                }}

                            </td>

                            <td
                                class="
                                    p-3
                                    text-right
                                    font-medium
                                    text-red-600
                                "
                            >

                                {{
                                    movement.qty_out
                                }}

                            </td>

                            <td
                                class="
                                    p-3
                                    text-right
                                    font-bold
                                "
                            >

                                {{
                                    movement.balance_qty
                                }}

                            </td>

                        </tr>

                 </tbody>
            </table>

        </div>

    </AppLayout>

</template>