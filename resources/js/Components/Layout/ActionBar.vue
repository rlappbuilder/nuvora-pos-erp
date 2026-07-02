<script setup>

const props = defineProps({

    align: {

        type: String,

        default: 'right'

    },

    bordered: {

        type: Boolean,

        default: false

    }

})

</script>
<template>

<div

    :class="[

        'flex',

        'items-center',

        align === 'left'

            ? 'justify-start'

            : align === 'center'

                ? 'justify-center'

                : align === 'between'

                    ? 'justify-between'

                    : 'justify-end',

        bordered

            ? 'border-t border-gray-200 pt-6 mt-6'

            : ''

    ]"

>

    <slot />

</div>

</template>