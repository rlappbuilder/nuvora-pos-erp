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

    productAttribute: Object,

})

const form = useForm({

    id: props.productAttribute.id,

    code: props.productAttribute.code,

    name: props.productAttribute.name,

    display_name: props.productAttribute.display_name,

     input_type: props.productAttribute.input_type,

    is_required: props.productAttribute.is_required,

    is_variant: props.productAttribute.is_variant,

    sort_order: props.productAttribute.sort_order,

    description: props.productAttribute.description,

    is_active: props.productAttribute.is_active,

})

const { update: put } = useCrudForm(form)
function update()
{
    put(
        route('product-attributes.update', form.id),
        {

            onSuccess: () => {

                success(
                    'Success',
                    'Product attribut Updated.'
                )

                router.visit(
                    route('product-attributes.index')
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
        route('product-attributes.index')
    )
}
const pageTitle = computed(() => 'Edit Product Attribut')

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

    mode="edit"

    @submit="update"

    @cancel="cancel"

/>

</AppLayout>

</template>