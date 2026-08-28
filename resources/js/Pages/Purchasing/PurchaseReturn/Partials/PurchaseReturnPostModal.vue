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

    purchaseReturn: {
        type: Object,
        default: null,
    },

    loading: {
        type: Boolean,
        default: false,
    },

})

console.log(
    'POST PURCHASE RETURN:',
    props.purchaseReturn
)

console.log(
    'POST PURCHASE RETURN DETAILS:',
    props.purchaseReturn?.details
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
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }
    ).format(
        Number(value ?? 0)
    )
}


function totalItems()
{
    return (
        props.purchaseReturn?.details?.length
        ?? 0
    )
}


function totalOrderedQuantity()
{
    return (
        props.purchaseReturn?.details?.reduce(
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


function totalReturnableQuantity()
{
    return (
        props.purchaseReturn?.details?.reduce(
            (
                total,
                detail
            ) =>
                total +
                Number(
                    detail.remaining_returnable_qty
                    ??
                    detail.returnable_qty
                    ??
                    0
                ),
            0
        )
        ?? 0
    )
}


function totalReturnedQuantity()
{
    return (
        props.purchaseReturn?.details?.reduce(
            (
                total,
                detail
            ) =>
                total +
                Number(
                    detail.returned_qty ?? 0
                ),
            0
        )
        ?? 0
    )
}


function totalCost()
{
    return (
        props.purchaseReturn?.details?.reduce(
            (
                total,
                detail
            ) => {

                const returnedQty =
                    Number(
                        detail.returned_qty ?? 0
                    )

                const unitCost =
                    Number(
                        detail.unit_cost ?? 0
                    )

                return (
                    total +
                    Number(
                        detail.total_cost
                        ??
                        (
                            returnedQty *
                            unitCost
                        )
                    )
                )

            },
            0
        )
        ?? 0
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
        '-'
    )
}


function getUnitCost(detail)
{
    return Number(
        detail?.unit_cost ?? 0
    )
}


function getTotalCost(detail)
{
    if (
        detail?.total_cost !== undefined &&
        detail?.total_cost !== null
    ) {

        return Number(
            detail.total_cost
        )

    }

    return (
        Number(
            detail?.returned_qty ?? 0
        ) *
        Number(
            detail?.unit_cost ?? 0
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
                            Post Purchase Return
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
                            Review the purchase return before
                            posting it to inventory.
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
                        v-if="purchaseReturn"
                    >

                        <!-- ========================================= -->
                        <!-- Purchase Return Information -->
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

                            <!-- Return Number -->

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
                                    Return Number
                                </div>

                                <div
                                    class="
                                        mt-1
                                        font-semibold
                                        text-gray-900
                                    "
                                >
                                    {{
                                        purchaseReturn.return_number
                                        ?? '-'
                                    }}
                                </div>

                            </div>


                            <!-- Return Date -->

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
                                    Return Date
                                </div>

                                <div
                                    class="
                                        mt-1
                                        font-semibold
                                        text-gray-900
                                    "
                                >
                                    {{
                                        purchaseReturn.return_date
                                            ? formatDate(
                                                purchaseReturn.return_date
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
                                        purchaseReturn
                                            .purchaseOrder
                                            ?.number
                                        ??
                                        purchaseReturn
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
                                        purchaseReturn
                                            .goodsReceipt
                                            ?.grn_number
                                        ??
                                        purchaseReturn
                                            .goods_receipt
                                            ?.grn_number
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
                                        purchaseReturn
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
                                        purchaseReturn
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
                                        purchaseReturn.status
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
                                        Purchase Return Details
                                    </div>

                                    <div
                                        class="
                                            mt-0.5
                                            text-xs
                                            text-gray-500
                                        "
                                    >
                                        Review the products and
                                        quantities that will be
                                        returned to the supplier.
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
                                        min-w-[1100px]
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
                                                Returnable
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
                                                Return Qty
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
                                                Unit Cost
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
                                                Total Cost
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
                                                in purchaseReturn.details
                                            "
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


                                            <!-- Returnable -->

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
                                                        detail.remaining_returnable_qty
                                                        ??
                                                        detail.returnable_qty
                                                        ??
                                                        0
                                                    )
                                                }}
                                            </td>


                                            <!-- Return Qty -->

                                            <td
                                                class="
                                                    whitespace-nowrap
                                                    px-4
                                                    py-3
                                                    text-right
                                                    text-sm
                                                    font-semibold
                                                    text-red-700
                                                "
                                            >
                                                {{
                                                    formatNumber(
                                                        detail.returned_qty
                                                    )
                                                }}
                                            </td>


                                            <!-- Unit Cost -->

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
                                                        getUnitCost(
                                                            detail
                                                        )
                                                    )
                                                }}
                                            </td>


                                            <!-- Total Cost -->

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
                                                        getTotalCost(
                                                            detail
                                                        )
                                                    )
                                                }}
                                            </td>

                                        </tr>


                                        <!-- Empty -->

                                        <tr
                                            v-if="
                                                !purchaseReturn.details?.length
                                            "
                                        >

                                            <td
                                                colspan="7"
                                                class="
                                                    px-4
                                                    py-8
                                                    text-center
                                                    text-sm
                                                    text-gray-500
                                                "
                                            >
                                                No purchase return details found.
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
                                purchaseReturn.remarks
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
                                    purchaseReturn.remarks
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
                                        class="font-semibold"
                                    >
                                        {{
                                            totalItems()
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
                                        Ordered Quantity
                                    </span>

                                    <span
                                        class="font-semibold"
                                    >
                                        {{
                                            formatNumber(
                                                totalOrderedQuantity()
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
                                        Returnable Quantity
                                    </span>

                                    <span
                                        class="font-semibold"
                                    >
                                        {{
                                            formatNumber(
                                                totalReturnableQuantity()
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
                                        Return Quantity
                                    </span>

                                    <span
                                        class="
                                            font-semibold
                                            text-red-700
                                        "
                                    >
                                        {{
                                            formatNumber(
                                                totalReturnedQuantity()
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
                                        Total Return Cost
                                    </span>

                                    <span>
                                        {{
                                            formatCurrency(
                                                totalCost()
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
                                Please make sure the return quantities,
                                unit costs, supplier, warehouse, and
                                source goods receipt are correct.
                                Posting this purchase return will reduce
                                inventory and record the corresponding
                                stock movement.
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
                        Purchase return data is not available.
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
                        variant="danger"
                        :loading="loading"
                        :disabled="
                            !purchaseReturn ||
                            !purchaseReturn.details?.length ||
                            loading
                        "
                        @click="confirmPost"
                    >
                        Post Purchase Return
                    </BaseButton>

                </div>

            </div>

        </div>

    </Teleport>

</template>