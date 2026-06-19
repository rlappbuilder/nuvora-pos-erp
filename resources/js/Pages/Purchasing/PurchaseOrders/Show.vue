<script setup>

import {
    Head,
    Link
} from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout
from '@/Layouts/AuthenticatedLayout.vue'

import { ref } from 'vue'

const showRejectModal =
    ref(false)

const showCancelModal =
    ref(false)

const rejectionReason =
    ref('')

const cancelReason =
    ref('')

const props = defineProps({

    purchaseOrder: Object

})
const submitReject = () => {

    router.patch(

        route(
            'purchase-orders.reject',
            props.purchaseOrder.id
        ),

        {

            rejection_reason:
                rejectionReason.value

        },

        {

            onSuccess: () => {

                showRejectModal.value =
                    false

                rejectionReason.value =
                    ''

            }

        }

    )

}

const submitCancel = () => {

    router.patch(

        route(
            'purchase-orders.cancel',
            props.purchaseOrder.id
        ),

        {

            cancel_reason:
                cancelReason.value

        },

        {

            onSuccess: () => {

                showCancelModal.value =
                    false

                cancelReason.value =
                    ''

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

</script>
<template>

    <Head title="Purchase Order Detail" />

    <AuthenticatedLayout>

        <div class="py-6 px-6">

            <!-- Header -->

            <div
                class="mb-6 rounded-xl bg-white p-6 shadow"
            >

                <div
                    class="flex items-center justify-between"
                >

                    <div>

                        <h1
                            class="text-2xl font-bold"
                        >

                            {{ purchaseOrder.po_number }}

                        </h1>

                        <p
                            class="text-sm text-gray-500"
                        >

                            Purchase Order Detail

                        </p>

                    </div>

                    <span
                        class="rounded-full bg-yellow-100 px-4 py-2 text-sm font-semibold text-yellow-700"
                    >

                       {{ purchaseOrder.status }}
                      

                    </span>
                    <!-- Approvel Submit Rijec-->

                     <div
                            class="mt-4 flex gap-2"
                        >

                            <Link

                                v-if="
                                    purchaseOrder.status === 'Draft'
                                "

                                method="patch"

                                as="button"

                                :href="
                                    route(
                                        'purchase-orders.submit',
                                        purchaseOrder.id
                                    )
                                "

                                class="rounded-lg bg-blue-600 px-4 py-2 text-white"

                            >

                                Submit

                            </Link>

                            <Link

                                v-if="
                                    purchaseOrder.status === 'Submitted'
                                "

                                method="patch"

                                as="button"

                                :href="
                                    route(
                                        'purchase-orders.approve',
                                        purchaseOrder.id
                                    )
                                "

                                class="rounded-lg bg-green-600 px-4 py-2 text-white"

                            >

                                Approve

                            </Link>
                        <!-- tombol reject-->
                            <button

                                v-if="
                                    purchaseOrder.status
                                    === 'Submitted'
                                "

                                @click="
                                    showRejectModal = true
                                "

                                class="
                                    rounded-lg
                                    bg-red-600
                                    px-4 py-2
                                    text-white
                                "

                            >

                                Reject

                            </button>
                            <!-- button reopen-->
                           <Link

                                    v-if="
                                        purchaseOrder.status
                                        === 'Rejected'
                                    "

                                    method="patch"

                                    as="button"

                                    :href="
                                        route(
                                            'purchase-orders.reopen',
                                            purchaseOrder.id
                                        )
                                    "

                                 class="rounded-lg bg-blue-600 px-4 py-2 text-white-50"

                                >

                                    Reopen

                                </Link>
                                <!-- cancell PO -->
                             <button

                                v-if="
                                    purchaseOrder.status
                                    === 'Approved'
                                "

                                @click="
                                    showCancelModal = true
                                "

                                class="
                                    rounded-lg
                                    bg-red-600
                                    px-4 py-2
                                    text-white
                                "

                            >

                                Cancel PO

                            </button>
                                <!-- good receipt button-->
                                            
                                <Link

                                    v-if="
                                        purchaseOrder.status
                                        === 'Approved'
                                    "

                                    :href="
                                        route(
                                            'goods-receipts.create-from-po',
                                            purchaseOrder.id
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

                                    Create Goods Receipt

                                </Link>
                        
                        </div>
                    <!-- end approval submit rijeck-->
                </div>

            </div>

            <!-- Information -->

            <div
                class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2"
            >

                <!-- Supplier -->

                <div
                    class="rounded-xl bg-white p-5 shadow"
                >

                    <h3
                        class="mb-3 font-bold"
                    >

                        Supplier Information

                    </h3>

                    <p>

                        {{ purchaseOrder.supplier?.name }}

                    </p>

                    <p>

                        {{ purchaseOrder.supplier?.phone }}

                    </p>

                    <p>

                        {{ purchaseOrder.supplier?.city }}

                    </p>

                </div>

                <!-- Document -->

                <div
                    class="rounded-xl bg-white p-5 shadow"
                >

                    <h3
                        class="mb-3 font-bold"
                    >

                        Document Information

                    </h3>

                    <p>

                        Order Date :

                        {{ purchaseOrder.order_date }}

                    </p>

                    <p>

                        Expected Date :

                        {{ purchaseOrder.expected_date }}

                    </p>

                    <p>

                        Warehouse :

                        {{ purchaseOrder.warehouse?.name }}

                    </p>

                </div>

            </div>
           
                <!-- Workflow History -->

                <div
                    class="mb-6 rounded-xl bg-white p-6 shadow"
                >

                    <h3
                        class="mb-4 text-lg font-bold"
                    >

                        Workflow History

                    </h3>

                    <div
                        class="space-y-3"
                    >

                        <div
                            v-if="
                                purchaseOrder.submitted_at
                            "
                        >

                            <span
                                class="font-semibold"
                            >

                                Submitted

                            </span>

                            <br>

                            By :

                            {{

                               purchaseOrder.submitted_by?.name
                                ||

                                '-'

                            }}

                            <br>

                            At :

                            {{

                                purchaseOrder
                                .submitted_at

                            }}

                        </div>

                        <div
                            v-if="
                                purchaseOrder.approved_at
                            "
                        >

                            <span
                                class="font-semibold text-green-600"
                            >

                                Approved

                            </span>

                            <br>

                            By :

                            {{

                                purchaseOrder
                                .approved_by?.name
                                ||

                                '-'

                            }}

                            <br>

                            At :

                            {{

                                purchaseOrder
                                .approved_at

                            }}

                        </div>

                        <div
                            v-if="
                                purchaseOrder.rejected_at
                            "
                        >

                            <span
                                class="font-semibold text-red-600"
                            >

                                Rejected

                            </span>

                            <br>

                            By :

                            {{

                                purchaseOrder
                                .rejected_by?.name
                                ||

                                '-'

                            }}

                            <br>

                            At :

                            {{

                                purchaseOrder
                                .rejected_at

                            }}
                            <!-- rejected reason-->
                             <br>
                                    <div
                                        v-if="
                                            purchaseOrder.rejection_reason
                                        "
                                    >
                                     <span
                                        class="font-semibold text-red-600"
                                         >
                                        Reason :

                                        {{
                                            purchaseOrder.rejection_reason
                                        }}
                                    </span>
                                    </div>
                             <!-- rejected reason-->
                        </div>
                           
                        <div
                            v-if="
                                purchaseOrder.cancelled_at
                            "
                        >

                            <span
                                class="font-semibold text-orange-600"
                            >

                                Cancelled

                            </span>

                            <br>

                            By :

                            {{

                                purchaseOrder
                                .cancelled_by?.name
                                ||

                                '-'

                            }}

                            <br>

                            At :

                            {{

                                purchaseOrder
                                .cancelled_at

                            }}
                        <!-- cancell reason-->
                            <div
                                v-if="
                                    purchaseOrder.cancel_reason
                                "
                            >
                            <p class="font-semibold text-orange-600"
                            >
                                Reason :

                                {{
                                    purchaseOrder.cancel_reason
                                }}
                            </p>
                            </div>
                        <!-- end cancell reason-->
                        </div>

                    </div>

                </div>

            <!-- end history-->
            <!-- Detail Items -->

            <div
                class="overflow-hidden rounded-xl bg-white shadow"
            >

                <table
                    class="min-w-full"
                >

                    <thead
                        class="bg-gray-100"
                    >

                        <tr>

                            <th class="px-6 py-4 text-left">
                                Product
                            </th>

                            <th class="px-6 py-4 text-left">
                                Qty
                            </th>

                            <th class="px-6 py-4 text-left">
                                Unit Cost
                            </th>

                            <th class="px-6 py-4 text-left">
                                Total
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        <tr

                            v-for="
                                item
                                in purchaseOrder.details
                            "

                            :key="
                                item.id
                            "

                            class="border-t"

                        >

                            <td class="px-6 py-4">

                                {{ item.product?.name }}

                            </td>

                            <td class="px-6 py-4">

                                {{ item.qty }}

                            </td>

                            <td class="px-6 py-4">

                                {{

                                    formatCurrency(

                                        item.unit_cost

                                    )

                                }}

                            </td>

                            <td class="px-6 py-4">

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
                class="mt-6 flex justify-end"
            >

                <div
                    class="w-full rounded-xl bg-white p-6 shadow md:w-96"
                >

                    <div
                        class="flex justify-between"
                    >

                        <span>

                            Subtotal

                        </span>

                        <span>

                            {{

                                formatCurrency(

                                    purchaseOrder.subtotal

                                )

                            }}

                        </span>

                    </div>

                    <div
                        class="mt-4 flex justify-between text-lg font-bold"
                    >

                        <span>

                            Grand Total

                        </span>

                        <span>

                            {{

                                formatCurrency(

                                    purchaseOrder.grand_total

                                )

                            }}

                        </span>

                    </div>

                </div>

            </div>

            <!-- Action -->

            <div
                class="mt-6"
            >

                <Link

                    :href="
                        route(
                            'purchase-orders.index'
                        )
                    "
                class="inline-block rounded-xl bg-red-600 px-5 py-3 text-white"
                 

                >

                    Back

                </Link>

            </div>

        </div>
<!-- modal reject cancell-->
<div

    v-if="
        showRejectModal
    "

    class="
        fixed inset-0
        flex items-center
        justify-center
        bg-black/50
    "

>

    <div
        class="
            w-full max-w-md
            rounded-xl
            bg-white
            p-6
        "
    >

        <h3
            class="
                mb-4
                text-lg
                font-bold
            "
        >

            Reject Purchase Order

        </h3>

        <textarea

            v-model="
                rejectionReason
            "

            rows="4"

            class="
                w-full rounded-lg border
            "

            placeholder="
                Enter rejection reason
            "

        />

        <div
            class="
                mt-4 flex justify-end gap-2
            "
        >

            <button

                @click="
                    showRejectModal = false
                "

                class="
                    rounded-lg
                    border px-4 py-2
                "

            >

                Close

            </button>

            <button

                @click="
                    submitReject
                "

                class="
                    rounded-lg
                    bg-red-600
                    px-4 py-2
                    text-white
                "

            >

                Reject

            </button>

        </div>

    </div>

</div>
<!-- modal cancell-->
 <div

    v-if="
        showCancelModal
    "

    class="
        fixed inset-0
        flex items-center
        justify-center
        bg-black/50
    "

>

    <div
        class="
            w-full max-w-md
            rounded-xl
            bg-white
            p-6
        "
    >

        <h3
            class="
                mb-4
                text-lg
                font-bold
            "
        >

            Cancel Purchase Order

        </h3>

        <textarea

            v-model="
                cancelReason
            "

            rows="4"

            class="
                w-full rounded-lg border
            "

            placeholder="
                Enter cancel reason
            "

        />

        <div
            class="
                mt-4 flex justify-end gap-2
            "
        >

            <button

                @click="
                    showCancelModal = false
                "

                class="
                    rounded-lg
                    border px-4 py-2
                "

            >

                Close

            </button>

            <button

                @click="
                    submitCancel
                "

                class="
                    rounded-lg
                    bg-red-600
                    px-4 py-2
                    text-white
                "

            >

                Confirm Cancel

            </button>

        </div>

    </div>

</div>
    </AuthenticatedLayout>

</template>