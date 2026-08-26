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

    goodsReceipt: {
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


/*
|--------------------------------------------------------------------------
| Details
|--------------------------------------------------------------------------
*/

const details = computed(() => {

    return props.goodsReceipt?.details ?? []

})


/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

const totalItems = computed(() => {

    return details.value.length

})


const totalOrdered = computed(() => {

    return details.value.reduce(

        (
            total,
            detail
        ) =>

            total +
            Number(
                detail.ordered_qty ?? 0
            ),

        0

    )

})


const totalReceived = computed(() => {

    return details.value.reduce(

        (
            total,
            detail
        ) =>

            total +
            Number(
                detail.received_qty ?? 0
            ),

        0

    )

})


const totalRejected = computed(() => {

    return details.value.reduce(

        (
            total,
            detail
        ) =>

            total +
            Number(
                detail.rejected_qty ?? 0
            ),

        0

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
            ?.productVariant
            ?.name
        ??
        detail
            ?.product_variant
            ?.name
        ??
        '-'
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
                            Submit Goods Receipt
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
                            Review the goods receipt before
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
                        v-if="goodsReceipt"
                    >

                        <!-- ========================================= -->
                        <!-- GRN Information -->
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

                                <!-- GRN Number -->

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
                                        GRN Number
                                    </div>

                                    <div
                                        class="
                                            mt-1
                                            font-semibold
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            goodsReceipt.grn_number
                                            ?? '-'
                                        }}
                                    </div>

                                </div>


                                <!-- Receipt Date -->

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
                                        Receipt Date
                                    </div>

                                    <div
                                        class="
                                            mt-1
                                            font-medium
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            goodsReceipt.receipt_date
                                                ? formatDate(
                                                    goodsReceipt.receipt_date
                                                )
                                                : '-'
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
                                            goodsReceipt
                                                .purchaseOrder
                                                ?.number
                                            ??
                                            goodsReceipt
                                                .purchase_order
                                                ?.number
                                            ??
                                            '-'
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
                                            goodsReceipt
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
                                        goodsReceipt
                                            .warehouse
                                            ?.name
                                        ?? '-'
                                    }}
                                </div>

                            </div>


                            <!-- Supplier DO -->

                            <div
                                v-if="
                                    goodsReceipt
                                        .supplier_do_number
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
                                    Supplier DO Number
                                </div>

                                <div
                                    class="
                                        mt-1
                                        font-semibold
                                        text-gray-900
                                    "
                                >
                                    {{
                                        goodsReceipt
                                            .supplier_do_number
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
                                        Receipt Details
                                    </h3>

                                    <p
                                        class="
                                            mt-0.5
                                            text-xs
                                            text-gray-500
                                        "
                                    >
                                        Review received and rejected quantities.
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
                                        min-w-[900px]
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
                                                Ordered
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
                                                Received
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
                                                Rejected
                                            </th>

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
                                                Remarks
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


                                            <!-- Ordered -->

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
                                                        detail.ordered_qty
                                                    )
                                                }}
                                            </td>


                                            <!-- Received -->

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
                                                        detail.received_qty
                                                    )
                                                }}
                                            </td>


                                            <!-- Rejected -->

                                            <td
                                                class="
                                                    whitespace-nowrap
                                                    px-4
                                                    py-3
                                                    text-right
                                                    text-sm
                                                    font-semibold
                                                    text-gray-700
                                                "
                                            >
                                                {{
                                                    formatNumber(
                                                        detail.rejected_qty
                                                    )
                                                }}
                                            </td>


                                            <!-- Remarks -->

                                            <td
                                                class="
                                                    px-4
                                                    py-3
                                                    text-sm
                                                    text-gray-700
                                                "
                                            >
                                                {{
                                                    detail.remarks
                                                    ?? '-'
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
                                                colspan="6"
                                                class="
                                                    px-4
                                                    py-8
                                                    text-center
                                                    text-sm
                                                    text-gray-500
                                                "
                                            >
                                                No goods receipt details found.
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
                                goodsReceipt.remarks
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
                                    goodsReceipt.remarks
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
                                        Ordered Quantity
                                    </span>

                                    <span
                                        class="
                                            font-semibold
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            formatNumber(
                                                totalOrdered
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
                                        Received Quantity
                                    </span>

                                    <span
                                        class="
                                            font-semibold
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            formatNumber(
                                                totalReceived
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
                                        Rejected Quantity
                                    </span>

                                    <span
                                        class="
                                            font-semibold
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            formatNumber(
                                                totalRejected
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
                                This goods receipt will be sent
                                for approval after submission.
                                Stock will not be updated until
                                the goods receipt is posted.
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
                        Goods receipt data is not available.
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
                            !goodsReceipt ||
                            !details.length ||
                            loading
                        "
                        @click="confirmSubmit"
                    >
                        Submit Goods Receipt
                    </BaseButton>

                </div>

            </div>

        </div>

    </Teleport>

</template>