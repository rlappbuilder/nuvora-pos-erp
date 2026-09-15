<script setup>

import { computed, ref } from 'vue'

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

    consignmentOut: {
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
| Tabs
|--------------------------------------------------------------------------
*/

const activeTab = ref('workflow')


/*
|--------------------------------------------------------------------------
| Computed
|--------------------------------------------------------------------------
*/

const details = computed(() => {

    return props.consignmentOut?.details ?? []

})


const activities = computed(() => {

    return props.consignmentOut?.activities ?? []

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


const totalConsignmentValue = computed(() => {

    return details.value.reduce(
        (total, detail) =>
            total +
            Number(detail.total_price || 0),
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


function productSku(detail)
{
    return (
        detail.variant?.sku
        ??
        '-'
    )
}


function productName(detail)
{
    return (
        detail.variant?.product?.name
        ??
        detail.variant?.name
        ??
        '-'
    )
}


function unitName(detail)
{
    return (
        detail.unit?.name
        ??
        detail.unit?.label
        ??
        '-'
    )
}

</script>


<template>

    <BaseModal
        :show="show"
        title="Consignment Out Detail"
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
                        Loading consignment out...
                    </span>

                </div>

            </div>


            <!-- ===================================================== -->
            <!-- Content -->
            <!-- ===================================================== -->

            <div
                v-else-if="consignmentOut"
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
                                Consignment out transaction information.
                            </p>

                        </div>


                        <StatusBadge
                            :status="
                                consignmentOut.status
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
                                    consignmentOut
                                        .consignment_out_number
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
                                        consignmentOut.transaction_date
                                    )
                                }}
                            </div>

                        </div>


                        <!-- Posting Date -->

                        <div>

                            <div class="text-xs text-gray-500">
                                Posting Date
                            </div>

                            <div
                                class="
                                    mt-1
                                    font-medium
                                    text-gray-900
                                "
                            >
                                {{
                                    formatDateTime(
                                        consignmentOut.posting_date
                                    )
                                }}
                            </div>

                        </div>


                        <!-- Reseller -->

                        <div>

                            <div class="text-xs text-gray-500">
                                Reseller
                            </div>

                            <div
                                class="
                                    mt-1
                                    font-medium
                                    text-gray-900
                                "
                            >
                                {{
                                    consignmentOut.reseller?.name
                                    ?? '-'
                                }}
                            </div>

                            <div
                                v-if="
                                    consignmentOut.reseller?.reseller_code
                                "
                                class="
                                    mt-0.5
                                    text-xs
                                    text-gray-500
                                "
                            >
                                {{
                                    consignmentOut
                                        .reseller
                                        .reseller_code
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
                                    consignmentOut.branch?.name
                                    ?? '-'
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
                                    consignmentOut.warehouse?.name
                                    ?? '-'
                                }}
                            </div>

                        </div>


                        <!-- Reference Number -->

                        <div>

                            <div class="text-xs text-gray-500">
                                Reference Number
                            </div>

                            <div
                                class="
                                    mt-1
                                    font-medium
                                    text-gray-900
                                "
                            >
                                {{
                                    consignmentOut.reference_number
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
                        consignmentOut.status === 'Rejected'
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
                                    consignmentOut.rejected_reason
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
                                            consignmentOut.rejected_at
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
                                        consignmentOut.rejector?.name
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
                        consignmentOut.status === 'Cancelled'
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
                                consignmentOut.cancel_reason
                                ??
                                '-'
                            }}
                        </div>

                    </div>

                </section>


                <!-- ================================================= -->
                <!-- Consignment Out Details -->
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
                            Consignment Out Details
                        </h3>

                        <p
                            class="
                                mt-1
                                text-sm
                                text-gray-500
                            "
                        >
                            Products transferred to reseller consignment stock.
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
                                    min-w-[850px]
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
                                            Quantity
                                        </th>

                                        <th
                                            class="
                                                px-4
                                                py-3
                                                text-right
                                            "
                                        >
                                            Consignment Price
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


                                        <!-- Consignment Price -->

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
                                                    detail.total_price
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
                                            No consignment out
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


                        <div
                            class="
                                flex
                                justify-between
                                border-t
                                border-gray-200
                                py-3
                                text-base
                                font-semibold
                            "
                        >

                            <span class="text-gray-900">
                                Total Consignment Value
                            </span>

                            <span class="text-gray-900">
                                {{
                                    formatCurrency(
                                        totalConsignmentValue
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
                    v-if="consignmentOut.remarks"
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
                            consignmentOut.remarks
                        }}
                    </div>

                </section>


                <!-- ================================================= -->
                <!-- Workflow / Audit Tabs -->
                <!-- ================================================= -->

                <section>

                    <!-- Tabs -->

                    <div
                        class="
                            border-b
                            border-gray-200
                        "
                    >

                        <nav
                            class="
                                -mb-px
                                flex
                                gap-6
                            "
                        >

                            <button
                                type="button"
                                class="
                                    border-b-2
                                    px-1
                                    py-3
                                    text-sm
                                    font-medium
                                    transition
                                "
                                :class="
                                    activeTab === 'workflow'
                                        ? `
                                            border-blue-600
                                            text-blue-600
                                        `
                                        : `
                                            border-transparent
                                            text-gray-500
                                            hover:border-gray-300
                                            hover:text-gray-700
                                        `
                                "
                                @click="
                                    activeTab = 'workflow'
                                "
                            >
                                Workflow Timeline
                            </button>


                            <button
                                type="button"
                                class="
                                    border-b-2
                                    px-1
                                    py-3
                                    text-sm
                                    font-medium
                                    transition
                                "
                                :class="
                                    activeTab === 'audit'
                                        ? `
                                            border-blue-600
                                            text-blue-600
                                        `
                                        : `
                                            border-transparent
                                            text-gray-500
                                            hover:border-gray-300
                                            hover:text-gray-700
                                        `
                                "
                                @click="
                                    activeTab = 'audit'
                                "
                            >
                                Audit Trail
                            </button>

                        </nav>

                    </div>


                    <!-- Workflow -->

                    <div
                        v-if="
                            activeTab === 'workflow'
                        "
                        class="pt-5"
                    >

                        <WorkflowTimeline
                            :activities="
                                activities
                            "
                        />

                    </div>


                    <!-- Audit -->

                    <div
                        v-else
                        class="pt-5"
                    >

                        <AuditTrail
                            :activities="
                                activities
                            "
                        />

                    </div>

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