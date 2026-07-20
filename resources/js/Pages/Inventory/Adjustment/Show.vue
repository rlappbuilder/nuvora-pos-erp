<script setup>

import { Head } from '@inertiajs/vue3'

import { router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({

    adjustment: Object,

})
const showCancelModal = ref(
    false
)

const cancelReason = ref('')

const postAdjustment = () => {

    if (

        !confirm(

            'Post this adjustment?'

        )

    ) {

        return

    }

    router.post(

        route(

            'inventory-adjustments.post',

            props.adjustment.id

        )

    )

}

const confirmCancel = () => {

    router.post(

        route(

            'inventory-adjustments.cancel',

            props.adjustment.id

        ),

        {

            cancel_reason:

                cancelReason.value

        },

        {

            onSuccess: () => {

                showCancelModal.value = false

                cancelReason.value = ''

            }

        }

    )

}
const cancelAdjustment = () => {

    showCancelModal.value = true

}
const formatDateTime = (date) => {

    if (!date) {

        return '-'

    }

    return new Date(date)

        .toLocaleString(

            'id-ID',

            {

                day: '2-digit',

                month: 'short',

                year: 'numeric',

                hour: '2-digit',

                minute: '2-digit',

            }

        )

}
</script>
<template>


            <Head
                title="Inventory Adjustment"
            />

<AppLayout>

            <template #header>

                    <div>

                        <h2

                            class="
                                text-3xl
                                font-bold
                                text-gray-800
                            "

                        >

                            Inventory Adjustment

                        </h2>

                        <p

                            class="
                                mt-1
                                text-sm
                                text-gray-500
                            "

                        >

                            Adjustment detail.

                        </p>

                    </div>
            </template>
                <!-- status Header card-->
                                <div

                                class="
                                    rounded-3xl
                                    bg-white
                                    p-6
                                    shadow-sm
                                "

                            >

                                <div

                                    class="
                                        flex
                                        items-center
                                        justify-between
                                    "

                                >

                                    <div>

                                        <h3

                                            class="
                                                text-2xl
                                                font-bold
                                            "

                                        >

                                            {{

                                                adjustment.adjustment_number

                                            }}

                                        </h3>

                                        <p

                                            class="
                                                mt-1
                                                text-gray-500
                                            "

                                        >

                                            {{ adjustment.warehouse.name }}

                                        </p>

                                    </div>

                                        <span

                                            class="
                                                rounded-full
                                                px-4
                                                py-2
                                                text-sm
                                                font-semibold
                                            "

                                            :class="

                                                adjustment.status === 'Draft'

                                                ? 'bg-yellow-100 text-yellow-800'

                                                : adjustment.status === 'Posted'

                                                ? 'bg-green-100 text-green-800'

                                                : adjustment.status === 'Cancelled'

                                                ? 'bg-red-100 text-red-800'

                                                : 'bg-gray-100 text-gray-800'

                                            "

                                        >

                                            {{ adjustment.status }}

                                        </span>
                                       

                                </div>
                                    
                            </div>
                            
                    <!-- end status header card-->
                     <!-- summary card-->
                      <div

                            class="
                                mt-6
                                grid
                                gap-6
                                md:grid-cols-4
                            "

                        >

                            <!-- Products -->

                            <div
                                class="
                                    rounded-3xl
                                    bg-white
                                    p-6
                                    shadow-sm
                                "
                            >

                                <p class="text-sm text-gray-500">

                                    Products

                                </p>

                                <h3
                                    class="
                                        mt-2
                                        text-4xl
                                        font-bold
                                        text-blue-600
                                    "
                                >

                                    {{

                                        adjustment.details.length

                                    }}

                                </h3>

                            </div>

                            <!-- Difference -->

                            <div
                                class="
                                    rounded-3xl
                                    bg-white
                                    p-6
                                    shadow-sm
                                "
                            >

                                <p class="text-sm text-gray-500">

                                    Total Difference

                                </p>

                                <h3
                                    class="
                                        mt-2
                                        text-4xl
                                        font-bold
                                        text-orange-500
                                    "
                                >

                                    {{

                                        adjustment.details

                                        .reduce(

                                            (

                                                total,

                                                item

                                            ) =>

                                                total +

                                                Math.abs(

                                                    Number(
                                                        item.difference_qty
                                                    )

                                                ),

                                            0

                                        )

                                    }}

                                </h3>

                            </div>

                            <!-- Value -->
                            <div

                                class="
                                    rounded-3xl
                                    bg-white
                                    p-6
                                    shadow-sm
                                "

                            >

                                <p
                                    class="
                                        text-sm
                                        text-gray-500
                                    "
                                >

                                    Adjusted Value

                                </p>

                                <h3

                                    class="
                                        mt-2
                                        text-2xl
                                        font-bold
                                        
                                        text-blue-500
                                    "

                                >

                                    Rp

                                    {{

                                        adjustment.details

                                        .reduce(

                                            (

                                                total,

                                                item

                                            ) =>

                                                total +

                                                (

                                                    Math.abs(

                                                        Number(
                                                            item.difference_qty
                                                        )

                                                    )

                                                    *

                                                    Number(
                                                        item.unit_cost
                                                    )

                                                ),

                                            0

                                        )

                                        .toLocaleString(
                                            'id-ID'
                                        )

                                    }}

                                </h3>

                            </div>
                            <!-- net impact-->
                           <div

                                    class="
                                        rounded-3xl
                                        bg-white
                                        p-6
                                        shadow-sm
                                    "

                                >

                                    <p

                                        class="
                                            text-sm
                                            text-gray-500
                                        "

                                    >

                                        Net Impact

                                    </p>

                                    <h3

                                        class="
                                         mt-2
                                        whitespace-nowrap
                                        text-2xl
                                        font-bold
                                        "

                                        :class="

                                            adjustment.details

                                            .reduce(

                                                (

                                                    total,

                                                    item

                                                ) =>

                                                    total +

                                                    (

                                                        Number(
                                                            item.difference_qty
                                                        )

                                                        *

                                                        Number(
                                                            item.unit_cost
                                                        )

                                                    ),

                                                0

                                            ) >= 0

                                            ? 'text-green-600'

                                            : 'text-red-600'

                                        "

                                    >

                                        Rp

                                        {{

                                            adjustment.details

                                            .reduce(

                                                (

                                                    total,

                                                    item

                                                ) =>

                                                    total +

                                                    (

                                                        Number(
                                                            item.difference_qty
                                                        )

                                                        *

                                                        Number(
                                                            item.unit_cost
                                                        )

                                                    ),

                                                0

                                            )

                                            .toLocaleString(
                                                'id-ID'
                                            )

                                        }}

                                    </h3>

                                </div>
                            <!-- end net impact-->
                            <!-- end diffrent adjustmen-->

                        </div>
                    <!-- end summary card-->
                     <!-- detail table-->

                            <div

                                class="
                                    mt-6
                                    overflow-hidden
                                    rounded-3xl
                                    bg-white
                                    shadow-sm
                                "

                            >

                                <div

                                    class="
                                        border-b
                                        px-6
                                        py-4
                                    "

                                >

                                    <h3

                                        class="
                                            text-lg
                                            font-semibold
                                        "

                                    >

                                        Adjustment Details

                                    </h3>

                                </div>

                                <div
                                    class="overflow-x-auto"
                                >

                                    <table
                                        class="min-w-full"
                                    >

                                        <thead>

                                            <tr
                                                class="
                                                    bg-gray-50
                                                "
                                            >

                                                <th
                                                    class="
                                                        px-6
                                                        py-4
                                                        text-left
                                                    "
                                                >
                                                    Product
                                                </th>

                                                <th
                                                    class="
                                                        px-6
                                                        py-4
                                                        text-right
                                                    "
                                                >
                                                    System Qty
                                                </th>

                                                <th
                                                    class="
                                                        px-6
                                                        py-4
                                                        text-right
                                                    "
                                                >
                                                    Physical Qty
                                                </th>

                                                <th
                                                    class="
                                                        px-6
                                                        py-4
                                                        text-right
                                                    "
                                                >
                                                    Difference
                                                </th>

                                                <th
                                                    class="
                                                        px-6
                                                        py-4
                                                        text-right
                                                    "
                                                >
                                                    Unit Cost
                                                </th>

                                                <th
                                                    class="
                                                        px-6
                                                        py-4
                                                        text-left
                                                    "
                                                >
                                                    Remarks
                                                </th>

                                            </tr>

                                        </thead>

                                        <tbody>

                                            <tr

                                                v-for="
                                                    row
                                                    in
                                                    adjustment.details
                                                "

                                                :key="
                                                    row.id
                                                "

                                                class="
                                                    border-t
                                                "

                                            >

                                                <!-- Product -->

                                                <td
                                                    class="
                                                        px-6
                                                        py-4
                                                        font-medium
                                                    "
                                                >

                                                    {{ row.product.name }}

                                                </td>

                                                <!-- System -->

                                                <td
                                                    class="
                                                        px-6
                                                        py-4
                                                        text-right
                                                    "
                                                >

                                                    {{ row.system_qty }}

                                                </td>

                                                <!-- Physical -->

                                                <td
                                                    class="
                                                        px-6
                                                        py-4
                                                        text-right
                                                    "
                                                >

                                                    {{ row.physical_qty }}

                                                </td>

                                                <!-- Difference -->

                                                <td
                                                    class="
                                                        px-6
                                                        py-4
                                                        text-right
                                                        font-semibold
                                                    "
                                                >

                                                    <span

                                                        :class="

                                                            row.difference_qty > 0

                                                            ? 'text-green-600'

                                                            : row.difference_qty < 0

                                                            ? 'text-red-600'

                                                            : 'text-gray-500'

                                                        "

                                                    >

                                                        {{ row.difference_qty }}

                                                    </span>

                                                </td>

                                                <!-- Cost -->

                                                <td
                                                    class="
                                                        px-6
                                                        py-4
                                                        text-right
                                                    "
                                                >

                                                    Rp

                                                    {{

                                                        Number(

                                                            row.unit_cost

                                                        )

                                                        .toLocaleString(
                                                            'id-ID'
                                                        )

                                                    }}

                                                </td>

                                                <!-- Remarks -->

                                                <td
                                                    class="
                                                        px-6
                                                        py-4
                                                    "
                                                >

                                                    {{ row.remarks }}

                                                </td>

                                            </tr>

                                        </tbody>

                                    </table>

                                </div>

                            </div>
                                    <!-- Action Buttons -->
                                            <div

                                                class="
                                                    mt-6
                                                    flex
                                                    items-center
                                                    justify-between
                                                "

                                            >

                                                <!-- Back -->

                                                <button

                                                    @click="
                                                        $inertia.visit(
                                                            route(
                                                                'inventory-adjustments.index'
                                                            )
                                                        )
                                                    "

                                                    class="
                                                        rounded-2xl
                                                        border
                                                        px-6
                                                        py-3
                                                        font-medium
                                                    "

                                                >

                                                    Back

                                                </button>

                                                <!-- Right Side -->

                                                <div

                                                    v-if="
                                                        adjustment.status
                                                        === 'Draft'
                                                    "

                                                    class="
                                                        flex
                                                        items-center
                                                        gap-3
                                                    "

                                                >

                                                    <button

                                                        @click="
                                                            cancelAdjustment
                                                        "

                                                        class="
                                                            rounded-2xl
                                                            bg-red-600
                                                            px-6
                                                            py-3
                                                            text-white
                                                        "

                                                    >

                                                        Cancel

                                                    </button>

                                                    <button

                                                        @click="
                                                            postAdjustment
                                                        "

                                                        class="
                                                            rounded-2xl
                                                            bg-green-600
                                                            px-6
                                                            py-3
                                                            text-white
                                                        "

                                                    >

                                                        Post

                                                    </button>

                                                </div>

                                            </div>
                                            <!-- End Action Buttons -->
                            <!-- End Detail Table -->
                           
                             <!-- Audit Trail -->

                                <div

                                    class="
                                        mt-6
                                        rounded-3xl
                                        bg-white
                                        p-6
                                        shadow-sm
                                    "

                                >

                                    <h3

                                        class="
                                            mb-6
                                            text-xl
                                            font-bold
                                        "

                                    >

                                        Audit Trail

                                    </h3>

                                    <div

                                        class="
                                            grid
                                            gap-6
                                            md:grid-cols-2
                                        "

                                    >

                                        <div>

                                            <p class="text-gray-500">

                                                Created By

                                            </p>

                                            <p class="font-semibold">

                                                {{

                                                    adjustment.creator?.name

                                                    ?? '-'

                                                }}

                                            </p>

                                        </div>

                                        <div>

                                            <p class="text-gray-500">

                                                Created At

                                            </p>

                                            <p class="font-semibold">

                                                {{ formatDateTime(
                                                    adjustment.created_at
                                                ) }}

                                            </p>

                                        </div>

                                        <div>

                                            <p class="text-gray-500">

                                                Posted By

                                            </p>

                                            <p class="font-semibold">

                                                {{

                                                    adjustment.poster?.name

                                                    ?? '-'

                                                }}

                                            </p>

                                        </div>

                                        <div>

                                            <p class="text-gray-500">

                                                Posted At

                                            </p>

                                            <p class="font-semibold">

                                               {{ formatDateTime(
                                                    adjustment.posted_at
                                                ) }}

                                            </p>

                                        </div>

                                        <div>

                                            <p class="text-gray-500">

                                                Cancelled By

                                            </p>

                                            <p class="font-semibold">

                                                {{

                                                    adjustment.canceller?.name

                                                    ?? '-'

                                                }}

                                            </p>

                                        </div>

                                        <div>

                                            <p class="text-gray-500">

                                                Cancelled At

                                            </p>

                                            <p class="font-semibold">

                                                {{ formatDateTime(
                                                    adjustment.cancelled_at
                                                ) }}

                                            </p>

                                        </div>

                                    </div>

                                </div>
                            <!-- end audit trail-->
                   <!-- workflow timeline -->

                        <div

                            class="
                                mt-6
                                rounded-3xl
                                bg-white
                                p-8
                                shadow-sm
                            "

                        >

                            <h3

                                class="
                                    mb-8
                                    text-xl
                                    font-bold
                                    text-gray-800
                                "

                            >

                                Workflow Timeline

                            </h3>

                            <!-- Draft -->

                            <div
                                class="
                                    flex
                                    gap-4
                                "
                            >

                                <div
                                    class="
                                        mt-2
                                        h-4
                                        w-4
                                        rounded-full
                                        bg-blue-500
                                    "
                                ></div>

                                <div>

                                    <h4
                                        class="
                                            text-lg
                                            font-semibold
                                        "
                                    >

                                        Draft Created

                                    </h4>

                                    <p
                                        class="
                                            text-sm
                                            text-gray-500
                                        "
                                    >

                                        {{ formatDateTime(
                                            adjustment.created_at
                                        ) }}

                                    </p>

                                    <p
                                        class="
                                            text-sm
                                            font-medium
                                        "
                                    >

                                        By

                                        {{

                                            adjustment.creator?.name

                                            ?? '-'

                                        }}

                                    </p>

                                </div>

                            </div>

                            <!-- Posted -->

                            <div

                                v-if="
                                    adjustment.posted_at
                                "

                                class="
                                    mt-8
                                    flex
                                    gap-4
                                "

                            >

                                <div
                                    class="
                                        mt-2
                                        h-4
                                        w-4
                                        rounded-full
                                        bg-green-500
                                    "
                                ></div>

                                <div>

                                    <h4
                                        class="
                                            text-lg
                                            font-semibold
                                        "
                                    >

                                        Inventory Adjustment Posted

                                    </h4>

                                    <p
                                        class="
                                            text-sm
                                            text-gray-500
                                        "
                                    >
 
                                      {{ formatDateTime(
                                            adjustment.posted_at
                                        ) }}

                                    </p>

                                    <p
                                        class="
                                            text-sm
                                            font-medium
                                        "
                                    >

                                        By

                                        {{

                                            adjustment.poster?.name

                                            ?? '-'

                                        }}

                                    </p>

                                </div>

                            </div>

                            <!-- Cancelled -->

                            <div

                                v-if="
                                    adjustment.cancelled_at
                                "

                                class="
                                    mt-8
                                    flex
                                    gap-4
                                "

                            >

                                <div
                                    class="
                                        mt-2
                                        h-4
                                        w-4
                                        rounded-full
                                        bg-red-500
                                    "
                                ></div>

                                <div>

                                    <h4
                                        class="
                                            text-lg
                                            font-semibold
                                            text-red-600
                                        "
                                    >

                                        Inventory Adjustment Cancelled

                                    </h4>

                                    <p
                                        class="
                                            text-sm
                                            text-gray-500
                                        "
                                    >

                                       {{ formatDateTime(
                                            adjustment.cancelled_at
                                        ) }}

                                    </p>

                                    <p
                                        class="
                                            text-sm
                                            font-medium
                                        "
                                    >

                                        By

                                        {{

                                            adjustment.canceller?.name

                                            ?? '-'

                                        }}

                                    </p>

                                    <div

                                        class="
                                            mt-3
                                            rounded-xl
                                            bg-red-50
                                            p-3
                                        "

                                    >

                                        <p
                                            class="
                                                text-sm
                                                text-red-700
                                            "
                                        >

                                            {{ adjustment.cancel_reason }}

                                        </p>

                                    </div>

                                </div>

                            </div>

                        </div>

                        <!-- End Audit Trail -->
                
                 <!-- modal Cancel Reason-->
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
                                rounded-3xl
                                bg-white
                                p-6
                            "

                        >

                            <h3

                                class="
                                    text-xl
                                    font-bold
                                "

                            >

                                Cancel Adjustment

                            </h3>

                            <p

                                class="
                                    mt-2
                                    text-sm
                                    text-gray-500
                                "

                            >

                                Please provide cancellation reason.

                            </p>

                            <textarea

                                v-model="
                                    cancelReason
                                "

                                rows="4"

                                class="
                                    mt-4
                                    w-full
                                    rounded-xl
                                    border-gray-300
                                "

                            />

                            <div

                                class="
                                    mt-6
                                    flex
                                    justify-end
                                    gap-3
                                "

                            >

                                <button

                                    @click="
                                        showCancelModal = false
                                    "

                                    class="
                                        rounded-xl
                                        border
                                        px-4
                                        py-2
                                    "

                                >

                                    Close

                                </button>

                                <button

                                    @click="
                                        confirmCancel
                                    "

                                    class="
                                        rounded-xl
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
                <!-- end modal cancell reason-->
</AppLayout>

</template>