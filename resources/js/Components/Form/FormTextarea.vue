<script setup>

import ValidationError

from './ValidationError.vue'

const props = defineProps({

    modelValue: {

        type: String,

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

    rows: {

        type: Number,

        default: 4

    },

    hint: {

        type: String,

        default: ''

    },

    error: {

        type: String,

        default: ''

    },

    required: {

        type: Boolean,

        default: false

    },

    readonly: {

        type: Boolean,

        default: false

    },

    disabled: {

        type: Boolean,

        default: false

    },

    maxlength: {

        type: [

            Number,

            String

        ],

        default: null

    }

})

const emit = defineEmits([

    'update:modelValue'

])

</script>

<template>

    <div>

        <!-- Label -->

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

                class="ml-1 text-red-500"

            >

                *

            </span>

        </label>

        <!-- Textarea -->

        <textarea

            :value="modelValue"

            @input="

                emit(

                    'update:modelValue',

                    $event.target.value

                )

            "

            :rows="rows"

            :placeholder="placeholder"

            :readonly="readonly"

            :disabled="disabled"

            :maxlength="maxlength"

            class="
                w-full
                rounded-xl
                border
                border-gray-300
                bg-white
                px-4
                py-3
                text-sm
                transition
                resize-none
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

        />

        <!-- Hint -->

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

        <!-- Error -->

        <ValidationError

            :message="error"

        />

    </div>

</template>