<script setup>

defineProps({

    bordered: {

        type: Boolean,

        default: true,

    },

    sticky: {

        type: Boolean,

        default: false,

    },

    compact: {

        type: Boolean,

        default: false,

    },

})


</script>

<template>

<div
            :class="[
            'flex',
            'flex-wrap',
            'flex-col',
            'lg:flex-row',
            'items-center',
            'justify-between',
            'gap-3',
            compact ? 'px-4 py-3' : 'px-5 py-4',
            'lg:px-6',

            bordered
                ? 'border-b border-gray-200'
                : '',

            sticky
                ? 'sticky top-0 z-20 bg-white'
                : '',
        ]"
        >
            <div
            class="
                flex
                flex-1
                items-center
                gap-3
                min-w-0
            "
        >
            <slot name="left">
                <slot />
            </slot>
        </div>

        <div
            class="
                flex
                items-center
                gap-2
                ml-auto
            "
        >
            <slot name="right" />
        </div>


</div>

</template>