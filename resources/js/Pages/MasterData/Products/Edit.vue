<script setup>

import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
const props = defineProps({

    product: Object,

    categories: Array,

    brands: Array

})
const form = useForm({

    category_id:
        props.product.category_id,

    brand_id:
        props.product.brand_id,

    name:
        props.product.name,

    barcode:
        props.product.barcode,

    product_type:
        props.product.product_type,

    unit:
        props.product.unit,

    cost_price:
        props.product.cost_price,

    selling_price:
        props.product.selling_price,

    minimum_stock:
        props.product.minimum_stock,

    description:
        props.product.description,

    status:
        props.product.status

})

const submit = () => {

    form.put(

        route(
    'products.update',
    props.product.id
)
    )

}

</script>
<template>

    <Head title="Edit Products" />

    <AuthenticatedLayout>

        <template #header>

            <div
                class="flex items-center justify-between"
            >

                <div>

                    <h2
                        class="text-3xl font-bold text-gray-800"
                    >
                        Edit Products
                    </h2>

                    <p
                        class="mt-1 text-sm text-gray-500"
                    >
                        Update product information
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

    <!-- Category -->

<div>

    <label class="mb-2 block text-sm font-medium">
        Category *
    </label>

    <select
        v-model="form.category_id"
        class="w-full rounded-xl border-gray-300"
    >

        <option value="">
            Select Category
        </option>

        <option
            v-for="category in categories"
            :key="category.id"
            :value="category.id"
        >

            {{ category.name }}

        </option>

    </select>

</div>

<!-- Brand -->

<div>

    <label class="mb-2 block text-sm font-medium">
        Brand
    </label>

    <select
        v-model="form.brand_id"
        class="w-full rounded-xl border-gray-300"
    >

        <option value="">
            Select Brand
        </option>

        <option
            v-for="brand in brands"
            :key="brand.id"
            :value="brand.id"
        >

            {{ brand.name }}

        </option>

    </select>

</div>

<!-- Product Name -->

<div>

    <label class="mb-2 block text-sm font-medium">
        Product Name *
    </label>

    <input
        v-model="form.name"
        type="text"
        class="w-full rounded-xl border-gray-300"
    />

</div>

<!-- Barcode -->

<div>

    <label class="mb-2 block text-sm font-medium">
        Barcode
    </label>

    <input
        v-model="form.barcode"
        type="text"
        class="w-full rounded-xl border-gray-300"
    />
<div>

    <label
        class="mb-2 block text-sm font-medium"
    >
        Product Type
    </label>

    <select
        v-model="form.product_type"
        class="w-full rounded-xl border-gray-300"
    >

        <option value="PRODUCT">
            Product
        </option>

        <option value="SERVICE">
            Service
        </option>

    </select>

</div>
<div>

    <label
        class="mb-2 block text-sm font-medium"
    >
        Unit
    </label>

    <select
        v-model="form.unit"
        class="w-full rounded-xl border-gray-300"
    >

        <option value="PCS">PCS</option>
        <option value="BOX">BOX</option>
        <option value="PACK">PACK</option>
        <option value="KG">KG</option>
        <option value="LITER">LITER</option>
        <option value="METER">METER</option>

    </select>

</div>

<div>

    <label
        class="mb-2 block text-sm font-medium"
    >
        Cost Price
    </label>

    <input
        v-model="form.cost_price"
        type="number"
        class="w-full rounded-xl border-gray-300"
    />

</div>
<div>

    <label
        class="mb-2 block text-sm font-medium"
    >
        Selling Price
    </label>

    <input
        v-model="form.selling_price"
        type="number"
        class="w-full rounded-xl border-gray-300"
    />

</div>
<div>

    <label
        class="mb-2 block text-sm font-medium"
    >
        Minimum Stock
    </label>

    <input
        v-model="form.minimum_stock"
        type="number"
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
            Description
        </label>

        <textarea

            v-model="
                    form.description
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
                'products.index'
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

        Edit Products

    </button>

</div>

</form>

   

</div>

</div>

                   

    </AuthenticatedLayout>

</template>