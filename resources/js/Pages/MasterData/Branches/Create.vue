<script setup>

import { Head, Link, useForm } from '@inertiajs/vue3'
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
                        Create Branch
                    </h1>

                    <p
                        class="
                            mt-1
                            text-sm
                            text-gray-500
                        "
                    >
                        Create a new company branch.
                    </p>

                </div>

            </div>


            <!-- =========================================================
                 Form
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

                <form
                    @submit.prevent="submit"
                >

                    <div
                        class="
                            grid
                            gap-6
                            md:grid-cols-2
                        "
                    >

                        <!-- Company -->

                        <div>

                            <label
                                class="
                                    mb-2
                                    block
                                    text-sm
                                    font-medium
                                    text-gray-700
                                "
                            >
                                Company *
                            </label>

                            <select
                                v-model="form.company_id"
                                class="
                                    w-full
                                    rounded-lg
                                    border
                                    border-gray-200
                                    px-3
                                    py-2
                                    text-sm
                                "
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

                            <div
                                v-if="form.errors.company_id"
                                class="
                                    mt-1
                                    text-xs
                                    text-red-600
                                "
                            >
                                {{ form.errors.company_id }}
                            </div>

                        </div>


                        <!-- Branch Name -->

                        <div>

                            <label
                                class="
                                    mb-2
                                    block
                                    text-sm
                                    font-medium
                                    text-gray-700
                                "
                            >
                                Branch Name *
                            </label>

                            <input
                                v-model="form.name"
                                type="text"
                                class="
                                    w-full
                                    rounded-lg
                                    border
                                    border-gray-200
                                    px-3
                                    py-2
                                    text-sm
                                "
                            />

                            <div
                                v-if="form.errors.name"
                                class="
                                    mt-1
                                    text-xs
                                    text-red-600
                                "
                            >
                                {{ form.errors.name }}
                            </div>

                        </div>


                        <!-- Manager -->

                        <div>

                            <label
                                class="
                                    mb-2
                                    block
                                    text-sm
                                    font-medium
                                    text-gray-700
                                "
                            >
                                Manager Name
                            </label>

                            <input
                                v-model="form.manager_name"
                                type="text"
                                class="
                                    w-full
                                    rounded-lg
                                    border
                                    border-gray-200
                                    px-3
                                    py-2
                                    text-sm
                                "
                            />

                        </div>


                        <!-- Phone -->

                        <div>

                            <label
                                class="
                                    mb-2
                                    block
                                    text-sm
                                    font-medium
                                    text-gray-700
                                "
                            >
                                Phone
                            </label>

                            <input
                                v-model="form.phone"
                                type="text"
                                class="
                                    w-full
                                    rounded-lg
                                    border
                                    border-gray-200
                                    px-3
                                    py-2
                                    text-sm
                                "
                            />

                        </div>


                        <!-- Email -->

                        <div>

                            <label
                                class="
                                    mb-2
                                    block
                                    text-sm
                                    font-medium
                                    text-gray-700
                                "
                            >
                                Email
                            </label>

                            <input
                                v-model="form.email"
                                type="email"
                                class="
                                    w-full
                                    rounded-lg
                                    border
                                    border-gray-200
                                    px-3
                                    py-2
                                    text-sm
                                "
                            />

                        </div>


                        <!-- City -->

                        <div>

                            <label
                                class="
                                    mb-2
                                    block
                                    text-sm
                                    font-medium
                                    text-gray-700
                                "
                            >
                                City
                            </label>

                            <input
                                v-model="form.city"
                                type="text"
                                class="
                                    w-full
                                    rounded-lg
                                    border
                                    border-gray-200
                                    px-3
                                    py-2
                                    text-sm
                                "
                            />

                        </div>


                        <!-- Province -->

                        <div>

                            <label
                                class="
                                    mb-2
                                    block
                                    text-sm
                                    font-medium
                                    text-gray-700
                                "
                            >
                                Province
                            </label>

                            <input
                                v-model="form.province"
                                type="text"
                                class="
                                    w-full
                                    rounded-lg
                                    border
                                    border-gray-200
                                    px-3
                                    py-2
                                    text-sm
                                "
                            />

                        </div>


                        <!-- Head Office -->

                        <div>

                            <label
                                class="
                                    mb-2
                                    block
                                    text-sm
                                    font-medium
                                    text-gray-700
                                "
                            >
                                Office Type
                            </label>

                            <select
                                v-model="form.is_head_office"
                                class="
                                    w-full
                                    rounded-lg
                                    border
                                    border-gray-200
                                    px-3
                                    py-2
                                    text-sm
                                "
                            >

                                <option :value="false">
                                    Branch Office
                                </option>

                                <option :value="true">
                                    Head Office
                                </option>

                            </select>

                        </div>


                        <!-- Status -->

                        <div>

                            <label
                                class="
                                    mb-2
                                    block
                                    text-sm
                                    font-medium
                                    text-gray-700
                                "
                            >
                                Status
                            </label>

                            <select
                                v-model="form.status"
                                class="
                                    w-full
                                    rounded-lg
                                    border
                                    border-gray-200
                                    px-3
                                    py-2
                                    text-sm
                                "
                            >

                                <option :value="true">
                                    Active
                                </option>

                                <option :value="false">
                                    Inactive
                                </option>

                            </select>

                        </div>

                    </div>


                    <!-- Address -->

                    <div class="mt-6">

                        <label
                            class="
                                mb-2
                                block
                                text-sm
                                font-medium
                                text-gray-700
                            "
                        >
                            Address
                        </label>

                        <textarea
                            v-model="form.address"
                            rows="4"
                            class="
                                w-full
                                rounded-lg
                                border
                                border-gray-200
                                px-3
                                py-2
                                text-sm
                            "
                        ></textarea>

                    </div>


                    <!-- Action -->

                    <div
                        class="
                            mt-8
                            flex
                            justify-end
                            gap-3
                        "
                    >

                        <Link
                            :href="
                                route(
                                    'branches.index'
                                )
                            "
                            class="
                                rounded-lg
                                border
                                border-gray-200
                                bg-white
                                px-4
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

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="
                                rounded-lg
                                bg-gray-900
                                px-4
                                py-2
                                text-sm
                                font-medium
                                text-white
                                transition
                                hover:bg-gray-800
                                disabled:cursor-not-allowed
                                disabled:opacity-50
                            "
                        >
                            {{
                                form.processing
                                    ? 'Saving...'
                                    : 'Save Branch'
                            }}
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </AppLayout>

</template>