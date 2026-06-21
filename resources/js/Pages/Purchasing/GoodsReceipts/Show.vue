<script setup>

import {
    Head,
    Link
} from '@inertiajs/vue3'
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout
from '@/Layouts/AuthenticatedLayout.vue'
import { ref } from 'vue'

const showCancelModal = ref(false)

const cancelReason = ref('')

const props = defineProps({

    goodsReceipt: Object,

})

const submitCancel = () => {

    router.patch(

        route(
            'goods-receipts.cancel',
            props.goodsReceipt.id
        ),

        {

            cancel_reason:
                cancelReason.value

        },

        {

            preserveScroll: true,

            onSuccess: () => {

                showCancelModal.value = false

                cancelReason.value = ''

            }

        }

    )

}
const formatCurrency = (
    value
) => {

    return 'Rp ' +

        Number(
            value || 0
        ).toLocaleString(
            'id-ID'
        )

}
const totalQty = computed(() => {

    return props.goodsReceipt.details.reduce(

        (total,item) =>

            total +

            Number(
                item.qty_received
            ),

        0

    )

})

const grandTotal = computed(() => {

    return props.goodsReceipt.details.reduce(

        (total,item) =>

            total +

            Number(
                item.line_total
            ),

        0

    )

})
</script>

<template>

    <Head
        title="Goods Receipt Detail"
    />

    <AuthenticatedLayout>

        <div
            class="
                p-6
            "
        >

            <!-- Header -->

                            <div
                        class="
                            mb-6
                            rounded-xl
                            bg-white
                            p-6
                            shadow
                        "
                    >

                        <div
                            class="
                                flex
                                items-center
                                justify-between
                            "
                        >

                            <!-- Kiri -->

                            <div>

                                <h1
                                    class="
                                        text-2xl
                                        font-bold
                                    "
                                >

                                    {{
                                        goodsReceipt
                                        .grn_number
                                    }}

                                </h1>

                                <p
                                    class="
                                        text-gray-500
                                    "
                                >

                                    Goods Receipt

                                </p>

                            </div>

                            <!-- Kanan -->

                            <div
                                class="
                                    flex
                                    items-center
                                    gap-2
                                "
                            >

                                <Link

                                    v-if="
                                        goodsReceipt.status
                                        === 'Draft'
                                    "

                                    method="patch"

                                    as="button"

                                    :href="
                                        route(
                                            'goods-receipts.post',
                                            goodsReceipt.id
                                        )
                                    "

                                    class="
                                        rounded-lg
                                        bg-green-600
                                        px-4
                                        py-2
                                        text-white
                                    "

                                >

                                    Post

                                </Link>
                                <!-- cancell BUttong-->
                                <button

                                    v-if="
                                        goodsReceipt.status
                                        === 'Draft'
                                    "

                                    @click="
                                        showCancelModal = true
                                    "

                                    class="
                                        rounded-lg
                                        bg-red-600
                                        px-4
                                        py-2
                                        text-white
                                    "

                                >

                                    Cancel

                                </button>
                                <!-- end cancell button -->
                                <button

                                    class="
                                        rounded-lg
                                        bg-slate-600
                                        px-4
                                        py-2
                                        text-white
                                    "

                                >

                                    Print

                                </button>

                                <Link

                                    :href="
                                        route(
                                            'goods-receipts.index'
                                        )
                                    "

                                    class="
                                        rounded-lg
                                        bg-blue-600
                                        px-4
                                        py-2
                                        text-white
                                    "

                                >

                                    Back

                                </Link>

                            </div>

                        </div>

                    </div>
            <!-- end header -->
            <!-- Document Information -->

            <div
                class="
                    mb-6
                    rounded-xl
                    bg-white
                    p-6
                    shadow
                "
            >

                <h2
                    class="
                        mb-4
                        text-lg
                        font-semibold
                    "
                >

                    Document Information

                </h2>

                <div
                    class="
                        grid
                        grid-cols-2
                        gap-4
                    "
                >

                    <div>

                        <strong>
                            Supplier
                        </strong>

                        <br>

                        {{
                            goodsReceipt
                            .supplier?.name
                        }}

                    </div>

                    <div>

                        <strong>
                            Warehouse
                        </strong>

                        <br>

                        {{
                            goodsReceipt
                            .warehouse?.name
                        }}

                    </div>

                    <div>

                        <strong>
                            Receipt Date
                        </strong>

                        <br>

                        {{
                            goodsReceipt
                            .receipt_date
                        }}

                    </div>

                    <div>

                        <strong>
                            Supplier DO
                        </strong>

                        <br>

                        {{
                            goodsReceipt
                            .supplier_do_number
                        }}

                    </div>

                    <div>

                        <strong>
                            Purchase Order
                        </strong>

                        <br>

                        {{
                             goodsReceipt
                            .purchase_order
                            ?.po_number

                        }}

                    </div>
     <div>

                        <strong>
                            Status
                        </strong>

                        <br>

                        <span

                            class="
                                rounded-full
                                bg-yellow-100
                                px-3
                                py-1
                                text-xs
                                font-semibold
                                text-yellow-700
                            "

                        >

                            {{
                                goodsReceipt
                                .status
                            }}

                        </span>

                    </div>

                </div>

            </div>

            <!-- Items -->

            <div
                class="
                    mb-6
                    rounded-xl
                    bg-white
                    p-6
                    shadow
                "
            >

                <h2
                    class="
                        mb-4
                        text-lg
                        font-semibold
                    "
                >

                    Items

                </h2>

                <table
                    class="
                        min-w-full
                    "
                >

                    <thead>

                        <tr
                            class="
                                border-b
                            "
                        >

                            <th
                                class="
                                    p-3
                                    text-left
                                "
                            >

                                Product

                            </th>

                            <th
                                class="
                                    p-3
                                    text-right
                                "
                            >

                                Qty

                            </th>

                            <th
                                class="
                                    p-3
                                    text-right
                                "
                            >

                                Cost

                            </th>

                            <th
                                class="
                                    p-3
                                    text-right
                                "
                            >

                                Total

                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        <tr

                            v-for="
                                item
                                in
                                goodsReceipt.details
                            "

                            :key="
                                item.id
                            "

                            class="
                                border-b
                            "

                        >

                            <td
                                class="
                                    p-3
                                "
                            >

                                {{
                                    item.product
                                    ?.name
                                }}

                            </td>

                            <td
                                class="
                                    p-3
                                    text-right
                                "
                            >

                                {{
                                    item.qty_received
                                }}

                            </td>

                            <td
                                class="
                                    p-3
                                    text-right
                                "
                            >

                                {{
                                    formatCurrency(
                                        item.unit_cost
                                    )
                                }}

                            </td>

                            <td
                                class="
                                    p-3
                                    text-right
                                "
                            >

                                {{
                                    formatCurrency(
                                        item.line_total
                                    )
                                }}

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

            <!-- Summary -->

                        <div
                class="
                    rounded-xl
                    bg-white
                    p-6
                    shadow
                "
            >

                    <h2
                        class="
                            mb-4
                            text-lg
                            font-semibold
                        "
                    >

                        Summary

                        </h2>

                        <div
                            class="
                                space-y-3
                            "
                        >

                            <div
                                class="
                                    flex
                                    justify-between
                                "
                            >

                                <span>
                                    Total Items
                                </span>

                                <span>

                                    {{
                                        goodsReceipt
                                        .details
                                        .length
                                    }}

                                </span>

                            </div>

                            <div
                                class="
                                    flex
                                    justify-between
                                "
                            >

                                <span>
                                    Total Qty
                                </span>

                                <span>

                                    {{
                                        totalQty
                                    }}

                                </span>

                            </div>

                            <div
                                class="
                                    flex
                                    justify-between
                                    border-t
                                    pt-3
                                    text-lg
                                    font-bold
                                "
                            >

                                <span>
                                    Grand Total
                                </span>

                                <span>

                                    {{
                                        formatCurrency(
                                            grandTotal
                                        )
                                    }}

                                </span>

                            </div>

                        </div>

                    </div>
            <!-- end summary card-->
                    <!-- autdit trail -->
<div
    class="
        mt-6
        rounded-xl
        bg-white
        p-6
        shadow
    "
>

    <h2
        class="
            mb-4
            text-lg
            font-semibold
        "
    >

        Audit Trail

    </h2>

    <div
        class="
            grid
            grid-cols-2
            gap-4
        "
    >

        <div>

            <p
                class="
                    text-sm
                    text-gray-500
                "
            >

                Created By

            </p>

            <p
                class="
                    font-semibold
                "
            >

                {{
                    goodsReceipt
                    .creator
                    ?.name
                    ?? '-'
                }}

            </p>

        </div>

        <div>

            <p
                class="
                    text-sm
                    text-gray-500
                "
            >

                Created At

            </p>

            <p
                class="
                    font-semibold
                "
            >

                {{
                    goodsReceipt
                    .created_at
                }}

            </p>

        </div>

        <div>

            <p
                class="
                    text-sm
                    text-gray-500
                "
            >

                Posted By

            </p>

            <p
                class="
                    font-semibold
                "
            >

                {{
                    goodsReceipt
                    .poster
                    ?.name
                    ?? '-'
                }}

            </p>

        </div>

        <div>

            <p
                class="
                    text-sm
                    text-gray-500
                "
            >

                Posted At

            </p>

            <p
                class="
                    font-semibold
                "
            >

                {{
                    goodsReceipt
                    .posted_at
                    ?? '-'
                }}

            </p>

        </div>

        <div>

            <p
                class="
                    text-sm
                    text-gray-500
                "
            >

                Cancelled By

            </p>

            <p
                class="
                    font-semibold
                "
            >

                {{
                    goodsReceipt
                    .canceller
                    ?.name
                    ?? '-'
                }}

            </p>

        </div>

        <div>

            <p
                class="
                    text-sm
                    text-gray-500
                "
            >

                Cancelled At

            </p>

            <p
                class="
                    font-semibold
                "
            >

                {{
                    goodsReceipt
                    .cancelled_at
                    ?? '-'
                }}

            </p>

        </div>

    </div>

    <div
        v-if="
            goodsReceipt
            .cancel_reason
        "
        class="
            mt-4
            rounded-lg
            bg-red-50
            p-4
        "
    >

        <p
            class="
                text-sm
                text-gray-500
            "
        >

            Cancel Reason

        </p>

        <p
            class="
                mt-1
                text-red-700
            "
        >

            {{
                goodsReceipt
                .cancel_reason
            }}

        </p>

    </div>

</div>
                    <!-- end audit trail-->
                     <div
    class="
        mt-6
        rounded-xl
        bg-white
        p-6
        shadow
    "
>

    <h2
        class="
            mb-6
            text-lg
            font-semibold
        "
    >

        Workflow Timeline

    </h2>

    <div
        class="
            space-y-6
        "
    >

        <!-- Draft -->

        <div
            class="
                flex
                items-start
                gap-4
            "
        >

            <div
                class="
                    h-4
                    w-4
                    rounded-full
                    bg-blue-500
                "
            />

            <div>

                <p
                    class="
                        font-semibold
                    "
                >

                    Draft Created

                </p>

                <p
                    class="
                        text-sm
                        text-gray-500
                    "
                >

                    {{
                        goodsReceipt.created_at
                    }}

                </p>

                <p
                    class="
                        text-sm
                    "
                >

                    By

                    {{

                        goodsReceipt
                        .creator
                        ?.name

                        ?? '-'

                    }}

                </p>

            </div>

        </div>

        <!-- Posted -->

        <div

            v-if="
                goodsReceipt.posted_at
            "

            class="
                flex
                items-start
                gap-4
            "

        >

            <div
                class="
                    h-4
                    w-4
                    rounded-full
                    bg-green-500
                "
            />

            <div>

                <p
                    class="
                        font-semibold
                    "
                >

                    Goods Receipt Posted

                </p>

                <p
                    class="
                        text-sm
                        text-gray-500
                    "
                >

                    {{
                        goodsReceipt.posted_at
                    }}

                </p>

                <p
                    class="
                        text-sm
                    "
                >

                    By

                    {{

                        goodsReceipt
                        .poster
                        ?.name

                        ?? '-'

                    }}

                </p>

            </div>

        </div>

        <!-- Cancelled -->

        <div

            v-if="
                goodsReceipt.cancelled_at
            "

            class="
                flex
                items-start
                gap-4
            "

        >

            <div
                class="
                    h-4
                    w-4
                    rounded-full
                    bg-red-500
                "
            />

            <div>

                <p
                    class="
                        font-semibold
                    "
                >

                    Goods Receipt Cancelled

                </p>

                <p
                    class="
                        text-sm
                        text-gray-500
                    "
                >

                    {{
                        goodsReceipt.cancelled_at
                    }}

                </p>

                <p
                    class="
                        text-sm
                    "
                >

                    By

                    {{

                        goodsReceipt
                        .canceller
                        ?.name

                        ?? '-'

                    }}

                </p>

                <p

                    v-if="
                        goodsReceipt
                        .cancel_reason
                    "

                    class="
                        mt-2
                        rounded-lg
                        bg-red-50
                        p-3
                        text-sm
                        text-red-700
                    "

                >

                    Reason:

                    {{
                        goodsReceipt
                        .cancel_reason
                    }}

                </p>

            </div>

        </div>

    </div>

</div>
        </div>

    </AuthenticatedLayout>
<div

    v-if="
        showCancelModal
    "

    class="
        fixed
        inset-0
        z-50
        flex
        items-center
        justify-center
        bg-black/50
    "

>

    <div
        class="
            w-full
            max-w-lg
            rounded-xl
            bg-white
            p-6
            shadow-xl
        "
    >

        <h2
            class="
                mb-4
                text-lg
                font-bold
            "
        >

            Cancel Goods Receipt

        </h2>

        <p
            class="
                mb-4
                text-sm
                text-gray-500
            "
        >

            Alasan pembatalan wajib diisi.

        </p>

        <textarea

            v-model="
                cancelReason
            "

            rows="4"

            class="
                w-full
                rounded-lg
                border
            "

        />

        <div
            class="
                mt-6
                flex
                justify-end
                gap-2
            "
        >

            <button

                @click="
                    showCancelModal = false
                "

                class="
                    rounded-lg
                    bg-gray-500
                    px-4
                    py-2
                    text-white
                "

            >

                Close

            </button>

            <button

                @click="
                    submitCancel()
                "

                class="
                    rounded-lg
                    bg-red-600
                    px-4
                    py-2
                    text-white
                "

            >

                Confirm Cancel

            </button>

        </div>

    </div>

</div>
</template>