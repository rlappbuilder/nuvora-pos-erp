<script setup>

defineProps({

    bordered: {

        type: Boolean,

        default: true,

    },

})

</script>

<template>

<div

    :class="[

        'flex',

        'flex-col',

        'gap-5',

        'rounded-2xl',

        'bg-white',

        'p-6',

        bordered

            ? 'border border-gray-200'

            : '',

        'shadow-sm',

        'lg:flex-row',

        'lg:items-center',

        'lg:justify-between',

    ]"

>

    <!-- ===================================================== -->
    <!-- Search -->
    <!-- ===================================================== -->

    <div

        class="
            w-full
            lg:max-w-sm
        "

    >

        <slot

            name="search"

        />

    </div>

    <!-- ===================================================== -->
    <!-- Filters -->
    <!-- ===================================================== -->

    <div

        class="
            flex
            flex-1
            flex-wrap
            items-center
            gap-3
        "

    >

        <slot

            name="filters"

        />

    </div>

    <!-- ===================================================== -->
    <!-- Actions -->
    <!-- ===================================================== -->

    <div

        class="
            flex
            items-center
            justify-end
            gap-3
        "

    >

        <slot

            name="actions"

        />

    </div>

</div>

</template>