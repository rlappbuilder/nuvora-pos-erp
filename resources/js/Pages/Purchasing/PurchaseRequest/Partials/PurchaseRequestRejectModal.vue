<script setup>

defineProps({

    show: {
        type: Boolean,
        default: false,
    },

    purchaseRequest: {
        type: Object,
        default: null,
    },

})

const emit = defineEmits([
    'close',
    'confirm',
])

</script>

<template>

    <Teleport to="body">

        <div
            v-if="show"
            class="
                fixed
                inset-0
                z-[100]
                flex
                items-center
                justify-center
                bg-black/40
                px-4
            "
        >

            <div
                class="
                    w-full
                    max-w-lg
                    rounded-2xl
                    bg-white
                    p-6
                    shadow-xl
                "
            >

                <h2
                    class="
                        text-lg
                        font-semibold
                        text-gray-900
                    "
                >
                    Reject Purchase Request
                </h2>


                <p
                    class="
                        mt-2
                        text-sm
                        text-gray-500
                    "
                >
                    {{
                        purchaseRequest
                            ? `Reject "${purchaseRequest.number}".`
                            : ''
                    }}
                </p>


                <div
                    class="
                        mt-6
                        flex
                        justify-end
                        gap-3
                    "
                >

                    <button
                        type="button"
                        @click="emit('close')"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        @click="emit('confirm')"
                    >
                        Reject
                    </button>

                </div>

            </div>

        </div>

    </Teleport>

</template>