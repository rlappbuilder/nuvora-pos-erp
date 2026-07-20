<script setup>

import { ref, computed, watch } from 'vue'

import { Head, Link, router } from '@inertiajs/vue3'

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

import DataTableToolbar from '@/Components/Table/DataTableToolbar.vue'

import TableEmpty from '@/Components/Table/TableEmpty.vue'

import TableAction from '@/Components/Table/TableAction.vue'

import ConfirmDeleteModal from '@/Components/Modal/ConfirmDeleteModal.vue'

import SearchableSelect from '@/Components/Form/SearchableSelect.vue'

import BaseButton from '@/Components/Button/BaseButton.vue'

/*
|--------------------------------------------------------------------------
| Utils
|--------------------------------------------------------------------------
*/

import {

    formatCurrency,

} from '@/Utils/currency'

/*
|--------------------------------------------------------------------------
| Icons
|--------------------------------------------------------------------------
*/

import {

    EyeIcon,

    PencilSquareIcon,

    TrashIcon,

} from '@heroicons/vue/24/outline'
import StatsCard

from '@/Components/Card/StatsCard.vue'
import StatusBadge from '@/Components/Display/StatusBadge.vue'

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

            },

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
</script>
<template>

<Head

    title="Cash & Bank"

/>

<AuthenticatedLayout>

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

            <template #actions>

                <BaseButton

                    variant="primary"

                    @click="

                        router.visit(

                            route(

                                'cash-banks.create'

                            )

                        )

                    "

                >

                    + New Cash & Bank

                </BaseButton>

            </template>

        </PageHeader>
        <!-- stats card-->
            <!-- ===================================================== -->
            <!-- Summary -->
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
         <!-- card tollbar-->
             <!-- ===================================================== -->
            <!-- Filter Section -->
            <!-- ===================================================== -->

            <Card>

                <div

                    class="
                        grid
                        grid-cols-1
                        gap-4
                        lg:grid-cols-12
                        lg:items-end
                    "

                >

                    <!-- Search -->

                    <div

                        class="
                            lg:col-span-5
                        "

                    >

                        <label

                            class="
                                mb-2
                                block
                                text-sm
                                font-medium
                                text-gray-700
                            "

                        >

                            Search

                        </label>

                        <input

                            v-model="search"

                            type="text"

                            placeholder="Search code, name, bank..."

                            class="
                                w-full
                                rounded-xl
                                border
                                border-gray-300
                                px-4
                                py-3
                                text-sm
                                transition
                                focus:border-indigo-500
                                focus:outline-none
                                focus:ring-2
                                focus:ring-indigo-100
                            "

                        >

                    </div>

                    <!-- Type -->

                    <div

                        class="
                            lg:col-span-2
                        "

                    >

                        <label

                            class="
                                mb-2
                                block
                                text-sm
                                font-medium
                                text-gray-700
                            "

                        >

                            Type

                        </label>

                        <SearchableSelect

                            v-model="type"

                            :options="typeOptions"

                            placeholder="All Type"

                        />

                    </div>

                    <!-- Status -->

                    <div

                        class="
                            lg:col-span-2
                        "

                    >

                        <label

                            class="
                                mb-2
                                block
                                text-sm
                                font-medium
                                text-gray-700
                            "

                        >

                            Status

                        </label>

                        <SearchableSelect

                            v-model="status"

                            :options="statusOptions"

                            placeholder="All Status"

                        />

                    </div>

                    <!-- Button -->

                    <div

                        class="
                            lg:col-span-3
                            flex
                            justify-left
                        "

                    >

                        <BaseButton

                            variant="danger"

                            @click="resetFilter"

                        >

                            Reset

                        </BaseButton>

                    </div>

                </div>

            </Card>
   
          <!-- end card toobar-->

          <!-- ===================================================== -->
            <!-- Data Table -->
            <!-- ===================================================== -->

            <Card>
                <!-- ===================================================== -->
                <!-- Bulk Action -->
                <!-- ===================================================== -->

                <Transition

                    enter-active-class="duration-200"

                    leave-active-class="duration-150"

                >

                <div

                    v-if="hasSelection"

                    class="
                        flex
                        items-center
                        justify-between
                        rounded-t-xl
                        border-b
                        border-gray-200
                        bg-indigo-50
                        px-6
                        py-4
                    "

                >

                    <div

                        class="
                            text-sm
                            font-medium
                            text-indigo-700
                        "

                    >

                        {{ selectedCount }}

                        item selected

                    </div>

                    <div

                        class="
                            flex
                            items-center
                            gap-3
                        "

                    >

                        <BaseButton

                            variant="secondary"

                        >

                            Export

                        </BaseButton>

                        <BaseButton

                            variant="secondary"

                        >

                            Activate

                        </BaseButton>

                        <BaseButton

                            variant="secondary"

                        >

                            Deactivate

                        </BaseButton>

                        <BaseButton

                            variant="danger"

                        >

                            Delete

                        </BaseButton>

                    </div>

                </div>

                </Transition>
                <DataTable>

                    <DataTableHead>

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

                        <DataTableHeaderCell>

                            Code

                        </DataTableHeaderCell>

                        <DataTableHeaderCell>

                            Name

                        </DataTableHeaderCell>

                        <DataTableHeaderCell>

                            Type

                        </DataTableHeaderCell>

                        <DataTableHeaderCell
                            align="right"
                        >

                            Balance

                        </DataTableHeaderCell>

                        <DataTableHeaderCell
                            align="center"
                        >

                            Status

                        </DataTableHeaderCell>

                        <DataTableHeaderCell
                            align="center"
                            width="150px"
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

                                <DataTableCell>

                                    {{ item.code }}

                                </DataTableCell>

                                <DataTableCell>

                                    {{ item.name }}

                                </DataTableCell>

                                <DataTableCell>

                                    {{ item.type }}

                                </DataTableCell>

                                <DataTableCell

                                    align="right"

                                >

                                    {{

                                        formatCurrency(

                                            item.current_balance

                                        )

                                    }}

                                </DataTableCell>

                               <DataTableCell

                                    align="center"

                                >

                                   <StatusBadge
                                        :active="item.is_active === 1"
                                    />

                                </DataTableCell>

                                <DataTableCell

                                    align="center"

                                >

                                   <TableAction

                                    @show="showCashBank(item)"

                                    @edit="editCashBank(item)"

                                    @delete="openDelete(item)"

                                />
                                </DataTableCell>

                            </DataTableRow>

                        </template>

                        <template

                            v-else

                        >

                            <TableEmpty

                                title="No Cash Bank Found"

                                description="Click New Cash Bank to create your first account."

                            />

                        </template>

                    </DataTableBody>

                </DataTable>

            </Card>
            <!-- end card databale-->
             <ConfirmDeleteModal

    :show="showDelete"

    title="Delete Cash Bank"

    :message="deleteMessage"

    @close="closeDelete"

    @confirm="deleteCashBank"

/>
    </div>

</AuthenticatedLayout>

</template>