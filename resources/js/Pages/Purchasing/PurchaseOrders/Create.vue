
<script setup>

import { Head, Link,useForm } from '@inertiajs/vue3'

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({

    suppliers: Array,

    warehouses: Array,
    
    products: Array,

})

const form = useForm({

    supplier_id: '',

    warehouse_id: '',

    order_date: '',

    expected_date: '',

    remarks: '',
    items: [
    {
        product_id: '',
        qty: 1,
        unit_cost: 0,
    }
]

})
const addItem = () => {

    form.items.push({

        product_id: '',

        qty: 1,

        unit_cost: 0,

    })

}
const submit = () => {

    form.post(

        route(

            'purchase-orders.store'

        )

    )

}
const removeItem = (index) => {

    if (

        form.items.length > 1

    ) {

        form.items.splice(

            index,

            1

        )

    }

}

</script>
<template>

<AuthenticatedLayout>

    <Head title="Create Purchase Order" />

    <div class="p-6">

        <h1
            class="mb-6 text-3xl font-bold"
        >
            Create Purchase Order
        </h1>

        <div
            class="grid gap-4 md:grid-cols-2"
        >

            <div>

                <label>
                    Supplier
                </label>

                <select

                    v-model="
                        form.supplier_id
                    "

                    class="mt-1 w-full rounded-lg border"

                >

                    <option value="">
                        Select Supplier
                    </option>

                    <option

                        v-for="
                            supplier
                            in suppliers
                        "

                        :key="
                            supplier.id
                        "

                        :value="
                            supplier.id
                        "

                    >

                        {{ supplier.name }}

                    </option>

                </select>

            </div>

            <div>

                <label>
                    Warehouse
                </label>

                <select

                    v-model="
                        form.warehouse_id
                    "

                    class="mt-1 w-full rounded-lg border"

                >

                    <option value="">
                        Select Warehouse
                    </option>

                    <option

                        v-for="
                            warehouse
                            in warehouses
                        "

                        :key="
                            warehouse.id
                        "

                        :value="
                            warehouse.id
                        "

                    >

                        {{ warehouse.name }}

                    </option>

                </select>

            </div>

            <div>

                <label>
                    Order Date
                </label>

                <input

                    type="date"

                    v-model="
                        form.order_date
                    "

                    class="mt-1 w-full rounded-lg border"

                >

            </div>

            <div>

                <label>
                    Expected Date
                </label>

                <input

                    type="date"

                    v-model="
                        form.expected_date
                    "

                    class="mt-1 w-full rounded-lg border"

                >

            </div>

        </div>

        <div class="mt-4">

            <label>
                Remarks
            </label>

            <textarea

                v-model="
                    form.remarks
                "

                rows="3"

                class="mt-1 w-full rounded-lg border"

            />

        </div>
        <!-- add item table -->
             <div class="mt-8">

                <h2
                    class="mb-4 text-xl font-bold"
                >
                    Purchase Order Items
                </h2>

                <button

                    type="button"

                    @click="addItem"

                    class="mb-4 rounded-lg bg-green-600 px-4 py-2 text-white"

                >

                    + Add Item

                </button>

            </div>
        <!-- tombol back add  -->
         <!-- Item produk table-->
                <table
                    class="w-full border rounded-lg"
                >

                    <thead>

                        <tr class="bg-gray-100">

                            <th class="p-3">
                                Product
                            </th>

                            <th class="p-3">
                                Qty
                            </th>

                            <th class="p-3">
                                Unit Cost
                            </th>
                            <th class="p-3">

                                Line Total

                            </th>
                            <th class="p-3">

                                Action

                            </th>
                        </tr>

                    </thead>

                    <tbody>

                        <tr

                            v-for="
                                (item, index)
                                in form.items
                            "

                            :key="index"

                        >

                            <td class="p-3">

                                <select

                                    v-model="
                                        item.product_id
                                    "

                                    class="w-full rounded border"

                                >

                                    <option value="">
                                        Select Product
                                    </option>

                                    <option

                                        v-for="
                                            product
                                            in products
                                        "

                                        :key="
                                            product.id
                                        "

                                        :value="
                                            product.id
                                        "

                                    >

                                        {{ product.name }}

                                    </option>

                                </select>

                            </td>

                            <td class="p-3">

                                <input

                                    type="number"

                                    v-model="
                                        item.qty
                                    "

                                    class="w-full rounded border"

                                >

                            </td>

                            <td class="p-3">

                                <input

                                    type="number"

                                    v-model="
                                        item.unit_cost
                                    "

                                    class="w-full rounded border"

                                >

                            </td>
                            <td class="p-3">

                               Rp {{

                                    (
                                        Number(item.qty || 0)

                                        *

                                        Number(item.unit_cost || 0)

                                    ).toLocaleString('id-ID')

                                }}

                            </td>
                            <td class="p-3">

                                <button

                                    type="button"

                                    @click="
                                        removeItem(index)
                                    "

                                    class="rounded bg-red-600 px-3 py-2 text-white hover:bg-red-700"

                                >

                                    ✕

                                </button>

                            </td>

                        </tr>

                    </tbody>

                </table>
         <!-- add item produk table-->
         <div
                class="mt-6 flex gap-3"
            >

                <Link

                    :href="
                        route(
                            'purchase-orders.index'
                        )
                    "

                    class="rounded-xl bg-slate-500 px-5 py-3 text-white"

                >

                    Back

                </Link>
                <button

                    type="button"

                    @click="submit"

                    class="rounded-xl bg-blue-600 px-5 py-3 text-white"

                    >

                    Save Draft

                </button>
            </div>
         <!-- end tombol back-->
    </div>

</AuthenticatedLayout>

</template>