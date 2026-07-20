<script setup>

defineProps({

    bordered: {

        type: Boolean,

        default: true,

    },

    sticky: {

        type: Boolean,

        default: false,

        

    },

})


</script>

<template>

<div
    :class="[
        'flex',
        'flex-wrap',
        'items-center',
        'justify-between',
        'gap-3',
        'px-5',
        'py-4',
        'lg:px-6',

        bordered
            ? 'border-b border-gray-200'
            : ''
    ]"
>
    <slot />


</div>

</template>