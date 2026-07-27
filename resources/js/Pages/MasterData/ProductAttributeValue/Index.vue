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
import { ref, reactive, computed, watch, onMounted, onUnmounted ,toRefs} from 'vue'
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
    currency,
} from '@/Utils'
import {

    LoadingOverlay,

} from '@/Components/Feedback'

import SearchableSelect from '@/Components/Form/SearchableSelect.vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    productAttributeValues: Object,
    statistics: Object,
    filters: Object,
})
const { productAttributeValues, statistics  } = toRefs(props)

const pageTitle = computed(() => 'Product Attribute Value')
const loading = ref(false)
const filters = reactive({

    search: props.filters?.search ?? '',

    is_active: props.filters?.is_active ?? '',

    per_page: props.filters?.per_page ?? 10,

})
let debounceTimer = null

function loadData()
{
    router.get(
        route('product-attribute-values.index'),
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
watch(
    () => filters.per_page,
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

    const totalRows = props.productAttributeValues?.data?.length ?? 0

    return (
        totalRows > 0 &&
        selectedRows.value.length === totalRows
    )

})
const isIndeterminate = computed(() => {

    const totalRows = props.productAttributeValues?.data?.length ?? 0

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

       selectedRows.value = props.productAttributeValues?.data?.map(
    item => item.id

) ?? []

    } else {

        selectedRows.value = []

    }
}
const deleteItem = ref(null)

function create()
{
    router.visit(
        route('product-attribute-values.create')
    )
}

function showProductAttributeValue(productAttributeValue)
{
    router.visit(

        route(
            'product-attribute-values.show',
            productAttributeValue.id
        )

    )
}

function editProductAttributeValue(productAttributeValue)
{
    router.visit(

        route(
            'product-attribute-values.edit',
            productAttributeValue.id
        )

    )
}

function duplicate(productAttributeValue)
{
    router.get(
        route(
            'product-attribute-values.duplicate',
            productAttributeValue.id
        )
    )
}
function openDelete(productAttributeValue)
{
    deleteItem.value = productAttributeValue

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

    return `Are you sure you want to delete "${deleteItem.value.value}"?`

})

/** end single delete */

/** bulk delete */
const bulkDelete = () => {

router.delete(
    route('product-attribute-values.bulk-delete'),
    {
        data: {
            ids: selectedRows.value,
        },
        preserveScroll: true,
            onSuccess: (page) => {

            const flash = page.props.flash

            if (flash?.error) {
                error('Error', flash.error)
                return
            }

            if (flash?.warning) {
                warning('Warning', flash.warning)
            } else {
                success(
                    'Success',
                    flash?.success ?? 'Product Attribute Deleted.'
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

    return `Are you sure you want to delete ${total} selected Product Attribute Value(s)?`

})

/** end bulk delete */

/** bulk activate */
const bulkActivate = () => {

    router.post(
        route('product-attribute-values.bulk-activate'),
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

    return `Are you sure you want to activate ${total} selected Product Attribute Value(s)?`

})
const bulkDeactivate = () => {

    router.post(
        route('product-attribute-values.bulk-deactivate'),
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

    return `Are you sure you want to deactivate ${total} selected Product Attribute Value(s)?`

})

/** end bulk deactivate */


function confirmDelete()
{
    console.log(route('product-attribute-values.destroy',deleteItem.value.id))
    if (!deleteItem.value) {

        return

    }

    router.delete(

        route(
            'product-attribute-values.destroy',
            deleteItem.value.id
        ),

        {

            preserveScroll: true,

            onSuccess: () => {

                closeDelete()

                success(
                    'Success',
                    'Product Attribute Value deleted successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to delete Product Attribute Value.'
                )

            },

        }

    )
}
const sort = ref(
    props.filters.sort_by ?? 'id'
)

const direction = ref(
    props.filters.sort_direction ?? 'desc'
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
            route('product-attribute-values.index'),
            {
                search: filters.search,
                is_active: filters.is_active,
                per_page: filters.per_page,
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
</script>
<template>

    <AppLayout>

        <div
            class="space-y-6"
        >

            <PageHeader

                icon="📂"

                title="Product Attribute Value"

                subtitle="Manage Product Attribute  Values."

            />

            <div
                class="
                    grid
                    grid-cols-5
                    gap-4
                    
                "
            >
                <StatsCard
                    title="Total Product Attribute Values"
                    :value="statistics?.total ?? 0"
                    icon="📂"
                />

                <StatsCard
                    title="Active"
                    :value="statistics?.active ?? 0"
                    icon="✅"
                />

                <StatsCard
                    title="Inactive"
                    :value="statistics?.inactive ?? 0"
                    icon="⛔"
                />

                <StatsCard
                    title="Variant"
                    :value="statistics?.variant ?? 0"
                    icon="🧩"
                />

                <StatsCard
                    title="Required"
                    :value="statistics?.required ?? 0"
                    icon="⭐"
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
                                        placeholder="Search
                                         code or name..."
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
                            text="Loading Product Attribute Values..."
                        />

                        <!-- End Loading -->

                        <DataTable
                            v-if="productAttributeValues?.data?.length"
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
                                    Value
                                </DataTableHeaderCell>
                                    <DataTableHeaderCell
                                    sortable
                                    column="percent"
                                    :sort="sort"
                                    :direction="direction"
                                    @sort="sortBy"
                                    width="100px"
                                >
                                    Display Value
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
                                    v-for="item in productAttributeValues.data"
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
                                        {{ item.value }}
                                    </DataTableCell>
                                    <DataTableCell>
                                        {{ item.display_value }}
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
                                            @view="showProductAttributeValue(item)"

                                            @edit="editProductAttributeValue(item)"

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
                                title="No Product Attribute Values Found"
                                description="There are no Product Attribute Values available."
                            >
                                <template #action>

                                    <BaseButton
                                        @click="router.visit(route('product-attribute-values.create'))"
                                    >
                                        Create Attrivute Value
                                    </BaseButton>

                                </template>
                            </TableEmpty>
                </div>

                    <div
                        class="mt-6"
                    >

                        <TablePagination
                            :data="productAttributeValues"
                            label="Product Attribute Value"
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