<script setup>
import { Head, useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Form from './Form.vue'

const props = defineProps({
    product: Object,
    categories: Array,
    brands: Array,
    units: Array,
})

const form = useForm({
    category_id: props.product.category_id,
    brand_id: props.product.brand_id,
    unit_id: props.product.unit_id,

    code: props.product.code,
    sku: props.product.sku,
    slug: props.product.slug,

    name: props.product.name,
    product_type: props.product.product_type,

    track_stock: props.product.track_stock,
    is_sellable: props.product.is_sellable,
    is_purchasable: props.product.is_purchasable,
    minimum_stock: props.product.minimum_stock,

    description: props.product.description,
    is_active: props.product.is_active,
})

const submit = () => {
    form.put(route('products.update', props.product.id))
}

const cancel = () => {
    router.visit(route('products.index'))
}
</script>

<template>
    <Head title="Edit Product" />

    <AppLayout>
        <Form
            :form="form"
            :categories="categories"
            :brands="brands"
            :units="units"
            :preview-code="product.code"
            mode="edit"
            @submit="submit"
            @cancel="cancel"
        />
    </AppLayout>
</template>