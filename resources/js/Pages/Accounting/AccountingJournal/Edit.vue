<script setup>

import PageHeader
    from '@/Components/Layout/PageHeader.vue'

import AccountingJournalForm
    from '@/Pages/Accounting/AccountingJournal/AccountingJournalForm.vue'

import ActionBar
    from '@/Components/Layout/ActionBar.vue'

import BaseButton
    from '@/Components/Button/BaseButton.vue'

import ButtonGroup
    from '@/Components/Button/ButtonGroup.vue'

import AppLayout
    from '@/Layouts/AppLayout.vue'

import {
    Head,
    router,
    useForm,
} from '@inertiajs/vue3'

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

    journal: {

        type: Object,

        required: true,

    },

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

    company_id:
        props.journal.company_id,

    code:
        props.journal.code,

    name:
        props.journal.name,

    type:
        props.journal.type,

    is_active:
        Boolean(
            props.journal.is_active
        ),

    accounts:
        props.journal.accounts?.map(
            account => ({

                account_id:
                    account.account_id,

                role:
                    account.role,

                is_default:
                    Boolean(
                        account.is_default
                    ),

            })
        ) ?? [],

    description:
        props.journal.description
        ?? '',

})


/*
|--------------------------------------------------------------------------
| Cancel
|--------------------------------------------------------------------------
*/

function cancel()
{
    router.visit(

        route(
            'accounting-journals.show',
            props.journal.id
        )

    )
}


/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/

function submit()
{
    form.put(

        route(
            'accounting-journals.update',
            props.journal.id
        ),

        {

            preserveScroll: true,

            onSuccess: () => {

                success(
                    'Accounting journal updated successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to update accounting journal.'
                )

            },

        }

    )
}

</script>


<template>

    <Head
        title="Edit Accounting Journal"
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

                title="Edit Accounting Journal"

                subtitle="Update accounting journal information."

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

                    mode="edit"

                />


                <!-- ================================================= -->
                <!-- Actions -->
                <!-- ================================================= -->

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

                            Update

                        </BaseButton>

                    </ButtonGroup>

                </ActionBar>

            </form>

        </div>

    </AppLayout>

</template>