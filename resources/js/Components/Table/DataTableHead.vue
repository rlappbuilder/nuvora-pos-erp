<script setup>

import { computed } from 'vue'

const props = defineProps({

    sticky: {

        type: Boolean,

        default: false,

    },
    

})

const headClass = computed(() => [

    'bg-white',

    props.sticky

        ? 'sticky top-0 z-20 shadow-sm'

        : '',

])

</script>

<template>

<thead

    :class="headClass"

>

    <tr

        class="
            divide-x
            divide-gray-200
        "

    >

        <slot />

    </tr>

</thead>

</template>