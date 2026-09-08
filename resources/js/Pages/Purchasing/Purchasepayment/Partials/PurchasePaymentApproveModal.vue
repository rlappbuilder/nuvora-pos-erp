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

const formatCurrency = (
    value
) => {

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


/*
|--------------------------------------------------------------------------
| Details
|--------------------------------------------------------------------------
*/

const details = computed(() => {

    return props.purchasePayment?.details ?? []

})


/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

const totalItems = computed(() => {

    return details.value.length

})


const totalPayment = computed(() => {

    return details.value.reduce(

        (
            total,
            detail
        ) =>

            total +
            Number(
                detail.payment_amount ?? 0
            ),

        0

    )

})


const totalInvoiceAmount = computed(() => {

    return details.value.reduce(

        (
            total,
            detail
        ) =>

            total +
            Number(
                detail.invoice_amount ?? 0
            ),

        0

    )

})


const totalPreviousPaid = computed(() => {

    return details.value.reduce(

        (
            total,
            detail
        ) =>

            total +
            Number(
                detail.previous_paid_amount ?? 0
            ),

        0

    )

})


const totalPreviousOutstanding = computed(() => {

    return details.value.reduce(

        (
            total,
            detail
        ) =>

            total +
            Number(
                detail.previous_outstanding_amount ?? 0
            ),

        0

    )

})


const grandTotal = computed(() => {

    return Number(
        props.purchasePayment?.total_amount ?? 0
    )

})

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
            @click.self="closeApprove"
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
                            Approve Purchase Payment
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
                            Review the purchase payment before
                            approving it.
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
                        @click="closeApprove"
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
                        v-if="purchasePayment"
                    >

                        <!-- ========================================= -->
                        <!-- Payment Information -->
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

                                <!-- Number -->

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

                                    <div
                                        class="
                                            text-xs
                                            font-medium
                                            uppercase
                                            tracking-wide
                                            text-gray-500
                                        "
                                    >
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
                                            purchasePayment.payment_date
                                                ? formatDate(
                                                    purchasePayment.payment_date
                                                )
                                                : '-'
                                        }}
                                    </div>

                                </div>


                                <!-- Payment Method -->

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
                                            purchasePayment
                                                .payment_method
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
                                            purchasePayment
                                                .supplier
                                                ?.name
                                            ?? '-'
                                        }}
                                    </div>

                                </div>

                            </div>


                            <!-- Payment Account / Location -->

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
                                        grid
                                        grid-cols-1
                                        gap-4
                                        sm:grid-cols-2
                                    "
                                >

                                    <!-- Branch -->

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
                                                purchasePayment
                                                    .branch
                                                    ?.name
                                                ?? '-'
                                            }}
                                        </div>

                                    </div>


                                    <!-- Payment Account -->

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
                                                purchasePayment
                                                    .payment_account
                                                    ?.code
                                                ?? '-'
                                            }}

                                            <span
                                                v-if="
                                                    purchasePayment
                                                        .payment_account
                                                        ?.name
                                                "
                                                class="
                                                    text-gray-500
                                                "
                                            >
                                                -
                                                {{
                                                    purchasePayment
                                                        .payment_account
                                                        .name
                                                }}
                                            </span>
                                        </div>

                                    </div>

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
                                        Payment Details
                                    </h3>

                                    <p
                                        class="
                                            mt-0.5
                                            text-xs
                                            text-gray-500
                                        "
                                    >
                                        Purchase invoices and payment amounts.
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
                                    {{ totalItems }} Invoices
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
                                        min-w-[1050px]
                                        w-full
                                        divide-y
                                        divide-gray-200
                                    "
                                >

                                    <thead
                                        class="
                                            bg-gray-50
                                        "
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
                                                Purchase Invoice
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
                                                Invoice Amount
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
                                                Previous Paid
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
                                                Outstanding
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
                                                Payment Amount
                                            </th>

                                            <th
                                                class="
                                                    w-56
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
                                                            .purchase_invoice
                                                            ?.number
                                                        ??
                                                        detail
                                                            .purchase_invoice
                                                            ?.invoice_number
                                                        ??
                                                        '-'
                                                    }}
                                                </div>

                                                <div
                                                    v-if="
                                                        detail
                                                            .purchase_invoice
                                                            ?.invoice_date
                                                    "
                                                    class="
                                                        mt-0.5
                                                        text-xs
                                                        text-gray-500
                                                    "
                                                >
                                                    {{
                                                        formatDate(
                                                            detail
                                                                .purchase_invoice
                                                                .invoice_date
                                                        )
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
                                                    font-medium
                                                    text-gray-900
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
                                                No purchase payment details found.
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
                                purchasePayment.remarks
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
                                    purchasePayment.remarks
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
                                        Total Invoices
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
                                        border-t
                                        border-gray-200
                                        py-2
                                        text-sm
                                    "
                                >

                                    <span
                                        class="text-gray-600"
                                    >
                                        Total Invoice Amount
                                    </span>

                                    <span
                                        class="
                                            font-semibold
                                            text-gray-900
                                        "
                                    >
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

                                    <span
                                        class="text-gray-600"
                                    >
                                        Previous Paid
                                    </span>

                                    <span
                                        class="
                                            font-semibold
                                            text-gray-900
                                        "
                                    >
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

                                    <span
                                        class="text-gray-600"
                                    >
                                        Previous Outstanding
                                    </span>

                                    <span
                                        class="
                                            font-semibold
                                            text-gray-900
                                        "
                                    >
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

                                    <span
                                        class="text-gray-900"
                                    >
                                        Total Payment
                                    </span>

                                    <span
                                        class="text-gray-900"
                                    >
                                        {{
                                            formatCurrency(
                                                grandTotal
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
                                    leading-6
                                    text-emerald-700
                                "
                            >
                                Please review the supplier,
                                payment method, payment account,
                                purchase invoices, outstanding
                                amounts, and payment amounts
                                carefully. Approving this purchase
                                payment will allow it to proceed
                                to posting.
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
                        Purchase payment data is not available.
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
                        @click="closeApprove"
                    >
                        Cancel
                    </BaseButton>


                    <BaseButton
                        type="button"
                        variant="success"
                        :loading="loading"
                        :disabled="
                            !purchasePayment ||
                            !details.length ||
                            loading
                        "
                        @click="confirmApprove"
                    >
                        Approve Purchase Payment
                    </BaseButton>

                </div>

            </div>

        </div>

    </Teleport>

</template>