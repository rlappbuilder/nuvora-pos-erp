<script setup>

import BaseButton
    from '@/Components/Button/BaseButton.vue'

import { formatDate }
    from '@/Utils'


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

    purchaseReturn: {
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


function updateReason(event)
{
    emit(
        'update:reason',
        event.target.value
    )
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
                            Cancel Purchase Return
                        </h2>

                        <p
                            class="
                                mt-1
                                text-sm
                                text-gray-500
                            "
                        >
                            Cancel this purchase return transaction.
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
                        title="Close"
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
                        v-if="purchaseReturn"
                    >

                        <!-- ========================================= -->
                        <!-- Purchase Return Information -->
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

                                <!-- Return Number -->

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
                                        Return Number
                                    </div>

                                    <div
                                        class="
                                            mt-1
                                            font-semibold
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            purchaseReturn
                                                .return_number
                                            ?? '-'
                                        }}
                                    </div>

                                </div>


                                <!-- Return Date -->

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
                                        Return Date
                                    </div>

                                    <div
                                        class="
                                            mt-1
                                            font-semibold
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            purchaseReturn.return_date
                                                ? formatDate(
                                                    purchaseReturn.return_date
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
                                            purchaseReturn
                                                .purchase_order
                                                ?.number
                                            ??
                                            purchaseReturn
                                                .purchaseOrder
                                                ?.number
                                            ??
                                            '-'
                                        }}
                                    </div>

                                </div>


                                <!-- Goods Receipt -->

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
                                        Goods Receipt
                                    </div>

                                    <div
                                        class="
                                            mt-1
                                            font-semibold
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            purchaseReturn
                                                .goods_receipt
                                                ?.grn_number
                                            ??
                                            purchaseReturn
                                                .goodsReceipt
                                                ?.grn_number
                                            ??
                                            '-'
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
                                            purchaseReturn
                                                .supplier
                                                ?.name
                                            ??
                                            purchaseReturn
                                                .supplier
                                                ?.label
                                            ??
                                            '-'
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
                                            purchaseReturn
                                                .branch
                                                ?.name
                                            ??
                                            purchaseReturn
                                                .branch
                                                ?.label
                                            ??
                                            '-'
                                        }}
                                    </div>

                                </div>


                                <!-- Warehouse -->

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
                                            purchaseReturn
                                                .warehouse
                                                ?.name
                                            ??
                                            purchaseReturn
                                                .warehouse
                                                ?.label
                                            ??
                                            '-'
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
                                This purchase return will be
                                cancelled. Cancelling it will change
                                its status to Cancelled and prevent
                                it from proceeding to the next
                                purchasing workflow.
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
                                @input="updateReason"
                            ></textarea>


                            <p
                                class="
                                    mt-1.5
                                    text-xs
                                    text-gray-500
                                "
                            >
                                Please provide a reason for
                                cancelling this purchase return.
                            </p>

                        </div>

                    </template>


                    <!-- ============================================= -->
                    <!-- No Data -->
                    <!-- ============================================= -->

                    <div
                        v-else
                        class="
                            flex
                            min-h-[250px]
                            items-center
                            justify-center
                            text-sm
                            text-gray-500
                        "
                    >
                        Purchase return data is not available.
                    </div>

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
                            !purchaseReturn ||
                            loading ||
                            !reason.trim()
                        "
                        @click="confirmCancel"
                    >
                        Cancel Purchase Return
                    </BaseButton>

                </div>

            </div>

        </div>

    </Teleport>

</template>