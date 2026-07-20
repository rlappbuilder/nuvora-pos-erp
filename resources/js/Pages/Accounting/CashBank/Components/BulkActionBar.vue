<script setup>

import {
    EyeIcon,
    PencilSquareIcon,
    TrashIcon,
} from '@heroicons/vue/24/outline'

const emit = defineEmits([
    'show',
    'edit',
    'delete',
])

</script>

<template>

<div
    class="
        flex
        items-center
        justify-center
        gap-3
    "
>

    <!-- View -->

    <button
        type="button"
        class="
            text-gray-400
            transition
            hover:text-indigo-600
        "
        @click="emit('show')"
    >

        <EyeIcon class="h-5 w-5"/>

    </button>

    <!-- Edit -->

    <button
        type="button"
        class="
            text-gray-400
            transition
            hover:text-amber-500
        "
        @click="emit('edit')"
    >

        <PencilSquareIcon class="h-5 w-5"/>

    </button>

    <!-- Delete -->

    <button
        type="button"
        class="
            text-gray-400
            transition
            hover:text-red-600
        "
        @click="emit('delete')"
    >

        <TrashIcon class="h-5 w-5"/>

    </button>

</div>

</template>