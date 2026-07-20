<script setup>
import { computed } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { usePage } from '@inertiajs/vue3'
import BaseToast from '@/Components/UI/BaseToast.vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const page = usePage()

const props = defineProps({

    products: Array,
    warehouses: Array
})

const form = useForm({

    warehouse_id: '',

    product_id: '',

    qty: 0,

    unit_cost: 0,

    remarks: ''

})
const selectedProduct = computed(() => {

    return props.products.find(

        product =>

            product.id == form.product_id

    )

})
const submit = () => {

    form.post(

        route(
            'opening-stock.store'
        ),

        {

            onSuccess: () => {

                form.reset()
                
            }

        }

    )

}

</script>
<template>

    <Head title="Opening Stock" />

    <AppLayout>

        <template #header>

            <div
                class="flex items-center justify-between"
            >

                <div>

                    <h2
                        class="text-3xl font-bold text-gray-800"
                    >
                        Opening Stock
                    </h2>

                    <p
                        class="mt-1 text-sm text-gray-500"
                    >
                        Set initial stock balance.
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

<!-- Warehouse -->

<div>

    <label
        class="mb-2 block text-sm font-medium"
    >
        Warehouse *
    </label>

    <select
        v-model="form.warehouse_id"
        class="w-full rounded-xl border-gray-300"
    >

        <option value="">
            Select Warehouse
        </option>

        <option
            v-for="warehouse in warehouses"
            :key="warehouse.id"
            :value="warehouse.id"
        >

            {{ warehouse.name }}

        </option>

    </select>

</div>


<div>

    <label
        class="mb-2 block text-sm font-medium"
    >
        Product *
    </label>

    <select
        v-model="form.product_id"
        class="w-full rounded-xl border-gray-300"
    >

        <option value="">
            Select Product
        </option>

        <option
            v-for="product in products"
            :key="product.id"
            :value="product.id"
        >

            {{ product.name }}

        </option>

    </select>

</div>

<div
    v-if="selectedProduct"
    class="rounded-2xl border border-blue-100 bg-blue-50 p-5"
>

    <div class="grid gap-4 md:grid-cols-2">

        <div>

            <div class="text-sm text-gray-500">
                SKU
            </div>

            <div class="font-medium">
               {{ selectedProduct?.sku || '-' }}
            </div>

        </div>

        <div>

            <div class="text-sm text-gray-500">
                Category
            </div>

            <div class="font-medium">
                {{ selectedProduct.category?.name }}
            </div>

        </div>

        <div>

            <div class="text-sm text-gray-500">
                Brand
            </div>

            <div class="font-medium">
                {{ selectedProduct.brand?.name }}
            </div>

        </div>

        <div>

            <div class="text-sm text-gray-500">
                Unit
            </div>

            <div class="font-medium">
              {{ selectedProduct?.unit || '-' }}
            </div>

        </div>

    </div>

</div>

<!-- Qty -->

<div>

    <label
        class="mb-2 block text-sm font-medium"
    >
        Quantity *
    </label>

    <input
        v-model="form.qty"
        type="number"
        class="w-full rounded-xl border-gray-300"
    />

</div>

<!-- Unit Cost -->

<div>

    <label
        class="mb-2 block text-sm font-medium"
    >
        Unit Cost *
    </label>

    <input
        v-model="form.unit_cost"
        type="number"
        class="w-full rounded-xl border-gray-300"
    />
<div>

    <label
        class="mb-2 block text-sm font-medium"
    >
        Inventory Value
    </label>

    <input

        :value="

            (
                Number(form.qty || 0)

                *

                Number(form.unit_cost || 0)

            ).toLocaleString('id-ID')

        "

        readonly

        class="w-full rounded-xl border-gray-300 bg-gray-100"

    />

</div>
</div>
    <!-- Address -->

    <div class="mt-6">

        <label
            class="mb-2 block text-sm font-medium"
        >
            Remarks
        </label>

        <textarea

            v-model="
                    form.remarks
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
            'dashboard'
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

        Save opening Stock

    </button>

</div>

</form>

   

</div>

</div>
<BaseToast

    :show="
        !!page.props.flash.success
    "

    :message="
        page.props.flash.success
    "

/>      

    </AppLayout>

</template>