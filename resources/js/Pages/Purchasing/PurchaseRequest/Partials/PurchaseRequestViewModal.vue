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

    purchaseRequest: {
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

    return props.purchaseRequest?.details ?? []

})


const totalItems = computed(() => {

    return details.value.length

})


const totalQuantity = computed(() => {

    return details.value.reduce(
        (total, detail) =>
            total +
            Number(detail.qty || 0),
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
        title="Purchase Request Detail"
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
                        Loading purchase request...
                    </span>

                </div>

            </div>


            <!-- ===================================================== -->
            <!-- Content -->
            <!-- ===================================================== -->

            <div
                v-else-if="purchaseRequest"
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
                                Purchase request transaction information.
                            </p>

                        </div>


                        <StatusBadge
                            :status="purchaseRequest.status"
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
                                    purchaseRequest.number
                                    ?? '-'
                                }}
                            </div>

                        </div>


                        <!-- Request Date -->

                        <div>

                            <div class="text-xs text-gray-500">
                                Request Date
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
                                        purchaseRequest.request_date
                                    )
                                }}
                            </div>

                        </div>


                        <!-- Required Date -->

                        <div>

                            <div class="text-xs text-gray-500">
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
                                    purchaseRequest.required_date
                                        ? formatDate(
                                            purchaseRequest.required_date
                                        )
                                        : '-'
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
                                    purchaseRequest.branch?.name
                                    ??
                                    purchaseRequest.branch?.label
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
                                    purchaseRequest.warehouse?.name
                                    ??
                                    purchaseRequest.warehouse?.label
                                    ??
                                    '-'
                                }}
                            </div>

                        </div>


                        <!-- Priority -->

                        <div>

                            <div class="text-xs text-gray-500">
                                Priority
                            </div>

                            <div
                                class="
                                    mt-1
                                    font-medium
                                    text-gray-900
                                "
                            >
                                {{
                                    purchaseRequest.priority
                                    ?? '-'
                                }}
                            </div>

                        </div>

                    </div>

                </section>


                <!-- ================================================= -->
                <!-- Rejection Information -->
                <!-- ================================================= -->

                <section
                    v-if="
                        purchaseRequest.status === 'Rejected'
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
                                    purchaseRequest.rejected_reason
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
                                            purchaseRequest.rejected_at
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
                                        purchaseRequest.rejector?.name
                                        ?? '-'
                                    }}
                                </div>

                            </div>

                        </div>

                    </div>

                </section>


                <!-- ================================================= -->
                <!-- Cancellation Information -->
                <!-- ================================================= -->

                <section
                    v-if="
                        purchaseRequest.status === 'Cancelled'
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


                    <div class="mt-4">

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
                                purchaseRequest.cancelled_reason
                                ??
                                purchaseRequest.cancel_reason
                                ??
                                '-'
                            }}
                        </div>

                    </div>

                </section>


                <!-- ================================================= -->
                <!-- Purchase Request Details -->
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
                            Purchase Request Details
                        </h3>

                        <p
                            class="
                                mt-1
                                text-sm
                                text-gray-500
                            "
                        >
                            Products requested in this purchase request.
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
                                            Quantity
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
                                                detail
                                                    .unit
                                                    ?.name
                                                ??
                                                detail
                                                    .unit
                                                    ?.label
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

                                    </tr>


                                    <tr
                                        v-if="
                                            !details.length
                                        "
                                    >

                                        <td
                                            colspan="3"
                                            class="
                                                px-4
                                                py-8
                                                text-center
                                                text-sm
                                                text-gray-500
                                            "
                                        >
                                            No purchase request
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
                                Total Quantity
                            </span>

                            <span class="font-semibold">
                                {{
                                    formatNumber(
                                        totalQuantity
                                    )
                                }}
                            </span>

                        </div>

                    </div>

                </section>


                <!-- ================================================= -->
                <!-- Description -->
                <!-- ================================================= -->

                <section
                    v-if="purchaseRequest.description"
                    class="
                        rounded-xl
                        border
                        border-gray-200
                        bg-white
                        p-5
                    "
                >

                    <div class="text-xs text-gray-500">
                        Description
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
                            purchaseRequest.description
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
                            purchaseRequest.activities
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
                            purchaseRequest.activities
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