<script setup>

import { computed } from 'vue'

const props = defineProps({

    printedBy: {

        type: String,

        default: '',

    },

})

const printedAt = computed(() => {

    return new Date().toLocaleString('id-ID', {

        dateStyle: 'full',

        timeStyle: 'short',

    })

})

</script>

<template>

<div
    class="
        mt-16
        border-t
        pt-4
        flex
        justify-between
        items-start
        text-sm
        text-gray-500
    "
>

    <div>

        <div>

            <strong>Printed By</strong>

            :

            {{ printedBy || '-' }}

        </div>

        <div>

            <strong>Printed At</strong>

            :

            {{ printedAt }}

        </div>

    </div>

    <div
        class="
            text-right
        "
    >

        <div>

            Powered by

        </div>

        <div
            class="
                font-semibold
            "
        >

            Nuvora ERP

        </div>

    </div>

</div>

</template>