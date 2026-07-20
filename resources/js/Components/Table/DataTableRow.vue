<script setup>

import { computed } from 'vue'

const props = defineProps({

    hover: {

        type: Boolean,

        default: true,

    },

    striped: {

        type: Boolean,

        default: false,

    },

})

const rowClass = computed(() => [

    'transition-all',

    'duration-150',

    props.hover

        ? 'hover:bg-gray-50'

        : '',

])

</script>

<template>

<tr
    class="
        border-b
        border-gray-100
        hover:bg-slate-50
        transition-colors
        duration-150
    "
>

    <slot />

</tr>

</template>