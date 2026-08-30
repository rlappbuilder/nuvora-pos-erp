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

import FiscalYearForm
    from '@/Pages/Accounting/FiscalYear/Components/FiscalYearForm.vue'

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

})


/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/

const currentYear =
    new Date().getFullYear()


const form = useForm({

    company_id: null,

    year: currentYear,

    start_date: `${currentYear}-01-01`,

    end_date: `${currentYear}-12-31`,

    status: 'Open',

    description: '',

})


/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/
function submit()
{
    console.log(
        'FISCAL YEAR COMPANY:',
        form.company_id
    )

    console.log(
        'FISCAL YEAR FORM:',
        form.data()
    )

    console.log(
        'FISCAL YEAR COMPANIES:',
        props.companies
    )

    form.post(
        route('fiscal-years.store'),
        {
            preserveScroll: true,

            onSuccess: () => {

                success(
                    'Fiscal year created successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to create fiscal year.'
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
            'fiscal-years.index'
        )

    )
}

</script>


<template>

    <Head
        title="Create Fiscal Year"
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

                icon="📅"

                title="Create Fiscal Year"

                subtitle="Create a new fiscal year for the company."

            />


            <!-- ===================================================== -->
            <!-- Form -->
            <!-- ===================================================== -->

            <form

                @submit.prevent="submit"

                class="space-y-6"

            >

                <FiscalYearForm

                    :form="form"

                    :companies="companies"

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