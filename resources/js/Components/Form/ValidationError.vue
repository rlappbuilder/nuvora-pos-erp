<script setup>

defineProps({

    message: {

        type: String,

        default: ''

    }

})

</script>

<template>

    <Transition

        enter-active-class="transition duration-200 ease-out"

        enter-from-class="opacity-0 -translate-y-1"

        enter-to-class="opacity-100 translate-y-0"

        leave-active-class="transition duration-150 ease-in"

        leave-from-class="opacity-100"

        leave-to-class="opacity-0"

    >

        <p

            v-if="message"

            class="
                mt-2
                flex
                items-center
                gap-2
                text-sm
                font-medium
                text-red-600
            "

        >

            <svg

                xmlns="http://www.w3.org/2000/svg"

                fill="none"

                viewBox="0 0 24 24"

                stroke="currentColor"

                class="
                    h-4
                    w-4
                    flex-shrink-0
                "

            >

                <path

                    stroke-linecap="round"

                    stroke-linejoin="round"

                    stroke-width="2"

                    d="M12 9v2m0 4h.01M12 3C7.03 3 3 7.03 3 12s4.03 9 9 9 9-4.03 9-9-4.03-9-9-9z"

                />

            </svg>

            <span>

                {{ message }}

            </span>

        </p>

    </Transition>

</template>