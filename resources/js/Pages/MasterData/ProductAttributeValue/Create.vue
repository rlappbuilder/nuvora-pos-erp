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

    productAttributeValue: Object,
    productAttributes: Array,
    duplicate: Boolean,
    errors: Object,

})

const form = useForm({
    company_id: props.productAttributeValue?.company_id ?? null,
    product_attribute_id: props.productAttributeValue?.product_attribute_id ?? null,
    code: props.productAttributeValue?.code ?? '',
    value: props.productAttributeValue?.value ?? '',
    display_value: props.productAttributeValue?.display_value ?? '',
    color_code: props.productAttributeValue?.color_code ?? '',
    sort_order: props.productAttributeValue?.sort_order ?? 0,
    description: props.productAttributeValue?.description ?? '',
    is_active: props.productAttributeValue?.is_active ?? true,
})

const { save: post } = useCrudForm(form)
function save()
{
    post(route('product-attribute-values.store'), {

        onSuccess: () => {

            success(
                'Success',
                'Product Attribute Value Saved.'
            )

            router.visit(
                route('product-attribute-values.index')
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

    post(route('product-attribute-values.store'), {

        onSuccess: () => {

            success(
                'Success',
                'Product Attribute Value Saved.'
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
        route('product-attribute-values.index')
    )
}
const pageTitle = computed(() =>

    props.duplicate

        ? 'Duplicate Product Attribute Value'

        : 'Create Product Attribute Value'

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

        :product-attributes="props.productAttributes"

        mode="create"

        @submit="save"

        @submitAndNew="saveAndNew"

        @cancel="cancel"

    />

</AppLayout>

</template>