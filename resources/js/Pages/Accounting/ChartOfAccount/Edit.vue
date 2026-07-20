<script setup>
import { router } from '@inertiajs/vue3'

import {

    success,

    resetExcept,

    focusFirst,

} from '@/Utils'

import { useCrudForm } from '@/Composables/useCrudForm'
import axios from 'axios'
import { watch, onMounted, onBeforeUnmount } from 'vue'

import { useForm } from '@inertiajs/vue3'

import AppLayout from '@/Layouts/AppLayout.vue'

import Form from './Form.vue'

const props = defineProps({

    chartOfAccount: Object,

    companies: Array,

    branches: Array,

    accountCategories: Array,

    parentAccounts: Array,

})
const form = useForm({

    company_id: props.chartOfAccount.company_id,

    account_category_id: props.chartOfAccount.account_category_id,

    parent_id: props.chartOfAccount.parent_id,

    code: props.chartOfAccount.code,

    name: props.chartOfAccount.name,

    normal_balance: props.chartOfAccount.normal_balance,

    opening_balance: props.chartOfAccount.opening_balance,

    is_header: props.chartOfAccount.is_header,

    allow_transaction: props.chartOfAccount.allow_transaction,

    is_active: props.chartOfAccount.is_active,

    description: props.chartOfAccount.description,

})
const {

    update,

} = useCrudForm(form)

function updateData()
{
    update(
        route(
            'chart-of-accounts.update',
            props.chartOfAccount.id
        ),
        {

            onSuccess: () => {

                success(
                    'Success',
                    'Chart of Account berhasil diperbarui.'
                )

                router.visit(
                    route('chart-of-accounts.index')
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

    }
)
</script>

<template>

    <AppLayout title="Edit Chart of Account">

        <Form
        :form="form"

        :companies="companies"

        :account-categories="accountCategories"

        :parent-accounts="parentAccounts"

        mode="edit"

        @submit="updateData"

        @cancel="cancel"
    />

    </AppLayout>

</template>