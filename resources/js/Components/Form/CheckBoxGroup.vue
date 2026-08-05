<script setup>


const model = defineModel({
    type: Array,
    default: () => [],
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

    options: {
        type: Array,
        default: () => [],
    },

    labelKey: {
        type: String,
        default: 'name',
    },

    valueKey: {
        type: String,
        default: 'id',
    },

    error: {
        type: String,
        default: '',
    },

})



</script>

<template>

<div class="space-y-3">

    <div
        v-if="label || description"
        class="space-y-1"
    >

        <label
            v-if="label"
            class="text-sm font-medium text-gray-700"
        >
            {{ label }}
        </label>

        <p
            v-if="description"
            class="text-xs text-gray-500"
        >
            {{ description }}
        </p>

    </div>

    <div class="space-y-3">

        <label
            v-for="option in options"
            :key="option[valueKey]"
            class="flex items-center gap-3 cursor-pointer"
        >

            <input
                v-model="model"
                type="checkbox"
                :value="option[valueKey]"
                class="
                    h-5
                    w-5
                    rounded
                    border-gray-300
                    text-blue-600
                    focus:ring-blue-500
                "
            >

            <span class="text-sm text-gray-700">

                {{ option[labelKey] }}

            </span>

        </label>

    </div>

    <p
        v-if="error"
        class="text-sm text-red-600"
    >
        {{ error }}
    </p>

</div>

</template>