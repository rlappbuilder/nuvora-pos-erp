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
])


/*
|--------------------------------------------------------------------------
| Computed
|--------------------------------------------------------------------------
*/

const details = computed(() => {

    return props.goodsReceipt?.details ?? []

})


const totalItems = computed(() => {

    return details.value.length

})


const totalOrderedQuantity = computed(() => {

    return details.value.reduce(
        (total, detail) =>
            total +
            Number(detail.ordered_qty || 0),
        0
    )

})


const totalReceivedQuantity = computed(() => {

    return details.value.reduce(
        (total, detail) =>
            total +
            Number(detail.received_qty || 0),
        0
    )

})


const totalRejectedQuantity = computed(() => {

    return details.value.reduce(
        (total, detail) =>
            total +
            Number(detail.rejected_qty || 0),
        0
    )

})
const stockImpact = computed(() => {

    const movements =
        props.goodsReceipt?.inventory_movements
        ?? []

    return movements.map(
        (movement) => {

            const stockIn =
                Number(
                    movement.qty_in || 0
                )

            const stockOut =
                Number(
                    movement.qty_out || 0
                )

            const stockAfter =
                Number(
                    movement.balance_qty || 0
                )

            const stockBefore =
                stockAfter -
                stockIn +
                stockOut

            return {

                id:
                    movement.id,

                productVariant:
                    movement.product_variant
                    ?? movement.productVariant
                    ?? null,

                unit:
                    movement.unit
                    ?? null,

                stockBefore,

                stockIn,

                stockOut,

                stockAfter,

                unitCost:
                    Number(
                        movement.unit_cost || 0
                    ),

                totalCost:
                    Number(
                        movement.total_cost || 0
                    ),

            }

        }
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

</script>


<template>

    <BaseModal
        :show="show"
        title="Goods Receipt Detail"
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
                        Loading goods receipt...
                    </span>

                </div>

            </div>


            <!-- ===================================================== -->
            <!-- Content -->
            <!-- ===================================================== -->

            <div
                v-else-if="goodsReceipt"
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
                                Goods receipt transaction information.
                            </p>

                        </div>


                        <StatusBadge
                            :status="goodsReceipt.status"
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

                        <!-- Number -->

                        <div>

                            <div class="text-xs text-gray-500">
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
                                    goodsReceipt.grn_number
                                    ?? '-'
                                }}
                            </div>

                        </div>


                        <!-- Receipt Date -->

                        <div>

                            <div class="text-xs text-gray-500">
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
                                    formatDate(
                                        goodsReceipt.receipt_date
                                    )
                                }}
                            </div>

                        </div>


                        <!-- Supplier DO Number -->

                        <div>

                            <div class="text-xs text-gray-500">
                                Supplier DO Number
                            </div>

                            <div
                                class="
                                    mt-1
                                    font-medium
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

                            <div class="text-xs text-gray-500">
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
                                    goodsReceipt
                                        .purchase_order
                                        ?.number
                                    ?? '-'
                                }}
                            </div>

                        </div>


                        <!-- Supplier -->

                        <div>

                            <div class="text-xs text-gray-500">
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
                                    goodsReceipt.supplier?.name
                                    ??
                                    goodsReceipt.supplier?.label
                                    ??
                                    '-'
                                }}
                            </div>

                        </div>


                        <!-- Branch -->

                        <div>

                            <div class="text-xs text-gray-500">
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
                                    goodsReceipt.branch?.name
                                    ??
                                    goodsReceipt.branch?.label
                                    ??
                                    '-'
                                }}
                            </div>

                        </div>


                        <!-- Warehouse -->

                        <div>

                            <div class="text-xs text-gray-500">
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
                                    goodsReceipt.warehouse?.name
                                    ??
                                    goodsReceipt.warehouse?.label
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
                        goodsReceipt.status === 'Cancelled'
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
                                    goodsReceipt.cancel_reason
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
                                            goodsReceipt.cancelled_at
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
                                        goodsReceipt
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
                <!-- Goods Receipt Details -->
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
                            Goods Receipt Details
                        </h3>

                        <p
                            class="
                                mt-1
                                text-sm
                                text-gray-500
                            "
                        >
                            Products received in this goods receipt.
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
                                    min-w-[900px]
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
                                            Ordered Qty
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
                                            Rejected Qty
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

                                        <!-- Product Variant -->

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
                                                    detail
                                                        .product_variant
                                                        ?.sku
                                                    ?? '-'
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
                                                detail.unit?.name
                                                ??
                                                detail.unit?.label
                                                ??
                                                '-'
                                            }}
                                        </td>


                                        <!-- Ordered Qty -->

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


                                        <!-- Received Qty -->

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


                                        <!-- Rejected Qty -->

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
                                            No goods receipt
                                            details found.
                                        </td>

                                    </tr>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </section>
<!-- ========================================================= -->
<!-- Stock Impact -->
<!-- ========================================================= -->

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
            Inventory impact generated by this goods receipt.
        </p>

    </div>


    <div
        v-if="stockImpact.length"
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
                    min-w-[900px]
                    w-full
                "
            >

                <!-- ================================================= -->
                <!-- Header -->
                <!-- ================================================= -->

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

                        <th
                            class="
                                px-4
                                py-3
                            "
                        >
                            Product Variant
                        </th>

                        <th
                            class="
                                px-4
                                py-3
                            "
                        >
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


                <!-- ================================================= -->
                <!-- Body -->
                <!-- ================================================= -->

                <tbody
                    class="
                        divide-y
                        divide-gray-100
                        bg-white
                    "
                >

                    <tr
                        v-for="
                            impact in stockImpact
                        "
                        :key="
                            impact.id
                        "
                        class="
                            transition
                            hover:bg-gray-50
                        "
                    >

                        <!-- Product Variant -->

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
                                    impact
                                        .productVariant
                                        ?.sku
                                    ??
                                    impact
                                        .product_variant
                                        ?.sku
                                    ??
                                    '-'
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
                                    impact
                                        .productVariant
                                        ?.name
                                    ??
                                    impact
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
                                impact
                                    .unit
                                    ?.name
                                ??
                                '-'
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
                                    impact.stockBefore
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
                                        impact.stockIn
                                    ) > 0
                                "
                            >
                                +{{
                                    formatNumber(
                                        impact.stockIn
                                    )
                                }}
                            </span>

                            <span
                                v-else
                                class="text-gray-400"
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
                                        impact.stockOut
                                    ) > 0
                                "
                            >
                                -{{
                                    formatNumber(
                                        impact.stockOut
                                    )
                                }}
                            </span>

                            <span
                                v-else
                                class="text-gray-400"
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
                                    impact.stockAfter
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
                                    impact.unitCost
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
                                    impact.totalCost
                                )
                            }}
                        </td>

                    </tr>


                    <!-- Empty -->

                    <tr
                        v-if="
                            !stockImpact.length
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
                            No inventory movement found
                            for this goods receipt.
                        </td>

                    </tr>

                </tbody>

            </table>

        </div>

    </div>


    <!-- Empty State -->

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
            No inventory movement has been recorded
            for this goods receipt.
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


                        <div
                            class="
                                flex
                                justify-between
                                py-2
                                text-sm
                            "
                        >

                            <span class="text-gray-600">
                                Total Ordered Quantity
                            </span>

                            <span class="font-semibold">
                                {{
                                    formatNumber(
                                        totalOrderedQuantity
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

                            <span class="text-gray-600">
                                Total Rejected Quantity
                            </span>

                            <span class="font-medium">
                                {{
                                    formatNumber(
                                        totalRejectedQuantity
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
                    v-if="goodsReceipt.remarks"
                    class="
                        rounded-xl
                        border
                        border-gray-200
                        bg-white
                        p-5
                    "
                >

                    <div class="text-xs text-gray-500">
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
                            Document workflow history.
                        </p>

                    </div>


                    <WorkflowTimeline
                        :activities="
                            goodsReceipt.activities
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
                            Complete activity history for this document.
                        </p>

                    </div>


                    <AuditTrail
                        :activities="
                            goodsReceipt.activities
                            ?? []
                        "
                    />

                </section>

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