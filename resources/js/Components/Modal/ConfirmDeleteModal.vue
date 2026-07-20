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

    @close="emit('close')"

>

    <div

        class="
            flex
            flex-col
            items-center
            text-center
        "

    >

        <div

            class="
                mx-auto
                mb-6
                flex
                h-16
                w-16
                items-center
                justify-center
                rounded-full
                bg-red-50
                text-3xl
                text-red-600
            "

        >

            ⚠️

        </div>
        <!-- message-->
      <div class="text-center">

            <p

                class="
                    text-base
                    font-medium
                    text-gray-800
                "

            >

                {{ message }}

            </p>

            <p

                class="
                    mt-3
                    text-sm
                    text-gray-500
                "

            >

                This action cannot be undone.

            </p>

        </div>
        <!-- end message-->
    </div>

    <template #footer>

        <BaseButton

            variant="secondary"

            @click="emit('close')"

        >

            Cancel

        </BaseButton>

        <BaseButton

            :variant="confirmVariant"

            :loading="loading"

            @click="emit('confirm')"

        >

            {{ confirmText }}

        </BaseButton>

    </template>

</BaseModal>

</template>