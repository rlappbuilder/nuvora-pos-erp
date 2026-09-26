<script setup>
defineProps({
    show: {
        type: Boolean,
        default: false,
    },

    sessionNumber: {
        type: String,
        default: '-',
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
            class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
        >
            <!-- Backdrop -->
            <div
                class="absolute inset-0 bg-slate-900/40 backdrop-blur-[2px]"
                @click="emit('close')"
            />

            <!-- Modal -->
            <div
                class="relative w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl shadow-slate-900/20"
            >
                <!-- Modal Header -->
                <div class="border-b border-slate-100 px-6 py-5">
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
                                Reconcile the physical cash before
                                closing this session.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="space-y-5 px-6 py-5">
                    <!-- Session -->
                    <div
                        class="rounded-xl bg-slate-50 px-4 py-3"
                    >
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

                    <!-- Closing Balance -->
                    <div>
                        <label
                            for="closing_balance"
                            class="mb-1.5 block text-xs font-semibold text-slate-700"
                        >
                            Closing Balance
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
                                class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-3.5 text-right text-sm font-semibold tabular-nums text-slate-800 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10"
                            />
                        </div>
                    </div>

                    <!-- Note -->
                    <div>
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
                    </div>
                </div>

                <!-- Modal Footer -->
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
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60"
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