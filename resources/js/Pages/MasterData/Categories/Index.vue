<script setup>
import { Head, router } from '@inertiajs/vue3'
import {
    ref,
    reactive,
    computed,
    watch,
    onMounted,
    onUnmounted,
} from 'vue'

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
import ConfirmDeleteModal from '@/Components/Modal/ConfirmDeleteModal.vue'
import ActionDropdown from '@/Components/Action/ActionDropdown.vue'
import SearchableSelect from '@/Components/Form/SearchableSelect.vue'

import {
    PlusIcon,
} from '@heroicons/vue/24/solid'

import {
    success,
    error,
} from '@/Utils'

import {
    LoadingOverlay,
} from '@/Components/Feedback'

import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    categories: Object,
    stats: Object,
    filters: Object,
})

/*
|--------------------------------------------------------------------------
| Page State
|--------------------------------------------------------------------------
*/

const pageTitle = computed(() => 'Category')

const loading = ref(false)

const filters = reactive({
    search: props.filters?.search ?? '',
    is_active: props.filters?.is_active ?? '',
    category_type: props.filters?.category_type ?? '',
    per_page: props.filters?.per_page ?? 10,
})

const sort = ref(props.filters?.sort ?? 'code')
const direction = ref(props.filters?.direction ?? 'asc')

let debounceTimer = null

/*
|--------------------------------------------------------------------------
| Filter Options
|--------------------------------------------------------------------------
*/

const statusOptions = [
    {
        value: '',
        label: 'All Status',
    },
    {
        value: 1,
        label: 'Active',
    },
    {
        value: 0,
        label: 'Inactive',
    },
]

const categoryTypeOptions = [
    {
        value: '',
        label: 'All Category Types',
    },
    {
        value: 'raw_material',
        label: 'Raw Material',
    },
    {
        value: 'finished_goods',
        label: 'Finished Goods',
    },
]

/*
|--------------------------------------------------------------------------
| Category Type Label
|--------------------------------------------------------------------------
*/

function categoryTypeLabel(type) {
    const labels = {
        raw_material: 'Raw Material',
        finished_goods: 'Finished Goods',
    }

    return labels[type] ?? '-'
}

/*
|--------------------------------------------------------------------------
| Load Data
|--------------------------------------------------------------------------
*/

function loadData() {
    router.get(
        route('categories.index'),
        {
            search: filters.search,
            is_active: filters.is_active,
            category_type: filters.category_type,
            per_page: filters.per_page,
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
    () => filters.search,
    () => {
        clearTimeout(debounceTimer)

        debounceTimer = setTimeout(() => {
            loadData()
        }, 500)
    }
)

watch(
    () => filters.is_active,
    () => {
        loadData()
    }
)

watch(
    () => filters.category_type,
    () => {
        loadData()
    }
)

/*
|--------------------------------------------------------------------------
| Refresh
|--------------------------------------------------------------------------
*/

function refresh() {
    clearTimeout(debounceTimer)

    Object.assign(filters, {
        search: '',
        is_active: '',
        category_type: '',
        per_page: 10,
    })

    sort.value = 'code'
    direction.value = 'asc'

    loadData()
}

/*
|--------------------------------------------------------------------------
| Loading Overlay
|--------------------------------------------------------------------------
*/

const startLoading = () => {
    loading.value = true
}

const stopLoading = () => {
    loading.value = false
}

let removeStartListener
let removeFinishListener

onMounted(() => {
    removeStartListener = router.on('start', startLoading)
    removeFinishListener = router.on('finish', stopLoading)
})

onUnmounted(() => {
    clearTimeout(debounceTimer)

    removeStartListener?.()
    removeFinishListener?.()
})

/*
|--------------------------------------------------------------------------
| Row Selection
|--------------------------------------------------------------------------
*/

const selectedRows = ref([])
const selectAllRef = ref(null)

const isAllSelected = computed(() => {
    return (
        props.categories.data.length > 0 &&
        selectedRows.value.length === props.categories.data.length
    )
})

const isIndeterminate = computed(() => {
    return (
        selectedRows.value.length > 0 &&
        selectedRows.value.length < props.categories.data.length
    )
})

watch(
    isIndeterminate,
    (value) => {
        if (selectAllRef.value) {
            selectAllRef.value.indeterminate = value
        }
    },
    {
        immediate: true,
    }
)

function toggleSelectAll(event) {
    if (event.target.checked) {
        selectedRows.value = props.categories.data.map(
            item => item.id
        )
    } else {
        selectedRows.value = []
    }
}

/*
|--------------------------------------------------------------------------
| Navigation
|--------------------------------------------------------------------------
*/

function create() {
    router.visit(route('categories.create'))
}

function showCategory(category) {
    router.visit(
        route('categories.show', category.id)
    )
}

function editCategory(category) {
    router.visit(
        route('categories.edit', category.id)
    )
}

function duplicate(category) {
    router.get(
        route('categories.duplicate', category.id)
    )
}

/*
|--------------------------------------------------------------------------
| Single Delete
|--------------------------------------------------------------------------
*/

const deleteItem = ref(null)
const showDelete = ref(false)

const deleteMessage = computed(() => {
    if (!deleteItem.value) {
        return ''
    }

    return `Are you sure you want to delete "${deleteItem.value.name}"?`
})

function openDelete(category) {
    deleteItem.value = category
    showDelete.value = true
}

function closeDelete() {
    deleteItem.value = null
    showDelete.value = false
}

function confirmDelete() {
    if (!deleteItem.value) {
        return
    }

    router.delete(
        route('categories.destroy', deleteItem.value.id),
        {
            preserveScroll: true,

            onSuccess: () => {
                closeDelete()
                selectedRows.value = []

                success(
                    'Category deleted successfully.'
                )
            },

            onError: () => {
                error(
                    'Failed to delete category.'
                )
            },
        }
    )
}

/*
|--------------------------------------------------------------------------
| Bulk Delete
|--------------------------------------------------------------------------
*/

const showBulkDelete = ref(false)

const bulkDeleteMessage = computed(() => {
    const total = selectedRows.value.length

    if (total === 0) {
        return ''
    }

    return `Are you sure you want to delete ${total} selected Category(s)?`
})

const openBulkDelete = () => {
    if (!selectedRows.value.length) {
        return
    }

    showBulkDelete.value = true
}

const bulkDelete = () => {
    router.delete(
        route('categories.bulk-delete'),
        {
            data: {
                ids: selectedRows.value,
            },

            preserveScroll: true,

            onSuccess: () => {
                showBulkDelete.value = false
                selectedRows.value = []
            },
        }
    )
}

/*
|--------------------------------------------------------------------------
| Bulk Activate
|--------------------------------------------------------------------------
*/

const showBulkActivate = ref(false)

const bulkActivateMessage = computed(() => {
    const total = selectedRows.value.length

    if (total === 0) {
        return ''
    }

    return `Are you sure you want to activate ${total} selected Category(s)?`
})

const openBulkActivate = () => {
    if (!selectedRows.value.length) {
        return
    }

    showBulkActivate.value = true
}

const bulkActivate = () => {
    router.patch(
        route('categories.bulk-activate'),
        {
            ids: selectedRows.value,
        },
        {
            preserveScroll: true,

            onSuccess: () => {
                showBulkActivate.value = false
                selectedRows.value = []
            },
        }
    )
}

/*
|--------------------------------------------------------------------------
| Bulk Deactivate
|--------------------------------------------------------------------------
*/

const showBulkDeactivate = ref(false)

const bulkDeactivateMessage = computed(() => {
    const total = selectedRows.value.length

    if (total === 0) {
        return ''
    }

    return `Are you sure you want to deactivate ${total} selected Category(s)?`
})

const openBulkDeactivate = () => {
    if (!selectedRows.value.length) {
        return
    }

    showBulkDeactivate.value = true
}

const bulkDeactivate = () => {
    router.patch(
        route('categories.bulk-deactivate'),
        {
            ids: selectedRows.value,
        },
        {
            preserveScroll: true,

            onSuccess: () => {
                showBulkDeactivate.value = false
                selectedRows.value = []
            },
        }
    )
}

/*
|--------------------------------------------------------------------------
| Sorting
|--------------------------------------------------------------------------
*/

function sortBy(column) {
    if (sort.value === column) {
        direction.value =
            direction.value === 'asc'
                ? 'desc'
                : 'asc'
    } else {
        sort.value = column
        direction.value = 'asc'
    }

    loadData()
}

</script>

<template>
    <Head :title="pageTitle" />

    <AppLayout>
        <div class="space-y-6">

            <PageHeader
                icon="📂"
                title="Category"
                subtitle="Manage product categories."
            />

            <!-- ===================================================== -->
            <!-- Statistics -->
            <!-- ===================================================== -->

            <div
                class="
                    grid
                    grid-cols-1
                    gap-6
                    md:grid-cols-2
                    xl:grid-cols-4
                "
            >
                <StatsCard
                    title="Total Category"
                    :value="stats.total"
                    icon="📂"
                />

                <StatsCard
                    title="Active"
                    :value="stats.active"
                    icon="✅"
                />

                <StatsCard
                    title="Inactive"
                    :value="stats.inactive"
                    icon="⛔"
                />

                <StatsCard
                    title="Deleted"
                    :value="stats.deleted"
                    icon="🗑️"
                />
            </div>

        </div>

        <!-- ========================================================= -->
        <!-- Category Table -->
        <!-- ========================================================= -->

        <Card class="mt-4">

            <!-- ===================================================== -->
            <!-- Toolbar -->
            <!-- ===================================================== -->

            <div
                class="
                    flex
                    flex-col
                    gap-4
                    lg:flex-row
                    lg:items-center
                    lg:justify-between
                "
            >

                <div
                    class="
                        flex
                        flex-1
                        flex-col
                        gap-4
                        lg:flex-row
                        lg:items-center
                    "
                >

                    <!-- Search and Filters -->

                    <div
                        class="
                            flex
                            flex-col
                            gap-3
                            lg:flex-row
                            lg:items-center
                        "
                    >

                        <input
                            v-model="filters.search"
                            type="text"
                            placeholder="Search category code or name..."
                            class="
                                w-full
                                lg:w-72
                                rounded-xl
                                border
                                border-gray-300
                                px-4
                                py-2.5
                            "
                        />

                        <SearchableSelect
                            v-model="filters.category_type"
                            :options="categoryTypeOptions"
                            label="label"
                            value-key="value"
                            placeholder="All Category Types"
                        />

                        <SearchableSelect
                            v-model="filters.is_active"
                            :options="statusOptions"
                            label="label"
                            value-key="value"
                            placeholder="All Status"
                        />

                    </div>

                    <!-- Actions -->

                    <div
                        class="
                            flex
                            flex-wrap
                            items-center
                            justify-end
                            gap-2
                        "
                    >

                        <BaseButton
                            variant="secondary"
                            @click="refresh"
                        >
                            Refresh
                        </BaseButton>

                        <BaseButton
                            variant="secondary"
                        >
                            Export
                        </BaseButton>

                        <BaseButton
                            class="w-full md:w-auto"
                            @click="create"
                        >
                            <template #icon>
                                <PlusIcon class="h-5 w-5" />
                            </template>

                            Add
                        </BaseButton>

                        <BulkActionDropdown
                            v-if="selectedRows.length"
                            :count="selectedRows.length"
                            @delete="openBulkDelete"
                            @activate="openBulkActivate"
                            @deactivate="openBulkDeactivate"
                        />

                    </div>

                </div>

            </div>

            <!-- ===================================================== -->
            <!-- Table -->
            <!-- ===================================================== -->

            <div class="mt-6">

                <LoadingOverlay
                    :show="loading"
                    text="Loading Categories..."
                />

                <DataTable
                    v-if="categories.data.length"
                    sticky-header
                    max-height="650px"
                >

                    <DataTableHead sticky>

                        <DataTableHeaderCell
                            width="60px"
                            align="center"
                        >
                            <input
                                ref="selectAllRef"
                                type="checkbox"
                                :checked="isAllSelected"
                                @change="toggleSelectAll"
                                class="rounded border-gray-300"
                            />
                        </DataTableHeaderCell>

                        <DataTableHeaderCell
                            sortable
                            column="code"
                            :sort="sort"
                            :direction="direction"
                            @sort="sortBy"
                            width="150px"
                        >
                            Code
                        </DataTableHeaderCell>

                        <DataTableHeaderCell
                            sortable
                            column="name"
                            :sort="sort"
                            :direction="direction"
                            @sort="sortBy"
                            width="220px"
                        >
                            Name
                        </DataTableHeaderCell>

                        <DataTableHeaderCell
                            sortable
                            column="category_type"
                            :sort="sort"
                            :direction="direction"
                            @sort="sortBy"
                            width="170px"
                        >
                            Category Type
                        </DataTableHeaderCell>

                        <DataTableHeaderCell
                            sortable
                            column="description"
                            :sort="sort"
                            :direction="direction"
                            @sort="sortBy"
                            width="250px"
                        >
                            Description
                        </DataTableHeaderCell>

                        <DataTableHeaderCell
                            sortable
                            column="is_active"
                            :sort="sort"
                            :direction="direction"
                            @sort="sortBy"
                            width="120px"
                            align="center"
                        >
                            Status
                        </DataTableHeaderCell>

                        <DataTableHeaderCell
                            width="100px"
                            align="center"
                        >
                            Actions
                        </DataTableHeaderCell>

                    </DataTableHead>

                    <DataTableBody>

                        <DataTableRow
                            v-for="item in categories.data"
                            :key="item.id"
                        >

                            <DataTableCell align="center">
                                <input
                                    v-model="selectedRows"
                                    :value="item.id"
                                    type="checkbox"
                                    class="rounded border-gray-300"
                                />
                            </DataTableCell>

                            <DataTableCell>
                                {{ item.code }}
                            </DataTableCell>

                            <DataTableCell>
                                {{ item.name }}
                            </DataTableCell>

                            <DataTableCell>
                                <span
                                    class="
                                        inline-flex
                                        items-center
                                        rounded-full
                                        px-3
                                        py-1
                                        text-xs
                                        font-medium
                                    "
                                    :class="
                                        item.category_type === 'raw_material'
                                            ? 'bg-amber-100 text-amber-700'
                                            : 'bg-emerald-100 text-emerald-700'
                                    "
                                >
                                    {{ categoryTypeLabel(item.category_type) }}
                                </span>
                            </DataTableCell>

                            <DataTableCell>
                                {{ item.description || '-' }}
                            </DataTableCell>

                            <DataTableCell align="center">
                                <StatusBadge
                                    :status="item.is_active"
                                />
                            </DataTableCell>

                            <DataTableCell align="center">
                                <ActionDropdown
                                    @edit="editCategory(item)"
                                    @duplicate="duplicate(item)"
                                    @export="exportRow(item)"
                                    @delete="openDelete(item)"
                                />
                            </DataTableCell>

                        </DataTableRow>

                    </DataTableBody>

                </DataTable>

                <TableEmpty
                    v-else
                    icon="📂"
                    title="No Categories Found"
                    description="There are no categories available."
                >
                    <template #action>
                        <BaseButton @click="create">
                            Create Category
                        </BaseButton>
                    </template>
                </TableEmpty>

            </div>

            <!-- ===================================================== -->
            <!-- Pagination -->
            <!-- ===================================================== -->

            <div class="mt-6">

                <TablePagination
                    :data="categories"
                    label="Categories"
                />

            </div>

        </Card>

    </AppLayout>

    <!-- ============================================================= -->
    <!-- Single Delete Modal -->
    <!-- ============================================================= -->

    <ConfirmDeleteModal
        :show="showDelete"
        title="Delete Confirmation"
        :message="deleteMessage"
        confirm-text="Delete"
        confirm-variant="danger"
        @close="closeDelete"
        @confirm="confirmDelete"
    />

    <!-- ============================================================= -->
    <!-- Bulk Delete Modal -->
    <!-- ============================================================= -->

    <ConfirmDeleteModal
        :show="showBulkDelete"
        title="Bulk Delete"
        :message="bulkDeleteMessage"
        confirm-text="Delete"
        confirm-variant="danger"
        @close="showBulkDelete = false"
        @confirm="bulkDelete"
    />

    <!-- ============================================================= -->
    <!-- Bulk Activate Modal -->
    <!-- ============================================================= -->

    <ConfirmDeleteModal
        :show="showBulkActivate"
        title="Bulk Activate"
        :message="bulkActivateMessage"
        confirm-text="Activate"
        confirm-variant="success"
        @close="showBulkActivate = false"
        @confirm="bulkActivate"
    />

    <!-- ============================================================= -->
    <!-- Bulk Deactivate Modal -->
    <!-- ============================================================= -->

    <ConfirmDeleteModal
        :show="showBulkDeactivate"
        title="Bulk Deactivate"
        :message="bulkDeactivateMessage"
        confirm-text="Deactivate"
        confirm-variant="warning"
        @close="showBulkDeactivate = false"
        @confirm="bulkDeactivate"
    />

</template>