<script setup>

import {
    Head,
    Link
} from '@inertiajs/vue3'

import AuthenticatedLayout
    from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({

    purchaseOrders: Object,

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
<pre>



</pre>
    <Head title="Purchase Orders" />

    <AuthenticatedLayout>

        <div
            class="py-6 px-6"
        >

            <!-- Header -->

            <div
                class="mb-6 flex items-center justify-between"
            >

                <div>

                    <h1
                        class="text-2xl font-bold text-gray-800"
                    >

                        Purchase Orders

                    </h1>

                    <p
                        class="text-sm text-gray-500"
                    >

                        Manage procurement transactions

                    </p>

                </div>

                <Link

                    :href="
                        route(
                            'purchase-orders.create'
                        )
                    "

                    class="rounded-xl bg-blue-600 px-5 py-3 text-white"

                >

                    + Create Purchase Order

                </Link>

            </div>

            <!-- Analytics Cards -->

            <div
                class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4"
            >

                <div
                    class="rounded-xl bg-white p-5 shadow"
                >

                    <p
                        class="text-sm text-gray-500"
                    >
                        Total PO
                    </p>

                    <h2
                        class="mt-2 text-3xl font-bold"
                    >

                        {{
                            purchaseOrders.total
                        }}

                    </h2>

                </div>

                <div
                    class="rounded-xl bg-white p-5 shadow"
                >

                    <p
                        class="text-sm text-gray-500"
                    >
                        Draft PO
                    </p>

                    <h2
                        class="mt-2 text-3xl font-bold text-yellow-600"
                    >

                        {{
                            purchaseOrders.data.filter(
                                po =>
                                po.status === 'Draft'
                            ).length
                        }}

                    </h2>

                </div>

                <div
                    class="rounded-xl bg-white p-5 shadow"
                >

                    <p
                        class="text-sm text-gray-500"
                    >
                        This Page Total
                    </p>

                    <h2
                        class="mt-2 text-xl font-bold text-green-600"
                    >

                        {{

                            formatCurrency(

                                purchaseOrders.data.reduce(

                                    (
                                        total,
                                        po
                                    ) =>

                                        total +

                                        Number(
                                            po.grand_total || 0
                                        ),

                                    0

                                )

                            )

                        }}

                    </h2>

                </div>

                <div
                    class="rounded-xl bg-white p-5 shadow"
                >

                    <p
                        class="text-sm text-gray-500"
                    >
                        Active Records
                    </p>

                    <h2
                        class="mt-2 text-3xl font-bold text-blue-600"
                    >

                        {{
                            purchaseOrders.data.length
                        }}

                    </h2>

                </div>

            </div>

            <!-- Search -->

            <div
                class="mb-6"
            >

                <input

                    type="text"

                    placeholder="Search PO Number..."

                    class="w-full rounded-xl border border-gray-300 px-4 py-3"

                >

            </div>

            <!-- Empty State -->

            <div

                v-if="
                    purchaseOrders.data.length === 0
                "

                class="rounded-xl bg-white p-10 text-center shadow"

            >

                <div
                    class="text-6xl"
                >

                    📦

                </div>

                <h2
                    class="mt-4 text-xl font-bold"
                >

                    No Purchase Orders Found

                </h2>

                <p
                    class="mt-2 text-gray-500"
                >

                    Create your first purchase order
                    to start procurement activities.

                </p>

                <Link

                    :href="
                        route(
                            'purchase-orders.create'
                        )
                    "

                    class="mt-5 inline-block rounded-xl bg-blue-600 px-5 py-3 text-white"

                >

                    Create Purchase Order

                </Link>

            </div>

            <!-- Table -->

            <div

                v-else

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
                                PO Number
                            </th>

                            <th class="px-6 py-4 text-left">
                                Supplier
                            </th>

                            <th class="px-6 py-4 text-left">
                                Warehouse
                            </th>

                            <th class="px-6 py-4 text-left">
                                Date
                            </th>

                            <th class="px-6 py-4 text-left">
                                Status
                            </th>

                            <th class="px-6 py-4 text-right">
                                Grand Total
                            </th>

                            <th class="px-6 py-4 text-center">
                                Action
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        <tr

                            v-for="
                                po
                                in purchaseOrders.data
                            "

                            :key="
                                po.id
                            "

                            class="border-t"

                        >

                            <td class="px-6 py-4">

                                {{ po.po_number }}

                            </td>

                            <td class="px-6 py-4">

                                {{
                                    po.supplier?.name
                                }}

                            </td>

                            <td class="px-6 py-4">

                                {{
                                    po.warehouse?.name
                                }}

                            </td>

                            <td class="px-6 py-4">

                                {{
                                    po.order_date
                                }}

                            </td>

                            <td class="px-6 py-4">

                                <span

                                    class="rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-700"

                                >

                                    {{
                                        po.status
                                    }}

                                </span>

                            </td>

                            <td
                                class="px-6 py-4 text-right font-semibold"
                            >

                                {{

                                    formatCurrency(

                                        po.grand_total

                                    )

                                }}

                            </td>

                            <td
                                class="px-6 py-4 text-center"
                            >

                                <div
                                    class="flex justify-center gap-2"
                                >

                                    <button
                                        class="rounded-lg bg-blue-500 px-3 py-2 text-white"
                                    >
                                        View
                                    </button>

                                    <button
                                        class="rounded-lg bg-amber-500 px-3 py-2 text-white"
                                    >
                                        Edit
                                    </button>

                                    <button
                                        class="rounded-lg bg-red-500 px-3 py-2 text-white"
                                    >
                                        Delete
                                    </button>

                                </div>

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

    </AuthenticatedLayout>

</template>