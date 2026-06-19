<script setup>

import {

    Head,

    Link,

    useForm

} from '@inertiajs/vue3'

import AuthenticatedLayout
    from '@/Layouts/AuthenticatedLayout.vue'

import { computed } from 'vue'
const props = defineProps({

    purchaseOrder: Object,

})

const formatCurrency = (
    value
) => {

    return 'Rp ' +

        Number(
            value || 0
        ).toLocaleString(
            'id-ID'
        )

}
const form = useForm({

    receipt_date: '',

    supplier_do_number: '',

    remarks: '',

    items: props.purchaseOrder.details.map(
        item => ({

            product_id:
                item.product_id,

            product_name:
                item.product.name,

            qty_po:
                Number(item.qty),

            qty_received:
                Number(item.qty),

            unit_cost:
                Number(item.unit_cost),

        })
    )

})
const totalQty =
computed(() => {

    return form.items.reduce(

        (total,item) =>

            total +

            Number(
                item.qty_received
            ),

        0

    )

})
const grandTotal =
computed(() => {

    return form.items.reduce(

        (total,item) =>

            total +

            (

                Number(
                    item.qty_received
                )

                *

                Number(
                    item.unit_cost
                )

            ),

        0

    )

})
</script>

<template>

<Head
    title="Create Goods Receipt"
/>

<AuthenticatedLayout>

<div
    class="p-6"
>
            <!-- template header -->
            <div
                class="mb-6 flex items-center justify-between"
            >

                <div>

                    <h1
                        class="text-2xl font-bold"
                    >

                        Goods Receipt

                    </h1>

                    <p
                        class="text-gray-500"
                    >

                        Create Goods Receipt
                        from Purchase Order

                    </p>

                </div>

            </div>
            <!-- end template header -->

            <!-- card information documetn-->

                <div
                    class="mb-6 rounded-xl bg-white p-6 shadow"
                >

                    <h2
                        class="mb-4 text-lg font-semibold"
                    >

                        Document Information

                    </h2>

                    <div
                        class="grid grid-cols-2 gap-4"
                    >

                        <div>

                            <label>
                                PO Number
                            </label>

                            <input

                                :value="
                                    purchaseOrder.po_number
                                "

                                disabled

                                class="mt-1 w-full rounded-lg border bg-gray-100"

                            >

                        </div>

                        <div>

                            <label>
                                Receipt Date
                            </label>

                            <input

                                type="date"

                                class="mt-1 w-full rounded-lg border"

                            >

                        </div>

                        <div>

                            <label>
                                Supplier
                            </label>

                            <input

                                :value="
                                    purchaseOrder.supplier.name
                                "

                                disabled

                                class="mt-1 w-full rounded-lg border bg-gray-100"

                            >

                        </div>

                        <div>

                            <label>
                                Warehouse
                            </label>

                            <input

                                :value="
                                    purchaseOrder.warehouse.name
                                "

                                disabled

                                class="mt-1 w-full rounded-lg border bg-gray-100"

                            >

                        </div>

                        <div
                            class="col-span-2"
                        >

                            <label>
                                Supplier DO Number
                            </label>

                            <input

                                type="text"

                                class="mt-1 w-full rounded-lg border"

                                placeholder="Supplier Delivery Order Number"

                            >

                        </div>

                    </div>

                </div>
                <!-- tabel item-->
                                    <div
                        class="rounded-xl bg-white p-6 shadow"
                    >

                        <h2
                            class="mb-4 text-lg font-semibold"
                        >

                            Receipt Items

                        </h2>

                        <table
                            class="w-full border-collapse"
                        >

                            <thead>

                                <tr
                                    class="bg-gray-100"
                                >

                                    <th class="p-3 text-left">
                                        Product
                                    </th>

                                    <th class="p-3 text-right">
                                        Qty PO
                                    </th>

                                    <th class="p-3 text-right">
                                        Qty Received
                                    </th>

                                    <th class="p-3 text-right">
                                        Unit Cost
                                    </th>

                                    <th class="p-3 text-right">
                                        Total
                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                                <tr

                                    v-for="
                                    (item,index)
                                    in form.items
                                    "

                                >

                                    <td class="p-3">

                                        {{
                                            item.product_name
                                        }}

                                    </td>

                                    <td
                                        class="p-3 text-right"
                                    >

                                        {{
                                            item.qty
                                        }}

                                    </td>

                                    <td class="p-3">

                                        <input

                                            type="number"

                                            v-model="
                                                item.qty_received
                                            "

                                            class="
                                                w-24
                                                rounded
                                                border
                                            "

                                        >

                                    </td>

                                    <td
                                        class="p-3 text-right"
                                    >

                                        {{

                                            formatCurrency(
                                                item.unit_cost
                                            )

                                        }}

                                    </td>

                                    <td
                                        class="p-3 text-right"
                                    >

                                        {{
                                        formatCurrency(

                                            item.qty_received

                                            *

                                            item.unit_cost

                                        )
                                        }}

                                    </td>

                                </tr>

                            </tbody>
                            <!-- summary remark-->
                                <div
                                    class="mt-6 rounded-xl bg-white p-6 shadow"
                                >

                                    <h2
                                        class="mb-4 text-lg font-semibold"
                                    >

                                        Remarks

                                    </h2>

                                    <textarea

                                        rows="4"

                                        class="
                                            w-full
                                            rounded-lg
                                            border
                                        "

                                        placeholder="
                                            Enter remarks...
                                        "

                                    />

                                </div>
                            <!-- end summary remark-->
                             <!-- summary grand total-->
                              <div
                                class="
                                    mt-6
                                    flex
                                    justify-end
                                "
                            >

                                <div
                                    class="
                                        w-full
                                        max-w-md
                                        rounded-xl
                                        bg-white
                                        p-6
                                        shadow
                                    "
                                >

                                    <h2
                                        class="
                                            mb-4
                                            text-lg
                                            font-semibold
                                        "
                                    >

                                        Summary

                                    </h2>

                                    <div
                                        class="
                                            space-y-3
                                        "
                                    >

                                        <div
                                            class="
                                                flex
                                                justify-between
                                            "
                                        >

                                            <span>

                                                Total Items

                                            </span>

                                            <span>

                                                {{

                                                   form.items.length

                                                }}

                                            </span>

                                        </div>

                                        <div
                                            class="
                                                flex
                                                justify-between
                                            "
                                        >

                                            <span>

                                                Total Quantity

                                            </span>

                                            <span>

                                              {{ totalQty }}

                                            </span>

                                        </div>

                                        <div
                                            class="
                                                flex
                                                justify-between
                                                border-t
                                                pt-3
                                                font-bold
                                            "
                                        >

                                            <span>

                                                Total Amount

                                            </span>

                                            <span>

                                               {{
                                                formatCurrency(
                                                    grandTotal
                                                )
                                                }}

                                            </span>

                                        </div>

                                    </div>

                                </div>

                            </div>
                              <!-- end summary grand total-->
                               <div
                                    class="
                                        mt-6
                                        flex
                                        justify-end
                                        gap-2
                                    "
                                >

                                    <Link

                                        :href="
                                            route(
                                                'purchase-orders.show',
                                                purchaseOrder.id
                                            )
                                        "

                                        class="
                                            rounded-lg
                                            bg-gray-500
                                            px-5
                                            py-3
                                            text-white
                                        "
                                    >

                                        Back

                                    </Link>

                                    <button

                                        class="
                                            rounded-lg
                                            bg-blue-600
                                            px-5
                                            py-3
                                            text-white
                                        "
                                    >

                                        Save Draft

                                    </button>

                                </div>
                        </table>

                    </div>
</div>

</AuthenticatedLayout>

</template>