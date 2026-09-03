<script setup>

import { computed } from 'vue'

import WorkflowTimeline from '@/Components/Workflow/WorkflowTimeline.vue'
import BaseModal from '@/Components/Modal/BaseModal.vue'
import BaseButton from '@/Components/Button/BaseButton.vue'
import StatusBadge from '@/Components/Display/StatusBadge.vue'
import AuditTrail from '@/Components/Workflow/AuditTrail.vue'

import { formatDate } from '@/Utils'
import { formatCurrency } from '@/Utils/currency'


const props = defineProps({

    show: {
        type: Boolean,
        default: false,
    },

    journalEntry: {
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

const lines = computed(() => {

    return props.journalEntry?.lines ?? []

})


const totalLines = computed(() => {

    return lines.value.length

})


const totalDebit = computed(() => {

    return lines.value.reduce(
        (
            total,
            line
        ) =>
            total +
            Number(
                line.debit || 0
            ),
        0
    )

})


const totalCredit = computed(() => {

    return lines.value.reduce(
        (
            total,
            line
        ) =>
            total +
            Number(
                line.credit || 0
            ),
        0
    )

})


const difference = computed(() => {

    return (
        totalDebit.value -
        totalCredit.value
    )

})


const isBalanced = computed(() => {

    return (
        Math.abs(
            difference.value
        ) < 0.005
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


function accountCode(line)
{
    return (
        line?.account?.code
        ?? '-'
    )
}


function accountName(line)
{
    return (
        line?.account?.name
        ?? '-'
    )
}

</script>


<template>

<BaseModal
    :show="show"
    title="Journal Entry Detail"
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
                    Loading journal entry...
                </span>

            </div>

        </div>


        <!-- ===================================================== -->
        <!-- Content -->
        <!-- ===================================================== -->

        <div
            v-else
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
                            Journal entry transaction information.
                        </p>

                    </div>


                    <StatusBadge
                        :status="
                            journalEntry?.status
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
                                journalEntry?.code
                                ?? '-'
                            }}
                        </div>

                    </div>


                    <!-- Entry Date -->

                    <div>

                        <div class="text-xs text-gray-500">
                            Entry Date
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
                                    journalEntry?.entry_date
                                )
                            }}
                        </div>

                    </div>


                    <!-- Accounting Journal -->

                    <div>

                        <div class="text-xs text-gray-500">
                            Accounting Journal
                        </div>

                        <div
                            class="
                                mt-1
                                font-medium
                                text-gray-900
                            "
                        >
                            {{
                                journalEntry
                                    ?.accounting_journal
                                    ?.name
                                ??
                                journalEntry
                                    ?.accounting_journal
                                    ?.label
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
                                journalEntry
                                    ?.branch
                                    ?.name
                                ??
                                journalEntry
                                    ?.branch
                                    ?.label
                                ??
                                '-'
                            }}
                        </div>

                    </div>


                    <!-- Fiscal Year -->

                    <div>

                        <div class="text-xs text-gray-500">
                            Fiscal Year
                        </div>

                        <div
                            class="
                                mt-1
                                font-medium
                                text-gray-900
                            "
                        >
                            {{
                                journalEntry
                                    ?.fiscal_year
                                    ?.year
                                ?? '-'
                            }}
                        </div>

                    </div>


                    <!-- Accounting Period -->

                    <div>

                        <div class="text-xs text-gray-500">
                            Accounting Period
                        </div>

                        <div
                            class="
                                mt-1
                                font-medium
                                text-gray-900
                            "
                        >
                            {{
                                journalEntry
                                    ?.accounting_period
                                    ?.name
                                ??
                                journalEntry
                                    ?.accounting_period
                                    ?.period_name
                                ??
                                '-'
                            }}
                        </div>

                    </div>


                    <!-- Reference -->

                    <div>

                        <div class="text-xs text-gray-500">
                            Reference
                        </div>

                        <div
                            class="
                                mt-1
                                font-medium
                                text-gray-900
                            "
                        >
                            {{
                                journalEntry?.reference
                                ?? '-'
                            }}
                        </div>

                    </div>


                    <!-- Posted At -->

                    <div
                        v-if="
                            journalEntry?.status === 'Posted'
                        "
                    >

                        <div class="text-xs text-gray-500">
                            Posted At
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
                                    journalEntry?.posted_at
                                )
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
                                journalEntry?.description
                                || '-'
                            }}
                        </div>

                    </div>

                </div>

            </section>


            <!-- ================================================= -->
            <!-- Journal Entry Summary -->
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
                        Journal Entry Summary
                    </h3>

                    <p
                        class="
                            mt-1
                            text-sm
                            text-gray-500
                        "
                    >
                        Summary of journal entry debit and credit.
                    </p>

                </div>


                <div
                    class="
                        grid
                        grid-cols-1
                        gap-4
                        md:grid-cols-4
                    "
                >

                    <!-- Total Lines -->

                    <div
                        class="
                            rounded-xl
                            border
                            border-gray-200
                            bg-gray-50
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
                            Total Lines
                        </div>

                        <div
                            class="
                                mt-2
                                text-2xl
                                font-bold
                                text-gray-900
                            "
                        >
                            {{ totalLines }}
                        </div>

                    </div>


                    <!-- Total Debit -->

                    <div
                        class="
                            rounded-xl
                            border
                            border-gray-200
                            bg-gray-50
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
                            Total Debit
                        </div>

                        <div
                            class="
                                mt-2
                                text-2xl
                                font-bold
                                text-gray-900
                            "
                        >
                            {{
                                formatCurrency(
                                    totalDebit
                                )
                            }}
                        </div>

                    </div>


                    <!-- Total Credit -->

                    <div
                        class="
                            rounded-xl
                            border
                            border-gray-200
                            bg-gray-50
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
                            Total Credit
                        </div>

                        <div
                            class="
                                mt-2
                                text-2xl
                                font-bold
                                text-gray-900
                            "
                        >
                            {{
                                formatCurrency(
                                    totalCredit
                                )
                            }}
                        </div>

                    </div>


                    <!-- Balance -->

                    <div
                        class="
                            rounded-xl
                            border
                            border-gray-200
                            bg-gray-50
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
                            Balance
                        </div>

                        <div
                            :class="
                                isBalanced
                                    ? 'mt-2 text-2xl font-bold text-emerald-600'
                                    : 'mt-2 text-2xl font-bold text-red-600'
                            "
                        >
                            {{
                                isBalanced
                                    ? 'Balanced'
                                    : 'Not Balanced'
                            }}
                        </div>

                    </div>

                </div>

            </section>


            <!-- ================================================= -->
            <!-- Journal Entry Details -->
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
                        Journal Entry Details
                    </h3>

                    <p
                        class="
                            mt-1
                            text-sm
                            text-gray-500
                        "
                    >
                        Accounts and amounts recorded in this journal entry.
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
                                        Account
                                    </th>

                                    <th class="px-4 py-3">
                                        Description
                                    </th>

                                    <th
                                        class="
                                            px-4
                                            py-3
                                            text-right
                                        "
                                    >
                                        Debit
                                    </th>

                                    <th
                                        class="
                                            px-4
                                            py-3
                                            text-right
                                        "
                                    >
                                        Credit
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
                                        line,
                                        index
                                    ) in lines"

                                    :key="
                                        line.id
                                        ?? index
                                    "

                                    class="
                                        hover:bg-gray-50
                                    "
                                >

                                    <!-- Account -->

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
                                                accountCode(
                                                    line
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
                                                accountName(
                                                    line
                                                )
                                            }}
                                        </div>

                                    </td>


                                    <!-- Description -->

                                    <td
                                        class="
                                            px-4
                                            py-3
                                            text-sm
                                            text-gray-700
                                        "
                                    >
                                        {{
                                            line.description
                                            || '-'
                                        }}
                                    </td>


                                    <!-- Debit -->

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
                                                line.debit
                                            )
                                        }}
                                    </td>


                                    <!-- Credit -->

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
                                                line.credit
                                            )
                                        }}
                                    </td>

                                </tr>


                                <!-- Empty -->

                                <tr
                                    v-if="
                                        !lines.length
                                    "
                                >

                                    <td
                                        colspan="4"
                                        class="
                                            px-4
                                            py-8
                                            text-center
                                            text-sm
                                            text-gray-500
                                        "
                                    >
                                        No journal entry lines found.
                                    </td>

                                </tr>


                                <!-- Totals -->

                                <tr
                                    v-if="
                                        lines.length
                                    "
                                    class="
                                        border-t
                                        border-gray-200
                                        bg-gray-50
                                    "
                                >

                                    <td
                                        colspan="2"
                                        class="
                                            px-4
                                            py-3
                                            text-right
                                            text-sm
                                            font-semibold
                                            text-gray-900
                                        "
                                    >
                                        Total
                                    </td>

                                    <td
                                        class="
                                            px-4
                                            py-3
                                            text-right
                                            text-sm
                                            font-bold
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            formatCurrency(
                                                totalDebit
                                            )
                                        }}
                                    </td>

                                    <td
                                        class="
                                            px-4
                                            py-3
                                            text-right
                                            text-sm
                                            font-bold
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            formatCurrency(
                                                totalCredit
                                            )
                                        }}
                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

            </section>


            <!-- ================================================= -->
            <!-- Posting Information -->
            <!-- ================================================= -->

            <section
                v-if="
                    journalEntry?.status === 'Posted'
                "
                class="
                    rounded-xl
                    border
                    border-emerald-200
                    bg-emerald-50
                    p-5
                "
            >

                <h3
                    class="
                        text-base
                        font-semibold
                        text-emerald-800
                    "
                >
                    Posting Information
                </h3>


                <div
                    class="
                        mt-4
                        grid
                        grid-cols-1
                        gap-4
                        md:grid-cols-2
                    "
                >

                    <!-- Posted At -->

                    <div>

                        <div
                            class="
                                text-xs
                                text-emerald-600
                            "
                        >
                            Posted At
                        </div>

                        <div
                            class="
                                mt-1
                                text-sm
                                font-medium
                                text-emerald-900
                            "
                        >
                            {{
                                formatDateTime(
                                    journalEntry?.posted_at
                                )
                            }}
                        </div>

                    </div>


                    <!-- Posted By -->

                    <div>

                        <div
                            class="
                                text-xs
                                text-emerald-600
                            "
                        >
                            Posted By
                        </div>

                        <div
                            class="
                                mt-1
                                text-sm
                                font-medium
                                text-emerald-900
                            "
                        >
                            {{
                                journalEntry
                                    ?.posted_by
                                    ?.name
                                ?? '-'
                            }}
                        </div>

                    </div>

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
                        journalEntry?.activities
                        ?? []
                    "
                />

            </section>


            <!-- ================================================= -->
            <!-- Audit Trail -->
            <!-- ================================================= -->

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
                        journalEntry?.activities
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