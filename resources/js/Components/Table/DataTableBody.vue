<script setup>

defineProps({

    loading: {

        type: Boolean,

        default: false,

    },

})

</script>

<template>

<tbody

    class="
        divide-y
        divide-gray-100
        bg-white
    "

>

    <slot />

</tbody>

</template>