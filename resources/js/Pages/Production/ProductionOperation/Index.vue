<script setup>

import {
    ref,
    reactive,
    computed,
    watch,
    onMounted,
    onUnmounted,
} from 'vue'

import { router, useForm } from '@inertiajs/vue3'
import ProductionOperationViewModal
    from './ProductionOperationViewModal.vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import Card from '@/Components/Layout/Card.vue'
import StatsCard from '@/Components/Card/StatsCard.vue'
import BaseButton from '@/Components/Button/BaseButton.vue'
import ActionDropdown from '@/Components/Action/ActionDropdown.vue'
import BulkActionDropdown from '@/Components/Bulk/BulkActionDropdown.vue'
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
import ConfirmDeleteModal from '@/Components/Modal/ConfirmDeleteModal.vue'
import { LoadingOverlay } from '@/Components/Feedback'

import {
    PlusIcon,
    MagnifyingGlassIcon,
} from '@heroicons/vue/24/solid'

import { success, error } from '@/Utils'


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    operations: {
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
            inactive: 0,
        }),
    },

    filters: {
        type: Object,
        default: () => ({}),
    },

    previewNumber: {
        type: String,
        default: '',
    },

})


/*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/

const pageTitle = computed(
    () => 'Production Operations'
)


/*
|--------------------------------------------------------------------------
| Loading
|--------------------------------------------------------------------------
*/

const loading = ref(false)

let removeStartListener
let removeFinishListener

const startLoading = () => {
    loading.value = true
}

const stopLoading = () => {
    loading.value = false
}


/*
|--------------------------------------------------------------------------
| Sorting
|--------------------------------------------------------------------------
*/

const sort = ref(
    props.filters?.sort_by ?? 'sort_order'
)

const direction = ref(
    props.filters?.sort_direction ?? 'asc'
)


function sortBy(column)
{
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


/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

const filters = reactive({

    search:
        props.filters?.search ?? '',

    is_active:
        props.filters?.is_active ?? '',

})

let debounceTimer = null


function loadData()
{
    router.get(

        route('production-operations.index'),

        {
            ...filters,

            per_page: props.filters?.per_page ?? 10,

            sort_by: sort.value,

            sort_direction: direction.value,
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


/*
|--------------------------------------------------------------------------
| Status Options
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


/*
|--------------------------------------------------------------------------
| Refresh
|--------------------------------------------------------------------------
*/

function refresh()
{
    filters.search = ''

    filters.is_active = ''

    sort.value = 'sort_order'

    direction.value = 'asc'

    loadData()
}

const showViewModal = ref(false)
const selectedOperation = ref(null)

const openView = (item) => {
    selectedOperation.value = item
    showViewModal.value = true
}

const closeView = () => {
    showViewModal.value = false
    selectedOperation.value = null
}

const editFromView = (item) => {
    const operation = item ?? selectedOperation.value

    if (!operation) return

    closeView()
    editOperation(operation)
}
/*
|--------------------------------------------------------------------------
| Selection
|--------------------------------------------------------------------------
*/

const selectedRows = ref([])

const selectAllRef = ref(null)


const isAllSelected = computed(() => {

    const totalRows =
        props.operations?.data?.length ?? 0

    return (
        totalRows > 0 &&
        selectedRows.value.length === totalRows
    )

})


const isIndeterminate = computed(() => {

    const totalRows =
        props.operations?.data?.length ?? 0

    return (
        selectedRows.value.length > 0 &&
        selectedRows.value.length < totalRows
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


function toggleSelectAll(event)
{
    if (event.target.checked) {

        selectedRows.value =
            props.operations?.data?.map(
                item => item.id
            ) ?? []

    } else {

        selectedRows.value = []

    }
}


/*
|--------------------------------------------------------------------------
| Clear Selection
|--------------------------------------------------------------------------
*/

watch(

    () => props.operations?.data,

    () => {
        selectedRows.value = []
    }

)


/*
|--------------------------------------------------------------------------
| Form State
|--------------------------------------------------------------------------
*/

const showFormModal = ref(false)

const formMode = ref('create')

const editingItem = ref(null)


const form = useForm({

    code: '',

    name: '',

    description: '',

    sort_order: 0,

    is_active: true,

})


/*
|--------------------------------------------------------------------------
| Reset Form
|--------------------------------------------------------------------------
*/

function resetForm()
{
    form.reset()

    form.clearErrors()

    form.code = ''

    form.name = ''

    form.description = ''

    form.sort_order = 0

    form.is_active = true
}


/*
|--------------------------------------------------------------------------
| Create
|--------------------------------------------------------------------------
*/

function create()
{
    formMode.value = 'create'

    editingItem.value = null

    resetForm()

    form.code = props.previewNumber

    showFormModal.value = true
}

/*
|--------------------------------------------------------------------------
| Edit
|--------------------------------------------------------------------------
*/

function editOperation(item)
{
    editingItem.value = item

    formMode.value = 'edit'

    form.clearErrors()

    form.code = item.code ?? ''

    form.name = item.name ?? ''

    form.description = item.description ?? ''

    form.sort_order = item.sort_order ?? 0

    form.is_active = Boolean(item.is_active)

    showFormModal.value = true
}


/*
|--------------------------------------------------------------------------
| Cancel Form
|--------------------------------------------------------------------------
*/

function cancelForm()
{
    form.clearErrors()

    editingItem.value = null

    showFormModal.value = false
}


/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/

function submit()
{
    if (formMode.value === 'create') {

        form.post(

            route('production-operations.store'),

            {

                preserveScroll: true,

                onSuccess: () => {

                    success(
                        'Success',
                        'Production Operation created successfully.'
                    )

                    showFormModal.value = false

                    resetForm()

                },

                onError: (errors) => {

                    console.error(
                        'CREATE PRODUCTION OPERATION ERRORS:',
                        errors
                    )

                    error(
                        'Failed to create Production Operation.'
                    )

                },

            }

        )

        return
    }


    form.put(

        route(
            'production-operations.update',
            editingItem.value.id
        ),

        {

            preserveScroll: true,

            onSuccess: () => {

                success(
                    'Success',
                    'Production Operation updated successfully.'
                )

                showFormModal.value = false

                editingItem.value = null

            },

            onError: (errors) => {

                console.error(
                    'UPDATE PRODUCTION OPERATION ERRORS:',
                    errors
                )

                error(
                    'Failed to update Production Operation.'
                )

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

function openBulkActivate()
{
    if (!selectedRows.value.length) {
        return
    }

    showBulkActivate.value = true
}

function closeBulkActivate()
{
    showBulkActivate.value = false
}

const bulkActivateMessage = computed(() => {

    const total = selectedRows.value.length

    if (!total) {
        return ''
    }

    return `Are you sure you want to activate ${total} selected Production Operation(s)?`

})

function bulkActivate()
{
    if (!selectedRows.value.length) {
        return
    }

    router.post(
        route('production-operations.bulkActivate'),
        {
            ids: selectedRows.value,
        },
        {
            preserveScroll: true,

            onSuccess: () => {

                closeBulkActivate()

                selectedRows.value = []

                success(
                    'Success',
                    'Production Operations activated successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to activate selected Production Operations.'
                )

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

function openBulkDeactivate()
{
    if (!selectedRows.value.length) {
        return
    }

    showBulkDeactivate.value = true
}

function closeBulkDeactivate()
{
    showBulkDeactivate.value = false
}

const bulkDeactivateMessage = computed(() => {

    const total = selectedRows.value.length

    if (!total) {
        return ''
    }

    return `Are you sure you want to deactivate ${total} selected Production Operation(s)?`

})

function bulkDeactivate()
{
    if (!selectedRows.value.length) {
        return
    }

    router.post(
        route('production-operations.bulkDeactivate'),
        {
            ids: selectedRows.value,
        },
        {
            preserveScroll: true,

            onSuccess: () => {

                closeBulkDeactivate()

                selectedRows.value = []

                success(
                    'Success',
                    'Production Operations deactivated successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to deactivate selected Production Operations.'
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


function openDelete(item)
{
    deleteItem.value = item

    showDelete.value = true
}


function closeDelete()
{
    deleteItem.value = null

    showDelete.value = false
}


const deleteMessage = computed(() => {

    if (!deleteItem.value) {
        return ''
    }

    return `Are you sure you want to delete "${deleteItem.value.code}"?`

})


function confirmDelete()
{
    if (!deleteItem.value) {
        return
    }

    router.delete(

        route(
            'production-operations.destroy',
            deleteItem.value.id
        ),

        {

            preserveScroll: true,

            onSuccess: () => {

                closeDelete()

                success(
                    'Success',
                    'Production Operation deleted successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to delete Production Operation.'
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


function openBulkDelete()
{
    if (!selectedRows.value.length) {
        return
    }

    showBulkDelete.value = true
}


function closeBulkDelete()
{
    showBulkDelete.value = false
}


const bulkDeleteMessage = computed(() => {

    const total = selectedRows.value.length

    if (!total) {
        return ''
    }

    return `Are you sure you want to delete ${total} selected Production Operation(s)?`

})


function bulkDelete()
{
    if (!selectedRows.value.length) {
        return
    }

    router.post(

        route('production-operations.bulkDelete'),

        {
            ids: selectedRows.value,
        },

        {

            preserveScroll: true,

            onSuccess: () => {

                closeBulkDelete()

                selectedRows.value = []

                success(
                    'Success',
                    'Production Operations deleted successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to delete selected Production Operations.'
                )

            },

        }

    )
}


/*
|--------------------------------------------------------------------------
| Selection Helpers
|--------------------------------------------------------------------------
*/

function canEdit()
{
    return true
}


function canDelete()
{
    return true
}


/*
|--------------------------------------------------------------------------
| Lifecycle
|--------------------------------------------------------------------------
*/

onMounted(() => {

    removeStartListener =
        router.on(
            'start',
            startLoading
        )

    removeFinishListener =
        router.on(
            'finish',
            stopLoading
        )

})


onUnmounted(() => {

    clearTimeout(debounceTimer)

    removeStartListener?.()

    removeFinishListener?.()

})

</script>


<template>

<AppLayout>

    <Transition
        name="page"
        mode="out-in"
    >

        <div
            key="list"
            class="space-y-6"
        >

            <!-- ================================================= -->
            <!-- Page Header -->
            <!-- ================================================= -->

            <div
                class="
                    flex
                    flex-col
                    gap-4
                    sm:flex-row
                    sm:items-end
                    sm:justify-between
                "
            >

                <div>

                    <div class="flex flex-wrap items-center gap-2 text-sm">

                        <span class="text-gray-500">
                            Production
                        </span>

                        <span class="text-gray-400">/</span>

                        <span class="text-gray-500">
                            Master Data
                        </span>

                        <span class="text-gray-400">/</span>

                        <span class="font-semibold text-gray-900">
                            Production Operations
                        </span>

                    </div>

                    <h1 class="mt-2 text-2xl font-bold text-gray-900">
                        {{ pageTitle }}
                    </h1>

                    <p class="mt-1 text-sm text-gray-500">
                        Manage production operations and their processing sequence.
                    </p>

                </div>


                <!-- Add Button -->

                <div class="flex shrink-0 items-center">

                    <BaseButton @click="create">

                        <template #icon>

                            <PlusIcon class="h-5 w-5" />

                        </template>

                        Add Operation

                    </BaseButton>

                </div>

            </div>


            <!-- ================================================= -->
            <!-- Statistics -->
            <!-- ================================================= -->

            <div
                class="
                    grid
                    grid-cols-1
                    gap-4
                    md:grid-cols-3
                "
            >

                <!-- Total Operations -->

                <StatsCard
                    title="Total Operations"
                    :value="statistics?.total ?? 0"
                    color="blue"
                >

                    <template #icon>

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="h-7 w-7"
                        >
                            <path d="M12 2v2" />
                            <path d="M12 20v2" />
                            <path d="m4.93 4.93 1.42 1.42" />
                            <path d="m17.65 17.65 1.42 1.42" />
                            <path d="M2 12h2" />
                            <path d="M20 12h2" />
                            <path d="m4.93 19.07 1.42-1.42" />
                            <path d="m17.65 6.35 1.42-1.42" />
                            <circle cx="12" cy="12" r="6" />
                            <circle cx="12" cy="12" r="2" />
                        </svg>

                    </template>

                </StatsCard>


                <!-- Active -->

                <StatsCard
                    title="Active"
                    :value="statistics?.active ?? 0"
                    color="emerald"
                >

                    <template #icon>

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="h-7 w-7"
                        >
                            <circle cx="12" cy="12" r="9" />
                            <path d="m8 12 2.5 2.5L16 9" />
                        </svg>

                    </template>

                </StatsCard>


                <!-- Inactive -->

                <StatsCard
                    title="Inactive"
                    :value="statistics?.inactive ?? 0"
                    color="red"
                >

                    <template #icon>

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="h-7 w-7"
                        >
                            <circle cx="12" cy="12" r="9" />
                            <path d="M9 9l6 6" />
                            <path d="m15 9-6 6" />
                        </svg>

                    </template>

                </StatsCard>

            </div>


            <!-- ================================================= -->
            <!-- List Card -->
            <!-- ================================================= -->

            <Card>

                <!-- ================================================= -->
                <!-- Toolbar -->
                <!-- ================================================= -->

                <div
                    class="
                        flex
                        flex-col
                        gap-3
                        lg:flex-row
                        lg:items-center
                    "
                >

                    <!-- Search -->

                    <div class="relative w-full lg:max-w-md">

                        <MagnifyingGlassIcon
                            class="
                                absolute
                                left-3
                                top-1/2
                                h-5
                                w-5
                                -translate-y-1/2
                                text-slate-400
                            "
                        />

                        <input
                            v-model="filters.search"
                            type="text"
                            placeholder="Search code or operation name..."
                            class="
                                w-full
                                rounded-lg
                                border
                                border-slate-300
                                bg-white
                                py-2.5
                                pl-10
                                pr-3.5
                                text-sm
                                text-slate-700
                                placeholder:text-slate-400
                                outline-none
                                focus:border-blue-500
                                focus:ring-1
                                focus:ring-blue-500
                            "
                        />

                    </div>


                    <!-- Status Filter -->

                    <SearchableSelect
                        v-model="filters.is_active"
                        :options="statusOptions"
                        label="label"
                        value-key="value"
                        placeholder="All Status"
                        class="w-full lg:w-48"
                    />


                    <!-- Actions -->

                    <div
                        class="
                            flex
                            items-center
                            gap-2
                            lg:ml-auto
                        "
                    >

                        <BaseButton
                            variant="secondary"
                            class="shrink-0 whitespace-nowrap"
                            @click="refresh"
                        >
                            Refresh
                        </BaseButton>

                       <BulkActionDropdown
                            :count="selectedRows.length"
                            :disabled="selectedRows.length === 0"
                            @delete="openBulkDelete"
                            @activate="openBulkActivate"
                            @deactivate="openBulkDeactivate"
                        />
                    </div>

                </div>


                <!-- ================================================= -->
                <!-- Table -->
                <!-- ================================================= -->

                <div class="mt-6">

                    <LoadingOverlay
                        :show="loading"
                        text="Loading Production Operations..."
                    />


                    <DataTable
                        v-if="operations?.data?.length"
                        sticky-header
                        max-height="650px"
                    >

                        <DataTableHead sticky>

                            <!-- Select -->

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


                            <!-- Code -->

                            <DataTableHeaderCell
                                sortable
                                column="code"
                                :sort="sort"
                                :direction="direction"
                                @sort="sortBy"
                                width="200px"
                            >
                                Code
                            </DataTableHeaderCell>


                            <!-- Name -->

                            <DataTableHeaderCell
                                sortable
                                column="name"
                                :sort="sort"
                                :direction="direction"
                                @sort="sortBy"
                            >
                                Name
                            </DataTableHeaderCell>


                            <!-- Sorting -->

                            <DataTableHeaderCell
                                sortable
                                column="sort_order"
                                :sort="sort"
                                :direction="direction"
                                @sort="sortBy"
                                width="130px"
                                align="center"
                            >
                                Sorting
                            </DataTableHeaderCell>


                            <!-- Status -->

                            <DataTableHeaderCell
                                sortable
                                column="is_active"
                                :sort="sort"
                                :direction="direction"
                                @sort="sortBy"
                                width="130px"
                                align="center"
                            >
                                Status
                            </DataTableHeaderCell>


                            <!-- Action -->

                            <DataTableHeaderCell
                                width="100px"
                                align="center"
                            >
                                Action
                            </DataTableHeaderCell>

                        </DataTableHead>


                        <DataTableBody>

                            <DataTableRow
                                v-for="item in operations.data"
                                :key="item.id"
                            >

                                <!-- Checkbox -->

                                <DataTableCell align="center">

                                    <input
                                        v-model="selectedRows"
                                        :value="item.id"
                                        type="checkbox"
                                        class="rounded border-gray-300"
                                    />

                                </DataTableCell>


                                <!-- Code -->

                                <DataTableCell>

                                    <span
                                        class="
                                            font-semibold
                                            text-slate-900
                                        "
                                    >
                                        {{ item.code ?? '-' }}
                                    </span>

                                </DataTableCell>


                                <!-- Name -->

                                <DataTableCell>

                                    <span
                                        class="
                                            font-medium
                                            text-slate-800
                                        "
                                    >
                                        {{ item.name ?? '-' }}
                                    </span>

                                </DataTableCell>


                                <!-- Sorting -->

                                <DataTableCell align="center">

                                    <span
                                        class="
                                            text-sm
                                            font-semibold
                                            tabular-nums
                                            text-slate-700
                                        "
                                    >
                                        {{ item.sort_order ?? 0 }}
                                    </span>

                                </DataTableCell>

                                <!-- Status -->

                                <DataTableCell align="center">

                                    <StatusBadge
                                        :status="item.is_active"
                                    />

                                </DataTableCell>

                                <!-- Action -->

                                <DataTableCell align="center">

                                    <ActionDropdown

                                        @view="openView(item)"

                                        @edit="editOperation(item)"

                                        @delete="openDelete(item)"

                                        :showEdit="canEdit(item)"

                                        :showDuplicate="false"

                                        :showPost="false"

                                        :showReject="false"

                                        :showExport="false"

                                        :showHistory="false"

                                        :showDelete="canDelete(item)"

                                    />

                                </DataTableCell>

                            </DataTableRow>

                        </DataTableBody>

                    </DataTable>


                    <!-- ================================================= -->
                    <!-- Empty -->
                    <!-- ================================================= -->

                    <TableEmpty
                        v-else
                        icon="⚙️"
                        title="No Production Operations Found"
                        description="There are no Production Operations available."
                    >

                        <template #action>

                            <BaseButton @click="create">

                                Create Production Operation

                            </BaseButton>

                        </template>

                    </TableEmpty>

                </div>


                <!-- ================================================= -->
                <!-- Pagination -->
                <!-- ================================================= -->

                <div class="mt-6">

                    <TablePagination
                        :data="operations"
                        label="Production Operation"
                    />

                </div>

            </Card>

        </div>

    </Transition>

</AppLayout>


<!-- ========================================================= -->
<!-- Production Operation Form Modal -->
<!-- ========================================================= -->

<Teleport to="body">

    <div
        v-if="showFormModal"
        class="
            fixed
            inset-0
            z-[100]
            flex
            items-center
            justify-center
            bg-black/50
            p-4
        "
        @click.self="cancelForm"
    >

        <div
            class="
                w-full
                max-w-xl
                overflow-hidden
                rounded-xl
                bg-white
                shadow-xl
            "
        >

            <!-- Header -->

            <div
                class="
                    flex
                    items-center
                    justify-between
                    border-b
                    border-slate-200
                    px-6
                    py-4
                "
            >

                <div>

                    <h2 class="text-lg font-semibold text-slate-900">

                        {{
                            formMode === 'create'
                                ? 'Create Production Operation'
                                : 'Edit Production Operation'
                        }}

                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Manage production operation information.
                    </p>

                </div>

                <button
                    type="button"
                    class="
                        rounded-lg
                        p-2
                        text-slate-400
                        hover:bg-slate-100
                        hover:text-slate-700
                    "
                    @click="cancelForm"
                >
                    ✕
                </button>

            </div>


            <!-- Form -->

            <form @submit.prevent="submit">

                <div class="space-y-4 px-6 py-5">

                   <!-- Code -->

                        <div>

                            <label
                                class="
                                    mb-1.5
                                    block
                                    text-sm
                                    font-medium
                                    text-slate-700
                                "
                            >
                                Operation Code
                            </label>

                            <input
                                v-model="form.code"
                                type="text"
                                maxlength="30"
                                readonly
                                class="
                                    w-full
                                    cursor-not-allowed
                                    rounded-lg
                                    border
                                    border-slate-300
                                    bg-slate-100
                                    px-3.5
                                    py-2.5
                                    text-sm
                                    font-semibold
                                    text-slate-700
                                    outline-none
                                "
                            />

                            <p class="mt-1 text-xs text-slate-500">
                                Automatically generated by the system.
                            </p>

                        </div>


                    <!-- Name -->

                    <div>

                        <label
                            class="
                                mb-1.5
                                block
                                text-sm
                                font-medium
                                text-slate-700
                            "
                        >
                            Operation Name
                            <span class="text-red-500">*</span>
                        </label>

                        <input
                            v-model="form.name"
                            type="text"
                            maxlength="100"
                            placeholder="Enter operation name"
                            class="
                                w-full
                                rounded-lg
                                border
                                border-slate-300
                                px-3.5
                                py-2.5
                                text-sm
                                outline-none
                                focus:border-blue-500
                                focus:ring-1
                                focus:ring-blue-500
                            "
                        />

                        <p
                            v-if="form.errors.name"
                            class="mt-1 text-xs text-red-600"
                        >
                            {{ form.errors.name }}
                        </p>

                    </div>


                    <!-- Description -->

                    <div>

                        <label
                            class="
                                mb-1.5
                                block
                                text-sm
                                font-medium
                                text-slate-700
                            "
                        >
                            Description
                        </label>

                        <textarea
                            v-model="form.description"
                            rows="3"
                            placeholder="Enter operation description"
                            class="
                                w-full
                                resize-none
                                rounded-lg
                                border
                                border-slate-300
                                px-3.5
                                py-2.5
                                text-sm
                                outline-none
                                focus:border-blue-500
                                focus:ring-1
                                focus:ring-blue-500
                            "
                        />

                        <p
                            v-if="form.errors.description"
                            class="mt-1 text-xs text-red-600"
                        >
                            {{ form.errors.description }}
                        </p>

                    </div>


                    <!-- Sorting and Status -->

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                        <div>

                            <label
                                class="
                                    mb-1.5
                                    block
                                    text-sm
                                    font-medium
                                    text-slate-700
                                "
                            >
                                Sorting
                            </label>

                            <input
                                v-model.number="form.sort_order"
                                type="number"
                                min="0"
                                class="
                                    w-full
                                    rounded-lg
                                    border
                                    border-slate-300
                                    px-3.5
                                    py-2.5
                                    text-sm
                                    outline-none
                                    focus:border-blue-500
                                    focus:ring-1
                                    focus:ring-blue-500
                                "
                            />

                            <p
                                v-if="form.errors.sort_order"
                                class="mt-1 text-xs text-red-600"
                            >
                                {{ form.errors.sort_order }}
                            </p>

                        </div>


                        <div>

                            <label
                                class="
                                    mb-1.5
                                    block
                                    text-sm
                                    font-medium
                                    text-slate-700
                                "
                            >
                                Status
                            </label>

                            <select
                                v-model="form.is_active"
                                class="
                                    w-full
                                    rounded-lg
                                    border
                                    border-slate-300
                                    bg-white
                                    px-3.5
                                    py-2.5
                                    text-sm
                                    outline-none
                                    focus:border-blue-500
                                    focus:ring-1
                                    focus:ring-blue-500
                                "
                            >

                                <option :value="true">
                                    Active
                                </option>

                                <option :value="false">
                                    Inactive
                                </option>

                            </select>

                            <p
                                v-if="form.errors.is_active"
                                class="mt-1 text-xs text-red-600"
                            >
                                {{ form.errors.is_active }}
                            </p>

                        </div>

                    </div>

                </div>


                <!-- Footer -->

                <div
                    class="
                        flex
                        justify-end
                        gap-2
                        border-t
                        border-slate-200
                        px-6
                        py-4
                    "
                >

                    <BaseButton
                        type="button"
                        variant="secondary"
                        :disabled="form.processing"
                        @click="cancelForm"
                    >
                        Cancel
                    </BaseButton>

                    <BaseButton
                        type="submit"
                        :disabled="form.processing"
                    >
                        {{
                            form.processing
                                ? 'Saving...'
                                : 'Save'
                        }}
                    </BaseButton>

                </div>

            </form>

        </div>

    </div>

</Teleport>


<!-- ========================================================= -->
<!-- Delete -->
<!-- ========================================================= -->

<ConfirmDeleteModal

    :show="showDelete"

    title="Delete Production Operation"

    :message="deleteMessage"

    confirm-text="Delete"

    @close="closeDelete"

    @confirm="confirmDelete"

/>


<!-- ========================================================= -->
<!-- Bulk Delete -->
<!-- ========================================================= -->

<ConfirmDeleteModal

    :show="showBulkDelete"

    title="Delete Production Operations"

    :message="bulkDeleteMessage"

    confirm-text="Delete Selected"

    @close="closeBulkDelete"

    @confirm="bulkDelete"

/>
<!-- ========================================================= -->
<!-- Bulk Activate -->
<!-- ========================================================= -->

<ConfirmDeleteModal
    :show="showBulkActivate"
    title="Activate Production Operations"
    :message="bulkActivateMessage"
    confirm-text="Activate Selected"
    @close="closeBulkActivate"
    @confirm="bulkActivate"
/>


<!-- ========================================================= -->
<!-- Bulk Deactivate -->
<!-- ========================================================= -->

<ConfirmDeleteModal
    :show="showBulkDeactivate"
    title="Deactivate Production Operations"
    :message="bulkDeactivateMessage"
    confirm-text="Deactivate Selected"
    @close="closeBulkDeactivate"
    @confirm="bulkDeactivate"
/>
<!-- ========================================================= -->
<!-- Production Operation View Modal -->
<!-- ========================================================= -->

<ProductionOperationViewModal
    :show="showViewModal"
    :operation="selectedOperation"
    :processing="form.processing"
    @close="closeView"
    @edit="editFromView"
/>
</template>


<style scoped>

.page-enter-active,
.page-leave-active {

    transition:
        opacity 0.2s ease,
        transform 0.2s ease;

}

.page-enter-from {

    opacity: 0;

    transform: translateY(8px);

}

.page-leave-to {

    opacity: 0;

    transform: translateY(-8px);

}

</style>