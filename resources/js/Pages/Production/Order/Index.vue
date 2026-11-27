<script setup>
import { ref, reactive, computed, watch, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'

import AppLayout from '@/Layouts/AppLayout.vue'

import StatsCard from '@/Components/Card/StatsCard.vue'
import PageHeader from '@/Components/Layout/PageHeader.vue'
import Card from '@/Components/Layout/Card.vue'

import DataTable from '@/Components/Table/DataTable.vue'
import DataTableHead from '@/Components/Table/DataTableHead.vue'
import DataTableBody from '@/Components/Table/DataTableBody.vue'
import DataTableHeaderCell from '@/Components/Table/DataTableHeaderCell.vue'
import DataTableRow from '@/Components/Table/DataTableRow.vue'
import DataTableCell from '@/Components/Table/DataTableCell.vue'
import TablePagination from '@/Components/Table/TablePagination.vue'
import TableEmpty from '@/Components/Table/TableEmpty.vue'

import StatusBadge from '@/Components/Display/StatusBadge.vue'
import BaseButton from '@/Components/Button/BaseButton.vue'
import BulkActionDropdown from '@/Components/Bulk/BulkActionDropdown.vue'
import ActionDropdown from '@/Components/Action/ActionDropdown.vue'
import ConfirmDeleteModal from '@/Components/Modal/ConfirmDeleteModal.vue'

import SearchableSelect from '@/Components/Form/SearchableSelect.vue'

import {
    FolderIcon,
    DocumentTextIcon,
    ClockIcon,
    CheckCircleIcon,
    Cog6ToothIcon,
    XCircleIcon,
} from '@heroicons/vue/24/solid'

import {
    success,
    error,
} from '@/Utils'

import {
    LoadingOverlay,
} from '@/Components/Feedback'

const props = defineProps({
    title: {
        type: String,
        default: 'Production Orders',
    },
    orders: {
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
            submitted: 0,
            approved: 0,
            in_progress: 0,
            completed: 0,
            rejected: 0,
            cancelled: 0,
        }),
    },
    branches: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            branch_id: '',
            status: '',
            date_from: '',
            date_to: '',
            per_page: 10,
            sort: 'created_at',
            direction: 'desc',
        }),
    },
})

const loading = ref(false)
const searchInput = ref(props.filters.search ?? '')
const selectedOrders = ref([])

const filterState = reactive({
    search: props.filters.search ?? '',
    branch_id: props.filters.branch_id ?? '',
    status: props.filters.status ?? '',
    date_from: props.filters.date_from ?? '',
    date_to: props.filters.date_to ?? '',
    per_page: Number(props.filters.per_page ?? 10),
})

const sortState = reactive({
    sort: props.filters.sort ?? 'created_at',
    direction: props.filters.direction ?? 'desc',
})

let searchTimeout = null
let routerStartListener = null
let routerFinishListener = null

const statusOptions = [
    { value: '', label: 'All Status' },
    { value: 'Draft', label: 'Draft' },
    { value: 'Submitted', label: 'Submitted' },
    { value: 'Approved', label: 'Approved' },
    { value: 'In Progress', label: 'In Progress' },
    { value: 'Completed', label: 'Completed' },
    { value: 'Rejected', label: 'Rejected' },
    { value: 'Cancelled', label: 'Cancelled' },
]

const branchOptions = computed(() => [
    { value: '', label: 'All Branches' },
    ...props.branches.map(branch => ({
        value: branch.id,
        label: branch.name,
    })),
])

const perPageOptions = [
    { value: 10, label: '10' },
    { value: 25, label: '25' },
    { value: 50, label: '50' },
    { value: 100, label: '100' },
]

const hasActiveFilters = computed(() => {
    return Boolean(
        filterState.search ||
        filterState.branch_id ||
        filterState.status ||
        filterState.date_from ||
        filterState.date_to
    )
})

const visibleOrders = computed(() => props.orders?.data ?? [])

const statisticsCards = computed(() => [
    {
        title: 'Total Orders',
        value: props.statistics.total ?? 0,
        color: 'cyan',
        icon: FolderIcon,
    },
    {
        title: 'Draft',
        value: props.statistics.draft ?? 0,
        color: 'amber',
        icon: DocumentTextIcon,
    },
    {
        title: 'Submitted',
        value: props.statistics.submitted ?? 0,
        color: 'cyan',
        icon: ClockIcon,
    },
    {
        title: 'Approved',
        value: props.statistics.approved ?? 0,
        color: 'emerald',
        icon: CheckCircleIcon,
    },
    {
        title: 'In Progress',
        value: props.statistics.in_progress ?? 0,
        color: 'cyan',
        icon: Cog6ToothIcon,
    },
    {
        title: 'Completed',
        value: props.statistics.completed ?? 0,
        color: 'emerald',
        icon: CheckCircleIcon,
    },
])

const getOutputVariant = order => {
    const variant = order?.details?.product_variant

    if (!variant) return '-'

    return variant.product?.name ?? variant.name ?? '-'
}

const getOutputSku = order => {
    return order?.details?.product_variant?.sku ?? '-'
}

const getBomName = order => {
    return order?.production_bom?.name ?? '-'
}

const getBomVersion = order => {
    const version = order?.production_bom_version

    if (!version) return '-'

    return version.version_number
        ? `Version ${version.version_number}`
        : version.code ?? `#${version.id}`
}

const formatQuantity = value => {
    if (value === null || value === undefined || value === '') return '0'

    return new Intl.NumberFormat('id-ID', {
        maximumFractionDigits: 4,
    }).format(Number(value))
}
const formatDate = value => {
    if (!value) return '-'

    const date = new Date(value)

    if (Number.isNaN(date.getTime())) return '-'

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(date)
}

const formatOrderDate = value => {
    if (!value) return '-'
    return formatDate(value)
}


const getProgress = order => {
    const planned = Number(order?.details?.planned_quantity ?? 0)
    const produced = Number(order?.details?.produced_quantity ?? 0)

    if (planned <= 0) return 0

    return Math.min((produced / planned) * 100, 100)
}

const getProgressLabel = order => {
    return `${getProgress(order).toFixed(1)}%`
}

const getStatusVariant = status => {
    const variants = {
        Draft: 'secondary',
        Submitted: 'warning',
        Approved: 'info',
        'In Progress': 'primary',
        Completed: 'success',
        Rejected: 'danger',
        Cancelled: 'danger',
    }

    return variants[status] ?? 'secondary'
}

const applyFilters = () => {
    router.get(
        route('production-orders.index'),
        {
            ...filterState,
            ...sortState,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onStart: () => {
                loading.value = true
            },
            onFinish: () => {
                loading.value = false
            },
        }
    )
}

const debouncedSearch = () => {
    clearTimeout(searchTimeout)

    searchTimeout = setTimeout(() => {
        filterState.search = searchInput.value
        applyFilters()
    }, 400)
}

const changeSort = field => {
    if (sortState.sort === field) {
        sortState.direction =
            sortState.direction === 'asc' ? 'desc' : 'asc'
    } else {
        sortState.sort = field
        sortState.direction = 'asc'
    }

    applyFilters()
}

const sortIcon = field => {
    if (sortState.sort !== field) return '↕'

    return sortState.direction === 'asc' ? '↑' : '↓'
}

const resetFilters = () => {
    clearTimeout(searchTimeout)

    searchInput.value = ''

    filterState.search = ''
    filterState.branch_id = ''
    filterState.status = ''
    filterState.date_from = ''
    filterState.date_to = ''
    filterState.per_page = 10

    sortState.sort = 'created_at'
    sortState.direction = 'desc'

    selectedOrders.value = []

    applyFilters()
}

const changePerPage = value => {
    filterState.per_page = Number(value)
    applyFilters()
}

const toggleSelectOrder = id => {
    const index = selectedOrders.value.indexOf(id)

    if (index === -1) {
        selectedOrders.value.push(id)
    } else {
        selectedOrders.value.splice(index, 1)
    }
}

const isSelected = id => selectedOrders.value.includes(id)

const viewOrder = order => {
    router.visit(route('production-orders.show', order.id))
}

const editOrder = order => {
    if (order.status !== 'Draft') return

    router.visit(route('production-orders.edit', order.id))
}

const createOrder = () => {
    router.visit(route('production-orders.create'))
}

watch(
    () => filterState.branch_id,
    () => applyFilters()
)

watch(
    () => filterState.status,
    () => applyFilters()
)

watch(
    () => filterState.date_from,
    () => applyFilters()
)

watch(
    () => filterState.date_to,
    () => applyFilters()
)

watch(
    () => props.filters,
    newFilters => {
        searchInput.value = newFilters.search ?? ''

        filterState.search = newFilters.search ?? ''
        filterState.branch_id = newFilters.branch_id ?? ''
        filterState.status = newFilters.status ?? ''
        filterState.date_from = newFilters.date_from ?? ''
        filterState.date_to = newFilters.date_to ?? ''
        filterState.per_page = Number(newFilters.per_page ?? 10)

        sortState.sort = newFilters.sort ?? 'created_at'
        sortState.direction = newFilters.direction ?? 'desc'
    },
    { deep: true }
)

watch(
    () => props.orders?.data,
    () => {
        const availableIds = visibleOrders.value.map(order => order.id)

        selectedOrders.value = selectedOrders.value.filter(id =>
            availableIds.includes(id)
        )
    },
    { deep: true }
)

onMounted(() => {
    routerStartListener = router.on('start', () => {
        loading.value = true
    })

    routerFinishListener = router.on('finish', () => {
        loading.value = false
    })
})

onUnmounted(() => {
    clearTimeout(searchTimeout)

    if (routerStartListener) routerStartListener()
    if (routerFinishListener) routerFinishListener()
})
</script>

<template>
    <AppLayout :title="title">
        <div class="space-y-6">
            <!-- Breadcrumb -->
            <div class="flex items-center gap-2 text-sm">
                <Link
                    href="/production"
                    class="text-gray-500 hover:text-primary-600 transition"
                >
                    Production
                </Link>

                <span class="text-gray-400">/</span>

                <span class="font-medium text-gray-900 dark:text-white">
                    Production Orders
                </span>
            </div>

            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                        Production Orders
                    </h1>

                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Manage and monitor your production orders.
                    </p>
                </div>

                <BaseButton
                    variant="primary"
                    @click="createOrder"
                >
                    <span class="mr-2">+</span>
                    Create Production Order
                </BaseButton>
            </div>

            <!-- Statistics -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                    <StatsCard
                        v-for="stat in statisticsCards"
                        :key="stat.title"
                        :title="stat.title"
                        :value="stat.value"
                        :color="stat.color"
                    >
                        <template #icon>
                            <component
                                :is="stat.icon"
                                class="h-7 w-7"
                            />
                        </template>
                    </StatsCard>
                </div>

            <!-- Main Content -->
            <Card>
                <div class="space-y-5">
                    <!-- Table Header -->
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Production Order List
                            </h2>

                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                View and manage all production orders.
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <BaseButton
                                variant="secondary"
                                @click="resetFilters"
                            >
                                Reset Filters
                            </BaseButton>

                            <BaseButton
                                variant="primary"
                                @click="createOrder"
                            >
                                + Create Order
                            </BaseButton>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                        <div class="lg:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Search
                            </label>

                            <input
                                v-model="searchInput"
                                type="text"
                                placeholder="Search order number, BOM, product..."
                                class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white text-sm focus:border-primary-500 focus:ring-primary-500"
                                @input="debouncedSearch"
                            />
                        </div>

                        <!-- Branch -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Branch
                            </label>

                            <SearchableSelect
                                v-model="filterState.branch_id"
                                :options="branches"
                                label="label"
                                value-key="id"
                                placeholder="All Branches"
                            />
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Status
                            </label>

                            <select
                                v-model="filterState.status"
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-500"
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

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Date From
                            </label>

                            <input
                                v-model="filterState.date_from"
                                type="date"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white text-sm focus:border-primary-500 focus:ring-primary-500"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Date To
                            </label>

                            <input
                                v-model="filterState.date_to"
                                type="date"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white text-sm focus:border-primary-500 focus:ring-primary-500"
                            />
                        </div>
                    </div>

                    <!-- Active Filter Info -->
                    <div
                        v-if="hasActiveFilters"
                        class="flex flex-wrap items-center gap-2 text-xs text-gray-500 dark:text-gray-400"
                    >
                        <span>Active filters:</span>

                        <span
                            v-if="filterState.search"
                            class="rounded-full bg-gray-100 dark:bg-gray-700 px-2.5 py-1"
                        >
                            Search: {{ filterState.search }}
                        </span>

                        <span
                            v-if="filterState.branch_id"
                            class="rounded-full bg-gray-100 dark:bg-gray-700 px-2.5 py-1"
                        >
                            Branch selected
                        </span>

                        <span
                            v-if="filterState.status"
                            class="rounded-full bg-gray-100 dark:bg-gray-700 px-2.5 py-1"
                        >
                            {{ filterState.status }}
                        </span>

                        <span
                            v-if="filterState.date_from"
                            class="rounded-full bg-gray-100 dark:bg-gray-700 px-2.5 py-1"
                        >
                            From: {{ filterState.date_from }}
                        </span>

                        <span
                            v-if="filterState.date_to"
                            class="rounded-full bg-gray-100 dark:bg-gray-700 px-2.5 py-1"
                        >
                            To: {{ filterState.date_to }}
                        </span>
                    </div>

                    <!-- Table -->
                    <div class="relative overflow-x-auto">
                        <LoadingOverlay v-if="loading" />

                        <DataTable
                                sticky-header
                                max-height="570px"
                            >
                                <DataTableHead sticky>
                                <DataTableHeaderCell
                                    width="42px"
                                    align="center"
                                >
                                    #
                                </DataTableHeaderCell>

                                <DataTableHeaderCell width="155px">
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-1 font-semibold"
                                        @click="changeSort('number')"
                                    >
                                        Order Number
                                        <span>{{ sortIcon('number') }}</span>
                                    </button>
                                </DataTableHeaderCell>

                                <DataTableHeaderCell width="125px">
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-1 font-semibold"
                                        @click="changeSort('order_date')"
                                    >
                                        Order Date
                                        <span>{{ sortIcon('order_date') }}</span>
                                    </button>
                                </DataTableHeaderCell>

                                <DataTableHeaderCell width="145px">
                                    Branch
                                </DataTableHeaderCell>

                                <DataTableHeaderCell width="210px">
                                    BOM
                                </DataTableHeaderCell>

                                <DataTableHeaderCell width="180px">
                                    Output Product
                                </DataTableHeaderCell>

                                <DataTableHeaderCell
                                    width="110px"
                                    align="right"
                                >
                                    Planned Qty
                                </DataTableHeaderCell>

                                <DataTableHeaderCell width="140px">
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-1 font-semibold"
                                        @click="changeSort('planned_start_date')"
                                    >
                                        Planned Start
                                        <span>{{ sortIcon('planned_start_date') }}</span>
                                    </button>
                                </DataTableHeaderCell>

                                <DataTableHeaderCell width="125px">
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-1 font-semibold"
                                        @click="changeSort('status')"
                                    >
                                        Status
                                        <span>{{ sortIcon('status') }}</span>
                                    </button>
                                </DataTableHeaderCell>

                                <DataTableHeaderCell
                                    width="130px"
                                    align="center"
                                >
                                    Progress
                                </DataTableHeaderCell>

                                <DataTableHeaderCell
                                    width="150px"
                                    align="right"
                                >
                                    Actions
                                </DataTableHeaderCell>
                            </DataTableHead>

                                <DataTableBody>
                                    <template v-if="visibleOrders.length">
                                        <DataTableRow
                                            v-for="(order, index) in visibleOrders"
                                            :key="order.id"
                                            class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/50"
                                            @click="viewOrder(order)"
                                        >
                                            <!-- Number -->
                                            <DataTableCell align="center">
                                                {{ (orders.current_page - 1) * orders.per_page + index + 1 }}
                                            </DataTableCell>

                                            <!-- Order Number -->
                                            <DataTableCell>
                                                <div class="font-medium text-primary-600 dark:text-primary-400">
                                                    {{ order.number }}
                                                </div>

                                                <div class="mt-1 text-xs text-gray-500">
                                                    {{ getBomVersion(order) }}
                                                </div>
                                            </DataTableCell>

                                            <!-- Order Date -->
                                            <DataTableCell>
                                                {{ formatOrderDate(order.order_date) }}
                                            </DataTableCell>

                                            <!-- Branch -->
                                            <DataTableCell>
                                                {{ order.branch?.name ?? '-' }}
                                            </DataTableCell>

                                            <!-- BOM -->
                                            <DataTableCell>
                                                <div class="font-medium text-gray-800 dark:text-gray-200">
                                                    {{ getBomName(order) }}
                                                </div>

                                                <div class="mt-1 text-xs text-gray-500">
                                                    {{ order.production_bom?.code ?? '-' }}
                                                </div>
                                            </DataTableCell>

                                            <!-- Output Product -->
                                            <DataTableCell>
                                                <div class="font-medium text-gray-800 dark:text-gray-200">
                                                    {{ getOutputVariant(order) }}
                                                </div>

                                                <div class="mt-1 text-xs text-gray-500">
                                                    SKU: {{ getOutputSku(order) }}
                                                </div>
                                            </DataTableCell>

                                            <!-- Planned Quantity -->
                                            <DataTableCell align="right">
                                                {{ formatQuantity(order.details?.planned_quantity) }}
                                            </DataTableCell>

                                            <!-- Planned Start -->
                                            <DataTableCell>
                                                {{ formatOrderDate(order.planned_start_date) }}
                                            </DataTableCell>

                                            <!-- Status -->
                                            <DataTableCell>
                                                <StatusBadge :status="order.status" />
                                            </DataTableCell>

                                            <!-- Progress -->
                                            <DataTableCell align="center">
                                                <div class="min-w-[100px]">
                                                    <div class="mb-1 text-xs text-gray-500">
                                                        {{ getProgressLabel(order) }}
                                                    </div>

                                                    <div class="h-2 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                                        <div
                                                            class="h-full rounded-full bg-primary-600 transition-all"
                                                            :style="{ width: `${getProgress(order)}%` }"
                                                        />
                                                    </div>
                                                </div>
                                            </DataTableCell>

                                            <!-- Actions -->
                                            <DataTableCell align="right">
                                                <div
                                                    class="flex items-center justify-end gap-2"
                                                    @click.stop
                                                >
                                                    <BaseButton
                                                        size="sm"
                                                        variant="secondary"
                                                        @click="viewOrder(order)"
                                                    >
                                                        View
                                                    </BaseButton>

                                                    <BaseButton
                                                        v-if="order.status === 'Draft'"
                                                        size="sm"
                                                        variant="primary"
                                                        @click="editOrder(order)"
                                                    >
                                                        Edit
                                                    </BaseButton>
                                                </div>
                                            </DataTableCell>
                                        </DataTableRow>
                                    </template>

                                    <template v-else>
                                        <DataTableRow>
                                            <DataTableCell colspan="11">
                                                <TableEmpty
                                                    title="No Production Orders Found"
                                                    description="There are no production orders matching your filters."
                                                />
                                            </DataTableCell>
                                        </DataTableRow>
                                    </template>
                                </DataTableBody>
                            </DataTable>
                    </div>

                    <!-- Pagination -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                            <span>Rows per page:</span>

                            <select
                                :value="filterState.per_page"
                                class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white text-sm"
                                @change="changePerPage($event.target.value)"
                            >
                                <option
                                    v-for="option in perPageOptions"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </option>
                            </select>
                        </div>

                        <TablePagination
                            :data="orders"
                            label="Production Order"
                        />
                    </div>
                </div>
            </Card>
        </div>
    </AppLayout>
</template>