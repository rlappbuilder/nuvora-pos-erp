<script setup>

const props = defineProps({

    company: {

        type: Object,

        required: true,

    },

    title: {

        type: String,

        required: true,

    },

    subtitle: {

        type: String,

        default: '',

    },

})

</script>

<template>

<div
    class="
        border-b-2
        border-gray-800
        pb-6
        mb-8
    "
>

    <div
        class="
            flex
            justify-between
            items-center
        "
    >

        <div
            class="
                flex
                items-center
                gap-4
            "
        >

            <img

                v-if="company.logo"

                :src="company.logo"

                class="
                    w-20
                    h-20
                    object-contain
                "

            >

            <div>

                <h1
                    class="
                        text-2xl
                        font-bold
                    "
                >

                    {{ company.company_name }}

                </h1>

                <div>{{ company.address }}</div>

                <div>

                    {{ company.phone }}

                    <span
                        v-if="company.email"
                    >

                        |

                        {{ company.email }}

                    </span>

                </div>

                <div
                    v-if="company.website"
                >

                    {{ company.website }}

                </div>

            </div>

        </div>

    </div>

    <div
        class="
            mt-6
            text-center
        "
    >

        <h2
            class="
                text-3xl
                font-bold
                uppercase
            "
        >

            {{ title }}

        </h2>

        <p
            v-if="subtitle"
            class="
                mt-2
                text-gray-600
            "
        >

            {{ subtitle }}

        </p>

    </div>

</div>

</template>