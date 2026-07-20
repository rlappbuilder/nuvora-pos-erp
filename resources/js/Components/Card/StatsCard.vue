<script setup>

const props = defineProps({

    title: {

        type: String,

        required: true,

    },

    value: {

        type: [

            String,

            Number,

        ],

        required: true,

    },

    icon: {

        type: String,

        default: '',

    },

    color: {

        type: String,

        default: 'blue',

    },

})

</script>

<template>

<div

    class="
        rounded-2xl
        border
        border-gray-200
        bg-white
        p-6
        shadow-sm
        transition-all
        duration-200
        hover:shadow-md
    "

>

    <div

        class="
            flex
            items-start
            justify-between
        "

    >

        <div>

            <p

                class="
                    text-sm
                    font-medium
                    text-gray-500
                "

            >

                {{ title }}

            </p>

            <h3

                class="
                    mt-3
                    text-3xl
                    font-bold
                    tracking-tight
                    text-gray-900
                "

            >

                {{ value }}

            </h3>

        </div>

        <div

            class="
                flex
                h-14
                w-14
                items-center
                justify-center
                rounded-xl
                bg-indigo-50
                text-2xl
            "

        >

            {{ icon }}

        </div>

    </div>

</div>

</template>