<script setup>
import { computed, ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import TablePagination from '@/Components/Table/TablePagination.vue'
import ActionDropdown from '@/Components/Action/ActionDropdown.vue'
import StatusBadge from '@/Components/Display/StatusBadge.vue'
import { formatDate, currency } from '@/Utils'


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

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
        default: () => ({
            total: 0,
            draft: 0,
            rejected: 0,
            posted: 0,
        }),
    },

    companyId: {
        type: [Number, String],
        default: null,
    },

    branches: {
        type: Array,
        default: () => [],
    },

    warehouses: {
        type: Array,
        default: () => [],
    },

    variants: {
        type: Array,
        default: () => [],
    },

    units: {
        type: Array,
        default: () => [],
    },

    filters: {
        type: Object,
        default: () => ({}),
    },

    previewNumber: {
        type: String,
        default: '',
    },

    dateFrom: {
        type: String,
        default: '',
    },

    dateTo: {
        type: String,
        default: '',
    },
})

const emit = defineEmits([
    'create',
    'view',
    'edit',
])
function openView(item) {
    emit('view', item)
}
/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const search = ref(props.filters?.search ?? '')
const branchId = ref(props.filters?.branch_id ?? '')
const status = ref(props.filters?.status ?? '')

const perPage = ref(Number(props.filters?.per_page ?? 10))
const sortBy = ref(props.filters?.sort_by ?? 'id')
const sortDirection = ref(props.filters?.sort_direction ?? 'desc')

const loading = ref(false)
const selectedRows = ref([])
const searchTimeout = ref(null)

const rows = computed(() => props.transfers?.data ?? [])

const totalRecords = computed(() =>
    Number(
        props.transfers?.total ??
        props.transfers?.meta?.total ??
        rows.value.length
    )
)

const currentPage = computed(() =>
    Number(props.transfers?.current_page ?? 1)
)

const lastPage = computed(() =>
    Number(props.transfers?.last_page ?? 1)
)

const pageSize = computed(() =>
    Number(props.transfers?.per_page ?? perPage.value)
)

const firstRecord = computed(() => {
    if (!totalRecords.value) return 0

    return (currentPage.value - 1) * pageSize.value + 1
})

const lastRecord = computed(() =>
    Math.min(
        currentPage.value * pageSize.value,
        totalRecords.value
    )
)

/*
|--------------------------------------------------------------------------
| Status Options
|--------------------------------------------------------------------------
*/

const statusOptions = [
    { value: '', label: 'All Statuses' },
    { value: 'Draft', label: 'Draft' },
    { value: 'Rejected', label: 'Rejected' },
    { value: 'Posted', label: 'Posted' },
]

/*
|--------------------------------------------------------------------------
| Statistics
|--------------------------------------------------------------------------
*/

const statCards = computed(() => [
    {
        key: 'total',
        label: 'Total Transfers',
        value: props.statistics?.total ?? 0,
        icon: 'transfer',
        color: 'blue',
    },
    {
        key: 'draft',
        label: 'Draft',
        value: props.statistics?.draft ?? 0,
        icon: 'document',
        color: 'slate',
    },
    {
        key: 'rejected',
        label: 'Rejected',
        value: props.statistics?.rejected ?? 0,
        icon: 'reject',
        color: 'red',
    },
    {
        key: 'posted',
        label: 'Posted',
        value: props.statistics?.posted ?? 0,
        icon: 'check',
        color: 'emerald',
    },
])

/*
|--------------------------------------------------------------------------
| Data Loading
|--------------------------------------------------------------------------
*/

function buildQuery() {
    return {
        search: search.value,
        branch_id: branchId.value,
        status: status.value,

        date_from: props.dateFrom || '',
        date_to: props.dateTo || '',

        per_page: perPage.value,
        sort_by: sortBy.value,
        sort_direction: sortDirection.value,
    }
}

function loadData() {
    loading.value = true
    selectedRows.value = []

    router.get(
        route('stock-transfers.index'),
        buildQuery(),
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
    clearTimeout(searchTimeout.value)

    search.value = ''
    branchId.value = ''
    status.value = ''
    perPage.value = 10
    selectedRows.value = []

    router.get(
        route('stock-transfers.index'),
        {
            search: '',
            branch_id: '',
            from_branch_id: '',
            from_warehouse_id: '',
            to_branch_id: '',
            to_warehouse_id: '',
            status: '',
            date_from: '',
            date_to: '',
            per_page: 10,
            sort_by: sortBy.value,
            sort_direction: sortDirection.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    )
}

/*
|--------------------------------------------------------------------------
| Filter Watchers
|--------------------------------------------------------------------------
*/

watch(search, () => {
    clearTimeout(searchTimeout.value)

    searchTimeout.value = setTimeout(() => {
        loadData()
    }, 500)
})

watch([branchId, status, perPage], () => {
    loadData()
})

watch(
    () => props.transfers?.data,
    () => {
        selectedRows.value = []
    }
)

/*
|--------------------------------------------------------------------------
| Sorting
|--------------------------------------------------------------------------
*/

function changeSort(column) {
    if (sortBy.value === column) {
        sortDirection.value =
            sortDirection.value === 'asc' ? 'desc' : 'asc'
    } else {
        sortBy.value = column
        sortDirection.value = 'asc'
    }

    loadData()
}

function sortIndicator(column) {
    if (sortBy.value !== column) return ''

    return sortDirection.value === 'asc' ? '↑' : '↓'
}

/*
|--------------------------------------------------------------------------
| Selection
|--------------------------------------------------------------------------
*/

const isAllSelected = computed(() =>
    rows.value.length > 0 &&
    rows.value.every((row) =>
        selectedRows.value.includes(row.id)
    )
)

const isIndeterminate = computed(() => {
    const count = rows.value.filter((row) =>
        selectedRows.value.includes(row.id)
    ).length

    return count > 0 && count < rows.value.length
})

function toggleAll(event) {
    selectedRows.value = event.target.checked
        ? rows.value.map((row) => row.id)
        : []
}

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
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
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



/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/

function visitPage(url) {
    if (!url || loading.value) return

    loading.value = true

    router.visit(url, {
        preserveState: true,
        preserveScroll: true,

        onFinish: () => {
            loading.value = false
        },
    })
}

</script>

<template>
    <div class="space-y-8">
        <!-- STATISTICS -->
        <section>
            <div class="mb-4">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-700">
                    Transfer Overview
                </h2>

                <p class="mt-1 text-xs text-slate-500">
                    Overview of stock transfer records and status.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div
                    v-for="stat in statCards"
                    :key="stat.key"
                    class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-medium text-slate-500">
                                {{ stat.label }}
                            </p>

                            <p class="mt-2 text-2xl font-bold tabular-nums tracking-tight text-slate-900">
                                {{ formatNumber(stat.value) }}
                            </p>
                        </div>

                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl"
                            :class="{
                                'bg-blue-50 text-blue-600': stat.color === 'blue',
                                'bg-slate-100 text-slate-600': stat.color === 'slate',
                                'bg-red-50 text-red-600': stat.color === 'red',
                                'bg-emerald-50 text-emerald-600': stat.color === 'emerald',
                            }"
                        >
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

                            <svg
                                v-else-if="stat.icon === 'reject'"
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
                                    d="m9 9 6 6m0-6-6 6"
                                />
                            </svg>

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
                    Transfer Records
                </h2>

                <p class="mt-1 text-xs text-slate-500">
                    Search and filter stock transfer transactions.
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="space-y-4">
                    <div>
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

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-12 sm:items-end">
                        <div class="sm:col-span-5">
                            <label class="mb-1.5 block text-xs font-medium text-slate-600">
                                Branch
                            </label>

                            <select
                                v-model="branchId"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/10"
                            >
                                <option value="">All Branches</option>

                                <option
                                    v-for="branch in branches"
                                    :key="branch.id"
                                    :value="branch.id"
                                >
                                    {{ branch.label }}
                                </option>
                            </select>
                        </div>

                        <div class="sm:col-span-5">
                            <label class="mb-1.5 block text-xs font-medium text-slate-600">
                                Status
                            </label>

                            <select
                                v-model="status"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/10"
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
            </div>
        </section>

        <!-- TABLE -->
        <section>
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <!-- TABLE HEADER -->
                <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">
                            Stock Transfers
                        </h2>

                        <p class="mt-1 text-xs text-slate-500">
                            {{ totalRecords }} transfer records
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
                                            :disabled="!rows.length"
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
                                        <button
                                            type="button"
                                            class="inline-flex items-center justify-center gap-1"
                                            @click="changeSort('status')"
                                        >
                                            Status
                                            <span>{{ sortIndicator('status') }}</span>
                                        </button>
                                    </th>

                                    <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                                        Actions
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100 bg-white">
                                <tr
                                    v-for="item in rows"
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
                                            {{ item.number ?? '-' }}
                                        </button>

                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ formatDate(item.transaction_date) }}
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
                                                        From
                                                    </p>

                                                    <p class="mt-0.5 text-sm font-medium text-slate-800">
                                                        {{ item.from_branch?.name ?? '-' }}
                                                    </p>

                                                    <p class="mt-0.5 text-xs text-slate-500">
                                                        {{ item.from_warehouse?.name ?? '-' }}
                                                    </p>
                                                </div>

                                                <div>
                                                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                                        To
                                                    </p>

                                                    <p class="mt-0.5 text-sm font-medium text-slate-800">
                                                        {{ item.to_branch?.name ?? '-' }}
                                                    </p>

                                                    <p class="mt-0.5 text-xs text-slate-500">
                                                        {{ item.to_warehouse?.name ?? '-' }}
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
                                            {{ formatAmount(item.total_cost ?? 0) }}
                                        </span>
                                    </td>

                                    <!-- STATUS -->
                                    <td class="whitespace-nowrap px-4 py-4 text-center align-top">
                                        <StatusBadge :status="item.status" />
                                    </td>

                                    <!-- ACTIONS -->
                                   <td class="px-5 py-4 text-right align-top">
                                    <ActionDropdown
                                        @view="openView(item)"
                                        :showEdit="false"
                                        :showDuplicate="false"
                                        :showPost="false"
                                        :showReject="false"
                                        :showExport="false"
                                        :showHistory="false"
                                        :showDelete="false"
                                    />
                                </td>
                                </tr>

                                <!-- EMPTY -->
                                <tr v-if="!rows.length">
                                    <td colspan="7" class="px-6 py-16 text-center">
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
                                            No stock transfers found
                                        </h3>

                                        <p class="mt-1 text-xs text-slate-500">
                                            No transfer records match the selected filters.
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
                                    d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z"
                                />
                            </svg>

                            <span class="text-sm font-medium text-slate-700">
                                Loading...
                            </span>
                        </div>
                    </div>
                </div>

               <!-- ===================================================== -->
                <!-- Pagination -->
                <!-- ===================================================== -->

                <div class="mt-6">
                    <TablePagination
                        :data="transfers"
                        label="Transfers Stock"
                    />
                </div>
            </div>
        </section>
    </div>
   
</template>