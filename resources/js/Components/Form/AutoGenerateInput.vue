<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { RefreshCw, LoaderCircle } from 'lucide-vue-next'

const props = defineProps({
    label: {
        type: String,
        default: '',
    },

    modelValue: {
        type: String,
        default: '',
    },

    generateRoute: {
        type: String,
        required: true,
    },

        responseKey: {
        type: String,
        default: 'sku',
    },

    placeholder: {
        type: String,
        default: '',
    },

    readonly: {
        type: Boolean,
        default: false,
    },

    disabled: {
        type: Boolean,
        default: false,
    },

    error: {
        type: String,
        default: '',
    },
})

const emit = defineEmits([
    'update:modelValue',
])

const loading = ref(false)

const generate = async () => {
    try {

        loading.value = true

        const response = await axios.get(props.generateRoute)

        const value = response.data[props.responseKey] ?? ''

        if (value) {
            emit('update:modelValue', value)
        }

    } catch (error) {

        console.error('Failed to generate value:', error)

    } finally {

        loading.value = false

    }
}
</script>

<template>

    <div class="space-y-2">

        <label
            v-if="label"
            class="text-sm font-medium"
        >
            {{ label }}
        </label>

        <div class="flex gap-2">

            <input
                :value="modelValue"
                @input="$emit('update:modelValue', $event.target.value)"
                :placeholder="placeholder"
                :readonly="readonly"
                :disabled="disabled"
                class="flex-1 rounded-md border px-3 py-2"
            />

            <button
                type="button"
                @click="generate"
                :disabled="loading || disabled"
                class="rounded-md border p-2 hover:bg-gray-100 disabled:opacity-50"
            >

                <LoaderCircle
                    v-if="loading"
                    class="h-4 w-4 animate-spin"
                />

                <RefreshCw
                    v-else
                    class="h-4 w-4"
                />

            </button>

        </div>

        <p
            v-if="error"
            class="text-sm text-red-600"
        >
            {{ error }}
        </p>

    </div>

</template>