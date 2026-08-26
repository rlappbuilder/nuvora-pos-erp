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

    goodsReceipt: {
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
                            Reject Goods Receipt
                        </h2>

                        <p
                            class="
                                mt-1
                                text-sm
                                text-gray-500
                            "
                        >
                            Reject this submitted goods receipt.
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
                        v-if="goodsReceipt"
                    >

                        <!-- ========================================= -->
                        <!-- Goods Receipt Information -->
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

                                <!-- GRN Number -->

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
                                        GRN Number
                                    </div>

                                    <div
                                        class="
                                            mt-1
                                            font-semibold
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            goodsReceipt
                                                .grn_number
                                            ?? '-'
                                        }}
                                    </div>

                                </div>


                                <!-- Receipt Date -->

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
                                        Receipt Date
                                    </div>

                                    <div
                                        class="
                                            mt-1
                                            font-semibold
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            goodsReceipt
                                                .receipt_date
                                                ? formatDate(
                                                    goodsReceipt
                                                        .receipt_date
                                                )
                                                : '-'
                                        }}
                                    </div>

                                </div>


                                <!-- Purchase Order -->

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
                                        Purchase Order
                                    </div>

                                    <div
                                        class="
                                            mt-1
                                            font-semibold
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            goodsReceipt
                                                .purchaseOrder
                                                ?.number
                                            ??
                                            goodsReceipt
                                                .purchase_order
                                                ?.number
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
                                            goodsReceipt
                                                .supplier
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
                                            goodsReceipt
                                                .warehouse
                                                ?.name
                                            ?? '-'
                                        }}
                                    </div>

                                </div>


                                <!-- Supplier DO -->

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
                                        Supplier DO
                                    </div>

                                    <div
                                        class="
                                            mt-1
                                            font-semibold
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            goodsReceipt
                                                .supplier_do_number
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
                                This goods receipt has been
                                submitted for approval. Rejecting it
                                will change its status to Rejected
                                and prevent it from proceeding to
                                the posting process.
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
                                    focus:border-red-400
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
                            !goodsReceipt ||
                            loading ||
                            !reason.trim()
                        "
                        @click="confirmReject"
                    >
                        Reject Goods Receipt
                    </BaseButton>

                </div>

            </div>

        </div>

    </Teleport>

</template>