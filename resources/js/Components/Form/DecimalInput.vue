<script setup>
import { computed } from 'vue'

const props = defineProps({
    modelValue: {
        type: [Number, String],
        default: null,
    },

    decimals: {
        type: Number,
        default: 6,
    },

    placeholder: {
        type: String,
        default: '',
    },

    disabled: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['update:modelValue'])

const displayValue = computed({
    get() {
        if (
            props.modelValue === null ||
            props.modelValue === '' ||
            props.modelValue === undefined
        ) {
            return ''
        }

        return Number(props.modelValue).toLocaleString('en-US', {
            minimumFractionDigits: props.decimals,
            maximumFractionDigits: props.decimals,
        })
    },

    set(value) {
        const number = value.replace(/,/g, '')

        emit('update:modelValue', number)
    },
})
</script>

<template>
    <input
        v-model="displayValue"
        type="text"
        :placeholder="placeholder"
        :disabled="disabled"
        class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500"
    />
</template>