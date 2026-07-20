<script setup>
import { Head } from '@inertiajs/vue3'
import StatisticCards from './Components/StatisticCards.vue'
import PageHeader from '@/Components/Layout/PageHeader.vue'
import Card from '@/Components/Layout/Card.vue'
import DataTable from '@/Components/Table/DataTable.vue'
import DataTableHead from '@/Components/Table/DataTableHead.vue'
import DataTableBody from '@/Components/Table/DataTableBody.vue'
import DataTableHeaderCell from '@/Components/Table/DataTableHeaderCell.vue'
import DataTableRow from '@/Components/Table/DataTableRow.vue'
import DataTableCell from '@/Components/Table/DataTableCell.vue'
import StatusBadge from '@/Components/Display/StatusBadge.vue'
/** import Actionbar from '@/Components/Layout/Actionbar.vue' */
import { ref,computed,watch,reactive } from 'vue'
import BaseButton from '@/Components/Button/BaseButton.vue'
import { router } from '@inertiajs/vue3'
import BulkActionDropdown from '@/Components/Bulk/BulkActionDropdown.vue'
import TablePagination from '@/Components/Table/TablePagination.vue'
import ConfirmDeleteModal from '@/Components/Modal/ConfirmDeleteModal.vue'
import ActionDropdown from '@/Components/Action/ActionDropdown.vue'
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
import Swal from 'sweetalert2'
import {

    LoadingOverlay,

} from '@/Components/Feedback'
import {

    useLoading,

} from '@/Composables/useLoading'
import SearchableSelect from '@/Components/Form/SearchableSelect.vue'
import { useTreeView } from '@/Composables/useTreeView'
import AppLayout from '@/Layouts/AppLayout.vue'

const {

    loading,

} = useLoading()

const expandedRows = ref([])

const VIEW_MODE_KEY = 'coa-view-mode'

const viewMode = ref(
    localStorage.getItem(VIEW_MODE_KEY) || 'list'
)

watch(viewMode, (value) => {
    localStorage.setItem(VIEW_MODE_KEY, value)
})
const accounts = computed(() => props.chartOfAccounts.data)

const selectedRows = ref([])
/** bulk delete  */
const showBulkDelete = ref(false)

const bulkDeleteMessage = computed(() => {

    const total = selectedRows.value.length

    if (total === 0) {

        return ''

    }

    return `Are you sure you want to delete ${total} selected Chart of Account(s)?`

})
/** end bulk delete */
/** bulk activate */
const showBulkActivate = ref(false)

const bulkActivateMessage = computed(() => {

    const total = selectedRows.value.length

    if (total === 0) {

        return ''

    }

    return `Are you sure you want to activate ${total} selected Chart of Account(s)?`

})
/** den bulk atived */
/** bulk deactived */
const showBulkDeactivate = ref(false)

const bulkDeactivateMessage = computed(() => {

    const total = selectedRows.value.length

    if (total === 0) {

        return ''

    }

    return `Are you sure you want to deactivate ${total} selected Chart of Account(s)?`

})
/** end bulk deactived */
const selectAllRef = ref(null)


const isAllSelected = computed(() => {

    if (!props.chartOfAccounts.data.length) {
        return false
    }

    return selectedRows.value.length === props.chartOfAccounts.data.length

})
/** const refresh = () => {

    router.reload({

        only: [

            'chartOfAccounts',

            'summary',

        ],

    })

}
    */

const props = defineProps({

    chartOfAccounts: Object,

    accountCategories: Array,

    accountTypes: Array,

    filters: Object,

    summary: Object,

    

})

const toggleSelectAll = (event) => {

    if (event.target.checked) {

        selectedRows.value =
            props.chartOfAccounts.data.map(item => item.id)

    } else {

        selectedRows.value = []

    }

}

/** bulk action */
function bulkDelete()
{
    console.log('bulkdelete muncul')
    if (selectedRows.value.length === 0) {

        return

    }

    showBulkDelete.value = true

}
function closeBulkDelete()
{

    showBulkDelete.value = false

}
function confirmBulkDelete()
{

    router.post(

        route('chart-of-accounts.bulk-delete'),

        {

            ids: selectedRows.value,

        },

        {

            preserveScroll: true,

            onSuccess: () => {

                closeBulkDelete()

                selectedRows.value = []

                success(
                    'Selected Chart of Account deleted successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to delete selected Chart of Account.'
                )

            }

        }

    )

}


function bulkActivate()
{

    if (selectedRows.value.length === 0) {

        return

    }

    showBulkActivate.value = true

}
function closeBulkActivate()
{

    showBulkActivate.value = false

}
function confirmBulkActivate()
{

    router.post(

        route('chart-of-accounts.bulk-activate'),

        {

            ids: selectedRows.value,

        },

        {

            preserveScroll: true,

            onSuccess: () => {

                closeBulkActivate()

                selectedRows.value = []

                success(
                    'Selected Chart of Account activated successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to activate selected Chart of Account.'
                )

            }

        }

    )

}

function bulkDeactivate()
{

    if (selectedRows.value.length === 0) {

        return

    }

    showBulkDeactivate.value = true

}
function closeBulkDeactivate()
{

    showBulkDeactivate.value = false

}
function confirmBulkDeactivate()
{

    router.post(

        route('chart-of-accounts.bulk-deactivate'),

        {

            ids: selectedRows.value,

        },

        {

            preserveScroll: true,

            onSuccess: () => {

                closeBulkDeactivate()

                selectedRows.value = []

                success(
                    'Selected Chart of Account deactivated successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to deactivate selected Chart of Account.'
                )

            }

        }

    )

}
/** bulk action */
const toggleRow = (id) => {
    console.log('Toggle:', id)

    if (expandedRows.value.includes(id)) {
        expandedRows.value = expandedRows.value.filter(rowId => rowId !== id)
    } else {
        expandedRows.value.push(id)
    }

    console.log(expandedRows.value)
}
const isExpanded = (id) => {

    return expandedRows.value.includes(id)

}
const visibleAccounts = computed(() => {

    if (viewMode.value !== 'tree') {
        return props.chartOfAccounts.data
    }

    return props.chartOfAccounts.data.filter(item => {

        if (!item.parent_id) {
            return true
        }

        let parent = props.chartOfAccounts.data.find(
            account => account.id === item.parent_id
        )

        while (parent) {

            if (!expandedRows.value.includes(parent.id)) {
                return false
            }

            parent = props.chartOfAccounts.data.find(
                account => account.id === parent.parent_id
            )
        }

        return true

    })

})
const expandAll = () => {

    expandedRows.value = props.chartOfAccounts.data
        .filter(item => item.is_header)
        .map(item => item.id)

}

const collapseAll = () => {

    expandedRows.value = []

}
const canExpandAll = computed(() => {

    return expandedRows.value.length <
        props.chartOfAccounts.data.filter(item => item.is_header).length

})

const canCollapseAll = computed(() => {

    return expandedRows.value.length > 0

})
const filters = reactive({

    search: props.filters.search ?? '',

    status: props.filters.status ?? '',

      account_type_id: props.filters.account_type_id ?? '',

     account_category_id: props.filters.account_category_id ?? '',

})
function resetFilter() {

    filters.search = ''

    filters.status = ''

    router.get(

        route('chart-of-accounts.index'),

        {},

        {

            preserveState: true,

            preserveScroll: true,

            replace: true,

        }

    )

}
const statusOptions = [

    {
        id: '',
        name: 'All Status',
    },

    {
        id: 1,
        name: 'Active',
    },

    {
        id: 0,
        name: 'Inactive',
    },

]
function search() {

    

    router.get(
        route('chart-of-accounts.index'),
        filters,
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    )

}
watch(

    () => filters.status,

    () => {

        search()

    }

)
watch(

    () => filters.account_type_id,

    () => {

        search()

    }

)
watch(

    () => filters.account_category_id,

    () => {

        search()

    }

)
let searchTimeout = null

watch(
    () => filters.search,
    () => {
        clearTimeout(searchTimeout)

        searchTimeout = setTimeout(() => {
            router.get(
                route('chart-of-accounts.index'),
                {
                    ...filters,
                },
                {
                    preserveState: true,
                    preserveScroll: true,
                    replace: true,
                }
            )
        }, 500)
    }
)
function refresh() {

    router.get(
        route('chart-of-accounts.index'),
        {
            search: '',
            status: '',
            account_type_id: '',
            account_category_id: '',
            sort: 'code',
            direction: 'asc',
            per_page: filters.per_page,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );

}
function showChartOfAccount(item)
{
    router.visit(
        route(
            'chart-of-accounts.show',
            item.id
        )
    )
}
function editChartOfAccount(item)
{
    router.visit(
        route(
            'chart-of-accounts.edit',
            item.id
        )
    )
}
function duplicate(item)
{
    router.visit(
        route(
            'chart-of-accounts.duplicate',
            item.id
        )
    )
}
function create()
{

    router.get(

        route(

            'chart-of-accounts.create'

        )

    )

}
</script>

<template>

    <Head title="Chart Of Accounts" />

<!--     <AuthenticatedLayout> -->
    <AppLayout>

        <div
            class="space-y-6"
        >

            <PageHeader

                icon="📚"

                title="Chart Of Accounts"

                subtitle="Manage company chart of accounts."

            />
            <StatisticCards

                :summary="summary"

            />
                    <Card>

                <Actionbar>

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
                                placeholder="Search account code or name..."
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
                                v-model="filters.status"
                                :options="statusOptions"
                                placeholder="All Status"
                            />

                            <SearchableSelect
                                v-model="filters.account_type_id"
                                :options="props.accountTypes"
                                placeholder="All Types"
                            />

                            <SearchableSelect
                                v-model="filters.account_category_id"
                                :options="props.accountCategories"
                                placeholder="All Categories"
                            />
                        </div>

                         <!-- Right toolbar pertama -->

                        <div class="flex items-center gap-2">

                            <BaseButton
                                variant="secondary"
                                @click="refresh"
                            >
                                Refresh
                            </BaseButton>
                            <!--
                            <BaseButton
                                variant="secondary"
                                @click="refresh"
                            >
                                Refresh
                            </BaseButton>
                            -->
                            <BaseButton
                                variant="secondary"
                            >
                                Export
                            </BaseButton>

                           
                                <BaseButton class="
                                                w-full
                                                md:w-auto
                                            "
                                        " @click="create">

                                <template #icon>

                                    <PlusIcon class="h-5 w-5" />

                                </template>

                                Add

                            </BaseButton>

                        </div><!-- end tollbar pertama-->

                            
                    </div>
                    <!-- tree-->
                     
                       
                    <!--  end tree-->
                </Actionbar>
                                <!-- list tree toolbar kedua-->
                <div
                    class="
                        mt-4
                        flex
                        items-center
                        justify-between
                    "
                >

                    <!-- Left -->
                    <div
                        class="
                            inline-flex
                            overflow-hidden
                            rounded-xl
                            border
                            border-gray-300
                            gap-2
                        "
                    >

                        <!-- List Button -->

                        <BaseButton
                            :variant="viewMode === 'list'
                                ? 'primary'
                                : 'secondary'"
                            @click="viewMode = 'list'"
                        >
                            ☰ List
                        </BaseButton>

                        <!-- Tree Button -->

                        <BaseButton
                            :variant="viewMode === 'tree'
                                ? 'primary'
                                : 'secondary'"
                            @click="viewMode = 'tree'"
                        >
                            🌳 Tree
                        </BaseButton>
                        <!-- expand collapse-->
                        <div
                            v-if="viewMode === 'tree'"
                            class="flex items-center gap-2"
                        >

                            <BaseButton
                                     variant="secondary"
                                    :disabled="!canExpandAll"
                                    @click="expandAll"
                                >
                                    Expand All
                                </BaseButton>

                                <BaseButton
                                variant="secondary"
                                    :disabled="!canCollapseAll"
                                    @click="collapseAll"
                                >
                                    Collapse All
                                </BaseButton>           
                        </div>
                        <!-- end expands collapse-->
                    </div>

                    <!-- Right -->

                    <div
                        class="
                            flex
                            items-center
                            gap-2
                        "
                    >

                        <template
                            v-if="selectedRows.length > 0"
                        >

                            <span
                                class="
                                    text-sm
                                    font-medium
                                    text-gray-600
                                "
                            >
                                {{ selectedRows.length }}
                                item(s) selected
                            </span>

                            <BulkActionDropdown
                                @delete="bulkDelete"
                                @activate="bulkActivate"
                                @deactivate="bulkDeactivate"
                            />

                            <BaseButton
                                variant="secondary"
                                @click="selectedRows = []"
                            >
                                Cancel
                            </BaseButton>

                        </template>

                    </div>

                </div>
                <!-- end list tree kedua toolbar kedua-->
                  <div
                        class="mt-6"
                    >
                      <!-- loading-->
                        

                        <LoadingOverlay
                            :show="loading"
                            text="Loading Cash Bank..."
                        />
                        
                      <!-- end loading -->
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
                                    width="140px"
                                >
                                    Code
                                </DataTableHeaderCell>

                                <DataTableHeaderCell
                                    width="280px"
                                >
                                    Account Name
                                </DataTableHeaderCell>

                                <DataTableHeaderCell
                                    width="180px"
                                >
                                    Category
                                </DataTableHeaderCell>

                                <DataTableHeaderCell
                                    width="180px"
                                >
                                    Parent
                                </DataTableHeaderCell>

                                <DataTableHeaderCell
                                    width="140px"
                                    align="center"
                                >
                                    Normal Balance
                                </DataTableHeaderCell>

                                <DataTableHeaderCell
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

                                    v-for="item in visibleAccounts"

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
                                    <div
                                        class="flex items-center"
                                        :style="viewMode === 'tree'
                                            ? { paddingLeft: `${(item.level - 1) * 24}px` }
                                            : {}"
                                    >

                                        <template v-if="viewMode === 'tree'">

                                            <button
                                                v-if="item.is_header"
                                                @click="toggleRow(item.id)"
                                                class="mr-1"
                                            >
                                                <ChevronDownIcon
                                                    v-if="isExpanded(item.id)"
                                                    class="h-4 w-4"
                                                />

                                                <ChevronRightIcon
                                                    v-else
                                                    class="h-4 w-4"
                                                />
                                            </button>

                                            <FolderIcon
                                                v-if="item.is_header"
                                                class="mr-2 h-5 w-5 text-amber-500"
                                            />

                                            <DocumentTextIcon
                                                v-else
                                                class="mr-2 h-5 w-5 text-slate-500"
                                            />

                                        </template>

                                        <span>{{ item.name }}</span>

                                    </div>

                                    </DataTableCell>

                                    <DataTableCell>

                                        {{ item.account_category?.name }}

                                    </DataTableCell>

                                    <DataTableCell>

                                        {{ item.parent?.name ?? '-' }}

                                    </DataTableCell>

                                    <DataTableCell
                                        align="center"
                                    >

                                        {{ item.normal_balance }}

                                    </DataTableCell>

                                    <DataTableCell
                                        align="center"
                                    >

                                        <StatusBadge

                                            :active="item.status"

                                        />

                                    </DataTableCell>

                                  <DataTableCell align="center">

                                    <ActionDropdown

                                        @view="showChartOfAccount(item)"

                                        @edit="editChartOfAccount(item)"

                                        @duplicate="duplicate(item)"

                                        @export="exportRow(item)"

                                        @delete="openDelete(item)"

                                    />

                                </DataTableCell>

                                </DataTableRow>

                            </DataTableBody>

                        </DataTable>
                        <TablePagination
                            :data="chartOfAccounts"
                            label="Accounts"
                        />
                    </div>

            </Card>

        </div>
</AppLayout>
   <!-- </AuthenticatedLayout> -->
     <ConfirmDeleteModal
            :show="showBulkDelete"
            title="Delete Confirmation"
            :message="bulkDeleteMessage"
            confirm-text="Delete"
            confirm-variant="danger"
            @close="closeBulkDelete"
            @confirm="confirmBulkDelete"
        />
       <ConfirmDeleteModal
            :show="showBulkActivate"
            title="Activate Confirmation"
            :message="bulkActivateMessage"
            confirm-text="Activate"
            confirm-variant="success"
            @close="closeBulkActivate"
            @confirm="confirmBulkActivate"
        />

        <ConfirmDeleteModal
            :show="showBulkDeactivate"
            title="Deactivate Confirmation"
            :message="bulkDeactivateMessage"
            confirm-text="Deactivate"
            confirm-variant="warning"
            @close="closeBulkDeactivate"
            @confirm="confirmBulkDeactivate"
        />
</template>