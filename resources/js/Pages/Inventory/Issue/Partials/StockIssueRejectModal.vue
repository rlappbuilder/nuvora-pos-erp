<script setup>

import BaseButton from '@/Components/Button/BaseButton.vue'


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    show: {
        type: Boolean,
        default: false,
    },

    issue: {
        type: Object,
        default: null,
    },

    reason: {
        type: String,
        default: '',
    },

})


/*
|--------------------------------------------------------------------------
| Emits
|--------------------------------------------------------------------------
*/

const emit = defineEmits([
    'close',
    'confirm',
    'update:reason',
])


/*
|--------------------------------------------------------------------------
| Reason
|--------------------------------------------------------------------------
*/

function updateReason(event)
{
    emit(
        'update:reason',
        event.target.value
    )
}


/*
|--------------------------------------------------------------------------
| Actions
|--------------------------------------------------------------------------
*/

function closeReject()
{
    emit('close')
}


function confirmReject()
{
    emit('confirm')
}

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
            @click.self="closeReject"
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

                <!-- ================================================= -->
                <!-- Header -->
                <!-- ================================================= -->

                <h2
                    class="
                        text-lg
                        font-semibold
                        text-gray-900
                    "
                >
                    Reject Stock Issue
                </h2>


                <p
                    class="
                        mt-2
                        text-sm
                        text-gray-500
                    "
                >

                    {{
                        issue
                            ? `Reject "${issue.number}".`
                            : ''
                    }}

                </p>


                <!-- ================================================= -->
                <!-- Reason -->
                <!-- ================================================= -->

                <div class="mt-5">

                    <label
                        class="
                            mb-2
                            block
                            text-sm
                            font-medium
                            text-gray-700
                        "
                    >

                        Rejection Reason

                        <span class="text-red-500">
                            *
                        </span>

                    </label>


                    <textarea
                        :value="reason"
                        rows="4"
                        class="
                            w-full
                            rounded-xl
                            border
                            border-gray-300
                            px-4
                            py-3
                            text-sm
                            outline-none
                            focus:border-red-400
                            focus:ring-2
                            focus:ring-red-100
                        "
                        placeholder="Enter rejection reason..."
                        @input="updateReason"
                    ></textarea>

                </div>


                <!-- ================================================= -->
                <!-- Actions -->
                <!-- ================================================= -->

                <div
                    class="
                        mt-6
                        flex
                        justify-end
                        gap-3
                    "
                >

                    <BaseButton
                        type="button"
                        variant="secondary"
                        @click="closeReject"
                    >
                        Cancel
                    </BaseButton>


                    <BaseButton
                        type="button"
                        variant="danger"
                        @click="confirmReject"
                    >
                        Reject
                    </BaseButton>

                </div>

            </div>

        </div>

    </Teleport>

</template>