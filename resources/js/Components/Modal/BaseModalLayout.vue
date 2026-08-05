<script setup>
defineProps({

    title: {
        type: String,
        required: true,
    },

    subtitle: {
        type: String,
        default: '',
    },

});
</script>

<template>

    <div
        class="
            flex
            flex-col
            max-h-[90vh]
        "
    >

        <!-- Header -->

        <div
              class="
                -mx-6
                px-8
                py-6
                mb-6
                bg-indigo-50
                border-b
                border-indigo-100
            "
        >

            <div class="flex items-start gap-3">

                <slot name="icon" />

                <div>

                    <h2
                        class="
                            text-xl
                            font-bold
                            text-indigo-900
                        "
                    >
                        {{ title }}
                    </h2>

                    <p
                        v-if="subtitle"
                        class="
                            mt-1
                            text-sm
                            text-indigo-600
                        "
                    >
                        {{ subtitle }}
                    </p>

                </div>

            </div>

        </div>

        <!-- Content -->

        <div
            class="
                flex-1
                overflow-y-auto
                px-3
            "
        >

            <slot name="content" />

        </div>

        <!-- Footer -->

        <div
            class="
                mt-6
                pt-4
                pb-2
                border-t
                border-gray-200
                bg-white
            "
        >

            <slot name="footer" />

        </div>

    </div>

</template>