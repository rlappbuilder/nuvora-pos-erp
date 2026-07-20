<script setup>

import { ref, onMounted, onBeforeUnmount } from 'vue'
import BaseButton from '@/Components/Button/BaseButton.vue'

const open = ref(false)
const dropdown = ref(null)

const emit = defineEmits([
    'delete',
    'activate',
    'deactivate',
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
    class="relative"
>

    <BaseButton
        @click.stop="toggle"
    >

        Bulk Action

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
                hover:bg-red-50
            "
            @click="
                emit('delete');
                close();
            "
        >
            🗑 Delete
        </button>

        <button
            class="
                w-full
                px-4
                py-3
                text-left
                hover:bg-green-50
            "
            @click="
                emit('activate');
                close();
            "
        >
            ✅ Activate
        </button>

        <button
            class="
                w-full
                px-4
                py-3
                text-left
                hover:bg-yellow-50
            "
            @click="
                emit('deactivate');
                close();
            "
        >
            🚫 Deactivate
        </button>

    </div>

</div>

</template>