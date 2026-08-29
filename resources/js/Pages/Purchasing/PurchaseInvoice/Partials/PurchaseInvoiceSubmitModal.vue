<script setup>

import { computed } from 'vue'

import BaseButton from '@/Components/Button/BaseButton.vue'

import {
    XMarkIcon,
} from '@heroicons/vue/24/outline'

import { formatDate } from '@/Utils'


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    show: {
        type: Boolean,
        default: false,
    },

    purchaseInvoice: {
        type: Object,
        default: null,
    },

    loading: {
        type: Boolean,
        default: false,
    },

})


/*
|--------------------------------------------------------------------------
| Emits
|--------------------------------------------------------------------------
*/

const emit = defineEmits([
    'close',
    'confirm',
])


/*
|--------------------------------------------------------------------------
| Actions
|--------------------------------------------------------------------------
*/

function closeSubmit()
{
    emit('close')
}


function confirmSubmit()
{
    emit('confirm')
}


/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

const formatNumber = (
    value
) => {

    return new Intl.NumberFormat(
        'id-ID',
        {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2,
        }
    ).format(
        Number(value ?? 0)
    )

}


const formatCurrency = (
    value
) => {

    return new Intl.NumberFormat(
        'id-ID',
        {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }
    ).format(
        Number(value ?? 0)
    )

}


/*
|--------------------------------------------------------------------------
| Details
|--------------------------------------------------------------------------
*/

const details = computed(() => {

    return props.purchaseInvoice?.details ?? []

})


/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

const totalItems = computed(() => {

    return details.value.length

})


const totalInvoiced = computed(() => {

    return details.value.reduce(

        (
            total,
            detail
        ) =>

            total +
            Number(
                detail.invoiced_qty ?? 0
            ),

        0

    )

})


const subtotal = computed(() => {

    if (
        props.purchaseInvoice?.subtotal !==
        undefined &&
        props.purchaseInvoice?.subtotal !==
        null
    ) {

        return Number(
            props.purchaseInvoice.subtotal
        )

    }


    return details.value.reduce(

        (
            total,
            detail
        ) =>

            total +
            Number(
                detail.subtotal ?? 0
            ),

        0

    )

})


const discountAmount = computed(() => {

    if (
        props.purchaseInvoice?.discount_amount !==
        undefined &&
        props.purchaseInvoice?.discount_amount !==
        null
    ) {

        return Number(
            props.purchaseInvoice.discount_amount
        )

    }


    return details.value.reduce(

        (
            total,
            detail
        ) =>

            total +
            Number(
                detail.discount_amount ?? 0
            ),

        0

    )

})


const taxAmount = computed(() => {

    if (
        props.purchaseInvoice?.tax_amount !==
        undefined &&
        props.purchaseInvoice?.tax_amount !==
        null
    ) {

        return Number(
            props.purchaseInvoice.tax_amount
        )

    }


    return details.value.reduce(

        (
            total,
            detail
        ) =>

            total +
            Number(
                detail.tax_amount ?? 0
            ),

        0

    )

})


const grandTotal = computed(() => {

    if (
        props.purchaseInvoice?.grand_total !==
        undefined &&
        props.purchaseInvoice?.grand_total !==
        null
    ) {

        return Number(
            props.purchaseInvoice.grand_total
        )

    }


    return (
        subtotal.value -
        discountAmount.value +
        taxAmount.value
    )

})


/*
|--------------------------------------------------------------------------
| Product
|--------------------------------------------------------------------------
*/

const getProductSku = (
    detail
) => {

    return (
        detail
            ?.productVariant
            ?.sku
        ??
        detail
            ?.product_variant
            ?.sku
        ??
        detail
            ?.variant
            ?.sku
        ??
        '-'
    )

}


const getProductName = (
    detail
) => {

    return (
        detail
            ?.productVariant
            ?.product
            ?.name
        ??
        detail
            ?.product_variant
            ?.product
            ?.name
        ??
        detail
            ?.variant
            ?.product
            ?.name
        ??
        detail
            ?.productVariant
            ?.name
        ??
        detail
            ?.product_variant
            ?.name
        ??
        detail
            ?.variant
            ?.name
        ??
        '-'
    )

}


const getUnitName = (
    detail
) => {

    return (
        detail
            ?.unit
            ?.name
        ??
        '-'
    )

}


const getUnitPrice = (
    detail
) => {

    return Number(
        detail?.unit_price ?? 0
    )

}


const getDiscountAmount = (
    detail
) => {

    return Number(
        detail?.discount_amount ?? 0
    )

}


const getTaxAmount = (
    detail
) => {

    return Number(
        detail?.tax_amount ?? 0
    )

}


const getTotalAmount = (
    detail
) => {

    if (
        detail?.total_amount !== undefined &&
        detail?.total_amount !== null
    ) {

        return Number(
            detail.total_amount
        )

    }


    return (
        Number(
            detail?.subtotal ?? 0
        ) -
        Number(
            detail?.discount_amount ?? 0
        ) +
        Number(
            detail?.tax_amount ?? 0
        )
    )

}

</script>


<template>

    <Teleport to="body">

        <div
            v-if="show"
            class="
                fixed
                inset-0
                z-[100]
                flex
                items-center
                justify-center
                bg-black/40
                px-3
                py-4
                sm:px-4
                sm:py-6
            "
            @click.self="closeSubmit"
        >

            <div
                class="
                    flex
                    max-h-[92vh]
                    w-full
                    max-w-6xl
                    flex-col
                    overflow-hidden
                    rounded-2xl
                    bg-white
                    shadow-2xl
                "
            >

                <!-- ================================================= -->
                <!-- Header -->
                <!-- ================================================= -->

                <div
                    class="
                        flex
                        shrink-0
                        items-center
                        justify-between
                        border-b
                        border-gray-200
                        px-4
                        py-4
                        sm:px-6
                    "
                >

                    <div class="min-w-0">

                        <h2
                            class="
                                truncate
                                text-lg
                                font-semibold
                                text-gray-900
                                sm:text-xl
                            "
                        >
                            Submit Purchase Invoice
                        </h2>

                        <p
                            class="
                                mt-1
                                hidden
                                text-sm
                                text-gray-500
                                sm:block
                            "
                        >
                            Review the purchase invoice before
                            submitting it for approval.
                        </p>

                    </div>


                    <button
                        type="button"
                        class="
                            ml-4
                            shrink-0
                            rounded-lg
                            p-2
                            text-gray-400
                            transition
                            hover:bg-gray-100
                            hover:text-gray-700
                        "
                        title="Close"
                        @click="closeSubmit"
                    >

                        <XMarkIcon
                            class="h-5 w-5"
                        />

                    </button>

                </div>


                <!-- ================================================= -->
                <!-- Body -->
                <!-- ================================================= -->

                <div
                    class="
                        min-h-0
                        flex-1
                        overflow-y-auto
                        p-4
                        sm:p-6
                    "
                >

                    <template
                        v-if="purchaseInvoice"
                    >

                        <!-- ========================================= -->
                        <!-- Purchase Invoice Information -->
                        <!-- ========================================= -->

                        <div
                            class="
                                rounded-xl
                                border
                                border-gray-200
                                bg-gray-50
                                p-4
                                sm:p-5
                            "
                        >

                            <div
                                class="
                                    grid
                                    grid-cols-1
                                    gap-4
                                    sm:grid-cols-2
                                    lg:grid-cols-4
                                "
                            >

                                <!-- Internal Number -->

                                <div>

                                    <div
                                        class="
                                            text-xs
                                            font-medium
                                            uppercase
                                            tracking-wide
                                            text-gray-500
                                        "
                                    >
                                        Internal Number
                                    </div>

                                    <div
                                        class="
                                            mt-1
                                            font-semibold
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            purchaseInvoice.number
                                            ?? '-'
                                        }}
                                    </div>

                                </div>


                                <!-- Supplier Invoice -->

                                <div>

                                    <div
                                        class="
                                            text-xs
                                            font-medium
                                            uppercase
                                            tracking-wide
                                            text-gray-500
                                        "
                                    >
                                        Supplier Invoice
                                    </div>

                                    <div
                                        class="
                                            mt-1
                                            font-semibold
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            purchaseInvoice.invoice_number
                                            ?? '-'
                                        }}
                                    </div>

                                </div>


                                <!-- Invoice Date -->

                                <div>

                                    <div
                                        class="
                                            text-xs
                                            font-medium
                                            uppercase
                                            tracking-wide
                                            text-gray-500
                                        "
                                    >
                                        Invoice Date
                                    </div>

                                    <div
                                        class="
                                            mt-1
                                            font-medium
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            purchaseInvoice.invoice_date
                                                ? formatDate(
                                                    purchaseInvoice.invoice_date
                                                )
                                                : '-'
                                        }}
                                    </div>

                                </div>


                                <!-- Due Date -->

                                <div>

                                    <div
                                        class="
                                            text-xs
                                            font-medium
                                            uppercase
                                            tracking-wide
                                            text-gray-500
                                        "
                                    >
                                        Due Date
                                    </div>

                                    <div
                                        class="
                                            mt-1
                                            font-medium
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            purchaseInvoice.due_date
                                                ? formatDate(
                                                    purchaseInvoice.due_date
                                                )
                                                : '-'
                                        }}
                                    </div>

                                </div>

                            </div>


                            <!-- Supplier / PO / GRN -->

                            <div
                                class="
                                    mt-4
                                    grid
                                    grid-cols-1
                                    gap-4
                                    border-t
                                    border-gray-200
                                    pt-4
                                    sm:grid-cols-2
                                    lg:grid-cols-4
                                "
                            >

                                <!-- Supplier -->

                                <div>

                                    <div
                                        class="
                                            text-xs
                                            font-medium
                                            uppercase
                                            tracking-wide
                                            text-gray-500
                                        "
                                    >
                                        Supplier
                                    </div>

                                    <div
                                        class="
                                            mt-1
                                            font-semibold
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            purchaseInvoice
                                                .supplier
                                                ?.name
                                            ?? '-'
                                        }}
                                    </div>

                                </div>


                                <!-- Purchase Order -->

                                <div>

                                    <div
                                        class="
                                            text-xs
                                            font-medium
                                            uppercase
                                            tracking-wide
                                            text-gray-500
                                        "
                                    >
                                        Purchase Order
                                    </div>

                                    <div
                                        class="
                                            mt-1
                                            font-semibold
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            purchaseInvoice
                                                .purchaseOrder
                                                ?.number
                                            ??
                                            purchaseInvoice
                                                .purchase_order
                                                ?.number
                                            ??
                                            '-'
                                        }}
                                    </div>

                                </div>


                                <!-- Goods Receipt -->

                                <div>

                                    <div
                                        class="
                                            text-xs
                                            font-medium
                                            uppercase
                                            tracking-wide
                                            text-gray-500
                                        "
                                    >
                                        Goods Receipt
                                    </div>

                                    <div
                                        class="
                                            mt-1
                                            font-semibold
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            purchaseInvoice
                                                .goodsReceipt
                                                ?.grn_number
                                            ??
                                            purchaseInvoice
                                                .goods_receipt
                                                ?.grn_number
                                            ??
                                            '-'
                                        }}
                                    </div>

                                </div>


                                <!-- Payment Term -->

                                <div>

                                    <div
                                        class="
                                            text-xs
                                            font-medium
                                            uppercase
                                            tracking-wide
                                            text-gray-500
                                        "
                                    >
                                        Payment Term
                                    </div>

                                    <div
                                        class="
                                            mt-1
                                            font-medium
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            purchaseInvoice
                                                .paymentTerm
                                                ?.name
                                            ??
                                            purchaseInvoice
                                                .payment_term
                                                ?.name
                                            ??
                                            '-'
                                        }}
                                    </div>

                                </div>

                            </div>


                            <!-- Currency / Tax -->

                            <div
                                class="
                                    mt-4
                                    grid
                                    grid-cols-1
                                    gap-4
                                    border-t
                                    border-gray-200
                                    pt-4
                                    sm:grid-cols-2
                                "
                            >

                                <div>

                                    <div
                                        class="
                                            text-xs
                                            font-medium
                                            uppercase
                                            tracking-wide
                                            text-gray-500
                                        "
                                    >
                                        Currency
                                    </div>

                                    <div
                                        class="
                                            mt-1
                                            font-medium
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            purchaseInvoice
                                                .currency
                                                ?.code
                                            ?? 'IDR'
                                        }}
                                    </div>

                                </div>


                                <div>

                                    <div
                                        class="
                                            text-xs
                                            font-medium
                                            uppercase
                                            tracking-wide
                                            text-gray-500
                                        "
                                    >
                                        Tax
                                    </div>

                                    <div
                                        class="
                                            mt-1
                                            font-medium
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            purchaseInvoice
                                                .tax
                                                ?.name
                                            ?? '-'
                                        }}
                                    </div>

                                </div>

                            </div>

                        </div>


                        <!-- ========================================= -->
                        <!-- Details -->
                        <!-- ========================================= -->

                        <div class="mt-6">

                            <div
                                class="
                                    mb-3
                                    flex
                                    items-center
                                    justify-between
                                "
                            >

                                <div>

                                    <h3
                                        class="
                                            text-base
                                            font-semibold
                                            text-gray-900
                                        "
                                    >
                                        Invoice Details
                                    </h3>

                                    <p
                                        class="
                                            mt-0.5
                                            text-xs
                                            text-gray-500
                                        "
                                    >
                                        Review the products, quantities
                                        and invoice amounts.
                                    </p>

                                </div>

                                <span
                                    class="
                                        rounded-full
                                        bg-gray-100
                                        px-2.5
                                        py-1
                                        text-xs
                                        font-medium
                                        text-gray-600
                                    "
                                >
                                    {{ totalItems }} Items
                                </span>

                            </div>


                            <div
                                class="
                                    overflow-x-auto
                                    rounded-xl
                                    border
                                    border-gray-200
                                "
                            >

                                <table
                                    class="
                                        min-w-[1250px]
                                        w-full
                                        divide-y
                                        divide-gray-200
                                    "
                                >

                                    <thead
                                        class="bg-gray-50"
                                    >

                                        <tr>

                                            <th
                                                class="
                                                    px-4
                                                    py-3
                                                    text-left
                                                    text-xs
                                                    font-semibold
                                                    uppercase
                                                    tracking-wide
                                                    text-gray-500
                                                "
                                            >
                                                Product Variant
                                            </th>

                                            <th
                                                class="
                                                    w-28
                                                    px-4
                                                    py-3
                                                    text-left
                                                    text-xs
                                                    font-semibold
                                                    uppercase
                                                    tracking-wide
                                                    text-gray-500
                                                "
                                            >
                                                Unit
                                            </th>

                                            <th
                                                class="
                                                    w-28
                                                    px-4
                                                    py-3
                                                    text-right
                                                    text-xs
                                                    font-semibold
                                                    uppercase
                                                    tracking-wide
                                                    text-gray-500
                                                "
                                            >
                                                Invoiced Qty
                                            </th>

                                            <th
                                                class="
                                                    w-36
                                                    px-4
                                                    py-3
                                                    text-right
                                                    text-xs
                                                    font-semibold
                                                    uppercase
                                                    tracking-wide
                                                    text-gray-500
                                                "
                                            >
                                                Unit Price
                                            </th>

                                            <th
                                                class="
                                                    w-36
                                                    px-4
                                                    py-3
                                                    text-right
                                                    text-xs
                                                    font-semibold
                                                    uppercase
                                                    tracking-wide
                                                    text-gray-500
                                                "
                                            >
                                                Subtotal
                                            </th>

                                            <th
                                                class="
                                                    w-36
                                                    px-4
                                                    py-3
                                                    text-right
                                                    text-xs
                                                    font-semibold
                                                    uppercase
                                                    tracking-wide
                                                    text-gray-500
                                                "
                                            >
                                                Discount
                                            </th>

                                            <th
                                                class="
                                                    w-32
                                                    px-4
                                                    py-3
                                                    text-right
                                                    text-xs
                                                    font-semibold
                                                    uppercase
                                                    tracking-wide
                                                    text-gray-500
                                                "
                                            >
                                                Tax
                                            </th>

                                            <th
                                                class="
                                                    w-40
                                                    px-4
                                                    py-3
                                                    text-right
                                                    text-xs
                                                    font-semibold
                                                    uppercase
                                                    tracking-wide
                                                    text-gray-500
                                                "
                                            >
                                                Total
                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody
                                        class="
                                            divide-y
                                            divide-gray-100
                                            bg-white
                                        "
                                    >

                                        <tr
                                            v-for="
                                                (
                                                    detail,
                                                    index
                                                )
                                                in details
                                            "
                                            :key="
                                                detail.id
                                                ?? index
                                            "
                                            class="
                                                transition
                                                hover:bg-gray-50
                                            "
                                        >

                                            <!-- Product -->

                                            <td
                                                class="
                                                    px-4
                                                    py-3
                                                "
                                            >

                                                <div
                                                    class="
                                                        font-medium
                                                        text-gray-900
                                                    "
                                                >
                                                    {{
                                                        getProductSku(
                                                            detail
                                                        )
                                                    }}
                                                </div>

                                                <div
                                                    class="
                                                        mt-0.5
                                                        text-xs
                                                        text-gray-500
                                                    "
                                                >
                                                    {{
                                                        getProductName(
                                                            detail
                                                        )
                                                    }}
                                                </div>

                                            </td>


                                            <!-- Unit -->

                                            <td
                                                class="
                                                    whitespace-nowrap
                                                    px-4
                                                    py-3
                                                    text-sm
                                                    text-gray-700
                                                "
                                            >
                                                {{
                                                    getUnitName(
                                                        detail
                                                    )
                                                }}
                                            </td>


                                            <!-- Invoiced Qty -->

                                            <td
                                                class="
                                                    whitespace-nowrap
                                                    px-4
                                                    py-3
                                                    text-right
                                                    text-sm
                                                    font-medium
                                                    text-gray-700
                                                "
                                            >
                                                {{
                                                    formatNumber(
                                                        detail.invoiced_qty
                                                    )
                                                }}
                                            </td>


                                            <!-- Unit Price -->

                                            <td
                                                class="
                                                    whitespace-nowrap
                                                    px-4
                                                    py-3
                                                    text-right
                                                    text-sm
                                                    font-medium
                                                    text-gray-700
                                                "
                                            >
                                                {{
                                                    formatCurrency(
                                                        getUnitPrice(
                                                            detail
                                                        )
                                                    )
                                                }}
                                            </td>


                                            <!-- Subtotal -->

                                            <td
                                                class="
                                                    whitespace-nowrap
                                                    px-4
                                                    py-3
                                                    text-right
                                                    text-sm
                                                    font-medium
                                                    text-gray-700
                                                "
                                            >
                                                {{
                                                    formatCurrency(
                                                        detail.subtotal
                                                    )
                                                }}
                                            </td>


                                            <!-- Discount -->

                                            <td
                                                class="
                                                    whitespace-nowrap
                                                    px-4
                                                    py-3
                                                    text-right
                                                    text-sm
                                                    font-medium
                                                    text-gray-700
                                                "
                                            >
                                                {{
                                                    formatCurrency(
                                                        getDiscountAmount(
                                                            detail
                                                        )
                                                    )
                                                }}
                                            </td>


                                            <!-- Tax -->

                                            <td
                                                class="
                                                    whitespace-nowrap
                                                    px-4
                                                    py-3
                                                    text-right
                                                    text-sm
                                                    font-medium
                                                    text-gray-700
                                                "
                                            >
                                                {{
                                                    formatCurrency(
                                                        getTaxAmount(
                                                            detail
                                                        )
                                                    )
                                                }}
                                            </td>


                                            <!-- Total -->

                                            <td
                                                class="
                                                    whitespace-nowrap
                                                    px-4
                                                    py-3
                                                    text-right
                                                    text-sm
                                                    font-semibold
                                                    text-gray-900
                                                "
                                            >
                                                {{
                                                    formatCurrency(
                                                        getTotalAmount(
                                                            detail
                                                        )
                                                    )
                                                }}
                                            </td>

                                        </tr>


                                        <!-- Empty -->

                                        <tr
                                            v-if="
                                                !details.length
                                            "
                                        >

                                            <td
                                                colspan="8"
                                                class="
                                                    px-4
                                                    py-8
                                                    text-center
                                                    text-sm
                                                    text-gray-500
                                                "
                                            >
                                                No purchase invoice details found.
                                            </td>

                                        </tr>

                                    </tbody>

                                </table>

                            </div>

                        </div>


                        <!-- ========================================= -->
                        <!-- Remarks -->
                        <!-- ========================================= -->

                        <div
                            v-if="
                                purchaseInvoice.remarks
                            "
                            class="
                                mt-6
                                rounded-xl
                                border
                                border-gray-200
                                p-4
                                sm:p-5
                            "
                        >

                            <div
                                class="
                                    text-xs
                                    font-medium
                                    uppercase
                                    tracking-wide
                                    text-gray-500
                                "
                            >
                                Remarks
                            </div>

                            <div
                                class="
                                    mt-2
                                    whitespace-pre-line
                                    text-sm
                                    leading-6
                                    text-gray-700
                                "
                            >
                                {{
                                    purchaseInvoice.remarks
                                }}
                            </div>

                        </div>


                        <!-- ========================================= -->
                        <!-- Summary -->
                        <!-- ========================================= -->

                        <div
                            class="
                                mt-6
                                flex
                                justify-end
                            "
                        >

                            <div
                                class="
                                    w-full
                                    rounded-xl
                                    border
                                    border-gray-200
                                    bg-gray-50
                                    p-4
                                    sm:max-w-md
                                    sm:p-5
                                "
                            >

                                <div
                                    class="
                                        flex
                                        justify-between
                                        py-2
                                        text-sm
                                    "
                                >

                                    <span
                                        class="text-gray-600"
                                    >
                                        Total Items
                                    </span>

                                    <span
                                        class="
                                            font-semibold
                                            text-gray-900
                                        "
                                    >
                                        {{ totalItems }}
                                    </span>

                                </div>


                                <div
                                    class="
                                        flex
                                        justify-between
                                        py-2
                                        text-sm
                                    "
                                >

                                    <span
                                        class="text-gray-600"
                                    >
                                        Invoiced Quantity
                                    </span>

                                    <span
                                        class="
                                            font-semibold
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            formatNumber(
                                                totalInvoiced
                                            )
                                        }}
                                    </span>

                                </div>


                                <div
                                    class="
                                        flex
                                        justify-between
                                        py-2
                                        text-sm
                                    "
                                >

                                    <span
                                        class="text-gray-600"
                                    >
                                        Subtotal
                                    </span>

                                    <span
                                        class="
                                            font-semibold
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            formatCurrency(
                                                subtotal
                                            )
                                        }}
                                    </span>

                                </div>


                                <div
                                    class="
                                        flex
                                        justify-between
                                        py-2
                                        text-sm
                                    "
                                >

                                    <span
                                        class="text-gray-600"
                                    >
                                        Discount
                                    </span>

                                    <span
                                        class="
                                            font-semibold
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            formatCurrency(
                                                discountAmount
                                            )
                                        }}
                                    </span>

                                </div>


                                <div
                                    class="
                                        flex
                                        justify-between
                                        py-2
                                        text-sm
                                    "
                                >

                                    <span
                                        class="text-gray-600"
                                    >
                                        Tax
                                    </span>

                                    <span
                                        class="
                                            font-semibold
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            formatCurrency(
                                                taxAmount
                                            )
                                        }}
                                    </span>

                                </div>


                                <div
                                    class="
                                        flex
                                        justify-between
                                        border-t
                                        border-gray-200
                                        pt-3
                                        text-base
                                        font-semibold
                                    "
                                >

                                    <span>
                                        Grand Total
                                    </span>

                                    <span>
                                        {{
                                            formatCurrency(
                                                grandTotal
                                            )
                                        }}
                                    </span>

                                </div>

                            </div>

                        </div>


                        <!-- ========================================= -->
                        <!-- Warning -->
                        <!-- ========================================= -->

                        <div
                            class="
                                mt-6
                                rounded-xl
                                border
                                border-blue-200
                                bg-blue-50
                                p-4
                            "
                        >

                            <div
                                class="
                                    text-sm
                                    font-semibold
                                    text-blue-800
                                "
                            >
                                Ready to submit
                            </div>

                            <p
                                class="
                                    mt-1
                                    text-sm
                                    leading-6
                                    text-blue-700
                                "
                            >
                                This purchase invoice will be sent
                                for approval after submission.
                                The invoice will not be posted to
                                accounting until it is approved
                                and posted.
                            </p>

                        </div>

                    </template>


                    <!-- No Data -->

                    <div
                        v-else
                        class="
                            flex
                            min-h-[300px]
                            items-center
                            justify-center
                            text-sm
                            text-gray-500
                        "
                    >
                        Purchase invoice data is not available.
                    </div>

                </div>


                <!-- ================================================= -->
                <!-- Footer -->
                <!-- ================================================= -->

                <div
                    class="
                        flex
                        shrink-0
                        flex-col-reverse
                        gap-2
                        border-t
                        border-gray-200
                        bg-gray-50
                        px-4
                        py-4
                        sm:flex-row
                        sm:justify-end
                        sm:px-6
                    "
                >

                    <BaseButton
                        type="button"
                        variant="secondary"
                        @click="closeSubmit"
                    >
                        Cancel
                    </BaseButton>


                    <BaseButton
                        type="button"
                        variant="success"
                        :loading="loading"
                        :disabled="
                            !purchaseInvoice ||
                            !details.length ||
                            loading
                        "
                        @click="confirmSubmit"
                    >
                        Submit Purchase Invoice
                    </BaseButton>

                </div>

            </div>

        </div>

    </Teleport>

</template>