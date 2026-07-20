<script setup>
import {

    ref,
    watch,

} from 'vue'

const props = defineProps({

    flash: {

        type: Object,

        default: () => ({}),

    },

})
const visible = ref(true)

watch(

    () => props.flash.success,

    () => {

        visible.value = true

        setTimeout(() => {

            visible.value = false

        }, 3000)

    }

)


</script>

<template>

<Transition
    enter-active-class="duration-300"
    leave-active-class="duration-200"
>

<div
    v-if="flash.success && visible"
    class="
        mb-6
        flex
        items-center
        rounded-xl
        border
        border-green-200
        bg-green-50
        px-5
        py-4
        text-green-700
    "
>

    <span class="mr-3 text-xl">

        ✅

    </span>

    <span>

        {{ flash.success }}

    </span>

</div>

</Transition>

</template>