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

import ButtonGroup from '@/Components/Button/ButtonGroup.vue'

import BaseButton from '@/Components/Button/BaseButton.vue'

import ActionBar from '@/Components/Layout/ActionBar.vue'

import CashBankForm from '@/Pages/Accounting/CashBank/Components/CashBankForm.vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import {
    success,
    error,
} from '@/Utils'

const props = defineProps({

    generatedCode: String,

    companies: Array,

    branches: Array,

    coaAccounts: Array,

     duplicate: {

        type: Object,

        default: null,

    },

})

const form = useForm({

    company_id: props.duplicate?.company_id ?? null,

    branch_id: props.duplicate?.branch_id ?? null,

    code: props.generatedCode,

    name: props.duplicate?.name ?? '',

    type: props.duplicate?.type ?? 'Cash',

    bank_name: props.duplicate?.bank_name ?? '',

    bank_branch: props.duplicate?.bank_branch ?? '',

    account_number: props.duplicate?.account_number ?? '',

    account_holder: props.duplicate?.account_holder ?? '',

    opening_balance: 0,

    current_balance: 0,

    coa_id: props.duplicate?.coa_id ?? null,

    description: props.duplicate?.description ?? '',

    status: props.duplicate?.status ?? true,

    create_another: false,

})



function submit(createAnother = false)
{
    form.create_another = createAnother

    form.post(route('cash-banks.store'), {

        onSuccess: () => {

            success('Cash Bank created successfully.')

            if (createAnother) {

                router.get(
                    route('cash-banks.create'),
                    {},
                    {
                        replace: true,
                        preserveState: false,
                        preserveScroll: false,
                    }
                )

            }

        },

        onError: () => {

            error('Failed to create Cash Bank.')

        }

    })
}
function resetForm()
{

    form.reset()

    form.is_active = true

    form.type = 'Cash'

}
function saveAndNew()
{

    form.post(

        route('cash-banks.store'),

        {

            preserveState: true,

            preserveScroll: true,

            onSuccess: () => {

                success(

                    'Cash Bank created successfully.'

                )

                resetForm();

            },

            onError: () => {

                error(

                    'Failed to create Cash Bank.'

                )

            }

        }

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

            title="Create Cash & Bank"

            subtitle="Create a new cash or bank account."

        />
        <form

            @submit.prevent="submit(false)"

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
                         <BaseButton

                            variant="primary"

                            type="button"

                            :loading="form.processing"

                            @click="submit(true)"

                        >

                            Save & Create New

                        </BaseButton>
                    </ButtonGroup>

                </ActionBar>
        </form>
        
    </div>

</AppLayout>

</template>