<script setup>

import AuthenticatedLayout

from '@/Layouts/AuthenticatedLayout.vue'

import {

    Head,

    router,

    useForm

}

from '@inertiajs/vue3'

import PageHeader

from '@/Components/Layout/PageHeader.vue'

import FormSection

from '@/Components/Form/FormSection.vue'

import SearchableSelect

from '@/Components/Form/SearchableSelect.vue'

import FormInput

from '@/Components/Form/FormInput.vue'

import CurrencyInput

from '@/Components/Form/CurrencyInput.vue'

import FormTextarea

from '@/Components/Form/FormTextarea.vue'

import FormField

from '@/Components/Form/FormField.vue'

import ButtonGroup from '@/Components/Button/ButtonGroup.vue'

import BaseButton from '@/Components/Button/BaseButton.vue'

import ActionBar from '@/Components/Layout/ActionBar.vue'

import CashBankForm from '@/Pages/Accounting/CashBank/Components/CashBankForm.vue'

const props = defineProps({

    generatedCode: String,

    companies: Array,

    branches: Array,

    coaAccounts: Array

})

const form = useForm({

    company_id: null,

    branch_id: null,

    code: props.generatedCode,

    name: '',

    type: 'Cash',

    bank_name: '',

    bank_branch: '',

    account_number: '',

    account_holder: '',

    opening_balance: 0,

    current_balance: 0,

    coa_id: null,

    description: '',

    status: true

})

function submit()
{

    form.post(

        route(

            'cash-banks.store'

        )

    )

}
function cancel()

{

    router.visit(

        route(

            'cash-banks.index'

        )

    )

}
</script>
<template>

<Head

    title="Create Cash & Bank"

/>

<AuthenticatedLayout>

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

            title="Create Cash & Bank"

            subtitle="Create a new cash or bank account."

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

                    mode="create"

                />
              <ActionBar

                    bordered

                >

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

                            Save

                        </BaseButton>

                    </ButtonGroup>

                </ActionBar>
        </form>
        
    </div>

</AuthenticatedLayout>

</template>