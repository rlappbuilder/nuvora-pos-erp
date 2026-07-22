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

    category: Object,

})

const form = useForm({

    id: props.category.id,

    code: props.category.code,

    name: props.category.name,

    description: props.category.description,

    is_active: props.category.is_active,

})

const { update: put } = useCrudForm(form)
function update()
{
    put(
        route('categories.update', form.id),
        {

            onSuccess: () => {

                success(
                    'Success',
                    'Category berhasil diperbarui.'
                )

                router.visit(
                    route('categories.index')
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
        route('categories.index')
    )
}
const pageTitle = computed(() => 'Edit Category')
async function previewCode()
{
    try {

        const { data } = await axios.get(
            route('categories.preview-code')
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