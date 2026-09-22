<script setup>

import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({

    company: Object

})

const form = useForm({

    company_name: props.company.company_name,

    legal_name: props.company.legal_name,

    phone: props.company.phone,

    email: props.company.email,

    website: props.company.website,

    tax_number: props.company.tax_number,

    director_name: props.company.director_name,

    logo: null,

    address: props.company.address,

    city: props.company.city,

    province: props.company.province,

    postal_code: props.company.postal_code,

    status: props.company.status

})

const submit = () => {

    form.transform(

        (data) => ({

            ...data,

            _method: 'put'

        })

    ).post(

        route(
            'companies.update',

            props.company.id

        ),

        {

            forceFormData: true

        }

    )

}

</script>

<template>

    <Head title="Edit Company" />

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
                        Edit Company
                    </h1>

                    <p
                        class="
                            mt-1
                            text-sm
                            text-gray-500
                        "
                    >
                        Edit company profile.
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

                        <!-- Company Name -->

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
                                Company Name
                            </label>

                            <input
                                v-model="form.company_name"
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
                                v-if="form.errors.company_name"
                                class="
                                    mt-1
                                    text-xs
                                    text-red-600
                                "
                            >
                                {{ form.errors.company_name }}
                            </div>

                        </div>


                        <!-- Legal Name -->

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
                                Legal Name
                            </label>

                            <input
                                v-model="form.legal_name"
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


                        <!-- Director -->

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
                                Director
                            </label>

                            <input
                                v-model="form.director_name"
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


                        <!-- Website -->

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
                                Website
                            </label>

                            <input
                                v-model="form.website"
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


                        <!-- Tax Number -->

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
                                Tax Number
                            </label>

                            <input
                                v-model="form.tax_number"
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


                        <!-- Logo -->

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
                                Logo
                            </label>

                            <div
                                v-if="company.logo"
                                class="mb-4"
                            >

                                <img
                                    :src="
                                        '/storage/' +
                                        company.logo
                                    "
                                    class="
                                        h-24
                                        w-24
                                        rounded-lg
                                        border
                                        border-gray-200
                                        object-cover
                                    "
                                >

                            </div>

                            <input
                                type="file"
                                accept=".jpg,.jpeg,.png"
                                @change="
                                    form.logo =
                                    $event.target.files[0]
                                "
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


                    <!-- Location -->

                    <div
                        class="
                            mt-6
                            grid
                            grid-cols-1
                            gap-6
                            md:grid-cols-3
                        "
                    >

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


                        <!-- Postal Code -->

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
                                Postal Code
                            </label>

                            <input
                                v-model="form.postal_code"
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

                    </div>


                    <!-- Status -->

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
                                md:w-1/2
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
                                    'companies.index'
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
                                    ? 'Updating...'
                                    : 'Update Company'
                            }}

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </AppLayout>

</template>