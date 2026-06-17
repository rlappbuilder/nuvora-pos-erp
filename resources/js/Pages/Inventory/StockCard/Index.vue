<script setup>

import { Head } from '@inertiajs/vue3'

import AuthenticatedLayout
from '@/Layouts/AuthenticatedLayout.vue'

defineProps({

    movements: Object,

    filters: Object,

})

</script>
<template>

    <Head title="Stock Card" />

    <AuthenticatedLayout>

        <template #header>

            <div>

                <h2
                    class="text-3xl font-bold text-gray-800"
                >
                    Stock Card
                </h2>

                <p
                    class="mt-1 text-sm text-gray-500"
                >
                    Inventory movement history.
                </p>

            </div>

        </template>

        <div
            class="rounded-2xl bg-white p-6 shadow-sm"
        >

            <table
                class="min-w-full"
            >

                <thead>

                    <tr>

                        <th>Date</th>

                        <th>Product</th>

                        <th>Warehouse</th>

                        <th>Type</th>

                        <th>Qty</th>

                        <th>Cost</th>

                    </tr>

                </thead>

                <tbody>

                    <tr

                        v-for="
                            movement
                            in
                            movements.data
                        "

                        :key="
                            movement.id
                        "

                    >

                        <td>
                            {{
                                movement.created_at
                            }}
                        </td>

                        <td>
                            {{
                                movement.product?.name
                            }}
                        </td>

                        <td>
                            {{
                                movement.warehouse?.name
                            }}
                        </td>

                        <td>

                            <span
                                class="rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700"
                            >
                                {{
                                    movement.transaction_type
                                }}
                            </span>

                        </td>

                        <td
                            class="text-right font-semibold"
                        >
                            {{
                                movement.qty
                            }}
                        </td>

                        <td
                            class="text-right"
                        >
                            Rp
                            {{
                                Number(
                                    movement.unit_cost
                                ).toLocaleString(
                                    'id-ID'
                                )
                            }}
                        </td>

                    </tr>

                </tbody>

            </table>

        </div>

    </AuthenticatedLayout>

</template>