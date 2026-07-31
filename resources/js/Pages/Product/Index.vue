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
//import BaseButton from '@/Components/Button/BaseButton.vue'
import BulkActionDropdown from '@/Components/Bulk/BulkActionDropdown.vue'
import TablePagination from '@/Components/Table/TablePagination.vue'
import ConfirmDeleteModal from '@/Components/Modal/ConfirmDeleteModal.vue'
import ActionDropdown from '@/Components/Action/ActionDropdown.vue'
import TableEmpty from '@/Components/Table/TableEmpty.vue'
import ActionBar from '@/Components/Layout/ActionBar.vue'
import FormInput from '@/Components/Form/FormInput.vue'

import BaseButton from '@/Components/Button/BaseButton.vue'


import {
    ArrowPathIcon,
    PlusIcon,
    ArrowDownTrayIcon, 
    
} from '@heroicons/vue/24/solid'
import {
    
    CubeIcon,
    CheckCircleIcon,
    XCircleIcon,
    Squares2X2Icon,
    ExclamationTriangleIcon,
} from '@heroicons/vue/24/solid'
import { ClockIcon } from '@heroicons/vue/24/outline'
import {
    success,
    error,
    currency,
} from '@/Utils'
import {

    LoadingOverlay,

} from '@/Components/Feedback'
import { warning } from '@/Utils/swal'
import SearchableSelect from '@/Components/Form/SearchableSelect.vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link } from '@inertiajs/vue3'
import ExportDropdown from '@/Components/Bulk/ExportDropdown.vue'
const props = defineProps({
    products: Object,
    categories: Array,
    brands: Array,
    statistics: Object,
    filters: Object,
})
const { products, statistics  } = toRefs(props)

const pageTitle = computed(() => 'Product')
const loading = ref(false)
const filters = reactive({
    search: props.filters?.search ?? '',
    category_id: props.filters?.category_id ?? '',
    brand_id: props.filters?.brand_id ?? '',
    product_type: props.filters?.product_type ?? '',
    is_active: props.filters?.is_active ?? '',
    per_page: props.filters?.per_page ?? 20,
})
let debounceTimer = null

function loadData()
{
    router.get(
        route('products.index'),
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
    () => filters.category_id,
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
    () => filters.brand_id,
    () => {
        loadData()
    }
)

watch(
    () => filters.product_type,
    () => {
        loadData()
    }
)
const brands = computed(() =>
    (props.brands ?? []).map(item => ({
        id: item.id,
        name: item.name,
    }))
)

const productTypes = [
    
    {
        id: 'PRODUCT',
        name: 'Product',
    },
    {
        id: 'SERVICE',
        name: 'Service',
    },
]


function refresh()
{
    Object.assign(filters, {
        brand_id: '',
        product_type: '',
        search: '',
        is_active: '',
        category_id: '',        
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

    const totalRows = props.products?.data?.length ?? 0

    return (
        totalRows > 0 &&
        selectedRows.value.length === totalRows
    )

})
const isIndeterminate = computed(() => {

    const totalRows = props.products?.data?.length ?? 0

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

       selectedRows.value = props.products?.data?.map(
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
        route('products.create')
    )
}

function showProduct(product)
{
    router.visit(

        route(
            'products.show',
            product.id
        )

    )
}

function editProduct(product)
{
    router.visit(

        route(
            'products.edit',
            product.id
        )

    )
}

function duplicate(product)
{
    router.get(
        route(
            'products.duplicate',
            product.id
        )
    )
}
function openDelete(product)
{
    deleteItem.value = product

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
        route('products.bulk-delete'),
        {
            data: {
                ids: selectedRows.value,
            },
            preserveScroll: true,
            onSuccess: (page) => {

                console.log('FLASH:', page.props.flash)

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

    return `Are you sure you want to delete ${total} selected Product(s)?`

})

/** end bulk delete */

/** bulk activate */
const bulkActivate = () => {

    router.patch(
        route('products.bulk-activate'),
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

    return `Are you sure you want to activate ${total} selected Product (s)?`

})
const bulkDeactivate = () => {

    router.patch(
        route('products.bulk-deactivate'),
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

    return `Are you sure you want to deactivate ${total} selected Product(s)?`

})

/** end bulk deactivate */


function confirmDelete()
{
    console.log(route('products.destroy',deleteItem.value.id))
    if (!deleteItem.value) {

        return

    }

    router.delete(

        route(
            'products.destroy',
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
                    'Failed to delete Product.'
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
            route('products.index'),
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
function toggleSelection(product)
{
    const index = selectedRows.value.indexOf(product.id)

    if (index === -1) {

        selectedRows.value.push(product.id)

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
                    'Master Data',
                    'Product'
                ]"
                icon="📂"
                title="Product"
                subtitle="Manage your products."
            />
             <div
                    class="
                        grid
                        grid-cols-1
                        gap-4
                        md:grid-cols-2
                        xl:grid-cols-5
                    "
                >
                    <StatsCard
                        title="Total Products"
                        :value="statistics?.total ?? 0"
                        subtitle="All Products"
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
                        title="Variant"
                        :value="statistics?.variant ?? 0"
                        subtitle="Product Variants"
                        color="amber"
                    >
                        <template #icon>
                            <Squares2X2Icon class="h-7 w-7" />
                        </template>
                    </StatsCard>

                    <StatsCard
                        title="Required"
                        :value="statistics?.required ?? 0"
                        subtitle="Required Fields"
                        color="cyan"
                    >
                        <template #icon>
                            <ExclamationTriangleIcon class="h-7 w-7" />
                        </template>
                    </StatsCard>
                </div>

        </div>
                <Card class="mt-4">
                <!--     
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
                -->
                        <!-- Search -->
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
                                            placeholder="Search code or name..."
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
                                            lg:w-auto
                                            lg:items-center
                                            lg:justify-end
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

                                    Add

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
                                lg:grid-cols-4
                            "
                        >

                            <SearchableSelect
                                v-model="filters.category_id"
                                :options="categories"
                                placeholder="All Categories"
                                class="w-full" 
                            />

                            <SearchableSelect
                                v-model="filters.brand_id"
                                :options="brands"
                                placeholder="All Brands"
                                class="w-full"
                            />

                            <SearchableSelect
                                v-model="filters.product_type"
                                :options="productTypes"
                                placeholder="All Type"
                                class="w-full"
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

        <!--
            </div>
        </div>-->
            <!--   
                                            <div
                                                class="
                                                    flex
                                                    items-center
                                                    gap-3
                                                "
                                            >

                                            </div>
                                        -->

        
                <!-- table-->
                <div
                    class="mt-6"
                >
                <!-- Loading -->

                        <LoadingOverlay
                            :show="loading"
                            text="Loading Product..."
                        />

                        <!-- End Loading -->
                        <!-- table-->
                         
                          <DataTable
                            v-if="products?.data?.length"
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
                            column="name"
                            :sort="sort"
                            :direction="direction"
                            @sort="sortBy"
                        >
                            Product
                        </DataTableHeaderCell>

                        <DataTableHeaderCell
                            sortable
                            column="category"
                            :sort="sort"
                            :direction="direction"
                            @sort="sortBy"
                        >
                            Category
                        </DataTableHeaderCell>

                        <DataTableHeaderCell
                            sortable
                            column="brand"
                            :sort="sort"
                            :direction="direction"
                            @sort="sortBy"
                        >
                            Brand
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
                            column="product_type"
                            :sort="sort"
                            :direction="direction"
                            @sort="sortBy"
                        >
                            Type
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
                                    v-for="item in products.data"
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

                                    <DataTableCell class="min-w-[340px]">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex h-16 w-16 items-center justify-center rounded-xl bg-yellow-200"
                                            >
                                                <CubeIcon class="h-10 w-10 text-red-600" />
                                            </div>

                                            <div class="min-w-0">
                                               <Link
                                                :href="route('products.show', item.id)"
                                                class="truncate text-sm font-semibold text-blue-700 transition-all duration-200 hover:text-blue-700 hover:underline"
                                            >
                                                {{ item.name }}
                                            </Link>
                                            <p class="mt-1 truncate text-xs font-medium text-slate-500">
                                                {{ item.code }}
                                                <span class="mx-1 text-slate-300">•</span>
                                                {{ item.sku }}
                                            </p>

                                            <p
                                            class="mt-1 inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-medium text-emerald-600"
                                            >
                                                <ClockIcon class="h-3 w-3" />

                                                {{ item.created_at_human }}
                                            </p>
                                            </div>
                                            
                                        </div>
                                    </DataTableCell>
                                    <DataTableCell>
                                        <span
                                            class="inline-flex items-center rounded-md bg-slate-50 px-2.5 py-1 text-sm font-medium text-slate-700"
                                        >
                                            {{ item.category?.name ?? '-' }}
                                        </span>
                                    </DataTableCell>
                                    <DataTableCell>
                                        <span class="font-medium text-slate-700">
                                            {{ item.brand?.name ?? '-' }}
                                        </span>
                                    </DataTableCell>
                                    <DataTableCell>
                                        <span
                                            class="rounded-md bg-gray-100 px-2 py-1 text-xs font-semibold uppercase text-gray-700"
                                        >
                                            {{ item.unit?.name ?? '-' }}
                                        </span>
                                    </DataTableCell>
                                    <DataTableCell>
                                        <span
                                            class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700"
                                        >
                                            {{ item.product_type }}
                                        </span>
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
                                            @view="showProduct(item)"

                                            @edit="editProduct(item)"

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
                                title="No Product Found"
                                description="There are no Products available."
                            >
                                <template #action>

                                    <BaseButton
                                        @click="router.visit(route('attributes.create'))"
                                    >
                                        Create Product
                                    </BaseButton>

                                </template>
                            </TableEmpty>
                </div>

                    <div
                        class="mt-6"
                    >

                        <TablePagination
                            :data="products"
                            label="Producte"
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

                   