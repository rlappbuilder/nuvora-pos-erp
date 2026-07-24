<script setup>

import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import AppLayout from '@/Layouts/AppLayout.vue'
const props = defineProps({

    company: Object

})

</script>

<template>

    <Head
        :title="company.company_name"
    />

    <AppLayout>

        <template #header>

            <div
                class="flex items-center justify-between"
            >

                <div>

                    <h2
                        class="text-3xl font-bold text-gray-800"
                    >
                        Company Profile
                    </h2>

                    <p
                        class="mt-1 text-sm text-gray-500"
                    >
                        View company information
                    </p>

                </div>

                <div
                    class="flex gap-3"
                >

                    <Link

                        :href="
                            route(
                                'companies.index'
                            )
                        "

                        class="rounded-xl bg-slate-600 px-5 py-3 text-white"

                    >

                        Back

                    </Link>

                    <Link

                        :href="
                            route(
                                'companies.edit',
                                company.id
                            )
                        "

                        class="rounded-xl bg-amber-500 px-5 py-3 text-white"

                    >

                        Edit

                    </Link>

                </div>

            </div>

        </template>

        <div
            class="mx-auto max-w-5xl"
        >

            <div
                class="overflow-hidden rounded-3xl bg-white shadow-sm"
            >

                <div
                    class="h-2 bg-blue-600"
                ></div>

                <div
                    class="p-10"
                >

                    <!-- Logo -->

                    <div
                        class="flex justify-center"
                    >

                        <div
                            class="mb-6"
                        >

                            <img

                                v-if="
                                    company.logo
                                "

                                :src="
                                    '/storage/' +
                                    company.logo
                                "

                                class="h-20 w-20 rounded-full border object-cover"

                            >

                            <div

                                v-else

                                class="flex h-32 w-32 items-center justify-center rounded-full bg-slate-100 text-6xl"

                            >

                                🏢

                            </div>

                        </div>

                    </div>

                    <!-- Name -->

                    <h1
                        class="text-center text-4xl font-bold text-gray-800"
                    >

                        {{ company.company_name }}

                    </h1>

                    <p
                        class="mt-2 text-center text-gray-500"
                    >

                        {{ company.company_code }}

                    </p>

                    <div
                        class="my-10 border-t"
                    ></div>

                    <!-- Information -->

                    <div
                        class="grid grid-cols-1 gap-8 md:grid-cols-2"
                    >

                      <div
                        class="rounded-2xl border border-slate-200 p-6"
                    >

                        <h3
                            class="mb-6 text-lg font-semibold text-blue-600"
                        >

                            General Information

                        </h3>
                        

                            <div class="space-y-4">

    <div class="grid grid-cols-2 gap-4">
        <div class="text-gray-500">Legal Name</div>
        <div class="font-medium">
            {{ company.legal_name || '-' }}
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div class="text-gray-500">Director</div>
        <div class="font-medium">
            {{ company.director_name || '-' }}
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div class="text-gray-500">Tax Number</div>
        <div class="font-medium">
            {{ company.tax_number || '-' }}
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div class="text-gray-500">Status</div>

        <div>

            <span
                v-if="company.status"
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

                        <div class="rounded-2xl border border-slate-200 p-6">

                            <h3
                                class="mb-6 text-lg font-semibold text-blue-600"
                            >

                                Contact Information

                            </h3>

                            <div class="space-y-4">

    <div class="grid grid-cols-2 gap-4">
        <div class="text-gray-500">Phone</div>
        <div class="font-medium">
            {{ company.phone || '-' }}
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div class="text-gray-500">Email</div>
        <div class="font-medium">
            {{ company.email || '-' }}
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div class="text-gray-500">Website</div>
        <div class="font-medium">
            {{ company.website || '-' }}
        </div>
    </div>

</div>

                        </div>

                    </div>

                    <div
                        class="my-10 border-t"
                    ></div>

                    <!-- Address -->

                    <div>

                        <h3
                           class="mb-6 text-lg font-semibold text-blue-600"
                        >

                            Address

                        </h3>

                        <div
                            class="rounded-2xl border border-slate-200 p-6"
                        >

                            <p>
                                {{ company.address || '-' }}
                            </p>

                            <div
                                class="mt-4 flex flex-wrap gap-6 text-sm text-gray-600"
                            >

                                <span>
                                    City :
                                    {{ company.city || '-' }}
                                </span>

                                <span>
                                    Province :
                                    {{ company.province || '-' }}
                                </span>

                                <span>
                                    Postal :
                                    {{ company.postal_code || '-' }}
                                </span>

                            </div>

                        </div>

                    </div>

                    <div
                        class="my-10 border-t"
                    ></div>

                    <!-- Future Statistics -->

                    <div
    class="grid grid-cols-2 gap-4 md:grid-cols-4"
>

    <!-- Branches -->

    <div
        class="rounded-2xl border border-slate-200 p-5 text-center transition hover:shadow-md"
    >

        <div class="mb-2 text-3xl">
            🏢
        </div>

        <div
            class="text-3xl font-bold text-blue-600"
        >
            0
        </div>

        <div
            class="mt-1 text-sm text-gray-500"
        >
            Branches
        </div>

    </div>

    <!-- Warehouses -->

    <div
        class="rounded-2xl border border-slate-200 p-5 text-center transition hover:shadow-md"
    >

        <div class="mb-2 text-3xl">
            📦
        </div>

        <div
            class="text-3xl font-bold text-emerald-600"
        >
            0
        </div>

        <div
            class="mt-1 text-sm text-gray-500"
        >
            Warehouses
        </div>

    </div>

    <!-- Employees -->

    <div
        class="rounded-2xl border border-slate-200 p-5 text-center transition hover:shadow-md"
    >

        <div class="mb-2 text-3xl">
            👥
        </div>

        <div
            class="text-3xl font-bold text-amber-600"
        >
            0
        </div>

        <div
            class="mt-1 text-sm text-gray-500"
        >
            Employees
        </div>

    </div>

    <!-- Products -->

    <div
        class="rounded-2xl border border-slate-200 p-5 text-center transition hover:shadow-md"
    >

        <div class="mb-2 text-3xl">
            🛒
        </div>

        <div
            class="text-3xl font-bold text-purple-600"
        >
            0
        </div>

        <div
            class="mt-1 text-sm text-gray-500"
        >
            Products
        </div>

    </div>

</div>

                </div>

            </div>

        </div>

    </AppLayout>

</template>