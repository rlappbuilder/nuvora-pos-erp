<script setup>
import BulkActionDropdown

from './BulkActionDropdown.vue'
import BaseButton

from '@/Components/Button/BaseButton.vue'

const props = defineProps({

    count: {

        type: Number,

        default: 0,

    },

})

const emit = defineEmits([

    'delete',

    'activate',

    'deactivate',

    'cancel',

])

</script>

<template>

<div
    class="
        flex
        w-full
        items-center
        justify-between
    "
>

    <div>

        {{ count }} item(s) selected

    </div>

    <div
        class="
            flex
            items-center
            gap-2
        "
    >

        <BulkActionDropdown
            @delete="$emit('delete')"
            @activate="$emit('activate')"
            @deactivate="$emit('deactivate')"
        />

        <BaseButton

            variant="secondary"

            @click="$emit('cancel')"

        >

            Cancel

        </BaseButton>

    </div>

</div>

</template>