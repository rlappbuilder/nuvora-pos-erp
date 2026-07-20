<script setup>

import { computed } from 'vue'

const props = defineProps({

    bordered: {

        type: Boolean,

        default: true,

    },

    hover: {

        type: Boolean,

        default: true,

    },

    striped: {

        type: Boolean,

        default: false,

    },

    stickyHeader: {

        type: Boolean,

        default: false,

    },
    maxHeight: {

    type: String,

    default: '650px',

},

})

const wrapperClass = computed(() => [

    'overflow-hidden',

    'rounded-2xl',

    'bg-white',

    props.bordered

        ? 'border border-gray-200'

        : '',

    'shadow-sm',

])

const tableClass = computed(() => [

    'min-w-full',
    
    'table-fixed',

    'border-collapse',

    'text-sm',


])

</script>

<template>

<div

    :class="wrapperClass"

>

  <div

    :class="[

        'overflow-x-auto',

        stickyHeader

            ? 'overflow-y-auto'

            : '',

    ]"

    :style="

        stickyHeader

            ? {

                maxHeight,

            }

            : {}

    "

>

        <table

            :class="tableClass"

        >

            <slot />

        </table>

    </div>

</div>

</template>