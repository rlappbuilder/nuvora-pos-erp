<script setup>
import {
    computed,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from 'vue'

import StatusBadge from '@/Components/Display/StatusBadge.vue'
import DateRangePicker from '@/Components/Form/DateRangePicker.vue'
import ReportActionButtons from '@/Components/Action/ReportActionButtons.vue'
import Swal from 'sweetalert2'

const props = defineProps({
    branches: {
        type: Array,
        default: () => [],
    },
})

/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const loading = ref(false)
const detailLoading = ref(false)

const transfers = ref([])

const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0,
    from: 0,
    to: 0,
    links: [],
})

const summary = ref({
    documents: 0,
    transfer_amount: 0,
    settled_amount: 0,
    outstanding_amount: 0,
})

const filters = ref({
    search: '',
    from_branch_id: '',
    to_branch_id: '',
    date_from: '',
    date_to: '',
    per_page: 10,
})

const selectedTransfer = ref(null)
const showDetailModal = ref(false)

let searchTimeout = null
let requestController = null
let requestSequence = 0

/*
|--------------------------------------------------------------------------
| Branch Options
|--------------------------------------------------------------------------
*/

const branchOptions = computed(() =>
    props.branches.map((branch) => ({
        value: branch.id,
        label: branch.name,
    }))
)

/*
|--------------------------------------------------------------------------
| Formatters
|--------------------------------------------------------------------------
*/

function formatCurrency(value) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(Number(value ?? 0))
}

function formatAmount(value) {
    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 2,
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

    return new Intl.DateTimeFormat('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(date)
}

/*
|--------------------------------------------------------------------------
| Transfer Display
|--------------------------------------------------------------------------
*/

function getBranchName(transfer, direction) {
    const branch = direction === 'from'
        ? transfer.from_branch
        : transfer.to_branch

    return branch?.name ?? 'Company'
}

function getAccountName(transfer, direction) {
    const account = direction === 'from'
        ? transfer.from_account
        : transfer.to_account

    if (!account) {
        return '-'
    }

    return [
        account.code,
        account.name,
    ].filter(Boolean).join(' - ')
}

/*
|--------------------------------------------------------------------------
| Query Parameters
|--------------------------------------------------------------------------
*/

function getQueryParams(page = 1) {
    return {
        search: filters.value.search || '',
        from_branch_id: filters.value.from_branch_id || '',
        to_branch_id: filters.value.to_branch_id || '',
        date_from: filters.value.date_from || '',
        date_to: filters.value.date_to || '',
        per_page: filters.value.per_page,
        page,
    }
}

/*
|--------------------------------------------------------------------------
| Load Allocation Report
|--------------------------------------------------------------------------
*/

async function loadReport(page = 1) {
    if (requestController) {
        requestController.abort()
    }

    const controller = new AbortController()
    requestController = controller

    const currentRequest = ++requestSequence

    loading.value = true

    try {
        const params = new URLSearchParams()

        Object.entries(getQueryParams(page)).forEach(
            ([key, value]) => {
                if (
                    value !== '' &&
                    value !== null &&
                    value !== undefined
                ) {
                    params.set(key, String(value))
                }
            }
        )

        const response = await fetch(
            `${route('fund-transfers.allocation.index')}?${params.toString()}`,
            {
                method: 'GET',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                signal: controller.signal,
            }
        )

        if (!response.ok) {
            throw new Error(
                `Failed to load allocation report (${response.status}).`
            )
        }

        const result = await response.json()

        if (currentRequest !== requestSequence) {
            return
        }

        transfers.value = result.data?.data ?? []

        pagination.value = {
            current_page: result.data?.current_page ?? 1,
            last_page: result.data?.last_page ?? 1,
            per_page: result.data?.per_page ?? 10,
            total: result.data?.total ?? 0,
            from: result.data?.from ?? 0,
            to: result.data?.to ?? 0,
            links: result.data?.links ?? [],
        }

        summary.value = {
            documents: Number(result.summary?.documents ?? 0),
            transfer_amount: Number(
                result.summary?.transfer_amount ?? 0
            ),
            settled_amount: Number(
                result.summary?.settled_amount ?? 0
            ),
            outstanding_amount: Number(
                result.summary?.outstanding_amount ?? 0
            ),
        }
    } catch (exception) {
        if (exception.name !== 'AbortError') {
            console.error(
                'Failed to load allocation report:',
                exception
            )

            Swal.fire({
                icon: 'error',
                title: 'Failed to Load Report',
                text: 'Unable to load the allocation report. Please try again.',
            })
        }
    } finally {
        if (currentRequest === requestSequence) {
            loading.value = false
        }
    }
}

/*
|--------------------------------------------------------------------------
| Search
|--------------------------------------------------------------------------
*/

watch(
    () => filters.value.search,
    () => {
        clearTimeout(searchTimeout)

        searchTimeout = setTimeout(() => {
            loadReport(1)
        }, 400)
    }
)

/*
|--------------------------------------------------------------------------
| Branch Filters
|--------------------------------------------------------------------------
*/

watch(
    [
        () => filters.value.from_branch_id,
        () => filters.value.to_branch_id,
    ],
    () => {
        loadReport(1)
    }
)

/*
|--------------------------------------------------------------------------
| Date Filters
|--------------------------------------------------------------------------
*/

function applyDateRange({ dateFrom, dateTo } = {}) {
    filters.value.date_from = dateFrom ?? ''
    filters.value.date_to = dateTo ?? ''

    loadReport(1)
}

/*
|--------------------------------------------------------------------------
| Reset Filters
|--------------------------------------------------------------------------
*/

function resetFilters() {
    clearTimeout(searchTimeout)

    filters.value = {
        search: '',
        from_branch_id: '',
        to_branch_id: '',
        date_from: '',
        date_to: '',
        per_page: 10,
    }

    loadReport(1)
}

/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/

function changePage(page) {
    if (
        !page ||
        page < 1 ||
        page > pagination.value.last_page ||
        page === pagination.value.current_page
    ) {
        return
    }

    loadReport(page)
}

function changePerPage(event) {
    filters.value.per_page = Number(event.target.value)
    loadReport(1)
}

/*
|--------------------------------------------------------------------------
| Detail
|--------------------------------------------------------------------------
*/

async function openDetail(transfer) {
    selectedTransfer.value = transfer
    showDetailModal.value = true
    detailLoading.value = true

    try {
        const response = await fetch(
            route(
                'fund-transfers.allocation.data',
                transfer.id
            ),
            {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            }
        )

        if (!response.ok) {
            throw new Error(
                `Failed to load allocation detail (${response.status}).`
            )
        }

        const result = await response.json()

        selectedTransfer.value = result.transfer ?? transfer
    } catch (exception) {
        console.error(
            'Failed to load allocation detail:',
            exception
        )

        showDetailModal.value = false

        Swal.fire({
            icon: 'error',
            title: 'Failed to Load Detail',
            text: 'Unable to load transfer details.',
        })
    } finally {
        detailLoading.value = false
    }
}

function closeDetail() {
    showDetailModal.value = false
    selectedTransfer.value = null
}

/*
|--------------------------------------------------------------------------
| Print / PDF / Excel
|--------------------------------------------------------------------------
*/

function exportReport(type) {
    const routeNames = {
        print: 'fund-transfers.allocation.print',
        pdf: 'fund-transfers.allocation.pdf',
        excel: 'fund-transfers.allocation.excel',
    }

    const routeName = routeNames[type]

    if (!routeName) {
        return
    }

    const params = new URLSearchParams()

    Object.entries(getQueryParams()).forEach(
        ([key, value]) => {
            if (
                key !== 'page' &&
                value !== '' &&
                value !== null &&
                value !== undefined
            ) {
                params.set(key, String(value))
            }
        }
    )

    const url = `${route(routeName)}?${params.toString()}`

    if (type === 'print') {
        window.open(url, '_blank', 'noopener,noreferrer')
        return
    }

    window.location.href = url
}

/*
|--------------------------------------------------------------------------
| Lifecycle
|--------------------------------------------------------------------------
*/

onMounted(() => {
    loadReport()
})

onBeforeUnmount(() => {
    clearTimeout(searchTimeout)

    if (requestController) {
        requestController.abort()
    }
})
</script>

<template>
    <div class="space-y-6">
        <!-- HEADER -->
        <section
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h2 class="text-lg font-semibold text-slate-900">
                    Fund Transfer Allocation
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Review posted fund allocations between branches and company-level accounts.
                </p>
            </div>

            <ReportActionButtons
                :loading="loading"
                :disabled="loading"
                @refresh="loadReport(pagination.current_page)"
                @print="exportReport('print')"
                @pdf="exportReport('pdf')"
                @excel="exportReport('excel')"
            />
        </section>

        <!-- SUMMARY -->
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">
                    Allocation Documents
                </p>

                <p class="mt-2 text-2xl font-bold text-slate-900">
                    {{ summary.documents.toLocaleString('id-ID') }}
                </p>
            </div>

            <div class="rounded-2xl border border-blue-200 bg-blue-50 p-5 shadow-sm">
                <p class="text-sm text-blue-700">
                    Total Allocated
                </p>

                <p class="mt-2 text-xl font-bold text-blue-800">
                    {{ formatCurrency(summary.transfer_amount) }}
                </p>
            </div>
        </section>

        <!-- FILTERS -->
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="xl:col-span-2">
                    <label class="mb-1.5 block text-xs font-medium text-slate-600">
                        Search
                    </label>

                    <input
                        v-model="filters.search"
                        type="search"
                        placeholder="Transfer number, description, or branch..."
                        class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/10"
                    />
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-600">
                        From Branch
                    </label>

                    <select
                        v-model="filters.from_branch_id"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none"
                    >
                        <option value="">All Branches</option>

                        <option
                            v-for="branch in branchOptions"
                            :key="branch.value"
                            :value="branch.value"
                        >
                            {{ branch.label }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-slate-600">
                        To Branch
                    </label>

                    <select
                        v-model="filters.to_branch_id"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none"
                    >
                        <option value="">All Branches</option>

                        <option
                            v-for="branch in branchOptions"
                            :key="branch.value"
                            :value="branch.value"
                        >
                            {{ branch.label }}
                        </option>
                    </select>
                </div>

                <div class="md:col-span-2 xl:col-span-3">
                    <DateRangePicker
                        :date-from="filters.date_from"
                        :date-to="filters.date_to"
                        :loading="loading"
                        placeholder="Filter transfer date"
                        @update:date-from="filters.date_from = $event"
                        @update:date-to="filters.date_to = $event"
                        @apply="applyDateRange"
                        @clear="applyDateRange({ dateFrom: '', dateTo: '' })"
                    />
                </div>

                <div class="flex items-end">
                    <button
                        type="button"
                        :disabled="loading"
                        @click="resetFilters"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 disabled:opacity-50"
                    >
                        Reset Filters
                    </button>
                </div>
            </div>
        </section>

        <!-- TABLE -->
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="font-semibold text-slate-900">
                        Allocation Transactions
                    </h3>

                    <p class="mt-1 text-xs text-slate-500">
                        {{ pagination.total.toLocaleString('id-ID') }} records
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <label for="allocation-per-page" class="text-xs text-slate-500">
                        Rows
                    </label>

                    <select
                        id="allocation-per-page"
                        :value="filters.per_page"
                        :disabled="loading"
                        @change="changePerPage"
                        class="rounded-lg border border-slate-200 bg-white px-2 py-2 text-sm"
                    >
                        <option :value="10">10</option>
                        <option :value="25">25</option>
                        <option :value="50">50</option>
                        <option :value="100">100</option>
                    </select>
                </div>
            </div>

            <div class="relative">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1000px] divide-y divide-slate-100">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Transfer Information
                                </th>

                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Transfer Accounts
                                </th>

                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Branch Information
                                </th>

                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Allocation Amount
                                </th>

                                <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Status
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="transfer in transfers"
                                :key="transfer.id"
                                class="transition hover:bg-blue-50/40"
                            >
                                <td class="px-5 py-4">
                                    <button
                                        type="button"
                                        @click="openDetail(transfer)"
                                        class="text-left text-sm font-semibold text-blue-600 hover:text-blue-700 hover:underline"
                                    >
                                        {{ transfer.transfer_number ?? '-' }}
                                    </button>

                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ formatDate(transfer.transfer_date) }}
                                    </p>

                                    <p
                                        v-if="transfer.description"
                                        class="mt-1 max-w-xs truncate text-xs text-slate-500"
                                        :title="transfer.description"
                                    >
                                        {{ transfer.description }}
                                    </p>
                                </td>

                                <td class="px-4 py-4">
                                    <div>
                                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                            From
                                        </p>

                                        <p class="mt-1 text-sm font-medium text-slate-800">
                                            {{ getAccountName(transfer, 'from') }}
                                        </p>
                                    </div>

                                    <div class="my-2 text-xs text-slate-400">
                                        ↓
                                    </div>

                                    <div>
                                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                            To
                                        </p>

                                        <p class="mt-1 text-sm font-medium text-slate-800">
                                            {{ getAccountName(transfer, 'to') }}
                                        </p>
                                    </div>
                                </td>

                                <td class="px-4 py-4">
                                    <p class="text-sm font-medium text-slate-800">
                                        {{ getBranchName(transfer, 'from') }}
                                    </p>

                                    <p class="my-1 text-xs text-slate-400">
                                        ↓
                                    </p>

                                    <p class="text-sm font-medium text-slate-800">
                                        {{ getBranchName(transfer, 'to') }}
                                    </p>
                                </td>

                                <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-semibold tabular-nums text-slate-900">
                                    {{ formatAmount(transfer.amount) }}
                                </td>

                                <td class="px-5 py-4 text-center">
                                    <StatusBadge :status="transfer.status ?? 'Posted'" />
                                </td>
                            </tr>

                            <tr v-if="!loading && transfers.length === 0">
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <div class="text-sm font-semibold text-slate-800">
                                        No allocation transactions found
                                    </div>

                                    <p class="mt-1 text-xs text-slate-500">
                                        Try changing the filters or date range.
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    v-if="loading"
                    class="absolute inset-0 z-10 flex items-center justify-center bg-white/70"
                >
                    <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow">
                        <svg
                            class="h-5 w-5 animate-spin text-blue-600"
                            viewBox="0 0 24 24"
                            fill="none"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            />

                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z"
                            />
                        </svg>

                        <span class="text-sm text-slate-700">
                            Loading report...
                        </span>
                    </div>
                </div>
            </div>

            <!-- PAGINATION -->
            <div class="flex flex-col gap-3 border-t border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs text-slate-500">
                    Showing
                    <span class="font-semibold text-slate-700">
                        {{ pagination.from ?? 0 }}
                    </span>
                    to
                    <span class="font-semibold text-slate-700">
                        {{ pagination.to ?? 0 }}
                    </span>
                    of
                    <span class="font-semibold text-slate-700">
                        {{ pagination.total }}
                    </span>
                    results
                </p>

                <div class="flex flex-wrap items-center gap-1">
                    <button
                        v-for="link in pagination.links"
                        :key="`${link.label}-${link.url}`"
                        type="button"
                        :disabled="!link.url || link.active || loading"
                        @click="link.url && changePage(Number(new URL(link.url).searchParams.get('page')))"
                        class="min-w-9 rounded-lg border px-3 py-2 text-sm transition"
                        :class="{
                            'border-blue-600 bg-blue-600 text-white': link.active,
                            'border-slate-200 bg-white text-slate-600 hover:bg-slate-50': !link.active && link.url,
                            'cursor-not-allowed border-slate-100 bg-slate-50 text-slate-300': !link.url,
                        }"
                    >
                        <span v-html="link.label" />
                    </button>
                </div>
            </div>
        </section>

        <!-- DETAIL MODAL -->
        <Teleport to="body">
            <div
                v-if="showDetailModal"
                class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/50 p-4"
                @click.self="closeDetail"
            >
                <div class="max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-2xl bg-white shadow-2xl">
                    <div class="flex items-start justify-between border-b border-slate-200 px-6 py-5">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">
                                Allocation Detail
                            </h3>

                            <p class="mt-1 text-sm text-slate-500">
                                {{ selectedTransfer?.transfer_number ?? '-' }}
                            </p>
                        </div>

                        <button
                            type="button"
                            @click="closeDetail"
                            class="rounded-lg p-2 text-slate-500 hover:bg-slate-100"
                            aria-label="Close detail"
                        >
                            ✕
                        </button>
                    </div>

                    <div
                        v-if="detailLoading"
                        class="p-10 text-center text-sm text-slate-500"
                    >
                        Loading allocation details...
                    </div>

                    <div
                        v-else-if="selectedTransfer"
                        class="space-y-6 p-6"
                    >
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div>
                                <p class="text-xs text-slate-500">
                                    Transfer Date
                                </p>

                                <p class="mt-1 text-sm font-medium text-slate-800">
                                    {{ formatDate(selectedTransfer.transfer_date) }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-500">
                                    Allocation Amount
                                </p>

                                <p class="mt-1 text-sm font-semibold text-slate-900">
                                    {{ formatCurrency(selectedTransfer.amount) }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-500">
                                    Workflow Status
                                </p>

                                <div class="mt-1">
                                    <StatusBadge :status="selectedTransfer.status ?? 'Posted'" />
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="rounded-xl border border-slate-200 p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                    Source
                                </p>

                                <p class="mt-2 text-sm font-medium text-slate-800">
                                    {{ getBranchName(selectedTransfer, 'from') }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{ getAccountName(selectedTransfer, 'from') }}
                                </p>
                            </div>

                            <div class="rounded-xl border border-slate-200 p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                    Destination
                                </p>

                                <p class="mt-2 text-sm font-medium text-slate-800">
                                    {{ getBranchName(selectedTransfer, 'to') }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{ getAccountName(selectedTransfer, 'to') }}
                                </p>
                            </div>
                        </div>

                        <div
                            v-if="selectedTransfer.description"
                            class="rounded-xl bg-slate-50 p-4"
                        >
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                Description
                            </p>

                            <p class="mt-2 whitespace-pre-wrap text-sm text-slate-700">
                                {{ selectedTransfer.description }}
                            </p>
                        </div>
                    </div>

                    <div class="flex justify-end border-t border-slate-200 px-6 py-4">
                        <button
                            type="button"
                            @click="closeDetail"
                            class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>