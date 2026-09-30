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

    processing: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits([
    'close',
    'print',
])
</script>

<template>
    <Teleport to="body">
        <div
            v-if="show"
            class="fixed inset-0 z-[10000] flex items-center justify-center p-4"
        >
            <!-- Backdrop -->
            <div
                class="absolute inset-0 bg-slate-900/40 backdrop-blur-[2px]"
                @click="emit('close')"
            />

            <!-- Modal -->
            <div
                class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl shadow-slate-900/20"
            >
                <!-- Success Icon -->
                <div
                    class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 text-emerald-600"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="m5 12 4 4L19 6"
                        />
                    </svg>
                </div>

                <!-- Content -->
                <div class="mt-4 text-center">
                    <h3
                        class="text-base font-semibold text-slate-900"
                    >
                        Session Closed Successfully
                    </h3>

                    <p
                        class="mt-2 text-sm leading-6 text-slate-500"
                    >
                        Cashier session
                        <span
                            class="font-semibold text-slate-700"
                        >
                            {{ sessionNumber }}
                        </span>
                        has been closed successfully.
                    </p>
                </div>

                <!-- Actions -->
                <div
                    class="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-center"
                >
                    <button
                        type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="processing"
                        @click="emit('print')"
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
                                d="M6 14h12v6H6z"
                            />
                        </svg>

                        Print Close Session Report
                    </button>

                    <button
                        type="button"
                        class="rounded-xl px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-800"
                        :disabled="processing"
                        @click="emit('close')"
                    >
                        Done
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>