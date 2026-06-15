<script setup>

defineProps({
    show: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits([
    'close'
])

</script>

<template>

    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center"
    >

        <!-- Backdrop -->

        <div
            class="absolute inset-0 bg-black/40"
            @click="emit('close')"
        ></div>

        <!-- Modal -->

        <div
            class="relative w-full max-w-lg rounded-2xl bg-white shadow-2xl"
        >

            <!-- Header -->

            <div
                class="flex items-center justify-between border-b px-6 py-4"
            >

                <h2
                    class="text-lg font-bold text-gray-800"
                >
                    <slot name="title" />
                </h2>

                <button
                    @click="emit('close')"
                    class="text-gray-400 hover:text-red-500"
                >
                    ✕
                </button>

            </div>

            <!-- Body -->

            <div
                class="p-6"
            >
                <slot />
            </div>

            <!-- Footer -->

            <div
                class="flex justify-end gap-3 border-t px-6 py-4"
            >
                <slot name="footer" />
            </div>

        </div>

    </div>

</template>