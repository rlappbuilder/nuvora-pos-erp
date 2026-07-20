<script setup>

const props = defineProps({

    columns: {

        type: Array,

        default: () => [

            'Prepared By',

            'Checked By',

            'Approved By',

        ],

    },

})

</script>

<template>

<div
    class="
        mt-16
        grid
        gap-8
    "
    :style="{

        gridTemplateColumns: `repeat(${columns.length}, minmax(0, 1fr))`

    }"
>

    <div

        v-for="column in columns"

        :key="column"

        class="text-center"

    >

        <div
            class="
                text-sm
                font-semibold
            "
        >

            {{ column }}

        </div>

        <div
            class="
                h-20
            "
        />

        <div
            class="
                border-t
                border-gray-700
                pt-2
                text-sm
            "
        >

            (...........................)

        </div>

    </div>

</div>

</template>