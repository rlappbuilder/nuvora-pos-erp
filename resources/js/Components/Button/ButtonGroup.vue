<script setup>

const props = defineProps({

    align: {

        type: String,

        default: 'right'

    },

    vertical: {

        type: Boolean,

        default: false

    },
    gap: {

    type: String,

    default: 'gap-3'

}

})

</script>
<template>

<div

    :class="[

        'flex',
        'flex-wrap',

        vertical

           ? 'flex-col items-stretch'

            : 'flex-row items-center',

        props.gap,

        align === 'left'

            ? 'justify-start'

            : align === 'center'

                ? 'justify-center'

                : align === 'between'

                    ? 'justify-between'

                    : 'justify-end'

    ]"

>

    <slot />

</div>

</template>