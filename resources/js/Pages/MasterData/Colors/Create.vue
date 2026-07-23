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

    previewCode: String,
    duplicate: Object,
    errors: Object,

})

const form = useForm({

    code: props.previewCode ?? '',

    name: props.duplicate?.name
        ? `${props.duplicate.name} (Copy)`
        : '',
    hex_color: props.duplicate?.hex_color ?? '',
    description: props.duplicate?.description ?? '',

    is_active: props.duplicate?.is_active ?? true,

})

const { save: post } = useCrudForm(form)
function save()
{
    post(route('colors.store'), {

        onSuccess: () => {

            success(
                'Success',
                'Color berhasil disimpan.'
            )

            router.visit(
                route('colors.index')
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

    post(route('colors.store'), {

        onSuccess: () => {

            success(
                'Success',
                'Color berhasil disimpan.'
            )

           form.reset()

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
        route('colors.index')
    )
}
const pageTitle = computed(() =>

    props.duplicate

        ? 'Duplicate Color'

        : 'Create Color'

)
async function previewCode()
{
    try {

        const { data } = await axios.get(
            route('colors.preview-code')
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

        mode="create"

        @submit="save"

        @submitAndNew="saveAndNew"

        @cancel="cancel"

    />

</AppLayout>

</template>