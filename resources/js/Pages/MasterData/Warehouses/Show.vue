<script setup>

import { Head, Link, usePage } from '@inertiajs/vue3'

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

        <div class="space-y-4">

            <!-- =========================================================
                 Header
            ========================================================== -->

            <div
                class="
                    flex
                    flex-col
                    gap-3
                    sm:flex-row
                    sm:items-center
                    sm:justify-between
                "
            >

                <div>

                    <h1
                        class="
                            text-xl
                            font-semibold
                            text-gray-900
                        "
                    >
                        Warehouse Detail
                    </h1>

                    <p
                        class="
                            mt-1
                            text-sm
                            text-gray-500
                        "
                    >
                        Warehouse information overview.
                    </p>

                </div>


                <!-- Actions -->

                <div
                    class="
                        flex
                        items-center
                        gap-2
                    "
                >

                    <!-- Back -->

                    <Link
                        :href="
                            route(
                                'warehouses.index'
                            )
                        "
                        class="
                            inline-flex
                            items-center
                            gap-2
                            rounded-lg
                            border
                            border-gray-200
                            bg-white
                            px-3
                            py-2
                            text-sm
                            font-medium
                            text-gray-700
                            transition
                            hover:bg-gray-50
                        "
                    >
                        Back
                    </Link>


                    <!-- Edit -->

                    <Link
                        :href="
                            route(
                                'warehouses.edit',
                                warehouse.id
                            )
                        "
                        class="
                            inline-flex
                            items-center
                            gap-2
                            rounded-lg
                            bg-gray-900
                            px-3
                            py-2
                            text-sm
                            font-medium
                            text-white
                            transition
                            hover:bg-gray-800
                        "
                    >
                        Edit
                    </Link>

                </div>

            </div>


            <!-- =========================================================
                 Warehouse Header Card
            ========================================================== -->

            <div
                class="
                    rounded-xl
                    border
                    border-gray-100
                    bg-white
                    p-4
                    shadow-sm
                "
            >

                <div
                    class="
                        flex
                        items-center
                        gap-4
                    "
                >

                    <div
                        class="
                            flex
                            h-16
                            w-16
                            items-center
                            justify-center
                            rounded-xl
                            bg-blue-50
                            text-3xl
                        "
                    >
                        🏭
                    </div>

                    <div>

                        <h2
                            class="
                                text-xl
                                font-semibold
                                text-gray-900
                            "
                        >
                            {{ warehouse.name }}
                        </h2>

                        <p
                            class="
                                mt-1
                                text-sm
                                text-gray-500
                            "
                        >
                            {{ warehouse.code }}
                        </p>

                    </div>

                </div>

            </div>


            <!-- =========================================================
                 General Information
            ========================================================== -->

            <div
                class="
                    rounded-xl
                    border
                    border-gray-100
                    bg-white
                    p-4
                    shadow-sm
                "
            >

                <h3
                    class="
                        mb-5
                        text-base
                        font-semibold
                        text-gray-900
                    "
                >
                    General Information
                </h3>

                <div
                    class="
                        grid
                        gap-5
                        md:grid-cols-2
                    "
                >

                    <div>

                        <div class="text-xs font-medium text-gray-500">
                            Company
                        </div>

                        <div
                            class="
                                mt-1
                                text-sm
                                font-medium
                                text-gray-900
                            "
                        >
                            {{
                                warehouse.branch?.company?.company_name || '-'
                            }}
                        </div>

                    </div>


                    <div>

                        <div class="text-xs font-medium text-gray-500">
                            Branch
                        </div>

                        <div
                            class="
                                mt-1
                                text-sm
                                font-medium
                                text-gray-900
                            "
                        >
                            {{
                                warehouse.branch?.name || '-'
                            }}
                        </div>

                    </div>


                    <div>

                        <div class="text-xs font-medium text-gray-500">
                            Warehouse Type
                        </div>

                        <div
                            class="
                                mt-1
                                text-sm
                                font-medium
                                text-gray-900
                            "
                        >
                            {{
                                warehouse.warehouse_type
                            }}
                        </div>

                    </div>


                    <div>

                        <div class="text-xs font-medium text-gray-500">
                            Status
                        </div>

                        <div class="mt-1">

                            <span
                                v-if="warehouse.status"
                                class="
                                    inline-flex
                                    rounded-full
                                    bg-green-100
                                    px-3
                                    py-1
                                    text-xs
                                    font-medium
                                    text-green-700
                                "
                            >
                                Active
                            </span>

                            <span
                                v-else
                                class="
                                    inline-flex
                                    rounded-full
                                    bg-red-100
                                    px-3
                                    py-1
                                    text-xs
                                    font-medium
                                    text-red-700
                                "
                            >
                                Inactive
                            </span>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =========================================================
                 Contact Information
            ========================================================== -->

            <div
                class="
                    rounded-xl
                    border
                    border-gray-100
                    bg-white
                    p-4
                    shadow-sm
                "
            >

                <h3
                    class="
                        mb-5
                        text-base
                        font-semibold
                        text-gray-900
                    "
                >
                    Contact Information
                </h3>

                <div
                    class="
                        grid
                        gap-5
                        md:grid-cols-3
                    "
                >

                    <div>

                        <div class="text-xs font-medium text-gray-500">
                            PIC Name
                        </div>

                        <div
                            class="
                                mt-1
                                text-sm
                                font-medium
                                text-gray-900
                            "
                        >
                            {{
                                warehouse.pic_name || '-'
                            }}
                        </div>

                    </div>


                    <div>

                        <div class="text-xs font-medium text-gray-500">
                            Phone
                        </div>

                        <div
                            class="
                                mt-1
                                text-sm
                                font-medium
                                text-gray-900
                            "
                        >
                            {{
                                warehouse.phone || '-'
                            }}
                        </div>

                    </div>


                    <div>

                        <div class="text-xs font-medium text-gray-500">
                            Email
                        </div>

                        <div
                            class="
                                mt-1
                                text-sm
                                font-medium
                                text-gray-900
                            "
                        >
                            {{
                                warehouse.email || '-'
                            }}
                        </div>

                    </div>

                </div>

            </div>


            <!-- =========================================================
                 Address
            ========================================================== -->

            <div
                class="
                    rounded-xl
                    border
                    border-gray-100
                    bg-white
                    p-4
                    shadow-sm
                "
            >

                <h3
                    class="
                        mb-5
                        text-base
                        font-semibold
                        text-gray-900
                    "
                >
                    Address
                </h3>

                <p
                    class="
                        text-sm
                        leading-6
                        text-gray-700
                    "
                >
                    {{
                        warehouse.address || '-'
                    }}
                </p>

            </div>


            <BaseToast
                :show="page.props.flash.success"
                :message="page.props.flash.success"
            />

        </div>

    </AppLayout>

</template>