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

    currency: Object,

})

const form = useForm({

    id: props.currency.id,

    code: props.currency.code,

    name: props.currency.name,

    symbol: props.currency.symbol,

    decimal_places: props.currency.decimal_places,

    exchange_rate: props.currency.exchange_rate,

    is_base_currency: props.currency.is_base_currency,

    description: props.currency.description,

    is_active: props.currency.is_active,

})

const { update: put } = useCrudForm(form)
function update()
{
    put(
        route('currencies.update', form.id),
        {

            onSuccess: () => {

                success(
                    'Success',
                    'Currency berhasil diperbarui.'
                )

                router.visit(
                    route('currencies.index')
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
        route('currencies.index')
    )
}
const pageTitle = computed(() => 'Edit Currency')

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