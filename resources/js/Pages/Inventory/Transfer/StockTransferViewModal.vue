<script setup>
import {
    ref,
    computed,
    watch,
} from 'vue'

import BaseModal from '@/Components/Modal/BaseModal.vue'
import BaseButton from '@/Components/Button/BaseButton.vue'
import StatusBadge from '@/Components/Display/StatusBadge.vue'
import WorkflowTimeline from '@/Components/Workflow/WorkflowTimeline.vue'
import AuditTrail from '@/Components/Workflow/AuditTrail.vue'

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },

    transfer: {
        type: Object,
        default: null,
    },

    loading: {
        type: Boolean,
        default: false,
    },

    processing: {
        type: Boolean,
        default: false,
    },

    currentBranchId: {
        type: [Number, String],
        default: null,
    },
})

const emit = defineEmits([
    'close',
    'edit',
    'submit',
    'post',
    'reject',
    'cancel',
    'print',
    'refresh',
])

/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const activeTab = ref('overview')

/*
|--------------------------------------------------------------------------
| Watchers
|--------------------------------------------------------------------------
*/

watch(
    () => props.show,
    (visible) => {
        if (visible) {
            activeTab.value = 'overview'
        }
    },
)

/*
|--------------------------------------------------------------------------
| Computed: Document
|--------------------------------------------------------------------------
*/

const documentNumber = computed(() => {
    return props.transfer?.number || '-'
})

const documentDate = computed(() => {
    return formatDate(
        props.transfer?.transaction_date,
    )
})

const documentStatus = computed(() => {
    return props.transfer?.status || 'Draft'
})

const normalizedStatus = computed(() => {
    return String(documentStatus.value)
        .trim()
        .toLowerCase()
})

const isDraft = computed(() => {
    return normalizedStatus.value === 'draft'
})

const isPosted = computed(() => {
    return normalizedStatus.value === 'posted'
})

const isRejected = computed(() => {
    return normalizedStatus.value === 'rejected'
})

const isCancelled = computed(() => {
    return normalizedStatus.value === 'cancelled'
        || normalizedStatus.value === 'canceled'
})

/*
|--------------------------------------------------------------------------
| Computed: Locations
|--------------------------------------------------------------------------
*/

const fromBranchName = computed(() => {
    return props.transfer?.from_branch?.name
        || props.transfer?.fromBranch?.name
        || '-'
})

const fromWarehouseName = computed(() => {
    return props.transfer?.from_warehouse?.name
        || props.transfer?.fromWarehouse?.name
        || '-'
})

const toBranchName = computed(() => {
    return props.transfer?.to_branch?.name
        || props.transfer?.toBranch?.name
        || '-'
})

const toWarehouseName = computed(() => {
    return props.transfer?.to_warehouse?.name
        || props.transfer?.toWarehouse?.name
        || '-'
})

const fromLocation = computed(() => {
    return `${fromBranchName.value} / ${fromWarehouseName.value}`
})

const toLocation = computed(() => {
    return `${toBranchName.value} / ${toWarehouseName.value}`
})

/*
|--------------------------------------------------------------------------
| Computed: Details
|--------------------------------------------------------------------------
*/

const details = computed(() => {
    return Array.isArray(props.transfer?.details)
        ? props.transfer.details
        : []
})

const totalItems = computed(() => {
    return details.value.length
})

const totalQuantity = computed(() => {
    return details.value.reduce(
        (total, detail) => {
            return total + toNumber(detail.quantity)
        },
        0,
    )
})

const estimatedTotal = computed(() => {
    /*
     * Controller menggunakan:
     * withSum('details', 'total_cost')
     *
     * Eloquent menghasilkan atribut:
     * details_sum_total_cost
     *
     * Fallback ke penjumlahan detail jika atribut
     * agregat tidak tersedia.
     */

    const aggregate =
        props.transfer?.details_sum_total_cost
        ?? props.transfer?.detailsSumTotalCost

    if (
        aggregate !== null
        && aggregate !== undefined
    ) {
        return toNumber(aggregate)
    }

    return details.value.reduce(
        (total, detail) => {
            return total + toNumber(detail.total_cost)
        },
        0,
    )
})

const description = computed(() => {
    return props.transfer?.description || '-'
})

/*
|--------------------------------------------------------------------------
| Computed: User & Workflow Information
|--------------------------------------------------------------------------
*/

const creatorName = computed(() => {
    return getUserName(
        props.transfer?.creator
        || props.transfer?.created_by_user,
    )
})

const updaterName = computed(() => {
    return getUserName(
        props.transfer?.updater
        || props.transfer?.updated_by_user,
    )
})

const posterName = computed(() => {
    return getUserName(
        props.transfer?.poster,
    )
})

const rejectorName = computed(() => {
    return getUserName(
        props.transfer?.rejector,
    )
})

const rejectedReason = computed(() => {
    return props.transfer?.rejected_reason || '-'
})

/*
|--------------------------------------------------------------------------
| Computed: Permissions / Actions
|--------------------------------------------------------------------------
|
| Status-based visibility follows the document workflow.
| Authorization must still be enforced by Laravel.
|--------------------------------------------------------------------------
*/

const canEdit = computed(() => {
    return isDraft.value
        && !props.processing
        && !props.loading
})

const canSubmit = computed(() => {
    return isDraft.value
        && !props.processing
        && !props.loading
})

const canPost = computed(() => {
    return isDraft.value
        && !props.processing
        && !props.loading
})

const canReject = computed(() => {
    return isDraft.value
        && !props.processing
        && !props.loading
})

const canCancel = computed(() => {
    /*
     * Tidak menampilkan Cancel untuk dokumen yang sudah
     * Posted atau Rejected.
     *
     * Aksi Cancel hanya tersedia jika status yang digunakan
     * aplikasi memang mendukung status Cancelled/Canceled.
     */
    return (
        isDraft.value
        || isCancelled.value
    )
        && !props.processing
        && !props.loading
        && false
})

const canPrint = computed(() => {
    return Boolean(props.transfer)
        && !props.loading
})

/*
|--------------------------------------------------------------------------
| Workflow Timeline
|--------------------------------------------------------------------------
*/

const workflowSteps = computed(() => {
    const transfer = props.transfer

    if (!transfer) {
        return []
    }

    const steps = [
        {
            title: 'Created',
            description: creatorName.value !== '-'
                ? `Created by ${creatorName.value}`
                : 'Stock transfer created',
            date: transfer.created_at || null,
            status: 'completed',
            user: transfer.creator || null,
        },
    ]

    if (isPosted.value) {
        steps.push({
            title: 'Posted',
            description: posterName.value !== '-'
                ? `Posted by ${posterName.value}`
                : 'Stock transfer posted',
            date: transfer.posted_at || null,
            status: 'completed',
            user: transfer.poster || null,
        })
    } else if (isRejected.value) {
        steps.push({
            title: 'Rejected',
            description: rejectorName.value !== '-'
                ? `Rejected by ${rejectorName.value}`
                : 'Stock transfer rejected',
            date: transfer.rejected_at || null,
            status: 'rejected',
            user: transfer.rejector || null,
        })
    } else if (isDraft.value) {
        steps.push({
            title: 'Pending',
            description: 'Waiting for the next action',
            date: null,
            status: 'pending',
            user: null,
        })
    }

    return steps
})

/*
|--------------------------------------------------------------------------
| Formatting Helpers
|--------------------------------------------------------------------------
*/

function toNumber(value) {
    const number = Number(value)

    return Number.isFinite(number)
        ? number
        : 0
}

function formatQty(value) {
    return new Intl.NumberFormat(
        'id-ID',
        {
            maximumFractionDigits: 4,
        },
    ).format(toNumber(value))
}

function formatCurrency(value) {
    return new Intl.NumberFormat(
        'id-ID',
        {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 2,
        },
    ).format(toNumber(value))
}

function formatDate(value) {
    if (!value) {
        return '-'
    }

    const date = new Date(value)

    if (Number.isNaN(date.getTime())) {
        return '-'
    }

    return new Intl.DateTimeFormat(
        'id-ID',
        {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        },
    ).format(date)
}

function formatDateTime(value) {
    if (!value) {
        return '-'
    }

    const date = new Date(value)

    if (Number.isNaN(date.getTime())) {
        return '-'
    }

    return new Intl.DateTimeFormat(
        'id-ID',
        {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        },
    ).format(date)
}

function getUserName(user) {
    if (!user) {
        return '-'
    }

    if (typeof user === 'string') {
        return user
    }

    return user.name
        || user.full_name
        || user.username
        || '-'
}

function getProductName(detail) {
    return detail?.variant?.product?.name
        || detail?.variant?.name
        || detail?.product?.name
        || '-'
}

function getVariantName(detail) {
    return detail?.variant?.name
        || detail?.variant?.sku
        || '-'
}

function getSku(detail) {
    return detail?.variant?.sku
        || detail?.sku
        || '-'
}

function getUnitName(detail) {
    return detail?.unit?.name
        || detail?.unit?.symbol
        || '-'
}

function getQuantity(detail) {
    return toNumber(detail?.quantity)
}

function getUnitCost(detail) {
    const quantity = getQuantity(detail)
    const totalCost = toNumber(detail?.total_cost)

    if (quantity <= 0) {
        return 0
    }

    return totalCost / quantity
}

/*
|--------------------------------------------------------------------------
| Actions
|--------------------------------------------------------------------------
*/

function closeModal() {
    if (props.processing) {
        return
    }

    emit('close')
}

function editTransfer() {
    if (!canEdit.value) {
        return
    }

    emit('edit', props.transfer)
}

function submitTransfer() {
    if (!canSubmit.value) {
        return
    }

    emit('submit', props.transfer)
}

function postTransfer() {
    if (!canPost.value) {
        return
    }

    emit('post', props.transfer)
}

function rejectTransfer() {
    if (!canReject.value) {
        return
    }

    emit('reject', props.transfer)
}

function cancelTransfer() {
    if (!canCancel.value) {
        return
    }

    emit('cancel', props.transfer)
}

function printTransfer() {
    if (!canPrint.value) {
        return
    }

    emit('print', props.transfer)
}

function refreshTransfer() {
    if (props.processing || props.loading) {
        return
    }

    emit('refresh', props.transfer)
}
</script>

<template>
    <BaseModal
        :show="show"
        max-width="6xl"
        @close="closeModal"
    >
        <div class="flex max-h-[90vh] flex-col overflow-hidden bg-white">

            <!-- ================================================== -->
            <!-- HEADER -->
            <!-- ================================================== -->

            <div class="border-b border-slate-200 px-6 py-5">
                <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">

                    <div class="flex min-w-0 items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-6 w-6"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M4 7h11m0 0-3-3m3 3-3 3M20 17H9m0 0 3-3m-3 3 3 3"
                                />
                            </svg>
                        </div>

                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-xl font-bold text-slate-900">
                                    Stock Transfer
                                </h2>

                                <StatusBadge
                                    :status="documentStatus"
                                />
                            </div>

                            <p class="mt-1 break-all text-sm font-medium text-slate-600">
                                {{ documentNumber }}
                            </p>

                            <p class="mt-1 text-xs text-slate-500">
                                Transaction Date: {{ documentDate }}
                            </p>
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-2">
                        <BaseButton
                            variant="secondary"
                            :disabled="loading || processing"
                            @click="refreshTransfer"
                        >
                            Refresh
                        </BaseButton>

                        <button
                            type="button"
                            class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                            :disabled="processing"
                            @click="closeModal"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M6 18 18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>

                </div>
            </div>

            <!-- ================================================== -->
            <!-- LOADING -->
            <!-- ================================================== -->

            <div
                v-if="loading"
                class="flex flex-1 items-center justify-center px-6 py-20"
            >
                <div class="text-center">
                    <div class="mx-auto h-8 w-8 animate-spin rounded-full border-4 border-blue-100 border-t-blue-600" />

                    <p class="mt-3 text-sm text-slate-500">
                        Loading stock transfer...
                    </p>
                </div>
            </div>

            <!-- ================================================== -->
            <!-- CONTENT -->
            <!-- ================================================== -->

            <template v-else-if="transfer">
                <!-- TABS -->

                <div class="border-b border-slate-200 px-6">
                    <nav class="-mb-px flex gap-6 overflow-x-auto">
                        <button
                            type="button"
                            class="whitespace-nowrap border-b-2 px-1 py-3 text-sm font-medium transition"
                            :class="activeTab === 'overview'
                                ? 'border-blue-600 text-blue-600'
                                : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700'"
                            @click="activeTab = 'overview'"
                        >
                            Overview
                        </button>

                        <button
                            type="button"
                            class="whitespace-nowrap border-b-2 px-1 py-3 text-sm font-medium transition"
                            :class="activeTab === 'workflow'
                                ? 'border-blue-600 text-blue-600'
                                : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700'"
                            @click="activeTab = 'workflow'"
                        >
                            Workflow
                        </button>

                        <button
                            type="button"
                            class="whitespace-nowrap border-b-2 px-1 py-3 text-sm font-medium transition"
                            :class="activeTab === 'audit'
                                ? 'border-blue-600 text-blue-600'
                                : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700'"
                            @click="activeTab = 'audit'"
                        >
                            Audit Timeline
                        </button>
                    </nav>
                </div>

                <!-- SCROLLABLE BODY -->

                <div class="min-h-0 flex-1 overflow-y-auto px-6 py-6">

                    <!-- ========================================== -->
                    <!-- OVERVIEW -->
                    <!-- ========================================== -->

                    <div
                        v-if="activeTab === 'overview'"
                        class="space-y-6"
                    >
                        <!-- SUMMARY CARDS -->

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div class="rounded-xl border border-slate-200 p-4">
                                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
                                    Total Items
                                </p>

                                <p class="mt-2 text-2xl font-bold text-slate-900">
                                    {{ totalItems }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    Product lines
                                </p>
                            </div>

                            <div class="rounded-xl border border-slate-200 p-4">
                                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
                                    Total Quantity
                                </p>

                                <p class="mt-2 text-2xl font-bold text-slate-900">
                                    {{ formatQty(totalQuantity) }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    Total units transferred
                                </p>
                            </div>

                            <div class="rounded-xl border border-slate-200 p-4">
                                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
                                    Estimated Total Cost
                                </p>

                                <p class="mt-2 break-words text-xl font-bold text-slate-900">
                                    {{ formatCurrency(estimatedTotal) }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    Based on transfer details
                                </p>
                            </div>
                        </div>

                        <!-- TRANSFER LOCATIONS -->

                        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                            <div class="rounded-xl border border-blue-200 bg-blue-50/50 p-5">
                                <div class="flex items-center gap-2">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-100 text-blue-700">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M4 7h11m0 0-3-3m3 3-3 3"
                                            />
                                        </svg>
                                    </div>

                                    <h3 class="text-sm font-semibold text-slate-900">
                                        Source Location
                                    </h3>
                                </div>

                                <dl class="mt-4 space-y-3">
                                    <div>
                                        <dt class="text-xs text-slate-500">
                                            From Branch
                                        </dt>

                                        <dd class="mt-1 font-medium text-slate-900">
                                            {{ fromBranchName }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt class="text-xs text-slate-500">
                                            From Warehouse
                                        </dt>

                                        <dd class="mt-1 font-medium text-slate-900">
                                            {{ fromWarehouseName }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            <div class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-5">
                                <div class="flex items-center gap-2">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M20 17H9m0 0 3-3m-3 3 3 3"
                                            />
                                        </svg>
                                    </div>

                                    <h3 class="text-sm font-semibold text-slate-900">
                                        Destination Location
                                    </h3>
                                </div>

                                <dl class="mt-4 space-y-3">
                                    <div>
                                        <dt class="text-xs text-slate-500">
                                            To Branch
                                        </dt>

                                        <dd class="mt-1 font-medium text-slate-900">
                                            {{ toBranchName }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt class="text-xs text-slate-500">
                                            To Warehouse
                                        </dt>

                                        <dd class="mt-1 font-medium text-slate-900">
                                            {{ toWarehouseName }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- DESCRIPTION -->

                        <div class="rounded-xl border border-slate-200 p-5">
                            <h3 class="text-sm font-semibold text-slate-900">
                                Document Information
                            </h3>

                            <dl class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <dt class="text-xs text-slate-500">
                                        Document Number
                                    </dt>

                                    <dd class="mt-1 break-all text-sm font-medium text-slate-900">
                                        {{ documentNumber }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-xs text-slate-500">
                                        Transaction Date
                                    </dt>

                                    <dd class="mt-1 text-sm font-medium text-slate-900">
                                        {{ documentDate }}
                                    </dd>
                                </div>

                                <div class="sm:col-span-2">
                                    <dt class="text-xs text-slate-500">
                                        Description
                                    </dt>

                                    <dd class="mt-1 whitespace-pre-line text-sm text-slate-700">
                                        {{ description }}
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <!-- PRODUCT DETAILS -->

                        <div class="overflow-hidden rounded-xl border border-slate-200">
                            <div class="flex flex-col justify-between gap-2 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center">
                                <div>
                                    <h3 class="text-sm font-semibold text-slate-900">
                                        Transfer Details
                                    </h3>

                                    <p class="mt-1 text-xs text-slate-500">
                                        Products included in this stock transfer
                                    </p>
                                </div>

                                <span class="text-xs font-medium text-slate-500">
                                    {{ totalItems }} item(s)
                                </span>
                            </div>

                            <div
                                v-if="details.length"
                                class="overflow-x-auto"
                            >
                                <table class="min-w-full divide-y divide-slate-200">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                #
                                            </th>

                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                Product
                                            </th>

                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                SKU
                                            </th>

                                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                Quantity
                                            </th>

                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                Unit
                                            </th>

                                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                Unit Cost
                                            </th>

                                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                Total Cost
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody class="divide-y divide-slate-100 bg-white">
                                        <tr
                                            v-for="(detail, index) in details"
                                            :key="detail.id ?? index"
                                            class="hover:bg-slate-50"
                                        >
                                            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-500">
                                                {{ index + 1 }}
                                            </td>

                                            <td class="px-4 py-3">
                                                <p class="text-sm font-medium text-slate-900">
                                                    {{ getProductName(detail) }}
                                                </p>

                                                <p
                                                    v-if="getVariantName(detail) !== '-'"
                                                    class="mt-1 text-xs text-slate-500"
                                                >
                                                    Variant: {{ getVariantName(detail) }}
                                                </p>
                                            </td>

                                            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">
                                                {{ getSku(detail) }}
                                            </td>

                                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-medium text-slate-900">
                                                {{ formatQty(getQuantity(detail)) }}
                                            </td>

                                            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">
                                                {{ getUnitName(detail) }}
                                            </td>

                                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-slate-600">
                                                {{ formatCurrency(getUnitCost(detail)) }}
                                            </td>

                                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-semibold text-slate-900">
                                                {{ formatCurrency(detail.total_cost) }}
                                            </td>
                                        </tr>
                                    </tbody>

                                    <tfoot class="bg-slate-50">
                                        <tr>
                                            <td
                                                colspan="3"
                                                class="px-4 py-3 text-right text-sm font-semibold text-slate-700"
                                            >
                                                Total
                                            </td>

                                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-bold text-slate-900">
                                                {{ formatQty(totalQuantity) }}
                                            </td>

                                            <td colspan="2" />

                                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-bold text-slate-900">
                                                {{ formatCurrency(estimatedTotal) }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <div
                                v-else
                                class="px-5 py-12 text-center"
                            >
                                <p class="text-sm font-medium text-slate-700">
                                    No transfer details found
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    This document does not contain any product details.
                                </p>
                            </div>
                        </div>

                        <!-- REJECTION INFORMATION -->

                        <div
                            v-if="isRejected"
                            class="rounded-xl border border-red-200 bg-red-50 p-5"
                        >
                            <h3 class="text-sm font-semibold text-red-900">
                                Rejection Information
                            </h3>

                            <dl class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <dt class="text-xs text-red-700">
                                        Rejected By
                                    </dt>

                                    <dd class="mt-1 text-sm font-medium text-red-900">
                                        {{ rejectorName }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-xs text-red-700">
                                        Rejected At
                                    </dt>

                                    <dd class="mt-1 text-sm font-medium text-red-900">
                                        {{ formatDateTime(transfer.rejected_at) }}
                                    </dd>
                                </div>

                                <div class="sm:col-span-2">
                                    <dt class="text-xs text-red-700">
                                        Reason
                                    </dt>

                                    <dd class="mt-1 whitespace-pre-line text-sm text-red-900">
                                        {{ rejectedReason }}
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <!-- POSTING INFORMATION -->

                        <div
                            v-if="isPosted"
                            class="rounded-xl border border-emerald-200 bg-emerald-50 p-5"
                        >
                            <h3 class="text-sm font-semibold text-emerald-900">
                                Posting Information
                            </h3>

                            <dl class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <dt class="text-xs text-emerald-700">
                                        Posted By
                                    </dt>

                                    <dd class="mt-1 text-sm font-medium text-emerald-900">
                                        {{ posterName }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-xs text-emerald-700">
                                        Posted At
                                    </dt>

                                    <dd class="mt-1 text-sm font-medium text-emerald-900">
                                        {{ formatDateTime(transfer.posted_at) }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- ========================================== -->
                    <!-- WORKFLOW -->
                    <!-- ========================================== -->

                    <div
                        v-else-if="activeTab === 'workflow'"
                        class="space-y-6"
                    >
                        <div>
                            <h3 class="text-base font-semibold text-slate-900">
                                Document Workflow
                            </h3>

                            <p class="mt-1 text-sm text-slate-500">
                                Track the progress of this stock transfer document.
                            </p>
                        </div>

                        <div class="rounded-xl border border-slate-200 p-5">
                            <WorkflowTimeline
                                :steps="workflowSteps"
                            />
                        </div>

                        <div class="rounded-xl border border-slate-200 p-5">
                            <h3 class="text-sm font-semibold text-slate-900">
                                Current Status
                            </h3>

                            <div class="mt-3 flex flex-wrap items-center gap-3">
                                <StatusBadge
                                    :status="documentStatus"
                                />

                                <span class="text-sm text-slate-600">
                                    Document: {{ documentNumber }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- ========================================== -->
                    <!-- AUDIT TIMELINE -->
                    <!-- ========================================== -->

                    <div
                        v-else-if="activeTab === 'audit'"
                        class="space-y-4"
                    >
                        <div>
                            <h3 class="text-base font-semibold text-slate-900">
                                Audit Timeline
                            </h3>

                            <p class="mt-1 text-sm text-slate-500">
                                Review the document activity history.
                            </p>
                        </div>

                        <div class="rounded-xl border border-slate-200 p-5">
                            <AuditTrail
                                :activities="transfer.activities || []"
                            />
                        </div>

                        <!-- CREATED / UPDATED SUMMARY -->

                        <div class="rounded-xl border border-slate-200 p-5">
                            <h3 class="text-sm font-semibold text-slate-900">
                                Record Information
                            </h3>

                            <dl class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <dt class="text-xs text-slate-500">
                                        Created By
                                    </dt>

                                    <dd class="mt-1 text-sm font-medium text-slate-900">
                                        {{ creatorName }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-xs text-slate-500">
                                        Created At
                                    </dt>

                                    <dd class="mt-1 text-sm font-medium text-slate-900">
                                        {{ formatDateTime(transfer.created_at) }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-xs text-slate-500">
                                        Last Updated By
                                    </dt>

                                    <dd class="mt-1 text-sm font-medium text-slate-900">
                                        {{ updaterName }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-xs text-slate-500">
                                        Last Updated At
                                    </dt>

                                    <dd class="mt-1 text-sm font-medium text-slate-900">
                                        {{ formatDateTime(transfer.updated_at) }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </template>

            <!-- ================================================== -->
            <!-- EMPTY STATE -->
            <!-- ================================================== -->

            <div
                v-else
                class="flex-1 px-6 py-16 text-center"
            >
                <p class="text-sm font-medium text-slate-700">
                    Stock transfer data is unavailable.
                </p>

                <p class="mt-1 text-xs text-slate-500">
                    Close this window and try opening the document again.
                </p>
            </div>

            <!-- ================================================== -->
            <!-- FOOTER -->
            <!-- ================================================== -->

            <div class="border-t border-slate-200 bg-white px-6 py-4">
                <div class="flex flex-col-reverse justify-between gap-3 sm:flex-row sm:items-center">

                    <!-- LEFT ACTIONS -->

                    <div class="flex flex-wrap items-center gap-2">
                        <BaseButton
                            variant="secondary"
                            :disabled="!canPrint"
                            @click="printTransfer"
                        >
                            Print
                        </BaseButton>

                        <BaseButton
                            v-if="canEdit"
                            variant="secondary"
                            :disabled="processing"
                            @click="editTransfer"
                        >
                            Edit
                        </BaseButton>
                    </div>

                    <!-- RIGHT ACTIONS -->

                    <div class="flex flex-wrap justify-end gap-2">
                        <BaseButton
                            v-if="canSubmit"
                            variant="secondary"
                            :disabled="processing"
                            @click="submitTransfer"
                        >
                            Submit
                        </BaseButton>

                        <BaseButton
                            v-if="canReject"
                            variant="danger"
                            :disabled="processing"
                            @click="rejectTransfer"
                        >
                            Reject
                        </BaseButton>

                        <BaseButton
                            v-if="canPost"
                            variant="primary"
                            :disabled="processing"
                            @click="postTransfer"
                        >
                            {{ processing ? 'Processing...' : 'Post Transfer' }}
                        </BaseButton>

                        <BaseButton
                            variant="secondary"
                            :disabled="processing"
                            @click="closeModal"
                        >
                            Close
                        </BaseButton>
                    </div>
                </div>
            </div>
        </div>
    </BaseModal>
</template>