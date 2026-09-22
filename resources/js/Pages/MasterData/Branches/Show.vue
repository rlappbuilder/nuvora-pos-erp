<script setup>

import { Head, Link, usePage } from '@inertiajs/vue3'

import BaseToast from '@/Components/UI/BaseToast.vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const page = usePage()

defineProps({

    branch: Object

})

</script>

<template>

    <Head :title="branch.name" />

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
                        Branch Detail
                    </h1>

                    <p
                        class="
                            mt-1
                            text-sm
                            text-gray-500
                        "
                    >
                        View branch information and organization structure.
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

                    <Link
                        :href="
                            route(
                                'branches.index'
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

                    <Link
                        :href="
                            route(
                                'branches.edit',
                                branch.id
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
                 Branch Header Card
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
                        🏢
                    </div>

                    <div>

                        <h2
                            class="
                                text-xl
                                font-semibold
                                text-gray-900
                            "
                        >
                            {{ branch.name }}
                        </h2>

                        <p
                            class="
                                mt-1
                                text-sm
                                text-gray-500
                            "
                        >
                            {{ branch.code }}
                        </p>

                        <div
                            class="
                                mt-2
                                flex
                                flex-wrap
                                gap-2
                            "
                        >

                            <span
                                class="
                                    inline-flex
                                    rounded-full
                                    bg-blue-50
                                    px-3
                                    py-1
                                    text-xs
                                    font-medium
                                    text-blue-700
                                "
                            >
                                {{
                                    branch.is_head_office
                                        ? 'Head Office'
                                        : 'Branch Office'
                                }}
                            </span>

                            <span
                                v-if="branch.status"
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

                        <div
                            class="
                                text-xs
                                font-medium
                                text-gray-500
                            "
                        >
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
                                branch.company?.company_name
                                || '-'
                            }}
                        </div>

                    </div>


                    <div>

                        <div
                            class="
                                text-xs
                                font-medium
                                text-gray-500
                            "
                        >
                            Manager
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
                                branch.manager_name
                                || '-'
                            }}
                        </div>

                    </div>


                    <div>

                        <div
                            class="
                                text-xs
                                font-medium
                                text-gray-500
                            "
                        >
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
                                branch.phone
                                || '-'
                            }}
                        </div>

                    </div>


                    <div>

                        <div
                            class="
                                text-xs
                                font-medium
                                text-gray-500
                            "
                        >
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
                                branch.email
                                || '-'
                            }}
                        </div>

                    </div>

                </div>

            </div>


            <!-- =========================================================
                 Location Information
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
                    Location Information
                </h3>

                <div
                    class="
                        grid
                        gap-5
                        md:grid-cols-2
                    "
                >

                    <div>

                        <div
                            class="
                                text-xs
                                font-medium
                                text-gray-500
                            "
                        >
                            City
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
                                branch.city
                                || '-'
                            }}
                        </div>

                    </div>


                    <div>

                        <div
                            class="
                                text-xs
                                font-medium
                                text-gray-500
                            "
                        >
                            Province
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
                                branch.province
                                || '-'
                            }}
                        </div>

                    </div>


                    <div
                        class="
                            md:col-span-2
                        "
                    >

                        <div
                            class="
                                text-xs
                                font-medium
                                text-gray-500
                            "
                        >
                            Address
                        </div>

                        <div
                            class="
                                mt-1
                                rounded-lg
                                bg-gray-50
                                p-3
                                text-sm
                                leading-6
                                text-gray-700
                            "
                        >
                            {{
                                branch.address
                                || '-'
                            }}
                        </div>

                    </div>

                </div>

            </div>


            <!-- =========================================================
                 Statistics
            ========================================================== -->

            <div
                class="
                    grid
                    gap-4
                    md:grid-cols-3
                "
            >

                <div
                    class="
                        rounded-xl
                        border
                        border-gray-100
                        bg-white
                        p-5
                        text-center
                        shadow-sm
                    "
                >

                    <div
                        class="
                            text-3xl
                            font-semibold
                            text-blue-600
                        "
                    >
                        0
                    </div>

                    <div
                        class="
                            mt-1
                            text-sm
                            text-gray-500
                        "
                    >
                        Warehouses
                    </div>

                </div>


                <div
                    class="
                        rounded-xl
                        border
                        border-gray-100
                        bg-white
                        p-5
                        text-center
                        shadow-sm
                    "
                >

                    <div
                        class="
                            text-3xl
                            font-semibold
                            text-green-600
                        "
                    >
                        0
                    </div>

                    <div
                        class="
                            mt-1
                            text-sm
                            text-gray-500
                        "
                    >
                        Employees
                    </div>

                </div>


                <div
                    class="
                        rounded-xl
                        border
                        border-gray-100
                        bg-white
                        p-5
                        text-center
                        shadow-sm
                    "
                >

                    <div
                        class="
                            text-3xl
                            font-semibold
                            text-purple-600
                        "
                    >
                        0
                    </div>

                    <div
                        class="
                            mt-1
                            text-sm
                            text-gray-500
                        "
                    >
                        Assets
                    </div>

                </div>

            </div>


            <BaseToast
                :show="page.props.flash.success"
                :message="page.props.flash.success"
            />

        </div>

    </AppLayout>

</template>