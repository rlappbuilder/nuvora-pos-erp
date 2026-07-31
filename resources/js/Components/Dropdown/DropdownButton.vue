<script setup>
import {
    ref,
    onMounted,
    onBeforeUnmount,
} from 'vue'

import {
    ChevronDownIcon,EllipsisVerticalIcon,
} from '@heroicons/vue/24/outline'


const props = defineProps({

    label: {
        type: String,
        required: true,
    },

    disabled: {
        type: Boolean,
        default: false,
    },

    width: {
        type: String,
        default: 'w-56',
    },
    iconOnly: {
    type: Boolean,
    default: false,
    },

    showChevron: {
        type: Boolean,
        default: true,
    },

    placement: {
        type: String,
        default: 'right',
    },

})

const open = ref(false)

const dropdown = ref(null)

function toggle()
{
    if (props.disabled) {
        return
    }

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
    type="button"
    :disabled="disabled"
    @click.stop="toggle"
    :class="[
        iconOnly
            ? 'rounded-lg p-2 transition hover:bg-gray-100 disabled:opacity-60 disabled:cursor-not-allowed'
            : 'inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-gray-50 disabled:opacity-60 disabled:cursor-not-allowed'
    ]"
>

        <template v-if="iconOnly">

            <slot name="trigger">

                <EllipsisVerticalIcon
                    class="h-5 w-5 text-gray-600"
                />

            </slot>

        </template>

        <template v-else>

            <slot name="icon" />

            <span>{{ label }}</span>

            <ChevronDownIcon
                v-if="showChevron"
                class="h-4 w-4"
            />

        </template>

    </button>

    <Transition
        enter-active-class="duration-150"
        leave-active-class="duration-100"
    >

        <div
            v-if="open"
            :class="[
                'absolute z-50 mt-2 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg',
                placement === 'right'
                    ? 'right-0'
                    : 'left-0',
                width,
            ]"
        >

            <slot
                :close="close"
            />

        </div>

    </Transition>

</div>

</template>