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

    deposit: {
        type: Object,
        default: null,
    },

})


/*
|--------------------------------------------------------------------------
| Computed
|--------------------------------------------------------------------------
*/

const deposit = computed(() => {
    return props.deposit ?? null
})

const status = computed(() => {
    return String(
        deposit.value?.status ?? ''
    ).toLowerCase()
})

const activities = computed(() => {
    return deposit.value?.activities ?? []
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
    ).format(
        Number(value || 0)
    )

}


const formatDate = (value) => {

    if (!value) {
        return '-'
    }

    return new Intl.DateTimeFormat(
        'id-ID',
        {
            day: '2-digit',
            month: 'long',
            year: 'numeric',
        }
    ).format(
        new Date(value)
    )

}


const formatDateTime = (value) => {

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


/*
|--------------------------------------------------------------------------
| Actions
|--------------------------------------------------------------------------
*/

const printPage = () => {
    window.print()
}


const goBack = () => {
    window.history.back()
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
        `Cash Deposit - ${
            deposit.value?.deposit_number ?? ''
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

        <!-- ========================================================= -->
        <!-- Toolbar -->
        <!-- ========================================================= -->

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


            <div
                class="
                    text-center
                "
            >

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
                    Cash Deposit
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


        <!-- ========================================================= -->
        <!-- Document -->
        <!-- ========================================================= -->

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

            <!-- ===================================================== -->
            <!-- Header -->
            <!-- ===================================================== -->

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
                            Cashier Deposit
                        </div>

                    </div>


                    <div
                        class="
                            text-right
                        "
                    >

                        <div
                            class="
                                text-[10px]
                                font-semibold
                                uppercase
                                tracking-[0.14em]
                                text-slate-400
                            "
                        >
                            Deposit Number
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
                                deposit?.deposit_number
                                ?? '-'
                            }}
                        </div>

                        <div
                            class="
                                mt-1
                                flex
                                justify-end
                            "
                        >

                            <StatusBadge
                                :status="status"
                            />

                        </div>

                    </div>

                </div>

            </header>


            <!-- ===================================================== -->
            <!-- Content -->
            <!-- ===================================================== -->

            <div
                class="
                    px-8
                    py-7
                    sm:px-10
                    print:px-0
                    print:py-6
                "
            >

                <!-- ================================================= -->
                <!-- Amount -->
                <!-- ================================================= -->

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
                        Deposit Amount
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
                        {{
                            formatCurrency(
                                deposit?.amount
                            )
                        }}
                    </div>

                </section>


                <!-- ================================================= -->
                <!-- Deposit Information -->
                <!-- ================================================= -->

                <section
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
                            Deposit Information
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

                        <!-- Session -->

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
                                Cashier Session
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
                                    deposit
                                        ?.session_number
                                    ??
                                    deposit
                                        ?.cashier_session
                                        ?.session_number
                                    ??
                                    '-'
                                }}
                            </div>

                        </div>


                        <!-- Deposit Date -->

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
                                Deposit Date
                            </div>

                            <div
                                class="
                                    mt-1
                                    text-sm
                                    font-medium
                                    text-slate-700
                                "
                            >
                                {{
                                    formatDate(
                                        deposit?.deposited_at
                                    )
                                }}
                            </div>

                        </div>


                        <!-- Destination -->

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
                                Destination Account
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
                                    deposit
                                        ?.destination_account
                                        ?.name
                                    ??
                                    '-'
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
                                    deposit
                                        ?.destination_account
                                        ?.code
                                    ??
                                    '-'
                                }}
                            </div>

                        </div>


                        <!-- Created -->

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
                                Created At
                            </div>

                            <div
                                class="
                                    mt-1
                                    text-sm
                                    font-medium
                                    text-slate-700
                                "
                            >
                                {{
                                    formatDateTime(
                                        deposit?.created_at
                                    )
                                }}
                            </div>

                        </div>


                        <!-- Status -->

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
                                Status
                            </div>

                            <div
                                class="
                                    mt-2
                                "
                            >

                                <StatusBadge
                                    :status="status"
                                />

                            </div>

                        </div>


                        <!-- Note -->

                        <div
                            class="
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
                                Note
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
                                {{
                                    deposit?.note
                                    ?? '-'
                                }}
                            </div>

                        </div>

                    </div>

                </section>


                <!-- ================================================= -->
                <!-- Workflow History -->
                <!-- ================================================= -->

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


                    <div
                        class="
                            overflow-hidden
                            border
                            border-slate-200
                        "
                    >

                        <div
                            v-for="(
                                activity,
                                index
                            ) in activities"
                            :key="
                                activity.id ??
                                index
                            "
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


                            <div
                                class="
                                    min-w-0
                                    flex-1
                                "
                            >

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
                                            ??
                                            activity.event
                                            ??
                                            '-'
                                        }}
                                    </div>

                                    <div
                                        class="
                                            text-[10px]
                                            text-slate-400
                                        "
                                    >
                                        {{
                                            formatDateTime(
                                                activity.created_at
                                            )
                                        }}
                                    </div>

                                </div>


                                <div
                                    v-if="
                                        activity.description
                                        ||
                                        activity.note
                                    "
                                    class="
                                        mt-1
                                        text-[11px]
                                        leading-5
                                        text-slate-500
                                    "
                                >
                                    {{
                                        activity.description
                                        ??
                                        activity.note
                                    }}
                                </div>

                            </div>

                        </div>

                    </div>

                </section>


                <!-- ================================================= -->
                <!-- Footer -->
                <!-- ================================================= -->

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
                                Cash Deposit Receipt
                            </div>

                        </div>


                        <div
                            class="
                                text-right
                            "
                        >

                            <div
                                class="
                                    text-[10px]
                                    text-slate-400
                                "
                            >
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
                                {{
                                    formatDateTime(
                                        new Date()
                                    )
                                }}
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