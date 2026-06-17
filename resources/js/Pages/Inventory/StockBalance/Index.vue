<script setup>

import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineProps({

    stocks: Object

})

</script>

<template>

    <Head title="Stock Balance" />

    <AuthenticatedLayout>

        <template #header>

            <h2
                class="text-3xl font-bold"
            >
                Stock Balance
            </h2>

        </template>

        <div
            class="rounded-2xl bg-white p-6 shadow-sm"
        >

            <table
                class="min-w-full"
            >

                <thead>

                    <tr>

                        <th>SKU</th>

                        <th>Product</th>

                        <th>Warehouse</th>

                        <th>Unit</th>

                        <th>Qty</th>

                    </tr>

                </thead>

                <tbody>

                    <tr

                        v-for="stock in stocks.data"

                        :key="stock.id"

                    >

                        <td>
                            {{ stock.product?.sku }}
                        </td>

                        <td>
                            {{ stock.product?.name }}
                        </td>

                        <td>
                            {{ stock.warehouse?.name }}
                        </td>

                        <td>
                            {{ stock.product?.unit }}
                        </td>

                        <td
                            class="text-right font-semibold"
                        >
                            {{ stock.qty }}
                        </td>

                    </tr>

                </tbody>

            </table>

        </div>

    </AuthenticatedLayout>

</template>