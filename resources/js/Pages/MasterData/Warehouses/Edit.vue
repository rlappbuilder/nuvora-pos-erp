<script setup>

import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

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
                        Edit Warehouse
                    </h1>

                    <p
                        class="
                            mt-1
                            text-sm
                            text-gray-500
                        "
                    >
                        Edit warehouse information.
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

                        <!-- Branch -->

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
                                Branch *
                            </label>

                            <select
                                v-model="form.branch_id"
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

                            <div
                                v-if="form.errors.branch_id"
                                class="
                                    mt-1
                                    text-xs
                                    text-red-600
                                "
                            >
                                {{ form.errors.branch_id }}
                            </div>

                        </div>


                        <!-- Warehouse Name -->

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
                                Warehouse Name *
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


                        <!-- Warehouse Type -->

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
                                Warehouse Type
                            </label>

                            <select
                                v-model="form.warehouse_type"
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
                                class="
                                    mb-2
                                    block
                                    text-sm
                                    font-medium
                                    text-gray-700
                                "
                            >
                                PIC Name
                            </label>

                            <input
                                v-model="form.pic_name"
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
                                    'warehouses.show',
                                    props.warehouse.id
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
                                    : 'Update Warehouse'
                            }}
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </AppLayout>

</template>