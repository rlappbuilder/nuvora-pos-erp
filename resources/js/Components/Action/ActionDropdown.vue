<script setup>

import {
    ref,
    onMounted,
    onBeforeUnmount,
} from 'vue'

import {
    EllipsisVerticalIcon,
    PencilSquareIcon,
    TrashIcon,
    EyeIcon,
    DocumentDuplicateIcon,
    ArrowDownTrayIcon,
} from '@heroicons/vue/24/outline'

const emit = defineEmits([
    'view',
    'edit',
    'duplicate',
    'export',
    'delete',
])

const open = ref(false)

const dropdown = ref(null)

function toggle()
{
    open.value = !open.value
}

function close()
{
    open.value = false
}

function handleClickOutside(event)
{
    if (

        dropdown.value &&

        !dropdown.value.contains(event.target)

    ) {

        close()

    }
}

onMounted(() => {

    document.addEventListener(

        'click',

        handleClickOutside

    )

})

onBeforeUnmount(() => {

    document.removeEventListener(

        'click',

        handleClickOutside

    )

})

</script>

<template>

<div
    ref="dropdown"
    class="relative inline-block text-left"
>

    <button

        @click.stop="toggle"

        class="
            rounded-lg
            p-2
            transition
            hover:bg-gray-100
        "

    >

        <EllipsisVerticalIcon
            class="h-5 w-5 text-gray-600"
        />

    </button>

    <Transition
        enter-active-class="duration-150"
        leave-active-class="duration-100"
    >

        <div

            v-if="open"

            class="
                absolute
                right-0
                z-50
                mt-2
                w-52
                overflow-hidden
                rounded-xl
                border
                border-gray-200
                bg-white
                shadow-lg
            "

        >

            <button
                class="
                    flex
                    w-full
                    items-center
                    gap-3
                    px-4
                    py-2.5
                    text-sm
                    text-gray-700
                    transition-colors
                    duration-150
                    hover:bg-indigo-50
                    hover:text-indigo-600
                "
                @click="$emit('view'); close()"
            >
                <EyeIcon class="h-5 w-5" />
                View
            </button>

            <button
                class="
                    flex
                    w-full
                    items-center
                    gap-3
                    px-4
                    py-2.5
                    text-sm
                    text-gray-700
                    transition-colors
                    duration-150
                    hover:bg-indigo-50
                    hover:text-indigo-600
                "
                @click="$emit('edit'); close()"
            >
                <PencilSquareIcon class="h-5 w-5" />
                Edit
            </button>

            <button
                class="
                    flex
                    w-full
                    items-center
                    gap-3
                    px-4
                    py-2.5
                    text-sm
                    text-gray-700
                    transition-colors
                    duration-150
                    hover:bg-indigo-50
                    hover:text-indigo-600
                "
                @click="$emit('duplicate'); close()"
            >
                <DocumentDuplicateIcon class="h-5 w-5" />
                Duplicate
            </button>

            <button
                class="
                    flex
                    w-full
                    items-center
                    gap-3
                    px-4
                    py-2.5
                    text-sm
                    text-gray-700
                    transition-colors
                    duration-150
                    hover:bg-indigo-50
                    hover:text-indigo-600
                "
                @click="$emit('export'); close()"
            >
                <ArrowDownTrayIcon class="h-5 w-5" />
                Export
            </button>

            <div
                class="
                    mx-2
                    my-1
                    border-t
                    border-gray-100
                "
            ></div>

            <button
                class="
                    flex
                    w-full
                    items-center
                    gap-3
                    px-4
                    py-2.5
                    text-sm
                    text-red-600
                    transition-colors
                    duration-150
                    hover:bg-red-50
                "
                @click="$emit('delete'); close()"
            >
                <TrashIcon class="h-5 w-5" />
                Delete
            </button>

        </div>

    </Transition>

</div>

</template>