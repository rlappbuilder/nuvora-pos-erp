<script setup>

import { Head, Link } from '@inertiajs/vue3'
import { usePage } from '@inertiajs/vue3'

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import BaseToast from '@/Components/UI/BaseToast.vue'

const page = usePage()

const props = defineProps({

    product: Object

})

</script>

<template>

    <Head title="Product Detail" />

    <AuthenticatedLayout>

        <template #header>

            <div
                class="flex items-center justify-between"
            >

                <div>

                    <h2
                        class="text-3xl font-bold text-gray-800"
                    >
                       Product Detail
                    </h2>

                    <p
                        class="mt-1 text-sm text-gray-500"
                    >
                       Product information overview.
                    </p>

                </div>

                <div
    class="flex gap-3"
>

    <Link
        :href="route('products.index')"
    >
        Back
    </Link>

    <Link
        :href="
            route(
                'products.edit',
                product.id
            )
        "
    >
        Edit
    </Link>

</div>

            </div>

        </template>

        <div
            class="mx-auto max-w-7xl space-y-4"
        >

            <!-- Header Card -->
<div
    class="flex items-center gap-5"
>

    <div
        class="flex h-20 w-20 items-center justify-center rounded-2xl bg-blue-100 text-4xl"
    >
        📦
    </div>

    <div>

        <div
            class="mb-2"
        >

            <span
                class="rounded-full bg-green-100 px-3 py-1 text-sm text-green-700"
            >
                {{ product.product_type }}
            </span>

        </div>

        <h1
            class="text-3xl font-bold text-gray-800"
        >
            {{ product.name }}
        </h1>

        <p
            class="mt-1 text-gray-500"
        >
            {{ product.sku }}
        </p>

    </div>

</div>

           

            <!-- Contact Information -->
<div class="space-y-6">

    <div class="grid grid-cols-2 gap-6">

        <div>

            <div class="text-sm text-gray-500">
                SKU
            </div>

            <div class="font-medium">
                {{ product.sku }}
            </div>

        </div>

        <div>

            <div class="text-sm text-gray-500">
                Barcode
            </div>

            <div class="font-medium">
                {{ product.barcode || '-' }}
            </div>

        </div>

        <div>

            <div class="text-sm text-gray-500">
                Category
            </div>

            <div class="font-medium">
                {{ product.category?.name || '-' }}
            </div>

        </div>

        <div>

            <div class="text-sm text-gray-500">
                Brand
            </div>

            <div class="font-medium">
                {{ product.brand?.name || '-' }}
            </div>

        </div>

        <div>

            <div class="text-sm text-gray-500">
                Product Type
            </div>

            <div class="font-medium">
                {{ product.product_type }}
            </div>

        </div>

        <div>

            <div class="text-sm text-gray-500">
                Unit
            </div>

            <div class="font-medium">
                {{ product.unit }}
            </div>

        </div>

        <div>

            <div class="text-sm text-gray-500">
                Cost Price
            </div>

            <div class="font-medium">
                Rp {{ Number(product.cost_price).toLocaleString() }}
            </div>

        </div>

        <div>

            <div class="text-sm text-gray-500">
                Selling Price
            </div>

            <div class="font-medium">
                Rp {{ Number(product.selling_price).toLocaleString() }}
            </div>

        </div>

        <div>

            <div class="text-sm text-gray-500">
                Minimum Stock
            </div>

            <div class="font-medium">
                {{ product.minimum_stock }}
            </div>

        </div>

        <div>

            <div class="text-sm text-gray-500">
                Status
            </div>

            <div class="font-medium">
                {{ product.status ? 'Active' : 'Inactive' }}
            </div>

        </div>

    </div>

</div>

            <!-- Address -->

            <div
                class="rounded-3xl bg-white p-8 shadow-sm"
            >

                <h3
                    class="mb-6 text-lg font-semibold"
                >
                    Description
                </h3>

                <p
                    class="text-gray-700"
                >
                    {{ product.description || '-' }}
                </p>

            </div>

        </div>

        <BaseToast

            :show="page.props.flash.success"

            :message="page.props.flash.success"

        />

    </AuthenticatedLayout>

</template>