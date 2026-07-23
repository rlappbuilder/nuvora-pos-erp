<script setup>


import StatsCard from '@/Components/Card/StatsCard.vue'
import PageHeader from '@/Components/Layout/PageHeader.vue'
import Card from '@/Components/Layout/Card.vue'
import DataTable from '@/Components/Table/DataTable.vue'
import DataTableHead from '@/Components/Table/DataTableHead.vue'
import DataTableBody from '@/Components/Table/DataTableBody.vue'
import DataTableHeaderCell from '@/Components/Table/DataTableHeaderCell.vue'
import DataTableRow from '@/Components/Table/DataTableRow.vue'
import DataTableCell from '@/Components/Table/DataTableCell.vue'
import StatusBadge from '@/Components/Display/StatusBadge.vue'
import { ref, reactive, computed, watch, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import BaseButton from '@/Components/Button/BaseButton.vue'
import BulkActionDropdown from '@/Components/Bulk/BulkActionDropdown.vue'
import TablePagination from '@/Components/Table/TablePagination.vue'
import ConfirmDeleteModal from '@/Components/Modal/ConfirmDeleteModal.vue'
import ActionDropdown from '@/Components/Action/ActionDropdown.vue'
import TableEmpty from '@/Components/Table/TableEmpty.vue'

import {
    FolderIcon,
    DocumentTextIcon,
    ChevronRightIcon,
    ChevronDownIcon,
    PlusIcon,
    PencilSquareIcon,
} from '@heroicons/vue/24/solid'
import {
    success,
    error,
} from '@/Utils'
import {

    LoadingOverlay,

} from '@/Components/Feedback'

import SearchableSelect from '@/Components/Form/SearchableSelect.vue'
import AppLayout from '@/Layouts/AppLayout.vue'



const props = defineProps({

    colors: Object,

    stats: Object,

    filters: Object,

})
const search = ref(props.filters.search ?? '')

const status = ref(props.filters.is_active ?? '')

const perPage = ref(props.filters.per_page ?? 10)


const pageTitle = computed(() => 'Color')
const loading = ref(false)
//const {

 //   loading,
   // showLoading,
    // hideLoading,

//} = useLoading()

const filters = reactive({

    search: props.filters?.search ?? '',

    is_active: props.filters?.is_active ?? '',

    per_page: props.filters?.per_page ?? 10,

})
let debounceTimer = null

function loadData()
{
    router.get(
        route('colors.index'),
        filters,
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    )
}
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
function refresh()
{
    Object.assign(filters, {
        search: '',
        is_active: '',
        per_page: 10,
    })

    loadData()
}
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
    removeStartListener?.()
    removeFinishListener?.()
})
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
const selectedRows = ref([])
const selectAllRef = ref(null)
const isAllSelected = computed(() => {

    return (
        props.colors.data.length > 0 &&
        selectedRows.value.length === props.colors.data.length
    )

})

const isIndeterminate = computed(() => {

    return (
        selectedRows.value.length > 0 &&
        selectedRows.value.length < props.colors.data.length
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

        selectedRows.value = props.colors.data.map(

            item => item.id

        )

    } else {

        selectedRows.value = []

    }
}
const deleteItem = ref(null)

function create()
{
    router.visit(
        route('colors.create')
    )
}

function showColor(colors)
{
    router.visit(

        route(
            'colors.show',
            color.id
        )

    )
}

function editColor(color)
{
    router.visit(

        route(
            'colors.edit',
            color.id
        )

    )
}

function duplicate(color)
{
    router.post(
        route('colors.duplicate', color.id),
        {},
        {
            preserveScroll: true,
        }
    )
}
function openDelete(color)
{
    deleteItem.value = color

    showDelete.value = true
}
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
function closeDelete()
{
    deleteItem.value = null

    showDelete.value = false
}


/** single delete */

const showDelete = ref(false)

const deleteMessage = computed(() => {

    if (!deleteItem.value) {

        return ''

    }

    return `Are you sure you want to delete "${deleteItem.value.name}"?`

})

/** end single delete */

/** bulk delete */
const bulkDelete = () => {

    router.delete(
        route('colors.bulk-delete'),
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
const showBulkDelete = ref(false)
const bulkDeleteMessage = computed(() => {

    const total = selectedRows.value.length

    if (total === 0) {

        return ''

    }

    return `Are you sure you want to delete ${total} selected Color(s)?`

})

/** end bulk delete */

/** bulk activate */
const bulkActivate = () => {

    router.patch(
        route('colors.bulk-activate'),
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

    return `Are you sure you want to activate ${total} selected Color(s)?`

})
const bulkDeactivate = () => {

    router.patch(
        route('colors.bulk-deactivate'),
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
/** end bulk activate */

/** bulk deactivate */
const showBulkDeactivate = ref(false)

const bulkDeactivateMessage = computed(() => {

    const total = selectedRows.value.length

    if (total === 0) {

        return ''

    }

    return `Are you sure you want to deactivate ${total} selected Color(s)?`

})

/** end bulk deactivate */


function confirmDelete()
{
    console.log(route('colors.destroy',deleteItem.value.id))
    if (!deleteItem.value) {

        return

    }

    router.delete(

        route(
            'colors.destroy',
            deleteItem.value.id
        ),

        {

            preserveScroll: true,

            onSuccess: () => {

                closeDelete()

                success(
                    'Color deleted successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to delete color.'
                )

            },

        }

    )
}
const sort = ref(
    props.filters.sort
)

const direction = ref(
    props.filters.direction
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

    router.get(
        route('colors.index'),
        {
            search: search.value,
            status: status.value,
            per_page: perPage.value,
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
</script>
<template>

    <AppLayout>

        <div
            class="space-y-6"
        >

            <PageHeader

                icon="📂"

                title="Color"

                subtitle="Manage product Colors."

            />

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

                    title="Total Color"

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
        <Card class="mt-4">

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
                            md:flex-row
                        "
                    >

                        <!-- Search -->
                       <!--  <Actionbar> -->

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

                                <!-- Left -->

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
                                        placeholder="Search color code or name..."
                                        class="
                                            w-full
                                            lg:w-80
                                            rounded-xl
                                            border
                                            border-gray-300
                                            px-4
                                            py-2.5
                                        "
                                    />

                                    <SearchableSelect
                                        v-model="filters.is_active"
                                        :options="statusOptions"
                                        label="label"
                                        value-key="value"
                                        placeholder="All Status"
                                    />

                                </div>

                                <!-- Right -->
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

                      <!--   </Actionbar> -->
                        <!-- bulkt action-->
                        <!-- end bulk action -->

                    </div>

                    <div
                        class="
                            flex
                            items-center
                            gap-3
                        "
                    >

                    </div>

                </div>
                <!-- table-->
                <div
                    class="mt-6"
                >

                   <!-- Loading -->

                        <LoadingOverlay
                            :show="loading"
                            text="Loading Colors..."
                        />

                        <!-- End Loading -->

                        <DataTable 
                            v-if="colors.data.length"
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
                                    width="250px"
                                >
                                    Name
                                </DataTableHeaderCell>
                                   <DataTableHeaderCell
                                    sortable
                                    column="hex_color"
                                    :sort="sort"
                                    :direction="direction"
                                    @sort="sortBy"
                                    width="120px"
                                >
                                    Hex Color
                                </DataTableHeaderCell>

                                <DataTableHeaderCell
                                    sortable
                                    column="description"
                                    :sort="sort"
                                    :direction="direction"
                                    @sort="sortBy"
                                    width="200px"
                                >
                                    Description
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

                                <DataTableRow
                                    v-for="item in colors.data"
                                    :key="item.id"
                                >

                                    <DataTableCell
                                        align="center"
                                    >

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
                                        {{ item.hex_color }}
                                    </DataTableCell>
                                    <DataTableCell>
                                        {{ item.description || '-' }}
                                    </DataTableCell>

                                    <DataTableCell
                                        align="center"
                                    >

                                        <StatusBadge
                                            :status="item.is_active"
                                        />

                                    </DataTableCell>

                                    <DataTableCell
                                        align="center"
                                    >

                                        <ActionDropdown

                                            @edit="editColor(item)"

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
                                icon="🗂️"
                                title="No Colors Found"
                                description="There are no Colors available."
                            >
                                <template #action>

                                    <BaseButton
                                        @click="router.visit(route('colors.create'))"
                                    >
                                        Create Color
                                    </BaseButton>

                                </template>
                            </TableEmpty>
                </div>

                    <div
                        class="mt-6"
                    >

                        <TablePagination
                            :data="colors"
                            label="Colors"
                        />

                    </div>
            <!-- end table-->
            </Card>
    </AppLayout>
<ConfirmDeleteModal
    :show="showDelete"
    title="Delete Confirmation"
    :message="deleteMessage"
    confirm-text="Delete"
    confirm-variant="danger"
    @close="closeDelete"
    @confirm="confirmDelete"
/>
<ConfirmDeleteModal
    :show="showBulkDelete"
    title="Bulk Delete"
    :message="bulkDeleteMessage"
    confirm-text="Delete"
    confirm-variant="danger"
    @close="showBulkDelete = false"
    @confirm="bulkDelete"
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