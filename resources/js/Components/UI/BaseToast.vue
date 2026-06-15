<script setup>

defineProps({

    message: {
        type: String,
        default: ''
    },

    show: {
        type: Boolean,
        default: false
    }

})

</script>

<template>

    <transition

        enter-active-class="transition duration-300"

        enter-from-class="opacity-0 translate-y-3"

        enter-to-class="opacity-100 translate-y-0"

        leave-active-class="transition duration-300"

        leave-from-class="opacity-100"

        leave-to-class="opacity-0"

    >

        <div

            v-if="show"

            class="fixed top-5 right-5 z-50 rounded-xl bg-green-600 px-6 py-4 text-white shadow-2xl"

        >

            <div class="flex items-center gap-3">

                <span>

                    ✓

                </span>

                <span>

                    {{ message }}

                </span>

            </div>

        </div>

    </transition>

</template>