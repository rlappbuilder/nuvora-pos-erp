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
import { ref, reactive, computed, watch, onMounted, onUnmounted ,toRefs,nextTick,} from 'vue'
import { router } from '@inertiajs/vue3'
import BulkActionDropdown from '@/Components/Bulk/BulkActionDropdown.vue'
import TablePagination from '@/Components/Table/TablePagination.vue'
import ConfirmDeleteModal from '@/Components/Modal/ConfirmDeleteModal.vue'
import ActionDropdown from '@/Components/Action/ActionDropdown.vue'
import TableEmpty from '@/Components/Table/TableEmpty.vue'
import BaseButton from '@/Components/Button/BaseButton.vue'


import {
    ArrowPathIcon,
    PlusIcon,
    ArrowDownTrayIcon, 
    
} from '@heroicons/vue/24/solid'
import {
    QrCodeIcon,
    CubeIcon,
    CheckCircleIcon,
    XCircleIcon,
} from '@heroicons/vue/24/solid'
import { ClockIcon,ArchiveBoxIcon, } from '@heroicons/vue/24/outline'
import { success,error,loading as showLoading,closeLoading,confirmRegenerate} from '@/Utils'
import { LoadingOverlay,} from '@/Components/Feedback'
import { warning } from '@/Utils/swal'
import SearchableSelect from '@/Components/Form/SearchableSelect.vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link } from '@inertiajs/vue3'
import CreateModal
from './Components/CreateModal.vue'
import FormModal from './Components/FormModal.vue'
const props = defineProps({

    units: Object,

    variants: Array,

    unitOptions: Array,

    statistics: Object,

    filters: Object,

    products: Array,

})

const {

    units,

    variants,

    statistics,

    products,

} = toRefs(props)
const selectedItem = ref(null)
const pageTitle = computed(() => 'Product Variant Units')
const loading = ref(false)
const filters = reactive({

    search:

        props.filters?.search ?? '',

    product_id:

        props.filters?.product_id ?? '',

    is_active:

        props.filters?.is_active ?? '',

    per_page:

        props.filters?.per_page ?? 20,

})
let debounceTimer = null

function loadData()
{
    router.get(
        route('product-variant-units.index'),
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
watch(
    () => filters.product_id,
    loadData
)

function refresh()
{
    Object.assign(filters, {

        search: '',

        product_id: '',

        is_active: '',

        per_page: 20,

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

    const totalRows = props.units?.data?.length ?? 0

    return (
        totalRows > 0 &&
        selectedRows.value.length === totalRows
    )

})
const isIndeterminate = computed(() => {

    const totalRows = props.units?.data?.length ?? 0

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

       selectedRows.value = props.units?.data?.map(
    item => item.id

) ?? []

    } else {

        selectedRows.value = []

    }
}

const deleteItem = ref(null)
//Modal GenerateVarian
const showCreateModal = ref(false)
const showModal = ref(false)
function create()
{
    selectedItem.value = null

    showModal.value = true
}

function closeModal()
{
    showModal.value = false

    selectedItem.value = null
}
function closeCreate()
{
    showCreateModal.value = false
}
function show(unit)
{
    router.visit(

        route(
            'product-variant-units.show',
            product.id
        )

    )
}

function edit(item)
{
    selectedItem.value = item

    showModal.value = true
}

function openDelete(unit)
{
    deleteItem.value = unit

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

    router.post(

        route('product-variant-units.bulk-delete'),

        {

            ids: selectedRows.value,

        },

        {

            preserveScroll: true,

            onSuccess: (page) => {

                if (page.props.flash?.success) {
                    success(page.props.flash.success)
                }

                if (page.props.flash?.error) {
                    error(page.props.flash.error)
                }

                if (page.props.flash?.warning) {
                    warning(page.props.flash.warning)
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

    return `Are you sure you want to delete ${total} selected Product units(s)?`

})

/** end bulk delete */

/** bulk activate */
const bulkActivate = () => {

    router.post(

        route('product-variant-units.bulk-activate'),

        {

            ids: selectedRows.value,

        },

        {

            preserveScroll: true,

            onSuccess: (page) => {

                if (page.props.flash?.success) {
                    success(page.props.flash.success)
                }

                if (page.props.flash?.error) {
                    error(page.props.flash.error)
                }

                if (page.props.flash?.warning) {
                    warning(page.props.flash.warning)
                }

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

    return `Are you sure you want to activate ${total} selected Product units (s)?`

})
const bulkDeactivate = () => {

    router.post(

        route('product-variant-units.bulk-deactivate'),

        {

            ids: selectedRows.value,

        },

        {

            preserveScroll: true,

            onSuccess: (page) => {

                if (page.props.flash?.success) {
                    success(page.props.flash.success)
                }

                if (page.props.flash?.error) {
                    error(page.props.flash.error)
                }

                if (page.props.flash?.warning) {
                    warning(page.props.flash.warning)
                }

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

    return `Are you sure you want to deactivate ${total} selected Product units(s)?`

})

/** end bulk deactivate */


function confirmDelete()
{
    if (!deleteItem.value) {

        return

    }

    router.delete(

        route(
            'product-variant-units.destroy',
            deleteItem.value.id
        ),

        {

            preserveScroll: true,

            onSuccess: (page) => {

                closeDelete()

                if (page.props.flash?.success) {
                    success(page.props.flash.success)
                }

                if (page.props.flash?.error) {
                    error(page.props.flash.error)
                }

                if (page.props.flash?.warning) {
                    warning(page.props.flash.warning)
                }

            },

            onError: () => {

                error(
                    'Failed to delete Product Variant Unit.'
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
            route('product-variant-units.index'),
            {
                search: filters.search,
                is_active: filters.is_active,
                product_id: filters.product_id,
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
function toggleSelection(unit)
{
    const index = selectedRows.value.indexOf(product_variant.id)

    if (index === -1) {

        selectedRows.value.push(product_variant.id)

    } else {

        selectedRows.value.splice(index, 1)

    }
}
const exportExcel = () => {
    console.log('Export Excel')
}

const exportPdf = () => {
    console.log('Export PDF')
}

const exportCsv = () => {
    console.log('Export CSV')
}
 const handleBulkAction = (action) => {
    switch (action) {
        case 'activate':
            openBulkActivate()
            break

        case 'deactivate':
            openBulkDeactivate()
            break

        case 'delete':
            openBulkDelete()
            break
    }
}
</script>
<template>

    <AppLayout>

        <div
            class="space-y-6"
        >

           <PageHeader
                :breadcrumb="[
                    'Product',
                    'Variant Units'
                ]"
                icon="📂"
                title="Product Varian tUnits"
                subtitle="Manage your Variant Unit."
            />
             <div
                    class="
                        grid
                        grid-cols-1
                        gap-4
                        md:grid-cols-2
                        xl:grid-cols-4
                    "
                >
                    <StatsCard
                        title="Total Variant"
                        :value="statistics?.total ?? 0"
                        subtitle="All Variants"
                        color="indigo"
                    >
                        <template #icon>
                            <CubeIcon class="h-7 w-7" />
                        </template>
                    </StatsCard>

                    <StatsCard
                        title="Active"
                        :value="statistics?.active ?? 0"
                        subtitle="Available"
                        color="emerald"
                    >
                        <template #icon>
                            <CheckCircleIcon class="h-7 w-7" />
                        </template>
                    </StatsCard>

                    <StatsCard
                        title="Inactive"
                        :value="statistics?.inactive ?? 0"
                        subtitle="Disabled"
                        color="red"
                    >
                        <template #icon>
                            <XCircleIcon class="h-7 w-7" />
                        </template>
                    </StatsCard>
                    <StatsCard
                        title="Variant Units"
                        :value="statistics?.units ?? 0"
                        subtitle="Has Variant"
                        color="blue"
                    >
                        <template #icon>
                            <ArchiveBoxIcon class="h-7 w-7" />
                        </template>
                    </StatsCard>
                </div>
                </div>
                <Card class="mt-4">
                       <!--  <Actionbar> -->
                    <div class="mb-6 border-b border-gray-200 pb-4">

                        <!-- Header -->

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

                                    <!-- Search -->

                                    <div class="flex-1">

                                        <input
                                            v-model="filters.search"
                                            type="text"
                                            placeholder="Search Variant or units..."
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

                                    </div>

                                    <!-- Action -->

                                    <div
                                        class="
                                            grid
                                            grid-cols-2
                                            gap-2
                                            w-full

                                            lg:flex
                                            lg:flex-wrap
                                            lg:items-center
                                            lg:justify-end
                                            lg:w-auto
                                        "
                                    >
                                        <BaseButton
                                            variant="secondary"
                                            class="w-full lg:w-auto"
                                            @click="refresh"
                                        >
                                            <template #icon>
                                                <ArrowPathIcon class="h-4 w-4 text-green-600" />
                                            </template>

                                            Refresh

                                        </BaseButton>

                                    <BaseButton variant="secondary">
                                        <template #icon>
                                            <ArrowDownTrayIcon class="h-4 w-4" />
                                        </template>

                                        Export
                                    </BaseButton>

                                    <BulkActionDropdown
                                      
                                        :count="selectedRows.length"
                                        :disabled="selectedRows.length === 0"
                                        @delete="openBulkDelete"
                                        @activate="openBulkActivate"
                                        @deactivate="openBulkDeactivate"
                                    />
                                    <BaseButton
                                        @click="create"
                                    >

                                    <template #icon>

                                        <PlusIcon class="h-5 w-5"/>

                                    </template>

                                    Create Units

                                </BaseButton>

                            </div>

                        </div>  

                        <!-- Filters -->
                        <div
                            class="
                                mt-4
                                grid
                                grid-cols-1
                                gap-3
                                sm:grid-cols-2
                                lg:grid-cols-2
                            "

                        >
                        <SearchableSelect
                            v-model="filters.product_id"
                            :options="products"
                            label="label"
                            value-key="id"
                            placeholder="All Products"
                        />

                            <SearchableSelect
                                v-model="filters.is_active"
                                :options="statusOptions"
                                label="label"
                                value-key="value"
                                placeholder="All Status"
                                class="w-full"
                            />
                            

                        </div>

                    </div>
                      <!--   </Actionbar> -->       
                <!-- table-->
                <div
                    class="mt-6"
                >
                <!-- Loading -->

                        <LoadingOverlay
                            :show="loading"
                            text="Loading Variant Units..."
                        />
                        <!-- End Loading -->
                        <!-- table-->
                       <!--  <pre>{{ units.data }}</pre> -->
                          <DataTable
                            v-if="units?.data?.length"
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
                                    column="product"
                                    :sort="sort"
                                    :direction="direction"
                                    @sort="sortBy"
                                >
                                    Product
                                </DataTableHeaderCell>

                                <DataTableHeaderCell
                                    sortable
                                    column="variant"
                                    :sort="sort"
                                    :direction="direction"
                                    @sort="sortBy"
                                >
                                    Variant
                                </DataTableHeaderCell>

                                <DataTableHeaderCell
                                    sortable
                                    column="unit"
                                    :sort="sort"
                                    :direction="direction"
                                    @sort="sortBy"
                                >
                                    Unit
                                </DataTableHeaderCell>

                                <DataTableHeaderCell
                                    sortable
                                    column="conversion_factor"
                                    :sort="sort"
                                    :direction="direction"
                                    @sort="sortBy"
                                    align="center"
                                    width="140px"
                                >
                                    Conversion
                                </DataTableHeaderCell>

                                <DataTableHeaderCell
                                    width="90px"
                                    align="center"
                                >
                                    Base
                                </DataTableHeaderCell>

                                <DataTableHeaderCell
                                    width="90px"
                                    align="center"
                                >
                                    Default
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
                                    v-for="item in units.data"
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

                                    <!-- Product -->
                                    <DataTableCell>

                                        <span
                                        class="font-semibold text-blue-700"
                                    >
                                        {{ item.variant?.product?.name }}
                                    </span>
                                    </DataTableCell>

                                    <!-- Variant -->
                                    <DataTableCell>

                                        {{ item.variant?.name }}

                                    </DataTableCell>

                                    <!-- Unit -->
                                    <DataTableCell>

                                        {{ item.unit?.name }}

                                    </DataTableCell>

                                    <!-- Conversion -->
                                    <DataTableCell align="center">

                                        {{ Number(item.conversion_factor )}}

                                    </DataTableCell>

                                    <!-- Base -->
                                    <DataTableCell align="center">

                                        {{ item.is_base ? 'Yes' : 'No' }}

                                    </DataTableCell>

                                    <!-- Default -->
                                  <DataTableCell align="center">

                                        <StatusBadge
                                            :status="item.is_default"
                                            active-text="Yes"
                                            inactive-text="-"
                                        />

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
                                            @view="show(item)"
                                            @edit="edit(item)"
                                            @delete="openDelete(item)"
                                        />

                                    </DataTableCell>

                                </DataTableRow>

                            </DataTableBody>
                        </DataTable> 
                                 <TableEmpty
                                v-else
                                icon="📂"
                                title="No Product Varian Found"
                                description="There are no Product Units available."
                            >
                                <template #action>

                                    <BaseButton
                                        @click="create"
                                    >
                                        Create Product units
                                    </BaseButton>

                                </template>
                            </TableEmpty>
                </div>

                    <div
                        class="mt-6"
                    >

                        <TablePagination
                            :data="units"
                            label="Product Variant"
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
<FormModal
    :show="showModal"
    :product-variant-unit="selectedItem"
    :variants="variants"
    :units="units"
    @close="closeModal"
    @saved="refresh"
/>
</template>

                   