<script setup>
defineProps({
    loading: {
        type: Boolean,
        default: false,
    },

    disabled: {
        type: Boolean,
        default: false,
    },

    showRefresh: {
        type: Boolean,
        default: true,
    },

    showPrint: {
        type: Boolean,
        default: true,
    },

    showPdf: {
        type: Boolean,
        default: true,
    },

    showExcel: {
        type: Boolean,
        default: true,
    },
})

const emit = defineEmits([
    'refresh',
    'print',
    'pdf',
    'excel',
])

const buttonClass = `
    inline-flex
    shrink-0
    items-center
    justify-center
    gap-2
    rounded-lg
    border
    border-gray-200
    bg-white
    px-3
    py-2
    text-sm
    font-medium
    text-gray-700
    transition
    hover:bg-gray-50
    disabled:cursor-not-allowed
    disabled:opacity-50
`
</script>

<template>
    <div
        class="
            flex
            flex-wrap
            items-center
            gap-2
            print:hidden
        "
    >
        <!-- REFRESH -->
        <button
            v-if="showRefresh"
            type="button"
            :disabled="disabled || loading"
            :class="buttonClass"
            @click="emit('refresh')"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                class="h-4 w-4"
                :class="{ 'animate-spin': loading }"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M20 11a8.1 8.1 0 0 0-14.9-4M4 5v4h4"
                />

                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M4 13a8.1 8.1 0 0 0 14.9 4M20 19v-4h-4"
                />
            </svg>

            <span>Refresh</span>
        </button>

        <!-- PRINT -->
        <button
            v-if="showPrint"
            type="button"
            :disabled="disabled || loading"
            :class="buttonClass"
            @click="emit('print')"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                class="h-4 w-4"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M6 9V3h12v6"
                />

                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5h-2"
                />

                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M6 14h12v7H6z"
                />

                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M18 12h.01"
                />
            </svg>

            <span>Print</span>
        </button>

        <!-- PDF -->
        <button
            v-if="showPdf"
            type="button"
            :disabled="disabled || loading"
            :class="buttonClass"
            @click="emit('pdf')"
        >
            <svg
                viewBox="0 0 32 32"
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5"
            >
                <path
                    fill="#E53935"
                    d="M7 2h13l7 7v21H7z"
                />

                <path
                    fill="#fff"
                    d="M20 2v7h7"
                />

                <path
                    fill="#fff"
                    d="
                        M10 18.5h2.3
                        c2.1 0 3.3-1.1 3.3-2.8
                        0-1.7-1.2-2.7-3.3-2.7H10zm2.2-1.8
                        h-.5v-1.9h.5
                        c.7 0 1.2.3 1.2.9
                        0 .7-.5 1-1.2 1z
                    "
                />

                <path
                    fill="#fff"
                    d="
                        M17 18.5h2.1
                        c2.4 0 3.8-1 3.8-3.7
                        0-2.5-1.4-3.8-3.8-3.8H17zm2.1-5.7
                        h.3c1.1 0 1.7.6 1.7 2
                        0 1.5-.6 2-1.7 2h-.3z
                    "
                />
            </svg>

            <span>PDF</span>
        </button>

        <!-- EXCEL -->
        <button
            v-if="showExcel"
            type="button"
            :disabled="disabled || loading"
            :class="buttonClass"
            @click="emit('excel')"
        >
            <svg
                viewBox="0 0 32 32"
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5"
            >
                <path
                    fill="#21A366"
                    d="
                        M18 3h9a2 2 0 0 1 2 2v22
                        a2 2 0 0 1-2 2h-9z
                    "
                />

                <path
                    fill="#107C41"
                    d="
                        M3 7a2 2 0 0 1 2-2h13v22H5
                        a2 2 0 0 1-2-2z
                    "
                />

                <path
                    fill="#fff"
                    d="
                        M8.2 11h2.4l2.1 3.5
                        2-3.5h2.3l-3.1 5
                        3.2 5h-2.5l-2-3.6
                        -2.1 3.6H8.1l3.2-5z
                    "
                />
            </svg>

            <span>Excel</span>
        </button>
    </div>
</template>