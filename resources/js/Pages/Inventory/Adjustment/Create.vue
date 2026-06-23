<script setup>

import { Head } from '@inertiajs/vue3'

import { useForm } from '@inertiajs/vue3'

import { watch } from 'vue'

import axios from 'axios'
import FlatPickr
from 'vue-flatpickr-component'

import 'flatpickr/dist/flatpickr.css'

import AuthenticatedLayout
from '@/Layouts/AuthenticatedLayout.vue'

import SearchableSelect
from '@/Components/SearchableSelect.vue'

const props = defineProps({

    warehouses: Array,

})

const form = useForm({

    warehouse_id: '',

    adjustment_date:

        new Date()

        .toISOString()

        .split('T')[0],

    remarks: '',

    details: [],

})

const dateConfig = {

    dateFormat:

        'Y-m-d',

}

const loadStocks = async () => {

    if (

        !form.warehouse_id

    ) {

        form.details = []

        return

    }

    const response = await axios.get(

        route(

            'inventory-adjustments.warehouse-stocks',

            form.warehouse_id

        )

    )

    form.details =

        response.data.map(

            stock => ({

                product_id:

                    stock.product_id,

                product_name:

                    stock.product.name,

                system_qty:

                    Number(stock.qty),

                physical_qty:

                    Number(stock.qty),

                difference_qty:

                    0,

                unit_cost:

                    Number(

                        stock.product
                        ?.cost_price ?? 0

                    ),

                remarks: '',

            })

        )

}

watch(

    () => form.warehouse_id,

    () => {

        loadStocks()

    }

)

const updateDifference = (

    row

) => {

    row.difference_qty =

        Number(
            row.physical_qty
        )

        -

        Number(
            row.system_qty
        )

}

const submit = () => {
    if (

    !form.warehouse_id

) {

    alert(

        'Warehouse is required.'

    )

    return

}

    const filteredDetails =

        form.details.filter(

            item =>

                item.difference_qty != 0

        )

    if (

        filteredDetails.length === 0

    ) {

        alert(

            'No differences found.'

        )

        return

    }

    form.details =

        filteredDetails

    form.post(

        route(

            'inventory-adjustments.store'

        )

    )

}

</script>
<template>
  
        <Head
            title="Inventory Adjustment"
        />

        <AuthenticatedLayout>

            <template #header>

                    <div>

                        <h2
                            class="text-3xl font-bold text-gray-800"
                        >

                            Inventory Adjustment

                        </h2>

                        <p
                            class="mt-1 text-sm text-gray-500"
                        >

                            Physical stock adjustment.

                        </p>
        
                    </div>
            </template>
                      <!-- Summary Cards -->

                            <div

                                class="
                                    mt-6
                                    grid
                                    gap-6
                                    md:grid-cols-3
                                "

                            >

                                <!-- Products -->

                                <div

                                    class="
                                        rounded-3xl
                                        bg-white
                                        p-6
                                        shadow-sm
                                    "

                                >

                                    <p

                                        class="
                                            text-sm
                                            text-gray-500
                                        "

                                    >

                                        Products

                                    </p>

                                    <h3

                                        class="
                                            mt-2
                                            text-4xl
                                            font-bold
                                            text-blue-600
                                        "

                                    >

                                        {{ form.details.length }}

                                    </h3>

                                    <p

                                        class="
                                            mt-1
                                            text-xs
                                            text-gray-400
                                        "

                                    >

                                        Total products loaded

                                    </p>

                                </div>

                                <!-- Current Stock -->

                                <div

                                    class="
                                        rounded-3xl
                                        bg-white
                                        p-6
                                        shadow-sm
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

                                    <h3

                                        class="
                                            mt-2
                                            text-4xl
                                            font-bold
                                            text-green-600
                                        "

                                    >

                                        {{

                                            form.details

                                            .reduce(

                                                (

                                                    total,

                                                    item

                                                ) =>

                                                    total +

                                                    Number(
                                                        item.system_qty
                                                    ),

                                                0

                                            )

                                        }}

                                        PCS

                                    </h3>

                                    <p

                                        class="
                                            mt-1
                                            text-xs
                                            text-gray-400
                                        "

                                    >

                                        Total warehouse stock

                                    </p>

                                </div>

                                <!-- Differences -->

                                <div

                                    class="
                                        rounded-3xl
                                        bg-white
                                        p-6
                                        shadow-sm
                                    "

                                >

                                    <p

                                        class="
                                            text-sm
                                            text-gray-500
                                        "

                                    >

                                        Differences

                                    </p>

                                    <h3

                                        class="
                                            mt-2
                                            text-4xl
                                            font-bold
                                            text-orange-500
                                        "

                                    >

                                        {{

                                            form.details

                                            .filter(

                                                item =>

                                                    item.difference_qty != 0

                                            )

                                            .length

                                        }}

                                    </h3>

                                    <p

                                        class="
                                            mt-1
                                            text-xs
                                            text-gray-400
                                        "

                                    >

                                        Items with variance

                                    </p>

                                </div>

                            </div>
                            <br>
                            <!-- End Summary Cards -->
           
                <!-- form header-->
                 <div
                        class="
                            rounded-3xl
                            bg-white
                            p-6
                            shadow-sm
                        "
                    >

                            <div
                                class="
                                    grid
                                    gap-4
                                    md:grid-cols-3
                                "
                            >

                                    <div>

                                        <label
                                            class="mb-1 block text-sm"
                                        >

                                            Adjustment Date

                                        </label>

                                       <FlatPickr

                                                v-model="
                                                    form.adjustment_date
                                                "

                                                :config="
                                                    dateConfig
                                                "

                                                class="
                                                    w-full
                                                    rounded-xl
                                                    border-gray-300
                                                "

                                            />
                                    </div>

                                    <div>

                                        <label
                                            class="mb-1 block text-sm"
                                        >

                                            Warehouse

                                        </label>

                                        <SearchableSelect

                                            v-model="
                                                form.warehouse_id
                                            "

                                            :options="
                                                warehouses
                                            "

                                            label="name"

                                            value-key="id"

                                            placeholder="Select Warehouse"

                                        />

                                    </div>

                                    <div>

                                        <label
                                            class="mb-1 block text-sm"
                                        >

                                            Remarks

                                        </label>

                                        <input

                                            v-model="
                                                form.remarks
                                            "

                                            class="
                                                w-full
                                                rounded-xl
                                                border-gray-300
                                            "
                                        >

                                    </div>

                            </div>

                     </div>
                 <!-- end form header -->
                  <div

                        class="
                            mt-6
                            overflow-hidden
                            rounded-3xl
                            bg-white
                            shadow-sm
                        "

                    >
                                <div

                                class="
                                    border-b
                                    px-6
                                    py-4
                                "

                                >

                                        <h3

                                            class="
                                                text-lg
                                                font-semibold
                                            "

                                        >

                                            Stock Opname Items

                                        </h3>

                                </div>
                            <div
                                class="overflow-x-auto"
                            >

                                <table
                                    class="min-w-full"
                                >

                                    <thead>

                                        <tr
                                            class="
                                                bg-gray-50
                                                            text-left
                                                        "
                                                    >

                                                        <th
                                                            class="
                                                                px-6
                                                                py-4
                                                                font-semibold
                                                            "
                                                        >
                                                            Product
                                                        </th>

                                                        <th
                                                            class="
                                                                px-6
                                                                py-4
                                                                text-right
                                                                font-semibold
                                                            "
                                                        >
                                                            System Qty
                                                        </th>

                                                        <th
                                                            class="
                                                                px-6
                                                                py-4
                                                                text-right
                                                                font-semibold
                                                            "
                                                        >
                                                            Physical Qty
                                                        </th>

                                                        <th
                                                            class="
                                                                px-6
                                                                py-4
                                                                text-right
                                                                font-semibold
                                                            "
                                                        >
                                                            Difference
                                                        </th>

                                                        <th
                                                            class="
                                                                px-6
                                                                py-4
                                                                text-right
                                                                font-semibold
                                                            "
                                                        >
                                                            Unit Cost
                                                        </th>

                                                        <th
                                                            class="
                                                                px-6
                                                                py-4
                                                                font-semibold
                                                            "
                                                        >
                                                            Remarks
                                                        </th>

                                                    </tr>

                                                </thead>

                                                <tbody>

                                                    <tr

                                                        v-for="
                                                            row
                                                            in
                                                            form.details
                                                        "

                                                        :key="
                                                            row.product_id
                                                        "

                                                        class="
                                                            border-t
                                                        "

                                                    >

                                                        <!-- Product -->

                                                        <td
                                                            class="
                                                                px-6
                                                                py-4
                                                                font-medium
                                                            "
                                                        >

                                                            {{ row.product_name }}

                                                        </td>

                                                        <!-- System Qty -->

                                                        <td
                                                            class="
                                                                px-6
                                                                py-4
                                                                text-right
                                                            "
                                                        >

                                                            {{ row.system_qty }}

                                                        </td>

                                                        <!-- Physical Qty -->

                                                        <td
                                                            class="
                                                                px-6
                                                                py-4
                                                            "
                                                        >

                                                            <input

                                                                v-model="
                                                                    row.physical_qty
                                                                "

                                                                @input="
                                                                    updateDifference(
                                                                        row
                                                                    )
                                                                "

                                                                type="number"

                                                                class="
                                                                    w-24
                                                                    rounded-lg
                                                                    border-gray-300
                                                                    text-right
                                                                "

                                                            >

                                                        </td>

                                                        <!-- Difference -->

                                                        <td

                                                            class="
                                                                px-6
                                                                py-4
                                                                text-right
                                                                font-semibold
                                                            "

                                                        >

                                                            <span

                                                                :class="

                                                                    row.difference_qty > 0

                                                                    ? 'text-green-600'

                                                                    : row.difference_qty < 0

                                                                    ? 'text-red-600'

                                                                    : 'text-gray-500'

                                                                "

                                                            >

                                                                {{ row.difference_qty }}

                                                            </span>

                                                        </td>

                                                        <!-- Unit Cost -->

                                                        <td
                                                            class="
                                                                px-6
                                                                py-4
                                                                text-right
                                                            "
                                                        >

                                                            Rp

                                                            {{

                                                                Number(
                                                                    row.unit_cost
                                                                )

                                                                .toLocaleString(
                                                                    'id-ID'
                                                                )

                                                            }}

                                                        </td>

                                                        <!-- Remarks -->

                                                        <td
                                                            class="
                                                                px-6
                                                                py-4
                                                            "
                                                        >

                                                            <input

                                                                v-model="
                                                                    row.remarks
                                                                "

                                                                class="
                                                                    w-full
                                                                    rounded-lg
                                                                    border-gray-300
                                                                "

                                                            >

                                    </td>

                                 </tr>

                            </tbody>

                         </table>

                    </div>

                </div>
               <!-- end teable-->
                <!-- tombol simpn -->
                 <div

                        class="
                            mt-6
                            flex
                            justify-end
                        "

                    >

                        <button

                            @click="submit"

                            type="button"

                            class="
                                rounded-2xl
                                bg-blue-600
                                px-6
                                py-3
                                font-medium
                                text-white
                                transition
                                hover:bg-blue-700
                            "

                        >

                            Save Draft

                        </button>

                    </div>
                 <!-- end tombol simpan-->
           
        </AuthenticatedLayout>

</template>
