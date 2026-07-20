<script setup>

import PageHeader from '@/Components/Layout/PageHeader.vue'

import CashBankForm from '@/Pages/Accounting/CashBank/Components/CashBankForm.vue'

import ActionBar from '@/Components/Layout/ActionBar.vue'

import BaseButton from '@/Components/Button/BaseButton.vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import ButtonGroup from '@/Components/Button/ButtonGroup.vue'

import {

    Head,

    router,

    useForm

}

from '@inertiajs/vue3'
import {
    success,
    error,
} from '@/Utils'
const props = defineProps({

    cashBank: {

        type: Object,

        required: true

    },

    companies: {

        type: Array,

        default: () => []

    },

    branches: {

        type: Array,

        default: () => []

    },

    coaAccounts: {

        type: Array,

        default: () => []

    }

})

const form = useForm({

    company_id: props.cashBank.company_id,

    branch_id: props.cashBank.branch_id,

    code: props.cashBank.code,

    name: props.cashBank.name,

    type: props.cashBank.type,

    status: props.cashBank.status,

    bank_name: props.cashBank.bank_name,

    bank_branch: props.cashBank.bank_branch,

    account_number: props.cashBank.account_number,

    account_holder: props.cashBank.account_holder,

    coa_id: props.cashBank.coa_id,

    opening_balance: props.cashBank.opening_balance,

    description: props.cashBank.description,

})

function cancel()

{

    router.visit(

        route(

            'cash-banks.index'

        )

    )

}
function submit()
{
    

    form.put(
        route(
            'cash-banks.update',
            props.cashBank.id
        ),
        {
            preserveScroll: true,

            onSuccess: () => {
               
            },

            onError: (errors) => {
               
            },

            onFinish: () => {
              
            }
        }
    )
}
</script>
<template>

<Head

    title="Edit Cash & Bank"

/>

<AppLayout>

    <div

        class="

            mx-auto

            max-w-7xl

            space-y-6

            px-6

            py-6

        "

    >

        <PageHeader

            icon="🏦"

            title="Edit Cash & Bank"

            subtitle="Update existing cash or bank account."

        />

        <form

            @submit.prevent="submit"

            class="space-y-6"

        >

            <CashBankForm

                :form="form"

                :companies="props.companies"

                :branches="props.branches"

                :coa-accounts="props.coaAccounts"

                mode="edit"

            />

            <ActionBar bordered>

                <ButtonGroup>

                    <BaseButton

                        variant="secondary"

                        type="button"

                        @click="cancel"

                    >

                        Cancel

                    </BaseButton>

                    <BaseButton

                        type="submit"

                        :loading="form.processing"

                    >

                        Update

                    </BaseButton>

                </ButtonGroup>

            </ActionBar>

        </form>

    </div>

</AppLayout>

</template>