<script setup>

import { Head, Link } from '@inertiajs/vue3'
import { usePage } from '@inertiajs/vue3'

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import BaseToast from '@/Components/UI/BaseToast.vue'
import AppLayout from '@/Layouts/AppLayout.vue'
const page = usePage()

defineProps({

    warehouse: Object

})

</script>

<template>

    <Head title="Warehouse Detail" />

    <AppLayout>

        <template #header>

            <div
                class="flex items-center justify-between"
            >

                <div>

                    <h2
                        class="text-3xl font-bold text-gray-800"
                    >
                        Warehouse Detail
                    </h2>

                    <p
                        class="mt-1 text-sm text-gray-500"
                    >
                        Warehouse information overview.
                    </p>

                </div>

                <div
    class="flex gap-3"
>

    <Link
        :href="route('warehouses.index')"
    >
        Back
    </Link>

    <Link
        :href="
            route(
                'warehouses.edit',
                warehouse.id
            )
        "
    >
        Edit
    </Link>

</div>

            </div>

        </template>

        <div
            class="mx-auto max-w-7xl space-y-4"
        >

            <!-- Header Card -->

            <div
                class="rounded-3xl bg-white p-8 shadow-sm"
            >

                <div
                    class="flex items-center gap-5"
                >

                    <div
                        class="flex h-20 w-20 items-center justify-center rounded-2xl bg-blue-100 text-4xl"
                    >
                        🏭

                    </div>

                    <div class="ml-4">

                        <h1
                            class="text-3xl font-bold text-gray-800"
                        >
                            {{ warehouse.name }}
                        </h1>

                        <p
                            class="mt-1 text-gray-500"
                        >
                            {{ warehouse.code }}
                        </p>

                    </div>

                </div>

            </div>

            <!-- General Information -->

            <div
                class="rounded-3xl bg-white p-8 shadow-sm"
            >

                <h3
                    class="mb-6 text-lg font-semibold"
                >
                    General Information
                </h3>

                <div
                    class="grid gap-6 md:grid-cols-2"
                >

                    <div>

                        <div class="text-sm text-gray-500">
                            Company
                        </div>

                        <div class="font-medium">
                            {{
                                warehouse.branch?.company?.company_name || '-'
                            }}
                        </div>

                    </div>

                    <div>

                        <div class="text-sm text-gray-500">
                            Branch
                        </div>

                        <div class="font-medium">
                            {{
                                warehouse.branch?.name || '-'
                            }}
                        </div>

                    </div>

                    <div>

                        <div class="text-sm text-gray-500">
                            Warehouse Type
                        </div>

                        <div class="font-medium">
                            {{
                                warehouse.warehouse_type
                            }}
                        </div>

                    </div>

                    <div>

                        <div class="text-sm text-gray-500">
                            Status
                        </div>

                        <div>

                            <span

                                v-if="
                                    warehouse.status
                                "

                                class="rounded-full bg-green-100 px-3 py-1 text-sm text-green-700"

                            >
                                Active
                            </span>

                            <span

                                v-else

                                class="rounded-full bg-red-100 px-3 py-1 text-sm text-red-700"

                            >
                                Inactive
                            </span>

                        </div>

                    </div>

                </div>

            </div>

            <!-- Contact Information -->

            <div
                class="rounded-3xl bg-white p-8 shadow-sm"
            >

                <h3
                    class="mb-6 text-lg font-semibold"
                >
                    Contact Information
                </h3>

                <div
                    class="grid gap-6 md:grid-cols-3"
                >

                    <div>

                        <div class="text-sm text-gray-500">
                            PIC Name
                        </div>

                        <div class="font-medium">
                            {{
                                warehouse.pic_name || '-'
                            }}
                        </div>

                    </div>

                    <div>

                        <div class="text-sm text-gray-500">
                            Phone
                        </div>

                        <div class="font-medium">
                            {{
                                warehouse.phone || '-'
                            }}
                        </div>

                    </div>

                    <div>

                        <div class="text-sm text-gray-500">
                            Email
                        </div>

                        <div class="font-medium">
                            {{
                                warehouse.email || '-'
                            }}
                        </div>

                    </div>

                </div>

            </div>

            <!-- Address -->

            <div
                class="rounded-3xl bg-white p-8 shadow-sm"
            >

                <h3
                    class="mb-6 text-lg font-semibold"
                >
                    Address
                </h3>

                <p
                    class="text-gray-700"
                >
                    {{
                        warehouse.address || '-'
                    }}
                </p>

            </div>

        </div>

        <BaseToast

            :show="page.props.flash.success"

            :message="page.props.flash.success"

        />

    </AppLayout>

</template>