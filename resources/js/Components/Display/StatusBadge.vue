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

/*
|--------------------------------------------------------------------------
| Variants
|--------------------------------------------------------------------------
*/

const variants = Object.freeze({

    /*
    |--------------------------------------------------------------------------
    | Fiscal Year
    |--------------------------------------------------------------------------
    */

    open: {
        badge: 'bg-emerald-100 text-emerald-700',
        dot: 'bg-emerald-500',
        text: 'Open',
    },

    closed: {
        badge: 'bg-slate-100 text-slate-700',
        dot: 'bg-slate-500',
        text: 'Closed',
    },

    /*
    |--------------------------------------------------------------------------
    | Master Data
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Transaction Status
    |--------------------------------------------------------------------------
    */

    draft: {
        badge: 'bg-amber-100 text-amber-700',
        dot: 'bg-amber-500',
        text: 'Draft',
    },

    submitted: {
    badge: 'bg-blue-100 text-blue-700',
    dot: 'bg-blue-500',
    text: 'Submitted',
    },

    rejected: {
        badge: 'bg-red-100 text-red-700',
        dot: 'bg-red-500',
        text: 'Rejected',
    },
    cancelled: {
        badge: 'bg-red-100 text-red-700',
        dot: 'bg-red-500',
        text: 'Cancelled',
    },
    posted: {
        badge: 'bg-emerald-100 text-emerald-700',
        dot: 'bg-emerald-500',
        text: 'Posted',
    },

    processing: {
        badge: 'bg-sky-100 text-sky-700',
        dot: 'bg-sky-500',
        text: 'Processing',
    },

   approved: {
    badge: 'bg-emerald-100 text-emerald-700',
    dot: 'bg-emerald-500',
    text: 'Approved',
},

    pending: {
        badge: 'bg-purple-100 text-purple-700',
        dot: 'bg-purple-500',
        text: 'Pending',
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

    gray: {
        badge: 'bg-slate-100 text-slate-700',
        dot: 'bg-slate-500',
        text: 'Unknown',
    },

    sent: {
    badge: 'bg-sky-100 text-sky-700',
    dot: 'bg-sky-500',
    text: 'Sent',
    },

    confirmed: {
        badge: 'bg-indigo-100 text-indigo-700',
        dot: 'bg-indigo-500',
        text: 'Confirmed',
    },

    partially_received: {
        badge: 'bg-orange-100 text-orange-700',
        dot: 'bg-orange-500',
        text: 'Partially Received',
    },

    fully_received: {
        badge: 'bg-emerald-100 text-emerald-700',
        dot: 'bg-emerald-500',
        text: 'Fully Received',
    },
  
})

/*
|--------------------------------------------------------------------------
| Resolve Type
|--------------------------------------------------------------------------
*/

const type = computed(() => {

    /*
    |--------------------------------------------------------------------------
    | Explicit Variant
    |--------------------------------------------------------------------------
    */

    if (props.variant) {
        return props.variant.toLowerCase()
    }

    /*
    |--------------------------------------------------------------------------
    | String Status
    |--------------------------------------------------------------------------
    */

    if (typeof props.status === 'string') {

        const status =
            props.status
                .trim()
                .toLowerCase()

        switch (status) {

            case 'draft':
                return 'draft'

            case 'submitted':
            return 'submitted'

            case 'cancelled':
            return 'cancelled'

            case 'rejected':
                return 'rejected'

            case 'posted':
                return 'posted'

            case 'processing':
                return 'processing'

            case 'approved':
                return 'approved'

            case 'pending':
                return 'pending'

                case 'sent':
                return 'sent'

            case 'confirmed':
                return 'confirmed'

            case 'partially received':
                return 'partially_received'

            case 'fully received':
                return 'fully_received'

           case 'open':
                return 'open'

            case 'closed':
                return 'closed'
            default:
                break
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Boolean / Number Status
    |--------------------------------------------------------------------------
    */

    const active = [
        true,
        1,
        '1',
        'true',
    ].includes(props.status)

    return active
        ? 'success'
        : 'danger'
})

/*
|--------------------------------------------------------------------------
| Current Variant
|--------------------------------------------------------------------------
*/

const current = computed(() => {

    return variants[type.value]
        ?? variants.gray

})

/*
|--------------------------------------------------------------------------
| Label
|--------------------------------------------------------------------------
*/

const text = computed(() => {

    return props.label
        || current.value.text

})
</script>

<template>

    <span
        :class="[
            current.badge,
            'inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold'
        ]"
    >

        <span
            :class="[
                current.dot,
                'h-2 w-2 rounded-full'
            ]"
        />

        {{ text }}

    </span>

</template>