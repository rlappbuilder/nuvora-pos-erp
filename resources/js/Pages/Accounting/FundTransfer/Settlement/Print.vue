<script setup>

import {
    computed,
    onMounted,
} from 'vue'

import { router } from '@inertiajs/vue3'

import StatusBadge
    from '@/Components/Display/StatusBadge.vue'


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({
    settlement: {
        type: Object,
        default: null,
    },
})


/*
|--------------------------------------------------------------------------
| Computed
|--------------------------------------------------------------------------
*/

const settlement = computed(() => {
    return props.settlement ?? null
})

const status = computed(() => {
    return String(
        settlement.value?.status ?? ''
    ).toLowerCase()
})

const receiptStatus = computed(() => {
    return String(
        settlement.value?.receipt_status ?? ''
    ).toLowerCase()
})

const details = computed(() => {
    return settlement.value?.details ?? []
})

const activities = computed(() => {
    return settlement.value?.activities ?? []
})


/*
|--------------------------------------------------------------------------
| Formatters
|--------------------------------------------------------------------------
*/

const formatCurrency = (value) => {
    return new Intl.NumberFormat(
        'id-ID',
        {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }
    ).format(Number(value || 0))
}


const formatDate = (value) => {
    if (!value) {
        return '-'
    }

    const date = new Date(value)

    if (Number.isNaN(date.getTime())) {
        return '-'
    }

    return new Intl.DateTimeFormat(
        'id-ID',
        {
            day: '2-digit',
            month: 'long',
            year: 'numeric',
        }
    ).format(date)
}


const formatDateTime = (value) => {
    if (!value) {
        return '-'
    }

    const date = new Date(value)

    if (Number.isNaN(date.getTime())) {
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
    ).format(date)
}


/*
|--------------------------------------------------------------------------
| Actions
|--------------------------------------------------------------------------
*/

const printPage = () => {
    window.print()
}

const goBack = () => {
    if (window.opener && !window.opener.closed) {
        window.close()
        return
    }

    if (window.history.length > 1) {
        window.history.back()
        return
    }

    router.visit(
        route('inter-branch-settlements.index')
    )
}


/*
|--------------------------------------------------------------------------
| Auto Print
|--------------------------------------------------------------------------
|
| Jangan langsung auto-print.
| User masuk ke halaman Print Preview terlebih dahulu.
|
|--------------------------------------------------------------------------
*/

onMounted(() => {
    document.title =
        `Inter-Branch Settlement - ${
            settlement.value?.settlement_number ?? ''
        }`
})

</script>


<template>

    <div
        class="
            min-h-screen
            bg-slate-100
            px-4
            py-6
            print:min-h-0
            print:bg-white
            print:p-0
        "
    >

        <!-- Toolbar -->

        <div
            class="
                mx-auto
                mb-5
                flex
                max-w-4xl
                items-center
                justify-between
                gap-3
                rounded-xl
                border
                border-slate-200
                bg-white
                px-4
                py-3
                shadow-sm
                print:hidden
            "
        >

            <button
                type="button"
                class="
                    inline-flex
                    items-center
                    gap-2
                    rounded-lg
                    border
                    border-slate-200
                    bg-white
                    px-3
                    py-2
                    text-sm
                    font-semibold
                    text-slate-700
                    transition
                    hover:bg-slate-50
                "
                @click="goBack"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M15 18l-6-6 6-6"
                    />
                </svg>

                Back
            </button>


            <div class="text-center">

                <div
                    class="
                        text-xs
                        font-semibold
                        uppercase
                        tracking-[0.14em]
                        text-slate-400
                    "
                >
                    Print Preview
                </div>

                <div
                    class="
                        mt-0.5
                        text-sm
                        font-semibold
                        text-slate-700
                    "
                >
                    Inter-Branch Settlement
                </div>

            </div>


            <button
                type="button"
                class="
                    inline-flex
                    items-center
                    gap-2
                    rounded-lg
                    bg-blue-600
                    px-4
                    py-2
                    text-sm
                    font-semibold
                    text-white
                    shadow-sm
                    transition
                    hover:bg-blue-700
                "
                @click="printPage"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M6 9V4h12v5"
                    />
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"
                    />
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M6 14h12v7H6z"
                    />
                </svg>

                Print
            </button>

        </div>


        <!-- Document -->

        <main
            class="
                mx-auto
                max-w-4xl
                overflow-hidden
                bg-white
                shadow-sm
                print:max-w-none
                print:overflow-visible
                print:shadow-none
            "
        >

            <!-- Header -->

            <header
                class="
                    border-b
                    border-slate-200
                    px-8
                    py-7
                    sm:px-10
                    print:px-0
                    print:py-0
                "
            >

                <div
                    class="
                        flex
                        items-start
                        justify-between
                        gap-6
                    "
                >

                    <div>

                        <div
                            class="
                                text-xl
                                font-bold
                                tracking-tight
                                text-slate-900
                            "
                        >
                            NUVORA POS
                        </div>

                        <div
                            class="
                                mt-1
                                text-[11px]
                                font-semibold
                                uppercase
                                tracking-[0.16em]
                                text-blue-600
                            "
                        >
                            Inter-Branch Settlement
                        </div>

                    </div>


                    <div class="text-right">

                        <div
                            class="
                                text-[10px]
                                font-semibold
                                uppercase
                                tracking-[0.14em]
                                text-slate-400
                            "
                        >
                            Settlement Number
                        </div>

                        <div
                            class="
                                mt-1
                                text-lg
                                font-bold
                                text-slate-900
                            "
                        >
                            {{
                                settlement?.settlement_number ?? '-'
                            }}
                        </div>

                        <div class="mt-1 flex justify-end">
                            <StatusBadge :status="status" />
                        </div>

                    </div>

                </div>

            </header>


            <!-- Content -->

            <div
                class="
                    px-8
                    py-7
                    sm:px-10
                    print:px-0
                    print:py-6
                "
            >

                <!-- Amount -->

                <section
                    class="
                        rounded-xl
                        border
                        border-blue-200
                        bg-blue-50
                        px-5
                        py-5
                        print:bg-white
                        print:px-0
                        print:py-4
                    "
                >

                    <div
                        class="
                            text-[10px]
                            font-semibold
                            uppercase
                            tracking-[0.14em]
                            text-blue-600
                        "
                    >
                        Settlement Amount
                    </div>

                    <div
                        class="
                            mt-1
                            text-3xl
                            font-bold
                            tabular-nums
                            text-blue-700
                        "
                    >
                        {{ formatCurrency(settlement?.amount) }}
                    </div>

                </section>


                <!-- Settlement Information -->

                <section class="mt-7">

                    <div
                        class="
                            mb-3
                            border-b
                            border-slate-200
                            pb-2
                        "
                    >
                        <h2
                            class="
                                text-sm
                                font-bold
                                text-slate-900
                            "
                        >
                            Settlement Information
                        </h2>
                    </div>


                    <div
                        class="
                            grid
                            grid-cols-2
                            border
                            border-slate-200
                        "
                    >

                        <!-- Settlement Date -->

                        <div
                            class="
                                border-b
                                border-r
                                border-slate-200
                                px-4
                                py-3
                            "
                        >
                            <div
                                class="
                                    text-[9px]
                                    font-semibold
                                    uppercase
                                    tracking-wide
                                    text-slate-400
                                "
                            >
                                Settlement Date
                            </div>

                            <div
                                class="
                                    mt-1
                                    text-sm
                                    font-medium
                                    text-slate-700
                                "
                            >
                                {{ formatDate(settlement?.settlement_date) }}
                            </div>
                        </div>


                        <!-- Payment Status -->

                        <div
                            class="
                                border-b
                                border-slate-200
                                px-4
                                py-3
                            "
                        >
                            <div
                                class="
                                    text-[9px]
                                    font-semibold
                                    uppercase
                                    tracking-wide
                                    text-slate-400
                                "
                            >
                                Payment Status
                            </div>

                            <div class="mt-2">
                                <StatusBadge :status="status" />
                            </div>
                        </div>


                        <!-- From Branch -->

                        <div
                            class="
                                border-b
                                border-r
                                border-slate-200
                                px-4
                                py-3
                            "
                        >
                            <div
                                class="
                                    text-[9px]
                                    font-semibold
                                    uppercase
                                    tracking-wide
                                    text-slate-400
                                "
                            >
                                From Branch
                            </div>

                            <div
                                class="
                                    mt-1
                                    text-sm
                                    font-semibold
                                    text-slate-800
                                "
                            >
                                {{
                                    settlement?.from_branch?.name ?? '-'
                                }}
                            </div>

                            <div
                                class="
                                    mt-0.5
                                    text-[11px]
                                    text-slate-400
                                "
                            >
                                {{
                                    settlement?.from_branch?.code ?? '-'
                                }}
                            </div>
                        </div>


                        <!-- To Branch -->

                        <div
                            class="
                                border-b
                                border-slate-200
                                px-4
                                py-3
                            "
                        >
                            <div
                                class="
                                    text-[9px]
                                    font-semibold
                                    uppercase
                                    tracking-wide
                                    text-slate-400
                                "
                            >
                                To Branch
                            </div>

                            <div
                                class="
                                    mt-1
                                    text-sm
                                    font-semibold
                                    text-slate-800
                                "
                            >
                                {{
                                    settlement?.to_branch?.name ?? '-'
                                }}
                            </div>

                            <div
                                class="
                                    mt-0.5
                                    text-[11px]
                                    text-slate-400
                                "
                            >
                                {{
                                    settlement?.to_branch?.code ?? '-'
                                }}
                            </div>
                        </div>


                        <!-- From Account -->

                        <div
                            class="
                                border-b
                                border-r
                                border-slate-200
                                px-4
                                py-3
                            "
                        >
                            <div
                                class="
                                    text-[9px]
                                    font-semibold
                                    uppercase
                                    tracking-wide
                                    text-slate-400
                                "
                            >
                                Payer Account
                            </div>

                            <div
                                class="
                                    mt-1
                                    text-sm
                                    font-semibold
                                    text-slate-800
                                "
                            >
                                {{
                                    settlement?.from_account?.name ?? '-'
                                }}
                            </div>

                            <div
                                class="
                                    mt-0.5
                                    text-[11px]
                                    text-slate-400
                                "
                            >
                                {{
                                    settlement?.from_account?.code ?? '-'
                                }}
                            </div>
                        </div>


                        <!-- To Account -->

                        <div
                            class="
                                border-b
                                border-slate-200
                                px-4
                                py-3
                            "
                        >
                            <div
                                class="
                                    text-[9px]
                                    font-semibold
                                    uppercase
                                    tracking-wide
                                    text-slate-400
                                "
                            >
                                Receiving Account
                            </div>

                            <div
                                class="
                                    mt-1
                                    text-sm
                                    font-semibold
                                    text-slate-800
                                "
                            >
                                {{
                                    settlement?.to_account?.name ?? '-'
                                }}
                            </div>

                            <div
                                class="
                                    mt-0.5
                                    text-[11px]
                                    text-slate-400
                                "
                            >
                                {{
                                    settlement?.to_account?.code ?? '-'
                                }}
                            </div>
                        </div>


                        <!-- Receipt Status -->

                        <div
                            class="
                                border-r
                                border-slate-200
                                px-4
                                py-3
                            "
                        >
                            <div
                                class="
                                    text-[9px]
                                    font-semibold
                                    uppercase
                                    tracking-wide
                                    text-slate-400
                                "
                            >
                                Receipt Status
                            </div>

                            <div class="mt-2">
                                <StatusBadge :status="receiptStatus" />
                            </div>
                        </div>


                        <!-- Description -->

                        <div class="px-4 py-3">

                            <div
                                class="
                                    text-[9px]
                                    font-semibold
                                    uppercase
                                    tracking-wide
                                    text-slate-400
                                "
                            >
                                Description
                            </div>

                            <div
                                class="
                                    mt-1
                                    whitespace-pre-line
                                    text-sm
                                    leading-5
                                    text-slate-700
                                "
                            >
                                {{ settlement?.description ?? '-' }}
                            </div>

                        </div>

                    </div>

                </section>


                <!-- Transfer Details -->

                <section
                    v-if="details.length"
                    class="mt-7"
                >

                    <div
                        class="
                            mb-3
                            border-b
                            border-slate-200
                            pb-2
                        "
                    >
                        <h2
                            class="
                                text-sm
                                font-bold
                                text-slate-900
                            "
                        >
                            Transfer Details
                        </h2>
                    </div>

                    <div class="overflow-x-auto">

                        <table
                            class="
                                w-full
                                border-collapse
                                text-left
                            "
                        >
                            <thead>
                                <tr class="bg-slate-50">

                                    <th
                                        class="
                                            border
                                            border-slate-200
                                            px-3
                                            py-2
                                            text-[10px]
                                            font-semibold
                                            uppercase
                                            text-slate-500
                                        "
                                    >
                                        No.
                                    </th>

                                    <th
                                        class="
                                            border
                                            border-slate-200
                                            px-3
                                            py-2
                                            text-[10px]
                                            font-semibold
                                            uppercase
                                            text-slate-500
                                        "
                                    >
                                        Fund Transfer
                                    </th>

                                    <th
                                        class="
                                            border
                                            border-slate-200
                                            px-3
                                            py-2
                                            text-right
                                            text-[10px]
                                            font-semibold
                                            uppercase
                                            text-slate-500
                                        "
                                    >
                                        Amount
                                    </th>

                                </tr>
                            </thead>

                            <tbody>

                                <tr
                                    v-for="(detail, index) in details"
                                    :key="detail.id ?? index"
                                >

                                    <td
                                        class="
                                            border
                                            border-slate-200
                                            px-3
                                            py-2
                                            text-xs
                                            text-slate-600
                                        "
                                    >
                                        {{ index + 1 }}
                                    </td>

                                    <td
                                        class="
                                            border
                                            border-slate-200
                                            px-3
                                            py-2
                                            text-xs
                                            text-slate-700
                                        "
                                    >
                                        {{
                                            detail?.fund_transfer?.transfer_number
                                            ?? detail?.fund_transfer?.code
                                            ?? detail?.fund_transfer?.description
                                            ?? '-'
                                        }}
                                    </td>

                                    <td
                                        class="
                                            border
                                            border-slate-200
                                            px-3
                                            py-2
                                            text-right
                                            text-xs
                                            font-medium
                                            tabular-nums
                                            text-slate-700
                                        "
                                    >
                                        {{ formatCurrency(detail?.amount) }}
                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                </section>


                <!-- Journal Entries -->

                <section class="mt-7">

                    <div
                        class="
                            mb-3
                            border-b
                            border-slate-200
                            pb-2
                        "
                    >
                        <h2
                            class="
                                text-sm
                                font-bold
                                text-slate-900
                            "
                        >
                            Journal Entries
                        </h2>
                    </div>

                    <div
                        class="
                            grid
                            grid-cols-2
                            border
                            border-slate-200
                        "
                    >

                        <div
                            class="
                                border-r
                                border-slate-200
                                px-4
                                py-3
                            "
                        >
                            <div
                                class="
                                    text-[9px]
                                    font-semibold
                                    uppercase
                                    tracking-wide
                                    text-slate-400
                                "
                            >
                                Payment Journal
                            </div>

                            <div
                                class="
                                    mt-1
                                    break-words
                                    text-sm
                                    font-semibold
                                    text-slate-800
                                "
                            >
                                {{
                                    settlement?.payment_journal_entry?.code
                                    ?? settlement?.payment_journal_entry?.journal_number
                                    ?? '-'
                                }}
                            </div>
                        </div>

                        <div class="px-4 py-3">
                            <div
                                class="
                                    text-[9px]
                                    font-semibold
                                    uppercase
                                    tracking-wide
                                    text-slate-400
                                "
                            >
                                Receipt Journal
                            </div>

                            <div
                                class="
                                    mt-1
                                    break-words
                                    text-sm
                                    font-semibold
                                    text-slate-800
                                "
                            >
                                {{
                                    settlement?.receipt_journal_entry?.code
                                    ?? settlement?.receipt_journal_entry?.journal_number
                                    ?? '-'
                                }}
                            </div>
                        </div>

                    </div>

                </section>


                <!-- Workflow History -->

                <section
                    v-if="activities.length"
                    class="mt-7"
                >

                    <div
                        class="
                            mb-3
                            border-b
                            border-slate-200
                            pb-2
                        "
                    >
                        <h2
                            class="
                                text-sm
                                font-bold
                                text-slate-900
                            "
                        >
                            Workflow History
                        </h2>
                    </div>

                    <div class="overflow-hidden border border-slate-200">

                        <div
                            v-for="(activity, index) in activities"
                            :key="activity.id ?? index"
                            class="
                                flex
                                items-start
                                gap-3
                                border-b
                                border-slate-100
                                px-4
                                py-3
                                last:border-b-0
                            "
                        >

                            <div
                                class="
                                    mt-1
                                    h-2
                                    w-2
                                    shrink-0
                                    rounded-full
                                    bg-blue-500
                                "
                            ></div>

                            <div class="min-w-0 flex-1">

                                <div
                                    class="
                                        flex
                                        flex-wrap
                                        items-center
                                        justify-between
                                        gap-2
                                    "
                                >
                                    <div
                                        class="
                                            text-xs
                                            font-semibold
                                            text-slate-800
                                        "
                                    >
                                        {{
                                            activity.action
                                            ?? activity.event
                                            ?? '-'
                                        }}
                                    </div>

                                    <div
                                        class="
                                            text-[10px]
                                            text-slate-400
                                        "
                                    >
                                        {{ formatDateTime(activity.created_at) }}
                                    </div>
                                </div>

                                <div
                                    v-if="activity.description || activity.note"
                                    class="
                                        mt-1
                                        text-[11px]
                                        leading-5
                                        text-slate-500
                                    "
                                >
                                    {{
                                        activity.description
                                        ?? activity.note
                                    }}
                                </div>

                            </div>

                        </div>

                    </div>

                </section>


                <!-- Footer -->

                <section
                    class="
                        mt-8
                        border-t
                        border-slate-200
                        pt-5
                    "
                >

                    <div
                        class="
                            flex
                            items-end
                            justify-between
                            gap-6
                        "
                    >

                        <div>

                            <div
                                class="
                                    text-[10px]
                                    font-semibold
                                    uppercase
                                    tracking-[0.12em]
                                    text-slate-400
                                "
                            >
                                Document
                            </div>

                            <div
                                class="
                                    mt-1
                                    text-xs
                                    font-medium
                                    text-slate-600
                                "
                            >
                                Inter-Branch Settlement Receipt
                            </div>

                        </div>

                        <div class="text-right">

                            <div class="text-[10px] text-slate-400">
                                Generated
                            </div>

                            <div
                                class="
                                    mt-1
                                    text-xs
                                    font-medium
                                    text-slate-600
                                "
                            >
                                {{ formatDateTime(new Date()) }}
                            </div>

                        </div>

                    </div>

                </section>

            </div>

        </main>

    </div>

</template>


<style>

@media print {

    @page {
        size: A4;
        margin: 12mm;
    }

    html,
    body {
        margin: 0 !important;
        padding: 0 !important;
        background: #ffffff !important;
    }

    body {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

}

</style>