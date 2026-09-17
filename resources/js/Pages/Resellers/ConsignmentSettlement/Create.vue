<script setup>

import { computed } from 'vue'
import { Head, useForm, usePage, router } from '@inertiajs/vue3'
import Swal from 'sweetalert2'
import AppLayout from '@/Layouts/AppLayout.vue'
import SettlementForm from './SettlementForm.vue'

const props = defineProps({

    title: {
        type: String,
        default: 'Consignment Settlement',
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

    resellerPrices: {
        type: Array,
        default: () => [],
    },

    variants: {
        type: Array,
        default: () => [],
    },

    units: {
        type: Array,
        default: () => [],
    },
    consignmentStocks: {
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

    settlement_number:
        props.previewNumber,

    branch_id:
        null,

    warehouse_id:
        null,

    reseller_id:
        null,

    settlement_date:
        '',

    period_from:
        '',

    period_to:
        '',

    payment_amount:
        0,

    remarks:
        '',

    details: [

        {
            product_variant_id:
                null,

            unit_id:
                null,

            qty_sold:
                1,

            unit_price:
                0,

            total_amount:
                0,
        },

    ],

})


/*
|--------------------------------------------------------------------------
| Filtered Warehouses
|--------------------------------------------------------------------------
*/

const filteredWarehouses = computed(() => {

    if (!form.branch_id) {

        return []

    }


    return props.warehouses.filter(

        warehouse =>

            Number(
                warehouse.branch_id
            ) ===
            Number(
                form.branch_id
            )

    )

})


/*
|--------------------------------------------------------------------------
| Filtered Resellers
|--------------------------------------------------------------------------
|
| Reseller is company-level, not branch-level.
|--------------------------------------------------------------------------
*/

const filteredResellers = computed(() => {

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

})


/*
|--------------------------------------------------------------------------
| Filtered Variants
|--------------------------------------------------------------------------
|
| Settlement only allows products that have a reseller price.
|--------------------------------------------------------------------------
*/

const filteredVariants = computed(() => {

    if (!form.reseller_id) {

        return []

    }


    const productIds =

        props.resellerPrices

            .filter(

                price =>

                    Number(
                        price.reseller_id
                    ) ===
                    Number(
                        form.reseller_id
                    )

            )

            .map(

                price =>

                    Number(
                        price.product_id
                    )

            )


    return props.variants

        .filter(

            variant =>

                productIds.includes(

                    Number(
                        variant.product_id
                    )

                )

        )

        .map(

            variant => ({

                ...variant,

                label:
                    variant.label ??
                    [
                        variant.product?.name,
                        variant.name,
                        variant.sku
                            ? `SKU: ${variant.sku}`
                            : null,
                    ]
                        .filter(Boolean)
                        .join(' - '),

                units:
                    (variant.units ?? [])
                        .map(
                            unit => ({

                                ...unit,

                                label:
                                    unit.label ??
                                    unit.name ??
                                    unit.unit_name ??
                                    '-',

                            })
                        ),

            })

        )

})


/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/

const submit = () => {

    form.post(
        route(
            'consignment-settlements.store'
        ),
        {

            preserveScroll: true,

            onSuccess: (
                response
            ) => {

                showSuccess(
                    response
                )

            },

        }
    )

}


/*
|--------------------------------------------------------------------------
| Success Swal
|--------------------------------------------------------------------------
*/

const showSuccess = (
    response
) => {

    const flash =
        response?.props?.flash
            ?.settlement_success


    /*
    |--------------------------------------------------------------------------
    | Fallback
    |--------------------------------------------------------------------------
    */

    if (!flash) {

        Swal.fire({

            icon: 'success',

            title: 'Settlement Posted',

            text:
                'Consignment settlement has been posted successfully.',

            confirmButtonText:
                'OK',

            buttonsStyling: false,

            customClass: {

                popup:
                    'rounded-2xl',

                confirmButton:
                    'rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-medium text-white',

            },

        })

        return

    }


    /*
    |--------------------------------------------------------------------------
    | Success Dialog
    |--------------------------------------------------------------------------
    */

    Swal.fire({

        icon: 'success',

        title: 'Settlement Posted',

        html: `

            <div class="space-y-1">

                <div class="text-sm text-gray-500">
                    Settlement has been posted successfully.
                </div>

                ${
                    flash.number
                        ? `
                            <div class="mt-3 text-base font-semibold text-gray-900">
                                ${flash.number}
                            </div>
                        `
                        : ''
                }

            </div>

        `,

        showConfirmButton: true,

        confirmButtonText:
            'Print Struk',

        showDenyButton: true,

        denyButtonText:
            'New Transaction',

        showCancelButton: true,

        cancelButtonText:
            'Exit',

        allowOutsideClick: false,

        allowEscapeKey: false,

        buttonsStyling: false,

        customClass: {

            popup:
                'rounded-2xl',

            title:
                'text-xl font-semibold text-gray-900',

            htmlContainer:
                'text-gray-500',

            actions:
                'gap-2',

            confirmButton:
                'rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-medium text-white',

            denyButton:
                'rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white',

            cancelButton:
                'rounded-lg bg-gray-100 px-4 py-2.5 text-sm font-medium text-gray-700',

        },

    }).then(

        result => {

            /*
            |--------------------------------------------------------------------------
            | Print
            |--------------------------------------------------------------------------
            */

            if (
                result.isConfirmed
            ) {

                const printUrl =
                    flash.print_url

                    ??
                    (
                        flash.id
                            ? route(
                                'consignment-settlements.print',
                                flash.id
                            )
                            : null
                    )


                if (printUrl) {

                    window.open(
                        printUrl,
                        '_blank'
                    )

                }

                return

            }


            /*
            |--------------------------------------------------------------------------
            | New Transaction
            |--------------------------------------------------------------------------
            */

            if (
                result.isDenied
            ) {

                window.location.href =
                    route(
                        'consignment-settlements.create'
                    )

                return

            }


            /*
            |--------------------------------------------------------------------------
            | Exit
            |--------------------------------------------------------------------------
            */

            window.location.href =
                route(
                    'consignment-settlements.index'
                )

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
            'consignment-settlements.index'
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

            <SettlementForm
                :form="form"
                :branches="branches"
                :filtered-warehouses="filteredWarehouses"
                :resellers="filteredResellers"
                :reseller-prices="resellerPrices"
                :filtered-variants="filteredVariants"
                :consignment-stocks="consignmentStocks"
                @submit="submit"
                @cancel="cancel"
            />

        </div>

    </div>
</AppLayout>
</template>