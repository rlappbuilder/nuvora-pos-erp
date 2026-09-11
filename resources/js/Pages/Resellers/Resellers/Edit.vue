<script setup>

import { router, useForm } from '@inertiajs/vue3'

import {
    success,
} from '@/Utils'

import { useCrudForm } from '@/Composables/useCrudForm'

import AppLayout from '@/Layouts/AppLayout.vue'

import Form from './Partials/Form.vue'

import {
    onMounted,
    onBeforeUnmount,
    computed,
} from 'vue'


const props = defineProps({

    reseller: Object,

    products: {
        type: Array,
        default: () => [],
    },

})


const form = useForm({

    id: props.reseller.id,

    code: props.reseller.reseller_code,

    name: props.reseller.name,

    contact_person:
        props.reseller.contact_person ?? '',

    phone:
        props.reseller.phone ?? '',

    email:
        props.reseller.email ?? '',

    city:
        props.reseller.city ?? '',

    tax_number:
        props.reseller.tax_number ?? '',

    address:
        props.reseller.address ?? '',

    status:
        props.reseller.status ?? true,

    prices:
        props.reseller.prices?.map(price => ({
            product_id: price.product_id,
            price: price.price,
            product: price.product,
        })) ?? [],

})


const { update: put } = useCrudForm(form)


function update()
{
    put(
        route(
            'resellers.update',
            form.id
        ),
        {

            onSuccess: () => {

                success(
                    'Success',
                    'Reseller berhasil diperbarui.'
                )

                router.visit(
                    route('resellers.index')
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
        route('resellers.index')
    )
}


const pageTitle = computed(() =>
    'Edit Reseller'
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
            :products="props.products"
            mode="edit"
            @submit="update"
            @cancel="cancel"
        />

    </AppLayout>

</template>