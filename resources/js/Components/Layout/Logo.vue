<script setup>
defineProps({
    collapsed: {
        type: Boolean,
        default: false,
    },
})
</script>

<template>
    <div
        class="flex items-center h-16 px-4 border-b border-gray-200 bg-white"
    >
        <div
            class="flex items-center justify-center w-10 h-10 rounded-xl bg-blue-600 text-white font-bold text-lg shrink-0"
        >
            N
        </div>

        <Transition
            enter-active-class="transition duration-200"
            leave-active-class="transition duration-150"
        >
            <div
                v-if="!collapsed"
                class="ml-3 overflow-hidden"
            >
                <h1 class="text-base font-bold text-gray-800">
                    Nuvora ERP
                </h1>

                <p class="text-xs text-gray-500">
                    Enterprise Suite
                </p>
            </div>
        </Transition>
    </div>
</template>