<script setup>

const props = defineProps({

    align: {

        type: String,

        default: 'right'

    },

    vertical: {

        type: Boolean,

        default: false

    }

})

</script>
<template>

<div

    :class="[

        'flex',

        vertical

            ? 'flex-col'

            : 'flex-row',

        'gap-3',

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