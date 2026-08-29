<script setup>

import { computed } from 'vue'

import WorkflowTimeline
    from '@/Components/Workflow/WorkflowTimeline.vue'

import BaseModal
    from '@/Components/Modal/BaseModal.vue'

import BaseButton
    from '@/Components/Button/BaseButton.vue'

import StatusBadge
    from '@/Components/Display/StatusBadge.vue'

import AuditTrail
    from '@/Components/Workflow/AuditTrail.vue'


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
])


/*
|--------------------------------------------------------------------------
| Computed
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


const totalInvoicedQuantity = computed(() => {

    return details.value.reduce(

        (total, detail) =>

            total +
            Number(
                detail.invoiced_qty || 0
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

        (total, detail) =>

            total +
            Number(
                detail.subtotal || 0
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

        (total, detail) =>

            total +
            Number(
                detail.discount_amount || 0
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

        (total, detail) =>

            total +
            Number(
                detail.tax_amount || 0
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


const paidAmount = computed(() => {

    return Number(
        props.purchaseInvoice?.paid_amount || 0
    )

})


const outstandingAmount = computed(() => {

    if (
        props.purchaseInvoice?.outstanding_amount !==
        undefined &&
        props.purchaseInvoice?.outstanding_amount !==
        null
    ) {

        return Number(
            props.purchaseInvoice.outstanding_amount
        )

    }


    return Math.max(
        0,
        grandTotal.value -
        paidAmount.value
    )

})


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
        Number(value || 0)
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
        Number(value || 0)
    )
}


function formatDate(value)
{
    if (!value) {

        return '-'

    }


    return new Intl.DateTimeFormat(
        'id-ID',
        {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        }
    ).format(
        new Date(value)
    )
}


function formatDateTime(value)
{
    if (!value) {

        return '-'

    }


    return new Intl.DateTimeFormat(
        'id-ID',
        {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        }
    ).format(
        new Date(value)
    )
}


/*
|--------------------------------------------------------------------------
| Product Helpers
|--------------------------------------------------------------------------
*/

function productSku(detail)
{
    return (
        detail
            ?.product_variant
            ?.sku
        ??
        detail
            ?.productVariant
            ?.sku
        ??
        detail
            ?.variant
            ?.sku
        ??
        '-'
    )
}


function productName(detail)
{
    return (
        detail
            ?.product_variant
            ?.product
            ?.name
        ??
        detail
            ?.productVariant
            ?.product
            ?.name
        ??
        detail
            ?.variant
            ?.product
            ?.name
        ??
        detail
            ?.product_variant
            ?.name
        ??
        detail
            ?.productVariant
            ?.name
        ??
        detail
            ?.variant
            ?.name
        ??
        '-'
    )
}


function unitName(detail)
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

function unitPrice(detail)
{
    return Number(
        detail?.unit_price || 0
    )
}


function detailSubtotal(detail)
{
    return Number(
        detail?.subtotal || 0
    )
}


function detailDiscount(detail)
{
    return Number(
        detail?.discount_amount || 0
    )
}


function detailTax(detail)
{
    return Number(
        detail?.tax_amount || 0
    )
}


function detailTotal(detail)
{
    if (
        detail?.total_amount !== undefined &&
        detail?.total_amount !== null
    ) {

        return Number(
            detail.total_amount
        )

    }


    return (
        detailSubtotal(detail) -
        detailDiscount(detail) +
        detailTax(detail)
    )
}

</script>


<template>

    <BaseModal
        :show="show"
        title="Purchase Invoice Detail"
        size="xl"
        @close="emit('close')"
    >

        <div class="space-y-6">

            <!-- ===================================================== -->
            <!-- Loading -->
            <!-- ===================================================== -->

            <div
                v-if="loading"
                class="
                    flex
                    min-h-[300px]
                    items-center
                    justify-center
                "
            >

                <div
                    class="
                        flex
                        flex-col
                        items-center
                        gap-3
                        text-gray-500
                    "
                >

                    <svg
                        class="
                            h-8
                            w-8
                            animate-spin
                        "
                        viewBox="0 0 24 24"
                        fill="none"
                    >

                        <circle
                            cx="12"
                            cy="12"
                            r="10"
                            stroke="currentColor"
                            stroke-width="4"
                            opacity=".2"
                        />

                        <path
                            d="M22 12a10 10 0 0 0-10-10"
                            stroke="currentColor"
                            stroke-width="4"
                        />

                    </svg>

                    <span class="text-sm">
                        Loading purchase invoice...
                    </span>

                </div>

            </div>


            <!-- ===================================================== -->
            <!-- Content -->
            <!-- ===================================================== -->

            <div
                v-else-if="purchaseInvoice"
                class="space-y-6"
            >

                <!-- ================================================= -->
                <!-- Document Information -->
                <!-- ================================================= -->

                <section>

                    <div
                        class="
                            mb-4
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
                                Document Information
                            </h3>

                            <p
                                class="
                                    mt-1
                                    text-sm
                                    text-gray-500
                                "
                            >
                                Purchase invoice transaction information.
                            </p>

                        </div>


                        <StatusBadge
                            :status="
                                purchaseInvoice.status
                            "
                        />

                    </div>


                    <div
                        class="
                            grid
                            grid-cols-1
                            gap-4
                            rounded-xl
                            border
                            border-gray-200
                            bg-gray-50
                            p-5
                            md:grid-cols-2
                            lg:grid-cols-3
                        "
                    >

                        <!-- Internal Number -->

                        <div>

                            <div
                                class="
                                    text-xs
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
                                    formatDate(
                                        purchaseInvoice.invoice_date
                                    )
                                }}
                            </div>

                        </div>


                        <!-- Due Date -->

                        <div>

                            <div
                                class="
                                    text-xs
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
                                    formatDate(
                                        purchaseInvoice.due_date
                                    )
                                }}
                            </div>

                        </div>


                        <!-- Supplier -->

                        <div>

                            <div
                                class="
                                    text-xs
                                    text-gray-500
                                "
                            >
                                Supplier
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
                                        .supplier
                                        ?.name
                                    ??
                                    purchaseInvoice
                                        .supplier
                                        ?.label
                                    ??
                                    '-'
                                }}
                            </div>

                        </div>


                        <!-- Branch -->

                        <div>

                            <div
                                class="
                                    text-xs
                                    text-gray-500
                                "
                            >
                                Branch
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
                                        .branch
                                        ?.name
                                    ??
                                    purchaseInvoice
                                        .branch
                                        ?.label
                                    ??
                                    '-'
                                }}
                            </div>

                        </div>


                        <!-- Warehouse -->

                        <div>

                            <div
                                class="
                                    text-xs
                                    text-gray-500
                                "
                            >
                                Warehouse
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
                                        .warehouse
                                        ?.name
                                    ??
                                    purchaseInvoice
                                        .warehouse
                                        ?.label
                                    ??
                                    '-'
                                }}
                            </div>

                        </div>


                        <!-- Purchase Order -->

                        <div>

                            <div
                                class="
                                    text-xs
                                    text-gray-500
                                "
                            >
                                Purchase Order
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
                                        .purchase_order
                                        ?.number
                                    ??
                                    purchaseInvoice
                                        .purchaseOrder
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
                                    text-gray-500
                                "
                            >
                                Goods Receipt
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
                                        .goods_receipt
                                        ?.grn_number
                                    ??
                                    purchaseInvoice
                                        .goodsReceipt
                                        ?.grn_number
                                    ??
                                    '-'
                                }}
                            </div>

                        </div>

                    </div>

                </section>


                <!-- ================================================= -->
                <!-- Payment & Tax Information -->
                <!-- ================================================= -->

                <section>

                    <div class="mb-4">

                        <h3
                            class="
                                text-base
                                font-semibold
                                text-gray-900
                            "
                        >
                            Payment &amp; Tax Information
                        </h3>

                        <p
                            class="
                                mt-1
                                text-sm
                                text-gray-500
                            "
                        >
                            Payment terms, currency, tax and
                            payable information.
                        </p>

                    </div>


                    <div
                        class="
                            grid
                            grid-cols-1
                            gap-4
                            rounded-xl
                            border
                            border-gray-200
                            bg-gray-50
                            p-5
                            md:grid-cols-2
                            lg:grid-cols-4
                        "
                    >

                        <!-- Payment Term -->

                        <div>

                            <div class="text-xs text-gray-500">
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
                                        .payment_term
                                        ?.name
                                    ??
                                    purchaseInvoice
                                        .paymentTerm
                                        ?.name
                                    ??
                                    '-'
                                }}
                            </div>

                        </div>


                        <!-- Currency -->

                        <div>

                            <div class="text-xs text-gray-500">
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


                        <!-- Tax -->

                        <div>

                            <div class="text-xs text-gray-500">
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


                        <!-- Tax Amount -->

                        <div>

                            <div class="text-xs text-gray-500">
                                Tax Amount
                            </div>

                            <div
                                class="
                                    mt-1
                                    font-medium
                                    text-gray-900
                                "
                            >
                                {{
                                    formatCurrency(
                                        taxAmount
                                    )
                                }}
                            </div>

                        </div>

                    </div>

                </section>


                <!-- ================================================= -->
                <!-- Purchase Invoice Details -->
                <!-- ================================================= -->

                <section>

                    <div class="mb-4">

                        <h3
                            class="
                                text-base
                                font-semibold
                                text-gray-900
                            "
                        >
                            Purchase Invoice Details
                        </h3>

                        <p
                            class="
                                mt-1
                                text-sm
                                text-gray-500
                            "
                        >
                            Products and amounts recorded from the
                            supplier invoice.
                        </p>

                    </div>


                    <div
                        class="
                            overflow-hidden
                            rounded-xl
                            border
                            border-gray-200
                        "
                    >

                        <div class="overflow-x-auto">

                            <table
                                class="
                                    min-w-[1250px]
                                    w-full
                                "
                            >

                                <thead
                                    class="
                                        bg-gray-50
                                        text-left
                                        text-xs
                                        font-semibold
                                        uppercase
                                        tracking-wider
                                        text-gray-500
                                    "
                                >

                                    <tr>

                                        <th class="px-4 py-3">
                                            Product Variant
                                        </th>

                                        <th class="px-4 py-3">
                                            Unit
                                        </th>

                                        <th
                                            class="
                                                px-4
                                                py-3
                                                text-right
                                            "
                                        >
                                            Invoiced Qty
                                        </th>

                                        <th
                                            class="
                                                px-4
                                                py-3
                                                text-right
                                            "
                                        >
                                            Unit Price
                                        </th>

                                        <th
                                            class="
                                                px-4
                                                py-3
                                                text-right
                                            "
                                        >
                                            Subtotal
                                        </th>

                                        <th
                                            class="
                                                px-4
                                                py-3
                                                text-right
                                            "
                                        >
                                            Discount
                                        </th>

                                        <th
                                            class="
                                                px-4
                                                py-3
                                                text-right
                                            "
                                        >
                                            Tax
                                        </th>

                                        <th
                                            class="
                                                px-4
                                                py-3
                                                text-right
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
                                        ) in details"

                                        :key="
                                            detail.id
                                            ?? index
                                        "

                                        class="hover:bg-gray-50"
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
                                                    productSku(
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
                                                    productName(
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
                                                unitName(
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
                                                text-gray-700
                                            "
                                        >
                                            {{
                                                formatCurrency(
                                                    unitPrice(
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
                                                    detailSubtotal(
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
                                                    detailDiscount(
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
                                                    detailTax(
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
                                                    detailTotal(
                                                        detail
                                                    )
                                                )
                                            }}
                                        </td>

                                    </tr>


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
                                            No purchase invoice
                                            details found.
                                        </td>

                                    </tr>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </section>


                <!-- ================================================= -->
                <!-- Summary -->
                <!-- ================================================= -->

                <section
                    class="
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
                                {{ totalItems }}
                            </span>

                        </div>


                        <!-- Invoiced Quantity -->

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
                                        totalInvoicedQuantity
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
                                        subtotal
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
                                        discountAmount
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
                                        taxAmount
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
                                        grandTotal
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
                                        paidAmount
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
                                        outstandingAmount
                                    )
                                }}
                            </span>

                        </div>

                    </div>

                </section>


                <!-- ================================================= -->
                <!-- Remarks -->
                <!-- ================================================= -->

                <section
                    v-if="
                        purchaseInvoice.remarks
                    "
                    class="
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

                </section>


                <!-- ================================================= -->
                <!-- Workflow Timeline -->
                <!-- ================================================= -->

                <section>

                    <div class="mb-4">

                        <h3
                            class="
                                text-base
                                font-semibold
                                text-gray-900
                            "
                        >
                            Workflow Timeline
                        </h3>

                        <p
                            class="
                                mt-1
                                text-sm
                                text-gray-500
                            "
                        >
                            Purchase invoice workflow history.
                        </p>

                    </div>


                    <WorkflowTimeline
                        :activities="
                            purchaseInvoice.activities
                            ?? []
                        "
                    />

                </section>


                <!-- ================================================= -->
                <!-- Audit Trail -->
                <!-- ================================================= -->

                <section>

                    <div class="mb-4">

                        <h3
                            class="
                                text-base
                                font-semibold
                                text-gray-900
                            "
                        >
                            Audit Trail
                        </h3>

                        <p
                            class="
                                mt-1
                                text-sm
                                text-gray-500
                            "
                        >
                            Complete activity history for this
                            purchase invoice.
                        </p>

                    </div>


                    <AuditTrail
                        :activities="
                            purchaseInvoice.activities
                            ?? []
                        "
                    />

                </section>

            </div>


            <!-- ===================================================== -->
            <!-- No Data -->
            <!-- ===================================================== -->

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


        <!-- ========================================================= -->
        <!-- Footer -->
        <!-- ========================================================= -->

        <template #footer>

            <BaseButton
                variant="secondary"
                @click="emit('close')"
            >
                Close
            </BaseButton>

        </template>

    </BaseModal>

</template>