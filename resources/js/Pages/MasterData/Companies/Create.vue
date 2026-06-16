<script setup>

import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const form = useForm({

    company_name: '',

    legal_name: '',

    phone: '',

    email: '',

    website: '',

    tax_number: '',

    director_name: '',

    logo: '',

    address: '',

    city: '',

    province: '',

    postal_code: '',

    status: true

})

const submit = () => {

   form.post(

    route(
        'companies.store'
    ),

    {

        forceFormData: true

    }

)

}

</script>

<template>

    <Head title="Create Company" />

    <AuthenticatedLayout>

        <template #header>

            <div
                class="flex items-center justify-between"
            >

                <div>

                    <h2
                        class="text-3xl font-bold text-gray-800"
                    >
                        Create Company
                    </h2>

                    <p
                        class="mt-1 text-sm text-gray-500"
                    >
                        Create new company profile.
                    </p>

                </div>

                <Link

                    :href="route('companies.index')"

                    class="rounded-xl bg-slate-600 px-5 py-3 text-white"

                >

                    Back

                </Link>

            </div>

        </template>

        <div
            class="rounded-3xl bg-white p-8 shadow-sm"
        >

            <form
                @submit.prevent="submit"
            >

                <div
                    class="grid grid-cols-1 gap-6 md:grid-cols-2"
                >

                    <div>

                        <label
                            class="mb-2 block text-sm font-medium"
                        >
                            Company Name
                        </label>

                        <input

                            v-model="
                                form.company_name
                            "

                            type="text"

                            class="w-full rounded-xl border-gray-300"

                        >

                    </div>

                    <div>

                        <label
                            class="mb-2 block text-sm font-medium"
                        >
                            Legal Name
                        </label>

                        <input

                            v-model="
                                form.legal_name
                            "

                            type="text"

                            class="w-full rounded-xl border-gray-300"

                        >

                    </div>

                    <div>

                        <label
                            class="mb-2 block text-sm font-medium"
                        >
                            Director
                        </label>

                        <input

                            v-model="
                                form.director_name
                            "

                            type="text"

                            class="w-full rounded-xl border-gray-300"

                        >

                    </div>

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

                    <div>

                        <label
                            class="mb-2 block text-sm font-medium"
                        >
                            Website
                        </label>

                        <input

                            v-model="
                                form.website
                            "

                            type="text"

                            class="w-full rounded-xl border-gray-300"

                        >

                    </div>

                    <div>

                        <label
                            class="mb-2 block text-sm font-medium"
                        >
                            Tax Number
                        </label>

                        <input

                            v-model="
                                form.tax_number
                            "

                            type="text"

                            class="w-full rounded-xl border-gray-300"

                        >

                    </div>

                    <div>

                        <label
                            class="mb-2 block text-sm font-medium"
                        >
                            Logo
                        </label>

                     <input

                            type="file"

                            accept=".jpg,.jpeg,.png"

                            @change="

                                form.logo =

                                $event.target.files[0]

                            "

                            class="w-full rounded-xl border-gray-300"

                        >

                    </div>

                </div>

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

                <div
                    class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-3"
                >

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

                            class="w-full rounded-xl border-gray-300"

                        >

                    </div>

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

                            class="w-full rounded-xl border-gray-300"

                        >

                    </div>

                    <div>

                        <label
                            class="mb-2 block text-sm font-medium"
                        >
                            Postal Code
                        </label>

                        <input

                            v-model="
                                form.postal_code
                            "

                            class="w-full rounded-xl border-gray-300"

                        >

                    </div>

                </div>

                <div
                    class="mt-6"
                >

                    <label
                        class="flex items-center gap-3"
                    >

                        <input

                            v-model="
                                form.status
                            "

                            type="checkbox"

                        >

                        Active

                    </label>

                </div>

                <div
                    class="mt-10 flex justify-end gap-3"
                >

                    <Link

                        :href="route('companies.index')"

                        class="rounded-xl bg-slate-500 px-5 py-3 text-white"

                    >

                        Cancel

                    </Link>

                    <button

                        type="submit"

                        class="rounded-xl bg-blue-600 px-5 py-3 text-white"

                    >

                        Save Company

                    </button>

                </div>

            </form>

        </div>

    </AuthenticatedLayout>

</template>