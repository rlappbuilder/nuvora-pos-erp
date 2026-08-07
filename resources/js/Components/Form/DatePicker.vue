<script setup>

const props = defineProps({

    modelValue: {

        type: String,

        default: '',

    },

    placeholder: {

        type: String,

        default: 'Select Date',

    },

    disabled: {

        type: Boolean,

        default: false,

    },

    required: {

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

    type="date"

    :value="props.modelValue"

    @input="emit('update:modelValue', $event.target.value)"

    :placeholder="props.placeholder"

    :disabled="props.disabled"

    :required="props.required"

    class="
        w-full
        rounded-xl
        border
        border-gray-300
        bg-white
        px-4
        py-2.5
        text-sm
        shadow-sm
        transition

        focus:border-indigo-500
        focus:outline-none
        focus:ring-2
        focus:ring-indigo-100

        disabled:bg-gray-100
        disabled:text-gray-500
    "

/>

</template>