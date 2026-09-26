<script setup>
const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },

    currentBranch: {
        type: Object,
        default: null,
    },

    authUser: {
        type: Object,
        default: null,
    },

    warehouses: {
        type: Array,
        default: () => [],
    },

    cashAccounts: {
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
</script>

<template>
    <Teleport to="body">
        <div
            v-if="show"
            class="fixed inset-0 z-[100] flex items-center justify-center p-4"
        >
            <div
                class="absolute inset-0 bg-slate-900/40 backdrop-blur-[2px]"
                @click="emit('close')"
            />

            <div
                class="relative w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl shadow-slate-900/20"
            >
                <!-- Header -->
                <div class="border-b border-slate-100 px-6 py-5">
                    <div class="flex items-start gap-3">
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600"
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
                                    d="M12 3v18M3 12h18"
                                />
                            </svg>
                        </div>

                        <div>
                            <h3
                                class="text-base font-semibold text-slate-900"
                            >
                                Open Cashier Session
                            </h3>

                            <p
                                class="mt-1 text-xs leading-5 text-slate-500"
                            >
                                Start a new cashier session for your
                                current branch.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Body -->
                <div class="space-y-5 px-6 py-5">

                    <!-- Branch -->
                    <div>
                        <label
                            class="mb-1.5 block text-xs font-semibold text-slate-700"
                        >
                            Branch
                        </label>

                        <div
                            class="rounded-xl bg-slate-50 px-3.5 py-3"
                        >
                            <div
                                class="text-sm font-medium text-slate-800"
                            >
                                {{ currentBranch?.code ?? '-' }}

                                <span
                                    v-if="currentBranch?.name"
                                    class="font-normal text-slate-500"
                                >
                                    • {{ currentBranch.name }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Cashier -->
                    <div>
                        <label
                            class="mb-1.5 block text-xs font-semibold text-slate-700"
                        >
                            Cashier
                        </label>

                        <div
                            class="rounded-xl bg-slate-50 px-3.5 py-3"
                        >
                            <div
                                class="text-sm font-medium text-slate-800"
                            >
                                {{ authUser?.name ?? '-' }}
                            </div>
                        </div>
                    </div>

                    <!-- Warehouse -->
                    <div>
                        <label
                            for="warehouse_id"
                            class="mb-1.5 block text-xs font-semibold text-slate-700"
                        >
                            Warehouse
                            <span class="text-red-500">*</span>
                        </label>

                        <select
                            id="warehouse_id"
                            v-model="form.warehouse_id"
                            class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-sm text-slate-800 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10"
                        >
                            <option
                                value=""
                                disabled
                            >
                                Select warehouse
                            </option>

                            <option
                                v-for="warehouse in warehouses"
                                :key="warehouse.id"
                                :value="warehouse.id"
                            >
                                {{ warehouse.code }} -
                                {{ warehouse.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Cash Account -->
                    <div>
                        <label
                            for="cash_account_id"
                            class="mb-1.5 block text-xs font-semibold text-slate-700"
                        >
                            Cash Account
                            <span class="text-red-500">*</span>
                        </label>

                        <select
                            id="cash_account_id"
                            v-model="form.cash_account_id"
                            class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-sm text-slate-800 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10"
                        >
                            <option
                                value=""
                                disabled
                            >
                                Select cash account
                            </option>

                            <option
                                v-for="account in cashAccounts"
                                :key="account.id"
                                :value="account.id"
                            >
                                {{ account.code }} -
                                {{ account.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Opening Balance -->
                    <div>
                        <label
                            for="opening_balance"
                            class="mb-1.5 block text-xs font-semibold text-slate-700"
                        >
                            Opening Balance
                            <span class="text-red-500">*</span>
                        </label>

                        <div class="relative">
                            <span
                                class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-sm font-medium text-slate-400"
                            >
                                Rp
                            </span>

                            <input
                                id="opening_balance"
                                v-model.number="form.opening_balance"
                                type="number"
                                min="0"
                                step="0.01"
                                class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-3.5 text-right text-sm font-semibold tabular-nums text-slate-800 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10"
                            />
                        </div>

                        <p
                            class="mt-1.5 text-[11px] leading-5 text-slate-400"
                        >
                            If there is a previous closed session,
                            the system will use the calculated
                            carry-forward balance.
                        </p>
                    </div>
                </div>

                <!-- Footer -->
                <div
                    class="flex items-center justify-end gap-2 border-t border-slate-100 bg-slate-50/70 px-6 py-4"
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
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="
                            processing ||
                            !form.warehouse_id ||
                            !form.cash_account_id
                        "
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
                                d="M4 12a8 8 0 0 0 8-8v4a4 4 0 0 0-4 4H4Z"
                            />
                        </svg>

                        Open Session
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>