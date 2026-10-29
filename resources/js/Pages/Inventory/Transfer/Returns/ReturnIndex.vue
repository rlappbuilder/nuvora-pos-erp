<script setup>
import {
    computed,
    ref,
    watch,
    onBeforeUnmount,
} from 'vue'

import TablePagination from '@/Components/Table/TablePagination.vue'
import StatusBadge from '@/Components/Display/StatusBadge.vue'
import { formatDate } from '@/Utils'

/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({
    availableTransfers: {
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

    returns: {
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

    transferStatistics: {
        type: Object,
        default: () => ({
            total: 0,
            available: 0,
            partiallyReturned: 0,
        }),
    },

    returnStatistics: {
        type: Object,
        default: () => ({
            total: 0,
            draft: 0,
            rejected: 0,
            posted: 0,
        }),
    },

    transferFilters: {
        type: Object,
        default: () => ({}),
    },

    returnFilters: {
        type: Object,
        default: () => ({}),
    },

    dateFrom: {
        type: String,
        default: '',
    },

    dateTo: {
        type: String,
        default: '',
    },

    mode: {
    type: String,
    default: 'available',
},
})

const emit = defineEmits([
    'create',
    'return',
    'view',
    'date-filter',
])

/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

const search = ref('')
const perPage = ref(10)

const loading = ref(false)
const selectedRows = ref([])
const searchTimeout = ref(null)

/*
|--------------------------------------------------------------------------
| Available Transfers
|--------------------------------------------------------------------------
*/

//const rows = computed(() =>
 //   props.availableTransfers?.data ?? []
//)

//const currentPagination = computed(() =>
  //  props.availableTransfers
//)

//const totalRecords = computed(() =>
 //   Number(
   //     currentPagination.value?.total ??
   //     rows.value.length
  //  )
//)
const isAvailableMode = computed(() => props.mode === 'available')

const rows = computed(() =>
    isAvailableMode.value
        ? props.availableTransfers?.data ?? []
        : props.returns?.data ?? []
)

const currentPagination = computed(() =>
    isAvailableMode.value
        ? props.availableTransfers
        : props.returns
)

const totalRecords = computed(() =>
    Number(currentPagination.value?.total ?? rows.value.length)
)
/*
|--------------------------------------------------------------------------
| Statistics
|--------------------------------------------------------------------------
*/

const statCards = computed(() => {
    if (isAvailableMode.value) {
        return [
            {
                key: 'total',
                label: 'Total Transfers',
                value: props.transferStatistics?.total ?? 0,
                color: 'blue',
                icon: 'transfer',
            },
            {
                key: 'available',
                label: 'Available for Return',
                value: props.transferStatistics?.available ?? 0,
                color: 'emerald',
                icon: 'check',
            },
            {
                key: 'partial',
                label: 'Partially Returned',
                value: props.transferStatistics?.partiallyReturned ?? 0,
                color: 'amber',
                icon: 'document',
            },
            {
                key: 'amount',
                label: 'Transfer Amount',
                value: props.transferStatistics?.totalAmount ?? 0,
                color: 'blue',
                icon: 'amount',
                isAmount: true,
            },
        ]
    }

    return [
        {
            key: 'total',
            label: 'Total Returns',
            value: props.returnStatistics?.total ?? 0,
            color: 'blue',
            icon: 'transfer',
        },
        {
            key: 'draft',
            label: 'Draft',
            value: props.returnStatistics?.draft ?? 0,
            color: 'slate',
            icon: 'document',
        },
        {
            key: 'posted',
            label: 'Posted',
            value: props.returnStatistics?.posted ?? 0,
            color: 'emerald',
            icon: 'check',
        },
        {
            key: 'amount',
            label: 'Transfer Amount',
            value: props.returnStatistics?.totalAmount ?? 0,
            color: 'blue',
            icon: 'amount',
            isAmount: true,
        },
    ]
})
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

function getItemCount(item) {
    return Number(
        item.details_count ??
        item.details?.length ??
        0
    )
}

function getTotalQty(item) {
    if (item.total_qty !== undefined && item.total_qty !== null) {
        return Number(item.total_qty)
    }

    return (item.details ?? []).reduce(
        (total, detail) => total + Number(detail.qty ?? 0),
        0
    )
}

function getAmount(item) {
    return Number(
        item.total_cost ??
        item.total_amount ??
        0
    )
}

function getOriginalTransfer(item) {
    return item.stock_transfer_header ??
        item.stockTransferHeader ??
        {}
}

function getSourceBranch(item) {
    if (isAvailableMode.value) {
        return item.from_branch?.name ?? '-'
    }

    return (
        item.from_branch?.name ??
        getOriginalTransfer(item).to_branch?.name ??
        '-'
    )
}

function getSourceWarehouse(item) {
    if (isAvailableMode.value) {
        return item.from_warehouse?.name ?? '-'
    }

    return (
        item.from_warehouse?.name ??
        getOriginalTransfer(item).to_warehouse?.name ??
        '-'
    )
}

function getDestinationBranch(item) {
    if (isAvailableMode.value) {
        return item.to_branch?.name ?? '-'
    }

    return (
        item.to_branch?.name ??
        getOriginalTransfer(item).from_branch?.name ??
        '-'
    )
}

function getDestinationWarehouse(item) {
    if (isAvailableMode.value) {
        return item.to_warehouse?.name ?? '-'
    }

    return (
        item.to_warehouse?.name ??
        getOriginalTransfer(item).from_warehouse?.name ??
        '-'
    )
}

function getDocumentNumber(item) {
    return item.number ?? '-'
}

function getDocumentDate(item) {
    return item.transaction_date
}

function openReturn(item) {
    emit('return', item)
}

function openView(item) {
    emit('view', item)
}

/*
|--------------------------------------------------------------------------
| Local Search
|--------------------------------------------------------------------------
|
| Search applies to the available transfers supplied by the parent.
| Server-side filtering and pagination remain the responsibility
| of the existing dashboard/controller integration.
|--------------------------------------------------------------------------
*/
const filteredRows = computed(() => {
    const query = search.value.trim().toLowerCase()

    return rows.value.filter((item) => {
        const original = getOriginalTransfer(item)

        const matchesSearch = !query || [
            item.number,
            item.description,
            original.number,
            item.from_branch?.name,
            item.to_branch?.name,
        ].some((value) =>
            String(value ?? '').toLowerCase().includes(query)
        )

        const matchesStatus =
            isAvailableMode.value ||
            !status.value ||
            item.status === status.value

        return matchesSearch && matchesStatus
    })
})

/*
|--------------------------------------------------------------------------
| Selection
|--------------------------------------------------------------------------
*/

const isAllSelected = computed(() =>
    filteredRows.value.length > 0 &&
    filteredRows.value.every((row) =>
        selectedRows.value.includes(row.id)
    )
)

const isIndeterminate = computed(() => {
    const count = filteredRows.value.filter((row) =>
        selectedRows.value.includes(row.id)
    ).length

    return count > 0 && count < filteredRows.value.length
})

function toggleAll(event) {
    selectedRows.value = event.target.checked
        ? filteredRows.value.map((row) => row.id)
        : []
}

/*
|--------------------------------------------------------------------------
| Sorting
|--------------------------------------------------------------------------
*/

const sortBy = ref('id')
const sortDirection = ref('desc')

function changeSort(column) {
    if (sortBy.value === column) {
        sortDirection.value =
            sortDirection.value === 'asc' ? 'desc' : 'asc'
    } else {
        sortBy.value = column
        sortDirection.value = 'asc'
    }

    emit('date-filter', {
        tab: 'available',
        search: search.value,
        sort_by: sortBy.value,
        sort_direction: sortDirection.value,
    })
}

function sortIndicator(column) {
    if (sortBy.value !== column) return ''

    return sortDirection.value === 'asc' ? '↑' : '↓'
}

/*
|--------------------------------------------------------------------------
| Reset
|--------------------------------------------------------------------------
*/

function resetFilters() {
    clearTimeout(searchTimeout.value)

    search.value = ''
    perPage.value = 10
    selectedRows.value = []

    emit('date-filter', {
        tab: 'available',
        search: '',
        per_page: 10,
        date_from: props.dateFrom,
        date_to: props.dateTo,
    })
}

/*
|--------------------------------------------------------------------------
| Watchers
|--------------------------------------------------------------------------
*/

watch([search, perPage], () => {
    selectedRows.value = []
})

watch(
    () => props.availableTransfers?.data,
    () => {
        selectedRows.value = []
    }
)

watch(search, () => {
    clearTimeout(searchTimeout.value)

    searchTimeout.value = setTimeout(() => {
        emit('date-filter', {
            tab: 'available',
            search: search.value,
            per_page: perPage.value,
        })
    }, 500)
})

/*
|--------------------------------------------------------------------------
| Date Range
|--------------------------------------------------------------------------
*/

function handleDateRangeApply({ dateFrom, dateTo }) {
    emit('date-filter', {
        tab: 'available',
        search: search.value,
        per_page: perPage.value,
        date_from: dateFrom,
        date_to: dateTo,
    })
}

function handleDateRangeClear() {
    emit('date-filter', {
        tab: 'available',
        search: search.value,
        per_page: perPage.value,
        date_from: '',
        date_to: '',
    })
}

/*
|--------------------------------------------------------------------------
| Cleanup
|--------------------------------------------------------------------------
*/

onBeforeUnmount(() => {
    clearTimeout(searchTimeout.value)
})
</script>

<template>
    <div class="space-y-8">
 

        <!-- STATISTICS -->
        <section>
            

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div
                    v-for="stat in statCards"
                    :key="stat.key"
                    class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-slate-500">
                                {{ stat.label }}
                            </p>

                            <p class="mt-2 text-2xl font-bold tabular-nums tracking-tight text-slate-900">
                                {{ stat.isAmount
                                    ? formatAmount(stat.value)
                                    : formatNumber(stat.value)
                                }}
                            </p>
                        </div>

                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl"
                            :class="{
                                'bg-blue-50 text-blue-600': stat.color === 'blue',
                                'bg-slate-100 text-slate-600': stat.color === 'slate',
                                'bg-red-50 text-red-600': stat.color === 'red',
                                'bg-emerald-50 text-emerald-600': stat.color === 'emerald',
                                'bg-amber-50 text-amber-600': stat.color === 'amber',
                            }"
                        >
                            <!-- TRANSFER ICON -->
                            <svg
                                v-if="stat.icon === 'transfer'"
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
                                    d="M7 7h14m0 0-4-4m4 4-4 4M17 17H3m0 0 4 4m-4-4 4-4"
                                />
                            </svg>

                            <!-- DOCUMENT ICON -->
                            <svg
                                v-else-if="stat.icon === 'document'"
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
                                    d="M8 7h8M8 11h8M8 15h5m-8 6h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H7L3 7v12a2 2 0 0 0 2 2Z"
                                />
                            </svg>

                            <!-- AMOUNT ICON -->
                            <svg
                                v-else-if="stat.icon === 'amount'"
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <rect
                                    x="2"
                                    y="5"
                                    width="20"
                                    height="14"
                                    rx="2"
                                />
                                <circle cx="12" cy="12" r="3" />
                                <path
                                    stroke-linecap="round"
                                    d="M6 9h.01M18 15h.01"
                                />
                            </svg>

                            <!-- DEFAULT CHECK ICON -->
                            <svg
                                v-else
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <circle cx="12" cy="12" r="9" />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m8 12 3 3 5-6"
                                />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FILTERS -->
        <section>
            <div class="mb-4">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-700">
                    Available Transfers
                </h2>

                <p class="mt-1 text-xs text-slate-500">
                    Find posted transfers received by the active branch.
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-12 sm:items-end">
                    <div class="sm:col-span-10">
                        <label class="mb-1.5 block text-xs font-medium text-slate-600">
                            Search
                        </label>

                        <div class="relative">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <circle cx="11" cy="11" r="7" />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m20 20-4-4"
                                />
                            </svg>

                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search transfer number..."
                                class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-9 pr-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/10"
                            />
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <button
                            type="button"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm font-medium text-slate-600 transition hover:bg-slate-50 hover:text-slate-900"
                            @click="resetFilters"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M20 7v5h-5M4 17v-5h5m11-3a8 8 0 0 0-14-3L4 12m16 0v3a8 8 0 0 1-14 3"
                                />
                            </svg>

                            Reset
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- TABLE -->
        <section>
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">
                            Available for Return
                        </h2>

                        <p class="mt-1 text-xs text-slate-500">
                            {{ totalRecords }} records
                        </p>
                    </div>
                </div>

                <div class="relative">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[1000px] divide-y divide-slate-100">
                            <thead class="bg-slate-50/80">
                                <tr>
                                    <th class="w-12 px-5 py-3 text-left">
                                        <input
                                            type="checkbox"
                                            :checked="isAllSelected"
                                            :indeterminate="isIndeterminate"
                                            :disabled="!filteredRows.length"
                                            class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                            @change="toggleAll"
                                        />
                                    </th>

                                    <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                                        <button
                                            type="button"
                                            class="inline-flex items-center gap-1"
                                            @click="changeSort('number')"
                                        >
                                            Transfer Information
                                            <span>{{ sortIndicator('number') }}</span>
                                        </button>
                                    </th>

                                    <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                                        Transfer Route
                                    </th>

                                    <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                                        Items
                                    </th>

                                    <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                                        Amount
                                    </th>

                                    <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                                        Status
                                    </th>

                                    <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                                        Actions
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100 bg-white">
                                <tr
                                    v-for="item in filteredRows"
                                    :key="item.id"
                                    class="transition hover:bg-blue-50/40"
                                >
                                    <td class="px-5 py-4 align-top">
                                        <input
                                            v-model="selectedRows"
                                            :value="item.id"
                                            type="checkbox"
                                            class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                        />
                                    </td>

                                    <!-- INFORMATION -->
                                    <td class="px-4 py-4 align-top">
                                        <button
                                            type="button"
                                            class="text-left text-sm font-semibold text-blue-600 transition hover:text-blue-700 hover:underline"
                                            @click="openView(item)"
                                        >
                                            {{ getDocumentNumber(item) }}
                                        </button>

                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ formatDate(getDocumentDate(item)) }}
                                        </p>

                                        <p
                                            v-if="item.description"
                                            class="mt-2 max-w-xs truncate text-xs text-slate-500"
                                            :title="item.description"
                                        >
                                            {{ item.description }}
                                        </p>
                                    </td>

                                    <!-- ROUTE -->
                                    <td class="px-4 py-4 align-top">
                                        <div class="flex items-start gap-3">
                                            <div class="flex shrink-0 flex-col items-center pt-1">
                                                <span class="h-2.5 w-2.5 rounded-full bg-blue-600 ring-4 ring-blue-50" />

                                                <span class="my-1 h-7 border-l-2 border-dashed border-slate-300" />

                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="h-3.5 w-3.5 text-emerald-600"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="2"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="m7 10 5 5 5-5"
                                                    />
                                                </svg>

                                                <span class="mt-1 h-2.5 w-2.5 rounded-full bg-emerald-600 ring-4 ring-emerald-50" />
                                            </div>

                                            <div class="min-w-0 space-y-3">
                                                <div>
                                                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                                        Source
                                                    </p>

                                                    <p class="mt-0.5 text-sm font-medium text-slate-800">
                                                        {{ getSourceBranch(item) }}
                                                    </p>

                                                    <p class="mt-0.5 text-xs text-slate-500">
                                                        {{ getSourceWarehouse(item) }}
                                                    </p>
                                                </div>

                                                <div>
                                                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                                        Destination
                                                    </p>

                                                    <p class="mt-0.5 text-sm font-medium text-slate-800">
                                                        {{ getDestinationBranch(item) }}
                                                    </p>

                                                    <p class="mt-0.5 text-xs text-slate-500">
                                                        {{ getDestinationWarehouse(item) }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- ITEMS -->
                                    <td class="whitespace-nowrap px-4 py-4 text-right align-top">
                                        <span class="text-sm font-medium tabular-nums text-slate-800">
                                            {{ formatNumber(getItemCount(item)) }}
                                        </span>

                                        <p class="mt-1 text-xs text-slate-400">
                                            {{ formatNumber(getTotalQty(item)) }} qty
                                        </p>
                                    </td>

                                    <!-- AMOUNT -->
                                    <td class="whitespace-nowrap px-4 py-4 text-right align-top">
                                        <span class="text-sm font-semibold tabular-nums text-slate-900">
                                            {{ formatAmount(getAmount(item)) }}
                                        </span>
                                    </td>

                                    <!-- STATUS -->
                                    <td class="whitespace-nowrap px-4 py-4 text-center align-top">
                                        <StatusBadge status="Posted" />
                                    </td>

                                    <!-- ACTIONS -->
                                    <td class="px-5 py-4 text-right align-top">
                                        <button
                                            type="button"
                                            class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-blue-700"
                                            @click="openReturn(item)"
                                        >
                                            Return
                                        </button>
                                    </td>
                                </tr>

                                <!-- EMPTY -->
                                <tr v-if="!filteredRows.length">
                                    <td
                                        colspan="7"
                                        class="px-6 py-16 text-center"
                                    >
                                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-6 w-6"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.8"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M4 7h11m0 0-3-3m3 3-3 3M20 17H9m0 0 3 3m-3-3 3-3"
                                                />
                                            </svg>
                                        </div>

                                        <h3 class="mt-3 text-sm font-semibold text-slate-800">
                                            No transfers available for return
                                        </h3>

                                        <p class="mt-1 text-xs text-slate-500">
                                            There are no posted transfers with remaining returnable quantities for this branch.
                                        </p>

                                        <button
                                            type="button"
                                            class="mt-4 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50"
                                            @click="resetFilters"
                                        >
                                            Reset Filters
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- LOADING -->
                    <div
                        v-if="loading"
                        class="absolute inset-0 z-20 flex items-center justify-center bg-white/70 backdrop-blur-[1px]"
                    >
                        <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-lg">
                            <svg
                                class="h-5 w-5 animate-spin text-blue-600"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
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
                                    d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 1-4 4H4Z"
                                />
                            </svg>

                            <span class="text-sm font-medium text-slate-700">
                                Loading...
                            </span>
                        </div>
                    </div>
                </div>

                <!-- PAGINATION -->
                <div class="mt-6">
                    <TablePagination
                        :data="currentPagination"
                        label="Available Transfers"
                    />
                </div>
            </div>
        </section>
    </div>
</template>