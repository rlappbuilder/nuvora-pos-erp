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

    tax: Object,

})

const form = useForm({

    id: props.tax.id,

    code: props.tax.code,

    name: props.tax.name,

    type: props.tax.type,

    rate: props.tax.rate,

    description: props.tax.description,

    is_active: props.tax.is_active,

})

const { update: put } = useCrudForm(form)
function update()
{
    put(
        route('taxes.update', form.id),
        {

            onSuccess: () => {

                success(
                    'Success',
                    'Tax berhasil diperbarui.'
                )

                router.visit(
                    route('taxes.index')
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
        route('taxes.index')
    )
}
const pageTitle = computed(() => 'Edit Tax')
async function previewCode()
{
    try {

        const { data } = await axios.get(
            route('taxes.preview-code')
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