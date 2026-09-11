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

import ActionDropdown from '@/Components/Action/ActionDropdown.vue'
import BulkActionDropdown from '@/Components/Bulk/BulkActionDropdown.vue'

import ConfirmDeleteModal from '@/Components/Modal/ConfirmDeleteModal.vue'
import { LoadingOverlay } from '@/Components/Feedback'

import {
    PlusIcon,
} from '@heroicons/vue/24/solid'

import {
    success,
    error,
} from '@/Utils'


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({
    resellers: {
        type: Object,
        required: true,
    },

    stats: {
        type: Object,
        default: () => ({
            total: 0,
            active: 0,
            inactive: 0,
            deleted: 0,
        }),
    },

    filters: {
        type: Object,
        default: () => ({}),
    },
})


/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

const filters = reactive({
    search: props.filters?.search ?? '',
    status: props.filters?.status ?? '',
    per_page: props.filters?.per_page ?? 10,
})


/*
|--------------------------------------------------------------------------
| Sorting
|--------------------------------------------------------------------------
*/

const sort = ref(
    props.filters?.sort ?? 'created_at'
)

const direction = ref(
    props.filters?.direction ?? 'desc'
)


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


/*
|--------------------------------------------------------------------------
| Loading
|--------------------------------------------------------------------------
*/

const loading = ref(false)


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
| Summary
|--------------------------------------------------------------------------
*/

const resellerCode = computed(() => {

    if (props.resellers?.data?.length === 1) {
        return props.resellers.data[0].reseller_code
    }

    return null
})


const resellerName = computed(() => {

    if (props.resellers?.data?.length === 1) {
        return props.resellers.data[0].name
    }

    return 'All Resellers'
})


const activeSummary = ref('total')


const summaryCards = computed(() => [

    {
        key: 'total',
        label: 'Total Reseller',
        value: props.stats?.total ?? 0,
        classes: 'border-gray-100 bg-white',
        accent: 'bg-gray-400',
    },

    {
        key: 'active',
        label: 'Active',
        value: props.stats?.active ?? 0,
        classes: 'border-green-100 bg-green-50/30',
        accent: 'bg-green-500',
    },

    {
        key: 'inactive',
        label: 'Inactive',
        value: props.stats?.inactive ?? 0,
        classes: 'border-yellow-100 bg-yellow-50/30',
        accent: 'bg-yellow-500',
    },

    {
        key: 'deleted',
        label: 'Deleted',
        value: props.stats?.deleted ?? 0,
        classes: 'border-red-100 bg-red-50/30',
        accent: 'bg-red-500',
    },

])


function selectSummary(key) {

    activeSummary.value = key

    if (key === 'total') {
        filters.status = ''
        loadData()
        return
    }

    if (key === 'active') {
        filters.status = 1
        loadData()
        return
    }

    if (key === 'inactive') {
        filters.status = 0
        loadData()
        return
    }

    if (key === 'deleted') {
        return
    }
}


/*
|--------------------------------------------------------------------------
| Data Loading
|--------------------------------------------------------------------------
*/

function loadData() {

    router.get(
        route('resellers.index'),
        {
            search: filters.search,
            status: filters.status,
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


let searchTimeout = null


watch(
    () => filters.search,
    () => {

        clearTimeout(searchTimeout)

        searchTimeout = setTimeout(() => {
            loadData()
        }, 500)

    }
)


watch(
    () => filters.status,
    () => {
        loadData()
    }
)


function refresh() {

    filters.search = ''
    filters.status = ''
    filters.per_page = 10

    sort.value = 'created_at'
    direction.value = 'desc'

    activeSummary.value = 'total'

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

    return (
        props.resellers.data.length > 0 &&
        selectedRows.value.length ===
            props.resellers.data.length
    )

})


const isIndeterminate = computed(() => {

    return (
        selectedRows.value.length > 0 &&
        selectedRows.value.length <
            props.resellers.data.length
    )

})


watch(
    isIndeterminate,
    value => {

        if (selectAllRef.value) {
            selectAllRef.value.indeterminate = value
        }

    },
    {
        immediate: true,
    }
)


function toggleSelectAll(event) {

    selectedRows.value = event.target.checked
        ? props.resellers.data.map(item => item.id)
        : []

}


/*
|--------------------------------------------------------------------------
| CRUD
|--------------------------------------------------------------------------
*/

function create() {
    router.visit(
        route('resellers.create')
    )
}


function showReseller(reseller) {

    router.visit(
        route(
            'resellers.show',
            reseller.id
        )
    )

}


function editReseller(reseller) {

    router.visit(
        route(
            'resellers.edit',
            reseller.id
        )
    )

}


function duplicate(reseller) {

    router.visit(
        route(
            'resellers.duplicate',
            reseller.id
        )
    )

}


/*
|--------------------------------------------------------------------------
| Actions
|--------------------------------------------------------------------------
*/

function handleAction(action, reseller) {

    switch (action) {

        case 'view':
            showReseller(reseller)
            break

        case 'edit':
            editReseller(reseller)
            break

        case 'duplicate':
            duplicate(reseller)
            break

        case 'delete':
            confirmDelete(reseller)
            break

    }

}
/** single delete */

const deleteItem = ref(null)

const showDelete = ref(false)
function openDelete(reseller) {

    deleteItem.value = reseller

    showDelete.value = true
}
const deleteMessage = computed(() => {

    if (!deleteItem.value) {
        return ''
    }

    return `Are you sure you want to delete "${deleteItem.value.name}"?`

})

function closeDelete() {

    deleteItem.value = null

    showDelete.value = false
}

/** end single delete */
function deleteReseller()
{
    console.log(
        route(
            'resellers.destroy',
            deleteItem.value?.id
        )
    )

    if (!deleteItem.value) {
        return
    }

    router.delete(

        route(
            'resellers.destroy',
            deleteItem.value.id
        ),

        {
            preserveScroll: true,

            onSuccess: (page) => {

                closeDelete()

                if (page.props.flash?.success) {
                    success(
                        page.props.flash.success
                    )
                }

                if (page.props.flash?.error) {
                    error(
                        page.props.flash.error
                    )
                }

                if (page.props.flash?.warning) {
                    warning(
                        page.props.flash.warning
                    )
                }

                selectedRows.value = []

            },

            onError: () => {

                error(
                    'Failed to delete Reseller.'
                )

            },

        }
    )
}
/*
|--------------------------------------------------------------------------
| Bulk Actions
|--------------------------------------------------------------------------
*/
const openBulkDelete = () => {

    if (!selectedRows.value.length) {

        return

    }

    showBulkDelete.value = true

}

const openBulkActivate = () => {

    if (!selectedRows.value.length) {

        return

    }

    showBulkActivate.value = true

}

const openBulkDeactivate = () => {

    if (!selectedRows.value.length) {

        return

    }

    showBulkDeactivate.value = true

}

/** bulk delete */

const bulkDelete = () => {

    router.delete(
        route('resellers.bulk-delete'),
        {
            data: {
                ids: selectedRows.value,
            },

            preserveScroll: true,

            onSuccess: (page) => {

                console.log(
                    'FLASH:',
                    page.props.flash
                )

                if (page.props.flash?.success) {
                    success(
                        page.props.flash.success
                    )
                }

                if (page.props.flash?.error) {
                    error(
                        page.props.flash.error
                    )
                }

                if (page.props.flash?.warning) {
                    warning(
                        page.props.flash.warning
                    )
                }

                showBulkDelete.value = false

                selectedRows.value = []

            },
        }
    )

}

const showBulkDelete = ref(false)

const bulkDeleteMessage = computed(() => {

    const total = selectedRows.value.length

    if (total === 0) {

        return ''

    }

    return `Are you sure you want to delete ${total} selected Reseller(s)?`

})

/** end bulk delete */

/** bulk activate */

const bulkActivate = () => {

    router.patch(
        route('resellers.bulk-activate'),
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

const showBulkActivate = ref(false)

const bulkActivateMessage = computed(() => {

    const total = selectedRows.value.length

    if (total === 0) {

        return ''

    }

    return `Are you sure you want to activate ${total} selected Reseller(s)?`

})

/** end bulk activate */

/** bulk deactivate */
const bulkDeactivate = () => {

    console.log(
        'DEACTIVATE URL:',
        route('resellers.bulk-deactivate')
    )

    console.log(
        'SELECTED IDS:',
        selectedRows.value
    )

    router.patch(
        route('resellers.bulk-deactivate'),
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

const showBulkDeactivate = ref(false)

const bulkDeactivateMessage = computed(() => {

    const total = selectedRows.value.length

    if (total === 0) {

        return ''

    }

    return `Are you sure you want to deactivate ${total} selected Reseller(s)?`

})

/** end bulk deactivate */


/*
|--------------------------------------------------------------------------
| Loading Events
|--------------------------------------------------------------------------
*/

let removeStartListener = null
let removeFinishListener = null


onMounted(() => {

    removeStartListener = router.on(
        'start',
        () => {
            loading.value = true
        }
    )

    removeFinishListener = router.on(
        'finish',
        () => {
            loading.value = false
        }
    )

})


onUnmounted(() => {

    clearTimeout(searchTimeout)

    if (removeStartListener) {
        removeStartListener()
    }

    if (removeFinishListener) {
        removeFinishListener()
    }

})
</script>
<template>
<AppLayout>

    <div class="space-y-4">

        <!-- =========================================================
             Header
        ========================================================== -->

        <div
            class="
                flex
                flex-col
                gap-3
                sm:flex-row
                sm:items-center
                sm:justify-between
            "
        >

            <div>

                <h1
                    class="
                        text-xl
                        font-semibold
                        text-gray-900
                    "
                >
                    Reseller
                </h1>

                <p
                    class="
                        mt-1
                        text-sm
                        text-gray-500
                    "
                >
                    Manage reseller master data and consignment partners.
                </p>

            </div>


            <!-- Actions -->

            <div
                class="
                    flex
                    items-center
                    gap-2
                    print:hidden
                "
            >

                <!-- Refresh -->

                <button
                    type="button"
                    class="
                        inline-flex
                        items-center
                        gap-2
                        rounded-lg
                        border
                        border-gray-200
                        bg-white
                        px-3
                        py-2
                        text-sm
                        font-medium
                        text-gray-700
                        transition
                        hover:bg-gray-50
                    "
                    @click="refresh"
                >
                    Refresh
                </button>


                <!-- Export -->

                <button
                    type="button"
                    class="
                        inline-flex
                        items-center
                        gap-2
                        rounded-lg
                        border
                        border-gray-200
                        bg-white
                        px-3
                        py-2
                        text-sm
                        font-medium
                        text-gray-700
                        transition
                        hover:bg-gray-50
                    "
                >
                    Export
                </button>


                <!-- Add Reseller -->

                <button
                    type="button"
                    class="
                        inline-flex
                        items-center
                        gap-2
                        rounded-lg
                        bg-gray-900
                        px-3
                        py-2
                        text-sm
                        font-medium
                        text-white
                        transition
                        hover:bg-gray-800
                    "
                    @click="create"
                >

                    <PlusIcon class="h-4 w-4" />

                    Add Reseller

                </button>

            </div>

        </div>


        <!-- =========================================================
             Reseller Info
        ========================================================== -->

        <div
            class="
                rounded-xl
                border
                border-gray-100
                bg-white
                px-4
                py-3
                shadow-sm
            "
        >

            <div
                class="
                    flex
                    flex-col
                    gap-1
                    sm:flex-row
                    sm:items-center
                    sm:justify-between
                "
            >

                <div>

                    <div
                        class="
                            text-xs
                            font-medium
                            text-gray-500
                        "
                    >
                        Reseller
                    </div>

                    <div
                        class="
                            mt-0.5
                            text-sm
                            font-semibold
                            text-gray-900
                        "
                    >

                        <span
                            v-if="resellerCode"
                            class="mr-1"
                        >
                            {{ resellerCode }} -
                        </span>

                        {{ resellerName }}

                    </div>

                </div>


                <div
                    class="
                        text-sm
                        text-gray-500
                    "
                >
                    Reseller Master Data
                </div>

            </div>

        </div>


        <!-- =========================================================
             Filter
        ========================================================== -->

        <div
            class="
                rounded-xl
                border
                border-gray-100
                bg-white
                p-4
                shadow-sm
                print:hidden
            "
        >

            <div
                class="
                    grid
                    grid-cols-1
                    gap-3
                    md:grid-cols-2
                    lg:grid-cols-3
                "
            >

                <!-- Search -->

                <div>

                    <label
                        class="
                            mb-1
                            block
                            text-xs
                            font-medium
                            text-gray-600
                        "
                    >
                        Search
                    </label>

                    <input
                        v-model="filters.search"
                        type="text"
                        placeholder="Search reseller code or name..."
                        class="
                            w-full
                            rounded-lg
                            border
                            border-gray-200
                            px-3
                            py-2
                            text-sm
                        "
                        @keyup.enter="loadData"
                    />

                </div>


                <!-- Status -->

                <div>

                    <label
                        class="
                            mb-1
                            block
                            text-xs
                            font-medium
                            text-gray-600
                        "
                    >
                        Status
                    </label>

                    <SearchableSelect
                        v-model="filters.status"
                        :options="statusOptions"
                        label="label"
                        value-key="value"
                        placeholder="All Status"
                    />

                </div>


                <!-- Apply -->

                <div
                    class="
                        flex
                        items-end
                    "
                >

                    <button
                        type="button"
                        class="
                            w-full
                            rounded-lg
                            bg-gray-900
                            px-4
                            py-2
                            text-sm
                            font-medium
                            text-white
                            transition
                            hover:bg-gray-800
                        "
                        @click="loadData"
                    >
                        Apply
                    </button>

                </div>

            </div>

        </div>


        <!-- =========================================================
             Summary
        ========================================================== -->

        <div
            class="
                print-summary
                rounded-xl
                border
                border-gray-100
                bg-white
                p-3
                shadow-sm
                print:rounded-none
                print:border-0
                print:p-0
                print:shadow-none
            "
        >

            <div
                class="
                    grid
                    grid-cols-2
                    gap-2
                    sm:grid-cols-4
                "
            >

                <button
                    v-for="card in summaryCards"
                    :key="card.key"
                    type="button"
                    class="
                        print-summary-item
                        relative
                        overflow-hidden
                        rounded-lg
                        border
                        px-4
                        py-3
                        text-left
                        transition
                        hover:-translate-y-px
                        hover:shadow-sm
                    "
                    :class="[
                        card.classes,

                        activeSummary === card.key
                            ? 'ring-2 ring-gray-300 ring-offset-1'
                            : '',
                    ]"
                    @click="selectSummary(card.key)"
                >

                    <span
                        class="
                            absolute
                            inset-y-0
                            left-0
                            w-1
                        "
                        :class="card.accent"
                    ></span>


                    <div
                        class="
                            text-xs
                            font-medium
                            text-gray-500
                        "
                    >
                        {{ card.label }}
                    </div>


                    <div
                        class="
                            mt-1
                            text-base
                            font-semibold
                            tabular-nums
                            text-gray-900
                        "
                    >
                        {{ card.value }}
                    </div>

                </button>

            </div>

        </div>

        <!-- TABLE START DI SINI -->
         <!-- =========================================================
             Reseller Table
        ========================================================== -->

        <div
            class="
                overflow-hidden
                rounded-xl
                border
                border-gray-100
                bg-white
                shadow-sm
            "
        >

            <!-- Bulk Actions -->

            <div
                v-if="selectedRows.length > 0"
                class="
                    flex
                    items-center
                    justify-between
                    border-b
                    border-gray-100
                    bg-gray-50
                    px-4
                    py-3
                "
            >

                <div
                    class="
                        text-sm
                        text-gray-600
                    "
                >
                    <span
                        class="
                            font-medium
                            text-gray-900
                        "
                    >
                        {{ selectedRows.length }}
                    </span>

                    reseller selected
                </div>


                <BulkActionDropdown
                    v-if="selectedRows.length"
                    :count="selectedRows.length"
                    @delete="openBulkDelete"
                    @activate="openBulkActivate"
                    @deactivate="openBulkDeactivate"
                />

            </div>


            <!-- Data Table -->

           <div class="mt-6">

    <!-- Loading -->

    <LoadingOverlay
        :show="loading"
        text="Loading Resellers..."
    />

    <!-- End Loading -->


   <DataTable
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
            column="reseller_code"
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
            width="250px"
        >
            Name
        </DataTableHeaderCell>


        <DataTableHeaderCell
            sortable
            column="contact_person"
            :sort="sort"
            :direction="direction"
            @sort="sortBy"
            width="180px"
        >
            Contact Person
        </DataTableHeaderCell>


        <DataTableHeaderCell
            sortable
            column="phone"
            :sort="sort"
            :direction="direction"
            @sort="sortBy"
            width="150px"
        >
            Phone
        </DataTableHeaderCell>


        <DataTableHeaderCell
            sortable
            column="email"
            :sort="sort"
            :direction="direction"
            @sort="sortBy"
            width="220px"
        >
            Email
        </DataTableHeaderCell>


        <DataTableHeaderCell
            sortable
            column="city"
            :sort="sort"
            :direction="direction"
            @sort="sortBy"
            width="150px"
        >
            City
        </DataTableHeaderCell>


        <DataTableHeaderCell
            sortable
            column="status"
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

        <!-- Data -->

        <DataTableRow
            v-for="item in resellers.data"
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
                {{ item.reseller_code }}
            </DataTableCell>


            <DataTableCell>
                {{ item.name }}
            </DataTableCell>


            <DataTableCell>
                {{ item.contact_person || '-' }}
            </DataTableCell>


            <DataTableCell>
                {{ item.phone || '-' }}
            </DataTableCell>


            <DataTableCell>
                {{ item.email || '-' }}
            </DataTableCell>


            <DataTableCell>
                {{ item.city || '-' }}
            </DataTableCell>


            <DataTableCell align="center">

                <StatusBadge
                    :status="item.status"
                />

            </DataTableCell>


            <DataTableCell align="center">

                <ActionDropdown
                    @view="showReseller(item)"
                    @edit="editReseller(item)"
                    @duplicate="duplicate(item)"
                    @export="exportRow(item)"
                    @delete="openDelete(item)"
                />

            </DataTableCell>

        </DataTableRow>


        <!-- Empty -->

        <DataTableRow
            v-if="!resellers.data.length"
        >

            <DataTableCell
                :colspan="9"
            >

                <TableEmpty
                    title="No Resellers Found"
                    description="There are no reseller records matching your current filters."
                />

            </DataTableCell>

        </DataTableRow>

    </DataTableBody>

</DataTable>

</div>
            <!-- Pagination -->

            <div
                v-if="resellers.data.length > 0"
                class="
                    border-t
                    border-gray-100
                    px-4
                    py-3
                "
            >

               <TablePagination
                    :data="resellers"
                    label="Resellers"
                />

            </div>

        </div>

        <!-- Loading -->

        <LoadingOverlay
            :show="loading"
        />

</div>
</AppLayout>

        <!-- Delete Confirmation -->

<ConfirmDeleteModal
    :show="showDelete"
    title="Delete Reseller"
    :message="deleteMessage"
    @confirm="deleteReseller"
    @close="closeDelete"
/>

<!-- Bulk Delete Confirmation -->

<ConfirmDeleteModal
    :show="showBulkDelete"
    title="Delete Selected Resellers"
    :message="bulkDeleteMessage"
    @confirm="bulkDelete"
    @close="showBulkDelete = false"
/>
<ConfirmDeleteModal
    :show="showBulkActivate"
    title="Bulk Activate"
    :message="bulkActivateMessage"
    confirm-text="Activate"
    confirm-variant="success"
    @close="showBulkActivate = false"
    @confirm="bulkActivate"
/>
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