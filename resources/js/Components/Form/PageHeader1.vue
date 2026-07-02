
<script setup>

import { computed } from 'vue'

const props = defineProps({

    breadcrumb: {

        type: Array,

        default: () => []

    },

    title: {

        type: String,

        required: true

    },

    subtitle: {

        type: String,

        default: ''

    },

    icon: {

        type: String,

        default: '📄'

    },

    badge: {

        type: String,

        default: ''

    },

    sticky: {

        type: Boolean,

        default: false

    }

})

const hasBreadcrumb = computed(

    () => props.breadcrumb.length > 0

)

const wrapperClass = computed(

    () => [

        'mb-6',

        'border-b',

        'border-gray-200',

        'bg-white',

        'pb-6',

        props.sticky

            ? 'sticky top-0 z-20'

            : ''

    ]

)

</script>

<template>

<div

      :class="[

        wrapperClass,

        'rounded-2xl',

        'border',

        'border-gray-200',

        'bg-white',

        'shadow-sm',

        'px-8',

        'py-7'

    ]"

>

    <div

        class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between"

    >

        <!-- LEFT -->

        <div class="min-w-0 flex-1">

            <!-- Breadcrumb -->

            <nav

                v-if="hasBreadcrumb"

               class="
                        mb-5
                        flex
                        flex-wrap
                        items-center
                        gap-2
                        text-sm
                        font-medium
                        text-gray-500
                        "
                                    >

                <template

                    v-for="(

                        item,

                        index

                    ) in breadcrumb"

                    :key="index"

                >

                    <span>

                        {{ item }}

                    </span>

                    <span

                        v-if="index < breadcrumb.length - 1"

                        class="text-gray-300"

                    >

                        /

                    </span>

                </template>

            </nav>

            <!-- Title -->

            <div

                class="flex flex-wrap items-center gap-3"

            >

                <div

                    class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-2xl"

                >

                    {{ icon }}

                </div>

                <div>

                    <div

                        class="flex flex-wrap items-center gap-2"

                    >

                        <h1

                            class="text-4xl font-bold tracking-tight text-gray-900"

                        >

                            {{ title }}

                        </h1>

                        <span

                            v-if="badge"

                            class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700"

                        >

                            {{ badge }}

                        </span>

                    </div>

                    <p

                        v-if="subtitle"

                        class="mt-2 text-base leading-relaxed"

                    >

                        {{ subtitle }}

                    </p>

                </div>

            </div>

        </div>

        <!-- RIGHT -->

        <div

            class="flex flex-wrap items-center justify-end gap-3"

        >

            <slot

                name="actions"

            />

        </div>

    </div>

</div>

</template>