<script setup>

import {
    Head,
    Link
} from '@inertiajs/vue3'
import { computed } from 'vue'
import AuthenticatedLayout
from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({

    goodsReceipt: Object,

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
const totalQty = computed(() => {

    return props.goodsReceipt.details.reduce(

        (total,item) =>

            total +

            Number(
                item.qty_received
            ),

        0

    )

})

const grandTotal = computed(() => {

    return props.goodsReceipt.details.reduce(

        (total,item) =>

            total +

            Number(
                item.line_total
            ),

        0

    )

})
</script>

<template>

    <Head
        title="Goods Receipt Detail"
    />

    <AuthenticatedLayout>

        <div
            class="
                p-6
            "
        >

            <!-- Header -->

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

                            <!-- Kiri -->

                            <div>

                                <h1
                                    class="
                                        text-2xl
                                        font-bold
                                    "
                                >

                                    {{
                                        goodsReceipt
                                        .grn_number
                                    }}

                                </h1>

                                <p
                                    class="
                                        text-gray-500
                                    "
                                >

                                    Goods Receipt

                                </p>

                            </div>

                            <!-- Kanan -->

                            <div
                                class="
                                    flex
                                    items-center
                                    gap-2
                                "
                            >

                                <Link

                                    v-if="
                                        goodsReceipt.status
                                        === 'Draft'
                                    "

                                    method="patch"

                                    as="button"

                                    :href="
                                        route(
                                            'goods-receipts.post',
                                            goodsReceipt.id
                                        )
                                    "

                                    class="
                                        rounded-lg
                                        bg-green-600
                                        px-4
                                        py-2
                                        text-white
                                    "

                                >

                                    Post

                                </Link>

                                <Link

                                    v-if="
                                        goodsReceipt.status
                                        === 'Draft'
                                    "

                                    method="patch"

                                    as="button"

                                    :href="
                                        route(
                                            'goods-receipts.cancel',
                                            goodsReceipt.id
                                        )
                                    "

                                    class="
                                        rounded-lg
                                        bg-red-600
                                        px-4
                                        py-2
                                        text-white
                                    "

                                >

                                    Cancel

                                </Link>

                                <button

                                    class="
                                        rounded-lg
                                        bg-slate-600
                                        px-4
                                        py-2
                                        text-white
                                    "

                                >

                                    Print

                                </button>

                                <Link

                                    :href="
                                        route(
                                            'goods-receipts.index'
                                        )
                                    "

                                    class="
                                        rounded-lg
                                        bg-blue-600
                                        px-4
                                        py-2
                                        text-white
                                    "

                                >

                                    Back

                                </Link>

                            </div>

                        </div>

                    </div>
            <!-- end header -->
            <!-- Document Information -->

            <div
                class="
                    mb-6
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

                    Document Information

                </h2>

                <div
                    class="
                        grid
                        grid-cols-2
                        gap-4
                    "
                >

                    <div>

                        <strong>
                            Supplier
                        </strong>

                        <br>

                        {{
                            goodsReceipt
                            .supplier?.name
                        }}

                    </div>

                    <div>

                        <strong>
                            Warehouse
                        </strong>

                        <br>

                        {{
                            goodsReceipt
                            .warehouse?.name
                        }}

                    </div>

                    <div>

                        <strong>
                            Receipt Date
                        </strong>

                        <br>

                        {{
                            goodsReceipt
                            .receipt_date
                        }}

                    </div>

                    <div>

                        <strong>
                            Supplier DO
                        </strong>

                        <br>

                        {{
                            goodsReceipt
                            .supplier_do_number
                        }}

                    </div>

                    <div>

                        <strong>
                            Purchase Order
                        </strong>

                        <br>

                        {{
                             goodsReceipt
                            .purchase_order
                            ?.po_number

                        }}

                    </div>
     <div>

                        <strong>
                            Status
                        </strong>

                        <br>

                        <span

                            class="
                                rounded-full
                                bg-yellow-100
                                px-3
                                py-1
                                text-xs
                                font-semibold
                                text-yellow-700
                            "

                        >

                            {{
                                goodsReceipt
                                .status
                            }}

                        </span>

                    </div>

                </div>

            </div>

            <!-- Items -->

            <div
                class="
                    mb-6
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

                    Items

                </h2>

                <table
                    class="
                        min-w-full
                    "
                >

                    <thead>

                        <tr
                            class="
                                border-b
                            "
                        >

                            <th
                                class="
                                    p-3
                                    text-left
                                "
                            >

                                Product

                            </th>

                            <th
                                class="
                                    p-3
                                    text-right
                                "
                            >

                                Qty

                            </th>

                            <th
                                class="
                                    p-3
                                    text-right
                                "
                            >

                                Cost

                            </th>

                            <th
                                class="
                                    p-3
                                    text-right
                                "
                            >

                                Total

                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        <tr

                            v-for="
                                item
                                in
                                goodsReceipt.details
                            "

                            :key="
                                item.id
                            "

                            class="
                                border-b
                            "

                        >

                            <td
                                class="
                                    p-3
                                "
                            >

                                {{
                                    item.product
                                    ?.name
                                }}

                            </td>

                            <td
                                class="
                                    p-3
                                    text-right
                                "
                            >

                                {{
                                    item.qty_received
                                }}

                            </td>

                            <td
                                class="
                                    p-3
                                    text-right
                                "
                            >

                                {{
                                    formatCurrency(
                                        item.unit_cost
                                    )
                                }}

                            </td>

                            <td
                                class="
                                    p-3
                                    text-right
                                "
                            >

                                {{
                                    formatCurrency(
                                        item.line_total
                                    )
                                }}

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

            <!-- Summary -->

            <div
    class="
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
                                        goodsReceipt
                                        .details
                                        .length
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
                                    Total Qty
                                </span>

                                <span>

                                    {{
                                        totalQty
                                    }}

                                </span>

                            </div>

                            <div
                                class="
                                    flex
                                    justify-between
                                    border-t
                                    pt-3
                                    text-lg
                                    font-bold
                                "
                            >

                                <span>
                                    Grand Total
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
            <!-- end summary card-->
        </div>

    </AuthenticatedLayout>

</template>