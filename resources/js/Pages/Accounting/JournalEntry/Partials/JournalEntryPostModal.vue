<script setup>

import BaseButton from '@/Components/Button/BaseButton.vue'
import { formatDate } from '@/Utils'
import { formatCurrency } from '@/Utils/currency'

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

    journalEntry: {
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


function totalLines()
{
    return (
        props.journalEntry?.lines?.length
        ?? 0
    )
}


function totalDebit()
{
    return (
        props.journalEntry?.lines?.reduce(
            (
                total,
                line
            ) =>
                total +
                Number(
                    line.debit ?? 0
                ),
            0
        )
        ?? 0
    )
}


function totalCredit()
{
    return (
        props.journalEntry?.lines?.reduce(
            (
                total,
                line
            ) =>
                total +
                Number(
                    line.credit ?? 0
                ),
            0
        )
        ?? 0
    )
}


function isBalanced()
{
    return (
        Math.abs(
            totalDebit()
            -
            totalCredit()
        ) < 0.005
    )
}


function accountName(line)
{
    return (
        line?.account?.name
        ??
        '-'
    )
}


function accountCode(line)
{
    return (
        line?.account?.code
        ??
        '-'
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
                px-4
                py-6
            "
            @click.self="closePost"
        >

            <div
                class="
                    flex
                    max-h-[90vh]
                    w-full
                    max-w-5xl
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
                        items-center
                        justify-between
                        border-b
                        border-gray-200
                        px-6
                        py-5
                    "
                >

                    <div>

                        <h2
                            class="
                                text-xl
                                font-semibold
                                text-gray-900
                            "
                        >
                            Review Journal Entry
                        </h2>

                        <p
                            class="
                                mt-1
                                text-sm
                                text-gray-500
                            "
                        >
                            Review the journal entry before
                            posting it to the general ledger.
                        </p>

                    </div>


                    <button
                        type="button"
                        class="
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
                        flex-1
                        overflow-y-auto
                        p-6
                    "
                >

                    <template v-if="journalEntry">

                        <!-- ========================================= -->
                        <!-- Journal Entry Information -->
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
                                p-5
                                md:grid-cols-2
                                lg:grid-cols-3
                            "
                        >

                            <!-- Code -->

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
                                        journalEntry.code
                                        ?? '-'
                                    }}
                                </div>

                            </div>


                            <!-- Entry Date -->

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
                                    Entry Date
                                </div>

                                <div
                                    class="
                                        mt-1
                                        font-semibold
                                        text-gray-900
                                    "
                                >
                                    {{
                                        formatDate(
                                            journalEntry.entry_date
                                        )
                                    }}
                                </div>

                            </div>


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
                                        font-semibold
                                        text-gray-900
                                    "
                                >
                                    {{
                                        journalEntry.branch?.name
                                        ?? '-'
                                    }}
                                </div>

                            </div>


                            <!-- Accounting Journal -->

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
                                    Accounting Journal
                                </div>

                                <div
                                    class="
                                        mt-1
                                        font-semibold
                                        text-gray-900
                                    "
                                >
                                    {{
                                        journalEntry
                                            .accounting_journal
                                            ?.name
                                        ?? '-'
                                    }}
                                </div>

                            </div>


                            <!-- Fiscal Year -->

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
                                    Fiscal Year
                                </div>

                                <div
                                    class="
                                        mt-1
                                        font-semibold
                                        text-gray-900
                                    "
                                >
                                    {{
                                        journalEntry
                                            .fiscal_year
                                            ?.year
                                        ?? '-'
                                    }}
                                </div>

                            </div>


                            <!-- Accounting Period -->

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
                                    Accounting Period
                                </div>

                                <div
                                    class="
                                        mt-1
                                        font-semibold
                                        text-gray-900
                                    "
                                >
                                    {{
                                        journalEntry
                                            .accounting_period
                                            ?.name
                                        ?? '-'
                                    }}
                                </div>

                            </div>


                            <!-- Reference -->

                            <div
                                v-if="journalEntry.reference"
                                class="md:col-span-2 lg:col-span-3"
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
                                    Reference
                                </div>

                                <div
                                    class="
                                        mt-1
                                        font-semibold
                                        text-gray-900
                                    "
                                >
                                    {{
                                        journalEntry.reference
                                    }}
                                </div>

                            </div>

                        </div>


                        <!-- ========================================= -->
                        <!-- Journal Lines -->
                        <!-- ========================================= -->

                        <div class="mt-6">

                            <div
                                class="
                                    mb-3
                                    text-base
                                    font-semibold
                                    text-gray-900
                                "
                            >
                                Journal Lines
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
                                        min-w-full
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
                                                Account
                                            </th>


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
                                                Description
                                            </th>


                                            <th
                                                class="
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
                                                Debit
                                            </th>


                                            <th
                                                class="
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
                                            v-for="
                                                (
                                                    line,
                                                    index
                                                )
                                                in journalEntry.lines
                                            "
                                            :key="
                                                line.id
                                                ?? index
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
                                                    ?? '-'
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

                                    </tbody>

                                </table>

                            </div>

                        </div>


                        <!-- ========================================= -->
                        <!-- Description -->
                        <!-- ========================================= -->

                        <div
                            v-if="journalEntry.description"
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
                                    journalEntry.description
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

                                <!-- Total Lines -->

                                <div
                                    class="
                                        flex
                                        justify-between
                                        py-2
                                        text-sm
                                    "
                                >

                                    <span
                                        class="
                                            text-gray-600
                                        "
                                    >
                                        Total Lines
                                    </span>

                                    <span
                                        class="
                                            font-semibold
                                        "
                                    >
                                        {{ totalLines() }}
                                    </span>

                                </div>


                                <!-- Total Debit -->

                                <div
                                    class="
                                        flex
                                        justify-between
                                        py-2
                                        text-sm
                                    "
                                >

                                    <span
                                        class="
                                            text-gray-600
                                        "
                                    >
                                        Total Debit
                                    </span>

                                    <span
                                        class="
                                            font-semibold
                                        "
                                    >
                                        {{
                                            formatCurrency(
                                                totalDebit()
                                            )
                                        }}
                                    </span>

                                </div>


                                <!-- Total Credit -->

                                <div
                                    class="
                                        flex
                                        justify-between
                                        py-2
                                        text-sm
                                    "
                                >

                                    <span
                                        class="
                                            text-gray-600
                                        "
                                    >
                                        Total Credit
                                    </span>

                                    <span
                                        class="
                                            font-semibold
                                        "
                                    >
                                        {{
                                            formatCurrency(
                                                totalCredit()
                                            )
                                        }}
                                    </span>

                                </div>


                                <!-- Balance -->

                                <div
                                    class="
                                        mt-2
                                        flex
                                        justify-between
                                        border-t
                                        border-gray-200
                                        pt-3
                                    "
                                >

                                    <span
                                        class="
                                            font-semibold
                                            text-gray-900
                                        "
                                    >
                                        Balance
                                    </span>

                                    <span
                                        :class="
                                            isBalanced()
                                                ? 'text-lg font-bold text-emerald-600'
                                                : 'text-lg font-bold text-red-600'
                                        "
                                    >
                                        {{
                                            isBalanced()
                                                ? 'Balanced'
                                                : 'Not Balanced'
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
                                    text-amber-700
                                "
                            >
                                Please make sure the account,
                                debit, credit, entry date, and
                                journal information are correct.
                                Posting this journal entry will
                                create the corresponding general
                                ledger entries.
                            </p>

                        </div>

                    </template>

                </div>


                <!-- ================================================= -->
                <!-- Footer -->
                <!-- ================================================= -->

                <div
                    class="
                        flex
                        items-center
                        justify-end
                        gap-3
                        border-t
                        border-gray-200
                        bg-gray-50
                        px-6
                        py-4
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
                        variant="success"
                        :loading="loading"
                        :disabled="
                            !journalEntry ||
                            loading ||
                            !isBalanced()
                        "
                        @click="confirmPost"
                    >
                        Post Journal Entry
                    </BaseButton>

                </div>

            </div>

        </div>

    </Teleport>

</template>