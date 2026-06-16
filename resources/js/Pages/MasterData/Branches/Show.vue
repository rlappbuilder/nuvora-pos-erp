<script setup>
import BaseToast from '@/Components/UI/BaseToast.vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({

    branch: Object

})

</script>

<template>

    <Head :title="branch.name" />

    <AuthenticatedLayout>

        <template #header>

            <div
                class="flex items-center justify-between"
            >

                <div>

                    <h2
                        class="text-3xl font-bold text-gray-800"
                    >
                        Branch Detail
                    </h2>

                    <p
                        class="mt-1 text-sm text-gray-500"
                    >
                        View branch information and organization structure.
                    </p>

                </div>

                <div
                    class="flex gap-3"
                >

                    <Link

                        :href="
                            route(
                                'branches.edit',
                                branch.id
                            )
                        "

                        class="rounded-xl bg-amber-500 px-5 py-3 font-medium text-white hover:bg-amber-600"

                    >

                        Edit Branch

                    </Link>

                    <Link

                        :href="
                            route(
                                'branches.index'
                            )
                        "

                        class="rounded-xl border border-gray-300 px-5 py-3 font-medium text-gray-700 hover:bg-gray-100"

                    >

                        Back

                    </Link>

                </div>

            </div>

        </template>

        <div
            class="mx-auto max-w-7xl space-y-6"
        >

            <!-- Hero Card -->

            <div
                class="overflow-hidden rounded-3xl bg-white shadow-sm"
            >

                <div
                    class="h-2 bg-blue-600"
                ></div>

                <div
                    class="p-10 text-center"
                >

                    <div

                        class="mx-auto mb-5 flex h-28 w-28 items-center justify-center rounded-full bg-slate-100 text-6xl"

                    >

                        🏢

                    </div>

                    <h1
                        class="text-3xl font-bold text-gray-800"
                    >

                        {{ branch.name }}

                    </h1>

                    <p
                        class="mt-2 text-gray-500"
                    >

                        {{ branch.code }}

                    </p>

                    <div
                        class="mt-5 flex justify-center gap-3"
                    >

                        <span

                            class="rounded-full bg-blue-100 px-4 py-1 text-sm font-medium text-blue-700"

                        >

                            {{
                                branch.is_head_office
                                    ? 'Head Office'
                                    : 'Branch Office'
                            }}

                        </span>

                        <span

                            v-if="branch.status"

                            class="rounded-full bg-green-100 px-4 py-1 text-sm font-medium text-green-700"

                        >

                            Active

                        </span>

                        <span

                            v-else

                            class="rounded-full bg-red-100 px-4 py-1 text-sm font-medium text-red-700"

                        >

                            Inactive

                        </span>

                    </div>

                </div>

            </div>

            <!-- Information -->

            <div
                class="grid gap-6 lg:grid-cols-2"
            >

                <!-- General -->

                <div
                    class="rounded-3xl bg-white p-8 shadow-sm"
                >

                    <h3
                        class="mb-6 text-xl font-bold text-gray-800"
                    >

                        General Information

                    </h3>

                    <div
                        class="space-y-5"
                    >

                        <div
                            class="flex justify-between border-b pb-3"
                        >

                            <span class="text-gray-500">

                                Company

                            </span>

                            <span class="font-medium">

                                {{
                                    branch.company
                                        ?.company_name
                                    || '-'
                                }}

                            </span>

                        </div>

                        <div
                            class="flex justify-between border-b pb-3"
                        >

                            <span class="text-gray-500">

                                Manager

                            </span>

                            <span class="font-medium">

                                {{
                                    branch.manager_name
                                    || '-'
                                }}

                            </span>

                        </div>

                        <div
                            class="flex justify-between border-b pb-3"
                        >

                            <span class="text-gray-500">

                                Phone

                            </span>

                            <span class="font-medium">

                                {{
                                    branch.phone
                                    || '-'
                                }}

                            </span>

                        </div>

                        <div
                            class="flex justify-between"
                        >

                            <span class="text-gray-500">

                                Email

                            </span>

                            <span class="font-medium">

                                {{
                                    branch.email
                                    || '-'
                                }}

                            </span>

                        </div>

                    </div>

                </div>

                <!-- Location -->

                <div
                    class="rounded-3xl bg-white p-8 shadow-sm"
                >

                    <h3
                        class="mb-6 text-xl font-bold text-gray-800"
                    >

                        Location Information

                    </h3>

                    <div
                        class="space-y-5"
                    >

                        <div
                            class="flex justify-between border-b pb-3"
                        >

                            <span class="text-gray-500">

                                City

                            </span>

                            <span class="font-medium">

                                {{
                                    branch.city
                                    || '-'
                                }}

                            </span>

                        </div>

                        <div
                            class="flex justify-between border-b pb-3"
                        >

                            <span class="text-gray-500">

                                Province

                            </span>

                            <span class="font-medium">

                                {{
                                    branch.province
                                    || '-'
                                }}

                            </span>

                        </div>

                        <div>

                            <div
                                class="mb-2 text-gray-500"
                            >

                                Address

                            </div>

                            <div
                                class="rounded-2xl bg-slate-50 p-4"
                            >

                                {{
                                    branch.address
                                    || '-'
                                }}

                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <!-- Statistics -->

            <div
                class="grid gap-6 md:grid-cols-3"
            >

                <div
                    class="rounded-3xl bg-white p-8 text-center shadow-sm"
                >

                    <div
                        class="text-4xl font-bold text-blue-600"
                    >

                        0

                    </div>

                    <div
                        class="mt-2 text-gray-500"
                    >

                        Warehouses

                    </div>

                </div>

                <div
                    class="rounded-3xl bg-white p-8 text-center shadow-sm"
                >

                    <div
                        class="text-4xl font-bold text-green-600"
                    >

                        0

                    </div>

                    <div
                        class="mt-2 text-gray-500"
                    >

                        Employees

                    </div>

                </div>

                <div
                    class="rounded-3xl bg-white p-8 text-center shadow-sm"
                >

                    <div
                        class="text-4xl font-bold text-purple-600"
                    >

                        0

                    </div>

                    <div
                        class="mt-2 text-gray-500"
                    >

                        Assets

                    </div>

                </div>

            </div>

        </div>
<BaseToast

    :show="page.props.flash.success"

    :message="page.props.flash.success"

/>
    </AuthenticatedLayout>

</template>