<script setup>

const props = defineProps({

    title: {

        type: String,

        required: true,

    },

})

</script>

<template>

<section
    class="
        mb-8
    "
>

    <div
        class="
            border-b
            border-gray-400
            pb-2
            mb-4
        "
    >

        <h3
            class="
                text-lg
                font-bold
                uppercase
            "
        >

            {{ title }}

        </h3>

    </div>

    <div>

        <slot />

    </div>

</section>

</template>