<script setup>

import { Head, useForm, usePage, router } from '@inertiajs/vue3'

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
        default: 'Consignment Receivable - Create',
    },

    previewNumber: {
        type: String,
        default: '',
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
| Inertia Page
|--------------------------------------------------------------------------
*/

const page = usePage()


/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/

const form = useForm({

    number:
        props.previewNumber,

    branch_id:
        null,

    reseller_id:
        null,

    payment_date:
        '',

    payment_method:
        '',

    payment_account_id:
        null,

    total_amount:
        0,

    remarks:
        '',

    details: [

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

    if (!form.branch_id) {

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


    if (!branch?.company_id) {

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
const submit = () => {

    console.log(
        'RECEIVABLE FORM DATA:',
        JSON.parse(
            JSON.stringify(
                form.data()
            )
        )
    )

    form.post(
        route(
            'consignment-receivables.store'
        ),
        {

            preserveScroll: true,

            onSuccess: () => {

                success(
                    'Success',
                    'Consignment Receivable created successfully.'
                )

            },

            onError: (errors) => {

                console.error(
                    'RECEIVABLE STORE ERROR:',
                    errors
                )

                error(
                    'Failed to create Consignment Receivable.'
                )

            },

            onFinish: () => {

                console.log(
                    'RECEIVABLE STORE FINISHED'
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