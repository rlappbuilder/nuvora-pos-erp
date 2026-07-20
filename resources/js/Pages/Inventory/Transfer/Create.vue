<script setup>

import { Head } from '@inertiajs/vue3'

import {

    useForm,

    router

} from '@inertiajs/vue3'

import {

    ref

} from 'vue'

import axios from 'axios'

import FlatPickr
from 'vue-flatpickr-component'

import 'flatpickr/dist/flatpickr.css'

import SearchableSelect
from '@/Components/Form/SearchableSelect.vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import {

    TrashIcon

} from '@heroicons/vue/24/outline'
const props = defineProps({

    warehouses: Array,

    products: Array,

})

const dateConfig = {

    dateFormat:

        'Y-m-d',

}

const warehouseProducts =
ref([])

const selectedProduct =
ref(null)

const form = useForm({

    transfer_date:

        new Date()

        .toISOString()

        .split('T')[0],

    from_warehouse_id: '',

    to_warehouse_id: '',

    remarks: '',

    details: [],

})

const loadWarehouseStocks =
async () => {

    form.details = []

    selectedProduct.value = null

    if (

        !form.from_warehouse_id

    ) {

        warehouseProducts.value = []

        return

    }

    try {

        const response =

            await axios.get(

                route(

                    'stock-transfers.warehouse-stocks',

                    form.from_warehouse_id

                )

            )

        warehouseProducts.value =

            response.data

    }

    catch (

        error

    ) {

        console.error(

            error

        )

    }

}

const addProduct =
() => {

    if (

        !selectedProduct.value

    ) {

        return

    }

    const exists =

        form.details.find(

            item =>

                item.product_id

                ===

                selectedProduct.value

        )

    if (

        exists

    ) {

        return

    }

    const product =

        warehouseProducts.value.find(

            item =>

                item.product_id

                ===

                selectedProduct.value

        )

    if (

        !product

    ) {

        return

    }

    form.details.push({

        product_id:

            product.product_id,

        product_name:

            product.product_name,

        available_qty:

            Number(

                product.available_qty

            ),

        qty: 1,

        unit_cost:

            Number(

                product.unit_cost

            ),

        total_cost:

            Number(

                product.unit_cost

            ),

        remarks: '',

    })

    selectedProduct.value = null

}

const removeProduct =
(
    index
) => {

    form.details.splice(

        index,

        1

    )

}

const calculateTotal =
(
    item
) => {

    item.total_cost =

        Number(
            item.qty
        )

        *

        Number(
            item.unit_cost
        )

}

const submit = () => {

    form.post(

        route(

            'stock-transfers.store'

        )

    )

}
const hasInvalidQty = () => {

    return form.details.some(

        item =>

            Number(
                item.qty
            )

            >

            Number(
                item.available_qty
            )

    )

}
</script>
<template>

<Head

    title="Create Stock Transfer"

/>


    <AppLayout>

        <template #header>

            <div>

                <h2
                    class="text-3xl font-bold text-gray-800"
                >
                     Create Stock Transfer
                </h2>

                <p
                    class="mt-1 text-sm text-gray-500"
                >
                    
                    Move inventory between warehouses.
                </p>

            </div>

        </template>

    <div

        class="
            py-6
        "

    >

        <!-- summary card-->
            <div

                class="
                    mb-6
                    grid
                    gap-6
                    md:grid-cols-3
                "

            >
                <!-- card produk -->
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
                            text-3xl
                            font-bold
                            text-blue-600
                        "

                    >

                        {{ form.details.length }}

                    </h3>

                </div>
                <!-- card trf quantyti-->
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

                        Transfer Qty

                    </p>

                    <h3

                        class="
                            mt-2
                            text-3xl
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
                                        item.qty
                                    ),

                                0

                            )

                        }}

                    </h3>

                </div>

                <!-- end trf qty-->
                 <!-- card tr value-->
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

                                Transfer Value

                            </p>

                            <h3

                                class="
                                    mt-2
                                    whitespace-nowrap
                                    text-3xl
                                    font-bold
                                    text-orange-500
                                "

                            >

                                Rp

                                {{

                                    form.details

                                    .reduce(

                                        (

                                            total,

                                            item

                                        ) =>

                                            total +

                                            Number(
                                                item.total_cost
                                            ),

                                        0

                                    )

                                    .toLocaleString(
                                        'id-ID'
                                    )

                                }}

                            </h3>

                        </div>

                    </div>
                 <!-- end card trf value-->
                  <!-- trf information card-->
                               <div

                                class="
                                    rounded-3xl
                                    bg-white
                                    p-6
                                    shadow-sm
                                "

                            >

                                <h2

                                    class="
                                        mb-6
                                        text-xl
                                        font-bold
                                    "

                                >

                                    Transfer Information

                                </h2>
                              <!--   <pre>

                                {{ warehouses }}

                                </pre> -->
                                <!-- grid form-->
                                    <div

                                        class="
                                           grid
                                            gap-6
                                            md:grid-cols-2
                                        "

                                    >
                                        
                               
                                        <!-- transfer date-->
                                            <div>

                                                <label

                                                    class="
                                                        mb-2
                                                        block
                                                        text-sm
                                                        font-medium
                                                    "

                                                >

                                                    Transfer Date

                                                </label>

                                                <FlatPickr

                                                    v-model="
                                                        form.transfer_date
                                                    "

                                                    :config="
                                                        dateConfig
                                                    "

                                                    class="
                                                        w-full
                                                        rounded-2xl
                                                        border-gray-300
                                                    "

                                                />

                                            </div>
                                        <!-- end transfer date-->
                                                <!-- warehouse option-->
                                                <div>

                                                        <label

                                                            class="
                                                                mb-2
                                                                block
                                                                text-sm
                                                                font-medium
                                                            "

                                                        >

                                                            From Warehouse

                                                        </label>

                                                        <SearchableSelect

                                                            v-model="
                                                                form.from_warehouse_id
                                                            "
                                                            
                                                             :options="
                                                                        warehouses
                                                                    "
                                                             
                                                                    placeholder="

                                                                                                                                
                                                                        Select Warehouse

                                                                    "

                                                                    @update:modelValue="
                                                                        loadWarehouseStocks
                                                                    "
                                                           
                                                            />
                                                    </div>
                                                <!-- end warehouse option-->
                                                 
                                                <!-- transfer to warehouse-->
                                                    <div>

                                                        <label

                                                            class="
                                                                mb-2
                                                                block
                                                                text-sm
                                                                font-medium
                                                            "

                                                        >

                                                            To Warehouse

                                                        </label>

                                                        <SearchableSelect

                                                            v-model="
                                                                form.to_warehouse_id
                                                                
                                                            "
                                                                class="
                                                                    w-full
                                                                    rounded-2xl
                                                                    border-gray-300
                                                                    text-left
                                                                "
                                                         :options="

                                                                warehouses.filter(

                                                                    warehouse =>

                                                                        warehouse.id
                                                                        !=
                                                                        form.from_warehouse_id

                                                                )

                                                            "

                                                            placeholder="

                                                                Destination Warehouse

                                                            "
                                                         />    
  
                                                    </div>
                                                
                                                <!-- end transfer to-->
                                                    <!-- Remarks-->
                                                        <div>
                                                             <label

                                                                class="
                                                                    mb-2
                                                                    block
                                                                    text-sm
                                                                    font-medium
                                                                "

                                                            >

                                                                Remarks

                                                            </label>

                                                            <textarea

                                                                v-model="
                                                                    form.remarks
                                                                "

                                                                rows="3"

                                                                class="
                                                                    w-full
                                                                    rounded-2xl
                                                                    border-gray-300
                                                                    text-left
                                                                "

                                                                placeholder="

                                                                    Transfer notes...

                                                                "

                                                            />

                                                        </div>
                                                    <!-- end remarks-->
                                    </div><!-- end grid form-->
                            </div><!-- end trf information card -->
                           
                                        <!-- transfer items -->

                                        <div

                                            class="
                                                mt-6
                                                rounded-3xl
                                                bg-white
                                                p-6
                                                shadow-sm
                                            "

                                        >

                                            <div

                                                class="
                                                    flex
                                                    items-center
                                                    justify-between
                                                "

                                            >

                                                <div>

                                                    <h2

                                                        class="
                                                            text-xl
                                                            font-bold
                                                        "

                                                    >

                                                        Transfer Items

                                                    </h2>

                                                    <p

                                                        class="
                                                            text-sm
                                                            text-gray-500
                                                        "

                                                    >

                                                        Add products to transfer.

                                                    </p>

                                                </div>

                                                <span

                                                    class="
                                                        rounded-full
                                                        bg-blue-100
                                                        px-4
                                                        py-2
                                                        text-sm
                                                        font-medium
                                                        text-blue-700
                                                    "

                                                >

                                                    {{ form.details.length }}

                                                    Products

                                                </span>

                                            </div>

                                            <div

                                                class="
                                                    mt-6
                                                    grid
                                                    gap-4
                                                    md:grid-cols-12
                                                "

                                            >

                                                <div

                                                    class="
                                                        md:col-span-10
                                                    "

                                                >

                                                    <SearchableSelect

                                                        v-model="
                                                            selectedProduct
                                                        "

                                                        :options="

                                                            warehouseProducts.map(

                                                                item => ({

                                                                    id:
                                                                        item.product_id,

                                                                    name:
                                                                        item.product_name,

                                                                })

                                                            )

                                                        "

                                                        placeholder="

                                                            Search Product...

                                                        "

                                                    />

                                                </div>

                                                <div

                                                    class="
                                                        md:col-span-2
                                                    "

                                                >

                                                    <button

                                                        @click="
                                                            addProduct
                                                        "

                                                        type="button"

                                                        class="
                                                            w-full
                                                            rounded-2xl
                                                            bg-blue-600
                                                            px-4
                                                            py-3
                                                            font-medium
                                                            text-white
                                                            transition
                                                            hover:bg-blue-700
                                                        "

                                                    >

                                                        + Add Product

                                                    </button>

                                                </div>

                                            </div>

                                            <div

                                                v-if="
                                                    !form.details.length
                                                "

                                                class="
                                                    py-16
                                                    text-center
                                                "

                                            >

                                                <div

                                                    class="
                                                        text-6xl
                                                    "

                                                >

                                                    📦

                                                </div>

                                                <h3

                                                    class="
                                                        mt-4
                                                        text-lg
                                                        font-semibold
                                                    "

                                                >

                                                    No Products Added

                                                </h3>

                                                <p

                                                    class="
                                                        mt-2
                                                        text-gray-500
                                                    "

                                                >

                                                    Search and add products to transfer.

                                                </p>

                                            </div>

                                        </div>

                                        <!-- end transfer items -->
                        <!-- table wrapper-->
                      <div

                                            class="
                                                mt-6
                                                rounded-3xl
                                                bg-white
                                                p-6
                                                shadow-sm
                                            "

                                        >
                         <div

                            v-if="
                                form.details.length
                            "

                            class="
                                mt-6
                                overflow-x-auto
                            "

                                >

                                    <table

                                        class="
                                            min-w-full
                                            table-fixed
                                        "

                                    >
                                        <!-- table header-->
                                            <thead>

                                            <tr

                                                class="
                                                    bg-gray-50
                                                "

                                            >

                                                <th

                                                    class="
                                                        w-40
                                                        px-4
                                                        py-3
                                                        text-left
                                                    "

                                                >

                                                    Product

                                                </th>

                                                <th

                                                    class="
                                                        w-30
                                                        px-4
                                                        py-3
                                                        text-right
                                                    "

                                                >

                                                    Available

                                                </th>

                                                <th

                                                    class="
                                                         w-30
                                                        px-4
                                                        py-3
                                                        text-right
                                                    "

                                                >

                                                    Transfer Qty

                                                </th>

                                                <th

                                                    class="
                                                         w-30
                                                        px-4
                                                        py-3
                                                        text-right
                                                    "

                                                >

                                                    Unit Cost

                                                </th>

                                                <th

                                                    
                                                        class="
                                                            w-60
                                                            px-4
                                                            py-3
                                                            text-right
                                                        "
                                                    

                                                >

                                                    Total Cost

                                                </th>

                                                <th

                                                    class="
                                                    w-40
                                                        px-4
                                                        py-3
                                                        text-left
                                                    "

                                                >

                                                    Remarks

                                                </th>

                                                <th

                                                    class="
                                                    w-30
                                                        px-4
                                                        py-3
                                                        text-center
                                                    "

                                                >

                                                    Action

                                                </th>

                                            </tr>

                                            </thead> <!-- end table header-->
                                       
                                        
                                          <tbody> <!-- table body -->

                                                <tr

                                                    v-for="

                                                        (

                                                            item,

                                                            index

                                                        )

                                                        in

                                                        form.details

                                                    "

                                                    :key="

                                                        item.product_id

                                                    "

                                                    class="
                                                        border-t
                                                    "

                                                      >
                                                    <td

                                                        class="
                                                            px-4
                                                            py-3
                                                            font-medium
                                                        "

                                                    >

                                                        {{ item.product_name }}

                                                    </td>
                                                    <td

                                                        class="
                                                            px-4
                                                            py-3
                                                            text-right
                                                        "

                                                    >

                                                        {{ item.available_qty }}

                                                    </td>
                                                    <td

                                                        class="
                                                            px-4
                                                            py-3
                                                        "

                                                    >

                                                            <input

                                                                v-model="
                                                                    item.qty
                                                                "

                                                                @input="
                                                                    calculateTotal(
                                                                        item
                                                                    )
                                                                "

                                                                type="number"

                                                                min="1"

                                                                :max="
                                                                    item.available_qty
                                                                "

                                                                :class="

                                                                        Number(item.qty)

                                                                        >

                                                                        Number(item.available_qty)

                                                                        ?

                                                                        'border-red-500 bg-red-50'

                                                                        :

                                                                        'border-gray-300'

                                                                    "

                                                                    class="
                                                                                w-24
                                                                                min-w-[96px]
                                                                                max-w-[96px]
                                                                                rounded-xl
                                                                                border-gray-300
                                                                                text-right
                                                                            "

                                                            />
                                                           

                                                    </td>
                                                     <td

                                                        class="
                                                            px-4
                                                            py-3
                                                            text-right
                                                            text-gray-600
                                                        "

                                                        >

                                                        Rp

                                                        {{

                                                            Number(
                                                                item.unit_cost
                                                            )

                                                            .toLocaleString(
                                                                'id-ID'
                                                            )

                                                        }}

                                                    </td>
                                                   <td

                                                        class="
                                                                w-48
                                                                px-4
                                                                py-3
                                                                text-right
                                                                whitespace-nowrap
                                                                tabular-nums
                                                                font-medium
                                                                text-green-600
                                                            "

                                                    >

                                                        Rp&nbsp;

                                                        {{

                                                            Number(

                                                                item.total_cost

                                                            )

                                                            .toLocaleString(

                                                                'id-ID'

                                                            )

                                                        }}

                                                    </td>
                                                    <td

                                                        class="
                                                            px-4
                                                            py-3
                                                        "

                                                    >

                                                        <input

                                                            v-model="
                                                                item.remarks
                                                            "

                                                            type="text"

                                                            placeholder="

                                                                Notes...

                                                            "

                                                            class="
                                                                w-full
                                                                rounded-xl
                                                                border-gray-300
                                                            "

                                                        />

                                                    </td>
                                                  <td

                                                        class="
                                                            px-4
                                                            py-3
                                                            text-center
                                                        "

                                                    >

                                                        <button

                                                            @click="
                                                                removeProduct(
                                                                    index
                                                                )
                                                            "

                                                            type="button"

                                                            class="
                                                                rounded-xl
                                                                p-2
                                                                text-red-600
                                                                transition
                                                                hover:bg-red-50
                                                                hover:text-red-700
                                                            "

                                                        >

                                                            <TrashIcon

                                                                class="
                                                                    h-5
                                                                    w-5
                                                                "

                                                            />

                                                        </button>

                                                    </td>
                                                </tr>
                                            </tbody>
                                    </table>
                                   
                        </div>
                    </div>
                        <!-- end table wrapper-->
                    <!-- button -->
                         <!-- warning -->
                          <br>
                           <div

                                class="
                                    mb-4
                                    min-h-[60px]
                                "

                            >

                                <div

                                    v-if="
                                        hasInvalidQty()
                                    "

                                    class="
                                        rounded-2xl
                                        border
                                        border-red-200
                                        bg-red-50
                                        p-4
                                        text-sm
                                        text-red-700
                                    "

                                >

                                    Some transfer quantities exceed available stock.

                                </div>

                            </div>
                    
                        <!-- end warning-->
                                <div

                                    class="
                                        mt-6
                                        flex
                                        items-center
                                        justify-end
                                        gap-3
                                    "

                                >

                                    <button

                                        @click="
                                            router.visit(
                                                route(
                                                    'stock-transfers.index'
                                                )
                                            )
                                        "

                                        type="button"

                                        class="
                                            rounded-2xl
                                            border
                                            border-gray-300
                                            px-6
                                            py-3
                                            font-medium
                                            text-gray-700
                                            transition
                                            hover:bg-gray-50
                                        "

                                    >

                                        Back

                                    </button>

                                    <button

                                        @click="
                                            submit
                                        "

                                        :disabled="
                                            hasInvalidQty()
                                        "

                                        type="button"

                                        class="
                                            rounded-2xl
                                            px-6
                                            py-3
                                            font-medium
                                            text-white
                                            transition
                                        "

                                        :class="

                                            hasInvalidQty()

                                            ?

                                            'cursor-not-allowed bg-gray-400'

                                            :

                                            'bg-blue-600 hover:bg-blue-700'

                                        "

                                    >

                                        Save Draft

                                    </button>

                                </div><!-- end button-->
                 </div>                          
    </AppLayout>
</template>