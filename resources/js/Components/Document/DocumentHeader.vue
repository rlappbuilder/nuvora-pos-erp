<script setup>

import DocumentLogo from './DocumentLogo.vue'

defineProps({

    title: String,

    subtitle: {

        type: String,

        default: 'PT Nuvora Technologies Indonesia',

    },

})

</script>

<template>

<div

    class="
        flex
        items-start
        justify-between
        border-b-2
        border-gray-300
        pb-6
    "

>

    <!-- Logo + Company -->

    <div

        class="
            flex
            items-center
            gap-4
        "

    >

        <DocumentLogo />

        <div>

            <h1

                class="
                    text-3xl
                    font-bold
                    text-indigo-700
                "

            >

                NUVORA ERP

            </h1>

            <p

                class="
                    mt-1
                    text-sm
                    text-gray-500
                "

            >

                {{ subtitle }}

            </p>

        </div>

    </div>

    <!-- Document Title -->

    <div
        class="text-right"
    >

        <h2

            class="
                text-xl
                font-semibold
            "

        >

            {{ title }}

        </h2>

    </div>

</div>

</template>