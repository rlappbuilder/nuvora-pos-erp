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
   
    productAttribute: Object,
    errors: Object,

})

const form = useForm({
    code: props.productAttribute?.code ?? '',
    name: props.productAttribute?.name ?? '',
    display_name: props.productAttribute?.display_name ?? '',
    description: props.productAttribute?.description ?? '',
    sort_order: props.productAttribute?.sort_order ?? 0,
    input_type: props.productAttribute?.input_type ?? 'Select',
    is_variant: props.productAttribute?.is_variant ?? false,
    is_required: props.productAttribute?.is_required ?? false,
    is_active: props.productAttribute?.is_active ?? true,
})

const { save: post } = useCrudForm(form)
function save()
{
    post(route('product-attributes.store'), {

        onSuccess: () => {

            success(
                'Success',
                'Product Attribute Saved.'
            )

            router.visit(
                route('product-attributes.index')
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

    post(route('product-attributes.store'), {

        onSuccess: () => {

            success(
                'Success',
                'Product Attribute Saved.'
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
        route('product-attributes.index')
    )
}
const pageTitle = computed(() =>

    props.duplicate

        ? 'Duplicate Product Attribute'

        : 'Create Product Attribute'

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

        mode="create"

        @submit="save"

        @submitAndNew="saveAndNew"

        @cancel="cancel"

    />

</AppLayout>

</template>