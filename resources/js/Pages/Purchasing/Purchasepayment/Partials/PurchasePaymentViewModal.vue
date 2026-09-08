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

    purchasePayment: {
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

    return props.purchasePayment?.details ?? []

})


const totalItems = computed(() => {

    return details.value.length

})


const totalInvoiceAmount = computed(() => {

    return details.value.reduce(
        (total, detail) =>
            total +
            Number(
                detail.invoice_amount || 0
            ),
        0
    )

})


const totalPreviousPaid = computed(() => {

    return details.value.reduce(
        (total, detail) =>
            total +
            Number(
                detail.previous_paid_amount || 0
            ),
        0
    )

})


const totalPreviousOutstanding = computed(() => {

    return details.value.reduce(
        (total, detail) =>
            total +
            Number(
                detail.previous_outstanding_amount || 0
            ),
        0
    )

})


const totalPayment = computed(() => {

    return details.value.reduce(
        (total, detail) =>
            total +
            Number(
                detail.payment_amount || 0
            ),
        0
    )

})


/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

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
        title="Purchase Payment Detail"
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
                        Loading purchase payment...
                    </span>

                </div>

            </div>


            <!-- ===================================================== -->
            <!-- Content -->
            <!-- ===================================================== -->

            <div
                v-else-if="purchasePayment"
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
                                Purchase payment transaction information.
                            </p>

                        </div>


                        <StatusBadge
                            :status="
                                purchasePayment.status
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
                                    purchasePayment.number
                                    ?? '-'
                                }}
                            </div>

                        </div>


                        <!-- Payment Date -->

                        <div>

                            <div class="text-xs text-gray-500">
                                Payment Date
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
                                        purchasePayment.payment_date
                                    )
                                }}
                            </div>

                        </div>


                        <!-- Payment Method -->

                        <div>

                            <div class="text-xs text-gray-500">
                                Payment Method
                            </div>

                            <div
                                class="
                                    mt-1
                                    font-medium
                                    text-gray-900
                                "
                            >
                                {{
                                    purchasePayment.payment_method
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
                                    purchasePayment.supplier?.name
                                    ??
                                    purchasePayment.supplier?.label
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
                                    purchasePayment.branch?.name
                                    ??
                                    purchasePayment.branch?.label
                                    ??
                                    '-'
                                }}
                            </div>

                        </div>


                        <!-- Payment Account -->

                        <div>

                            <div class="text-xs text-gray-500">
                                Payment Account
                            </div>

                            <div
                                class="
                                    mt-1
                                    font-medium
                                    text-gray-900
                                "
                            >
                                {{
                                    purchasePayment.paymentAccount?.name
                                    ??
                                    purchasePayment.payment_account?.name
                                    ??
                                    '-'
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
                        purchasePayment.status === 'Rejected'
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
                                    whitespace-pre-line
                                    text-sm
                                    font-medium
                                    text-red-900
                                "
                            >
                                {{
                                    purchasePayment.reject_reason
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
                                            purchasePayment.rejected_at
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
                                        purchasePayment
                                            .rejector
                                            ?.name
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
                        purchasePayment.status === 'Cancelled'
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
                                purchasePayment.cancel_reason
                                ?? '-'
                            }}
                        </div>

                    </div>

                </section>


                <!-- ================================================= -->
                <!-- Purchase Payment Details -->
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
                            Purchase Payment Details
                        </h3>

                        <p
                            class="
                                mt-1
                                text-sm
                                text-gray-500
                            "
                        >
                            Purchase invoices included in this payment.
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
                                    min-w-[1050px]
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
                                            Purchase Invoice
                                        </th>

                                        <th
                                            class="
                                                px-4
                                                py-3
                                                text-right
                                            "
                                        >
                                            Invoice Amount
                                        </th>

                                        <th
                                            class="
                                                px-4
                                                py-3
                                                text-right
                                            "
                                        >
                                            Previous Paid
                                        </th>

                                        <th
                                            class="
                                                px-4
                                                py-3
                                                text-right
                                            "
                                        >
                                            Outstanding
                                        </th>

                                        <th
                                            class="
                                                px-4
                                                py-3
                                                text-right
                                            "
                                        >
                                            Payment Amount
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

                                        <!-- Purchase Invoice -->

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
                                                        .purchaseInvoice
                                                        ?.number
                                                    ??
                                                    detail
                                                        .purchase_invoice
                                                        ?.number
                                                    ??
                                                    detail
                                                        .purchaseInvoice
                                                        ?.invoice_number
                                                    ??
                                                    detail
                                                        .purchase_invoice
                                                        ?.invoice_number
                                                    ?? '-'
                                                }}
                                            </div>

                                        </td>


                                        <!-- Invoice Amount -->

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
                                                    detail.invoice_amount
                                                )
                                            }}
                                        </td>


                                        <!-- Previous Paid -->

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
                                                    detail.previous_paid_amount
                                                )
                                            }}
                                        </td>


                                        <!-- Outstanding -->

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
                                                    detail.previous_outstanding_amount
                                                )
                                            }}
                                        </td>


                                        <!-- Payment Amount -->

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
                                                    detail.payment_amount
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
                                            No purchase payment
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
                                Total Invoices
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
                                Total Invoice Amount
                            </span>

                            <span class="font-medium">
                                {{
                                    formatCurrency(
                                        totalInvoiceAmount
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
                                Previous Paid
                            </span>

                            <span class="font-medium">
                                {{
                                    formatCurrency(
                                        totalPreviousPaid
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
                                Previous Outstanding
                            </span>

                            <span class="font-medium">
                                {{
                                    formatCurrency(
                                        totalPreviousOutstanding
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

                            <span class="text-gray-900">
                                Total Payment
                            </span>

                            <span class="text-gray-900">
                                {{
                                    formatCurrency(
                                        totalPayment
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
                    v-if="purchasePayment.remarks"
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
                            purchasePayment.remarks
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
                            purchasePayment.activities
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
                            purchasePayment.activities
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