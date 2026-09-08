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

    purchasePayment: {
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
                overflow-y-auto
                bg-black/40
                px-4
                py-6
            "
            @click.self="closeReject"
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
                            Reject Purchase Payment
                        </h2>

                        <p
                            class="
                                mt-1
                                text-sm
                                text-gray-500
                            "
                        >
                            Reject this submitted purchase payment.
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
                        @click="closeReject"
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
                        v-if="purchasePayment"
                    >

                        <!-- ========================================= -->
                        <!-- Purchase Payment Information -->
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
                                            purchasePayment.number
                                            ?? '-'
                                        }}
                                    </div>

                                </div>


                                <!-- Payment Date -->

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
                                        Payment Date
                                    </div>

                                    <div
                                        class="
                                            mt-1
                                            font-semibold
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            purchasePayment.payment_date
                                                ? formatDate(
                                                    purchasePayment.payment_date
                                                )
                                                : '-'
                                        }}
                                    </div>

                                </div>


                                <!-- Payment Method -->

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
                                        Payment Method
                                    </div>

                                    <div
                                        class="
                                            mt-1
                                            font-semibold
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            purchasePayment
                                                .payment_method
                                            ?? '-'
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
                                            purchasePayment
                                                .branch
                                                ?.name
                                            ?? '-'
                                        }}
                                    </div>

                                </div>


                                <!-- Supplier -->

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
                                        Supplier
                                    </div>

                                    <div
                                        class="
                                            mt-1
                                            font-semibold
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            purchasePayment
                                                .supplier
                                                ?.name
                                            ?? '-'
                                        }}
                                    </div>

                                </div>


                                <!-- Payment Account -->

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
                                        Payment Account
                                    </div>

                                    <div
                                        class="
                                            mt-1
                                            font-semibold
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            purchasePayment
                                                .paymentAccount
                                                ?.name
                                            ??
                                            purchasePayment
                                                .payment_account
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
                                Before rejecting
                            </div>

                            <p
                                class="
                                    mt-1
                                    text-sm
                                    text-red-700
                                "
                            >
                                This purchase payment has been
                                submitted for approval. Rejecting it
                                will change its status to Rejected and
                                prevent it from being posted.
                            </p>

                        </div>


                        <!-- ========================================= -->
                        <!-- Rejection Reason -->
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
                                Rejection Reason

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
                                    focus:border-emereald-400
                                    focus:ring-2
                                    focus:ring-red-100
                                "
                                placeholder="Enter rejection reason..."
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
                        @click="closeReject"
                    >
                        Cancel
                    </BaseButton>


                    <BaseButton
                        type="button"
                        variant="danger"
                        :loading="loading"
                        :disabled="
                            !purchasePayment ||
                            loading ||
                            !reason.trim()
                        "
                        @click="confirmReject"
                    >
                        Reject Purchase Payment
                    </BaseButton>

                </div>

            </div>

        </div>

    </Teleport>

</template>