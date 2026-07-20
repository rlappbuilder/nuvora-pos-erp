<script setup>

const props = defineProps({

    label: {
        type: String,
        required: true,
    },

    value: {
        type: [String, Number],
        default: '-',
    },

    icon: {
        type: String,
        default: '',
    },

})

</script>
<template>

<div
    class="
        flex
        flex-col
        gap-2
        border-b
        border-gray-100
        py-3
        last:border-0
        sm:flex-row
        sm:items-center
    "
>

    <!-- Label -->

    <div
      class="
            w-full
            shrink-0
            text-sm
            font-medium
            text-gray-500
            sm:w-48
        "
    >
        {{ label }}
    </div>

    <!-- Colon -->

    <div
        class="
        hidden
        w-4
        shrink-0
        text-gray-400
        sm:block
    "
    >
        :
    </div>

    <!-- Value -->

    <div
      class="
            flex
            flex-wrap
            items-center
            gap-2
            text-sm
            font-semibold
            text-gray-900
            break-all
        "
    >

        <span v-if="icon">

            {{ icon }}

        </span>

      <slot>

        <span>

            {{ value || '-' }}

        </span>

    </slot>

    </div>

</div>

</template>