<script setup>
import { computed } from 'vue'

const props = defineProps({
    status: {
        type: [Boolean, Number, String],
        default: false,
    },

    label: {
        type: String,
        default: '',
    },

    variant: {
        type: String,
        default: '',
    },
})

const type = computed(() => {

    if (props.variant) {
        return props.variant.toLowerCase()
    }

    const active = [true, 1, '1', 'true'].includes(props.status)

    return active ? 'success' : 'danger'
})

const variants = Object.freeze({
    success: {
        badge: 'bg-emerald-100 text-emerald-700',
        dot: 'bg-emerald-500',
        text: 'Active',
    },

    danger: {
        badge: 'bg-red-100 text-red-700',
        dot: 'bg-red-500',
        text: 'Inactive',
    },

    warning: {
        badge: 'bg-amber-100 text-amber-700',
        dot: 'bg-amber-500',
        text: 'Draft',
    },

    info: {
        badge: 'bg-sky-100 text-sky-700',
        dot: 'bg-sky-500',
        text: 'Processing',
    },

    primary: {
        badge: 'bg-indigo-100 text-indigo-700',
        dot: 'bg-indigo-500',
        text: 'Approved',
    },

    purple: {
        badge: 'bg-purple-100 text-purple-700',
        dot: 'bg-purple-500',
        text: 'Pending',
    },

    gray: {
        badge: 'bg-slate-100 text-slate-700',
        dot: 'bg-slate-500',
        text: 'Unknown',
    },
    secondary: {
    badge: 'bg-cyan-100 text-cyan-700',
    dot: 'bg-cyan-500',
    text: 'Transfer',
},

orange: {
    badge: 'bg-orange-100 text-orange-700',
    dot: 'bg-orange-500',
    text: 'Adjustment',
},
})

const current = computed(() => variants[type.value] ?? variants.gray)

const text = computed(() => props.label || current.value.text)
</script>
<template>

<span
    :class="[
        current.badge,
        'inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold'
    ]"
>
    <span
        :class="[current.dot,'h-2 w-2 rounded-full']"
    />

    {{ text }}
</span>

</template>