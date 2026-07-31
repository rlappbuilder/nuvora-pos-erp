<script setup>
import {
    FolderIcon,
    DocumentTextIcon,
    ChevronRightIcon,
    ChevronDownIcon,
    PlusIcon,
    PencilSquareIcon,
    CubeIcon,
} from '@heroicons/vue/24/solid'

import { computed } from 'vue'
const props = defineProps({

    title: {

        type: String,

        required: true,

    },

    value: {

        type: [

            String,

            Number,

        ],

        required: true,

    },

    icon: {

        type: String,

        default: '',

    },

    color: {

        type: String,

        default: 'blue',

    },
    subtitle: {

    type: String,

    default: '',

    },
    trend: {

    type: String,

    default: '',

    },

    trendType: {

        type: String,

        default: 'up',

    },

})
const colorClass = computed(() => {

    switch (props.color) {

        case 'emerald':

            return 'bg-emerald-50 text-emerald-600'

        case 'red':

            return 'bg-red-50 text-red-600'

        case 'amber':

            return 'bg-amber-50 text-amber-600'

        case 'cyan':

            return 'bg-cyan-50 text-cyan-600'

        default:

            return 'bg-indigo-50 text-indigo-600'

    }

})
</script>

<template>

<div

    class="
        rounded-2xl
        border
        border-gray-200
        bg-white
        p-6
        shadow-sm
        transition-all
        duration-200
        hover:shadow-md
    "

>

    <div

        class="
            flex
            items-start
            justify-between
        "

    >

        <div>

            <p

                class="
                    text-sm
                    font-medium
                    text-gray-500
                "

            >

                {{ title }}

            </p>
            <h3
                class="
                    mt-3
                    text-3xl
                    font-bold
                    tracking-tight
                    text-gray-900
                "
            >
                {{ value }}
            </h3>

            <p
                v-if="subtitle"
                class="
                    mt-1
                    text-sm
                    text-gray-500
                "
            >
                {{ subtitle }}
            </p>

            <p
                v-if="trend"
                :class="[
                    'mt-2 text-sm font-medium',
                    trendType === 'up'
                        ? 'text-emerald-600'
                        : 'text-red-600'
                ]"
            >
                {{ trend }}
            </p>

        </div>
            <div
                :class="[
                    'flex h-14 w-14 items-center justify-center rounded-xl text-2xl',
                    colorClass,
                ]"
            >

                <slot name="icon">

                    {{ icon }}

                </slot>

            </div>

    </div>

</div>

</template>