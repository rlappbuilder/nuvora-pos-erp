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
        gap-2
    "
>

    <!-- View -->
    <button
        type="button"
        class="
            rounded-lg
            p-2
            text-slate-400
            transition-all
            duration-200
            hover:bg-blue-50
            hover:text-blue-600
        "
        title="View"
        @click="emit('show')"
    >
        <EyeIcon class="h-5 w-5"/>
    </button>

    <!-- Edit -->
    <button
        type="button"
        class="
            rounded-lg
            p-2
            text-slate-400
            transition-all
            duration-200
            hover:bg-amber-50
            hover:text-amber-600
        "
        title="Edit"
        @click="emit('edit')"
    >
        <PencilSquareIcon class="h-5 w-5"/>
    </button>

    <!-- Delete -->
    <button
        type="button"
        class="
            rounded-lg
            p-2
            text-slate-400
            transition-all
            duration-200
            hover:bg-red-50
            hover:text-red-600
        "
        title="Delete"
        @click="emit('delete')"
    >
        <TrashIcon class="h-5 w-5"/>
    </button>

</div>

</template>