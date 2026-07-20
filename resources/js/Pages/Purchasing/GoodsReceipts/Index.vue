<script setup>

import {
    Head,
    Link
} from '@inertiajs/vue3'

import AppLayout from '@/Layouts/AppLayout.vue'


const props = defineProps({

    goodsReceipts: Object,

    filters: Object,

    totalGrn: Number,

    totalDraft: Number,

    totalPosted: Number,

    totalCancelled: Number,

})

</script>
<template>

<Head title="Goods Receipts" />

<AppLayout>

    <div class="p-6">

        <div
            class="
                mb-6
                flex
                items-center
                justify-between
            "
        >

            <h1
                class="
                    text-2xl
                    font-bold
                "
            >

                Goods Receipts

            </h1>

        </div>
       
         <!-- card statistic-->
          <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4">

                <div class="rounded-xl bg-white p-5 shadow">

                    <p class="text-sm text-gray-500">
                        Total GRN
                    </p>

                    <h2 class="mt-2 text-3xl font-bold">
                        {{ totalGrn }}
                    </h2>

                </div>

                <div class="rounded-xl bg-white p-5 shadow">

                    <p class="text-sm text-yellow-600">
                        Draft
                    </p>

                    <h2 class="mt-2 text-3xl font-bold">
                        {{ totalDraft }}
                    </h2>

                </div>

                <div class="rounded-xl bg-white p-5 shadow">

                    <p class="text-sm text-green-600">
                        Posted
                    </p>

                    <h2 class="mt-2 text-3xl font-bold">
                        {{ totalPosted }}
                    </h2>

                </div>

                <div class="rounded-xl bg-white p-5 shadow">

                    <p class="text-sm text-red-600">
                        Cancelled
                    </p>

                    <h2 class="mt-2 text-3xl font-bold">
                        {{ totalCancelled }}
                    </h2>

                </div>

            </div>
          <!-- end card static-->
           <!-- filter -->
            <div
                class="
                    mb-6
                    rounded-xl
                    bg-white
                    p-4
                    shadow
                "
            >

                <div
                    class="
                        grid
                        grid-cols-1
                        gap-4
                        md:grid-cols-2
                    "
                >

                    <input

                        v-model="
                            filters.search
                        "

                        type="text"

                        placeholder="Search GRN Number..."

                        class="
                            rounded-lg
                            border
                        "

                    >

                    <select

                        v-model="
                            filters.status
                        "

                        class="
                            rounded-lg
                            border
                        "

                    >

                        <option value="">
                            All Status
                        </option>

                        <option value="Draft">
                            Draft
                        </option>

                        <option value="Posted">
                            Posted
                        </option>

                        <option value="Cancelled">
                            Cancelled
                        </option>

                    </select>

                </div>

            </div>
            <!-- end filter -->
        <div
            class="
                overflow-hidden
                rounded-xl
                bg-white
                shadow
            "
        >

            <table
                class="
                    min-w-full
                "
            >

                <thead>

                    <tr
                        class="
                            border-b
                            bg-gray-50
                        "
                    >

                        <th class="p-3 text-left">
                            GRN Number
                        </th>

                        <th class="p-3 text-left">
                            Receipt Date
                        </th>

                        <th class="p-3 text-left">
                            Supplier
                        </th>

                        <th class="p-3 text-left">
                            Warehouse
                        </th>

                        <th class="p-3 text-left">
                            Status
                        </th>

                        <th class="p-3 text-center">
                            Action
                        </th>

                    </tr>

                </thead>

                <tbody>
                        <!-- emtpty state-->
                                        <div

                                            v-if="
                                                goodsReceipts.data
                                                .length === 0
                                            "

                                            class="
                                                rounded-xl
                                                bg-white
                                                p-12
                                                text-center
                                            "

                                        >

                                            <h3
                                                class="
                                                    text-lg
                                                    font-semibold
                                                "
                                            >

                                                No Goods Receipts

                                            </h3>

                                            <p
                                                class="
                                                    mt-2
                                                    text-gray-500
                                                "
                                            >

                                                Goods Receipts are
                                                created from
                                                approved
                                                Purchase Orders.

                                            </p>

                                        </div>
                                <!-- end empty state-->
                    <tr

                        v-for="
                            grn
                            in goodsReceipts.data
                        "

                        :key="
                            grn.id
                        "

                        class="
                            border-b
                        "

                    >

                        <td class="p-3">

                            {{ grn.grn_number }}

                        </td>

                        <td class="p-3">

                            {{ grn.receipt_date }}

                        </td>

                        <td class="p-3">

                            {{ grn.supplier?.name }}

                        </td>

                        <td class="p-3">

                            {{ grn.warehouse?.name }}

                        </td>

                        <td class="p-3">
                            <!-- grn status-->
                            <span

                                    v-if="
                                        grn.status
                                        === 'Draft'
                                    "

                                    class="
                                        rounded-full
                                        bg-yellow-100
                                        px-3 py-1
                                        text-xs
                                        font-semibold
                                        text-yellow-700
                                    "

                                >

                                    Draft

                                </span>

                                <span

                                    v-else-if="
                                        grn.status
                                        === 'Posted'
                                    "

                                    class="
                                        rounded-full
                                        bg-green-100
                                        px-3 py-1
                                        text-xs
                                        font-semibold
                                        text-green-700
                                    "

                                >

                                    Posted

                                </span>

                                <span

                                    v-else

                                    class="
                                        rounded-full
                                        bg-red-100
                                        px-3 py-1
                                        text-xs
                                        font-semibold
                                        text-red-700
                                    "

                                >

                                    Cancelled

                                </span>
                                <!-- grn status-->
                        </td>
                                <!-- action button-->
                                                <td
                                class="
                                    p-3
                                    text-center
                                "
                            >

                                <div
                                    class="
                                        flex
                                        justify-center
                                        gap-2
                                    "
                                >

                                    <Link

                                        :href="
                                            route(
                                                'goods-receipts.show',
                                                grn.id
                                            )
                                        "

                                        class="
                                            rounded-lg
                                            bg-blue-600
                                            px-3
                                            py-2
                                            text-white
                                        "

                                    >

                                        👁

                                    </Link>

                                </div>

                            </td>
                    <!-- action button-->
                    </tr>

                </tbody>

            </table>

        </div>

    </div>

</AppLayout>

</template>