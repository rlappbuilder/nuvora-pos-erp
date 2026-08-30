<script setup>

import PageHeader
    from '@/Components/Layout/PageHeader.vue'

import FiscalYearForm
    from '@/Pages/Accounting/FiscalYear/Components/FiscalYearForm.vue'

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

    fiscalYear: {

        type: Object,

        required: true,

    },

})


/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/

const form = useForm({

    year:
        props.fiscalYear.year,

    start_date:
        props.fiscalYear.start_date,

    end_date:
        props.fiscalYear.end_date,

    status:
        props.fiscalYear.status,

    description:
        props.fiscalYear.description ?? '',

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
            'fiscal-years.show',
            props.fiscalYear.id
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
            'fiscal-years.update',
            props.fiscalYear.id
        ),

        {

            preserveScroll: true,

            onSuccess: () => {

                success(
                    'Fiscal year updated successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to update fiscal year.'
                )

            },

        }

    )
}

</script>


<template>

    <Head
        title="Edit Fiscal Year"
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

                title="Edit Fiscal Year"

                subtitle="Update fiscal year information."

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