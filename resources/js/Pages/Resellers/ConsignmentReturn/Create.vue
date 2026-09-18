<script setup>

import { Head, useForm, usePage, router } from '@inertiajs/vue3'

import AppLayout from '@/Layouts/AppLayout.vue'

import ReturnForm from './ReturnForm.vue'

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
        default: 'Consignment Return - Create',
    },

    previewNumber: {
        type: String,
        default: '',
    },

    branches: {
        type: Array,
        default: () => [],
    },

    warehouses: {
        type: Array,
        default: () => [],
    },

    resellers: {
        type: Array,
        default: () => [],
    },

    consignmentStocks: {
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

    return_number:
        props.previewNumber,

    branch_id:
        null,

    warehouse_id:
        null,

    reseller_id:
        null,

    settlement_header_id:
        null,

    return_date:
        '',

    remarks:
        '',

    details: [

        {
            product_variant_id:
                null,

            unit_id:
                null,

            returned_qty:
                0,

            unit_cost:
                0,

            total_cost:
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


/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/

const submit = () => {

    console.log(
        'CONSINGMENT RETURN FORM DATA:',
        JSON.parse(
            JSON.stringify(
                form.data()
            )
        )
    )


    form.post(

        route(
            'consignment-returns.store'
        ),

        {

            preserveScroll: true,

            onSuccess: () => {

                success(
                    'Success',
                    'Consignment Return created successfully.'
                )

            },

            onError: (errors) => {

                console.error(
                    'CONSIGNMENT RETURN STORE ERROR:',
                    errors
                )

                error(
                    'Failed to create Consignment Return.'
                )

            },

            onFinish: () => {

                console.log(
                    'CONSIGNMENT RETURN STORE FINISHED'
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
            'consignment-returns.index'
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

                <ReturnForm

                    :form="
                        form
                    "

                    :branches="
                        branches
                    "

                    :warehouses="
                        warehouses
                    "

                    :resellers="
                        filteredResellers()
                    "

                    :consignment-stocks="
                        consignmentStocks
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