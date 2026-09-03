<script setup>

import {
    Head,
    router,
    useForm,
} from '@inertiajs/vue3'

import PageHeader
    from '@/Components/Layout/PageHeader.vue'

import ButtonGroup
    from '@/Components/Button/ButtonGroup.vue'

import BaseButton
    from '@/Components/Button/BaseButton.vue'

import ActionBar
    from '@/Components/Layout/ActionBar.vue'

import AccountingJournalForm
    from '@/Pages/Accounting/AccountingJournal/AccountingJournalForm.vue'

import AppLayout
    from '@/Layouts/AppLayout.vue'

import {
    success,
    error,
} from '@/Utils'


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    companies: {

        type: Array,

        default: () => [],

    },

    coaAccounts: {

        type: Array,

        default: () => [],

    },

})


/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/

const form = useForm({

    company_id: null,

    code: '',

    name: '',

    type: 'General',

    is_active: true,

    accounts: [],

    description: '',

})


/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/

function submit()
{
    form.post(

        route(
            'accounting-journals.store'
        ),

        {

            preserveScroll: true,

            onSuccess: () => {

                success(
                    'Accounting journal created successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to create accounting journal.'
                )

            },

        }

    )
}


/*
|--------------------------------------------------------------------------
| Cancel
|--------------------------------------------------------------------------
*/

function cancel()
{
    router.visit(

        route(
            'accounting-journals.index'
        )

    )
}

</script>


<template>

    <Head
        title="Create Accounting Journal"
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

            <!-- ===================================================== -->
            <!-- Page Header -->
            <!-- ===================================================== -->

            <PageHeader

                icon="📒"

                title="Create Accounting Journal"

                subtitle="Create a new accounting journal for the company."

            />


            <!-- ===================================================== -->
            <!-- Form -->
            <!-- ===================================================== -->

            <form

                @submit.prevent="submit"

                class="space-y-6"

            >

                <AccountingJournalForm

                    :form="form"

                    :companies="companies"

                    :coa-accounts="coaAccounts"

                    mode="create"

                />


                <!-- ================================================= -->
                <!-- Actions -->
                <!-- ================================================= -->

                <ActionBar
                    bordered
                >

                    <ButtonGroup>

                        <BaseButton

                            type="button"

                            variant="secondary"

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

    </AppLayout>

</template>