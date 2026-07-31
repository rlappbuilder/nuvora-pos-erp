<script setup>
const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },

    disabled: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits([
    'update:modelValue',
])
</script>

<template>
    <input
        type="checkbox"
        :checked="modelValue"
        :disabled="disabled"
        class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 disabled:cursor-not-allowed disabled:opacity-50"
        @change="emit('update:modelValue', $event.target.checked)"
    >
</template>