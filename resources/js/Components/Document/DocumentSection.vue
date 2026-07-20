<script setup>

defineProps({

    title: String,

})

</script>

<template>

<div class="mt-8">

    <h3
        class="
            mb-4
            border-b
            border-gray-200
            pb-2
            text-sm
            font-bold
            uppercase
            tracking-wide
            text-gray-700
        "
    >

        {{ title }}

    </h3>

    <slot />

</div>

</template>