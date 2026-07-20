<script setup>

import { computed } from 'vue'

const props = defineProps({

    active: {

        type: Boolean,

        default: false,

    },

})

const badgeClass = computed(() => [

    props.active

        ? 'bg-green-50 text-green-700'

        : 'bg-red-50 text-red-700',

])

const dotClass = computed(() => [

    props.active

        ? 'bg-green-500'

        : 'bg-red-500',

])

</script>

<template>

<span

    :class="[

        badgeClass,

        'inline-flex',

        'items-center',

        'gap-2',

        'rounded-full',

        'px-3',

        'py-1',

        'text-xs',

        'font-medium',

    ]"

>

    <span

        :class="[

            dotClass,

            'h-2',

            'w-2',

            'rounded-full',

        ]"

    />

    {{ active ? 'Active' : 'Inactive' }}

</span>

</template>