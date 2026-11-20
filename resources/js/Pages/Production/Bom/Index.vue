<script setup>
import {
    ref,
    reactive,
    computed,
    watch,
    onMounted,
    onUnmounted,
} from 'vue'

import { router } from '@inertiajs/vue3'

import AppLayout from '@/Layouts/AppLayout.vue'
import Card from '@/Components/Layout/Card.vue'
import StatsCard from '@/Components/Card/StatsCard.vue'
import BaseButton from '@/Components/Button/BaseButton.vue'
import DataTable from '@/Components/Table/DataTable.vue'
import DataTableHead from '@/Components/Table/DataTableHead.vue'
import DataTableBody from '@/Components/Table/DataTableBody.vue'
import DataTableHeaderCell from '@/Components/Table/DataTableHeaderCell.vue'
import DataTableRow from '@/Components/Table/DataTableRow.vue'
import DataTableCell from '@/Components/Table/DataTableCell.vue'
import TablePagination from '@/Components/Table/TablePagination.vue'
import TableEmpty from '@/Components/Table/TableEmpty.vue'
import StatusBadge from '@/Components/Display/StatusBadge.vue'
import SearchableSelect from '@/Components/Form/SearchableSelect.vue'
import { LoadingOverlay } from '@/Components/Feedback'
import ActionDropdown from '@/Components/Action/ActionDropdown.vue'
import ConfirmDeleteModal from '@/Components/Modal/ConfirmDeleteModal.vue'

import { success, error, formatDate } from '@/Utils'

/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    boms: {
        type: Object,
        default: () => ({
            data: [],
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
            active: 0,
            draft: 0,
            inactive: 0,
        }),
    },

    branches: {
        type: Array,
        default: () => [],
    },

    variants: {
        type: Array,
        default: () => [],
    },

    filters: {
        type: Object,
        default: () => ({}),
    },

})

/*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/

const pageTitle = computed(() => 'Bill of Materials')

/*
|--------------------------------------------------------------------------
| Loading
|--------------------------------------------------------------------------
*/

const loading = ref(false)

let removeStartListener
let removeFinishListener

function startLoading() {
    loading.value = true
}

function stopLoading() {
    loading.value = false
}

/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

const filterState = reactive({

    search: props.filters?.search ?? '',

    branch_id: props.filters?.branch_id ?? '',

    product_variant_id:
        props.filters?.product_variant_id ?? '',

    bom_type: props.filters?.bom_type ?? '',

    status: props.filters?.status ?? '',

    per_page: Number(props.filters?.per_page ?? 10),

})

let debounceTimer = null

function loadData() {

    router.get(
        route('production-boms.index'),
        {
            ...filterState,
            sort: sort.value,
            direction: direction.value,
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

watch(
    () => filterState.search,
    () => {

        clearTimeout(debounceTimer)

        debounceTimer = setTimeout(() => {
            loadData()
        }, 500)

    }
)

watch(
    () => filterState.branch_id,
    () => loadData()
)

watch(
    () => filterState.product_variant_id,
    () => loadData()
)

watch(
    () => filterState.bom_type,
    () => loadData()
)

watch(
    () => filterState.status,
    () => loadData()
)

watch(
    () => filterState.per_page,
    () => loadData()
)

/*
|--------------------------------------------------------------------------
| Filter Options
|--------------------------------------------------------------------------
*/

const bomTypeOptions = [
    { value: '', label: 'All BOM Types' },
    { value: 'standard', label: 'Standard' },
    { value: 'alternative', label: 'Alternative' },
]

const statusOptions = [
    { value: '', label: 'All Status' },
    { value: 'active', label: 'Active' },
    { value: 'draft', label: 'Draft' },
    { value: 'inactive', label: 'Inactive' },
]

/*
|--------------------------------------------------------------------------
| Refresh
|--------------------------------------------------------------------------
*/

function refresh() {

    clearTimeout(debounceTimer)

    Object.assign(filterState, {
        search: '',
        branch_id: '',
        product_variant_id: '',
        bom_type: '',
        status: '',
        per_page: 10,
    })

    sort.value = 'created_at'
    direction.value = 'desc'

    selectedRows.value = []

    loadData()

}

/*
|--------------------------------------------------------------------------
| Sorting
|--------------------------------------------------------------------------
*/

const sort = ref(props.filters?.sort ?? 'created_at')

const direction = ref(
    props.filters?.direction ?? 'desc'
)

function sortBy(column) {

    if (sort.value === column) {

        direction.value =
            direction.value === 'asc' ? 'desc' : 'asc'

    } else {

        sort.value = column
        direction.value = 'asc'

    }

    loadData()

}

/*
|--------------------------------------------------------------------------
| Selection
|--------------------------------------------------------------------------
*/

const selectedRows = ref([])

const selectAllRef = ref(null)

const isAllSelected = computed(() => {

    const totalRows = props.boms?.data?.length ?? 0

    return (
        totalRows > 0 &&
        selectedRows.value.length === totalRows
    )

})

const isIndeterminate = computed(() => {

    const totalRows = props.boms?.data?.length ?? 0

    return (
        selectedRows.value.length > 0 &&
        selectedRows.value.length < totalRows
    )

})

watch(
    isIndeterminate,
    value => {

        if (selectAllRef.value) {
            selectAllRef.value.indeterminate = value
        }

    },
    { immediate: true }
)

function toggleSelectAll(event) {

    selectedRows.value = event.target.checked
        ? props.boms.data.map(item => item.id)
        : []

}

/*
|--------------------------------------------------------------------------
| Selected BOM / Detail Panel
|--------------------------------------------------------------------------
*/

const selectedBom = ref(null)

function selectBom(item) {

    selectedBom.value = item

}

watch(
    () => props.boms?.data,
    (items) => {

        if (!items?.length) {
            selectedBom.value = null
            return
        }

        const existing = items.find(
            item => item.id === selectedBom.value?.id
        )

        selectedBom.value = existing ?? items[0]

    },
    { immediate: true }
)

/*
|--------------------------------------------------------------------------
| Selected Version
|--------------------------------------------------------------------------
*/

const selectedVersion = computed(() => {

    if (!selectedBom.value) return null

    const versions = selectedBom.value.versions ?? []

    return (
        versions.find(version => version.status === 'active') ??
        versions.find(version => version.status === 'draft') ??
        versions[0] ??
        null
    )

})

const selectedDraftVersion = computed(() => {

    return selectedBom.value?.versions?.find(
        version => version.status === 'draft'
    ) ?? null

})

const materials = computed(() => {

    return selectedVersion.value?.materials ?? []

})

const operations = computed(() => {

    return selectedVersion.value?.operations ?? []

})

const activeVersionLabel = computed(() => {

    if (!selectedVersion.value) return '-'

    return `Version ${selectedVersion.value.version}`

})

const bomStatus = computed(() => {

    return getBomStatus(selectedBom.value)

})

/*
|--------------------------------------------------------------------------
| Display Helpers
|--------------------------------------------------------------------------
*/

function productName(item) {

    return (
        item?.product_variant?.product?.name ??
        item?.product_variant?.name ??
        '-'
    )

}

function variantName(item) {

    return item?.product_variant?.name ?? '-'

}

function variantSku(item) {

    return item?.product_variant?.sku ?? '-'

}

function materialProductName(item) {

    return (
        item?.component_variant?.product?.name ??
        item?.component_variant?.name ??
        '-'
    )

}

function materialVariantName(item) {

    return item?.component_variant?.name ?? '-'

}

function materialSku(item) {

    return item?.component_variant?.sku ?? '-'

}

function operationName(item) {

    return item?.production_operation?.name ?? '-'

}

function unitName(item) {

    return item?.unit?.name ?? '-'

}

function outputUnitName(version) {

    return version?.output_unit?.name ?? '-'

}

/*
|--------------------------------------------------------------------------
| Number Formatting
|--------------------------------------------------------------------------
*/

function numberFormat(value, digits = 4) {

    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 0,
        maximumFractionDigits: digits,
    }).format(Number(value ?? 0))

}

/*
|--------------------------------------------------------------------------
| Currency Formatting
|--------------------------------------------------------------------------
*/

function currencyFormat(value) {

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(Number(value ?? 0))

}

/*
|--------------------------------------------------------------------------
| Gross Quantity
|--------------------------------------------------------------------------
*/

function grossQuantity(material) {

    const qty = Number(material.qty ?? 0)
    const scrap = Number(material.scrap_percent ?? 0)

    if (scrap >= 100) return 0

    return qty / (1 - scrap / 100)

}

/*
|--------------------------------------------------------------------------
| Date
|--------------------------------------------------------------------------
*/

function dateLabel(value) {

    if (!value) return '-'

    return formatDate(value)

}

/*
|--------------------------------------------------------------------------
| Navigation
|--------------------------------------------------------------------------
*/

function create() {

    router.visit(route('production-boms.create'))

}

function openShow(item) {

    router.visit(
        route('production-boms.show', item.id)
    )

}

function edit(item) {

    if (!canEdit(item)) {

        error(
            'Only a Draft BOM can be edited.'
        )

        return

    }

    router.visit(
        route('production-boms.edit', item.id)
    )

}

/*
|--------------------------------------------------------------------------
| Revision
|--------------------------------------------------------------------------
*/

function createRevision(item) {

    if (hasDraftVersion(item)) {

        error(
            'This BOM already has a Draft revision.'
        )

        return

    }

    router.post(
        route('production-boms.revision', item.id),
        {},
        {
            preserveScroll: true,

            onSuccess: () => {

                success(
                    'Success',
                    'BOM revision created successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to create BOM revision.'
                )

            },
        }
    )

}

/*
|--------------------------------------------------------------------------
| Activate Version
|--------------------------------------------------------------------------
*/

function activateVersion(version) {

    if (!version || version.status !== 'draft') {

        error(
            'Only a Draft BOM version can be activated.'
        )

        return

    }

    router.post(
        route(
            'production-boms.activate-version',
            version.id
        ),
        {},
        {
            preserveScroll: true,

            onSuccess: () => {

                success(
                    'Success',
                    'BOM version activated successfully.'
                )

                selectedBom.value = null

            },

            onError: () => {

                error(
                    'Failed to activate BOM version.'
                )

            },
        }
    )

}

/*
|--------------------------------------------------------------------------
| Deactivate
|--------------------------------------------------------------------------
*/

function deactivate(item) {

    if (!item?.is_active) return

    router.post(
        route('production-boms.deactivate', item.id),
        {},
        {
            preserveScroll: true,

            onSuccess: () => {

                success(
                    'Success',
                    'BOM deactivated successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to deactivate BOM.'
                )

            },
        }
    )

}

/*
|--------------------------------------------------------------------------
| Delete
|--------------------------------------------------------------------------
*/

const deleteItem = ref(null)

const showDelete = ref(false)

function canDelete(item) {

    return (
        item?.is_active === false &&
        item?.versions?.length > 0 &&
        item.versions.every(
            version => version.status === 'draft'
        )
    )

}

function openDelete(item) {

    if (!canDelete(item)) {

        error(
            'Only a BOM containing Draft versions can be deleted.'
        )

        return

    }

    deleteItem.value = item
    showDelete.value = true

}

function closeDelete() {

    deleteItem.value = null
    showDelete.value = false

}

const deleteMessage = computed(() => {

    if (!deleteItem.value) return ''

    return `Are you sure you want to delete BOM "${deleteItem.value.code}"?`

})

function confirmDelete() {

    if (!deleteItem.value) return

    router.delete(
        route(
            'production-boms.destroy',
            deleteItem.value.id
        ),
        {
            preserveScroll: true,

            onSuccess: () => {

                const deletedId = deleteItem.value.id

                if (selectedBom.value?.id === deletedId) {
                    selectedBom.value = null
                }

                closeDelete()

                success(
                    'Success',
                    'Draft BOM deleted successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to delete BOM.'
                )

            },
        }
    )

}

/*
|--------------------------------------------------------------------------
| Action Helpers
|--------------------------------------------------------------------------
*/

function hasDraftVersion(item) {

    return item?.versions?.some(
        version => version.status === 'draft'
    ) ?? false

}

function canEdit(item) {

    return hasDraftVersion(item)

}

function canActivate(item) {

    return hasDraftVersion(item)

}

function canDeactivate(item) {

    return Boolean(item?.is_active)

}

/*
|--------------------------------------------------------------------------
| Lifecycle
|--------------------------------------------------------------------------
*/

onMounted(() => {

    removeStartListener = router.on(
        'start',
        startLoading
    )

    removeFinishListener = router.on(
        'finish',
        stopLoading
    )

})

onUnmounted(() => {

    clearTimeout(debounceTimer)

    removeStartListener?.()
    removeFinishListener?.()

})
function getBomStatus(item) {

    if (!item) return 'Inactive'

    const versions = item.versions ?? []

    const hasActiveVersion = versions.some(
        version => version.status === 'active'
    )

    if (item.is_active && hasActiveVersion) {
        return 'Active'
    }

    const hasDraftVersion = versions.some(
        version => version.status === 'draft'
    )

    if (hasDraftVersion) {
        return 'Draft'
    }

    return 'Inactive'
}
</script>

<template>
<AppLayout>

    <div class="space-y-5">

        <!-- ================================================= -->
        <!-- PAGE HEADER -->
        <!-- ================================================= -->

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <h1 class="text-xl font-semibold text-slate-800">
                    Bill of Materials
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Manage product material requirements and production routing.
                </p>
            </div>

            <div class="flex items-center gap-2">

                <BaseButton
                    variant="secondary"
                    @click="refresh"
                >
                    Refresh
                </BaseButton>

                <BaseButton @click="create">
                    + Create BOM
                </BaseButton>

            </div>

        </div>

        <!-- ================================================= -->
        <!-- STATISTICS -->
        <!-- ================================================= -->

        <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">

            <StatsCard
                title="Total BOM"
                :value="statistics?.total ?? 0"
                icon="📋"
            />

            <StatsCard
                title="Active BOM"
                :value="statistics?.active ?? 0"
                icon="✅"
            />

            <StatsCard
                title="Draft BOM"
                :value="statistics?.draft ?? 0"
                icon="📝"
            />

            <StatsCard
                title="Inactive BOM"
                :value="statistics?.inactive ?? 0"
                icon="⏸️"
            />

        </div>

        <!-- ================================================= -->
        <!-- SPLIT PANEL -->
        <!-- ================================================= -->

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-12">

            <!-- ============================================= -->
            <!-- LEFT PANEL: BOM LIST -->
            <!-- ============================================= -->

            <Card class="xl:col-span-5 min-w-0">

                <div class="flex items-center justify-between gap-3 border-b border-slate-200 pb-4">

                    <div>
                        <h2 class="font-semibold text-slate-800">
                            BOM List
                        </h2>

                        <p class="mt-1 text-xs text-slate-500">
                            {{ boms?.total ?? 0 }} records
                        </p>
                    </div>

                    <BaseButton
                        size="sm"
                        variant="secondary"
                        @click="refresh"
                    >
                        Reset
                    </BaseButton>

                </div>

                <!-- Filters -->

                <div class="space-y-3 border-b border-slate-200 py-4">

                    <input
                        v-model="filterState.search"
                        type="text"
                        placeholder="Search BOM code, name, product or SKU..."
                        class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                    />

                    <SearchableSelect
                        v-model="filterState.branch_id"
                        :options="branches"
                        label="label"
                        value-key="id"
                        placeholder="All Branches"
                    />

                    <SearchableSelect
                        v-model="filterState.product_variant_id"
                        :options="variants"
                        label="label"
                        value-key="id"
                        placeholder="All Product Variants"
                    />

                    <div class="grid grid-cols-2 gap-2">

                        <select
                            v-model="filterState.bom_type"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-500"
                        >
                            <option
                                v-for="option in bomTypeOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>

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

                </div>

                <!-- BOM Table -->

                <div class="relative mt-4">

                    <LoadingOverlay
                        :show="loading"
                        text="Loading BOM..."
                    />

                    <DataTable
                        v-if="boms?.data?.length"
                        sticky-header
                        max-height="570px"
                    >

                        <DataTableHead sticky>

                            <DataTableHeaderCell
                                width="42px"
                                align="center"
                            >
                                <input
                                    ref="selectAllRef"
                                    type="checkbox"
                                    :checked="isAllSelected"
                                    @change="toggleSelectAll"
                                    class="rounded border-slate-300"
                                />
                            </DataTableHeaderCell>

                            <DataTableHeaderCell
                                sortable
                                column="code"
                                :sort="sort"
                                :direction="direction"
                                @sort="sortBy"
                                width="155px"
                            >
                                BOM Code
                            </DataTableHeaderCell>

                            <DataTableHeaderCell
                                sortable
                                column="name"
                                :sort="sort"
                                :direction="direction"
                                @sort="sortBy"
                                width="180px"
                            >
                                BOM Name
                            </DataTableHeaderCell>

                            <DataTableHeaderCell width="120px">
                                Type
                            </DataTableHeaderCell>

                            <DataTableHeaderCell width="100px">
                                Status
                            </DataTableHeaderCell>

                        </DataTableHead>

                        <DataTableBody>

                            <DataTableRow
                                v-for="item in boms.data"
                                :key="item.id"
                                @click="selectBom(item)"
                                class="cursor-pointer"
                                :class="selectedBom?.id === item.id ? 'bg-blue-50' : ''"
                            >

                                <DataTableCell align="center">

                                    <input
                                        v-model="selectedRows"
                                        :value="item.id"
                                        type="checkbox"
                                        class="rounded border-slate-300"
                                        @click.stop
                                    />

                                </DataTableCell>

                                <DataTableCell>

                                    <button
                                        type="button"
                                        class="text-left font-semibold text-blue-700 hover:text-blue-900"
                                        @click.stop="selectBom(item)"
                                    >
                                        {{ item.code }}
                                    </button>

                                    <div class="mt-1 text-xs text-slate-500">
                                        {{ item.branch?.name ?? '-' }}
                                    </div>

                                </DataTableCell>

                                <DataTableCell>

                                    <div class="font-medium text-slate-800">
                                        {{ item.name }}
                                    </div>

                                    <div class="mt-1 text-xs text-slate-500">
                                        {{ productName(item) }}
                                    </div>

                                    <div class="text-xs text-slate-400">
                                        {{ variantSku(item) }}
                                    </div>

                                </DataTableCell>

                                <DataTableCell>

                                    <span class="text-xs font-medium uppercase text-slate-600">
                                        {{ item.bom_type }}
                                    </span>

                                </DataTableCell>

                                <DataTableCell>

                                    <StatusBadge
                                        :status="getBomStatus(item)"
                                    />

                                </DataTableCell>

                            </DataTableRow>

                        </DataTableBody>

                    </DataTable>

                    <TableEmpty
                        v-else
                        icon="📋"
                        title="No BOM Found"
                        description="No Bill of Materials matches your current filters."
                    >
                        <template #action>
                            <BaseButton @click="create">
                                Create BOM
                            </BaseButton>
                        </template>
                    </TableEmpty>

                </div>

                <!-- Pagination -->

                <div class="mt-4 border-t border-slate-200 pt-4">

                    <div class="mb-3 flex items-center justify-between">

                        <span class="text-xs text-slate-500">
                            Show per page
                        </span>

                        <select
                            v-model.number="filterState.per_page"
                            class="rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-xs"
                        >
                            <option :value="10">10</option>
                            <option :value="25">25</option>
                            <option :value="50">50</option>
                            <option :value="100">100</option>
                        </select>

                    </div>

                    <TablePagination
                        :data="boms"
                        label="BOM"
                    />

                </div>

            </Card>

            <!-- ============================================= -->
            <!-- RIGHT PANEL: BOM DETAIL -->
            <!-- ============================================= -->

            <Card class="xl:col-span-7 min-w-0">

                <template v-if="selectedBom">

                    <!-- Detail Header -->

                    <div class="flex flex-col gap-3 border-b border-slate-200 pb-4 sm:flex-row sm:items-start sm:justify-between">

                        <div class="min-w-0">

                            <div class="flex flex-wrap items-center gap-2">

                                <h2 class="text-lg font-semibold text-slate-800">
                                    {{ selectedBom.name }}
                                </h2>

                                <StatusBadge :status="bomStatus" />

                            </div>

                            <p class="mt-1 text-sm text-slate-500">
                                {{ selectedBom.code }}
                                ·
                                {{ selectedBom.bom_type }}
                                BOM
                            </p>

                            <p class="mt-1 text-xs text-slate-500">
                                {{ productName(selectedBom) }}
                                ·
                                {{ variantSku(selectedBom) }}
                            </p>

                        </div>

                        <div class="flex flex-wrap items-center gap-2">

                            <BaseButton
                                size="sm"
                                variant="secondary"
                                @click="openShow(selectedBom)"
                            >
                                View
                            </BaseButton>

                            <BaseButton
                                v-if="canEdit(selectedBom)"
                                size="sm"
                                variant="secondary"
                                @click="edit(selectedBom)"
                            >
                                Edit
                            </BaseButton>

                            <BaseButton
                                v-if="canDelete(selectedBom)"
                                size="sm"
                                variant="danger"
                                @click="openDelete(selectedBom)"
                            >
                                Delete
                            </BaseButton>

                        </div>

                    </div>

                    <!-- BOM Attributes -->

                    <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">

                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">

                            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
                                BOM Information
                            </p>

                            <dl class="mt-3 space-y-2 text-sm">

                                <div class="flex justify-between gap-3">
                                    <dt class="text-slate-500">BOM Code</dt>
                                    <dd class="font-medium text-slate-800">
                                        {{ selectedBom.code }}
                                    </dd>
                                </div>

                                <div class="flex justify-between gap-3">
                                    <dt class="text-slate-500">Branch</dt>
                                    <dd class="font-medium text-slate-800">
                                        {{ selectedBom.branch?.name ?? '-' }}
                                    </dd>
                                </div>

                                <div class="flex justify-between gap-3">
                                    <dt class="text-slate-500">BOM Type</dt>
                                    <dd class="font-medium capitalize text-slate-800">
                                        {{ selectedBom.bom_type }}
                                    </dd>
                                </div>

                            </dl>

                        </div>

                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">

                            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
                                Output Configuration
                            </p>

                            <dl class="mt-3 space-y-2 text-sm">

                                <div class="flex justify-between gap-3">
                                    <dt class="text-slate-500">Product</dt>
                                    <dd class="text-right font-medium text-slate-800">
                                        {{ productName(selectedBom) }}
                                    </dd>
                                </div>

                                <div class="flex justify-between gap-3">
                                    <dt class="text-slate-500">Variant / SKU</dt>
                                    <dd class="text-right font-medium text-slate-800">
                                        {{ variantName(selectedBom) }}
                                        <span class="block text-xs text-slate-500">
                                            {{ variantSku(selectedBom) }}
                                        </span>
                                    </dd>
                                </div>

                                <div class="flex justify-between gap-3">
                                    <dt class="text-slate-500">Output Qty</dt>
                                    <dd class="font-medium text-slate-800">
                                        {{ numberFormat(selectedVersion?.output_qty) }}
                                        {{ outputUnitName(selectedVersion) }}
                                    </dd>
                                </div>

                            </dl>

                        </div>

                    </div>

                    <!-- Version Information -->

                    <div class="mt-4 rounded-lg border border-slate-200 p-4">

                        <div class="flex flex-wrap items-center justify-between gap-3">

                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
                                    Current BOM Version
                                </p>

                                <p class="mt-1 font-semibold text-slate-800">
                                    {{ activeVersionLabel }}
                                </p>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">

                                <StatusBadge
                                    v-if="selectedVersion"
                                    :status="selectedVersion.status"
                                />

                                <BaseButton
                                    v-if="selectedDraftVersion"
                                    size="sm"
                                    @click="activateVersion(selectedDraftVersion)"
                                >
                                    Activate Version
                                </BaseButton>

                                <BaseButton
                                    v-if="canDeactivate(selectedBom)"
                                    size="sm"
                                    variant="secondary"
                                    @click="deactivate(selectedBom)"
                                >
                                    Deactivate BOM
                                </BaseButton>

                                <BaseButton
                                    v-if="!hasDraftVersion(selectedBom)"
                                    size="sm"
                                    variant="secondary"
                                    @click="createRevision(selectedBom)"
                                >
                                    + Create Revision
                                </BaseButton>

                            </div>

                        </div>

                        <div class="mt-3 grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">

                            <div>
                                <p class="text-xs text-slate-500">
                                    Effective From
                                </p>
                                <p class="mt-1 font-medium text-slate-700">
                                    {{ dateLabel(selectedVersion?.effective_from) }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-500">
                                    Effective Until
                                </p>
                                <p class="mt-1 font-medium text-slate-700">
                                    {{ dateLabel(selectedVersion?.effective_until) }}
                                </p>
                            </div>

                        </div>

                    </div>

                    <!-- Materials -->

                    <div class="mt-6">

                        <div class="mb-3 flex items-center justify-between">

                            <div>
                                <h3 class="font-semibold text-slate-800">
                                    Material Components
                                </h3>

                                <p class="mt-1 text-xs text-slate-500">
                                    Components required to produce the finished variant.
                                </p>
                            </div>

                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                                {{ materials.length }} Materials
                            </span>

                        </div>

                        <div
                            v-if="materials.length"
                            class="overflow-x-auto rounded-lg border border-slate-200"
                        >

                            <table class="min-w-full divide-y divide-slate-200 text-sm">

                                <thead class="bg-slate-50">

                                    <tr>
                                        <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500">
                                            Component
                                        </th>

                                        <th class="px-3 py-3 text-right text-xs font-semibold text-slate-500">
                                            Net Qty
                                        </th>

                                        <th class="px-3 py-3 text-right text-xs font-semibold text-slate-500">
                                            Scrap %
                                        </th>

                                        <th class="px-3 py-3 text-right text-xs font-semibold text-slate-500">
                                            Gross Qty
                                        </th>

                                        <th class="px-3 py-3 text-left text-xs font-semibold text-slate-500">
                                            Operation
                                        </th>
                                    </tr>

                                </thead>

                                <tbody class="divide-y divide-slate-100 bg-white">

                                    <tr
                                        v-for="material in materials"
                                        :key="material.id"
                                        class="hover:bg-slate-50"
                                    >

                                        <td class="px-3 py-3">

                                            <p class="font-medium text-slate-800">
                                                {{ materialProductName(material) }}
                                            </p>

                                            <p class="mt-0.5 text-xs text-slate-500">
                                                {{ materialVariantName(material) }}
                                                ·
                                                {{ materialSku(material) }}
                                            </p>

                                        </td>

                                        <td class="whitespace-nowrap px-3 py-3 text-right tabular-nums text-slate-700">
                                            {{ numberFormat(material.qty) }}
                                            {{ unitName(material) }}
                                        </td>

                                        <td class="whitespace-nowrap px-3 py-3 text-right tabular-nums text-slate-700">
                                            {{ numberFormat(material.scrap_percent, 2) }}%
                                        </td>

                                        <td class="whitespace-nowrap px-3 py-3 text-right font-medium tabular-nums text-slate-800">
                                            {{ numberFormat(grossQuantity(material)) }}
                                            {{ unitName(material) }}
                                        </td>

                                        <td class="px-3 py-3 text-slate-600">
                                            {{ operationName(material.production_bom_operation) }}
                                        </td>

                                    </tr>

                                </tbody>

                            </table>

                        </div>

                        <div
                            v-else
                            class="rounded-lg border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500"
                        >
                            No material components available.
                        </div>

                    </div>

                    <!-- Production Routing -->

                    <div class="mt-6">

                        <div class="mb-3 flex items-center justify-between">

                            <div>
                                <h3 class="font-semibold text-slate-800">
                                    Production Routing
                                </h3>

                                <p class="mt-1 text-xs text-slate-500">
                                    Ordered production operations.
                                </p>
                            </div>

                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                                {{ operations.length }} Operations
                            </span>

                        </div>

                        <div
                            v-if="operations.length"
                            class="space-y-2"
                        >

                            <div
                                v-for="operation in operations"
                                :key="operation.id"
                                class="flex items-start gap-3 rounded-lg border border-slate-200 p-3"
                            >

                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-100 text-sm font-semibold text-blue-700">
                                    {{ operation.sequence }}
                                </div>

                                <div class="min-w-0 flex-1">

                                    <p class="font-medium text-slate-800">
                                        {{ operationName(operation) }}
                                    </p>

                                    <p
                                        v-if="operation.instructions"
                                        class="mt-1 text-xs text-slate-500"
                                    >
                                        {{ operation.instructions }}
                                    </p>

                                </div>

                                <div class="shrink-0 text-right">

                                    <p class="text-xs text-slate-500">
                                        Planned Duration
                                    </p>

                                    <p class="mt-1 text-sm font-medium text-slate-700">
                                        {{
                                            operation.planned_duration_minutes
                                                ? `${numberFormat(operation.planned_duration_minutes, 2)} min`
                                                : '-'
                                        }}
                                    </p>

                                </div>

                            </div>

                        </div>

                        <div
                            v-else
                            class="rounded-lg border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500"
                        >
                            No production operations available.
                        </div>

                    </div>

                    <!-- Revision History -->

                    <div class="mt-6 border-t border-slate-200 pt-5">

                        <h3 class="font-semibold text-slate-800">
                            Version History
                        </h3>

                        <div class="mt-3 space-y-2">

                            <div
                                v-for="version in selectedBom.versions ?? []"
                                :key="version.id"
                                class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-slate-200 px-3 py-3"
                            >

                                <div>

                                    <p class="text-sm font-medium text-slate-800">
                                        Version {{ version.version }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ version.revision_notes || 'No revision notes' }}
                                    </p>

                                </div>

                                <div class="flex items-center gap-2">

                                    <StatusBadge :status="version.status" />

                                    <BaseButton
                                        v-if="version.status === 'draft'"
                                        size="sm"
                                        variant="secondary"
                                        @click="edit(selectedBom)"
                                    >
                                        Edit
                                    </BaseButton>

                                </div>

                            </div>

                        </div>

                    </div>

                </template>

                <!-- No Selected BOM -->

                <div
                    v-else
                    class="flex min-h-[500px] flex-col items-center justify-center text-center"
                >

                    <div class="text-5xl">
                        📋
                    </div>

                    <h3 class="mt-4 font-semibold text-slate-800">
                        Select a BOM
                    </h3>

                    <p class="mt-2 max-w-sm text-sm text-slate-500">
                        Select a Bill of Materials from the list to view its configuration, materials and production routing.
                    </p>

                    <BaseButton
                        class="mt-5"
                        @click="create"
                    >
                        Create New BOM
                    </BaseButton>

                </div>

            </Card>

        </div>

    </div>

</AppLayout>

<!-- ========================================================= -->
<!-- DELETE MODAL -->
<!-- ========================================================= -->

<ConfirmDeleteModal
    :show="showDelete"
    title="Delete Bill of Materials"
    :message="deleteMessage"
    confirm-text="Delete"
    @close="closeDelete"
    @confirm="confirmDelete"
/>

</template>