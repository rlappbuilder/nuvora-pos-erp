<script setup>

import {
    Head,
    useForm,
    router,
} from '@inertiajs/vue3'

import AppLayout from '@/Layouts/AppLayout.vue'

import ReceivableForm from './ReceivableForm.vue'
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

    title: {
        type: String,
        default: 'Edit Consignment Receivable - Update',
    },

    consignmentReceivable: {
        type: Object,
        required: true,
    },

    branches: {
        type: Array,
        default: () => [],
    },

    resellers: {
        type: Array,
        default: () => [],
    },

    paymentAccounts: {
        type: Array,
        default: () => [],
    },

    settlements: {
        type: Array,
        default: () => [],
    },

})


/*
|--------------------------------------------------------------------------
| Existing Receivable
|--------------------------------------------------------------------------
*/

const receivable =
    props.consignmentReceivable


/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/

const form = useForm({

    number:
        receivable.number
        ?? '',

    branch_id:
        receivable.branch_id
        ?? null,

    reseller_id:
        receivable.reseller_id
        ?? null,

    payment_date:
        receivable.payment_date
        ?? '',

    payment_method:
        receivable.payment_method
        ?? '',

    payment_account_id:
        receivable.payment_account_id
        ?? null,

    total_amount:
        Number(
            receivable.total_amount
            ?? 0
        ),

    remarks:
        receivable.remarks
        ?? '',

    details:
        receivable.details?.length

            ? receivable.details.map(
                detail => ({

                    settlement_header_id:
                        detail.settlement_header_id
                        ?? null,

                    settlement_amount:
                        Number(
                            detail.settlement_amount
                            ?? 0
                        ),

                    previous_paid_amount:
                        Number(
                            detail.previous_paid_amount
                            ?? 0
                        ),

                    previous_outstanding_amount:
                        Number(
                            detail.previous_outstanding_amount
                            ?? 0
                        ),

                    payment_amount:
                        Number(
                            detail.payment_amount
                            ?? 0
                        ),

                    remarks:
                        detail.remarks
                        ?? '',

                })
            )

            : [

                {
                    settlement_header_id:
                        null,

                    settlement_amount:
                        0,

                    previous_paid_amount:
                        0,

                    previous_outstanding_amount:
                        0,

                    payment_amount:
                        0,

                    remarks:
                        '',
                },

            ],

})


/*
|--------------------------------------------------------------------------
| Filtered Resellers
|--------------------------------------------------------------------------
|
| Reseller belongs to Company.
| Branch determines the Company context.
|--------------------------------------------------------------------------
*/

const filteredResellers = () => {

    if (
        !form.branch_id
    ) {

        return props.resellers

    }


    const branch =
        props.branches.find(

            item =>

                Number(
                    item.id
                ) ===
                Number(
                    form.branch_id
                )

        )


    if (
        !branch?.company_id
    ) {

        return props.resellers

    }


    return props.resellers.filter(

        reseller =>

            Number(
                reseller.company_id
            ) ===
            Number(
                branch.company_id
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
            'consignment-receivables.update',
            receivable.id
        ),
        {
            preserveScroll: true,

            onSuccess: () => {

                success(
                    'Success',
                    'Consignment Receivable updated successfully.'
                )

            },

            onError: (errors) => {

                console.error(
                    'UPDATE CONSIGNMENT RECEIVABLE ERRORS:',
                    errors
                )

                error(
                    'Failed to update Consignment Receivable.'
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

const cancel = () => {

    router.get(

        route(
            'consignment-receivables.index'
        )

    )

}

</script>


<template>

    <AppLayout>

        <Head
            :title="
                title
            "
        />


        <div
            class="
                min-h-full
                bg-gray-50
                px-4
                py-6
                sm:px-6
                lg:px-8
            "
        >

            <div
                class="
                    mx-auto
                    max-w-7xl
                "
            >

                <ReceivableForm

                    :form="
                        form
                    "

                    :branches="
                        branches
                    "

                    :resellers="
                        filteredResellers()
                    "

                    :payment-accounts="
                        paymentAccounts
                    "

                    :settlements="
                        settlements
                    "

                    @submit="
                        submit
                    "

                    @cancel="
                        cancel
                    "

                />

            </div>

        </div>

    </AppLayout>

</template>