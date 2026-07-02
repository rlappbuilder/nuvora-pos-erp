<script setup>

const props = defineProps({

    label: {

        type: String,

        default: ''

    },

    required: {

        type: Boolean,

        default: false

    },

    error: {

        type: String,

        default: ''

    },

    hint: {

        type: String,

        default: ''

    }

})

</script>
<template>

    <div>

        <!-- ========================================= -->
        <!-- Label -->
        <!-- ========================================= -->

        <label

            v-if="label"

            class="
                mb-2
                block
                text-sm
                font-medium
                text-gray-700
            "

        >

            {{ label }}

            <span

                v-if="required"

                class="
                    ml-1
                    text-red-500
                "

            >

                *

            </span>

        </label>

        <!-- ========================================= -->
        <!-- Field -->
        <!-- ========================================= -->

        <slot />

        <!-- ========================================= -->
        <!-- Error -->
        <!-- ========================================= -->

        <p

            v-if="error"

            class="
                mt-2
                text-sm
                text-red-600
            "

        >

            {{ error }}

        </p>

        <!-- ========================================= -->
        <!-- Hint -->
        <!-- ========================================= -->

        <p

            v-else-if="hint"

            class="
                mt-2
                text-xs
                text-gray-500
            "

        >

            {{ hint }}

        </p>

    </div>

</template>