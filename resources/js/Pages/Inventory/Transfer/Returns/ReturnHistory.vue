<script setup>
import { computed, ref, watch, onBeforeUnmount } from 'vue'
import TablePagination from '@/Components/Table/TablePagination.vue'
import ActionDropdown from '@/Components/Action/ActionDropdown.vue'
import StatusBadge from '@/Components/Display/StatusBadge.vue'
import { formatDate } from '@/Utils'
import DateRangePicker from '@/Components/Form/DateRangePicker.vue'
import ReturnDetailModal from './ReturnDetailModal.vue'

const props = defineProps({
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

    returnStatistics: {
        type: Object,
        default: () => ({
            total: 0,
            draft: 0,
            submitted: 0,
            approved: 0,
            posted: 0,
            rejected: 0,
            cancelled: 0,
            totalAmount: 0,
        }),
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

    closeDetailOnSubmit: {
    type: Boolean,
    default: false,
},

submitSuccessSignal: {
    type: Number,
    default: 0,
},
})

const emit = defineEmits([
    'view',
    'submit',
    'approve',
    'post',
    'reject',
    'cancel',
    'date-filter',
    'close-detail',
])

/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const search = ref(props.returnFilters?.search ?? '')
const status = ref(props.returnFilters?.status ?? '')
const perPage = ref(Number(props.returnFilters?.per_page ?? 10))

const loading = ref(false)
const selectedRows = ref([])
const searchTimeout = ref(null)

const showDetail = ref(false)
const selectedReturn = ref(null)

/*
|--------------------------------------------------------------------------
| Status Options
|--------------------------------------------------------------------------
*/

const statusOptions = [
    { value: '', label: 'All Statuses' },
    { value: 'Draft', label: 'Draft' },
    { value: 'Submitted', label: 'Submitted' },
    { value: 'Approved', label: 'Approved' },
    { value: 'Posted', label: 'Posted' },
    { value: 'Rejected', label: 'Rejected' },
    { value: 'Cancelled', label: 'Cancelled' },
]

/*
|--------------------------------------------------------------------------
| Data
|--------------------------------------------------------------------------
*/

const rows = computed(() => props.returns?.data ?? [])

const totalRecords = computed(() =>
    Number(props.returns?.total ?? rows.value.length)
)

const statCards = computed(() => [
    {
        key: 'total',
        label: 'Total Returns',
        value: props.returnStatistics?.total ?? 0,
        color: 'blue',
    },
    {
        key: 'draft',
        label: 'Draft',
        value: props.returnStatistics?.draft ?? 0,
        color: 'slate',
    },
    {
        key: 'pending',
        label: 'Pending Approval',
        value: props.returnStatistics?.submitted ?? 0,
        color: 'amber',
    },
    {
        key: 'posted',
        label: 'Posted',
        value: props.returnStatistics?.posted ?? 0,
        color: 'emerald',
    },
])

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

function getItemCount(item) {
    return Number(
        item?.details_count
        ?? item?.details?.length
        ?? 0
    )
}

function getTotalQty(item) {
    if (item?.total_qty !== undefined && item?.total_qty !== null) {
        return Number(item.total_qty)
    }

    return (item?.details ?? []).reduce(
        (total, detail) => total + Number(detail.qty ?? 0),
        0
    )
}

function getAmount(item) {
    return Number(
        item?.total_cost
        ?? item?.total_amount
        ?? item?.amount
        ?? 0
    )
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
| Search and Filtering
|--------------------------------------------------------------------------
*/

const filteredRows = computed(() => {
    const query = search.value.trim().toLowerCase()

    return rows.value.filter((item) => {
        const originalNumber = getOriginalTransferNumber(item)

        const matchesSearch = !query || [
            getDocumentNumber(item),
            item?.description,
            originalNumber,
            item?.from_branch?.name,
            item?.to_branch?.name,
        ].some((value) =>
            String(value ?? '').toLowerCase().includes(query)
        )

        const matchesStatus =
            !status.value ||
            String(item?.status ?? '') === status.value

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

const sortBy = ref(props.returnFilters?.sort_by ?? 'id')

const sortDirection = ref(
    props.returnFilters?.sort_direction ?? 'desc'
)

function changeSort(column) {
    if (sortBy.value === column) {
        sortDirection.value =
            sortDirection.value === 'asc' ? 'desc' : 'asc'
    } else {
        sortBy.value = column
        sortDirection.value = 'asc'
    }

    emitFilters()
}

function sortIndicator(column) {
    if (sortBy.value !== column) return ''

    return sortDirection.value === 'asc' ? '↑' : '↓'
}

/*
|--------------------------------------------------------------------------
| Filter Events
|--------------------------------------------------------------------------
*/

function emitFilters(overrides = {}) {
    emit('date-filter', {
        tab: 'history',
        search: search.value,
        status: status.value,
        per_page: perPage.value,
        sort_by: sortBy.value,
        sort_direction: sortDirection.value,
        date_from: props.dateFrom,
        date_to: props.dateTo,
        ...overrides,
    })
}

function resetFilters() {
    clearTimeout(searchTimeout.value)

    search.value = ''
    status.value = ''
    perPage.value = 10
    sortBy.value = 'id'
    sortDirection.value = 'desc'
    selectedRows.value = []

    emitFilters({
        search: '',
        status: '',
        per_page: 10,
        sort_by: 'id',
        sort_direction: 'desc',
    })
}

watch([status, perPage], () => {
    selectedRows.value = []
    emitFilters()
})

watch(search, () => {
    clearTimeout(searchTimeout.value)

    selectedRows.value = []

    searchTimeout.value = setTimeout(() => {
        emitFilters()
    }, 500)
})

watch(
    () => props.returns?.data,
    () => {
        selectedRows.value = []

        if (selectedReturn.value && showDetail.value) {
            const updated = rows.value.find(
                (item) => item.id === selectedReturn.value.id
            )

            if (updated) {
                selectedReturn.value = updated
            }
        }
    }
)

/*
|--------------------------------------------------------------------------
| Date Range
|--------------------------------------------------------------------------
*/

function handleDateRangeApply({ dateFrom, dateTo }) {
    emitFilters({
        date_from: dateFrom,
        date_to: dateTo,
    })
}

function handleDateRangeClear() {
    emitFilters({
        date_from: '',
        date_to: '',
    })
}

/*
|--------------------------------------------------------------------------
| Return Detail
|--------------------------------------------------------------------------
*/

function openView(item) {
    selectedReturn.value = item
    showDetail.value = true

    emit('view', item)
}

function closeDetail() {
    showDetail.value = false
    selectedReturn.value = null
    emit('close-detail')
}
watch(
    () => props.submitSuccessSignal,
    (value, previousValue) => {
        console.log('RETURN HISTORY SIGNAL:', value, previousValue)

        if (value > 0 && value !== previousValue) {
            console.log('CLOSING RETURN DETAIL')
            closeDetail()
        }
    }
)
function onDropdownView(item) {
    openView(item)
}

/*
|--------------------------------------------------------------------------
| Cleanup
|--------------------------------------------------------------------------
*/

onBeforeUnmount(() => {
    clearTimeout(searchTimeout.value)
})
function handleSubmit(item) {
    emit('submit', item)
}
</script>

<template>
    <div class="space-y-8">
        <!-- HEADER AND DATE FILTER -->
       

        <!-- STATISTICS -->
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div
                v-for="stat in statCards"
                :key="stat.key"
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
            >
                <p class="text-xs font-medium text-slate-500">
                    {{ stat.label }}
                </p>

                <p class="mt-2 text-2xl font-bold tracking-tight text-slate-900">
                    {{ formatNumber(stat.value) }}
                </p>

                <div
                    class="mt-3 h-1 w-12 rounded-full"
                    :class="{
                        'bg-blue-500': stat.color === 'blue',
                        'bg-slate-400': stat.color === 'slate',
                        'bg-amber-500': stat.color === 'amber',
                        'bg-emerald-500': stat.color === 'emerald',
                    }"
                />
            </div>
        </section>

        <!-- FILTERS -->
        <section>
            <div class="mb-4">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-700">
                    Return Documents
                </h3>

                <p class="mt-1 text-xs text-slate-500">
                    Search and filter return records by number or status.
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-12 sm:items-end">
                    <div class="sm:col-span-7">
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
                                placeholder="Search return or transfer number..."
                                class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-9 pr-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/10"
                            />
                        </div>
                    </div>

                    <div class="sm:col-span-3">
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
                            class="inline-flex w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm font-medium text-slate-600 transition hover:bg-slate-50 hover:text-slate-900"
                            @click="resetFilters"
                        >
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
                        <h3 class="text-base font-semibold text-slate-900">
                            Return History
                        </h3>

                        <p class="mt-1 text-xs text-slate-500">
                            {{ totalRecords }} records
                        </p>
                    </div>

                    <div class="text-xs text-slate-500">
                        Selected: {{ selectedRows.length }}
                    </div>
                </div>

                <div class="relative">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[1050px] divide-y divide-slate-100">
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
                                            Return Information
                                            <span>{{ sortIndicator('number') }}</span>
                                        </button>
                                    </th>

                                    <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                                        Original Transfer
                                    </th>

                                    <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                                        Return Route
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
                                    v-for="item in filteredRows"
                                    :key="item.id"
                                    class="transition hover:bg-blue-50/40"
                                >
                                    <!-- SELECTION -->
                                    <td class="px-5 py-4 align-top">
                                        <input
                                            v-model="selectedRows"
                                            :value="item.id"
                                            type="checkbox"
                                            class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                        />
                                    </td>

                                    <!-- RETURN INFORMATION -->
                                    <td class="px-4 py-4 align-top">
                                        <span class="text-sm font-semibold text-slate-800">
                                            {{ getDocumentNumber(item) }}
                                        </span>

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

                                    <!-- ORIGINAL TRANSFER -->
                                    <td class="px-4 py-4 align-top">
                                        <span class="text-sm font-medium text-slate-800">
                                            {{ getOriginalTransferNumber(item) }}
                                        </span>

                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ formatDate(
                                                getOriginalTransfer(item)?.transaction_date
                                                ?? getOriginalTransfer(item)?.date
                                            ) }}
                                        </p>
                                    </td>

                                    <!-- ROUTE -->
                                    <td class="px-4 py-4 align-top">
                                        <div class="space-y-3">
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
                                        <StatusBadge :status="item.status ?? 'Draft'" />
                                    </td>

                                    <!-- ACTIONS -->
                                    <td class="px-5 py-4 text-right align-top">
                                        <ActionDropdown
                                            @view="onDropdownView(item)"
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
                                <tr v-if="!filteredRows.length">
                                    <td colspan="8" class="px-6 py-16 text-center">
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
                                            No return documents found
                                        </h3>

                                        <p class="mt-1 text-xs text-slate-500">
                                            No return records match the selected filters.
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

                <!-- PAGINATION -->
                <div class="border-t border-slate-100 px-5 py-4">
                    <TablePagination
                        :data="props.returns"
                        label="Transfer Returns"
                    />
                </div>
            </div>
        </section>
    </div>

    <!-- RETURN DETAIL MODAL -->
       <ReturnDetailModal
        :show="showDetail"
        :return-item="selectedReturn"
        @close="closeDetail"
        @submit="handleSubmit"
        @approve="emit('approve', $event)"
        @post="emit('post', $event)"
        @reject="emit('reject', $event)"
        @cancel="emit('cancel', $event)"
    />
</template>