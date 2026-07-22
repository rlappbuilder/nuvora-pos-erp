<script setup>

import { ref, computed, watch } from 'vue'

import { Head, Link, router } from '@inertiajs/vue3'

import {

    LoadingOverlay,

} from '@/Components/Feedback'
/*

|--------------------------------------------------------------------------
| Layout
|--------------------------------------------------------------------------
*/

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

/*
|--------------------------------------------------------------------------
| Components
|--------------------------------------------------------------------------
*/

import PageHeader from '@/Components/Layout/PageHeader.vue'

import Card from '@/Components/Layout/Card.vue'

import DataTable from '@/Components/Table/DataTable.vue'

import DataTableHead from '@/Components/Table/DataTableHead.vue'

import DataTableHeaderCell from '@/Components/Table/DataTableHeaderCell.vue'

import DataTableBody from '@/Components/Table/DataTableBody.vue'

import DataTableRow from '@/Components/Table/DataTableRow.vue'

import DataTableCell from '@/Components/Table/DataTableCell.vue'

import TableEmpty from '@/Components/Table/TableEmpty.vue'

import ConfirmDeleteModal from '@/Components/Modal/ConfirmDeleteModal.vue'

import SearchableSelect from '@/Components/Form/SearchableSelect.vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import BaseButton from '@/Components/Button/BaseButton.vue'
import {   TablePagination, } from '@/Components/Table'
import {   ActionBar,} from '@/Components/Layout'
/*
|--------------------------------------------------------------------------
| Utils
|--------------------------------------------------------------------------
*/

import {

    formatCurrency,

} from '@/Utils/currency'
import {

    BulkSelectionBar,

} from '@/Components/Bulk'
/*
|--------------------------------------------------------------------------
| Icons
|--------------------------------------------------------------------------
*/


import StatsCard

from '@/Components/Card/StatsCard.vue'
import StatusBadge from '@/Components/Display/StatusBadge.vue'

import {
    success,
    error,
} from '@/Utils'
import Swal from 'sweetalert2'

import {

    onMounted,

} from 'vue'

import {

    usePage,

} from '@inertiajs/vue3'

import { PlusIcon } from '@heroicons/vue/24/outline'
import ActionDropdown from '@/Components/Action/ActionDropdown.vue'
import {

    useLoading,

} from '@/Composables/useLoading'
const {

    loading,

} = useLoading()

const page = usePage()

onMounted(() => {

    if (

        page.props.flash?.success

    ) {

        Swal.fire({

            icon: 'success',

            title: 'Success',

            text: page.props.flash.success,

            confirmButtonColor: '#2563eb',

        })

    }

})
const showBulkActivate = ref(false)

const showBulkDeactivate = ref(false)

const selectedRows = ref([])

const props = defineProps({

    cashBanks: Object,

    filters: Object,

    summary: Object,

})
console.log(props.summary)
/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

const search = ref(

    props.filters.search ?? ''

)
const sort = ref(

    props.filters.sort

)

const direction = ref(

    props.filters.direction

)
const type = ref(

    props.filters.type ?? ''

)

const status = ref(

    props.filters.status ?? ''

)

/*
|--------------------------------------------------------------------------
| Delete Modal
|--------------------------------------------------------------------------
*/

const showDelete = ref(false)

const selectedCashBank = ref(null)

const deleteMessage = computed(() => {

    if (!selectedCashBank.value) {

        return ''

    }

    return `Are you sure you want to delete "${selectedCashBank.value.name}"?`

})

/*
|--------------------------------------------------------------------------
| bulkDelete Modal
|--------------------------------------------------------------------------
*/
const showBulkDelete = ref(false)
const bulkDeleteMessage = computed(() => {

    return `Are you sure you want to delete ${selectedRows.value.length} selected Cash Bank account(s)?`

})
/*


|--------------------------------------------------------------------------
| Filter Options
|--------------------------------------------------------------------------
*/

const typeOptions = [

    {

        id: '',

        name: 'All Type',

    },

    {

        id: 'Cash',

        name: 'Cash',

    },

    {

        id: 'Bank',

        name: 'Bank',

    },

]

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
/*
|--------------------------------------------------------------------------
| Watch Filters
|--------------------------------------------------------------------------
*/

watch(

    [

        search,

        type,

        status,

    ],

    () => {

        router.get(

            route(

                'cash-banks.index'

            ),

            {

                search: search.value,

                type: type.value,

                status: status.value,

            },

            {

                preserveState: true,

                preserveScroll: true,

                replace: true,

            },

        )

    }

)
/*
|--------------------------------------------------------------------------
| Methods
|--------------------------------------------------------------------------
*/

function resetFilter()
{

    search.value = ''

    type.value = ''

    status.value = ''

}
function openDelete(item)
{

    selectedCashBank.value = item

    showDelete.value = true

}
function closeDelete()
{

    showDelete.value = false

    selectedCashBank.value = null

}

function deleteCashBank()
{

    if (!selectedCashBank.value) {

        return

    }

    router.delete(

        route(
            'cash-banks.destroy',
            selectedCashBank.value.id
        ),

        {

            preserveScroll: true,

            onSuccess: () => {

                closeDelete()

                success(
                    'Cash Bank deleted successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to delete Cash Bank.'
                )

            }

        }

    )

}
function showCashBank(id)
{

    router.visit(

        route(

            'cash-banks.show',

            id

        )

    )

}
function editCashBank(id)
{

    router.visit(

        route(

            'cash-banks.edit',

            id

        )

    )

}

const isAllSelected = computed(() => {

    return (

        props.cashBanks.data.length > 0 &&

        selectedRows.value.length === props.cashBanks.data.length

    )

})

function toggleSelectAll(event)
{

    if (

        event.target.checked

    ) {

      selectedRows.value = props.cashBanks.data.map(
    item => item.id
)

    } else {

        selectedRows.value = []

    }

}
const hasSelection = computed(() => {

    return selectedRows.value.length > 0

})

const selectedCount = computed(() => {

    return selectedRows.value.length

})

const selectAllRef = ref(null)
const isIndeterminate = computed(() => {

    return (

        selectedRows.value.length > 0 &&

        selectedRows.value.length < props.cashBanks.data.length

    )

})
watch(

    isIndeterminate,

    (value) => {

        if (

            selectAllRef.value

        ) {

            selectAllRef.value.indeterminate = value

        }

    }

)
function openBulkDelete()
{

    if (selectedRows.value.length === 0) {

        return

    }

    showBulkDelete.value = true

}
function bulkDelete()
{

    router.delete(

        route('cash-banks.bulk-delete'),

        {

            data: {

                ids: selectedRows.value,

            },

            preserveScroll: true,

            onSuccess: () => {

                showBulkDelete.value = false

                selectedRows.value = []

                success(
                    'Selected Cash Bank deleted successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to delete selected Cash Bank.'
                )

            }

        }

    )

}
function openBulkActivate()
{

    if (

        selectedRows.value.length === 0

    ) {

        return

    }

    showBulkActivate.value = true

}

function openBulkDeactivate()
{

    if (

        selectedRows.value.length === 0

    ) {

        return

    }

    showBulkDeactivate.value = true

}

const bulkActivateMessage = computed(() => {

    return `Activate ${selectedRows.value.length} selected Cash Bank account(s)?`

})

const bulkDeactivateMessage = computed(() => {

    return `Deactivate ${selectedRows.value.length} selected Cash Bank account(s)?`

})

function bulkActivate()
{

    router.patch(

        route('cash-banks.bulk-activate'),

        {

            ids: selectedRows.value,

        },

        {

            preserveScroll: true,

            onSuccess: () => {

                showBulkActivate.value = false

                selectedRows.value = []
                success(
                    'Selected Cash Bank activated successfully.'
                )

            }

        }

    )

}

function bulkDeactivate()
{

    router.patch(

        route('cash-banks.bulk-deactivate'),

        {

            ids: selectedRows.value,

        },

        {

            preserveScroll: true,

            onSuccess: () => {

                showBulkDeactivate.value = false

                selectedRows.value = []
                success(
                    'Selected Cash Bank Dectivated successfully.'
                )

            }

        }

    )

}
function create()
{

    router.get(

        route(

            'cash-banks.create'

        )

    )

}
function clearSelection()
{

    selectedRows.value = []

}

function duplicate(item)
{

    router.get(

        route(

            'cash-banks.duplicate',

            item.id

        )

    )

}
function sortBy(column)
{

    if (

        sort.value === column

    ) {

        direction.value =

            direction.value === 'asc'

                ? 'desc'

                : 'asc'

    }

    else {

        sort.value = column

        direction.value = 'asc'

    }

    router.get(

        route(

            'cash-banks.index'

        ),

        {

            search: search.value,

            type: type.value,

            status: status.value,

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

<Head

    title="Cash & Bank"

/>

<AppLayout>

    <div

        class="
            space-y-6
        "

    >

        <PageHeader

            icon="🏦"

            title="Cash & Bank"

            subtitle="Manage your cash and bank accounts."

        >



        </PageHeader>

        <!-- stats card-->
            <!-- ===================================================== -->
            <!-- Summary -->
            <!-- ===================================================== -->

            <div
                class="
                    grid
                    grid-cols-1
                    sm:grid-cols-2
                    xl:grid-cols-4
                    gap-6
                "
            >

                <StatsCard

                    title="Total Account"

                    :value="summary.total_accounts"

                    icon="🏦"

                />

                <StatsCard

                    title="Bank Accounts"

                    :value="summary.bank_accounts"

                    icon="🏛️"

                />

                  <StatsCard

                    title="Cash Accounts"

                    :value="summary.cash_accounts"

                    icon="💵"

                />

                <StatsCard

                    title="Current Balance"

                    :value="formatCurrency(
                        summary.current_balance
                    )"

                    icon="💰"

                />
            </div>
        <!-- end stats card-->
      
          <!-- ===================================================== -->
            <!-- Data Table -->
            <!-- ===================================================== -->

            <Card>
                    <!-- Bulk Action Container -->
                    <!-- <div
                        class="
                            flex
                            items-center
                            justify-between
                            border-b
                            border-gray-200
                            px-6
                            py-4
                            min-h-[72px]
                        "
                    > -->
                    
                    <Actionbar>

                       <template v-if="!hasSelection">

                            <div
                                class="
                                    flex
                                    flex-col
                                    gap-3

                                    lg:flex-row
                                    lg:items-center
                                    lg:justify-between
                                "   >

                                <!-- Left -->

                                <div
                                    class="
                                            flex
                                            flex-col
                                            gap-3
                                            md:flex-row
                                            md:flex-wrap
                                            md:items-center
                                            flex-1
                                        "
                                >

                                    <!-- Search -->

                                    <input

                                        v-model="search"

                                        type="text"

                                        placeholder="Search..."

                                        class="
                                                w-full
                                                lg:max-w-sm
                                                rounded-xl
                                                border
                                                border-gray-300
                                                px-4
                                                py-2.5
                                            "
                                    >
                                </div>
                                    <div class="w-full md:w-44">

                                    <SearchableSelect

                                        v-model="type"

                                        :options="typeOptions"

                                        placeholder="All Type"

                                    />

                                </div>

                                <div class="w-full md:w-44">

                                    <SearchableSelect

                                        v-model="status"

                                        :options="statusOptions"

                                        placeholder="All Status"

                                    />

                                </div>
                                <BaseButton
                                    class="
                                        w-full
                                        md:w-auto
                                    "
                                    variant="secondary"

                                    @click="resetFilter"

                                >

                                    Reset

                                </BaseButton>
                                <!-- Right -->

                                <BaseButton class="
                                                w-full
                                                md:w-auto
                                            "
                                        " @click="create">

                                <template #icon>

                                    <PlusIcon class="h-5 w-5" />

                                </template>

                                Create New

                            </BaseButton>

                   </div>

                        </template>
                   
                        <!-- action bulk-->
                         
                 <template v-if="hasSelection">

                    <BulkSelectionBar
                         :count="selectedRows.length"

                        @delete="openBulkDelete"

                        @activate="openBulkActivate"

                        @deactivate="openBulkDeactivate"

                        @cancel="clearSelection"

                    />

                    </template>
                     </ActionBar>
                    <br>
                        <!-- end action bulk-->
                    
                   <!-- </div>  end toolbar action-->
                <div
                        v-if="cashBanks.data.length === 0"
                    >
                        <TableEmpty


                            icon="🏦"

                                title="No Cash & Bank Accounts"

                                description="You haven't created any Cash & Bank accounts yet. Start by creating your first account."

                                button-text="+ New Cash Bank"

                                @action="create"

                            />
                        

                        </div>
         <div class="relative">

        <LoadingOverlay
            :show="loading"
            text="Loading Cash Bank..."
        />
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

                                 class="
                                    rounded
                                    border-gray-300
                                "

                            />
      
                        </DataTableHeaderCell>

                       <DataTableHeaderCell

                            sortable

                            column="code"

                            :sort="sort"

                            :direction="direction"

                            @sort="sortBy"

                            width="140px"

                        >

                            Code

                        </DataTableHeaderCell>

                        <DataTableHeaderCell

                            sortable

                            column="name"

                            :sort="sort"

                            :direction="direction"

                            @sort="sortBy"

                            width="260px"

                        >

                            Name

                        </DataTableHeaderCell>
                        <DataTableHeaderCell

                            sortable

                            column="type"

                            :sort="sort"

                            :direction="direction"

                            @sort="sortBy"

                            width="120px"

                        >

                            Type

                        </DataTableHeaderCell>


                       <DataTableHeaderCell

                            sortable

                            column="current_balance"

                            :sort="sort"

                            :direction="direction"

                            @sort="sortBy"

                            align="right"

                            width="150px"

                        >

                            Balance

                        </DataTableHeaderCell>

                        <DataTableHeaderCell

                            sortable

                            column="status"

                            :sort="sort"

                            :direction="direction"

                            @sort="sortBy"

                            align="center"

                            width="100px"

                        >

                            Status

                        </DataTableHeaderCell>

                        <DataTableHeaderCell
                            align="center"
                            width="100px"
                        >

                            Actions

                        </DataTableHeaderCell>

                    </DataTableHead>

                    <DataTableBody>

                        <template

                            v-if="cashBanks.data.length"

                        >

                            <DataTableRow

                                v-for="item in cashBanks.data"

                                :key="item.id"

                            >

                                <DataTableCell

                                    align="center"

                                >

                                    <input

                                        v-model="selectedRows"

                                        :value="item.id"

                                        type="checkbox"


                                        class="
                                            rounded
                                            border-gray-300
                                        "

                                    >

                                </DataTableCell>

                                <DataTableCell  width="60px">

                                    {{ item.code }}

                                </DataTableCell>

                                <DataTableCell width="260px">

                                    {{ item.name }}

                                </DataTableCell>

                                <DataTableCell width="120px" >

                                    {{ item.type }}

                                </DataTableCell>

                                <DataTableCell 
                                    width="180px"

                                    align="right"

                                >

                                    {{

                                        formatCurrency(

                                            item.current_balance

                                        )

                                    }}

                                </DataTableCell>

                               <DataTableCell
                                    width="120px"
                                    align="center"

                                >

                                   <StatusBadge
                                        :status="item.is_active === 1"
                                    />

                                </DataTableCell>

                                <DataTableCell
                                    width="150px"
                                    align="center"

                                >
                                <ActionDropdown

                                    @view="showCashBank(item)"

                                    @edit="editCashBank(item)"

                                    @duplicate="duplicate(item)"

                                    @export="exportRow(item)"

                                    @delete="openDelete(item)"

                                />
                                 
                                </DataTableCell>

                            </DataTableRow>

                        </template>

                        <template

                            v-else

                        >



                        </template>

                    </DataTableBody>

                </DataTable>
            </div>
                <TablePagination

                    :data="cashBanks"

                />                   

            </Card>
            <!-- end card databale-->
             <ConfirmDeleteModal

    :show="showDelete"

    title="Delete Cash Bank"

    :message="deleteMessage"

    @close="closeDelete"

    @confirm="deleteCashBank"

/>

<ConfirmDeleteModal

    :show="showBulkDelete"

    title="Bulk Delete Cash Bank"

    :message="bulkDeleteMessage"

    @close="showBulkDelete = false"

    @confirm="bulkDelete"

/>

<ConfirmDeleteModal

    :show="showBulkActivate"

    title="Bulk Activate"

    :message="bulkActivateMessage"

    @close="showBulkActivate = false"

    @confirm="bulkActivate"

/>

<ConfirmDeleteModal

    :show="showBulkDeactivate"

    title="Bulk Deactivate"

    :message="bulkDeactivateMessage"

    @close="showBulkDeactivate = false"

    @confirm="bulkDeactivate"

/>
    </div>

</AppLayout>

</template>