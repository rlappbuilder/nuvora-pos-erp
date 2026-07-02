<script setup>

import ValidationError

from './ValidationError.vue'

const props = defineProps({

    modelValue: {

        type: [

            Number,

            String

        ],

        default: ''

    },

    label: {

        type: String,

        default: ''

    },

    placeholder: {

        type: String,

        default: ''

    },

    error: {

        type: String,

        default: ''

    },

    hint: {

        type: String,

        default: ''

    },

    required: {

        type: Boolean,

        default: false

    },

    disabled: {

        type: Boolean,

        default: false

    },

    readonly: {

        type: Boolean,

        default: false

    },

    min: {

        type: Number,

        default: null

    },

    max: {

        type: Number,

        default: null

    },
            suffix: {

        type: String,

        default: ''

    },

    prefix: {

        type: String,

        default: ''

    }



})

const emit = defineEmits([

    'update:modelValue'

])

function onInput(event)
{

    let value = event.target.value

    value = value.replace(

        /[^0-9.]/g,

        ''

    )

    emit(

        'update:modelValue',

        value

    )

}

</script>

<template>

<div>

    <label

        v-if="label"

        class="
            mb-2
            block
            text-sm
            font-medium
            text-gray-700
        "

    >

        {{ label }}

        <span

            v-if="required"

            class="text-red-500"

        >

            *

        </span>

    </label>
    

            <input

                :value="modelValue"

                @input="onInput"

                inputmode="decimal"

                :placeholder="placeholder"

                :readonly="readonly"

                :disabled="disabled"

                :min="min"

                :max="max"

                class="
                    w-full
                    rounded-xl
                    border
                    border-gray-300
                    bg-white
                    px-4
                    py-2.5
                    text-sm
                    transition
                    focus:border-indigo-500
                    focus:outline-none
                    focus:ring-2
                    focus:ring-indigo-100
                    disabled:bg-gray-100
                    readonly:bg-gray-50
                "

                :class="

                    error

                        ? 'border-red-400 focus:border-red-500 focus:ring-red-100'

                        : ''

                "

            >

            <p

                v-if="hint && !error"

                class="
                    mt-2
                    text-xs
                    text-gray-500
                "

            >

                {{ hint }}

            </p>

            <ValidationError

                :message="error"

            />

</div>

</template>