<script setup>

import BaseButton from '@/Components/Button/BaseButton.vue'
import { formatDate } from '@/Utils'


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

    consignmentOut: {
        type: Object,
        default: null,
    },

    loading: {
        type: Boolean,
        default: false,
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
| Actions
|--------------------------------------------------------------------------
*/

function closeCancel()
{
    emit('close')
}


function confirmCancel()
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
                overflow-y-auto
                bg-black/40
                px-4
                py-6
            "
            @click.self="closeCancel"
        >

            <div
                class="
                    flex
                    max-h-[calc(100vh-3rem)]
                    w-full
                    max-w-lg
                    flex-col
                    overflow-hidden
                    rounded-2xl
                    bg-white
                    shadow-2xl
                "
            >

                <!-- ================================================= -->
                <!-- Header -->
                <!-- ================================================= -->

                <div
                    class="
                        flex
                        items-center
                        justify-between
                        border-b
                        border-gray-200
                        px-6
                        py-5
                    "
                >

                    <div>

                        <h2
                            class="
                                text-xl
                                font-semibold
                                text-gray-900
                            "
                        >
                            Cancel Consignment Out
                        </h2>

                        <p
                            class="
                                mt-1
                                text-sm
                                text-gray-500
                            "
                        >
                            Cancel this consignment out transaction.
                        </p>

                    </div>


                    <button
                        type="button"
                        class="
                            rounded-lg
                            p-2
                            text-gray-400
                            transition
                            hover:bg-gray-100
                            hover:text-gray-700
                        "
                        @click="closeCancel"
                    >
                        ✕
                    </button>

                </div>


                <!-- ================================================= -->
                <!-- Body -->
                <!-- ================================================= -->

                <div
                    class="
                        flex-1
                        overflow-y-auto
                        p-6
                    "
                >

                    <template
                        v-if="consignmentOut"
                    >

                        <!-- ========================================= -->
                        <!-- Consignment Out Information -->
                        <!-- ========================================= -->

                        <div
                            class="
                                rounded-xl
                                border
                                border-gray-200
                                bg-gray-50
                                p-5
                            "
                        >

                            <div
                                class="
                                    grid
                                    grid-cols-1
                                    gap-4
                                    sm:grid-cols-2
                                "
                            >

                                <!-- Number -->

                                <div>

                                    <div
                                        class="
                                            text-xs
                                            font-medium
                                            uppercase
                                            tracking-wide
                                            text-gray-500
                                        "
                                    >
                                        Number
                                    </div>

                                    <div
                                        class="
                                            mt-1
                                            font-semibold
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            consignmentOut
                                                .consignment_out_number
                                            ?? '-'
                                        }}
                                    </div>

                                </div>


                                <!-- Transaction Date -->

                                <div>

                                    <div
                                        class="
                                            text-xs
                                            font-medium
                                            uppercase
                                            tracking-wide
                                            text-gray-500
                                        "
                                    >
                                        Transaction Date
                                    </div>

                                    <div
                                        class="
                                            mt-1
                                            font-semibold
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            consignmentOut.transaction_date
                                                ? formatDate(
                                                    consignmentOut.transaction_date
                                                )
                                                : '-'
                                        }}
                                    </div>

                                </div>


                                <!-- Branch -->

                                <div>

                                    <div
                                        class="
                                            text-xs
                                            font-medium
                                            uppercase
                                            tracking-wide
                                            text-gray-500
                                        "
                                    >
                                        Branch
                                    </div>

                                    <div
                                        class="
                                            mt-1
                                            font-semibold
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            consignmentOut
                                                .branch
                                                ?.name
                                            ?? '-'
                                        }}
                                    </div>

                                </div>


                                <!-- Warehouse -->

                                <div>

                                    <div
                                        class="
                                            text-xs
                                            font-medium
                                            uppercase
                                            tracking-wide
                                            text-gray-500
                                        "
                                    >
                                        Warehouse
                                    </div>

                                    <div
                                        class="
                                            mt-1
                                            font-semibold
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            consignmentOut
                                                .warehouse
                                                ?.name
                                            ?? '-'
                                        }}
                                    </div>

                                </div>


                                <!-- Reseller -->

                                <div
                                    class="
                                        sm:col-span-2
                                    "
                                >

                                    <div
                                        class="
                                            text-xs
                                            font-medium
                                            uppercase
                                            tracking-wide
                                            text-gray-500
                                        "
                                    >
                                        Reseller
                                    </div>

                                    <div
                                        class="
                                            mt-1
                                            font-semibold
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            consignmentOut
                                                .reseller
                                                ?.name
                                            ?? '-'
                                        }}
                                    </div>

                                </div>

                            </div>

                        </div>


                        <!-- ========================================= -->
                        <!-- Warning -->
                        <!-- ========================================= -->

                        <div
                            class="
                                mt-5
                                rounded-xl
                                border
                                border-red-200
                                bg-red-50
                                p-4
                            "
                        >

                            <div
                                class="
                                    text-sm
                                    font-semibold
                                    text-red-800
                                "
                            >
                                Before cancelling
                            </div>

                            <p
                                class="
                                    mt-1
                                    text-sm
                                    leading-6
                                    text-red-700
                                "
                            >
                                This consignment out will be
                                cancelled. If this transaction has
                                already been posted, cancelling it
                                will reverse the inventory movement
                                between the warehouse and reseller
                                consignment stock and create the
                                corresponding reversal journal entry.
                            </p>

                        </div>


                        <!-- ========================================= -->
                        <!-- Cancellation Reason -->
                        <!-- ========================================= -->

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
                                Cancellation Reason

                                <span
                                    class="text-red-500"
                                >
                                    *
                                </span>

                            </label>


                            <textarea
                                :value="reason"
                                @input="
                                    emit(
                                        'update:reason',
                                        $event.target.value
                                    )
                                "
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
                                    transition
                                    focus:border-red-400
                                    focus:ring-2
                                    focus:ring-red-100
                                "
                                placeholder="Enter cancellation reason..."
                            ></textarea>

                        </div>

                    </template>

                </div>


                <!-- ================================================= -->
                <!-- Footer -->
                <!-- ================================================= -->

                <div
                    class="
                        flex
                        items-center
                        justify-end
                        gap-3
                        border-t
                        border-gray-200
                        bg-gray-50
                        px-6
                        py-4
                    "
                >

                    <BaseButton
                        type="button"
                        variant="secondary"
                        @click="closeCancel"
                    >
                        Cancel
                    </BaseButton>


                    <BaseButton
                        type="button"
                        variant="danger"
                        :loading="loading"
                        :disabled="
                            !consignmentOut ||
                            loading ||
                            !reason.trim()
                        "
                        @click="confirmCancel"
                    >
                        Cancel Consignment Out
                    </BaseButton>

                </div>

            </div>

        </div>

    </Teleport>

</template>