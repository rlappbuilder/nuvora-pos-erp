<script setup>
import { router } from '@inertiajs/vue3'

import {

    success,

    resetExcept,

    focusFirst,

} from '@/Utils'

import { useCrudForm } from '@/Composables/useCrudForm'
import axios from 'axios'
import { watch, onMounted, onBeforeUnmount,computed } from 'vue'

import { useForm } from '@inertiajs/vue3'

import AppLayout from '@/Layouts/AppLayout.vue'

import Form from './Form.vue'



const props = defineProps({

    companies: Array,

    branches: Array,

    accountCategories: Array,

    parentAccounts: Array,

    duplicate: Object,

})

const form = useForm({

    company_id: props.duplicate?.company_id ?? '',

    account_category_id: props.duplicate?.account_category_id ?? '',

    parent_id: props.duplicate?.parent_id ?? '',

    code: '',

    name: props.duplicate?.name ?? '',

    normal_balance: props.duplicate?.normal_balance ?? 'Debit',

    opening_balance: props.duplicate?.opening_balance ?? 0,

    is_header: props.duplicate?.is_header ?? false,

    allow_transaction: props.duplicate?.allow_transaction ?? true,

    is_active: props.duplicate?.is_active ?? true,

    description: props.duplicate?.description ?? '',

})

const { save: post } = useCrudForm(form)

function save()
{
    post(route('chart-of-accounts.store'), {

        onSuccess: () => {

            success(
                'Success',
                'Chart of Account berhasil disimpan.'
            )

            router.visit(
                route('chart-of-accounts.index')
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

    post(route('chart-of-accounts.store'), {

        onSuccess: () => {

            success(
                'Success',
                'Chart of Account berhasil disimpan.'
            )

            form.reset()

            form.code = ''

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
        route('chart-of-accounts.index')
    )
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
async function previewCode()
{
    try {

        const { data } = await axios.get(
            route('chart-of-accounts.preview-code'),
            {
                params: {
                    parent_id: form.parent_id,
                },
            }
        )

        form.code = data.code

    } catch (error) {

        form.code = ''

    }
}
watch(
    [
        () => form.account_category_id,
        () => form.parent_id,
    ],
    () => {

        if (!form.account_category_id) {
            form.code = ''
            return
        }

        previewCode()

    },
    {
        immediate: true,
    }
)
const pageTitle = computed(() =>
    props.duplicate
        ? 'Duplicate Chart of Account'
        : 'Create Chart of Account'
)
</script>

<template>

    <AppLayout :title="pageTitle">

        <Form
        :form="form"

        :companies="companies"

        :account-categories="accountCategories"

        :parent-accounts="parentAccounts"

        mode="create"

        @submit="save"

        @submitAndNew="saveAndNew"

        @cancel="cancel"
    />

    </AppLayout>

</template>