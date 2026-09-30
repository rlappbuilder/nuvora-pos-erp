<script setup>
import { computed } from 'vue'

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },

    sessionNumber: {
        type: String,
        default: '-',
    },

    openingBalance: {
        type: [Number, String],
        default: 0,
    },

    activeSession: {
    type: Object,
    default: null,
    },

    summary: {
        type: Object,
        default: () => ({
            session: {},
            transactions: {
                cash: {
                    transaction_count: 0,
                    amount: 0,
                },
                qris: {
                    transaction_count: 0,
                    amount: 0,
                },
                debit_card: {
                    transaction_count: 0,
                    amount: 0,
                },
                e_wallet: {
                    transaction_count: 0,
                    amount: 0,
                },
                transfer: {
                    transaction_count: 0,
                    amount: 0,
                },
                total: {
                    transaction_count: 0,
                    amount: 0,
                },
            },
            cash_movement: {
                opening_cash: 0,
                cash_sales: 0,
                cash_deposit: 0,
                expected_cash: 0,
            },
        }),
    },

    form: {
        type: Object,
        required: true,
    },

    processing: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits([
    'close',
    'submit',
])

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(Number(value || 0))
}

const transactionRows = computed(() => [
    {
        label: 'Cash',
        key: 'cash',
    },
    {
        label: 'QRIS',
        key: 'qris',
    },
    {
        label: 'Debit Card',
        key: 'debit_card',
    },
    {
        label: 'E-Wallet',
        key: 'e_wallet',
    },
    {
        label: 'Transfer',
        key: 'transfer',
    },
])

const expectedCash = computed(() => {
    return Number(
        props.summary?.cash_movement?.expected_cash ?? 0
    )
})

const actualCash = computed(() => {
    return Number(
        props.form?.closing_balance ?? 0
    )
})

const difference = computed(() => {
    return actualCash.value - expectedCash.value
})

const reconciliationStatus = computed(() => {
    if (difference.value === 0) {
        return {
            label: 'Balanced',
            class: 'bg-emerald-50 text-emerald-700',
            dot: 'bg-emerald-500',
        }
    }

    if (difference.value < 0) {
        return {
            label: 'Cash Short',
            class: 'bg-red-50 text-red-700',
            dot: 'bg-red-500',
        }
    }

    return {
        label: 'Cash Over',
        class: 'bg-amber-50 text-amber-700',
        dot: 'bg-amber-500',
    }
})
const formatDateTime = (value) => {
    if (!value) return '-'

    const date = new Date(value)

    if (Number.isNaN(date.getTime())) {
        return value
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
    }).format(date)
}
</script>

<template>
    <Teleport to="body">
        <div
            v-if="show"
            class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
        >
            <!-- Backdrop -->
            <div
                class="absolute inset-0 bg-slate-900/40 backdrop-blur-[2px]"
                @click="!processing && emit('close')"
            />

            <!-- Modal -->
            <div
                class="relative flex max-h-[calc(100vh-2rem)] w-full max-w-6xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl shadow-slate-900/20"
            >
                <!-- Header -->
                <div
                    class="shrink-0 border-b border-slate-100 px-6 py-5"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-600"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"
                                    />

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M5 7h14v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V7Z"
                                    />
                                </svg>
                            </div>

                            <div>
                                <h3
                                    class="text-base font-semibold text-slate-900"
                                >
                                    Close Cashier Session
                                </h3>

                                <p
                                    class="mt-1 text-xs leading-5 text-slate-500"
                                >
                                    Reconcile the cashier session before closing.
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 disabled:opacity-50"
                            :disabled="processing"
                            @click="emit('close')"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M6 6l12 12M18 6 6 18"
                                />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Body -->
                <div
                    class="min-h-0 flex-1 overflow-y-auto px-6 py-5"
                >
                    <div
                        class="grid grid-cols-1 gap-5 lg:grid-cols-2"
                    >
                        <!-- ================================================= -->
                        <!-- LEFT COLUMN -->
                        <!-- ================================================= -->

                        <div class="space-y-5">
                            <!-- Session -->
                            <section
                                class="rounded-xl border border-slate-100 bg-white"
                            >
                                <div
                                    class="border-b border-slate-100 px-4 py-3"
                                >
                                    <h4
                                        class="text-sm font-semibold text-slate-800"
                                    >
                                        Session Information
                                    </h4>
                                </div>

                                <div
                                    class="grid grid-cols-2 gap-x-4 gap-y-4 px-4 py-4"
                                >
                                    <div>
                                        <div
                                            class="text-[11px] font-medium uppercase tracking-wide text-slate-400"
                                        >
                                            Session
                                        </div>

                                        <div
                                            class="mt-1 text-sm font-semibold text-slate-800"
                                        >
                                            {{ sessionNumber }}
                                        </div>
                                    </div>

                                    <div>
                                        <div
                                            class="text-[11px] font-medium uppercase tracking-wide text-slate-400"
                                        >
                                            Cashier
                                        </div>

                                       <div
                                            class="mt-1 text-sm font-medium text-slate-800"
                                        >
                                            {{ activeSession?.user?.name ?? '-' }}
                                        </div>
                                    </div>

                                    <div>
                                        <div
                                            class="text-[11px] font-medium uppercase tracking-wide text-slate-400"
                                        >
                                            Branch
                                        </div>

                                        <div
                                            class="mt-1 text-sm font-medium text-slate-800"
                                        >
                                            {{ activeSession?.branch?.name ?? '-' }}
                                        </div>
                                    </div>

                                    <div>
                                        <div
                                            class="text-[11px] font-medium uppercase tracking-wide text-slate-400"
                                        >
                                            Warehouse
                                        </div>

                                    <div
                                        class="mt-1 text-sm font-medium text-slate-800"
                                    >
                                        {{ activeSession?.warehouse?.name ?? '-' }}
                                    </div>
                                    </div>

                                    <div class="col-span-2">
                                        <div
                                            class="text-[11px] font-medium uppercase tracking-wide text-slate-400"
                                        >
                                            Opened At
                                        </div>

                                      <div
                                            class="mt-1 text-sm font-medium text-slate-800"
                                        >
                                            {{ formatDateTime(activeSession?.opened_at) }}
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <!-- Transaction Summary -->
                            <section
                                class="rounded-xl border border-slate-100 bg-white"
                            >
                                <div
                                    class="border-b border-slate-100 px-4 py-3"
                                >
                                    <h4
                                        class="text-sm font-semibold text-slate-800"
                                    >
                                        Transaction Summary
                                    </h4>
                                </div>

                                <div class="px-4 py-2">
                                    <div
                                        v-for="item in transactionRows"
                                        :key="item.key"
                                        class="flex items-center justify-between gap-4 py-3"
                                    >
                                        <div class="min-w-0">
                                            <div
                                                class="text-sm font-medium text-slate-700"
                                            >
                                                {{ item.label }}
                                            </div>

                                            <div
                                                class="mt-0.5 text-[11px] text-slate-400"
                                            >
                                                {{
                                                    summary?.transactions?.[item.key]?.transaction_count ?? 0
                                                }}
                                                transactions
                                            </div>
                                        </div>

                                        <div
                                            class="shrink-0 text-right text-sm font-semibold tabular-nums text-slate-800"
                                        >
                                            {{
                                                formatCurrency(
                                                    summary?.transactions?.[item.key]?.amount ?? 0
                                                )
                                            }}
                                        </div>
                                    </div>

                                    <div
                                        class="flex items-center justify-between border-t border-slate-100 py-3"
                                    >
                                        <div>
                                            <div
                                                class="text-sm font-semibold text-slate-800"
                                            >
                                                Total Sales
                                            </div>

                                            <div
                                                class="mt-0.5 text-[11px] text-slate-400"
                                            >
                                                {{
                                                    summary?.transactions?.total?.transaction_count ?? 0
                                                }}
                                                transactions
                                            </div>
                                        </div>

                                        <div
                                            class="text-sm font-bold tabular-nums text-slate-900"
                                        >
                                            {{
                                                formatCurrency(
                                                    summary?.transactions?.total?.amount ?? 0
                                                )
                                            }}
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>

                        <!-- ================================================= -->
                        <!-- RIGHT COLUMN -->
                        <!-- ================================================= -->

                        <div class="space-y-5">
                            <!-- Cash Movement -->
                            <section
                                class="rounded-xl border border-slate-100 bg-white"
                            >
                                <div
                                    class="border-b border-slate-100 px-4 py-3"
                                >
                                    <h4
                                        class="text-sm font-semibold text-slate-800"
                                    >
                                        Cash Movement
                                    </h4>
                                </div>

                                <div class="px-4 py-4">
                                    <div
                                        class="space-y-3"
                                    >
                                        <div
                                            class="flex items-center justify-between gap-4"
                                        >
                                            <span
                                                class="text-sm text-slate-600"
                                            >
                                                Opening Cash
                                            </span>

                                            <span
                                                class="text-sm font-semibold tabular-nums text-slate-800"
                                            >
                                                {{
                                                    formatCurrency(
                                                        summary?.cash_movement?.opening_cash ?? 0
                                                    )
                                                }}
                                            </span>
                                        </div>

                                        <div
                                            class="flex items-center justify-between gap-4"
                                        >
                                            <span
                                                class="text-sm text-slate-600"
                                            >
                                                + Cash Sales
                                            </span>

                                            <span
                                                class="text-sm font-semibold tabular-nums text-slate-800"
                                            >
                                                {{
                                                    formatCurrency(
                                                        summary?.cash_movement?.cash_sales ?? 0
                                                    )
                                                }}
                                            </span>
                                        </div>

                                        <div
                                            class="flex items-center justify-between gap-4"
                                        >
                                            <span
                                                class="text-sm text-slate-600"
                                            >
                                                - Cash Deposit
                                            </span>

                                            <span
                                                class="text-sm font-semibold tabular-nums text-slate-800"
                                            >
                                                {{
                                                    formatCurrency(
                                                        summary?.cash_movement?.cash_deposit ?? 0
                                                    )
                                                }}
                                            </span>
                                        </div>

                                        <div
                                            class="border-t border-slate-200 pt-3"
                                        >
                                            <div
                                                class="flex items-center justify-between gap-4"
                                            >
                                                <span
                                                    class="text-sm font-semibold text-slate-800"
                                                >
                                                    Expected Cash
                                                </span>

                                                <span
                                                    class="text-base font-bold tabular-nums text-slate-900"
                                                >
                                                    {{
                                                        formatCurrency(
                                                            expectedCash
                                                        )
                                                    }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <!-- Cash Reconciliation -->
                            <section
                                class="rounded-xl border border-slate-100 bg-white"
                            >
                                <div
                                    class="border-b border-slate-100 px-4 py-3"
                                >
                                    <h4
                                        class="text-sm font-semibold text-slate-800"
                                    >
                                        Cash Reconciliation
                                    </h4>
                                </div>

                                <div class="space-y-5 px-4 py-4">
                                    <div>
                                        <label
                                            for="closing_balance"
                                            class="mb-1.5 block text-xs font-semibold text-slate-700"
                                        >
                                            Actual Cash
                                            <span class="text-red-500">*</span>
                                        </label>

                                        <div class="relative">
                                            <span
                                                class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-sm font-medium text-slate-400"
                                            >
                                                Rp
                                            </span>

                                            <input
                                                id="closing_balance"
                                                v-model.number="form.closing_balance"
                                                type="number"
                                                min="0"
                                                step="0.01"
                                                class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-3.5 text-right text-base font-bold tabular-nums text-slate-800 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10"
                                            />
                                        </div>

                                        <p
                                            class="mt-1.5 text-[11px] leading-5 text-slate-400"
                                        >
                                            Enter the physical cash counted in the drawer.
                                        </p>
                                    </div>

                                    <div
                                        class="rounded-xl bg-slate-50 px-4 py-3"
                                    >
                                        <div
                                            class="flex items-center justify-between gap-4"
                                        >
                                            <span
                                                class="text-sm font-medium text-slate-600"
                                            >
                                                Expected Cash
                                            </span>

                                            <span
                                                class="text-sm font-semibold tabular-nums text-slate-800"
                                            >
                                                {{ formatCurrency(expectedCash) }}
                                            </span>
                                        </div>

                                        <div
                                            class="mt-3 flex items-center justify-between gap-4"
                                        >
                                            <span
                                                class="text-sm font-semibold text-slate-700"
                                            >
                                                Difference
                                            </span>

                                            <span
                                                class="text-base font-bold tabular-nums"
                                                :class="{
                                                    'text-emerald-600': difference === 0,
                                                    'text-red-600': difference < 0,
                                                    'text-amber-600': difference > 0,
                                                }"
                                            >
                                                {{
                                                    difference > 0 ? '+' : ''
                                                }}{{ formatCurrency(difference) }}
                                            </span>
                                        </div>
                                    </div>

                                    <div
                                        class="flex items-center justify-between rounded-xl px-4 py-3"
                                        :class="reconciliationStatus.class"
                                    >
                                        <span
                                            class="text-sm font-semibold"
                                        >
                                            Reconciliation Status
                                        </span>

                                        <span
                                            class="inline-flex items-center gap-2 text-sm font-semibold"
                                        >
                                            <span
                                                class="h-2 w-2 rounded-full"
                                                :class="reconciliationStatus.dot"
                                            />

                                            {{ reconciliationStatus.label }}
                                        </span>
                                    </div>
                                </div>
                            </section>

                            <!-- Closing Note -->
                            <section>
                                <label
                                    for="closing_note"
                                    class="mb-1.5 block text-xs font-semibold text-slate-700"
                                >
                                    Closing Note
                                </label>

                                <textarea
                                    id="closing_note"
                                    v-model="form.closing_note"
                                    rows="3"
                                    class="w-full resize-none rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10"
                                    placeholder="Optional note..."
                                />
                            </section>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div
                    class="shrink-0 flex items-center justify-end gap-2 border-t border-slate-100 bg-slate-50/70 px-6 py-4"
                >
                    <button
                        type="button"
                        class="rounded-xl px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-800 disabled:opacity-50"
                        :disabled="processing"
                        @click="emit('close')"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="processing"
                        @click="emit('submit')"
                    >
                        <svg
                            v-if="processing"
                            class="h-4 w-4 animate-spin"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            />

                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z"
                            />
                        </svg>

                        Close Session
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>