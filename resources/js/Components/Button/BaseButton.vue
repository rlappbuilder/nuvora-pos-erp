<script setup>

import { computed } from 'vue'

defineOptions({

    inheritAttrs: false

})

const props = defineProps({

    variant: {

        type: String,

        default: 'primary'

    },

    size: {

        type: String,

        default: 'md'

    },

    type: {

        type: String,

        default: 'button'

    },

    disabled: {

        type: Boolean,

        default: false

    },

    loading: {

        type: Boolean,

        default: false

    },

    block: {

        type: Boolean,

        default: false

    }

})

const emit = defineEmits([

    'click'

])

const variantClass = computed(() => {

    switch (props.variant) {

        case 'secondary':

            return 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50'

        case 'danger':

            return 'border border-red-600 bg-white text-red-600 hover:bg-red-50'

        case 'success':

           return 'border border-emerald-600 bg-white text-emerald-600 hover:bg-emerald-50'

        case 'warning':

          return 'border border-amber-500 bg-white text-amber-600 hover:bg-amber-50'

        case 'info':

        return 'border border-sky-600 bg-white text-sky-600 hover:bg-sky-50'

        default:

            return 'bg-indigo-600 text-white hover:bg-indigo-700'

    }

})

const sizeClass = computed(() => {

    switch (props.size) {

        case 'sm':

            return 'px-3 py-2 text-sm'

        case 'lg':

            return 'px-6 py-3 text-base'

        default:

            return 'px-4 py-2.5 text-sm'

    }

})

const buttonClass = computed(() => [

    'inline-flex',

    'items-center',

    'justify-center',

    'gap-2',

    'rounded-xl',

    'font-medium',

    'transition-all',

    'shadow-sm',

    'hover:shadow',

    'active:scale-[0.98]',

    'duration-200',

    'focus:outline-none',

    'focus:ring-2',

    'focus:ring-indigo-200',

    'disabled:cursor-not-allowed',

    'disabled:opacity-60',

    props.block ? 'w-full' : '',

    variantClass.value,

    sizeClass.value

])

function onClick(event) {

    if (

        props.disabled ||

        props.loading

    ) {

        return

    }

    emit(

        'click',

        event

    )

}

</script>
<template>

<button v-bind="$attrs"

    :type="type"

    :disabled="

        disabled ||

        loading

    "

:class="[

    buttonClass,

    $attrs.class

]" 

    @click="onClick"

>

    <!-- ====================================== -->
    <!-- Loading -->
    <!-- ====================================== -->

    <svg

        v-if="loading"

        class="
            h-4
            w-4
            animate-spin
            shrink-0
        "

        viewBox="0 0 24 24"

        fill="none"

    >

        <circle

            cx="12"

            cy="12"

            r="10"

            stroke="currentColor"

            stroke-width="4"

            class="opacity-25"

        />

        <path

            d="M22 12a10 10 0 00-10-10"

            stroke="currentColor"

            stroke-width="4"

            class="opacity-75"

        />

    </svg>

    <!-- ====================================== -->
    <!-- Left Icon -->
    <!-- ====================================== -->

    <slot

    v-if="!loading"

    name="icon"

/>

    <!-- ====================================== -->
    <!-- Text -->
    <!-- ====================================== -->

    <span>

        <slot />

    </span>

</button>

</template>