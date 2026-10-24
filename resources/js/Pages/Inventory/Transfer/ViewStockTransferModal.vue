<script setup>
import {
    computed,
    ref,
    watch,
} from 'vue'

import WorkflowTimeline
    from '@/Components/Workflow/WorkflowTimeline.vue'

import BaseModal
    from '@/Components/Modal/BaseModal.vue'

import BaseButton
    from '@/Components/Button/BaseButton.vue'

import StatusBadge
    from '@/Components/Display/StatusBadge.vue'

import AuditTrail
    from '@/Components/Workflow/AuditTrail.vue'


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


/*
|--------------------------------------------------------------------------
| Emits
|--------------------------------------------------------------------------
*/

const emit = defineEmits([
    'close',
    'edit',
    'submit',
    'approve',
    'reject',
    'post',
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
    (show) => {
        if (show) {
            activeTab.value = 'overview'
        }
    }
)


/*
|--------------------------------------------------------------------------
| Computed
|--------------------------------------------------------------------------
*/

const status = computed(() => {
    return String(
        props.transfer?.status ?? ''
    ).toLowerCase()
})

const documentNumber = computed(() => {
    return (
        props.transfer?.number
        ?? props.transfer?.transfer_number
        ?? props.transfer?.code
        ?? '-'
    )
})

const transactionDate = computed(() => {
    return (
        props.transfer?.transaction_date
        ?? props.transfer?.date
        ?? null
    )
})

const fromBranch = computed(() => {
    return (
        props.transfer?.from_branch?.name
        ?? props.transfer?.fromBranch?.name
        ?? '-'
    )
})

const toBranch = computed(() => {
    return (
        props.transfer?.to_branch?.name
        ?? props.transfer?.toBranch?.name
        ?? '-'
    )
})

const fromWarehouse = computed(() => {
    return (
        props.transfer?.from_warehouse?.name
        ?? props.transfer?.fromWarehouse?.name
        ?? '-'
    )
})

const toWarehouse = computed(() => {
    return (
        props.transfer?.to_warehouse?.name
        ?? props.transfer?.toWarehouse?.name
        ?? '-'
    )
})

const details = computed(() => {
    return Array.isArray(props.transfer?.details)
        ? props.transfer.details
        : []
})

const activities = computed(() => {
    return props.transfer?.activities ?? []
})

const totalQuantity = computed(() => {
    return details.value.reduce((total, detail) => {
        return total + Number(detail.qty ?? 0)
    }, 0)
})

const totalCost = computed(() => {
    return details.value.reduce((total, detail) => {
        return total + Number(detail.total_cost ?? 0)
    }, 0)
})


/*
|--------------------------------------------------------------------------
| Workflow Permissions
|--------------------------------------------------------------------------
*/

const canEdit = computed(() => {
    return status.value === 'draft'
})

const canSubmit = computed(() => {
    return status.value === 'draft'
})

const canCancel = computed(() => {
    return status.value === 'draft'
})

const canReject = computed(() => {
    return status.value === 'submitted'
})

const canApprove = computed(() => {
    return status.value === 'submitted'
})

const canPost = computed(() => {
    return status.value === 'approved'
})


/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function formatCurrency(value) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(Number(value ?? 0))
}

function formatNumber(value) {
    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(Number(value ?? 0))
}

function formatDate(value) {
    if (!value) {
        return '-'
    }

    const date = new Date(value)

    if (Number.isNaN(date.getTime())) {
        return '-'
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(date)
}

function formatDateTime(value) {
    if (!value) {
        return '-'
    }

    const date = new Date(value)

    if (Number.isNaN(date.getTime())) {
        return '-'
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date)
}

function setTab(tab) {
    activeTab.value = tab
}

function emitAction(action) {
    emit(action, props.transfer)
}
</script>


<template>
    <BaseModal
        :show="show"
        title="Stock Transfer"
        size="2xl"
        @close="emit('close')"
    >
        <div class="flex min-h-0 flex-col">

            <!-- Document Header -->

            <div
                class="
                    flex flex-col gap-4
                    border-b border-slate-200 pb-5
                    sm:flex-row sm:items-start
                    sm:justify-between
                "
            >
                <div class="min-w-0">
                    <div
                        class="
                            text-[11px] font-semibold
                            uppercase tracking-[0.14em]
                            text-blue-600
                        "
                    >
                        Inventory Transfer
                    </div>

                    <div
                        class="
                            mt-1.5 flex flex-wrap
                            items-center gap-3
                        "
                    >
                        <h2
                            class="
                                text-lg font-bold
                                tracking-tight text-slate-900
                            "
                        >
                            {{ documentNumber }}
                        </h2>

                        <StatusBadge :status="status" />
                    </div>

                    <div class="mt-1.5 text-xs text-slate-500">
                        Transaction Date

                        <span
                            class="
                                ml-1 font-semibold
                                text-slate-700
                            "
                        >
                            {{ formatDate(transactionDate) }}
                        </span>
                    </div>
                </div>

                <div
                    class="
                        shrink-0 rounded-xl
                        border border-blue-100
                        bg-blue-50/60 px-5 py-3.5
                        sm:min-w-[210px] sm:text-right
                    "
                >
                    <div
                        class="
                            text-[10px] font-semibold
                            uppercase tracking-[0.14em]
                            text-blue-500
                        "
                    >
                        Total Transfer Cost
                    </div>

                    <div
                        class="
                            mt-1 text-xl font-bold
                            tabular-nums text-blue-700
                        "
                    >
                        {{ formatCurrency(totalCost) }}
                    </div>
                </div>
            </div>


            <!-- Tabs -->

            <div class="mt-5 border-b border-slate-200">
                <nav class="-mb-px flex gap-6 overflow-x-auto">
                    <button
                        v-for="tab in [
                            { key: 'overview', label: 'Overview' },
                            { key: 'workflow', label: 'Workflow' },
                            { key: 'audit', label: 'Audit Timeline' },
                        ]"
                        :key="tab.key"
                        type="button"
                        class="
                            whitespace-nowrap border-b-2
                            px-1 pb-3 text-sm font-semibold
                            transition
                        "
                        :class="
                            activeTab === tab.key
                                ? 'border-blue-600 text-blue-700'
                                : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700'
                        "
                        @click="setTab(tab.key)"
                    >
                        {{ tab.label }}
                    </button>
                </nav>
            </div>


            <!-- Content -->

            <div class="mt-5 min-h-[300px]">

                <!-- Loading -->

                <div
                    v-if="loading"
                    class="
                        flex min-h-[300px]
                        items-center justify-center
                    "
                >
                    <div
                        class="
                            flex flex-col items-center
                            gap-3 text-slate-500
                        "
                    >
                        <svg
                            class="h-7 w-7 animate-spin text-blue-600"
                            viewBox="0 0 24 24"
                            fill="none"
                        >
                            <circle
                                cx="12"
                                cy="12"
                                r="9"
                                stroke="currentColor"
                                stroke-width="3"
                                opacity=".2"
                            />

                            <path
                                d="M21 12a9 9 0 0 0-9-9"
                                stroke="currentColor"
                                stroke-width="3"
                            />
                        </svg>

                        <span class="text-sm">
                            Loading stock transfer...
                        </span>
                    </div>
                </div>


                <!-- No Data -->

                <div
                    v-else-if="!transfer"
                    class="
                        flex min-h-[300px]
                        items-center justify-center
                        text-sm text-slate-500
                    "
                >
                    Stock transfer data is not available.
                </div>


                <!-- Overview -->

                <div
                    v-else-if="activeTab === 'overview'"
                    class="space-y-6"
                >

                    <!-- Transfer Information -->

                    <section>
                        <div class="mb-4">
                            <div class="flex items-center gap-2">
                                <span
                                    class="
                                        h-5 w-1 rounded-full
                                        bg-blue-600
                                    "
                                />

                                <h3
                                    class="
                                        text-sm font-bold
                                        text-slate-900
                                    "
                                >
                                    Transfer Information
                                </h3>
                            </div>

                            <p
                                class="
                                    mt-1 pl-3 text-xs
                                    text-slate-500
                                "
                            >
                                Document and transaction information.
                            </p>
                        </div>

                        <div
                            class="
                                overflow-hidden rounded-xl
                                border border-slate-200 bg-white
                            "
                        >
                            <div class="grid grid-cols-1 sm:grid-cols-2">

                                <div
                                    class="
                                        border-b border-slate-100
                                        px-4 py-3.5 sm:border-r
                                    "
                                >
                                    <div
                                        class="
                                            text-[10px] font-semibold
                                            uppercase tracking-[0.12em]
                                            text-slate-400
                                        "
                                    >
                                        Transfer Number
                                    </div>

                                    <div
                                        class="
                                            mt-1 text-sm font-bold
                                            text-blue-700
                                        "
                                    >
                                        {{ documentNumber }}
                                    </div>
                                </div>

                                <div
                                    class="
                                        border-b border-slate-100
                                        px-4 py-3.5
                                    "
                                >
                                    <div
                                        class="
                                            text-[10px] font-semibold
                                            uppercase tracking-[0.12em]
                                            text-slate-400
                                        "
                                    >
                                        Transaction Date
                                    </div>

                                    <div
                                        class="
                                            mt-1 text-sm font-medium
                                            text-slate-700
                                        "
                                    >
                                        {{ formatDate(transactionDate) }}
                                    </div>
                                </div>

                                <div
                                    class="
                                        border-b border-slate-100
                                        px-4 py-3.5 sm:border-r
                                    "
                                >
                                    <div
                                        class="
                                            text-[10px] font-semibold
                                            uppercase tracking-[0.12em]
                                            text-slate-400
                                        "
                                    >
                                        Created At
                                    </div>

                                    <div
                                        class="
                                            mt-1 text-sm font-medium
                                            text-slate-700
                                        "
                                    >
                                        {{
                                            formatDateTime(
                                                transfer.created_at
                                            )
                                        }}
                                    </div>
                                </div>

                                <div
                                    class="
                                        border-b border-slate-100
                                        px-4 py-3.5
                                    "
                                >
                                    <div
                                        class="
                                            text-[10px] font-semibold
                                            uppercase tracking-[0.12em]
                                            text-slate-400
                                        "
                                    >
                                        Status
                                    </div>

                                    <div class="mt-1">
                                        <StatusBadge :status="status" />
                                    </div>
                                </div>

                                <div class="px-4 py-3.5 sm:col-span-2">
                                    <div
                                        class="
                                            text-[10px] font-semibold
                                            uppercase tracking-[0.12em]
                                            text-slate-400
                                        "
                                    >
                                        Description
                                    </div>

                                    <div
                                        class="
                                            mt-1 whitespace-pre-line
                                            text-sm leading-6
                                            text-slate-600
                                        "
                                    >
                                        {{ transfer.description ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>


                    <!-- Transfer Locations -->

                    <section>
                        <div class="mb-4">
                            <div class="flex items-center gap-2">
                                <span
                                    class="
                                        h-5 w-1 rounded-full
                                        bg-blue-600
                                    "
                                />

                                <h3
                                    class="
                                        text-sm font-bold
                                        text-slate-900
                                    "
                                >
                                    Transfer Locations
                                </h3>
                            </div>

                            <p
                                class="
                                    mt-1 pl-3 text-xs
                                    text-slate-500
                                "
                            >
                                Source and destination inventory locations.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                            <!-- Source -->

                            <div
                                class="
                                    overflow-hidden rounded-xl
                                    border border-slate-200 bg-white
                                "
                            >
                                <div
                                    class="
                                        border-b border-slate-200
                                        bg-slate-50 px-4 py-3
                                    "
                                >
                                    <div
                                        class="
                                            text-[10px] font-semibold
                                            uppercase tracking-[0.12em]
                                            text-slate-400
                                        "
                                    >
                                        Source
                                    </div>

                                    <div
                                        class="
                                            mt-1 text-sm font-bold
                                            text-slate-900
                                        "
                                    >
                                        From Location
                                    </div>
                                </div>

                                <div class="space-y-4 px-4 py-4">
                                    <div>
                                        <div
                                            class="
                                                text-[10px] font-semibold
                                                uppercase tracking-[0.12em]
                                                text-slate-400
                                            "
                                        >
                                            Branch
                                        </div>

                                        <div
                                            class="
                                                mt-1 text-sm font-semibold
                                                text-slate-800
                                            "
                                        >
                                            {{ fromBranch }}
                                        </div>
                                    </div>

                                    <div>
                                        <div
                                            class="
                                                text-[10px] font-semibold
                                                uppercase tracking-[0.12em]
                                                text-slate-400
                                            "
                                        >
                                            Warehouse
                                        </div>

                                        <div
                                            class="
                                                mt-1 text-sm font-semibold
                                                text-slate-800
                                            "
                                        >
                                            {{ fromWarehouse }}
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <!-- Destination -->

                            <div
                                class="
                                    overflow-hidden rounded-xl
                                    border border-slate-200 bg-white
                                "
                            >
                                <div
                                    class="
                                        border-b border-slate-200
                                        bg-slate-50 px-4 py-3
                                    "
                                >
                                    <div
                                        class="
                                            text-[10px] font-semibold
                                            uppercase tracking-[0.12em]
                                            text-slate-400
                                        "
                                    >
                                        Destination
                                    </div>

                                    <div
                                        class="
                                            mt-1 text-sm font-bold
                                            text-slate-900
                                        "
                                    >
                                        To Location
                                    </div>
                                </div>

                                <div class="space-y-4 px-4 py-4">
                                    <div>
                                        <div
                                            class="
                                                text-[10px] font-semibold
                                                uppercase tracking-[0.12em]
                                                text-slate-400
                                            "
                                        >
                                            Branch
                                        </div>

                                        <div
                                            class="
                                                mt-1 text-sm font-semibold
                                                text-slate-800
                                            "
                                        >
                                            {{ toBranch }}
                                        </div>
                                    </div>

                                    <div>
                                        <div
                                            class="
                                                text-[10px] font-semibold
                                                uppercase tracking-[0.12em]
                                                text-slate-400
                                            "
                                        >
                                            Warehouse
                                        </div>

                                        <div
                                            class="
                                                mt-1 text-sm font-semibold
                                                text-slate-800
                                            "
                                        >
                                            {{ toWarehouse }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>


                    <!-- Transfer Details -->

                    <section>
                        <div class="mb-4">
                            <div class="flex items-center gap-2">
                                <span
                                    class="
                                        h-5 w-1 rounded-full
                                        bg-blue-600
                                    "
                                />

                                <h3
                                    class="
                                        text-sm font-bold
                                        text-slate-900
                                    "
                                >
                                    Transfer Details
                                </h3>
                            </div>

                            <p
                                class="
                                    mt-1 pl-3 text-xs
                                    text-slate-500
                                "
                            >
                                Products and quantities included in this transfer.
                            </p>
                        </div>

                        <div
                            class="
                                overflow-hidden rounded-xl
                                border border-slate-200 bg-white
                            "
                        >
                            <div class="overflow-x-auto">
                                <table class="w-full table-fixed min-w-0 box-border">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th
                                                class="
                                                    px-4 py-3 text-left
                                                    text-[10px] font-semibold
                                                    uppercase tracking-[0.12em]
                                                    text-slate-500
                                                "
                                            >
                                                No
                                            </th>

                                            <th
                                                class="
                                                    px-4 py-3 text-left
                                                    text-[10px] font-semibold
                                                    uppercase tracking-[0.12em]
                                                    text-slate-500
                                                "
                                            >
                                                Product
                                            </th>

                                            <th
                                                class="
                                                    px-4 py-3 text-left
                                                    text-[10px] font-semibold
                                                    uppercase tracking-[0.12em]
                                                    text-slate-500
                                                "
                                            >
                                                SKU
                                            </th>

                                            <th
                                                class="
                                                    px-4 py-3 text-left
                                                    text-[10px] font-semibold
                                                    uppercase tracking-[0.12em]
                                                    text-slate-500
                                                "
                                            >
                                                Unit
                                            </th>

                                            <th
                                                class="
                                                    px-4 py-3 text-right
                                                    text-[10px] font-semibold
                                                    uppercase tracking-[0.12em]
                                                    text-slate-500
                                                "
                                            >
                                                Quantity
                                            </th>

                                            <th
                                                class="
                                                    px-4 py-3 text-right
                                                    text-[10px] font-semibold
                                                    uppercase tracking-[0.12em]
                                                    text-slate-500
                                                "
                                            >
                                                Unit Cost
                                            </th>

                                            <th
                                                class="
                                                    px-4 py-3 text-right
                                                    text-[10px] font-semibold
                                                    uppercase tracking-[0.12em]
                                                    text-slate-500
                                                "
                                            >
                                                Total Cost
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody class="divide-y divide-slate-100">
                                        <tr
                                            v-for="(detail, index) in details"
                                            :key="detail.id ?? index"
                                            class="hover:bg-slate-50/70"
                                        >
                                            <td
                                                class="
                                                    px-4 py-3 text-sm
                                                    text-slate-500
                                                "
                                            >
                                                {{ index + 1 }}
                                            </td>

                                            <td class="px-4 py-3">
                                                <div
                                                    class="
                                                        text-sm font-semibold
                                                        text-slate-800
                                                    "
                                                >
                                                    {{
                                                        detail.variant?.product?.name
                                                        ?? detail.product?.name
                                                        ?? detail.variant?.name
                                                        ?? '-'
                                                    }}
                                                </div>

                                                <div
                                                    v-if="detail.description"
                                                    class="
                                                        mt-1 text-xs
                                                        text-slate-500
                                                    "
                                                >
                                                    {{ detail.description }}
                                                </div>
                                            </td>

                                            <td
                                                class="
                                                    whitespace-nowrap px-4 py-3
                                                    text-sm text-slate-600
                                                "
                                            >
                                                {{
                                                    detail.variant?.sku
                                                    ?? detail.product_variant?.sku
                                                    ?? '-'
                                                }}
                                            </td>

                                            <td
                                                class="
                                                    whitespace-nowrap px-4 py-3
                                                    text-sm text-slate-600
                                                "
                                            >
                                                {{
                                                    detail.unit?.name
                                                    ?? '-'
                                                }}
                                            </td>

                                            <td
                                                class="
                                                    whitespace-nowrap px-4 py-3
                                                    text-right text-sm
                                                    font-semibold tabular-nums
                                                    text-slate-700
                                                "
                                            >
                                                {{ formatNumber(detail.qty) }}
                                            </td>

                                            <td
                                                class="
                                                    whitespace-nowrap px-4 py-3
                                                    text-right text-sm
                                                    tabular-nums text-slate-600
                                                "
                                            >
                                                {{ formatCurrency(detail.unit_cost) }}
                                            </td>

                                            <td
                                                class="
                                                    whitespace-nowrap px-4 py-3
                                                    text-right text-sm
                                                    font-semibold tabular-nums
                                                    text-slate-700
                                                "
                                            >
                                                {{ formatCurrency(detail.total_cost) }}
                                            </td>
                                        </tr>

                                        <tr v-if="details.length === 0">
                                            <td
                                                colspan="7"
                                                class="
                                                    px-4 py-10 text-center
                                                    text-sm text-slate-500
                                                "
                                            >
                                                No transfer details available.
                                            </td>
                                        </tr>
                                    </tbody>

                                    <tfoot class="bg-slate-50">
                                        <tr>
                                            <td
                                                colspan="4"
                                                class="
                                                    px-4 py-3 text-right
                                                    text-xs font-bold
                                                    uppercase tracking-wide
                                                    text-slate-600
                                                "
                                            >
                                                Total
                                            </td>

                                            <td
                                                class="
                                                    whitespace-nowrap px-4 py-3
                                                    text-right text-sm font-bold
                                                    tabular-nums text-slate-800
                                                "
                                            >
                                                {{ formatNumber(totalQuantity) }}
                                            </td>

                                            <td class="px-4 py-3" />

                                            <td
                                                class="
                                                    whitespace-nowrap px-4 py-3
                                                    text-right text-sm font-bold
                                                    tabular-nums text-blue-700
                                                "
                                            >
                                                {{ formatCurrency(totalCost) }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </section>


                    <!-- Rejection Reason -->

                    <section
                        v-if="
                            status === 'rejected' &&
                            (
                                transfer.rejected_reason ||
                                transfer.rejection_reason
                            )
                        "
                        class="
                            rounded-xl border border-red-200
                            bg-red-50 px-4 py-4
                        "
                    >
                        <div class="text-xs font-bold text-red-700">
                            Rejection Reason
                        </div>

                        <div
                            class="
                                mt-2 whitespace-pre-line
                                text-sm leading-6 text-red-800
                            "
                        >
                            {{
                                transfer.rejected_reason
                                ?? transfer.rejection_reason
                            }}
                        </div>
                    </section>


                    <!-- Cancellation Reason -->

                    <section
                        v-if="
                            status === 'cancelled' &&
                            transfer.cancelled_reason
                        "
                        class="
                            rounded-xl border border-orange-200
                            bg-orange-50 px-4 py-4
                        "
                    >
                        <div class="text-xs font-bold text-orange-700">
                            Cancellation Reason
                        </div>

                        <div
                            class="
                                mt-2 whitespace-pre-line
                                text-sm leading-6 text-orange-800
                            "
                        >
                            {{ transfer.cancelled_reason }}
                        </div>
                    </section>
                </div>


                <!-- Workflow -->

                <div
                    v-else-if="activeTab === 'workflow'"
                    class="space-y-5"
                >
                    <div>
                        <div class="flex items-center gap-2">
                            <span
                                class="
                                    h-5 w-1 rounded-full
                                    bg-blue-600
                                "
                            />

                            <h3
                                class="
                                    text-sm font-bold
                                    text-slate-900
                                "
                            >
                                Workflow Timeline
                            </h3>
                        </div>

                        <p class="mt-1 pl-3 text-xs text-slate-500">
                            Stock transfer status progression and activities.
                        </p>
                    </div>

                    <div
                        class="
                            rounded-xl border border-slate-200
                            bg-white p-4
                        "
                    >
                        <WorkflowTimeline :activities="activities" />
                    </div>
                </div>


                <!-- Audit Timeline -->

                <div
                    v-else-if="activeTab === 'audit'"
                    class="space-y-5"
                >
                    <div>
                        <div class="flex items-center gap-2">
                            <span
                                class="
                                    h-5 w-1 rounded-full
                                    bg-blue-600
                                "
                            />

                            <h3
                                class="
                                    text-sm font-bold
                                    text-slate-900
                                "
                            >
                                Audit Timeline
                            </h3>
                        </div>

                        <p class="mt-1 pl-3 text-xs text-slate-500">
                            Activity history for this stock transfer.
                        </p>
                    </div>

                    <div
                        class="
                            rounded-xl border border-slate-200
                            bg-white p-4
                        "
                    >
                        <AuditTrail :activities="activities" />
                    </div>
                </div>
            </div>
        </div>


        <!-- Footer -->

        <template #footer>
            <div
                class="
                    flex w-full flex-col-reverse gap-3
                    sm:flex-row sm:items-center
                    sm:justify-between
                "
            >
                <div
                    class="
                        text-[11px] font-medium
                        tracking-wide text-slate-400
                    "
                >
                    {{ documentNumber }}
                </div>

                <div class="flex flex-wrap justify-end gap-2">

                    <BaseButton
                        v-if="canEdit"
                        variant="secondary"
                        :disabled="processing"
                        @click="emitAction('edit')"
                    >
                        Edit
                    </BaseButton>

                    <BaseButton
                        v-if="canSubmit"
                        variant="primary"
                        :disabled="processing"
                        @click="emitAction('submit')"
                    >
                        Submit
                    </BaseButton>

                    <BaseButton
                        v-if="canCancel"
                        variant="secondary"
                        :disabled="processing"
                        @click="emitAction('cancel')"
                    >
                        Cancel
                    </BaseButton>

                    <BaseButton
                        v-if="canReject"
                        variant="danger"
                        :disabled="processing"
                        @click="emitAction('reject')"
                    >
                        Reject
                    </BaseButton>

                    <BaseButton
                        v-if="canApprove"
                        variant="primary"
                        :disabled="processing"
                        @click="emitAction('approve')"
                    >
                        Approve
                    </BaseButton>

                    <BaseButton
                        v-if="canPost"
                        variant="primary"
                        :disabled="processing"
                        @click="emitAction('post')"
                    >
                        Post Transfer
                    </BaseButton>

                    <BaseButton
                        variant="secondary"
                        :disabled="processing"
                        @click="emit('close')"
                    >
                        Close
                    </BaseButton>
                </div>
            </div>
        </template>
    </BaseModal>
</template>