<script setup>

import BaseButton from '@/Components/Button/BaseButton.vue'

const props = defineProps({

    icon: {

        type: String,

        default: '📁',

    },

    title: {

        type: String,

        default: 'No Data Found',

    },

    description: {

        type: String,

        default: 'There is no data available.',

    },

    buttonText: {

        type: String,

        default: '',

    },

})

const emit = defineEmits([

    'action',

])

</script>

<template>

<div

    class="
        flex
        min-h-[420px]
        flex-col
        items-center
        justify-center
        px-6
        text-center
    "

>

    <div

        class="
            mb-6
            text-6xl
        "

    >

        {{ icon }}

    </div>

    <h3

        class="
            text-2xl
            font-bold
            text-gray-800
        "

    >

        {{ title }}

    </h3>

    <p

        class="
            mt-3
            max-w-md
            text-gray-500
        "

    >

        {{ description }}

    </p>

    <BaseButton

        v-if="buttonText"

        class="mt-8"

        @click="emit('action')"

    >

        {{ buttonText }}

    </BaseButton>

</div>

</template>