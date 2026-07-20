<script setup>

defineProps({

    documentId: String,

    printedBy: String,

    printedAt: String,

    version: {

        type: String,

        default: '1.0.0',

    },

})

</script>

<template>

<div

    class="
        mb-8
        rounded-xl
        border
        border-gray-200
        bg-gray-50
        p-5
    "

>

    <div

        class="
            mb-3
            text-sm
            font-semibold
            uppercase
            tracking-wide
            text-gray-600
        "

    >

        Document Information

    </div>

    <div

        class="
            grid
            grid-cols-2
            gap-x-8
            gap-y-3
            text-sm
        "

    >

        <div>

            <strong>Document ID</strong><br>

            {{ documentId }}

        </div>

        <div>

            <strong>Printed By</strong><br>

            {{ printedBy }}

        </div>

        <div>

            <strong>Printed At</strong><br>

            {{ printedAt }}

        </div>

        <div>

            <strong>System Version</strong><br>

            v{{ version }}

        </div>

    </div>

</div>

</template>