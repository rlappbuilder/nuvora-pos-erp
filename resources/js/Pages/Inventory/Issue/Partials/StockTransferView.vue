<script setup>

import StockImpact from '@/Components/Inventory/StockImpact.vue'
import { computed } from 'vue'
import WorkflowTimeline from '@/Components/Workflow/WorkflowTimeline.vue'
import BaseModal from '@/Components/Modal/BaseModal.vue'
import BaseButton from '@/Components/Button/BaseButton.vue'
import StatusBadge from '@/Components/Display/StatusBadge.vue'
import AuditTrail from '@/Components/Workflow/AuditTrail.vue'
const props = defineProps({

    show: {
        type: Boolean,
        default: false,
    },

    transfer: {
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

    return props.transfer?.details ?? []

})

const totalItems = computed(() => {

    return details.value.length

})

const totalQuantity = computed(() => {

    return details.value.reduce(
        (total, detail) =>
            total +
            Number(
                detail.qty || 0
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
    title="Stock Transfer Detail"
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
                    Loading Stock Transfer...
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
                            Stock transfer transaction information.
                        </p>

                    </div>

                    <StatusBadge
                        :status="
                            props.transfer?.status
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
                                props.transfer?.number
                                ?? '-'
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
                                    props.transfer?.transaction_date
                                )
                            }}
                        </div>

                    </div>


                    <!-- Source Branch -->

                    <div>

                        <div class="text-xs text-gray-500">
                            From Branch
                        </div>

                        <div
                            class="
                                mt-1
                                font-medium
                                text-gray-900
                            "
                        >
                            {{
                                props.transfer?.from_branch?.name
                                ??
                                props.transfer?.from_branch?.label
                                ??
                                '-'
                            }}
                        </div>

                    </div>


                    <!-- Source Warehouse -->

                    <div>

                        <div class="text-xs text-gray-500">
                            From Warehouse
                        </div>

                        <div
                            class="
                                mt-1
                                font-medium
                                text-gray-900
                            "
                        >
                            {{
                                props.transfer?.from_warehouse?.name
                                ??
                                props.transfer?.from_warehouse?.label
                                ??
                                '-'
                            }}
                        </div>

                    </div>


                    <!-- Destination Branch -->

                    <div>

                        <div class="text-xs text-gray-500">
                            To Branch
                        </div>

                        <div
                            class="
                                mt-1
                                font-medium
                                text-gray-900
                            "
                        >
                            {{
                                props.transfer?.to_branch?.name
                                ??
                                props.transfer?.to_branch?.label
                                ??
                                '-'
                            }}
                        </div>

                    </div>


                    <!-- Destination Warehouse -->

                    <div>

                        <div class="text-xs text-gray-500">
                            To Warehouse
                        </div>

                        <div
                            class="
                                mt-1
                                font-medium
                                text-gray-900
                            "
                        >
                            {{
                                props.transfer?.to_warehouse?.name
                                ??
                                props.transfer?.to_warehouse?.label
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
                                props.transfer?.description
                                || '-'
                            }}
                        </div>

                    </div>

                </div>

            </section>
           <!-- ========================================================= -->
            <!-- Transfer Stock Summary -->
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
            Transfer Summary
        </h3>

        <p
            class="
                mt-1
                text-sm
                text-gray-500
            "
        >
            Summary of transferred quantities and costs.
        </p>

    </div>


    <div
        class="
            grid
            grid-cols-1
            gap-4
            md:grid-cols-3
        "
    >

        <!-- Total Items -->

        <div
            class="
                rounded-xl
                border
                border-gray-200
                bg-green-50
                p-5
            "
        >

            <div class="text-xs text-gray-500">
                Total Items
            </div>

            <div
                class="
                    mt-1
                    text-xl
                    font-semibold
                    text-gray-900
                "
            >
                {{ totalItems }}
            </div>

        </div>
        <div
            class="
                rounded-xl
                border
                border-gray-200
                bg-red-100
                p-5
            "
        >

            <div class="text-xs text-gray-500">
                Total Quantity
            </div>

            <div
                class="
                    mt-1
                    text-xl
                    font-semibold
                    text-gray-900
                "
            >
                {{
                    formatNumber(
                        totalQuantity
                    )
                }}
            </div>

        </div>

        <!-- Total Cost -->

        <div
            class="
                rounded-xl
                border
                border-gray-200
                bg-orange-50
                p-5
            "
        >

            <div class="text-xs text-gray-500">
                Total Cost
            </div>

            <div
                class="
                    mt-1
                    text-xl
                    font-semibold
                    text-gray-900
                "
            >
                {{
                    formatCurrency(
                        totalCost
                    )
                }}
            </div>

        </div>

    </div>

</section>
            <!-- ===================================================== -->
            <!-- Rejection Information -->
            <!-- ===================================================== -->

            <section
                v-if="
                    transfer?.status === 'Rejected'
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
                                transfer?.rejected_reason
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
                                        transfer?.rejected_at
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
                                    transfer?.rejector?.name
                                    ?? '-'
                                }}
                            </div>

                        </div>

                    </div>

                </div>

            </section>
            <!-- ===================================================== -->
            <!--ent Details -->
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
                        Transfer Details
                    </h3>

                    <p
                        class="
                            mt-1
                            text-sm
                            text-gray-500
                        "
                    >
                        Stock quantity Transer Stock details.
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
                                        Transfer Qty
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
                                    <!-- Quantity -->

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
                                                detail.qty
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
                                        colspan="5"
                                        class="
                                            px-4
                                            py-8
                                            text-center
                                            text-sm
                                            text-gray-500
                                        "
                                    >
                                        No Transfer Stock details found.
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
                        Transer impact generated by this document.
                    </p>

                </div>

                <StockImpact
                    :movements="
                        props.transfer?.movements
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
                            props.transfer?.activities
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
                    props.transfer?.activities
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