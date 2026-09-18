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

import ConsignmentReceivableCancelModal from './Partials/ConsignmentReceivableCancelModal.vue'
import ConsignmentReceivableRejectModal from './Partials/ConsignmentReceivableRejectModal.vue'
/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    receivable: {

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
| Detail Items
|--------------------------------------------------------------------------
*/

const totalItems = computed(() => {

    return (
        props.receivable?.details?.length
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
        props.receivable?.activities
        ?? []
    )

})

/*
|--------------------------------------------------------------------------
| Status
|--------------------------------------------------------------------------
*/

const status = computed(() => {

    return props.receivable?.status
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
            'consignment-receivables.index'
        )
    )

}


function edit()
{

    router.get(
        route(
            'consignment-receivables.edit',
            props.receivable.id
        )
    )

}


function submit()
{

    router.post(
        route(
            'consignment-receivables.submit',
            props.receivable.id
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
            'consignment-receivables.approve',
            props.receivable.id
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
            'consignment-receivables.post',
            props.receivable.id
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
            'consignment-receivables.print',
            props.receivable.id
        ),

        '_blank'

    )

}
const showCancelModal = ref(false)
const showRejectModal = ref(false)

const cancelLoading = ref(false)
const rejectLoading = ref(false)

const cancelReason = ref('')
const rejectReason = ref('')
function reject()
{
    rejectReason.value = ''
    showRejectModal.value = true
}


function closeReject()
{
    if (rejectLoading.value) return

    showRejectModal.value = false
    rejectReason.value = ''
}


function confirmReject()
{
    if (!rejectReason.value.trim()) return

    rejectLoading.value = true

    router.post(
        route(
            'consignment-receivables.reject',
            props.receivable.id
        ),
        {
            reason: rejectReason.value.trim(),
        },
        {
            preserveScroll: true,

            onSuccess: () => {

                showRejectModal.value = false
                rejectReason.value = ''

            },

            onFinish: () => {

                rejectLoading.value = false

            },
        }
    )
}


function cancel()
{
    cancelReason.value = ''
    showCancelModal.value = true
}


function closeCancel()
{
    if (cancelLoading.value) return

    showCancelModal.value = false
    cancelReason.value = ''
}


function confirmCancel()
{
    if (!cancelReason.value.trim()) return

    cancelLoading.value = true

    router.post(
        route(
            'consignment-receivables.cancel',
            props.receivable.id
        ),
        {
            reason: cancelReason.value.trim(),
        },
        {
            preserveScroll: true,

            onSuccess: () => {

                showCancelModal.value = false
                cancelReason.value = ''

            },

            onFinish: () => {

                cancelLoading.value = false

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
                    Consignment Receivable
                </h1>


                <p
                    class="
                        mt-1
                        text-sm
                        text-gray-500
                    "
                >
                    Receivable payment transaction detail.
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


                    <BaseButton
                        class="
                            !border-red-600
                            !bg-red-600
                            !text-white
                            hover:!bg-red-700
                        "
                        @click="cancel"
                    >
                        Cancel
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

                <!-- Receivable -->

                <div class="px-4 py-4">

                    <div
                        class="
                            text-xs
                            font-medium
                            text-gray-500
                        "
                    >
                        Receivable
                    </div>


                    <div
                        class="
                            mt-1
                            font-semibold
                            text-gray-900
                        "
                    >
                        {{
                            receivable.number
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
                            receivable.payment_date
                                ? formatDate(
                                    receivable.payment_date
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
                            receivable.reseller?.name
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
                            receivable.reseller?.reseller_code
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
                        Branch / Payment Account
                    </div>


                    <div
                        class="
                            mt-1
                            font-semibold
                            text-gray-900
                        "
                    >
                        {{
                            receivable.branch?.name
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
                            receivable.payment_account?.code
                            ?? '-'
                        }}

                        <span
                            v-if="
                                receivable.payment_account?.name
                            "
                        >
                            -
                            {{
                                receivable.payment_account.name
                            }}
                        </span>
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
                                receivable.status
                            "
                        />

                    </div>

                </div>

            </div>

        </div>


        <!-- ========================================================= -->
        <!-- Payment Information -->
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
                    flex
                    flex-col
                    gap-2
                    sm:flex-row
                    sm:items-center
                    sm:justify-between
                "
            >

                <div>

                    <div
                        class="
                            text-xs
                            font-medium
                            text-gray-500
                        "
                    >
                        Payment Method
                    </div>


                    <div
                        class="
                            text-sm
                            font-medium
                            text-gray-900
                        "
                    >
                        {{
                            receivable.payment_method
                            ?? '-'
                        }}
                    </div>

                </div>


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
                        Total Payment
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
                                receivable.total_amount
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
                    Receivable Detail
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
            <!-- Receivable Detail Tab -->
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
                                Receivable Details
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
                            receivable.details?.length
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
                                    width="180px"
                                >
                                    Settlement
                                </DataTableHeaderCell>


                                <DataTableHeaderCell
                                    width="160px"
                                    align="right"
                                >
                                    Settlement Amount
                                </DataTableHeaderCell>


                                <DataTableHeaderCell
                                    width="160px"
                                    align="right"
                                >
                                    Previous Paid
                                </DataTableHeaderCell>


                                <DataTableHeaderCell
                                    width="160px"
                                    align="right"
                                >
                                    Outstanding
                                </DataTableHeaderCell>


                                <DataTableHeaderCell
                                    width="160px"
                                    align="right"
                                >
                                    Payment
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
                                            receivable.details
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


                                    <!-- Settlement -->

                                    <DataTableCell>

                                        <div
                                            class="
                                                font-medium
                                                text-gray-900
                                            "
                                        >
                                            {{
                                                detail.settlement?.settlement_number
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
                                                detail.settlement?.settlement_date
                                                    ? formatDate(
                                                        detail.settlement.settlement_date
                                                    )
                                                    : '-'
                                            }}
                                        </div>

                                    </DataTableCell>


                                    <!-- Settlement Amount -->

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
                                                    detail.settlement_amount
                                                )
                                            }}
                                        </span>

                                    </DataTableCell>


                                    <!-- Previous Paid -->

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
                                                    detail.previous_paid_amount
                                                )
                                            }}
                                        </span>

                                    </DataTableCell>


                                    <!-- Outstanding -->

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
                                            Rp
                                            {{
                                                formatAmount(
                                                    detail.previous_outstanding_amount
                                                )
                                            }}
                                        </span>

                                    </DataTableCell>


                                    <!-- Payment -->

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
                                                    detail.payment_amount
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
                        No receivable details.
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
                                Total Payment
                            </span>

                            <span class="tabular-nums">
                                Rp
                                {{
                                    formatAmount(
                                        receivable.total_amount
                                    )
                                }}
                            </span>

                        </div>

                    </div>

                </div>


                <!-- Remarks -->

                <div
                    v-if="
                        receivable.remarks
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
                        {{ receivable.remarks }}
                    </div>

                </div>

            </div>


            <!-- ========================================================= -->
            <!-- Workflow Timeline Tab -->
            <!-- ========================================================= -->

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


            <!-- ========================================================= -->
            <!-- Audit Trail Tab -->
            <!-- ========================================================= -->

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

</AppLayout>
<ConsignmentReceivableRejectModal
    :show="showRejectModal"
    :loading="rejectLoading"
    :reason="rejectReason"
    @close="closeReject"
    @confirm="confirmReject"
    @update:reason="rejectReason = $event"
/>


<ConsignmentReceivableCancelModal
    :show="showCancelModal"
    :loading="cancelLoading"
    :reason="cancelReason"
    @close="closeCancel"
    @confirm="confirmCancel"
    @update:reason="cancelReason = $event"
/>
</template>