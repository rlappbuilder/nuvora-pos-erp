<script setup>
import { computed, ref, watch } from 'vue'
import StatusBadge from '@/Components/Display/StatusBadge.vue'
import { formatDate } from '@/Utils'

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },

    returnItem: {
        type: Object,
        default: null,
    },

    processing: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits([
    'close',
    'submit',
    'approve',
    'post',
    'reject',
    'cancel',
])

const showReasonModal = ref(false)
const reasonAction = ref('')
const actionReason = ref('')

const selectedStatus = computed(() =>
    String(props.returnItem?.status ?? 'Draft').toLowerCase()
)

const selectedDetails = computed(() =>
    props.returnItem?.details
    ?? props.returnItem?.return_details
    ?? []
)

const selectedTotalQty = computed(() => {
    if (props.returnItem?.total_qty != null) {
        return Number(props.returnItem.total_qty)
    }

    return selectedDetails.value.reduce(
        (total, detail) => total + Number(detail.qty ?? 0),
        0
    )
})

const selectedAmount = computed(() =>
    Number(
        props.returnItem?.total_cost
        ?? props.returnItem?.total_amount
        ?? props.returnItem?.amount
        ?? 0
    )
)

/*
|--------------------------------------------------------------------------
| Display Helpers
|--------------------------------------------------------------------------
*/

function formatNumber(value) {
    return new Intl.NumberFormat('id-ID', {
        maximumFractionDigits: 2,
    }).format(Number(value ?? 0))
}

function formatAmount(value) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(Number(value ?? 0))
}

function getDocumentNumber(item) {
    return item?.number ?? item?.return_number ?? '-'
}

function getDocumentDate(item) {
    return item?.transaction_date ?? item?.date ?? null
}

function getStatus(item) {
    return String(item?.status ?? 'Draft')
}

function getOriginalTransfer(item) {
    return item?.stock_transfer_header
        ?? item?.stockTransferHeader
        ?? item?.stock_transfer
        ?? item?.stockTransfer
        ?? {}
}

function getOriginalTransferNumber(item) {
    const original = getOriginalTransfer(item)

    return original?.number
        ?? original?.transfer_number
        ?? item?.stock_transfer_number
        ?? '-'
}

function getSourceBranch(item) {
    const original = getOriginalTransfer(item)

    return item?.from_branch?.name
        ?? original?.to_branch?.name
        ?? original?.toBranch?.name
        ?? '-'
}

function getSourceWarehouse(item) {
    const original = getOriginalTransfer(item)

    return item?.from_warehouse?.name
        ?? original?.to_warehouse?.name
        ?? original?.toWarehouse?.name
        ?? '-'
}

function getDestinationBranch(item) {
    const original = getOriginalTransfer(item)

    return item?.to_branch?.name
        ?? original?.from_branch?.name
        ?? original?.fromBranch?.name
        ?? '-'
}

function getDestinationWarehouse(item) {
    const original = getOriginalTransfer(item)

    return item?.to_warehouse?.name
        ?? original?.from_warehouse?.name
        ?? original?.fromWarehouse?.name
        ?? '-'
}

/*
|--------------------------------------------------------------------------
| Workflow Permissions
|--------------------------------------------------------------------------
*/

function canSubmit() {
    return selectedStatus.value === 'draft'
}

function canCancel() {
    return selectedStatus.value === 'draft'
}

function canApprove() {
    return selectedStatus.value === 'submitted'
}

function canReject() {
    return selectedStatus.value === 'submitted'
}

function canPost() {
    return selectedStatus.value === 'approved'
}

/*
|--------------------------------------------------------------------------
| Modal Controls
|--------------------------------------------------------------------------
*/

function close() {
    if (props.processing) return

    showReasonModal.value = false
    reasonAction.value = ''
    actionReason.value = ''

    emit('close')
}

function runAction(action) {
    if (!props.returnItem || props.processing) return

    emit(action, props.returnItem)
}

function confirmReasonAction() {
    if (
        !props.returnItem
        || !reasonAction.value
        || props.processing
    ) {
        return
    }

    const reason = actionReason.value.trim()

    if (!reason) return

    const action = reasonAction.value

    emit(action, {
        ...props.returnItem,
        reason,
        reject_reason: action === 'reject'
            ? reason
            : props.returnItem.reject_reason,
        cancel_reason: action === 'cancel'
            ? reason
            : props.returnItem.cancel_reason,
    })

    showReasonModal.value = false
    reasonAction.value = ''
    actionReason.value = ''
}

function closeReasonModal() {
    if (props.processing) return

    showReasonModal.value = false
    reasonAction.value = ''
    actionReason.value = ''
}

watch(
    () => props.show,
    (visible) => {
        if (!visible) {
            showReasonModal.value = false
            reasonAction.value = ''
            actionReason.value = ''
        }
    }
)
function handleSubmit(item) {
    emit('submit', item)
}
watch(
    () => props.closeDetailOnSubmit,
    (shouldClose) => {
        if (shouldClose) {
            closeDetail()
        }
    }
)
</script>

<template>
    <Teleport to="body">
        <!-- RETURN DETAIL MODAL -->
        <div
            v-if="show && returnItem"
            class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/50 p-3 backdrop-blur-sm sm:p-6"
            @click.self="close"
        >
            <div
                class="flex max-h-[94vh] w-full max-w-5xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl"
                role="dialog"
                aria-modal="true"
                aria-label="Transfer Return Details"
            >
                <!-- MODAL HEADER -->
                <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-4 sm:px-7">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-3">
                            <h2 class="text-lg font-bold text-slate-900">
                                {{ getDocumentNumber(returnItem) }}
                            </h2>

                            <StatusBadge :status="returnItem.status ?? 'Draft'" />
                        </div>

                        <p class="mt-1 text-sm text-slate-500">
                            Transfer Return Details
                        </p>

                        <p class="mt-1 text-xs text-slate-400">
                            Transaction Date:
                            {{ formatDate(getDocumentDate(returnItem)) }}
                        </p>
                    </div>

                    <button
                        type="button"
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 disabled:cursor-not-allowed disabled:opacity-50"
                        aria-label="Close details"
                        :disabled="processing"
                        @click="close"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m6 6 12 12M18 6 6 18"
                            />
                        </svg>
                    </button>
                </div>

                <!-- MODAL BODY -->
                <div class="flex-1 space-y-6 overflow-y-auto p-5 sm:p-7">
                    <!-- SUMMARY -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-medium text-slate-500">
                                Original Transfer
                            </p>

                            <p class="mt-2 break-words text-sm font-semibold text-slate-900">
                                {{ getOriginalTransferNumber(returnItem) }}
                            </p>
                        </div>

                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-medium text-slate-500">
                                Total Quantity
                            </p>

                            <p class="mt-2 text-lg font-bold text-slate-900">
                                {{ formatNumber(selectedTotalQty) }}
                            </p>
                        </div>

                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-medium text-slate-500">
                                Total Return Amount
                            </p>

                            <p class="mt-2 break-words text-lg font-bold text-slate-900">
                                {{ formatAmount(selectedAmount) }}
                            </p>
                        </div>
                    </div>

                    <!-- DOCUMENT INFORMATION -->
                    <section>
                        <h3 class="mb-3 text-sm font-semibold text-slate-900">
                            Document Information
                        </h3>

                        <div class="grid grid-cols-1 gap-4 rounded-xl border border-slate-200 p-4 sm:grid-cols-2">
                            <div>
                                <p class="text-xs text-slate-500">
                                    Return Number
                                </p>

                                <p class="mt-1 break-words text-sm font-medium text-slate-800">
                                    {{ getDocumentNumber(returnItem) }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-500">
                                    Transaction Date
                                </p>

                                <p class="mt-1 text-sm font-medium text-slate-800">
                                    {{ formatDate(getDocumentDate(returnItem)) }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-500">
                                    Original Transfer
                                </p>

                                <p class="mt-1 break-words text-sm font-medium text-slate-800">
                                    {{ getOriginalTransferNumber(returnItem) }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-500">
                                    Status
                                </p>

                                <div class="mt-1">
                                    <StatusBadge :status="returnItem.status ?? 'Draft'" />
                                </div>
                            </div>

                            <div class="sm:col-span-2">
                                <p class="text-xs text-slate-500">
                                    Description
                                </p>

                                <p class="mt-1 whitespace-pre-wrap text-sm text-slate-800">
                                    {{ returnItem.description || '-' }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- RETURN ROUTE -->
                    <section>
                        <h3 class="mb-3 text-sm font-semibold text-slate-900">
                            Return Route
                        </h3>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="rounded-xl border border-slate-200 p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                    Source
                                </p>

                                <p class="mt-2 text-sm font-semibold text-slate-900">
                                    {{ getSourceBranch(returnItem) }}
                                </p>

                                <p class="mt-1 text-sm text-slate-500">
                                    {{ getSourceWarehouse(returnItem) }}
                                </p>
                            </div>

                            <div class="rounded-xl border border-slate-200 p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                    Destination
                                </p>

                                <p class="mt-2 text-sm font-semibold text-slate-900">
                                    {{ getDestinationBranch(returnItem) }}
                                </p>

                                <p class="mt-1 text-sm text-slate-500">
                                    {{ getDestinationWarehouse(returnItem) }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- RETURN ITEMS -->
                    <section>
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <h3 class="text-sm font-semibold text-slate-900">
                                Return Items
                            </h3>

                            <span class="text-xs text-slate-500">
                                {{ selectedDetails.length }} items
                            </span>
                        </div>

                        <div class="overflow-x-auto rounded-xl border border-slate-200">
                            <table class="w-full min-w-[650px] divide-y divide-slate-100">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                            Product
                                        </th>

                                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                            Unit
                                        </th>

                                        <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                            Quantity
                                        </th>

                                        <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                            Unit Cost
                                        </th>

                                        <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                            Total
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-slate-100">
                                    <tr
                                        v-for="(detail, index) in selectedDetails"
                                        :key="detail.id ?? index"
                                    >
                                        <td class="px-4 py-3">
                                            <p class="text-sm font-medium text-slate-800">
                                                {{ detail.variant?.product?.name
                                                    ?? detail.product_variant?.product?.name
                                                    ?? detail.product?.name
                                                    ?? detail.variant?.name
                                                    ?? detail.product_variant?.name
                                                    ?? detail.product_name
                                                    ?? detail.name
                                                    ?? '-'
                                                }}
                                            </p>

                                            <p
                                                v-if="detail.variant?.sku ?? detail.product_variant?.sku ?? detail.sku"
                                                class="mt-1 text-xs text-slate-500"
                                            >
                                                SKU:
                                                {{ detail.variant?.sku ?? detail.product_variant?.sku ?? detail.sku }}
                                            </p>
                                        </td>

                                        <td class="px-4 py-3 text-sm text-slate-600">
                                            {{ detail.unit?.name ?? detail.unit_name ?? '-' }}
                                        </td>

                                        <td class="px-4 py-3 text-right text-sm tabular-nums text-slate-800">
                                            {{ formatNumber(detail.qty) }}
                                        </td>

                                        <td class="px-4 py-3 text-right text-sm tabular-nums text-slate-600">
                                            {{ formatAmount(detail.unit_cost) }}
                                        </td>

                                        <td class="px-4 py-3 text-right text-sm font-semibold tabular-nums text-slate-900">
                                            {{ formatAmount(
                                                detail.total_cost
                                                ?? Number(detail.qty ?? 0) * Number(detail.unit_cost ?? 0)
                                            ) }}
                                        </td>
                                    </tr>

                                    <tr v-if="!selectedDetails.length">
                                        <td
                                            colspan="5"
                                            class="px-4 py-10 text-center text-sm text-slate-500"
                                        >
                                            No return item details available.
                                        </td>
                                    </tr>
                                </tbody>

                                <tfoot class="bg-slate-50">
                                    <tr>
                                        <td
                                            colspan="2"
                                            class="px-4 py-3 text-sm font-semibold text-slate-700"
                                        >
                                            Total
                                        </td>

                                        <td class="px-4 py-3 text-right text-sm font-semibold tabular-nums text-slate-900">
                                            {{ formatNumber(selectedTotalQty) }}
                                        </td>

                                        <td class="px-4 py-3" />

                                        <td class="px-4 py-3 text-right text-sm font-bold tabular-nums text-slate-900">
                                            {{ formatAmount(selectedAmount) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </section>

                    <!-- WORKFLOW INFORMATION -->
                    <section>
                        <h3 class="mb-3 text-sm font-semibold text-slate-900">
                            Workflow Information
                        </h3>

                        <div class="space-y-3 rounded-xl border border-slate-200 p-4">
                            <div class="flex items-start gap-3">
                                <span
                                    class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full"
                                    :class="{
                                        'bg-slate-400': selectedStatus === 'draft',
                                        'bg-amber-500': selectedStatus === 'submitted',
                                        'bg-blue-500': selectedStatus === 'approved',
                                        'bg-emerald-500': selectedStatus === 'posted',
                                        'bg-red-500': selectedStatus === 'rejected',
                                        'bg-slate-400': selectedStatus === 'cancelled',
                                    }"
                                />

                                <div>
                                    <p class="text-sm font-medium text-slate-800">
                                        Current Status: {{ getStatus(returnItem) }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        The available actions depend on the current document status.
                                    </p>
                                </div>
                            </div>

                            <div
                                v-if="returnItem.reject_reason"
                                class="rounded-lg border border-red-100 bg-red-50 p-3"
                            >
                                <p class="text-xs font-semibold text-red-700">
                                    Rejection Reason
                                </p>

                                <p class="mt-1 whitespace-pre-wrap text-sm text-red-800">
                                    {{ returnItem.reject_reason }}
                                </p>
                            </div>

                            <div
                                v-if="returnItem.cancel_reason"
                                class="rounded-lg border border-slate-200 bg-slate-50 p-3"
                            >
                                <p class="text-xs font-semibold text-slate-700">
                                    Cancellation Reason
                                </p>

                                <p class="mt-1 whitespace-pre-wrap text-sm text-slate-600">
                                    {{ returnItem.cancel_reason }}
                                </p>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- MODAL FOOTER -->
                <div class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-7">
                    <button
                        type="button"
                        class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 disabled:opacity-50"
                        :disabled="processing"
                        @click="close"
                    >
                        Close
                    </button>

                    <div class="flex flex-wrap items-center justify-end gap-2">
                        <button
                            v-if="canCancel()"
                            type="button"
                            class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 disabled:opacity-50"
                            :disabled="processing"
                            @click="runAction('cancel')"
                        >
                            Cancel
                        </button>

                        <button
                            v-if="canReject()"
                            type="button"
                            class="rounded-xl border border-red-200 bg-white px-4 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-50 disabled:opacity-50"
                            :disabled="processing"
                            @click="runAction('reject')"
                        >
                            Reject
                        </button>

                        <button
                            v-if="canSubmit()"
                            type="button"
                            class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:opacity-50"
                            :disabled="processing"
                            @click="runAction('submit')"
                        >
                            Submit
                        </button>

                        <button
                            v-if="canApprove()"
                            type="button"
                            class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:opacity-50"
                            :disabled="processing"
                            @click="runAction('approve')"
                        >
                            Approve
                        </button>

                        <button
                            v-if="canPost()"
                            type="button"
                            class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-50"
                            :disabled="processing"
                            @click="runAction('post')"
                        >
                            Post
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- REJECT / CANCEL REASON MODAL -->
        <div
            v-if="show && showReasonModal"
            class="fixed inset-0 z-[110] flex items-center justify-center bg-slate-950/50 p-4"
            @click.self="closeReasonModal"
        >
            <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
                <h3 class="text-lg font-semibold text-slate-900">
                    {{ reasonAction === 'reject' ? 'Reject Return' : 'Cancel Return' }}
                </h3>

                <p class="mt-1 text-sm text-slate-500">
                    {{ reasonAction === 'reject'
                        ? 'Enter the reason for rejecting this return document.'
                        : 'Enter the reason for cancelling this return document.'
                    }}
                </p>

                <textarea
                    v-model="actionReason"
                    rows="4"
                    maxlength="500"
                    class="mt-4 w-full rounded-xl border border-slate-200 px-3 py-3 text-sm text-slate-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/10"
                    :placeholder="reasonAction === 'reject'
                        ? 'Rejection reason...'
                        : 'Cancellation reason...'"
                    :disabled="processing"
                />

                <p class="mt-1 text-right text-xs text-slate-400">
                    {{ actionReason.length }}/500
                </p>

                <div class="mt-5 flex justify-end gap-2">
                    <button
                        type="button"
                        class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50 disabled:opacity-50"
                        :disabled="processing"
                        @click="closeReasonModal"
                    >
                        Back
                    </button>

                    <button
                        type="button"
                        class="rounded-xl px-4 py-2.5 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-50"
                        :class="reasonAction === 'reject'
                            ? 'bg-red-600 hover:bg-red-700'
                            : 'bg-slate-700 hover:bg-slate-800'"
                        :disabled="!actionReason.trim() || processing"
                        @click="confirmReasonAction"
                    >
                        Confirm {{ reasonAction === 'reject' ? 'Reject' : 'Cancel' }}
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>