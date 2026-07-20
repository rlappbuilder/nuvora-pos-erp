<script setup>

defineProps({

    show: {

        type: Boolean,

        default: false,

    },

    text: {

        type: String,

        default: 'Loading...',

    },

})

</script>
<template>

<Transition
    name="fade"
>

<div

    v-if="show"

    class="
        absolute
        inset-0
        z-50
        flex
        items-center
        justify-center
        bg-white/70
        backdrop-blur-sm
    "

>

    <div

        class="
            flex
            flex-col
            items-center
            gap-4
        "

    >

        <div

            class="
                h-10
                w-10
                animate-spin
                rounded-full
                border-4
                border-indigo-200
                border-t-indigo-600
            "

        />

        <p

            class="
                text-sm
                font-medium
                text-gray-600
            "

        >

            {{ text }}

        </p>

    </div>

</div>

</Transition>

</template>
<style scoped>

.fade-enter-active,
.fade-leave-active {

    transition: opacity .2s;

}

.fade-enter-from,
.fade-leave-to {

    opacity: 0;

}

</style>