<script setup>

import { Head, Link, useForm } from '@inertiajs/vue3'

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

import BaseToast from '@/Components/UI/BaseToast.vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const props = defineProps({

    supplier: Object

})

const form = useForm({

    name:
        props.supplier.name,

    contact_person:
        props.supplier.contact_person,

    phone:
        props.supplier.phone,

    email:
        props.supplier.email,

    city:
        props.supplier.city,

    tax_number:
        props.supplier.tax_number,

    payment_term:
        props.supplier.payment_term,

    address:
        props.supplier.address,

    status:
        props.supplier.status,

})

const submit = () => {

    form.put(

        route(
    'suppliers.update',
    props.supplier.id
)

    )

}

</script>
<template>

    <Head title="Edit suppliers" />

    <AuthenticatedLayout>

        <template #header>

            <div
                class="flex items-center justify-between"
            >

                <div>

                    <h2
                        class="text-3xl font-bold text-gray-800"
                    >
                        Edit Supplier
                    </h2>

                    <p
                        class="mt-1 text-sm text-gray-500"
                    >
                        Update supplier information
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

                      <!-- Supplier Information -->
                        <div class="grid gap-6 md:grid-cols-2">

                         

                            <!-- Supplier Name -->

                            <div>

                                <label class="mb-2 block text-sm font-medium">
                                    Supplier Name *
                                </label>

                                <input
                                    v-model="form.name"
                                    type="text"
                                    class="w-full rounded-xl border-gray-300"
                                />

                            </div>

                            <!-- Contact Person -->

                            <div>

                                <label class="mb-2 block text-sm font-medium">
                                    Contact Person
                                </label>

                                <input
                                    v-model="form.contact_person"
                                    type="text"
                                    class="w-full rounded-xl border-gray-300"
                                />

                            </div>

                            <!-- Phone -->

                            <div>

                                <label class="mb-2 block text-sm font-medium">
                                    Phone
                                </label>

                                <input
                                    v-model="form.phone"
                                    type="text"
                                    class="w-full rounded-xl border-gray-300"
                                />

                            </div>

                            <!-- Email -->

                            <div>

                                <label class="mb-2 block text-sm font-medium">
                                    Email
                                </label>

                                <input
                                    v-model="form.email"
                                    type="email"
                                    class="w-full rounded-xl border-gray-300"
                                />

                            </div>

                            <!-- City -->

                            <div>

                                <label class="mb-2 block text-sm font-medium">
                                    City
                                </label>

                                <input
                                    v-model="form.city"
                                    type="text"
                                    class="w-full rounded-xl border-gray-300"
                                />

                            </div>

                            <!-- Tax Number -->

                            <div>

                                <label class="mb-2 block text-sm font-medium">
                                    Tax Number
                                </label>

                                <input
                                    v-model="form.tax_number"
                                    type="text"
                                    class="w-full rounded-xl border-gray-300"
                                />

                            </div>

                            <!-- Payment Term -->

                            <div>

                                <label class="mb-2 block text-sm font-medium">
                                    Payment Term (Days)
                                </label>

                                <input
                                    v-model="form.payment_term"
                                    type="number"
                                    class="w-full rounded-xl border-gray-300"
                                />

                            </div>

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

                                <option :value="true">
                                    Active
                                </option>

                                <option :value="false">
                                    Inactive
                                </option>

                            </select>

                       

                     </div>

                    <!-- Address -->

                            <div class="mt-6">

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
                            :href="route('suppliers.index')"
                            class="rounded-xl border border-gray-300 px-6 py-3 text-gray-700 hover:bg-gray-50"
                        >
                            Back
                        </Link>

                        <button
                            type="submit"
                            class="rounded-xl bg-blue-600 px-6 py-3 text-white hover:bg-blue-700"
                        >
                            Edit Supplier
                        </button>

                    </div>

</form>

</div>

</div>
  <BaseToast

            :show="page.props.flash.success"

            :message="page.props.flash.success"

        />
</AuthenticatedLayout>

</template>
                   

   