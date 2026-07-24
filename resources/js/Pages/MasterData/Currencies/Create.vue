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
   
    duplicate: Object,
    errors: Object,

})

const form = useForm({

    code: props.duplicate?.code ?? '',

    name: props.duplicate?.name
        ? `${props.duplicate.name} (Copy)`
        : '',

    symbol: props.duplicate?.symbol ?? '',

    decimal_places: props.duplicate?.decimal_places ?? 2,

    exchange_rate: props.duplicate?.exchange_rate ?? 1,

    is_base_currency: props.duplicate?.is_base_currency ?? false,

    description: props.duplicate?.description ?? '',

    is_active: props.duplicate?.is_active ?? true,

})
const { save: post } = useCrudForm(form)
function save()
{
    post(route('currencies.store'), {

        onSuccess: () => {

            success(
                'Success',
                'Currency berhasil disimpan.'
            )

            router.visit(
                route('currencies.index')
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

    post(route('currencies.store'), {

        onSuccess: () => {

            success(
                'Success',
                'Currency berhasil disimpan.'
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
        route('currencies.index')
    )
}
const pageTitle = computed(() =>

    props.duplicate

        ? 'Duplicate Currency'

        : 'Create Currency'

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