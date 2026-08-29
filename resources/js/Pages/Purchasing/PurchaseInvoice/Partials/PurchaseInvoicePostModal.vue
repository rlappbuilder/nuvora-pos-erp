<script setup>

import BaseButton from '@/Components/Button/BaseButton.vue'
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
| Debug
|--------------------------------------------------------------------------
*/

console.log(
    'POST PURCHASE INVOICE:',
    props.purchaseInvoice
)

console.log(
    'POST PURCHASE INVOICE DETAILS:',
    props.purchaseInvoice?.details
)


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

function closePost()
{
    emit('close')
}


function confirmPost()
{
    emit('confirm')
}


/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function formatNumber(value)
{
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


function formatCurrency(value)
{
    return new Intl.NumberFormat(
        'id-ID',
        {
            style: 'currency',
            currency:
                props.purchaseInvoice
                    ?.currency
                    ?.code
                ?? 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }
    ).format(
        Number(value ?? 0)
    )
}


/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

function totalItems()
{
    return (
        props.purchaseInvoice
            ?.details
            ?.length
        ?? 0
    )
}


function totalInvoicedQuantity()
{
    return (
        props.purchaseInvoice
            ?.details
            ?.reduce(
                (
                    total,
                    detail
                ) =>
                    total +
                    Number(
                        detail.invoiced_qty
                        ?? 0
                    ),
                0
            )
        ?? 0
    )
}


function totalSubtotal()
{
    if (
        props.purchaseInvoice
            ?.subtotal !==
        undefined &&
        props.purchaseInvoice
            ?.subtotal !==
        null
    ) {

        return Number(
            props.purchaseInvoice.subtotal
        )

    }


    return (
        props.purchaseInvoice
            ?.details
            ?.reduce(
                (
                    total,
                    detail
                ) =>
                    total +
                    Number(
                        detail.subtotal
                        ?? 0
                    ),
                0
            )
        ?? 0
    )
}


function totalDiscount()
{
    if (
        props.purchaseInvoice
            ?.discount_amount !==
        undefined &&
        props.purchaseInvoice
            ?.discount_amount !==
        null
    ) {

        return Number(
            props.purchaseInvoice.discount_amount
        )

    }


    return (
        props.purchaseInvoice
            ?.details
            ?.reduce(
                (
                    total,
                    detail
                ) =>
                    total +
                    Number(
                        detail.discount_amount
                        ?? 0
                    ),
                0
            )
        ?? 0
    )
}


function totalTax()
{
    if (
        props.purchaseInvoice
            ?.tax_amount !==
        undefined &&
        props.purchaseInvoice
            ?.tax_amount !==
        null
    ) {

        return Number(
            props.purchaseInvoice.tax_amount
        )

    }


    return (
        props.purchaseInvoice
            ?.details
            ?.reduce(
                (
                    total,
                    detail
                ) =>
                    total +
                    Number(
                        detail.tax_amount
                        ?? 0
                    ),
                0
            )
        ?? 0
    )
}


function grandTotal()
{
    if (
        props.purchaseInvoice
            ?.grand_total !==
        undefined &&
        props.purchaseInvoice
            ?.grand_total !==
        null
    ) {

        return Number(
            props.purchaseInvoice.grand_total
        )

    }


    return (
        totalSubtotal() -
        totalDiscount() +
        totalTax()
    )
}


function paidAmount()
{
    return Number(
        props.purchaseInvoice
            ?.paid_amount
        ?? 0
    )
}


function outstandingAmount()
{
    if (
        props.purchaseInvoice
            ?.outstanding_amount !==
        undefined &&
        props.purchaseInvoice
            ?.outstanding_amount !==
        null
    ) {

        return Number(
            props.purchaseInvoice
                .outstanding_amount
        )

    }


    return Math.max(
        0,
        grandTotal() -
        paidAmount()
    )
}


/*
|--------------------------------------------------------------------------
| Product
|--------------------------------------------------------------------------
*/

function getProductSku(detail)
{
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


function getProductName(detail)
{
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


function getUnitName(detail)
{
    return (
        detail
            ?.unit
            ?.name
        ??
        detail
            ?.unit
            ?.label
        ??
        '-'
    )
}


/*
|--------------------------------------------------------------------------
| Detail Helpers
|--------------------------------------------------------------------------
*/

function getUnitPrice(detail)
{
    return Number(
        detail?.unit_price
        ?? 0
    )
}


function getSubtotal(detail)
{
    return Number(
        detail?.subtotal
        ?? 0
    )
}


function getDiscount(detail)
{
    return Number(
        detail?.discount_amount
        ?? 0
    )
}


function getTax(detail)
{
    return Number(
        detail?.tax_amount
        ?? 0
    )
}


function getTotal(detail)
{
    if (
        detail?.total_amount !==
        undefined &&
        detail?.total_amount !==
        null
    ) {

        return Number(
            detail.total_amount
        )

    }


    return (
        getSubtotal(detail) -
        getDiscount(detail) +
        getTax(detail)
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
            @click.self="closePost"
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
                            Post Purchase Invoice
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
                            posting it.
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
                        @click="closePost"
                    >
                        ✕
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
                                grid
                                grid-cols-1
                                gap-4
                                rounded-xl
                                border
                                border-gray-200
                                bg-gray-50
                                p-4
                                sm:p-5
                                md:grid-cols-2
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
                                        purchaseInvoice
                                            .invoice_number
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
                                        font-semibold
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
                                        font-semibold
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


                            <!-- Branch -->

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
                                    Branch
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
                                            .branch
                                            ?.name
                                        ?? '-'
                                    }}
                                </div>

                            </div>


                            <!-- Warehouse -->

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
                                    Warehouse
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
                                            .warehouse
                                            ?.name
                                        ?? '-'
                                    }}
                                </div>

                            </div>


                            <!-- Status -->

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
                                    Status
                                </div>

                                <div
                                    class="
                                        mt-1
                                        font-semibold
                                        text-gray-900
                                    "
                                >
                                    {{
                                        purchaseInvoice.status
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
                                        font-semibold
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


                            <!-- Currency -->

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
                                        font-semibold
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


                            <!-- Tax -->

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
                                        font-semibold
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

                                    <div
                                        class="
                                            text-base
                                            font-semibold
                                            text-gray-900
                                        "
                                    >
                                        Purchase Invoice Details
                                    </div>

                                    <div
                                        class="
                                            mt-0.5
                                            text-xs
                                            text-gray-500
                                        "
                                    >
                                        Review the products and
                                        amounts that will be posted.
                                    </div>

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
                                    {{ totalItems() }} Items
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
                                                    w-24
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
                                            v-for="(
                                                detail,
                                                index
                                            ) in purchaseInvoice.details"

                                            :key="
                                                detail.id
                                                ?? index
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


                                            <!-- Quantity -->

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
                                                    text-gray-700
                                                "
                                            >
                                                {{
                                                    formatCurrency(
                                                        getSubtotal(
                                                            detail
                                                        )
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
                                                    text-gray-700
                                                "
                                            >
                                                {{
                                                    formatCurrency(
                                                        getDiscount(
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
                                                    text-gray-700
                                                "
                                            >
                                                {{
                                                    formatCurrency(
                                                        getTax(
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
                                                        getTotal(
                                                            detail
                                                        )
                                                    )
                                                }}
                                            </td>

                                        </tr>


                                        <!-- Empty -->

                                        <tr
                                            v-if="
                                                !purchaseInvoice.details?.length
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
                                                No purchase invoice
                                                details found.
                                            </td>

                                        </tr>

                                    </tbody>

                                </table>

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
                                    max-w-md
                                    rounded-xl
                                    border
                                    border-gray-200
                                    bg-gray-50
                                    p-5
                                "
                            >

                                <!-- Total Items -->

                                <div
                                    class="
                                        flex
                                        justify-between
                                        py-2
                                        text-sm
                                    "
                                >

                                    <span class="text-gray-600">
                                        Total Items
                                    </span>

                                    <span class="font-semibold">
                                        {{ totalItems() }}
                                    </span>

                                </div>


                                <!-- Quantity -->

                                <div
                                    class="
                                        flex
                                        justify-between
                                        py-2
                                        text-sm
                                    "
                                >

                                    <span class="text-gray-600">
                                        Invoiced Quantity
                                    </span>

                                    <span class="font-semibold">
                                        {{
                                            formatNumber(
                                                totalInvoicedQuantity()
                                            )
                                        }}
                                    </span>

                                </div>


                                <!-- Subtotal -->

                                <div
                                    class="
                                        flex
                                        justify-between
                                        py-2
                                        text-sm
                                    "
                                >

                                    <span class="text-gray-600">
                                        Subtotal
                                    </span>

                                    <span class="font-semibold">
                                        {{
                                            formatCurrency(
                                                totalSubtotal()
                                            )
                                        }}
                                    </span>

                                </div>


                                <!-- Discount -->

                                <div
                                    class="
                                        flex
                                        justify-between
                                        py-2
                                        text-sm
                                    "
                                >

                                    <span class="text-gray-600">
                                        Discount
                                    </span>

                                    <span class="font-semibold">
                                        {{
                                            formatCurrency(
                                                totalDiscount()
                                            )
                                        }}
                                    </span>

                                </div>


                                <!-- Tax -->

                                <div
                                    class="
                                        flex
                                        justify-between
                                        py-2
                                        text-sm
                                    "
                                >

                                    <span class="text-gray-600">
                                        Tax
                                    </span>

                                    <span class="font-semibold">
                                        {{
                                            formatCurrency(
                                                totalTax()
                                            )
                                        }}
                                    </span>

                                </div>
                                <!-- Grand Total -->

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
                                                grandTotal()
                                            )
                                        }}
                                    </span>

                                </div>


                                <!-- Paid -->

                                <div
                                    class="
                                        mt-3
                                        flex
                                        justify-between
                                        py-2
                                        text-sm
                                    "
                                >

                                    <span class="text-gray-600">
                                        Paid Amount
                                    </span>

                                    <span
                                        class="
                                            font-semibold
                                            text-emerald-700
                                        "
                                    >
                                        {{
                                            formatCurrency(
                                                paidAmount()
                                            )
                                        }}
                                    </span>

                                </div>


                                <!-- Outstanding -->

                                <div
                                    class="
                                        flex
                                        justify-between
                                        border-t
                                        border-gray-200
                                        pt-3
                                        text-sm
                                    "
                                >

                                    <span
                                        class="
                                            font-semibold
                                            text-gray-700
                                        "
                                    >
                                        Outstanding
                                    </span>

                                    <span
                                        class="
                                            font-semibold
                                            text-red-700
                                        "
                                    >
                                        {{
                                            formatCurrency(
                                                outstandingAmount()
                                            )
                                        }}
                                    </span>

                                </div>

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
                                bg-white
                                p-5
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
                        <!-- Warning -->
                        <!-- ========================================= -->

                        <div
                            class="
                                mt-6
                                rounded-xl
                                border
                                border-amber-200
                                bg-amber-50
                                p-4
                            "
                        >

                            <div
                                class="
                                    text-sm
                                    font-semibold
                                    text-amber-800
                                "
                            >
                                Before posting
                            </div>

                            <p
                                class="
                                    mt-1
                                    text-sm
                                    leading-6
                                    text-amber-700
                                "
                            >
                                Please make sure the supplier invoice
                                number, invoice date, due date,
                                supplier, source goods receipt,
                                quantities, prices, tax, and total
                                amount are correct. Posting this
                                purchase invoice will finalize the
                                document and make it ready for the
                                next accounts payable process.
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
                        @click="closePost"
                    >
                        Cancel
                    </BaseButton>


                    <BaseButton
                        type="button"
                        variant="success"
                        :loading="loading"
                        :disabled="
                            !purchaseInvoice ||
                            !purchaseInvoice.details?.length ||
                            loading
                        "
                        @click="confirmPost"
                    >
                        Post Purchase Invoice
                    </BaseButton>

                </div>

            </div>

        </div>

    </Teleport>

</template>