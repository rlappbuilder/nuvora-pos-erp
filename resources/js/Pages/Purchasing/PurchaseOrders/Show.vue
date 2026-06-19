<script setup>

import {
    Head,
    Link
} from '@inertiajs/vue3'

import AuthenticatedLayout
from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({

    purchaseOrder: Object

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

    <Head title="Purchase Order Detail" />

    <AuthenticatedLayout>

        <div class="py-6 px-6">

            <!-- Header -->

            <div
                class="mb-6 rounded-xl bg-white p-6 shadow"
            >

                <div
                    class="flex items-center justify-between"
                >

                    <div>

                        <h1
                            class="text-2xl font-bold"
                        >

                            {{ purchaseOrder.po_number }}

                        </h1>

                        <p
                            class="text-sm text-gray-500"
                        >

                            Purchase Order Detail

                        </p>

                    </div>

                    <span
                        class="rounded-full bg-yellow-100 px-4 py-2 text-sm font-semibold text-yellow-700"
                    >

                       {{ purchaseOrder.status }}
                      

                    </span>
                    <!-- Approvel Submit Rijec-->

                     <div
                            class="mt-4 flex gap-2"
                        >

                            <Link

                                v-if="
                                    purchaseOrder.status === 'Draft'
                                "

                                method="patch"

                                as="button"

                                :href="
                                    route(
                                        'purchase-orders.submit',
                                        purchaseOrder.id
                                    )
                                "

                                class="rounded-lg bg-blue-600 px-4 py-2 text-white"

                            >

                                Submit

                            </Link>

                            <Link

                                v-if="
                                    purchaseOrder.status === 'Submitted'
                                "

                                method="patch"

                                as="button"

                                :href="
                                    route(
                                        'purchase-orders.approve',
                                        purchaseOrder.id
                                    )
                                "

                                class="rounded-lg bg-green-600 px-4 py-2 text-white"

                            >

                                Approve

                            </Link>
                        <!-- v if -->

                            <Link

                            v-if="
                                    purchaseOrder.status === 'Submitted'
                                "

                                method="patch"

                                as="button"

                                :href="
                                    route(
                                        'purchase-orders.reject',
                                        purchaseOrder.id
                                    )
                                "

                                class="rounded-lg bg-red-600 px-4 py-2 text-white"

                              
                             
                            >

                                Reject

                            </Link>
                            <!-- button reopen-->
                           <Link

                                    v-if="
                                        purchaseOrder.status
                                        === 'Rejected'
                                    "

                                    method="patch"

                                    as="button"

                                    :href="
                                        route(
                                            'purchase-orders.reopen',
                                            purchaseOrder.id
                                        )
                                    "

                                 class="rounded-lg bg-blue-600 px-4 py-2 text-white-50"

                                >

                                    Reopen

                                </Link>
                        </div>
                    <!-- end approval submit rijeck-->
                </div>

            </div>

            <!-- Information -->

            <div
                class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2"
            >

                <!-- Supplier -->

                <div
                    class="rounded-xl bg-white p-5 shadow"
                >

                    <h3
                        class="mb-3 font-bold"
                    >

                        Supplier Information

                    </h3>

                    <p>

                        {{ purchaseOrder.supplier?.name }}

                    </p>

                    <p>

                        {{ purchaseOrder.supplier?.phone }}

                    </p>

                    <p>

                        {{ purchaseOrder.supplier?.city }}

                    </p>

                </div>

                <!-- Document -->

                <div
                    class="rounded-xl bg-white p-5 shadow"
                >

                    <h3
                        class="mb-3 font-bold"
                    >

                        Document Information

                    </h3>

                    <p>

                        Order Date :

                        {{ purchaseOrder.order_date }}

                    </p>

                    <p>

                        Expected Date :

                        {{ purchaseOrder.expected_date }}

                    </p>

                    <p>

                        Warehouse :

                        {{ purchaseOrder.warehouse?.name }}

                    </p>

                </div>

            </div>

            <!-- Detail Items -->

            <div
                class="overflow-hidden rounded-xl bg-white shadow"
            >

                <table
                    class="min-w-full"
                >

                    <thead
                        class="bg-gray-100"
                    >

                        <tr>

                            <th class="px-6 py-4 text-left">
                                Product
                            </th>

                            <th class="px-6 py-4 text-left">
                                Qty
                            </th>

                            <th class="px-6 py-4 text-left">
                                Unit Cost
                            </th>

                            <th class="px-6 py-4 text-left">
                                Total
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        <tr

                            v-for="
                                item
                                in purchaseOrder.details
                            "

                            :key="
                                item.id
                            "

                            class="border-t"

                        >

                            <td class="px-6 py-4">

                                {{ item.product?.name }}

                            </td>

                            <td class="px-6 py-4">

                                {{ item.qty }}

                            </td>

                            <td class="px-6 py-4">

                                {{

                                    formatCurrency(

                                        item.unit_cost

                                    )

                                }}

                            </td>

                            <td class="px-6 py-4">

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
                class="mt-6 flex justify-end"
            >

                <div
                    class="w-full rounded-xl bg-white p-6 shadow md:w-96"
                >

                    <div
                        class="flex justify-between"
                    >

                        <span>

                            Subtotal

                        </span>

                        <span>

                            {{

                                formatCurrency(

                                    purchaseOrder.subtotal

                                )

                            }}

                        </span>

                    </div>

                    <div
                        class="mt-4 flex justify-between text-lg font-bold"
                    >

                        <span>

                            Grand Total

                        </span>

                        <span>

                            {{

                                formatCurrency(

                                    purchaseOrder.grand_total

                                )

                            }}

                        </span>

                    </div>

                </div>

            </div>

            <!-- Action -->

            <div
                class="mt-6"
            >

                <Link

                    :href="
                        route(
                            'purchase-orders.index'
                        )
                    "
                class="inline-block rounded-xl bg-red-600 px-5 py-3 text-white"
                 

                >

                    Back

                </Link>

            </div>

        </div>

    </AuthenticatedLayout>

</template>