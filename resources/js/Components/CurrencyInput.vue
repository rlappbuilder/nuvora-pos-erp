<script setup>

import {

    computed

}

from 'vue'

const props = defineProps({

    modelValue: {

        type: [

            Number,

            String

        ],

        default: 0,

    },

    disabled: {

        type: Boolean,

        default: false,

    },

})

const emit = defineEmits([

    'update:modelValue'

])

const displayValue = computed({

    get() {

        return Number(

            props.modelValue ?? 0

        ).toLocaleString(

            'id-ID'

        )

    },

    set(value) {

        const numeric = value

            .toString()

            .replace(

                /\./g,

                ''

            )

            .replace(

                /,/g,

                '.'

            )

            .replace(

                /[^0-9.]/g,

                ''

            )

        emit(

            'update:modelValue',

            numeric === ''

                ? 0

                : Number(

                    numeric

                )

        )

    }

})

</script>

<template>

    <div

        class="relative"

    >

        <span

            class="
                pointer-events-none
                absolute
                left-4
                top-1/2
                -translate-y-1/2
                text-gray-500
            "

        >

            Rp

        </span>

        <input

            v-model="displayValue"

            type="text"

            :disabled="disabled"

            class="
                w-full
                rounded-xl
                border
                border-gray-300
                py-2.5
                pl-12
                pr-4
                focus:border-indigo-500
                focus:ring-indigo-500
                disabled:bg-gray-100
                disabled:text-gray-500
            "

        >

    </div>

</template>