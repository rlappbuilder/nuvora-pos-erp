<script setup>

const props = defineProps({

    headers: {

        type: Array,

        default: () => [],

    },

})

</script>

<template>

<table
    class="
        w-full
        border-collapse
        text-sm
    "
>

    <thead>

        <tr>

            <th

                v-for="header in headers"

                :key="header.key"

                class="
                    border
                    border-gray-700
                    px-3
                    py-2
                    bg-gray-100
                    font-bold
                    text-left
                "

                :class="header.class"

            >

                {{ header.label }}

            </th>

        </tr>

    </thead>

    <tbody>

        <slot />

    </tbody>

</table>

</template>
<style scoped>

td{

    border:1px solid #4b5563;

    padding:8px;

}

th{

    border:1px solid #4b5563;

}

</style>