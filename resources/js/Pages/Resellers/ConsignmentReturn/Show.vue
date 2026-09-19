<script setup>

import {
    computed,
    ref,
} from 'vue'

import {
    router,
} from '@inertiajs/vue3'

import AppLayout from '@/Layouts/AppLayout.vue'

import BaseButton from '@/Components/Button/BaseButton.vue'
import StatusBadge from '@/Components/Display/StatusBadge.vue'

import DataTable from '@/Components/Table/DataTable.vue'
import DataTableHead from '@/Components/Table/DataTableHead.vue'
import DataTableBody from '@/Components/Table/DataTableBody.vue'
import DataTableHeaderCell from '@/Components/Table/DataTableHeaderCell.vue'
import DataTableRow from '@/Components/Table/DataTableRow.vue'
import DataTableCell from '@/Components/Table/DataTableCell.vue'

import WorkflowTimeline
    from '@/Components/Workflow/WorkflowTimeline.vue'

import AuditTrail
    from '@/Components/Workflow/AuditTrail.vue'

import {
    formatDate,
} from '@/Utils'



import ConsignmentReturnRejectModal
    from './Partials/ConsignmentReturnRejectModal.vue'


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    consignmentReturn: {

        type: Object,

        default: () => ({}),

    },

})


/*
|--------------------------------------------------------------------------
| Tabs
|--------------------------------------------------------------------------
*/

const activeTab = ref('detail')


/*
|--------------------------------------------------------------------------
| Currency
|--------------------------------------------------------------------------
*/

const formatAmount = (value) => {

    return new Intl.NumberFormat(

        'id-ID',

        {

            minimumFractionDigits: 0,

            maximumFractionDigits: 0,

        }

    ).format(

        Number(value || 0)

    )

}


/*
|--------------------------------------------------------------------------
| Number
|--------------------------------------------------------------------------
*/

const formatNumber = (value) => {

    return new Intl.NumberFormat(

        'id-ID',

        {

            minimumFractionDigits: 0,

            maximumFractionDigits: 2,

        }

    ).format(

        Number(value || 0)

    )

}


/*
|--------------------------------------------------------------------------
| Detail Items
|--------------------------------------------------------------------------
*/

const totalItems = computed(() => {

    return (
        props.consignmentReturn?.details?.length
        ?? 0
    )

})


/*
|--------------------------------------------------------------------------
| Total Returned
|--------------------------------------------------------------------------
*/

const totalReturned = computed(() => {

    return (
        props.consignmentReturn?.details
            ?.reduce(

                (
                    total,
                    detail
                ) =>

                    total +
                    Number(
                        detail.returned_qty || 0
                    ),

                0

            )
        ?? 0
    )

})


/*
|--------------------------------------------------------------------------
| Total Cost
|--------------------------------------------------------------------------
*/

const totalCost = computed(() => {

    return (
        props.consignmentReturn?.details
            ?.reduce(

                (
                    total,
                    detail
                ) =>

                    total +
                    Number(
                        detail.total_cost || 0
                    ),

                0

            )
        ?? 0
    )

})


/*
|--------------------------------------------------------------------------
| Activities
|--------------------------------------------------------------------------
*/

const activities = computed(() => {

    return (
        props.consignmentReturn?.activities
        ?? []
    )

})


/*
|--------------------------------------------------------------------------
| Status
|--------------------------------------------------------------------------
*/

const status = computed(() => {

    return props.consignmentReturn?.status
        ?? ''

})


/*
|--------------------------------------------------------------------------
| Actions
|--------------------------------------------------------------------------
*/

function back()
{

    router.get(

        route(
            'consignment-returns.index'
        )

    )

}


function edit()
{

    router.get(

        route(
            'consignment-returns.edit',
            props.consignmentReturn.id
        )

    )

}


function submit()
{

    router.post(

        route(
            'consignment-returns.submit',
            props.consignmentReturn.id
        ),

        {},

        {

            preserveScroll: true,

        }

    )

}


function approve()
{

    router.post(

        route(
            'consignment-returns.approve',
            props.consignmentReturn.id
        ),

        {},

        {

            preserveScroll: true,

        }

    )

}


function post()
{

    router.post(

        route(
            'consignment-returns.post',
            props.consignmentReturn.id
        ),

        {},

        {

            preserveScroll: true,

        }

    )

}


function print()
{

    window.open(

        route(
            'consignment-returns.print',
            props.consignmentReturn.id
        ),

        '_blank'

    )

}


/*
|--------------------------------------------------------------------------
| Reject / Cancel Modal
|--------------------------------------------------------------------------
*/



const showRejectModal =
    ref(false)


const rejectLoading =
    ref(false)


const rejectReason =
    ref('')


/*
|--------------------------------------------------------------------------
| Reject
|--------------------------------------------------------------------------
*/

function reject()
{

    rejectReason.value = ''

    showRejectModal.value = true

}


function closeReject()
{

    if (
        rejectLoading.value
    ) {

        return

    }


    showRejectModal.value =
        false

    rejectReason.value =
        ''

}


function confirmReject()
{

    if (
        !rejectReason.value.trim()
    ) {

        return

    }


    rejectLoading.value =
        true


    router.post(

        route(
            'consignment-returns.reject',
            props.consignmentReturn.id
        ),

        {

            reason:
                rejectReason.value.trim(),

        },

        {

            preserveScroll: true,

            onSuccess: () => {

                showRejectModal.value =
                    false

                rejectReason.value =
                    ''

            },

            onFinish: () => {

                rejectLoading.value =
                    false

            },

        }

    )

}


</script>


<template>

<AppLayout>

    <div
        class="space-y-4"
    >

        <!-- ========================================================= -->
        <!-- Header -->
        <!-- ========================================================= -->

        <div
            class="
                flex
                flex-col
                gap-3
                sm:flex-row
                sm:items-center
                sm:justify-between
            "
        >

            <div>

                <h1
                    class="
                        text-xl
                        font-semibold
                        text-gray-900
                    "
                >
                    Consignment Return
                </h1>


                <p
                    class="
                        mt-1
                        text-sm
                        text-gray-500
                    "
                >
                    Consignment return transaction detail.
                </p>

            </div>


            <div
                class="
                    flex
                    flex-wrap
                    items-center
                    gap-2
                "
            >

                <BaseButton
                    variant="secondary"
                    @click="back"
                >
                    Back
                </BaseButton>


                <!-- Draft -->

                <template
                    v-if="
                        status === 'Draft'
                    "
                >

                    <BaseButton
                        variant="secondary"
                        @click="edit"
                    >
                        Edit
                    </BaseButton>


                    <BaseButton
                        class="
                            !border-blue-600
                            !bg-blue-600
                            !text-white
                            hover:!bg-blue-700
                        "
                        @click="submit"
                    >
                        Submit
                    </BaseButton>

                </template>


                <!-- Submitted -->

                <template
                    v-else-if="
                        status === 'Submitted'
                    "
                >

                    <BaseButton
                        class="
                            !border-green-600
                            !bg-green-600
                            !text-white
                            hover:!bg-green-700
                        "
                        @click="approve"
                    >
                        Approve
                    </BaseButton>


                    <BaseButton
                        class="
                            !border-red-600
                            !bg-red-600
                            !text-white
                            hover:!bg-red-700
                        "
                        @click="reject"
                    >
                        Reject
                    </BaseButton>

                </template>


                <!-- Rejected -->

                <template
                    v-else-if="
                        status === 'Rejected'
                    "
                >

                    <BaseButton
                        variant="secondary"
                        @click="edit"
                    >
                        Edit
                    </BaseButton>


                    <BaseButton
                        class="
                            !border-yellow-300
                            !bg-yellow-500
                            !text-white
                            hover:!bg-yellow-600
                        "
                        @click="submit"
                    >
                        Submit
                    </BaseButton>

                </template>


                <!-- Approved -->

                <template
                    v-else-if="
                        status === 'Approved'
                    "
                >

                    <BaseButton
                        class="
                            !border-blue-600
                            !bg-blue-600
                            !text-white
                            hover:!bg-blue-700
                        "
                        @click="post"
                    >
                        Post
                    </BaseButton>
                </template>


                <!-- Posted / Cancelled -->

                <template
                    v-if="
                        status === 'Posted'
                        ||
                        status === 'Cancelled'
                    "
                >

                    <BaseButton
                        @click="print"
                    >
                        Print
                    </BaseButton>

                </template>

            </div>

        </div>


        <!-- ========================================================= -->
        <!-- Summary -->
        <!-- ========================================================= -->

        <div
            class="
                overflow-hidden
                rounded-xl
                border
                border-gray-100
                bg-white
                shadow-sm
            "
        >

            <div
                class="
                    grid
                    grid-cols-1
                    divide-y
                    divide-gray-100
                    sm:grid-cols-2
                    sm:divide-x
                    sm:divide-y-0
                    lg:grid-cols-4
                "
            >

                <!-- Return -->

                <div class="px-4 py-4">

                    <div
                        class="
                            text-xs
                            font-medium
                            text-gray-500
                        "
                    >
                        Return
                    </div>


                    <div
                        class="
                            mt-1
                            font-semibold
                            text-gray-900
                        "
                    >
                        {{
                            consignmentReturn.return_number
                            ?? '-'
                        }}
                    </div>


                    <div
                        class="
                            mt-0.5
                            text-xs
                            text-gray-500
                        "
                    >
                        {{
                            consignmentReturn.return_date
                                ? formatDate(
                                    consignmentReturn.return_date
                                )
                                : '-'
                        }}
                    </div>

                </div>


                <!-- Reseller -->

                <div class="px-4 py-4">

                    <div
                        class="
                            text-xs
                            font-medium
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
                            consignmentReturn.reseller?.name
                            ?? '-'
                        }}
                    </div>


                    <div
                        class="
                            mt-0.5
                            text-xs
                            text-gray-500
                        "
                    >
                        {{
                            consignmentReturn.reseller?.reseller_code
                            ?? '-'
                        }}
                    </div>

                </div>


                <!-- Location -->

                <div class="px-4 py-4">

                    <div
                        class="
                            text-xs
                            font-medium
                            text-gray-500
                        "
                    >
                        Branch / Warehouse
                    </div>


                    <div
                        class="
                            mt-1
                            font-semibold
                            text-gray-900
                        "
                    >
                        {{
                            consignmentReturn.branch?.name
                            ?? '-'
                        }}
                    </div>


                    <div
                        class="
                            mt-0.5
                            text-xs
                            text-gray-500
                        "
                    >
                        {{
                            consignmentReturn.warehouse?.name
                            ?? '-'
                        }}
                    </div>

                </div>


                <!-- Status -->

                <div class="px-4 py-4">

                    <div
                        class="
                            text-xs
                            font-medium
                            text-gray-500
                        "
                    >
                        Status
                    </div>


                    <div class="mt-2">

                        <StatusBadge
                            :status="
                                consignmentReturn.status
                            "
                        />

                    </div>

                </div>

            </div>

        </div>


        <!-- ========================================================= -->
        <!-- Return Information -->
        <!-- ========================================================= -->

        <div
            class="
                rounded-xl
                border
                border-gray-100
                bg-white
                px-4
                py-3
                shadow-sm
            "
        >

            <div
                class="
                    grid
                    grid-cols-1
                    gap-4
                    sm:grid-cols-3
                "
            >

                <!-- Settlement -->

                <div>

                    <div
                        class="
                            text-xs
                            font-medium
                            text-gray-500
                        "
                    >
                        Settlement
                    </div>


                    <div
                        class="
                            text-sm
                            font-medium
                            text-gray-900
                        "
                    >
                        {{
                            consignmentReturn.settlement?.settlement_number
                            ?? '-'
                        }}
                    </div>

                </div>


                <!-- Total Returned -->

                <div>

                    <div
                        class="
                            text-xs
                            font-medium
                            text-gray-500
                        "
                    >
                        Total Returned
                    </div>


                    <div
                        class="
                            text-sm
                            font-semibold
                            tabular-nums
                            text-gray-900
                        "
                    >
                        {{
                            formatNumber(
                                totalReturned
                            )
                        }}
                    </div>

                </div>


                <!-- Total Cost -->

                <div
                    class="
                        text-left
                        sm:text-right
                    "
                >

                    <div
                        class="
                            text-xs
                            font-medium
                            text-gray-500
                        "
                    >
                        Total Cost
                    </div>


                    <div
                        class="
                            text-sm
                            font-semibold
                            tabular-nums
                            text-gray-900
                        "
                    >
                        Rp
                        {{
                            formatAmount(
                                totalCost
                            )
                        }}
                    </div>

                </div>

            </div>

        </div>


        <!-- ========================================================= -->
        <!-- Tabs -->
        <!-- ========================================================= -->

        <div
            class="
                overflow-hidden
                rounded-xl
                border
                border-gray-100
                bg-white
                shadow-sm
            "
        >

            <!-- Tab Navigation -->

            <div
                class="
                    flex
                    overflow-x-auto
                    border-b
                    border-gray-100
                "
            >

                <button
                    type="button"
                    class="
                        whitespace-nowrap
                        border-b-2
                        px-5
                        py-3
                        text-sm
                        font-medium
                        transition
                    "
                    :class="
                        activeTab === 'detail'
                            ? `
                                border-blue-600
                                text-blue-600
                            `
                            : `
                                border-transparent
                                text-gray-500
                                hover:text-gray-700
                            `
                    "
                    @click="
                        activeTab = 'detail'
                    "
                >
                    Return Detail
                </button>


                <button
                    type="button"
                    class="
                        whitespace-nowrap
                        border-b-2
                        px-5
                        py-3
                        text-sm
                        font-medium
                        transition
                    "
                    :class="
                        activeTab === 'workflow'
                            ? `
                                border-blue-600
                                text-blue-600
                            `
                            : `
                                border-transparent
                                text-gray-500
                                hover:text-gray-700
                            `
                    "
                    @click="
                        activeTab = 'workflow'
                    "
                >
                    Workflow Timeline
                </button>


                <button
                    type="button"
                    class="
                        whitespace-nowrap
                        border-b-2
                        px-5
                        py-3
                        text-sm
                        font-medium
                        transition
                    "
                    :class="
                        activeTab === 'audit'
                            ? `
                                border-blue-600
                                text-blue-600
                            `
                            : `
                                border-transparent
                                text-gray-500
                                hover:text-gray-700
                            `
                    "
                    @click="
                        activeTab = 'audit'
                    "
                >
                    Audit Trail
                </button>

            </div>


            <!-- ===================================================== -->
            <!-- Return Detail Tab -->
            <!-- ===================================================== -->

            <div
                v-if="
                    activeTab === 'detail'
                "
                class="space-y-4 p-4"
            >

                <!-- Details -->

                <div
                    class="
                        overflow-hidden
                        rounded-xl
                        border
                        border-gray-100
                        bg-white
                    "
                >

                    <div
                        class="
                            flex
                            items-center
                            justify-between
                            border-b
                            border-gray-100
                            px-4
                            py-3
                        "
                    >

                        <div>

                            <div
                                class="
                                    text-sm
                                    font-semibold
                                    text-gray-900
                                "
                            >
                                Return Details
                            </div>


                            <div
                                class="
                                    mt-0.5
                                    text-xs
                                    text-gray-500
                                "
                            >
                                {{ totalItems }} Items
                            </div>

                        </div>

                    </div>


                    <div
                        v-if="
                            consignmentReturn.details?.length
                        "
                        class="overflow-x-auto"
                    >

                        <DataTable>

                            <DataTableHead>

                                <DataTableHeaderCell
                                    width="50px"
                                    align="center"
                                >
                                    #
                                </DataTableHeaderCell>


                                <DataTableHeaderCell
                                    width="280px"
                                >
                                    Product
                                </DataTableHeaderCell>


                                <DataTableHeaderCell
                                    width="120px"
                                >
                                    Unit
                                </DataTableHeaderCell>


                                <DataTableHeaderCell
                                    width="140px"
                                    align="right"
                                >
                                    Returned Qty
                                </DataTableHeaderCell>


                                <DataTableHeaderCell
                                    width="160px"
                                    align="right"
                                >
                                    Unit Cost
                                </DataTableHeaderCell>


                                <DataTableHeaderCell
                                    width="160px"
                                    align="right"
                                >
                                    Total Cost
                                </DataTableHeaderCell>


                                <DataTableHeaderCell
                                    width="220px"
                                >
                                    Remarks
                                </DataTableHeaderCell>

                            </DataTableHead>


                            <DataTableBody>

                                <DataTableRow
                                    v-for="
                                        (
                                            detail,
                                            index
                                        ) in
                                        (
                                            consignmentReturn.details
                                            ?? []
                                        )
                                    "
                                    :key="
                                        detail.id
                                        ?? index
                                    "
                                >

                                    <!-- Number -->

                                    <DataTableCell
                                        align="center"
                                    >

                                        <span
                                            class="
                                                text-xs
                                                text-gray-500
                                            "
                                        >
                                            {{ index + 1 }}
                                        </span>

                                    </DataTableCell>


                                    <!-- Product -->

                                    <DataTableCell>

                                        <div
                                            class="
                                                font-medium
                                                text-gray-900
                                            "
                                        >
                                            {{
                                                detail.variant?.product?.name
                                                ?? detail.variant?.name
                                                ?? '-'
                                            }}
                                        </div>


                                        <div
                                            class="
                                                mt-0.5
                                                text-xs
                                                text-gray-500
                                            "
                                        >
                                            {{
                                                detail.variant?.sku
                                                ?? '-'
                                            }}
                                        </div>

                                    </DataTableCell>


                                    <!-- Unit -->

                                    <DataTableCell>

                                        <span
                                            class="
                                                text-sm
                                                text-gray-700
                                            "
                                        >
                                            {{
                                                detail.unit?.name
                                                ?? '-'
                                            }}
                                        </span>

                                    </DataTableCell>


                                    <!-- Returned Qty -->

                                    <DataTableCell
                                        align="right"
                                    >

                                        <span
                                            class="
                                                font-medium
                                                tabular-nums
                                                text-gray-900
                                            "
                                        >
                                            {{
                                                formatNumber(
                                                    detail.returned_qty
                                                )
                                            }}
                                        </span>

                                    </DataTableCell>


                                    <!-- Unit Cost -->

                                    <DataTableCell
                                        align="right"
                                    >

                                        <span
                                            class="
                                                tabular-nums
                                                text-gray-700
                                            "
                                        >
                                            Rp
                                            {{
                                                formatAmount(
                                                    detail.unit_cost
                                                )
                                            }}
                                        </span>

                                    </DataTableCell>


                                    <!-- Total Cost -->

                                    <DataTableCell
                                        align="right"
                                    >

                                        <span
                                            class="
                                                font-semibold
                                                tabular-nums
                                                text-gray-900
                                            "
                                        >
                                            Rp
                                            {{
                                                formatAmount(
                                                    detail.total_cost
                                                )
                                            }}
                                        </span>

                                    </DataTableCell>


                                    <!-- Remarks -->

                                    <DataTableCell>

                                        <span
                                            class="
                                                text-sm
                                                text-gray-600
                                            "
                                        >
                                            {{
                                                detail.remarks
                                                ?? '-'
                                            }}
                                        </span>

                                    </DataTableCell>

                                </DataTableRow>

                            </DataTableBody>

                        </DataTable>

                    </div>


                    <div
                        v-else
                        class="
                            px-4
                            py-8
                            text-center
                            text-sm
                            text-gray-500
                        "
                    >
                        No return details.
                    </div>

                </div>


                <!-- Totals -->

                <div
                    class="
                        rounded-xl
                        border
                        border-gray-100
                        bg-white
                    "
                >

                    <div
                        class="
                            ml-auto
                            w-full
                            max-w-md
                            space-y-2
                            px-4
                            py-4
                        "
                    >

                        <div
                            class="
                                flex
                                items-center
                                justify-between
                                text-sm
                                font-semibold
                                text-gray-900
                            "
                        >

                            <span>
                                Total Returned
                            </span>

                            <span class="tabular-nums">
                                {{
                                    formatNumber(
                                        totalReturned
                                    )
                                }}
                            </span>

                        </div>


                        <div
                            class="
                                flex
                                items-center
                                justify-between
                                text-sm
                                font-semibold
                                text-gray-900
                            "
                        >

                            <span>
                                Total Cost
                            </span>

                            <span class="tabular-nums">
                                Rp
                                {{
                                    formatAmount(
                                        totalCost
                                    )
                                }}
                            </span>

                        </div>

                    </div>

                </div>


                <!-- Remarks -->

                <div
                    v-if="
                        consignmentReturn.remarks
                    "
                    class="
                        rounded-xl
                        border
                        border-gray-100
                        bg-white
                        px-4
                        py-3
                    "
                >

                    <div
                        class="
                            text-xs
                            font-medium
                            text-gray-500
                        "
                    >
                        Remarks
                    </div>


                    <div
                        class="
                            mt-1
                            whitespace-pre-line
                            text-sm
                            text-gray-700
                        "
                    >
                        {{ consignmentReturn.remarks }}
                    </div>

                </div>

            </div>


            <!-- ===================================================== -->
            <!-- Workflow Timeline Tab -->
            <!-- ===================================================== -->

            <div
                v-else-if="
                    activeTab === 'workflow'
                "
                class="p-4"
            >

                <WorkflowTimeline
                    :activities="
                        activities
                    "
                />

            </div>


            <!-- ===================================================== -->
            <!-- Audit Trail Tab -->
            <!-- ===================================================== -->

            <div
                v-else-if="
                    activeTab === 'audit'
                "
                class="p-4"
            >

                <AuditTrail
                    :activities="
                        activities
                    "
                />

            </div>

        </div>

    </div>


    <!-- ========================================================= -->
    <!-- Reject Modal -->
    <!-- ========================================================= -->

    <ConsignmentReturnRejectModal
        :show="
            showRejectModal
        "

        :loading="
            rejectLoading
        "

        :reason="
            rejectReason
        "

        @close="
            closeReject
        "

        @confirm="
            confirmReject
        "

        @update:reason="
            rejectReason = $event
        "
    />

</AppLayout>

</template>