<script setup>
import { computed } from 'vue'
import FlatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },

    sessionNumber: {
        type: String,
        default: '-',
    },

    activeSession: {
        type: Object,
        default: null,
    },

    availableCash: {
        type: Number,
        default: 0,
    },

    depositAccounts: {
        type: Array,
        default: () => [],
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

const flatpickrConfig = {
    dateFormat: 'Y-m-d',
    allowInput: true,
    clickOpens: true,
}

const depositAmount = computed(() => {
    const amount = Number(props.form.amount)

    return Number.isFinite(amount) && amount > 0
        ? amount
        : 0
})

const remainingCash = computed(() => {
    return Math.max(
        0,
        Number(props.availableCash || 0) -
        depositAmount.value
    )
})

const canSubmit = computed(() => {
    return (
        depositAmount.value > 0 &&
        props.form.destination_account_id &&
        props.form.deposited_at &&
        depositAmount.value <= Number(props.availableCash || 0) &&
        !props.processing
    )
})

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(Number(value || 0))
}

/*
|--------------------------------------------------------------------------
| Deposit Amount
|--------------------------------------------------------------------------
*/

const formatAmount = (value) => {
    if (
        value === null ||
        value === undefined ||
        value === ''
    ) {
        return ''
    }

    const raw = String(value).replace(/\D/g, '')

    if (!raw) {
        return ''
    }

    return new Intl.NumberFormat('id-ID').format(
        Number(raw)
    )
}

const handleAmountInput = (event) => {
    const raw = event.target.value.replace(/\D/g, '')

    props.form.amount = raw
        ? Number(raw)
        : 0
}

const submit = () => {
    if (!canSubmit.value) {
        return
    }

    emit('submit')
}
</script>

<template>
    <Teleport to="body">
        <div
            v-if="show"
            class="fixed inset-0 z-[100] flex items-center justify-center p-4"
        >
            <!-- Backdrop -->
            <div
                class="absolute inset-0 bg-slate-950/50 backdrop-blur-[2px]"
                @click="!processing && emit('close')"
            ></div>

            <!-- Modal -->
            <div
                class="relative w-full max-w-4xl overflow-hidden rounded-2xl bg-white shadow-2xl"
            >
                <!-- Header -->
                <div
                    class="border-b border-slate-200 bg-white px-6 py-5"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div
                                class="mb-1 flex items-center gap-2"
                            >
                                <div
                                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M12 6v12m-3-3 3 3 3-3M6 3h12"
                                        />
                                    </svg>
                                </div>

                                <h2
                                    class="text-lg font-semibold text-slate-900"
                                >
                                    Cash Deposit
                                </h2>
                            </div>

                            <p class="text-sm text-slate-500">
                                Submit cash deposit for supervisor approval.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                            :disabled="processing"
                            @click="emit('close')"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M6 18 18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Body -->
                <div class="max-h-[calc(100vh-220px)] overflow-y-auto">
                    <div class="space-y-6 p-6">

                        <!-- Session Information -->
                        <section>
                            <div
                                class="mb-3 flex items-center justify-between"
                            >
                                <div>
                                    <h3
                                        class="text-sm font-semibold text-slate-900"
                                    >
                                        Session Information
                                    </h3>

                                    <p
                                        class="mt-0.5 text-xs text-slate-500"
                                    >
                                        Current cashier session
                                    </p>
                                </div>

                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700"
                                >
                                    <span
                                        class="h-1.5 w-1.5 rounded-full bg-emerald-500"
                                    ></span>

                                    Open
                                </span>
                            </div>

                            <div
                                class="grid grid-cols-2 gap-x-6 gap-y-4 rounded-xl border border-slate-200 bg-slate-50/70 px-4 py-4 md:grid-cols-4"
                            >
                                <div>
                                    <p
                                        class="text-[11px] font-medium uppercase tracking-wide text-slate-400"
                                    >
                                        Session
                                    </p>

                                    <p
                                        class="mt-1 text-sm font-semibold text-slate-800"
                                    >
                                        {{ sessionNumber }}
                                    </p>
                                </div>

                                <div>
                                    <p
                                        class="text-[11px] font-medium uppercase tracking-wide text-slate-400"
                                    >
                                        Branch
                                    </p>

                                    <p
                                        class="mt-1 text-sm font-medium text-slate-700"
                                    >
                                        {{
                                            activeSession?.branch?.name ?? '-'
                                        }}
                                    </p>
                                </div>

                                <div>
                                    <p
                                        class="text-[11px] font-medium uppercase tracking-wide text-slate-400"
                                    >
                                        Warehouse
                                    </p>

                                    <p
                                        class="mt-1 text-sm font-medium text-slate-700"
                                    >
                                        {{
                                            activeSession?.warehouse?.name ?? '-'
                                        }}
                                    </p>
                                </div>

                                <div>
                                    <p
                                        class="text-[11px] font-medium uppercase tracking-wide text-slate-400"
                                    >
                                        Cashier
                                    </p>

                                    <p
                                        class="mt-1 text-sm font-medium text-slate-700"
                                    >
                                        {{
                                            activeSession?.user?.name ?? '-'
                                        }}
                                    </p>
                                </div>
                            </div>
                        </section>

                        <!-- Cash Position -->
                        <section>
                            <div class="mb-3">
                                <h3
                                    class="text-sm font-semibold text-slate-900"
                                >
                                    Cash Position
                                </h3>

                                <p
                                    class="mt-0.5 text-xs text-slate-500"
                                >
                                    Current cash position of this session
                                </p>
                            </div>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                                <!-- Available Cash -->
                                <div
                                    class="relative overflow-hidden rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4"
                                >
                                    <div
                                        class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-emerald-100/80"
                                    ></div>

                                    <div class="relative">
                                        <div
                                            class="flex items-center gap-2 text-emerald-700"
                                        >
                                            <div
                                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/80"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="h-4 w-4"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="2"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="M12 8c-2.21 0-4 1.12-4 2.5S9.79 13 12 13s4 1.12 4 2.5S14.21 18 12 18m0-12v12m4-9.5C16 7.12 14.21 6 12 6S8 7.12 8 8.5"
                                                    />
                                                </svg>
                                            </div>

                                            <span
                                                class="text-xs font-semibold uppercase tracking-wide"
                                            >
                                                Available Cash
                                            </span>
                                        </div>

                                        <p
                                            class="mt-3 text-2xl font-bold tracking-tight text-emerald-800"
                                        >
                                            {{ formatCurrency(availableCash) }}
                                        </p>

                                        <p
                                            class="mt-1 text-xs text-emerald-700/70"
                                        >
                                            Cash currently available for deposit
                                        </p>
                                    </div>
                                </div>

                                <!-- Remaining Cash -->
                                <div
                                    class="relative overflow-hidden rounded-xl border border-amber-200 bg-amber-50 px-5 py-4"
                                >
                                    <div
                                        class="absolute -right-8 -top-8 h-24 w-24 rounded-full bg-amber-100"
                                    ></div>

                                    <div class="relative">
                                        <div
                                            class="flex items-center gap-2 text-amber-700"
                                        >
                                            <div
                                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/80"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="h-4 w-4"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="2"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="M12 9v4m0 4h.01M10.29 3.86l-8.24 14A2 2 0 003.78 21h16.44a2 2 0 001.73-3.14l-8.24-14a2 2 0 00-3.42 0z"
                                                    />
                                                </svg>
                                            </div>

                                            <span
                                                class="text-xs font-semibold uppercase tracking-wide"
                                            >
                                                Remaining Cash
                                            </span>
                                        </div>

                                        <p
                                            class="mt-3 text-2xl font-bold tracking-tight text-amber-800"
                                        >
                                            {{ formatCurrency(remainingCash) }}
                                        </p>

                                        <p
                                            class="mt-1 text-xs text-amber-700/70"
                                        >
                                            Estimated cash after this deposit
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- Deposit Information -->
                        <section>
                            <div class="mb-3">
                                <h3
                                    class="text-sm font-semibold text-slate-900"
                                >
                                    Deposit Information
                                </h3>

                                <p
                                    class="mt-0.5 text-xs text-slate-500"
                                >
                                    Enter the cash deposit details
                                </p>
                            </div>

                            <div
                                class="grid grid-cols-1 gap-5 md:grid-cols-2"
                            >
                                <!-- Amount -->
                                <div>
                                    <label
                                        class="mb-1.5 block text-sm font-medium text-slate-700"
                                    >
                                        Deposit Amount
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <div class="relative">
                                        <span
                                            class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-medium text-slate-400"
                                        >
                                            Rp
                                        </span>

                                        <input
                                            :value="formatAmount(form.amount)"
                                            type="text"
                                            inputmode="numeric"
                                            class="w-full rounded-lg border border-slate-300 px-3 py-2.5 pl-10 text-right text-sm font-medium tabular-nums text-slate-800 outline-none transition focus:border-emerald-500 focus:ring-emerald-500/10"
                                            placeholder="0"
                                            :disabled="processing"
                                            @input="handleAmountInput"
                                        />
                                    </div>
                                </div>

                                <!-- Destination Account -->
                                <div>
                                    <label
                                        class="mb-1.5 block text-sm font-medium text-slate-700"
                                    >
                                        Destination Account
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <select
                                        v-model="form.destination_account_id"
                                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-emerald-500 focus:ring-emerald-500/10"
                                        :disabled="processing"
                                    >
                                        <option value="">
                                            Select destination account
                                        </option>

                                        <option
                                            v-for="account in depositAccounts"
                                            :key="account.id"
                                            :value="account.id"
                                        >
                                            {{ account.code }} -
                                            {{ account.name }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Deposit Date -->
                                <div>
                                    <label
                                        class="mb-1.5 block text-sm font-medium text-slate-700"
                                    >
                                        Deposit Date
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <FlatPickr
                                        v-model="form.deposited_at"
                                        :config="flatpickrConfig"
                                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-emerald-500 focus:ring-emerald-500/10"
                                        placeholder="Select deposit date"
                                        :disabled="processing"
                                    />
                                </div>

                                <!-- Note -->
                                <div>
                                    <label
                                        class="mb-1.5 block text-sm font-medium text-slate-700"
                                    >
                                        Note
                                    </label>

                                    <input
                                        v-model="form.note"
                                        type="text"
                                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500/10"
                                        placeholder="Optional note"
                                        :disabled="processing"
                                    />
                                </div>
                            </div>

                            <!-- Warning -->
                            <div
                                v-if="depositAmount > availableCash"
                                class="mt-4 flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="mt-0.5 h-4 w-4 shrink-0"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 9v4m0 4h.01M10.29 3.86l-8.24 14A2 2 0 003.78 21h16.44a2 2 0 001.73-3.14l-8.24-14a2 2 0 00-3.42 0z"
                                    />
                                </svg>

                                <span>
                                    Deposit amount cannot exceed available cash.
                                </span>
                            </div>
                        </section>
                    </div>
                </div>

                <!-- Footer -->
                <div
                    class="flex items-center justify-end gap-3 border-t border-slate-200 bg-slate-50/70 px-6 py-4"
                >
                    <button
                        type="button"
                        class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="processing"
                        @click="emit('close')"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="!canSubmit"
                        @click="submit"
                    >
                        <svg
                            v-if="processing"
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4 animate-spin"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 3v3m6.36-.36-2.12 2.12M21 12h-3m.36 6.36-2.12-2.12M12 21v-3m-6.36.36 2.12-2.12M3 12h3m-.36-6.36 2.12 2.12"
                            />
                        </svg>

                        <span>
                            {{
                                processing
                                    ? 'Submitting...'
                                    : 'Submit Deposit'
                            }}
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>