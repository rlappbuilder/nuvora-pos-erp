<script setup>

import { ref, onMounted, onBeforeUnmount } from 'vue'
import BaseButton from '@/Components/Button/BaseButton.vue'

const open = ref(false)
const dropdown = ref(null)

const emit = defineEmits([
    'excel',
    'pdf',
    'csv',
])

function toggle()
{
    open.value = !open.value
}

function close()
{
    open.value = false
}

function clickOutside(e)
{
    if (
        dropdown.value &&
        !dropdown.value.contains(e.target)
    ) {
        close()
    }
}

onMounted(() => {

    document.addEventListener(
        'click',
        clickOutside
    )

})

onBeforeUnmount(() => {

    document.removeEventListener(
        'click',
        clickOutside
    )

})

</script>

<template>

<div
 
    ref="dropdown"
    class="relative w-full lg:w-auto"
>

    <BaseButton
        variant="secondary"
        class="w-full"
        @click.stop="toggle"
    >
        Export
    </BaseButton>

    <div
        v-if="open"
        class="
            absolute
            right-0
            z-50
            mt-2
            w-56
            overflow-hidden
            rounded-xl
            border
            border-gray-200
            bg-white
            shadow-xl
        "
    >

        <button
            class="
                w-full
                px-4
                py-3
                text-left
                hover:bg-gray-50
            "
            @click="
                emit('excel');
                close();
            "
        >
            📗 Export Excel
        </button>

        <button
            class="
                w-full
                px-4
                py-3
                text-left
                hover:bg-gray-50
            "
            @click="
                emit('pdf');
                close();
            "
        >
            📕 Export PDF
        </button>

        <button
            class="
                w-full
                px-4
                py-3
                text-left
                hover:bg-gray-50
            "
            @click="
                emit('csv');
                close();
            "
        >
            📄 Export CSV
        </button>

    </div>

</div>

</template>