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
     errors: Object,

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

    purchase_order_id:
        props.purchaseOrder.id,

    supplier_id:
        props.purchaseOrder.supplier_id,

    warehouse_id:
        props.purchaseOrder.warehouse_id,

    receipt_date:
        new Date()
            .toISOString()
            .split('T')[0],

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

            received_qty:
                Number(
                    item.received_qty || 0
                ),

            remaining_qty:
                Number(
                    item.remaining_qty || 0
                ),

            qty_received:
                Number(
                    item.remaining_qty || 0
                ),

            unit_cost:
                Number(
                    item.unit_cost
                ),

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
const saveDraft = () => {

    form.post(

        route(
            'goods-receipts.store'
        )

    )

}
const totalOrderedQty = computed(() => {

    return form.items.reduce(

        (total, item) =>

            total +

            Number(item.qty_po || 0),

        0

    )

})

const totalReceivedQty = computed(() => {

    return form.items.reduce(

        (total, item) =>

            total +

            Number(item.received_qty || 0),

        0

    )

})

const remainingQty = computed(() => {

    return form.items.reduce(

        (total, item) =>

            total +

            Number(item.remaining_qty || 0),

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
                    class="
                        mb-6
                        rounded-xl
                        bg-white
                        p-6
                        shadow
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

                            <h1
                                class="
                                    text-2xl
                                    font-bold
                                "
                            >

                                Create Goods Receipt

                            </h1>

                            <p
                                class="
                                    text-gray-500
                                "
                            >

                                Purchase Order
                                {{ purchaseOrder.po_number }}

                            </p>

                        </div>

                        <span
                            class="
                                rounded-full
                                bg-yellow-100
                                px-4
                                py-2
                                text-sm
                                font-medium
                                text-yellow-700
                            "
                        >

                            Draft

                        </span>

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
                    <div>

                            <label>
                                Receipt Date
                            </label>
                            <!-- tanggal receipt-->
                            <input
                             

                                type="date"

                                v-model="
                                    form.receipt_date
                                "

                                class="
                                    mt-1 w-full
                                    rounded-lg border
                                "

                            >
                            <!-- end tanggal receipt-->
                           
                        </div>
                        <br>
                        <!-- Do nuber -->
                        <div
                            class="col-span-2"
                        >

                            <label>
                                Supplier DO Number
                            </label>

                          <input

                                type="text"

                                v-model="
                                    form.supplier_do_number
                                "

                                class="
                                    mt-1 w-full
                                    rounded-lg border
                                "
                                placeholder="Supplier DO Number Reference"
                            >

                        </div>
                        <!-- end do number-->
                         <br>
                    <div
                        class="grid grid-cols-2 gap-4"
                    >
 
                        <!-- PO summary card-->
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
                                        PO Number
                                    </p>

                                    <p
                                        class="
                                            mt-2
                                            font-bold
                                        "
                                    >
                                        {{ purchaseOrder.po_number }}
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
                                        Supplier
                                    </p>

                                    <p
                                        class="
                                            mt-2
                                            font-bold
                                        "
                                    >
                                        {{ purchaseOrder.supplier.name }}
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
                                        Warehouse
                                    </p>

                                    <p
                                        class="
                                            mt-2
                                            font-bold
                                        "
                                    >
                                        {{ purchaseOrder.warehouse.name }}
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
                                        PO Status
                                    </p>

                                    <p
                                        class="
                                            mt-2
                                            font-bold
                                            text-blue-600
                                        "
                                    >
                                        {{ purchaseOrder.status }}
                                    </p>

                                </div>
                                <!-- po qty-->
                               
                            </div>
                         <!-- end po summary card-->
                          <!-- receiving statistic-->
                           <div
                                class="
                                    mb-6
                                    grid
                                    grid-cols-3
                                    gap-4
                                "
                            >

                                <div
                                    class="
                                        rounded-xl
                                        bg-white
                                        p-5
                                        shadow
                                    "
                                >

                                    <p
                                        class="
                                            text-sm
                                            text-gray-500
                                        "
                                    >
                                        Ordered Qty
                                    </p>

                                    <p
                                        class="
                                            mt-2
                                            text-2xl
                                            font-bold
                                        "
                                    >
                                        {{ totalOrderedQty }}
                                    </p>

                                </div>

                                <div
                                    class="
                                        rounded-xl
                                        bg-white
                                        p-5
                                        shadow
                                    "
                                >

                                    <p
                                        class="
                                            text-sm
                                            text-gray-500
                                        "
                                    >
                                        Received Qty
                                    </p>

                                    <p
                                        class="
                                            mt-2
                                            text-2xl
                                            font-bold
                                            text-green-600
                                        "
                                    >
                                        {{ totalReceivedQty }}
                                    </p>

                                </div>

                                <div
                                    class="
                                        rounded-xl
                                        bg-white
                                        p-5
                                        shadow
                                    "
                                >

                                    <p
                                        class="
                                            text-sm
                                            text-gray-500
                                        "
                                    >
                                        Remaining Qty
                                    </p>

                                    <p
                                        class="
                                            mt-2
                                            text-2xl
                                            font-bold
                                            text-orange-600
                                        "
                                    >
                                        {{ remainingQty }}
                                    </p>

                                </div>

                            </div>
                           <!-- end receiving statistic-->
                        <!-- Message Supplier DO Number-->
                         <div

                            v-if="
                                form.errors
                                .supplier_do_number
                            "

                            class="
                                mt-1 text-sm
                                text-red-500
                            "

                        >

                            {{

                                form.errors
                                .supplier_do_number

                            }}

                        </div>
                         <!-- end message -->
                    </div>

                </div>
                <!-- error message Qty receipts-->
                 <div
                        class="
                            mb-4
                            h-14
                        "
                    >

                        <div

                            v-if="
                                form.items.some(
                                    item =>
                                        Number(item.qty_received)
                                        >
                                        Number(item.remaining_qty)
                                )
                            "

                            class="
                                flex
                                items-center
                                rounded-xl
                                border
                                border-red-200
                                bg-red-50
                                px-4
                                py-3
                                text-sm
                                font-medium
                                text-red-600
                            "

                        >

                            ⚠ Qty Receipt tidak boleh melebihi Remaining Qty.

                        </div>

                    </div>

                 <!-- end error message Receipts-->
                <!-- tabel item-->
                    <div
                        class="rounded-xl bg-white p-6 shadow"
                    >

                        <h2
                            class="mb-4 text-lg font-semibold"
                        >

                            Receipt Items

                        </h2>
                       <!-- error message Qty Remaining-->
                     
                        <!-- end error message Qty remaining-->
                        <table
                           class="
                                w-full
                                table-fixed
                            "
                        >

                            <thead>

                                <tr
                                    class="bg-gray-100"
                                >

                                    <th class="w-64" >
                                        Product
                                    </th>

                                    <th class="w-24">
                                        Ordered
                                    </th>

                                    <th class="w-24">
                                        Receive
                                    </th>

                                    <th class="w-24">
                                        Remaining
                                    </th>

                                    <th class="w-32">
                                        Receipt Qty
                                    </th>
                                     <th class="w-32">
                                        Unit Cost
                                    </th>
                                    <th class="w-32">
                                        Amount
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
                                            item.qty_po
                                        }}

                                    </td>

                                    <td class="p-3">
                                    <!-- field item qty receive-->
                               <div class="relative">
                                        <!-- error message Remaining-->
                                        <input

                                            v-model.number="
                                                item.qty_received
                                            "

                                            type="number"

                                            :disabled="
                                                item.remaining_qty <= 0
                                            "

                                            :class="[
                                                'w-full rounded-lg border px-3 py-2',

                                                item.remaining_qty <= 0
                                                    ? 'bg-gray-100 text-gray-500'
                                                    : '',

                                                Number(item.qty_received)
                                                >
                                                Number(item.remaining_qty)
                                                   ? 'border-red-300 bg-red-50'
                                                  : 'border-gray-300'

                                            ]"

                                        />

                                        <!-- error message remaining-->
                                    </div>

                                    </td>
                                    <!-- end item qty-->
                                    <td class="p-3 text-right">

                                        {{ item.remaining_qty }}

                                    </td>
                                    <td class="p-3 text-right">

                                        {{ item.received_qty }}

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
                                    v-model="
                                            form.remarks
                                        "

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
                                       <!-- end error message date-->
                               
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
                             
                           
                        </table>
                        <!-- Tombol Save dan Back-->
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
                                <!-- meessage Error ketika Tanggal kosong-->
                             <div

                                v-if="
                                    form.errors
                                    .receipt_date
                                "

                                class="
                                    mt-1 text-sm
                                    text-red-500
                                "

                            >

                                {{

                                    form.errors
                                    .receipt_date

                                }}

                            </div>
                       
                                  <button

                                @click="saveDraft"

                                :disabled="
                                    form.items.some(
                                        item =>
                                            Number(item.qty_received)
                                            >
                                            Number(item.remaining_qty)
                                    )
                                "

                                class="
                                    rounded-lg
                                    bg-blue-600
                                    px-5
                                    py-3
                                    text-white
                                    disabled:cursor-not-allowed
                                    disabled:opacity-50
                                "

                            >

                                Save Draft

                            </button>

                        </div>
                         <!-- end tombol save dan back-->
                    </div>
</div>

</AuthenticatedLayout>

</template>