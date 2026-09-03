<script setup>

import { ref, reactive, computed, watch, onMounted, onUnmounted,} from 'vue'
import { Head,router,} from '@inertiajs/vue3'
import StatsCard from '@/Components/Card/StatsCard.vue'
import PageHeader from '@/Components/Layout/PageHeader.vue'
import Card from '@/Components/Layout/Card.vue'
import DataTable from '@/Components/Table/DataTable.vue'
import DataTableHead from '@/Components/Table/DataTableHead.vue'
import DataTableBody from '@/Components/Table/DataTableBody.vue'
import DataTableHeaderCell   from '@/Components/Table/DataTableHeaderCell.vue'
import DataTableRow  from '@/Components/Table/DataTableRow.vue'
import DataTableCell   from '@/Components/Table/DataTableCell.vue'
import TablePagination  from '@/Components/Table/TablePagination.vue'
import TableEmpty  from '@/Components/Table/TableEmpty.vue'
import StatusBadge    from '@/Components/Display/StatusBadge.vue'
import SearchableSelect  from '@/Components/Form/SearchableSelect.vue'
import BaseButton from '@/Components/Button/BaseButton.vue'
import ConfirmDeleteModal from '@/Components/Modal/ConfirmDeleteModal.vue'
import ActionDropdown  from '@/Components/Action/ActionDropdown.vue'
import LoadingOverlay from '@/Components/Feedback/LoadingOverlay.vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import { success, error,} from '@/Utils'
import { PlusIcon,} from '@heroicons/vue/24/solid'


const props = defineProps({

    journals: {

        type: Object,

        default: () => ({

            data: [],

        }),

    },

    stats: {

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

})

const loading = ref(false)
const filters = reactive({

    search:
        props.filters?.search
        ?? '',

    status:
        props.filters?.status
        ?? '',

    type:
        props.filters?.type
        ?? '',

    per_page:
        props.filters?.per_page
        ?? 10,

})


const sort = ref(

    props.filters?.sort
    ?? 'code'

)


const direction = ref(

    props.filters?.direction
    ?? 'asc'

)


let debounceTimer = null
const pageTitle = computed(

    () => 'Accounting Journal'

)

const typeOptions = [

    {
        value: '',
        label: 'All Types',
    },

    {
        value: 'General',
        label: 'General',
    },

    {
        value: 'Sales',
        label: 'Sales',
    },

    {
        value: 'Purchase',
        label: 'Purchase',
    },

    {
        value: 'Cash',
        label: 'Cash',
    },

    {
        value: 'Bank',
        label: 'Bank',
    },

    {
        value: 'Adjustment',
        label: 'Adjustment',
    },

    {
        value: 'Opening',
        label: 'Opening',
    },

]
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

function startLoading()
{
    loading.value = true
}


function stopLoading()
{
    loading.value = false
}


let removeStartListener

let removeFinishListener


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

    removeStartListener?.()

    removeFinishListener?.()

    clearTimeout(
        debounceTimer
    )

})
function loadData()
{
    router.get(

        route(
            'accounting-journals.index'
        ),

        {

            search:
                filters.search,

            status:
                filters.status,

            type:
                filters.type,

            per_page:
                filters.per_page,

            sort:
                sort.value,

            direction:
                direction.value,

        },

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

        clearTimeout(
            debounceTimer
        )

        debounceTimer =
            setTimeout(

                () => {

                    loadData()

                },

                500

            )

    }

)


watch(

    () => filters.status,

    () => {

        loadData()

    }

)


watch(

    () => filters.type,

    () => {

        loadData()

    }

)


function refresh()
{
    filters.search = ''

    filters.status = ''

    filters.type = ''

    filters.per_page = 10

    sort.value = 'code'

    direction.value = 'asc'

    loadData()
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


    loadData()
}

function create()
{
    router.visit(

        route(
            'accounting-journals.create'
        )

    )
}


function showJournal(journal)
{
    router.visit(

        route(
            'accounting-journals.show',
            journal.id
        )

    )
}


function editJournal(journal)
{
    router.visit(

        route(
            'accounting-journals.edit',
            journal.id
        )

    )
}

const deleteItem = ref(null)
const showDelete = ref(false)
const deleteMessage = computed(() => {

    if (
        !deleteItem.value
    ) {

        return ''

    }


    return `Are you sure you want to delete accounting journal "${deleteItem.value.code} - ${deleteItem.value.name}"?`

})


function openDelete(journal)
{
    deleteItem.value =
        journal

    showDelete.value =
        true
}


function closeDelete()
{
    deleteItem.value =
        null

    showDelete.value =
        false
}


function confirmDelete()
{
    if (
        !deleteItem.value
    ) {

        return

    }


    router.delete(

        route(
            'accounting-journals.destroy',
            deleteItem.value.id
        ),

        {

            preserveScroll: true,

            onSuccess: () => {

                closeDelete()

                success(
                    'Accounting journal deleted successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to delete accounting journal.'
                )

            },

        }

    )

}

</script>


<template>

    <Head
        :title="pageTitle"
    />


    <AppLayout>

        <div
            class="space-y-6"
        >

            <!-- ===================================================== -->
            <!-- Header -->
            <!-- ===================================================== -->
<!-- <PageHeader

              title="Accounting Journal"

             />
 -->
           

           
            <!-- ===================================================== -->
            <!-- Data Table -->
            <!-- ===================================================== -->

            <Card>

                <!-- ================================================= -->
                <!-- Toolbar -->
                <!-- ================================================= -->

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
                            flex-col
                            gap-3
                            lg:flex-row
                            lg:items-center
                        "
                    >

                        <input

                            v-model="filters.search"

                            type="text"

                            placeholder="Search journal..."

                            class="
                                w-full
                                rounded-xl
                                border
                                border-gray-300
                                px-4
                                py-2.5
                                lg:w-80
                            "

                        />


                        <SearchableSelect

                            v-model="filters.type"

                            :options="typeOptions"

                            label="label"

                            value-key="value"

                            placeholder="All Types"

                        />


                        <SearchableSelect

                            v-model="filters.status"

                            :options="statusOptions"

                            label="label"

                            value-key="value"

                            placeholder="All Status"

                        />

                    </div>


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

                            class="w-full md:w-auto"

                            @click="create"

                        >

                            <template #icon>

                                <PlusIcon
                                    class="h-5 w-5"
                                />

                            </template>

                            Add

                        </BaseButton>

                    </div>

                </div>


                <!-- ================================================= -->
                <!-- Table -->
                <!-- ================================================= -->

                <div
                    class="relative mt-6"
                >

                    <LoadingOverlay

                        :show="loading"

                        text="Loading Accounting Journals..."

                    />


                    <DataTable

                        v-if="
                            journals.data?.length
                        "

                        sticky-header

                        max-height="650px"

                    >

                        <DataTableHead sticky>

                            <DataTableHeaderCell

                                sortable

                                column="code"

                                :sort="sort"

                                :direction="direction"

                                @sort="sortBy"

                                width="130px"

                            >

                                Code

                            </DataTableHeaderCell>


                            <DataTableHeaderCell

                                sortable

                                column="name"

                                :sort="sort"

                                :direction="direction"

                                @sort="sortBy"

                                width="240px"

                            >

                                Journal Name

                            </DataTableHeaderCell>


                            <DataTableHeaderCell

                                sortable

                                column="type"

                                :sort="sort"

                                :direction="direction"

                                @sort="sortBy"

                                width="160px"

                            >

                                Type

                            </DataTableHeaderCell>


                            <DataTableHeaderCell

                                width="240px"

                            >

                                Company

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

                                v-for="
                                    journal
                                    in journals.data
                                "

                                :key="
                                    journal.id
                                "
                            >
                                <!-- Code -->

                                <DataTableCell>

                                    <span
                                        class="
                                            font-semibold
                                            text-gray-900
                                        "
                                    >

                                        {{
                                            journal.code
                                            ?? '-'
                                        }}

                                    </span>

                                </DataTableCell>


                                <!-- Name -->

                                <DataTableCell>

                                    <div
                                        class="
                                            font-medium
                                            text-gray-900
                                        "
                                    >

                                        {{
                                            journal.name
                                            ?? '-'
                                        }}

                                    </div>


                                    <div
                                        v-if="
                                            journal.description
                                        "
                                        class="
                                            mt-0.5
                                            text-xs
                                            text-gray-500
                                        "
                                    >

                                        {{
                                            journal.description
                                        }}

                                    </div>

                                </DataTableCell>


                                <!-- Type -->

                                <DataTableCell>

                                    <span
                                        class="
                                            inline-flex
                                            items-center
                                            rounded-full
                                            bg-gray-100
                                            px-2.5
                                            py-1
                                            text-xs
                                            font-medium
                                            text-gray-700
                                        "
                                    >

                                        {{
                                            journal.type
                                            ?? '-'
                                        }}

                                    </span>

                                </DataTableCell>


                                <!-- Company -->

                                <DataTableCell>

                                    {{
                                        journal.company
                                            ?.company_name
                                        ?? '-'
                                    }}

                                </DataTableCell>
                                <!-- Status -->

                                <DataTableCell
                                    align="center"
                                >

                                    <StatusBadge
                                        :status="journal.is_active"
                                    />

                                </DataTableCell>
                                <!-- Actions -->

                                <DataTableCell
                                    align="center"
                                >

                                    <ActionDropdown

                                        :show-history="false"

                                        :show-duplicate="false"

                                        :show-export="false"

                                        :show-edit="true"

                                        :show-delete="true"

                                        :show-close="false"

                                        :show-reopen="false"

                                        @view="
                                            showJournal(
                                                journal
                                            )
                                        "

                                        @edit="
                                            editJournal(
                                                journal
                                            )
                                        "

                                        @delete="
                                            openDelete(
                                                journal
                                            )
                                        "

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

                        icon="📒"

                        title="No Accounting Journals Found"

                        description="
                            There are no accounting journals available.
                            Create your first accounting journal to start
                            managing journal entries.
                        "
                    >

                        <template #action>

                            <BaseButton
                                @click="create"
                            >

                                Create Accounting Journal

                            </BaseButton>

                        </template>

                    </TableEmpty>

                </div>


                <!-- ================================================= -->
                <!-- Pagination -->
                <!-- ================================================= -->

                <div
                    class="mt-6"
                >

                    <TablePagination

                        :data="journals"

                        label="Accounting Journals"

                    />

                </div>

            </Card>

        </div>

    </AppLayout>


    <!-- ============================================================= -->
    <!-- Delete Modal -->
    <!-- ============================================================= -->

    <ConfirmDeleteModal

        :show="showDelete"

        title="Delete Accounting Journal"

        :message="deleteMessage"

        confirm-text="Delete"

        confirm-variant="danger"

        @close="closeDelete"

        @confirm="confirmDelete"

    />

</template>