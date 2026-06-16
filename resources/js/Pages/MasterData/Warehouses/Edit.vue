<script setup>

import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({

    warehouse: Object,

    branches: Array

})

const form = useForm({

    branch_id:
        props.warehouse.branch_id,

    name:
        props.warehouse.name,

    warehouse_type:
        props.warehouse.warehouse_type,

    pic_name:
        props.warehouse.pic_name,

    phone:
        props.warehouse.phone,

    email:
        props.warehouse.email,

    address:
        props.warehouse.address,

    status:
        props.warehouse.status

})

const submit = () => {

    form.put(

        route(

            'warehouses.update',

            props.warehouse.id

        )

    )

}

</script>
<template>

    <Head title="Edit Warehouse" />

    <AuthenticatedLayout>

        <template #header>

            <div
                class="flex items-center justify-between"
            >

                <div>

                    <h2
                        class="text-3xl font-bold text-gray-800"
                    >
                        Edit Warehouse
                    </h2>

                    <p
                        class="mt-1 text-sm text-gray-500"
                    >
                        Edit Warehouse.
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

        <!-- Branch -->

        <div>

            <label
                class="mb-2 block text-sm font-medium"
            >
                Branch *
            </label>

            <select

                v-model="
                    form.branch_id
                "

                class="w-full rounded-xl border-gray-300"

            >

                <option value="">
                    Select Branch
                </option>

                <option

                    v-for="branch in branches"

                    :key="branch.id"

                    :value="branch.id"

                >

                    {{ branch.name }}

                </option>

            </select>

        </div>

        <!-- Warehouse Name -->

        <div>

            <label
                class="mb-2 block text-sm font-medium"
            >
                Warehouse Name *
            </label>

            <input

                v-model="
                    form.name
                "

                type="text"

                class="w-full rounded-xl border-gray-300"

            />

        </div>

        <!-- Warehouse Type -->

        <div>

            <label
                class="mb-2 block text-sm font-medium"
            >
                Warehouse Type
            </label>

            <select

                v-model="
                    form.warehouse_type
                "

                class="w-full rounded-xl border-gray-300"

            >

                <option value="MAIN">
                    Main Warehouse
                </option>

                <option value="TRANSIT">
                    Transit Warehouse
                </option>

                <option value="RETURN">
                    Return Warehouse
                </option>

                <option value="DAMAGED">
                    Damaged Warehouse
                </option>

            </select>

        </div>

        <!-- PIC -->

        <div>

            <label
                class="mb-2 block text-sm font-medium"
            >
                PIC Name
            </label>

            <input

                v-model="
                    form.pic_name
                "

                type="text"

                class="w-full rounded-xl border-gray-300"

            />

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

            />

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

            />

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

        :href="
            route(
                'warehouses.show',
                warehouse.id
            )
        "

        class="rounded-xl border border-gray-300 px-6 py-3 text-gray-700 hover:bg-gray-50"

    >

        Back

    </Link>

    <button

        type="submit"

        class="rounded-xl bg-blue-600 px-6 py-3 text-white hover:bg-blue-700"

    >

        Update Warehouse

    </button>

</div>

</form>

   

</div>

</div>

                   

    </AuthenticatedLayout>

</template>