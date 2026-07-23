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

    unit: Object,

})

const form = useForm({

    id: props.unit.id,

    code: props.unit.code,

    name: props.unit.name,

    symbol: props.unit.symbol,

    description: props.unit.description,

    is_active: props.unit.is_active,

})

const { update: put } = useCrudForm(form)
function update()
{
    put(
        route('units.update', form.id),
        {

            onSuccess: () => {

                success(
                    'Success',
                    'Unit berhasil diperbarui.'
                )

                router.visit(
                    route('units.index')
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
        route('units.index')
    )
}
const pageTitle = computed(() => 'Edit Unit')
async function previewCode()
{
    try {

        const { data } = await axios.get(
            route('units.preview-code')
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