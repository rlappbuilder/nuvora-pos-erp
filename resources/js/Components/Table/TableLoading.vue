<script setup>

defineProps({

    rows: {

        type: Number,

        default: 5,

    },

})

</script>

<template>

<tbody>

<tr

    v-for="i in rows"

    :key="i"

    class="
        animate-pulse
    "

>

    <td

        colspan="100"

        class="
            px-6
            py-5
        "

    >

        <div

            class="
                h-4
                w-full
                rounded
                bg-gray-200
            "

        />

    </td>

</tr>

</tbody>

</template>