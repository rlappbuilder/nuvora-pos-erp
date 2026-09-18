<script setup>

import {
    Head,
    useForm,
    router,
} from '@inertiajs/vue3'

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
        default: 'Edit Consignment Return - Update',
    },

    consignmentReturn: {
        type: Object,
        required: true,
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
| Existing Return
|--------------------------------------------------------------------------
*/

const consignmentReturn =
    props.consignmentReturn


/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/

const form = useForm({

    return_number:
        consignmentReturn.return_number
        ?? '',

    branch_id:
        consignmentReturn.branch_id
        ?? null,

    warehouse_id:
        consignmentReturn.warehouse_id
        ?? null,

    reseller_id:
        consignmentReturn.reseller_id
        ?? null,

    settlement_header_id:
        consignmentReturn.settlement_header_id
        ?? null,

    return_date:
        consignmentReturn.return_date
        ?? '',

    remarks:
        consignmentReturn.remarks
        ?? '',

    details:
        consignmentReturn.details?.length

            ? consignmentReturn.details.map(
                detail => ({

                    product_variant_id:
                        detail.product_variant_id
                        ?? null,

                    unit_id:
                        detail.unit_id
                        ?? null,

                    returned_qty:
                        Number(
                            detail.returned_qty
                            ?? 0
                        ),

                    unit_cost:
                        Number(
                            detail.unit_cost
                            ?? 0
                        ),

                    total_cost:
                        Number(
                            detail.total_cost
                            ?? 0
                        ),

                    remarks:
                        detail.remarks
                        ?? '',

                })
            )

            : [

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
            'consignment-returns.update',
            consignmentReturn.id
        ),

        {

            preserveScroll: true,

            onSuccess: () => {

                success(
                    'Success',
                    'Consignment Return updated successfully.'
                )

            },

            onError: (errors) => {

                console.error(
                    'UPDATE CONSIGNMENT RETURN ERRORS:',
                    errors
                )

                error(
                    'Failed to update Consignment Return.'
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