<script setup>

import { computed } from 'vue'

const model = defineModel({

    type: Boolean,

    default: false,

})

const props = defineProps({

    label: {

        type: String,

        default: '',

    },

    description: {

        type: String,

        default: '',

    },

    disabled: {

        type: Boolean,

        default: false,

    },

    variant: {

        type: String,

        default: 'checkbox',

    },

})

const isSwitch = computed(() => {

    return props.variant === 'switch'

})

</script>

<template>

<div>

    <!-- Checkbox -->

    <label

        v-if="!isSwitch"

        class="
            inline-flex
            items-center
            gap-3
            cursor-pointer
            select-none
        "

    >

        <input

            v-model="model"

            type="checkbox"

            :disabled="disabled"

            class="
                h-5
                w-5
                rounded
                border-gray-300
                text-blue-600
                focus:ring-blue-500
            "

        >

        <div>

            <div class="font-medium">

                {{ label }}

            </div>

            <div

                v-if="description"

                class="text-sm text-gray-500"

            >

                {{ description }}

            </div>

        </div>

    </label>

    <!-- Switch -->

    <label

        v-else

        class="
            flex
            items-center
            justify-between
            gap-4
            cursor-pointer
        "

    >

        <div>

            <div class="font-medium">

                {{ label }}

            </div>

            <div

                v-if="description"

                class="text-sm text-gray-500"

            >

                {{ description }}

            </div>

        </div>

        <button

            type="button"

            :disabled="disabled"

            @click="model = !model"

            class="
                relative
                inline-flex
                h-6
                w-11
                rounded-full
                transition-colors
            "

            :class="

                model

                    ? 'bg-blue-600'

                    : 'bg-gray-300'

            "

        >

            <span

                class="
                    inline-block
                    h-5
                    w-5
                    rounded-full
                    bg-white
                    transform
                    transition
                    mt-0.5
                "

                :class="

                    model

                        ? 'translate-x-5'

                        : 'translate-x-0.5'

                "

            />

        </button>

    </label>

</div>

</template>