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

    purchaseReturn: {
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

    return props.purchaseReturn?.details ?? []

})


const inventoryMovements = computed(() => {

    return props.purchaseReturn?.inventory_movements
        ?? props.purchaseReturn?.inventoryMovements
        ?? []

})


/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

const totalItems = computed(() => {

    return details.value.length

})


const totalReceivedQuantity = computed(() => {

    return details.value.reduce(

        (total, detail) =>

            total +
            Number(
                detail.received_qty || 0
            ),

        0

    )

})


const totalReturnedQuantity = computed(() => {

    return details.value.reduce(

        (total, detail) =>

            total +
            Number(
                detail.returned_qty || 0
            ),

        0

    )

})


const totalCost = computed(() => {

    return details.value.reduce(

        (total, detail) =>

            total +
            Number(
                detail.total_cost || 0
            ),

        0

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
            currency: 'IDR',
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
            ?.product_variant
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
| Inventory Helpers
|--------------------------------------------------------------------------
*/

function movementSku(movement)
{
    return (
        movement
            ?.product_variant
            ?.sku
        ??
        movement
            ?.productVariant
            ?.sku
        ??
        '-'
    )
}


function movementProductName(movement)
{
    return (
        movement
            ?.product_variant
            ?.product
            ?.name
        ??
        movement
            ?.productVariant
            ?.product
            ?.name
        ??
        movement
            ?.product_variant
            ?.name
        ??
        movement
            ?.productVariant
            ?.name
        ??
        '-'
    )
}


function movementUnitName(movement)
{
    return (
        movement
            ?.unit
            ?.name
        ??
        '-'
    )
}


function stockBefore(movement)
{
    const stockAfter =
        Number(
            movement.balance_qty || 0
        )

    const stockIn =
        Number(
            movement.qty_in || 0
        )

    const stockOut =
        Number(
            movement.qty_out || 0
        )

    return (
        stockAfter -
        stockIn +
        stockOut
    )
}

</script>


<template>

    <BaseModal
        :show="show"
        title="Purchase Return Detail"
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
                        Loading purchase return...
                    </span>

                </div>

            </div>


            <!-- ===================================================== -->
            <!-- Content -->
            <!-- ===================================================== -->

            <div
                v-else-if="purchaseReturn"
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
                                Purchase return transaction information.
                            </p>

                        </div>


                        <StatusBadge
                            :status="
                                purchaseReturn.status
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

                        <!-- Return Number -->

                        <div>

                            <div
                                class="
                                    text-xs
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
                                    text-gray-500
                                "
                            >
                                Return Date
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
                                        purchaseReturn.return_date
                                    )
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
                                    purchaseReturn
                                        .purchase_order
                                        ?.number
                                    ??
                                    purchaseReturn
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
                                    purchaseReturn
                                        .goods_receipt
                                        ?.grn_number
                                    ??
                                    purchaseReturn
                                        .goodsReceipt
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
                                    purchaseReturn
                                        .supplier
                                        ?.name
                                    ??
                                    purchaseReturn
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
                                    purchaseReturn
                                        .branch
                                        ?.name
                                    ??
                                    purchaseReturn
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
                                    purchaseReturn
                                        .warehouse
                                        ?.name
                                    ??
                                    purchaseReturn
                                        .warehouse
                                        ?.label
                                    ??
                                    '-'
                                }}
                            </div>

                        </div>

                    </div>

                </section>


                <!-- ================================================= -->
                <!-- Cancellation Information -->
                <!-- ================================================= -->

                <section
                    v-if="
                        purchaseReturn.status === 'Cancelled'
                    "
                    class="
                        rounded-xl
                        border
                        border-gray-200
                        bg-gray-50
                        p-5
                    "
                >

                    <h3
                        class="
                            text-base
                            font-semibold
                            text-gray-800
                        "
                    >
                        Cancellation Information
                    </h3>


                    <div class="mt-4 space-y-3">

                        <div>

                            <div class="text-xs text-gray-500">
                                Cancellation Reason
                            </div>

                            <div
                                class="
                                    mt-1
                                    whitespace-pre-line
                                    text-sm
                                    font-medium
                                    text-gray-900
                                "
                            >
                                {{
                                    purchaseReturn
                                        .cancel_reason
                                    ?? '-'
                                }}
                            </div>

                        </div>


                        <div
                            class="
                                grid
                                grid-cols-1
                                gap-4
                                md:grid-cols-2
                            "
                        >

                            <div>

                                <div class="text-xs text-gray-500">
                                    Cancelled At
                                </div>

                                <div
                                    class="
                                        mt-1
                                        text-sm
                                        text-gray-900
                                    "
                                >
                                    {{
                                        formatDateTime(
                                            purchaseReturn
                                                .cancelled_at
                                        )
                                    }}
                                </div>

                            </div>


                            <div>

                                <div class="text-xs text-gray-500">
                                    Cancelled By
                                </div>

                                <div
                                    class="
                                        mt-1
                                        text-sm
                                        text-gray-900
                                    "
                                >
                                    {{
                                        purchaseReturn
                                            .canceller
                                            ?.name
                                        ?? '-'
                                    }}
                                </div>

                            </div>

                        </div>

                    </div>

                </section>


                <!-- ================================================= -->
                <!-- Purchase Return Details -->
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
                            Purchase Return Details
                        </h3>

                        <p
                            class="
                                mt-1
                                text-sm
                                text-gray-500
                            "
                        >
                            Products returned to the supplier.
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
                                    min-w-[1100px]
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
                                            Received Qty
                                        </th>

                                        <th
                                            class="
                                                px-4
                                                py-3
                                                text-right
                                            "
                                        >
                                            Returned Qty
                                        </th>

                                        <th
                                            class="
                                                px-4
                                                py-3
                                                text-right
                                            "
                                        >
                                            Unit Cost
                                        </th>

                                        <th
                                            class="
                                                px-4
                                                py-3
                                                text-right
                                            "
                                        >
                                            Total Cost
                                        </th>

                                        <th class="px-4 py-3">
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


                                        <!-- Received -->

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
                                                    detail.received_qty
                                                )
                                            }}
                                        </td>


                                        <!-- Returned -->

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
                                                text-gray-700
                                            "
                                        >
                                            {{
                                                formatCurrency(
                                                    detail.unit_cost
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
                                                    detail.total_cost
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


                                    <tr
                                        v-if="
                                            !details.length
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
                                            No purchase return
                                            details found.
                                        </td>

                                    </tr>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </section>


                <!-- ================================================= -->
                <!-- Stock Impact -->
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
                            Stock Impact
                        </h3>

                        <p
                            class="
                                mt-1
                                text-sm
                                text-gray-500
                            "
                        >
                            Inventory impact generated by this
                            purchase return.
                        </p>

                    </div>


                    <div
                        v-if="
                            inventoryMovements.length
                        "
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
                                    min-w-[1000px]
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
                                            Stock Before
                                        </th>

                                        <th
                                            class="
                                                px-4
                                                py-3
                                                text-right
                                            "
                                        >
                                            Stock In
                                        </th>

                                        <th
                                            class="
                                                px-4
                                                py-3
                                                text-right
                                            "
                                        >
                                            Stock Out
                                        </th>

                                        <th
                                            class="
                                                px-4
                                                py-3
                                                text-right
                                            "
                                        >
                                            Stock After
                                        </th>

                                        <th
                                            class="
                                                px-4
                                                py-3
                                                text-right
                                            "
                                        >
                                            Unit Cost
                                        </th>

                                        <th
                                            class="
                                                px-4
                                                py-3
                                                text-right
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
                                        v-for="(
                                            movement,
                                            index
                                        ) in inventoryMovements"

                                        :key="
                                            movement.id
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
                                                    movementSku(
                                                        movement
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
                                                    movementProductName(
                                                        movement
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
                                                movementUnitName(
                                                    movement
                                                )
                                            }}
                                        </td>


                                        <!-- Stock Before -->

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
                                                    stockBefore(
                                                        movement
                                                    )
                                                )
                                            }}
                                        </td>


                                        <!-- Stock In -->

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
                                            <span
                                                v-if="
                                                    Number(
                                                        movement.qty_in
                                                    ) > 0
                                                "
                                            >
                                                +{{
                                                    formatNumber(
                                                        movement.qty_in
                                                    )
                                                }}
                                            </span>

                                            <span
                                                v-else
                                                class="
                                                    text-gray-400
                                                "
                                            >
                                                -
                                            </span>

                                        </td>


                                        <!-- Stock Out -->

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
                                            <span
                                                v-if="
                                                    Number(
                                                        movement.qty_out
                                                    ) > 0
                                                "
                                            >
                                                -{{
                                                    formatNumber(
                                                        movement.qty_out
                                                    )
                                                }}
                                            </span>

                                            <span
                                                v-else
                                                class="
                                                    text-gray-400
                                                "
                                            >
                                                -
                                            </span>

                                        </td>


                                        <!-- Stock After -->

                                        <td
                                            class="
                                                whitespace-nowrap
                                                px-4
                                                py-3
                                                text-right
                                                text-sm
                                                font-semibold
                                                text-blue-700
                                            "
                                        >
                                            {{
                                                formatNumber(
                                                    movement.balance_qty
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
                                                text-gray-700
                                            "
                                        >
                                            {{
                                                formatCurrency(
                                                    movement.unit_cost
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
                                                    movement.total_cost
                                                )
                                            }}
                                        </td>

                                    </tr>

                                </tbody>

                            </table>

                        </div>

                    </div>


                    <div
                        v-else
                        class="
                            rounded-xl
                            border
                            border-gray-200
                            bg-gray-50
                            px-4
                            py-8
                            text-center
                        "
                    >

                        <div
                            class="
                                text-sm
                                font-medium
                                text-gray-600
                            "
                        >
                            No Stock Impact
                        </div>

                        <div
                            class="
                                mt-1
                                text-xs
                                text-gray-500
                            "
                        >
                            No inventory movement has been
                            recorded for this purchase return.
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


                        <!-- Total Received -->

                        <div
                            class="
                                flex
                                justify-between
                                py-2
                                text-sm
                            "
                        >

                            <span class="text-gray-600">
                                Total Received Quantity
                            </span>

                            <span class="font-semibold">
                                {{
                                    formatNumber(
                                        totalReceivedQuantity
                                    )
                                }}
                            </span>

                        </div>


                        <!-- Total Returned -->

                        <div
                            class="
                                flex
                                justify-between
                                py-2
                                text-sm
                            "
                        >

                            <span class="text-gray-600">
                                Total Returned Quantity
                            </span>

                            <span
                                class="
                                    font-semibold
                                    text-red-700
                                "
                            >
                                {{
                                    formatNumber(
                                        totalReturnedQuantity
                                    )
                                }}
                            </span>

                        </div>


                        <!-- Total Cost -->

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
                                Total Cost
                            </span>

                            <span
                                class="
                                    font-semibold
                                    text-gray-900
                                "
                            >
                                {{
                                    formatCurrency(
                                        totalCost
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
                        purchaseReturn.remarks
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
                            purchaseReturn.remarks
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
                            Purchase return workflow history.
                        </p>

                    </div>


                    <WorkflowTimeline
                        :activities="
                            purchaseReturn.activities
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
                            purchase return.
                        </p>

                    </div>


                    <AuditTrail
                        :activities="
                            purchaseReturn.activities
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
                Purchase return data is not available.
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