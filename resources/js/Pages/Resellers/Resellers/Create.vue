<script setup>

import { router, useForm } from '@inertiajs/vue3'

import {success,focusFirst,} from '@/Utils'

import { useCrudForm } from '@/Composables/useCrudForm'

import AppLayout from '@/Layouts/AppLayout.vue'

import Form from './Partials/Form.vue'

import axios from 'axios'

import {
    onMounted,
    onBeforeUnmount,
    computed,
} from 'vue'


const props = defineProps({

    previewCode: String,

    duplicate: Object,

    errors: Object,

    products: {
        type: Array,
        default: () => [],
    },

})


const form = useForm({

    code: props.previewCode ?? '',

    name: props.duplicate?.name
        ? `${props.duplicate.name} (Copy)`
        : '',

    contact_person:
        props.duplicate?.contact_person ?? '',

    phone:
        props.duplicate?.phone ?? '',

    email:
        props.duplicate?.email ?? '',

    city:
        props.duplicate?.city ?? '',

    tax_number:
        props.duplicate?.tax_number ?? '',

    address:
        props.duplicate?.address ?? '',

    status:
        props.duplicate?.status ?? true,

    prices:
        props.duplicate?.prices?.map(price => ({
            product_id: price.product_id,
            price: price.price,
            product: price.product,
        })) ?? [],

})


const { save: post } = useCrudForm(form)


function save()
{
    post(route('resellers.store'), {

        onSuccess: () => {

            success(
                'Success',
                'Reseller berhasil disimpan.'
            )

            router.visit(
                route('resellers.index')
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

    post(route('resellers.store'), {

        onSuccess: () => {

            success(
                'Success',
                'Reseller berhasil disimpan.'
            )

            form.reset()

            form.status = true

            form.prices = []

            previewCode()

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
        route('resellers.index')
    )
}


const pageTitle = computed(() =>

    props.duplicate

        ? 'Duplicate Reseller'

        : 'Create Reseller'

)


async function previewCode()
{
    try {

        const { data } = await axios.get(
            route('resellers.preview-code')
        )

        form.code = data.code

    } catch (error) {

        form.code = ''

    }
}


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

    previewCode()

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
            :products="props.products"
            mode="create"

            @submit="save"

            @submitAndNew="saveAndNew"

            @cancel="cancel"

        />

    </AppLayout>

</template>