<script setup>

import { CheckIcon } from '@heroicons/vue/24/outline'
import BaseButton from './BaseButton.vue'

defineProps({

    loading: Boolean,

    disabled: Boolean,

    size: {

        type: String,

        default: 'md'

    },

    type: {

        type: String,

        default: 'submit'

    },

    variant: {

        type: String,

        default: 'primary'

    },

    block: Boolean,

})

defineEmits([
    'click'
])

</script>

<template>

<BaseButton

    :loading="loading"

    :disabled="disabled"

    :size="size"

    :type="type"

    :variant="variant"

    :block="block"

    @click="$emit('click', $event)"

>

    <template #icon>

        <CheckIcon class="h-4 w-4" />

    </template>

    <slot>

        Simpan

    </slot>

</BaseButton>

</template>