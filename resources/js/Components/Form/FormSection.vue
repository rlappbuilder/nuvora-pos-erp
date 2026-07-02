<script setup>

import { computed } from 'vue'

import Card from '@/Components/Layout/Card.vue'

const props = defineProps({

    title: {

        type: String,

        required: true

    },

    description: {

        type: String,

        default: ''

    },

    icon: {

        type: String,

        default: ''

    },

    columns: {

        type: Number,

        default: 2

    }

})

const gridClass = computed(() => {

    switch (props.columns) {

        case 1:

            return 'grid grid-cols-1 gap-6'

        case 2:

            return 'grid grid-cols-1 gap-6 md:grid-cols-2'

        case 3:

            return 'grid grid-cols-1 gap-6 lg:grid-cols-3'

        case 4:

            return 'grid grid-cols-1 gap-6 xl:grid-cols-4'

        default:

            return 'grid grid-cols-1 gap-6 md:grid-cols-2'

    }

})

</script>
<template>

<Card

    :icon="icon"

    :title="title"

    :description="description"

>

    <!-- ===================================================== -->
    <!-- Header Action -->
    <!-- ===================================================== -->

    <template

        v-if="$slots.header"

        #actions

    >

        <slot

            name="header"

        />

    </template>

    <!-- ===================================================== -->
    <!-- Body -->
    <!-- ===================================================== -->

    <div

        :class="gridClass"

    >

        <slot />

    </div>

    <!-- ===================================================== -->
    <!-- Footer -->
    <!-- ===================================================== -->

    <template

        v-if="$slots.footer"

        #footer

    >

        <slot

            name="footer"

        />

    </template>

</Card>

</template>