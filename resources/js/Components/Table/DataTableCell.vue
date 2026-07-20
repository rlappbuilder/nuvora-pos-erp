<script setup>

import { computed } from 'vue'

const props = defineProps({

    align: {

        type: String,

        default: 'left',

    },

    width: {

        type: String,

        default: '',

    },

    

})

const cellClass = computed(() => [

    'px-6',

    'py-4',

    'text-sm',

    'text-gray-700',

    'whitespace-nowrap',

    props.align === 'center'

        ? 'text-center'

        : '',

    props.align === 'right'

        ? 'text-right'

        : '',

])

</script>

<template>

<td

    :class="cellClass"

     :style="width ? {
        width,
        minWidth: width,
        maxWidth: width,
    } : {}"

>

    <slot />

</td>

</template>