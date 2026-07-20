<script setup>


import DocumentWatermark from './DocumentWatermark.vue'


</script>

<template>

<div
    class="
        relative
        min-h-screen
        bg-gray-100
        py-10
        print:bg-white
    "
>

    <DocumentWatermark />

    <div

        class="
            relative
            z-10
            mx-auto
            w-[210mm]
            min-h-[297mm]
            bg-white
            p-12
            shadow-xl
            print:p-8
            print:shadow-none
        "

    >

        <slot />

    </div>

</div>

</template>