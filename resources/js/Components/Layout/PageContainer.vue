<script setup>

defineProps({

    fluid: {

        type: Boolean,

        default: false,

    },

})

</script>

<template>

<div

    :class="[

        fluid

            ? 'w-full'

            : 'mx-auto max-w-7xl',

        'space-y-6',

        'px-6',

        'py-6',

    ]"

>

    <slot />

</div>

</template>