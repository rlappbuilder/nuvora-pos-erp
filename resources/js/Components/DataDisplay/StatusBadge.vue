<script setup>

import {

    computed

}

from 'vue'

const props = defineProps({

    status: {

        type: [

            String,

            Boolean,

            Number

        ],

        default: ''

    }

})

const badge = computed(() => {

    const status =

        String(

            props.status

        )

        .toLowerCase()

    switch (

        status

    ) {

        case 'true':

        case '1':

        case 'active':

            return {

                text: 'Active',

                class:

                    'bg-green-100 text-green-700'

            }

        case 'false':

        case '0':

        case 'inactive':

            return {

                text: 'Inactive',

                class:

                    'bg-red-100 text-red-700'

            }

        case 'draft':

            return {

                text: 'Draft',

                class:

                    'bg-gray-100 text-gray-700'

            }

        case 'pending':

            return {

                text: 'Pending',

                class:

                    'bg-yellow-100 text-yellow-700'

            }

        case 'approved':

            return {

                text: 'Approved',

                class:

                    'bg-blue-100 text-blue-700'

            }

        case 'posted':

            return {

                text: 'Posted',

                class:

                    'bg-indigo-100 text-indigo-700'

            }

        case 'paid':

            return {

                text: 'Paid',

                class:

                    'bg-emerald-100 text-emerald-700'

            }

        case 'partial':

            return {

                text: 'Partial',

                class:

                    'bg-orange-100 text-orange-700'

            }

        case 'cancelled':

            return {

                text: 'Cancelled',

                class:

                    'bg-red-100 text-red-700'

            }

        case 'completed':

            return {

                text: 'Completed',

                class:

                    'bg-green-100 text-green-700'

            }

        default:

            return {

                text:

                    props.status,

                class:

                    'bg-gray-100 text-gray-700'

            }

    }

})

</script>
<template>

<span

    :class="[

        'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold',

        badge.class

    ]"

>

    <span

        class="mr-1 h-2 w-2 rounded-full bg-current opacity-70"

    />

    {{ badge.text }}

</span>

</template>