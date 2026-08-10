<script setup>

import { router } from '@inertiajs/vue3'

import {

    success,

    focusFirst,
    currency,

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
})

const form = useForm({

    id: props.productAttributeValue.id,

    company_id: props.productAttributeValue.company_id,

    product_attribute_id: props.productAttributeValue.product_attribute_id,

    code: props.productAttributeValue.code,

    value: props.productAttributeValue.value,

    display_value: props.productAttributeValue.display_value,

    color_code: props.productAttributeValue.color_code,

    sort_order: props.productAttributeValue.sort_order,

    description: props.productAttributeValue.description,

    is_active: props.productAttributeValue.is_active,

})

const { update: put } = useCrudForm(form)
function update()
{
    put(
        route('product-attribute-values.update', form.id),
        {

            onSuccess: () => {

                success(
                    'Success',
                    'Product attribut Value Updated.'
                )

                router.visit(
                    route('product-attribute-values.index')
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
        route('product-attribute-values.index')
    )
}
const pageTitle = computed(() => 'Edit Product Attribut Value')

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

    mode="edit"

    @submit="update"

    @cancel="cancel"

/>

</AppLayout>

</template>