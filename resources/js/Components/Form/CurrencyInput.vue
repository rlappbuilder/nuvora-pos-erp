<script setup>

import {

    ref,

    watch,

    computed

}

from 'vue'

import ValidationError

from './ValidationError.vue'

import {

    formatCurrency,

    parseCurrency,

    getCurrencySymbol

}

from '@/Utils'

import {

    inputClass,

    labelClass,

    hintClass

}

from './DesignSystem'

const props = defineProps({

    modelValue: {

        type: [

            Number,

            String

        ],

        default: 0

    },

    label: String,

    placeholder: {

        type: String,

        default: '0'

    },

    hint: String,

    error: String,

    required: Boolean,

    disabled: Boolean,

    readonly: Boolean,

    autofocus: Boolean,

    currency: {

        type: String,

        default: 'IDR'

    },

    decimals: {

        type: Number,

        default: 0

    }

})

const emit = defineEmits([

    'update:modelValue',

    'blur',

    'focus'

])

const displayValue = ref('')

const inputRef = ref(null)

const prefix = computed(

    () =>

        getCurrencySymbol(

            props.currency

        )

)
watch(

    () => props.modelValue,

    (

        value

    ) => {

        displayValue.value =

            formatCurrency(

                value,

                'id-ID',

                props.decimals

            )

    },

    {

        immediate: true

    }

)

function onInput(

    event

)
{

    displayValue.value =

        event.target.value

}


function onFocus(

    event

)
{

    displayValue.value =

        props.modelValue

            ? String(

                props.modelValue

            )

            : ''

    event.target.select()

    emit(

        'focus',

        event

    )

}


function onBlur(

    event

)
{

    const value =

        parseCurrency(

            displayValue.value

        )

    emit(

        'update:modelValue',

        value

    )

    displayValue.value =

        formatCurrency(

            value,

            'id-ID',

            props.decimals

        )

    emit(

        'blur',

        event

    )

}
{

    displayValue.value =

        formatCurrency(

            props.modelValue,

            'id-ID',

            props.decimals

        )

    emit(

        'blur',

        event

    )

}

defineExpose({

    focus()

    {

        inputRef.value?.focus()

    }

})
</script>
<template>

<div>

    <label

        v-if="label"

        :class="labelClass"

    >

        {{ label }}

        <span

            v-if="required"

            class="text-red-500"

        >

            *

        </span>

    </label>

  <div

    class="relative"

>

    <span

        class="
            absolute
            left-4
            top-1/2
            -translate-y-1/2
            text-gray-500
            pointer-events-none
            select-none
        "

    >

        {{ prefix }}

    </span>

    <input

        ref="inputRef"

        v-model="displayValue"

        type="text"

        inputmode="decimal"

        :placeholder="placeholder"

        :readonly="readonly"

        :disabled="disabled"

        :autofocus="autofocus"

        :class="[

            inputClass,

            error

                ? errorClass

                : '',

            'pl-12',

            'text-right'

        ]"

        @input="onInput"

        @focus="onFocus"

        @blur="onBlur"

    >

</div>

    <p

        v-if="hint && !error"

        :class="hintClass"

    >

        {{ hint }}

    </p>

    <ValidationError

        :message="error"

    />

</div>

</template>