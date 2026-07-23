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

    size: Object,

})

const form = useForm({

    id: props.size.id,

    code: props.size.code,

    name: props.size.name,

    sort_order: props.size.sort_order,

    description: props.size.description,

    is_active: props.size.is_active,

})

const { update: put } = useCrudForm(form)
function update()
{
    put(
        route('sizes.update', form.id),
        {

            onSuccess: () => {

                success(
                    'Success',
                    'Size berhasil diperbarui.'
                )

                router.visit(
                    route('sizes.index')
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
        route('sizes.index')
    )
}
const pageTitle = computed(() => 'Edit Size')
async function previewCode()
{
    try {

        const { data } = await axios.get(
            route('sizes.preview-code')
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