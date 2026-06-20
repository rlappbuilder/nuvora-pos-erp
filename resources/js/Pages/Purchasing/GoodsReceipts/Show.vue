<script setup>

import {
    Head,
    Link
} from '@inertiajs/vue3'

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

                    <Link

                        :href="
                            route(
                                'goods-receipts.index'
                            )
                        "

                        class="
                            rounded-lg
                            bg-gray-600
                            px-4
                            py-2
                            text-white
                        "

                    >

                        Back

                    </Link>

                </div>

            </div>

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
                            .purchaseOrder
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

                <div>

                    Total Items :

                    {{
                        goodsReceipt
                        .details
                        .length
                    }}

                </div>

            </div>

        </div>

    </AuthenticatedLayout>

</template>