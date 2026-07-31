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

    categories: Array,

    brands: Array,

    units: Array,

    previewCode: String,

    duplicate: {
        type: Boolean,
        default: false,
    },

    errors: Object,

})

const form = useForm({
    category_id: props.duplicate?.category_id ?? '',
    brand_id: props.duplicate?.brand_id ?? '',
    unit_id: props.duplicate?.unit_id ?? '',

    code: '',
    sku: '',

    name: props.duplicate?.name ?? '',
    slug: '',

    product_type: props.duplicate?.product_type ?? 'PRODUCT',

    track_stock: props.duplicate?.track_stock ?? true,
    is_sellable: props.duplicate?.is_sellable ?? true,
    is_purchasable: props.duplicate?.is_purchasable ?? true,

    minimum_stock: props.duplicate?.minimum_stock ?? 0,

    description: props.duplicate?.description ?? '',

    is_active: props.duplicate?.is_active ?? true,
});

const { save: post } = useCrudForm(form)
function save()
{
    post(route('products.store'), {

        onSuccess: () => {

            success(
                'Success',
                'Product Saved.'
            )

            router.visit(
                route('products.index')
            )

        },

    })
}

function saveAndNew()
{
    form.transform(data => ({

        ...data,

        create_another: true,

    }))

    post(route('products.store'), {

        onSuccess: () => {

            success(
                'Success',
                'Product Saved.'
            )

           form.reset()

            focusFirst()

        },

        onFinish: () => {

            form.transform(data => data)

        },

    })
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
const pageTitle = computed(() =>

    props.duplicate

        ? 'Duplicate Product'

        : 'Create Product'

)

function beforeUnload(event)
{
    if (!form.isDirty) {
        return
    }

    event.preventDefault()
    event.returnValue = ''
}

onMounted(() => {

    window.addEventListener(
        'beforeunload',
        beforeUnload
    )

})

onBeforeUnmount(() => {

    window.removeEventListener(
        'beforeunload',
        beforeUnload
    )

})
</script>
<template>

<AppLayout :title="pageTitle">

  <Form
    :form="form"
    :categories="categories"
    :brands="brands"
    :units="units"
    :preview-code="previewCode"
    :mode="props.duplicate ? 'duplicate' : 'create'"
    @submit="save"
    @submit-and-new="saveAndNew"
    @cancel="cancel"
/>

</AppLayout>

</template>