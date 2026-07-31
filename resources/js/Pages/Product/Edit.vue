<script setup>

import { router } from '@inertiajs/vue3'

import {

    success,

    focusFirst,

} from '@/Utils'

import { useCrudForm } from '@/Composables/useCrudForm'

import { useForm } from '@inertiajs/vue3'

import AppLayout from '@/Layouts/AppLayout.vue'

import Form from './Partials/Form.vue'
import axios from 'axios'
import { watch, onMounted, onBeforeUnmount, computed } from 'vue'
const props = defineProps({

    product: Object,

    categories: Array,

    brands: Array,

    units: Array,

    errors: Object,

})
const form = useForm({

    category_id: props.product.category_id,

    brand_id: props.product.brand_id,

    unit_id: props.product.unit_id,

    code: props.product.code,

    sku: props.product.sku,

    name: props.product.name,

    slug: props.product.slug,

    product_type: props.product.product_type,

    track_stock: props.product.track_stock,

    is_sellable: props.product.is_sellable,

    is_purchasable: props.product.is_purchasable,

    minimum_stock: props.product.minimum_stock,

    description: props.product.description,

    is_active: props.product.is_active,

})
const { save: post } = useCrudForm(form)
function save()
{
    form.put(
        route('products.update', props.product.id),
        {
            onSuccess: () => {

                success(
                    'Success',
                    'Product Updated.'
                )

                router.visit(
                    route('products.index')
                )

            },
        }
    )
}

function cancel()
{
    if (

        form.isDirty &&

        !confirm(
            'Perubahan belum disimpan. Yakin ingin keluar?'
        )

    ) {

        return

    }

    router.visit(
        route('products.index')
    )
}

</script>
<template>

<AppLayout :title="pageTitle">
<PageHeader
                :breadcrumb="[
                    'Master Data',
                    'Product',
                    'Edit Product'
                ]"
                icon="📂"
                title="Product"
                subtitle="Manage your products."
            />

  <Form
    :form="form"
    :categories="categories"
    :brands="brands"
    :units="units"
    :preview-code="product.code"
    mode="edit"
    @submit="save"
    @cancel="cancel"
/>

</AppLayout>

</template>