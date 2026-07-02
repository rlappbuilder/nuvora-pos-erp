<script setup>



import ValidationError

from './ValidationError.vue'

import {

    Radius,

    InputSize,

    InputBase,

    LabelClass,

    HintClass,

    ErrorClass,

    NormalClass

}

from './DesignSystem'

const props = defineProps({

    modelValue: {

        type: [

            String,

            Number

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

    type: {

        type: String,

        default: 'text'

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

    readonly: {

        type: Boolean,

        default: false

    },

    disabled: {

        type: Boolean,

        default: false

    },

    autofocus: {

        type: Boolean,

        default: false

    },

    autocomplete: {

        type: String,

        default: 'off'

    },

    maxlength: {

        type: [

            Number,

            String

        ],

        default: null

    },

    prefix: {

        type: String,

        default: ''

    },

    suffix: {

        type: String,

        default: ''

    },

    size: {

    type: String,

    default: 'md'

},

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

        <!-- Input -->

        <div

            class="relative"

        >

            <!-- Prefix -->

            <span

                v-if="prefix"

                class="
                    absolute
                    left-3
                    top-1/2
                    -translate-y-1/2
                    text-sm
                    text-gray-500
                "

            >

                {{ prefix }}

            </span>

            <input

                :value="modelValue"

                @input="

                    emit(

                        'update:modelValue',

                        $event.target.value

                    )

                "

                :type="type"

                :placeholder="placeholder"

                :readonly="readonly"

                :disabled="disabled"

                :maxlength="maxlength"

                :autocomplete="autocomplete"

                :autofocus="autofocus"

                class="
                    w-full
                    rounded-xl
                    border
                    bg-white
                    py-2.5
                    text-sm
                    transition
                    focus:outline-none
                    focus:ring-2
                    disabled:bg-gray-100
                    disabled:text-gray-500
                    readonly:bg-gray-50
                "

                :class="[

                    prefix

                        ? 'pl-10'

                        : 'pl-4',

                    suffix

                        ? 'pr-10'

                        : 'pr-4',

                    error

                        ? 'border-red-400 focus:border-red-500 focus:ring-red-100'

                        : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-100'

                ]"

            >

            <!-- Suffix -->

            <span

                v-if="suffix"

                class="
                    absolute
                    right-3
                    top-1/2
                    -translate-y-1/2
                    text-sm
                    text-gray-500
                "

            >

                {{ suffix }}

            </span>

        </div>

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