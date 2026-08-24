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

    purchaseOrder: {
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

function closeConfirm()
{
    emit('close')
}

function confirmPurchaseOrder()
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

    return props.purchaseOrder?.details ?? []

})


/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

const totalItems = computed(() => {

    return details.value.length

})


const totalQuantity = computed(() => {

    return details.value.reduce(

        (
            total,
            detail
        ) =>

            total +
            Number(
                detail.qty ?? 0
            ),

        0

    )

})


const subtotal = computed(() => {

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

    return details.value.reduce(

        (
            total,
            detail
        ) =>

            total +
            Number(
                detail.total ?? 0
            ),

        0

    )

})


/*
|--------------------------------------------------------------------------
| Product Name
|--------------------------------------------------------------------------
*/

const getProductName = (
    detail
) => {

    return (

        detail
            ?.product_variant
            ?.product
            ?.name

        ??

        detail
            ?.product_variant
            ?.name

        ??

        '-'

    )

}


/*
|--------------------------------------------------------------------------
| Product SKU
|--------------------------------------------------------------------------
*/

const getProductSku = (
    detail
) => {

    return (
        detail
            ?.product_variant
            ?.sku
        ?? '-'
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
            @click.self="closeConfirm"
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
                            Confirm Purchase Order
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
                            Review the purchase order before
                            Confirm it for supplier.
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
                        @click="closeConfirm"
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
                        v-if="purchaseOrder"
                    >

                        <!-- ========================================= -->
                        <!-- PO Information -->
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

                                <!-- Number -->

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
                                        Number
                                    </div>

                                    <div
                                        class="
                                            mt-1
                                            font-semibold
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            purchaseOrder.number
                                            ?? '-'
                                        }}
                                    </div>

                                </div>


                                <!-- Order Date -->

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
                                        Order Date
                                    </div>

                                    <div
                                        class="
                                            mt-1
                                            font-medium
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            purchaseOrder.order_date
                                                ? formatDate(
                                                    purchaseOrder.order_date
                                                )
                                                : '-'
                                        }}
                                    </div>

                                </div>


                                <!-- Required Date -->

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
                                        Required Date
                                    </div>

                                    <div
                                        class="
                                            mt-1
                                            font-medium
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            purchaseOrder.required_date
                                                ? formatDate(
                                                    purchaseOrder.required_date
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
                                            purchaseOrder
                                                .supplier
                                                ?.name
                                            ?? '-'
                                        }}
                                    </div>

                                </div>

                            </div>


                            <!-- Location -->

                            <div
                                class="
                                    mt-4
                                    border-t
                                    border-gray-200
                                    pt-4
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
                                    Location
                                </div>

                                <div
                                    class="
                                        mt-1
                                        flex
                                        flex-wrap
                                        items-center
                                        gap-x-2
                                        gap-y-1
                                    "
                                >

                                    <span
                                        class="
                                            font-medium
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            purchaseOrder
                                                .branch
                                                ?.name
                                            ?? '-'
                                        }}
                                    </span>

                                    <span
                                        class="
                                            text-gray-400
                                        "
                                    >
                                        /
                                    </span>

                                    <span
                                        class="
                                            text-gray-700
                                        "
                                    >
                                        {{
                                            purchaseOrder
                                                .warehouse
                                                ?.name
                                            ?? '-'
                                        }}
                                    </span>

                                </div>

                            </div>


                            <!-- Purchase Request -->

                            <div
                                v-if="
                                    purchaseOrder
                                        .purchase_request
                                "
                                class="
                                    mt-4
                                    border-t
                                    border-gray-200
                                    pt-4
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
                                    Purchase Request
                                </div>

                                <div
                                    class="
                                        mt-1
                                        font-semibold
                                        text-gray-900
                                    "
                                >
                                    {{
                                        purchaseOrder
                                            .purchase_request
                                            ?.number
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

                                    <h3
                                        class="
                                            text-base
                                            font-semibold
                                            text-gray-900
                                        "
                                    >
                                        Order Details
                                    </h3>

                                    <p
                                        class="
                                            mt-0.5
                                            text-xs
                                            text-gray-500
                                        "
                                    >
                                        Products, quantities and pricing.
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
                                        min-w-[1050px]
                                        w-full
                                        divide-y
                                        divide-gray-200
                                    "
                                >

                                    <thead
                                        class="
                                            bg-gray-50
                                        "
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
                                                    w-24
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
                                                Qty
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
                                                Amount
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
                                                    detail
                                                        .unit
                                                        ?.name
                                                    ?? '-'
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
                                                    font-semibold
                                                    text-gray-900
                                                "
                                            >
                                                {{
                                                    formatNumber(
                                                        detail.qty
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
                                                        detail.unit_price
                                                    )
                                                }}
                                            </td>


                                            <!-- Amount -->

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
                                                        Number(
                                                            detail.qty ?? 0
                                                        ) *
                                                        Number(
                                                            detail.unit_price ?? 0
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
                                                        detail.discount_amount
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
                                                        detail.tax_amount
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
                                                        detail.total
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
                                                No purchase order details found.
                                            </td>

                                        </tr>

                                    </tbody>

                                </table>

                            </div>

                        </div>


                        <!-- ========================================= -->
                        <!-- Description -->
                        <!-- ========================================= -->

                        <div
                            v-if="
                                purchaseOrder.description
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
                                Description
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
                                    purchaseOrder.description
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

                                <!-- Total Items -->

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


                                <!-- Total Quantity -->

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
                                        Total Quantity
                                    </span>

                                    <span
                                        class="
                                            font-semibold
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            formatNumber(
                                                totalQuantity
                                            )
                                        }}
                                    </span>

                                </div>


                                <!-- Subtotal -->

                                <div
                                    class="
                                        flex
                                        justify-between
                                        border-t
                                        border-gray-200
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
                                            font-medium
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


                                <!-- Discount -->

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
                                            font-medium
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


                                <!-- Tax -->

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
                                            font-medium
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

                                    <span
                                        class="text-gray-900"
                                    >
                                        Grand Total
                                    </span>

                                    <span
                                        class="text-gray-900"
                                    >
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
                                Ready to Confirm
                            </div>

                            <p
                                class="
                                    mt-1
                                    text-sm
                                    leading-6
                                    text-blue-700
                                "
                            >
                               This purchase order will be Confirmed after
                                confirmation. Please make sure the products, quantities,
                                supplier, pricing, and other information are correct.
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
                        Purchase order data is not available.
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
                        @click="closeConfirm"
                    >
                        Cancel
                    </BaseButton>


                    <BaseButton
                        type="button"
                        variant="success"
                        :loading="loading"
                        :disabled="
                            !purchaseOrder ||
                            !details.length ||
                            loading
                        "
                        @click="confirmPurchaseOrder"
                    >
                        Confirm Purchase Order
                    </BaseButton>

                </div>

            </div>

        </div>

    </Teleport>

</template>