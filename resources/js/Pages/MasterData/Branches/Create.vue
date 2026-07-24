<script setup>

import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import AppLayout from '@/Layouts/AppLayout.vue'
const props = defineProps({

    companies: Array

})

const form = useForm({

    company_id: '',

    name: '',

    manager_name: '',

    phone: '',

    email: '',

    address: '',

    city: '',

    province: '',

    is_head_office: false,

    status: true

})

const submit = () => {

    form.post(

        route(
            'branches.store'
        )

    )

}

</script>
<template>

    <Head title="Create Branch" />

    <AppLayout>

        <template #header>

            <div
                class="flex items-center justify-between"
            >

                <div>

                    <h2
                        class="text-3xl font-bold text-gray-800"
                    >
                        Create Branch
                    </h2>

                    <p
                        class="mt-1 text-sm text-gray-500"
                    >
                        Create new company branch.
                    </p>

                </div>

            </div>

        </template>

        <div
            class="mx-auto max-w-6xl"
        >

            <div
                class="rounded-3xl bg-white p-8 shadow-sm"
            >

                <form
                    @submit.prevent="submit"
                >

                    <div
                        class="grid gap-6 md:grid-cols-2"
                    >

                        <!-- Company -->

                        <div>

                            <label
                                class="mb-2 block text-sm font-medium"
                            >
                                Company *
                            </label>

                            <select

                                v-model="
                                    form.company_id
                                "

                                class="w-full rounded-xl border-gray-300"

                            >

                                <option value="">
                                    Select Company
                                </option>

                                <option

                                    v-for="company in companies"

                                    :key="company.id"

                                    :value="company.id"

                                >

                                    {{ company.company_name }}

                                </option>

                            </select>

                        </div>

                        <!-- Branch Name -->

                        <div>

                            <label
                                class="mb-2 block text-sm font-medium"
                            >
                                Branch Name *
                            </label>

                            <input

                                v-model="
                                    form.name
                                "

                                type="text"

                                class="w-full rounded-xl border-gray-300"

                            >

                        </div>

                        <!-- Manager -->

                        <div>

                            <label
                                class="mb-2 block text-sm font-medium"
                            >
                                Manager Name
                            </label>

                            <input

                                v-model="
                                    form.manager_name
                                "

                                type="text"

                                class="w-full rounded-xl border-gray-300"

                            >

                        </div>

                        <!-- Phone -->

                        <div>

                            <label
                                class="mb-2 block text-sm font-medium"
                            >
                                Phone
                            </label>

                            <input

                                v-model="
                                    form.phone
                                "

                                type="text"

                                class="w-full rounded-xl border-gray-300"

                            >

                        </div>

                        <!-- Email -->

                        <div>

                            <label
                                class="mb-2 block text-sm font-medium"
                            >
                                Email
                            </label>

                            <input

                                v-model="
                                    form.email
                                "

                                type="email"

                                class="w-full rounded-xl border-gray-300"

                            >

                        </div>

                        <!-- City -->

                        <div>

                            <label
                                class="mb-2 block text-sm font-medium"
                            >
                                City
                            </label>

                            <input

                                v-model="
                                    form.city
                                "

                                type="text"

                                class="w-full rounded-xl border-gray-300"

                            >

                        </div>

                        <!-- Province -->

                        <div>

                            <label
                                class="mb-2 block text-sm font-medium"
                            >
                                Province
                            </label>

                            <input

                                v-model="
                                    form.province
                                "

                                type="text"

                                class="w-full rounded-xl border-gray-300"

                            >

                        </div>

                        <!-- Head Office -->

                        <div>

                            <label
                                class="mb-2 block text-sm font-medium"
                            >
                                Office Type
                            </label>

                            <select

                                v-model="
                                    form.is_head_office
                                "

                                class="w-full rounded-xl border-gray-300"

                            >

                                <option
                                    :value="false"
                                >
                                    Branch Office
                                </option>

                                <option
                                    :value="true"
                                >
                                    Head Office
                                </option>

                            </select>

                        </div>

                        <!-- Status -->

                        <div>

                            <label
                                class="mb-2 block text-sm font-medium"
                            >
                                Status
                            </label>

                            <select

                                v-model="
                                    form.status
                                "

                                class="w-full rounded-xl border-gray-300"

                            >

                                <option
                                    :value="true"
                                >
                                    Active
                                </option>

                                <option
                                    :value="false"
                                >
                                    Inactive
                                </option>

                            </select>

                        </div>

                    </div>

                    <!-- Address -->

                    <div
                        class="mt-6"
                    >

                        <label
                            class="mb-2 block text-sm font-medium"
                        >
                            Address
                        </label>

                        <textarea

                            v-model="
                                form.address
                            "

                            rows="4"

                            class="w-full rounded-xl border-gray-300"

                        ></textarea>

                    </div>

                    <!-- Action -->

                    <div
                        class="mt-8 flex justify-end gap-3"
                    >

                        <Link

                            :href="
                                route(
                                    'branches.index'
                                )
                            "

                            class="rounded-xl border px-5 py-3"

                        >

                            Cancel

                        </Link>

                        <button

                            type="submit"

                            class="rounded-xl bg-blue-600 px-6 py-3 text-white hover:bg-blue-700"

                        >

                            Save Branch

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </AppLayout>

</template>