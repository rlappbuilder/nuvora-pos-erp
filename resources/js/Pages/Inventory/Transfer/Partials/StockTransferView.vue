<script setup>

import StockImpact from '@/Components/Inventory/StockImpact.vue'
import { computed } from 'vue'
import WorkflowTimeline from '@/Components/Workflow/WorkflowTimeline.vue'
import BaseModal from '@/Components/Modal/BaseModal.vue'
import BaseButton from '@/Components/Button/BaseButton.vue'
import StatusBadge from '@/Components/Display/StatusBadge.vue'
import AuditTrail from '@/Components/Workflow/AuditTrail.vue'
import AdjustmentSummary from '@/Components/Inventory/AdjustmentSummary.vue'
const props = defineProps({

    show: {
        type: Boolean,
        default: false,
    },

    adjustment: {
        type: Object,
        default: null,
    },

    loading: {
        type: Boolean,
        default: false,
    },

})

const emit = defineEmits([
    'close',
])

/*
|--------------------------------------------------------------------------
| Computed
|--------------------------------------------------------------------------
*/

const details = computed(() => {

    return props.adjustment?.details ?? []

})

const totalItems = computed(() => {

    return details.value.length

})

const totalDifference = computed(() => {

    return details.value.reduce(
        (total, detail) =>
            total +
            Number(
                detail.difference_qty || 0
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
        'en-US',
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
    title="Inventory Adjustment Detail"
    size="xl"
    @close="emit('close')"
>

    <div class="space-y-6">

        <!-- Loading -->

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
                    Loading inventory adjustment...
                </span>

            </div>

        </div>

        <!-- Content -->

        <div
            v-else
            class="space-y-6"
        >

            <!-- ===================================================== -->
            <!-- Document Information -->
            <!-- ===================================================== -->

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
                            Inventory adjustment transaction information.
                        </p>

                    </div>

                    <StatusBadge
                        :status="
                            adjustment?.status
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
                                adjustment?.number || '-'
                            }}
                        </div>

                    </div>


                    <!-- Transaction Date -->

                    <div>

                        <div class="text-xs text-gray-500">
                            Transaction Date
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
                                    adjustment?.transaction_date
                                )
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
                                adjustment?.branch?.name
                                ??
                                adjustment?.branch?.label
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
                                adjustment?.warehouse?.name
                                ??
                                adjustment?.warehouse?.label
                                ??
                                '-'
                            }}
                        </div>

                    </div>


                    <!-- Description -->

                    <div class="md:col-span-2">

                        <div class="text-xs text-gray-500">
                            Description
                        </div>

                        <div
                            class="
                                mt-1
                                whitespace-pre-line
                                text-sm
                                text-gray-700
                            "
                        >
                            {{
                                adjustment?.description || '-'
                            }}
                        </div>

                    </div>

                </div>

            </section>
           <!-- ========================================================= -->
            <!-- Adjustment Summary -->
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
                        Adjustment Summary
                    </h3>

                    <p
                        class="
                            mt-1
                            text-sm
                            text-gray-500
                        "
                    >
                        Summary of quantity and cost adjustment.
                    </p>

                </div>


                <AdjustmentSummary
                    :details="details"
                />

            </section>
            <!-- ===================================================== -->
            <!-- Rejection Information -->
            <!-- ===================================================== -->

            <section
                v-if="
                    adjustment?.status === 'Rejected'
                "
                class="
                    rounded-xl
                    border
                    border-red-200
                    bg-red-50
                    p-5
                "
            >

                <h3
                    class="
                        text-base
                        font-semibold
                        text-red-800
                    "
                >
                    Rejection Information
                </h3>

                <div class="mt-4 space-y-3">

                    <div>

                        <div class="text-xs text-red-600">
                            Rejection Reason
                        </div>

                        <div
                            class="
                                mt-1
                                text-sm
                                font-medium
                                text-red-900
                            "
                        >
                            {{
                                adjustment?.rejected_reason
                                || '-'
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

                            <div class="text-xs text-red-600">
                                Rejected At
                            </div>

                            <div
                                class="
                                    mt-1
                                    text-sm
                                    text-red-900
                                "
                            >
                                {{
                                    formatDateTime(
                                        adjustment?.rejected_at
                                    )
                                }}
                            </div>

                        </div>


                        <div>

                            <div class="text-xs text-red-600">
                                Rejected By
                            </div>

                            <div
                                class="
                                    mt-1
                                    text-sm
                                    text-red-900
                                "
                            >
                                {{
                                    adjustment?.rejector?.name
                                    ?? '-'
                                }}
                            </div>

                        </div>

                    </div>

                </div>

            </section>
<!-- ===================================================== -->
            <!-- Adjustment Details -->
            <!-- ===================================================== -->

            <section>

                <div class="mb-4">

                    <h3
                        class="
                            text-base
                            font-semibold
                            text-gray-900
                        "
                    >
                        Adjustment Details
                    </h3>

                    <p
                        class="
                            mt-1
                            text-sm
                            text-gray-500
                        "
                    >
                        Stock quantity adjustment details.
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

                        <table class="min-w-full">

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
                                        System Qty
                                    </th>

                                    <th
                                        class="
                                            px-4
                                            py-3
                                            text-right
                                        "
                                    >
                                        Actual Qty
                                    </th>

                                    <th
                                        class="
                                            px-4
                                            py-3
                                            text-right
                                        "
                                    >
                                        Difference
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
                                        detail,
                                        index
                                    ) in details"

                                    :key="
                                        detail.id
                                        ?? index
                                    "

                                    class="
                                        hover:bg-gray-50
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
                                                detail.variant?.sku
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
                                                detail.variant?.product?.name
                                                ??
                                                detail.variant?.name
                                                ??
                                                ''
                                            }}

                                            <span
                                                v-if="
                                                    detail.variant?.name
                                                "
                                            >
                                                -
                                                {{
                                                    detail.variant.name
                                                }}
                                            </span>

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


                                    <!-- System Qty -->

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
                                                detail.system_qty
                                            )
                                        }}
                                    </td>


                                    <!-- Actual Qty -->

                                    <td
                                        class="
                                            whitespace-nowrap
                                            px-4
                                            py-3
                                            text-right
                                            text-sm
                                            font-medium
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            formatNumber(
                                                detail.actual_qty
                                            )
                                        }}
                                    </td>


                                    <!-- Difference -->

                                    <td
                                        class="
                                            whitespace-nowrap
                                            px-4
                                            py-3
                                            text-right
                                            text-sm
                                            font-semibold
                                        "
                                        :class="
                                            Number(
                                                detail.difference_qty
                                                || 0
                                            ) > 0
                                                ? 'text-green-600'
                                                : Number(
                                                    detail.difference_qty
                                                    || 0
                                                ) < 0
                                                    ? 'text-red-600'
                                                    : 'text-gray-500'
                                        "
                                    >
                                        {{
                                            Number(
                                                detail.difference_qty
                                                || 0
                                            ) > 0
                                                ? '+'
                                                : ''
                                        }}{{
                                            formatNumber(
                                                detail.difference_qty
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
                                        No adjustment details found.
                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

            </section>         
           <!-- Stock impact-->
            <section class="mt-8">

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
                        Inventory impact generated by this document.
                    </p>

                </div>

                I<StockImpact
                    :movements="
                        adjustment?.movements
                        ?? []
                    "
                />

            </section>
            <!-- end stock impact-->
        
        <!-- ========================================================= -->
        <!-- audit trail -->
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
                            adjustment.activities
                            ?? []
                        "
                    />

                </section>

    <section class="mt-8">

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
                adjustment.activities
                ?? []
            "
        />

    </section>
</div>
</div>
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