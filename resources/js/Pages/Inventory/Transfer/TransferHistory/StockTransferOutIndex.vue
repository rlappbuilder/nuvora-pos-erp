<script setup>
import { computed, ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'

import TablePagination from '@/Components/Table/TablePagination.vue'
import ActionDropdown from '@/Components/Action/ActionDropdown.vue'
import StatusBadge from '@/Components/Display/StatusBadge.vue'
import ReportActionButtons from '@/Components/Action/ReportActionButtons.vue'

import { formatDate } from '@/Utils'

const props = defineProps({
    transfers: {
        type: Object,
        default: () => ({
            data: [],
            links: [],
            current_page: 1,
            last_page: 1,
            per_page: 10,
            total: 0,
        }),
    },

    statistics: {
        type: Object,
        default: () => ({}),
    },

    filters: {
        type: Object,
        default: () => ({}),
    },
})

const emit = defineEmits(['view'])

const loading = ref(false)

const search = ref('')
const status = ref('all')
const dateFrom = ref('')
const dateTo = ref('')

const rows = computed(() => props.transfers?.data ?? [])

const stats = computed(() => ({
    total: props.statistics?.total ?? 0,
    draft: props.statistics?.draft ?? 0,
    submitted: props.statistics?.submitted ?? 0,
    approved: props.statistics?.approved ?? 0,
    posted: props.statistics?.posted ?? 0,
    rejected: props.statistics?.rejected ?? 0,
    cancelled: props.statistics?.cancelled ?? 0,
    totalAmount: props.statistics?.totalAmount ?? 0,
    totalQty: props.statistics?.totalQty ?? 0,
}))

const statusOptions = [
    { label: 'All Statuses', value: 'all' },
    { label: 'Draft', value: 'draft' },
    { label: 'Submitted', value: 'submitted' },
    { label: 'Approved', value: 'approved' },
    { label: 'Posted', value: 'posted' },
    { label: 'Rejected', value: 'rejected' },
    { label: 'Cancelled', value: 'cancelled' },
]

watch(
    () => props.filters,
    (filters) => {
        search.value =
            filters?.history_search
            ?? filters?.search
            ?? ''

        status.value =
            filters?.history_status
            ?? filters?.status
            ?? 'all'

        dateFrom.value =
            filters?.history_date_from
            ?? filters?.date_from
            ?? ''

        dateTo.value =
            filters?.history_date_to
            ?? filters?.date_to
            ?? ''
    },
    {
        immediate: true,
        deep: true,
    }
)

watch(
    () => props.transfers,
    () => {
        loading.value = false
    }
)

function formatAmount(value) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(Number(value ?? 0))
}

function getDestinationBranch(transfer) {
    return (
        transfer.to_branch?.name
        ?? transfer.toBranch?.name
        ?? '-'
    )
}

function getItemCount(transfer) {
    return Number(
        transfer.details_count
        ?? transfer.details?.length
        ?? 0
    )
}

function getTotalQty(transfer) {
    return Number(
        transfer.details_sum_qty
        ?? transfer.total_qty
        ?? transfer.totalQty
        ?? 0
    )
}

function getTotalAmount(transfer) {
    return Number(
        transfer.details_sum_total_cost
        ?? transfer.total_amount
        ?? transfer.totalAmount
        ?? transfer.total_cost
        ?? 0
    )
}

function getTransferNumber(transfer) {
    return (
        transfer.transfer_number
        ?? transfer.number
        ?? transfer.code
        ?? '-'
    )
}

function getTransferDate(transfer) {
    return (
        transfer.transaction_date
        ?? transfer.transfer_date
        ?? transfer.date
        ?? null
    )
}

function getStatus(transfer) {
    return String(transfer.status ?? 'draft').toLowerCase()
}

function openView(transfer) {
    emit('view', transfer)
}

function buildQuery() {
    const params = new URLSearchParams()

    const normalizedSearch = search.value.trim()

    if (normalizedSearch) {
        params.set('history_search', normalizedSearch)
    }

    if (status.value && status.value !== 'all') {
        params.set('history_status', status.value)
    }

    if (dateFrom.value) {
        params.set('history_date_from', dateFrom.value)
    }

    if (dateTo.value) {
        params.set('history_date_to', dateTo.value)
    }

    const query = params.toString()

    return query ? `?${query}` : ''
}

function applyFilters() {
    loading.value = true

    router.get(
        window.location.pathname,
        {
            history_search: search.value.trim() || undefined,

            history_status:
                status.value !== 'all'
                    ? status.value
                    : undefined,

            history_date_from: dateFrom.value || undefined,
            history_date_to: dateTo.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onFinish: () => {
                loading.value = false
            },
        }
    )
}

function resetFilters() {
    search.value = ''
    status.value = 'all'
    dateFrom.value = ''
    dateTo.value = ''

    applyFilters()
}

function refreshReport() {
    loading.value = true

    router.reload({
        preserveScroll: true,
        onFinish: () => {
            loading.value = false
        },
    })
}

function printReport() {
    window.open(
        route('stock-transfers.history.out.print') + buildQuery(),
        '_blank'
    )
}

function exportPdf() {
    window.location.href =
        route('stock-transfers.history.out.pdf') + buildQuery()
}

function exportExcel() {
    window.location.href =
        route('stock-transfers.history.out.excel') + buildQuery()
}
</script>

<template>
    <div class="space-y-6">
        <!-- REPORT HEADER -->
        <div
            class="
                flex flex-col gap-4
                sm:flex-row sm:items-start sm:justify-between
            "
        >
            <div>
                <h2 class="text-xl font-semibold text-gray-800">
                    Stock Transfer Out
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    History of stock transfers sent from this branch.
                </p>
            </div>

            <ReportActionButtons
                :loading="loading"
                @refresh="refreshReport"
                @print="printReport"
                @pdf="exportPdf"
                @excel="exportExcel"
            />
        </div>

        <!-- STATISTICS -->
        <div
            class="
                grid grid-cols-1 gap-4
                sm:grid-cols-2 xl:grid-cols-4
            "
        >
            <div class="rounded-xl border border-gray-200 bg-white p-4">
                <p class="text-sm text-gray-500">
                    Total Transfers
                </p>

                <p class="mt-2 text-2xl font-semibold text-gray-800">
                    {{ stats.total }}
                </p>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4">
                <p class="text-sm text-gray-500">
                    Draft
                </p>

                <p class="mt-2 text-2xl font-semibold text-gray-800">
                    {{ stats.draft }}
                </p>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4">
                <p class="text-sm text-gray-500">
                    Posted
                </p>

                <p class="mt-2 text-2xl font-semibold text-green-600">
                    {{ stats.posted }}
                </p>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4">
                <p class="text-sm text-gray-500">
                    Total Quantity
                </p>

                <p class="mt-2 text-2xl font-semibold text-gray-800">
                    {{ stats.totalQty }}
                </p>
            </div>
        </div>

        <!-- FILTER -->
        <div class="rounded-xl border border-gray-200 bg-white p-4">
            <div
                class="
                    grid grid-cols-1 gap-4
                    sm:grid-cols-2 xl:grid-cols-4
                "
            >
                <!-- SEARCH -->
                <div>
                    <label
                        for="transfer-out-search"
                        class="mb-1.5 block text-sm font-medium text-gray-700"
                    >
                        Search
                    </label>

                    <input
                        id="transfer-out-search"
                        v-model="search"
                        type="text"
                        placeholder="Search transfer number..."
                        class="
                            w-full rounded-lg border border-gray-300
                            px-3 py-2 text-sm
                            focus:border-blue-500 focus:outline-none
                            focus:ring-1 focus:ring-blue-500
                        "
                        @keyup.enter="applyFilters"
                    >
                </div>

                <!-- STATUS -->
                <div>
                    <label
                        for="transfer-out-status"
                        class="mb-1.5 block text-sm font-medium text-gray-700"
                    >
                        Status
                    </label>

                    <select
                        id="transfer-out-status"
                        v-model="status"
                        class="
                            w-full rounded-lg border border-gray-300
                            bg-white px-3 py-2 text-sm
                            focus:border-blue-500 focus:outline-none
                            focus:ring-1 focus:ring-blue-500
                        "
                    >
                        <option
                            v-for="option in statusOptions"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </option>
                    </select>
                </div>

                <!-- DATE FROM -->
                <div>
                    <label
                        for="transfer-out-date-from"
                        class="mb-1.5 block text-sm font-medium text-gray-700"
                    >
                        Date From
                    </label>

                    <input
                        id="transfer-out-date-from"
                        v-model="dateFrom"
                        type="date"
                        class="
                            w-full rounded-lg border border-gray-300
                            px-3 py-2 text-sm
                            focus:border-blue-500 focus:outline-none
                            focus:ring-1 focus:ring-blue-500
                        "
                    >
                </div>

                <!-- DATE TO -->
                <div>
                    <label
                        for="transfer-out-date-to"
                        class="mb-1.5 block text-sm font-medium text-gray-700"
                    >
                        Date To
                    </label>

                    <input
                        id="transfer-out-date-to"
                        v-model="dateTo"
                        type="date"
                        class="
                            w-full rounded-lg border border-gray-300
                            px-3 py-2 text-sm
                            focus:border-blue-500 focus:outline-none
                            focus:ring-1 focus:ring-blue-500
                        "
                    >
                </div>
            </div>

            <div class="mt-4 flex flex-wrap justify-end gap-2">
                <button
                    type="button"
                    class="
                        rounded-lg border border-gray-300
                        bg-white px-4 py-2 text-sm font-medium
                        text-gray-700 transition hover:bg-gray-50
                        disabled:cursor-not-allowed disabled:opacity-50
                    "
                    :disabled="loading"
                    @click="resetFilters"
                >
                    Reset
                </button>

                <button
                    type="button"
                    class="
                        rounded-lg bg-blue-600 px-4 py-2
                        text-sm font-medium text-white
                        transition hover:bg-blue-700
                        disabled:cursor-not-allowed disabled:opacity-50
                    "
                    :disabled="loading"
                    @click="applyFilters"
                >
                    Apply Filters
                </button>
            </div>
        </div>

        <!-- TABLE -->
        <div
            class="
                overflow-hidden rounded-xl
                border border-gray-200 bg-white
            "
        >
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="
                                    px-6 py-3 text-left text-xs
                                    font-semibold uppercase tracking-wider
                                    text-gray-500
                                "
                            >
                                Transfer Information
                            </th>

                            <th
                                class="
                                    px-6 py-3 text-left text-xs
                                    font-semibold uppercase tracking-wider
                                    text-gray-500
                                "
                            >
                                Destination Branch
                            </th>

                            <th
                                class="
                                    px-6 py-3 text-left text-xs
                                    font-semibold uppercase tracking-wider
                                    text-gray-500
                                "
                            >
                                Items / Qty
                            </th>

                            <th
                                class="
                                    px-6 py-3 text-right text-xs
                                    font-semibold uppercase tracking-wider
                                    text-gray-500
                                "
                            >
                                Total Amount
                            </th>

                            <th
                                class="
                                    px-6 py-3 text-center text-xs
                                    font-semibold uppercase tracking-wider
                                    text-gray-500
                                "
                            >
                                Status
                            </th>

                            <th
                                class="
                                    px-6 py-3 text-center text-xs
                                    font-semibold uppercase tracking-wider
                                    text-gray-500
                                "
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        <tr
                            v-for="transfer in rows"
                            :key="transfer.id"
                            class="transition hover:bg-gray-50"
                        >
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="font-medium text-gray-800">
                                    {{ getTransferNumber(transfer) }}
                                </div>

                                <div class="mt-1 text-sm text-gray-500">
                                    {{ formatDate(getTransferDate(transfer)) }}
                                </div>
                            </td>

                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="text-sm text-gray-700">
                                    {{ getDestinationBranch(transfer) }}
                                </span>
                            </td>

                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="font-medium text-gray-800">
                                    {{ getItemCount(transfer) }} items
                                </div>

                                <div class="mt-1 text-sm text-gray-500">
                                    Qty: {{ getTotalQty(transfer) }}
                                </div>
                            </td>

                            <td
                                class="
                                    whitespace-nowrap px-6 py-4
                                    text-right font-medium text-gray-800
                                "
                            >
                                {{ formatAmount(getTotalAmount(transfer)) }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-center">
                                <StatusBadge :status="getStatus(transfer)" />
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-center">
                                <ActionDropdown
                                    :item="transfer"
                                    :show-view="true"
                                    :show-edit="false"
                                    :show-delete="false"
                                    @view="openView(transfer)"
                                />
                            </td>
                        </tr>

                        <tr v-if="rows.length === 0">
                            <td
                                colspan="6"
                                class="px-6 py-12 text-center"
                            >
                                <p class="font-medium text-gray-700">
                                    No stock transfers sent
                                </p>

                                <p class="mt-1 text-sm text-gray-500">
                                    Transfer records will appear here when
                                    this branch sends stock to another branch.
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION: ONLY BELOW THE TABLE -->
            <div
                v-if="transfers?.links"
                class="border-t border-gray-200 px-4 py-4"
            >
                <TablePagination :data="transfers" />
            </div>
        </div>
    </div>
</template>