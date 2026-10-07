<script setup>

import BaseModal from './BaseModal.vue'

import BaseButton from '@/Components/Button/BaseButton.vue'

const {
    show,
    title,
    message,
    loading,
    confirmText,
    confirmVariant,
} = defineProps({

    show: {
        type: Boolean,
        default: false,
    },

    title: {
        type: String,
        default: 'Delete Confirmation',
    },

    message: {
        type: String,
        default: 'Are you sure you want to delete this record?',
    },

    loading: {
        type: Boolean,
        default: false,
    },

    confirmText: {
        type: String,
        default: 'Delete',
    },

    confirmVariant: {
        type: String,
        default: 'danger',
    },

})

const emit = defineEmits([
    'close',
    'confirm',
])

</script>

<template>

<BaseModal
    :show="show"
    :title="title"
    size="sm"
    @close="emit('close')"
>

    <div
        class="
            flex
            flex-col
            items-center
            px-2
            py-2
            text-center
        "
    >

        <!-- Icon -->

        <div
            class="
                mb-5
                flex
                h-14
                w-14
                items-center
                justify-center
                rounded-full
                bg-red-50
                text-red-600
                ring-8
                ring-red-50/50
            "
            aria-hidden="true"
        >

            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="
                    h-7
                    w-7
                "
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
            >

                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M12 9v4"
                />

                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M12 17h.01"
                />

                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M10.3 3.9 2.8 17a2 2 0 0 0 1.7 3h15a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"
                />

            </svg>

        </div>

        <!-- Message -->

        <div class="max-w-sm">

            <p
                class="
                    text-base
                    font-semibold
                    leading-6
                    text-gray-900
                "
            >
                {{ message }}
            </p>

            <p
                class="
                    mt-2
                    text-sm
                    leading-5
                    text-gray-500
                "
            >
                This action cannot be undone. The record will be permanently
                removed from the system.
            </p>

        </div>

    </div>

    <!-- Footer -->

    <template #footer>

        <div
            class="
                flex
                w-full
                items-center
                justify-end
                gap-3
            "
        >

            <BaseButton
                variant="secondary"
                :disabled="loading"
                @click="emit('close')"
            >
                Cancel
            </BaseButton>

            <BaseButton
                :variant="confirmVariant"
                :loading="loading"
                :disabled="loading"
                @click="emit('confirm')"
            >
                {{ confirmText }}
            </BaseButton>

        </div>

    </template>

</BaseModal>

</template>

