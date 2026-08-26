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

function closeApprove()
{
    emit('close')
}


function confirmApprove()
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


function totalItems()
{
    return (
        props.goodsReceipt?.details?.length
        ?? 0
    )
}


function totalOrderedQuantity()
{
    return (
        props.goodsReceipt?.details?.reduce(
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
        ?? 0
    )
}


function totalReceivedQuantity()
{
    return (
        props.goodsReceipt?.details?.reduce(
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
        ?? 0
    )
}


function totalRejectedQuantity()
{
    return (
        props.goodsReceipt?.details?.reduce(
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
        ?? 0
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
                px-4
                py-6
            "
            @click.self="closeApprove"
        >

            <div
                class="
                    flex
                    max-h-[90vh]
                    w-full
                    max-w-5xl
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
                        items-center
                        justify-between
                        border-b
                        border-gray-200
                        px-6
                        py-5
                    "
                >

                    <div>

                        <h2
                            class="
                                text-xl
                                font-semibold
                                text-gray-900
                            "
                        >
                            Review Goods Receipt
                        </h2>

                        <p
                            class="
                                mt-1
                                text-sm
                                text-gray-500
                            "
                        >
                            Review the goods receipt before
                            approving it.
                        </p>

                    </div>


                    <button
                        type="button"
                        class="
                            rounded-lg
                            p-2
                            text-gray-400
                            transition
                            hover:bg-gray-100
                            hover:text-gray-700
                        "
                        @click="closeApprove"
                    >
                        ✕
                    </button>

                </div>


                <!-- ================================================= -->
                <!-- Body -->
                <!-- ================================================= -->

                <div
                    class="
                        flex-1
                        overflow-y-auto
                        p-6
                    "
                >

                    <template
                        v-if="goodsReceipt"
                    >

                        <!-- ========================================= -->
                        <!-- Goods Receipt Information -->
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
                                p-5
                                md:grid-cols-2
                                lg:grid-cols-3
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
                                        font-semibold
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


                            <!-- Supplier DO -->

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
                                    Supplier DO
                                </div>

                                <div
                                    class="
                                        mt-1
                                        font-semibold
                                        text-gray-900
                                    "
                                >
                                    {{
                                        goodsReceipt.supplier_do_number
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
                                        goodsReceipt
                                            .purchase_order
                                            ?.number
                                        ?? '-'
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
                                        goodsReceipt
                                            .warehouse
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
                                    text-base
                                    font-semibold
                                    text-gray-900
                                "
                            >
                                Goods Receipt Details
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
                                        min-w-full
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
                                                in goodsReceipt.details
                                            "
                                            :key="
                                                detail.id
                                                ?? index
                                            "
                                        >

                                            <!-- Product -->

                                            <td
                                                class="
                                                    whitespace-nowrap
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
                                                        detail
                                                            .product_variant
                                                            ?.sku
                                                        ?? '-'
                                                    }}
                                                </div>

                                                <div
                                                    class="
                                                        text-xs
                                                        text-gray-500
                                                    "
                                                >
                                                    {{
                                                        detail
                                                            .product_variant
                                                            ?.product
                                                            ?.name

                                                        ??

                                                        detail
                                                            .product_variant
                                                            ?.name

                                                        ??
                                                        '-'
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
                                                    font-semibold
                                                    text-gray-900
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
                                                    text-emerald-700
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
                                                    text-red-600
                                                "
                                            >
                                                {{
                                                    formatNumber(
                                                        detail.rejected_qty
                                                    )
                                                }}
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

                                    <span
                                        class="
                                            text-gray-600
                                        "
                                    >
                                        Total Items
                                    </span>

                                    <span
                                        class="
                                            font-semibold
                                        "
                                    >
                                        {{
                                            totalItems()
                                        }}
                                    </span>

                                </div>


                                <!-- Total Ordered -->

                                <div
                                    class="
                                        flex
                                        justify-between
                                        py-2
                                        text-sm
                                    "
                                >

                                    <span
                                        class="
                                            text-gray-600
                                        "
                                    >
                                        Total Ordered
                                    </span>

                                    <span
                                        class="
                                            font-semibold
                                        "
                                    >
                                        {{
                                            formatNumber(
                                                totalOrderedQuantity()
                                            )
                                        }}
                                    </span>

                                </div>


                                <!-- Total Received -->

                                <div
                                    class="
                                        flex
                                        justify-between
                                        py-2
                                        text-sm
                                    "
                                >

                                    <span
                                        class="
                                            text-gray-600
                                        "
                                    >
                                        Total Received
                                    </span>

                                    <span
                                        class="
                                            font-semibold
                                            text-emerald-700
                                        "
                                    >
                                        {{
                                            formatNumber(
                                                totalReceivedQuantity()
                                            )
                                        }}
                                    </span>

                                </div>


                                <!-- Total Rejected -->

                                <div
                                    class="
                                        flex
                                        justify-between
                                        py-2
                                        text-sm
                                    "
                                >

                                    <span
                                        class="
                                            text-gray-600
                                        "
                                    >
                                        Total Rejected
                                    </span>

                                    <span
                                        class="
                                            font-semibold
                                            text-red-600
                                        "
                                    >
                                        {{
                                            formatNumber(
                                                totalRejectedQuantity()
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
                                border-emerald-200
                                bg-emerald-50
                                p-4
                            "
                        >

                            <div
                                class="
                                    text-sm
                                    font-semibold
                                    text-emerald-800
                                "
                            >
                                Before approving
                            </div>

                            <p
                                class="
                                    mt-1
                                    text-sm
                                    text-emerald-700
                                "
                            >
                                Please review the purchase order,
                                supplier, received quantities,
                                rejected quantities, and warehouse
                                carefully. Approving this goods receipt
                                will allow it to proceed to posting.
                            </p>

                        </div>

                    </template>

                </div>


                <!-- ================================================= -->
                <!-- Footer -->
                <!-- ================================================= -->

                <div
                    class="
                        flex
                        items-center
                        justify-end
                        gap-3
                        border-t
                        border-gray-200
                        bg-gray-50
                        px-6
                        py-4
                    "
                >

                    <BaseButton
                        type="button"
                        variant="secondary"
                        @click="closeApprove"
                    >
                        Cancel
                    </BaseButton>


                    <BaseButton
                        type="button"
                        variant="success"
                        :loading="loading"
                        :disabled="
                            !goodsReceipt ||
                            loading
                        "
                        @click="confirmApprove"
                    >
                        Approve Goods Receipt
                    </BaseButton>

                </div>

            </div>

        </div>

    </Teleport>

</template>