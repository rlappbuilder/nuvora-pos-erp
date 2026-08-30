<script setup>

import {
    ref,
    reactive,
    computed,
    watch,
    onMounted,
    onUnmounted,
} from 'vue'

import {
    Head,
    router,
} from '@inertiajs/vue3'


import StatsCard
    from '@/Components/Card/StatsCard.vue'

import PageHeader
    from '@/Components/Layout/PageHeader.vue'

import Card
    from '@/Components/Layout/Card.vue'


import DataTable
    from '@/Components/Table/DataTable.vue'

import DataTableHead
    from '@/Components/Table/DataTableHead.vue'

import DataTableBody
    from '@/Components/Table/DataTableBody.vue'

import DataTableHeaderCell
    from '@/Components/Table/DataTableHeaderCell.vue'

import DataTableRow
    from '@/Components/Table/DataTableRow.vue'

import DataTableCell
    from '@/Components/Table/DataTableCell.vue'

import TablePagination
    from '@/Components/Table/TablePagination.vue'

import TableEmpty
    from '@/Components/Table/TableEmpty.vue'


import StatusBadge
    from '@/Components/Display/StatusBadge.vue'

import SearchableSelect
    from '@/Components/Form/SearchableSelect.vue'


import BaseButton
    from '@/Components/Button/BaseButton.vue'

import ConfirmDeleteModal
    from '@/Components/Modal/ConfirmDeleteModal.vue'

import ActionDropdown
    from '@/Components/Action/ActionDropdown.vue'


import LoadingOverlay
    from '@/Components/Feedback/LoadingOverlay.vue'


import AppLayout
    from '@/Layouts/AppLayout.vue'


import {
    success,
    error,
} from '@/Utils'


import {
    PlusIcon,
} from '@heroicons/vue/24/solid'


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    fiscalYears: {

        type: Object,

        default: () => ({

            data: [],

        }),

    },

    summary: {

        type: Object,

        default: () => ({

            total_fiscal_years: 0,

            open_fiscal_years: 0,

            closed_fiscal_years: 0,

            current_year: null,

        }),

    },

    filters: {

        type: Object,

        default: () => ({}),

    },

})


/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const loading = ref(false)


const filters = reactive({

    search:
        props.filters?.search
        ?? '',

    status:
        props.filters?.status
        ?? '',

    per_page:
        props.filters?.per_page
        ?? 10,

})


const sort = ref(

    props.filters?.sort
    ?? 'year'

)


const direction = ref(

    props.filters?.direction
    ?? 'desc'

)


let debounceTimer = null


/*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/

const pageTitle = computed(

    () => 'Fiscal Year'

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
        value: 'Open',
        label: 'Open',
    },

    {
        value: 'Closed',
        label: 'Closed',
    },

]


/*
|--------------------------------------------------------------------------
| Loading
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| Data Loading
|--------------------------------------------------------------------------
*/

function loadData()
{
    router.get(

        route(
            'fiscal-years.index'
        ),

        {

            search:
                filters.search,

            status:
                filters.status,

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


/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

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


function refresh()
{
    filters.search = ''

    filters.status = ''

    filters.per_page = 10

    sort.value = 'year'

    direction.value = 'desc'

    loadData()
}


/*
|--------------------------------------------------------------------------
| Sorting
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| Navigation
|--------------------------------------------------------------------------
*/

function create()
{
    router.visit(

        route(
            'fiscal-years.create'
        )

    )
}


function showFiscalYear(fiscalYear)
{
    router.visit(

        route(
            'fiscal-years.show',
            fiscalYear.id
        )

    )
}


function editFiscalYear(fiscalYear)
{
    if (
        statusValue(fiscalYear) !== 'Open'
    ) {

        return

    }


    router.visit(

        route(
            'fiscal-years.edit',
            fiscalYear.id
        )

    )
}


/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function statusValue(fiscalYear)
{
    return fiscalYear?.status
        ?? (
            fiscalYear?.is_closed
                ? 'Closed'
                : 'Open'
        )
}


function periodCount(fiscalYear)
{
    return fiscalYear?.periods_count ?? 0
}


function formatDate(value)
{
    if (!value) {

        return '-'

    }


    return new Intl.DateTimeFormat(

        'id-ID',

        {

            day: '2-digit',

            month: 'short',

            year: 'numeric',

        }

    ).format(

        new Date(value)

    )
}


/*
|--------------------------------------------------------------------------
| Close Fiscal Year
|--------------------------------------------------------------------------
*/

const showClose = ref(false)

const selectedFiscalYear = ref(null)


const closeMessage = computed(() => {

    if (
        !selectedFiscalYear.value
    ) {

        return ''

    }


    return `Are you sure you want to close fiscal year "${selectedFiscalYear.value.year}"? All accounting periods must be closed first.`

})


function openClose(fiscalYear)
{
    if (
        statusValue(fiscalYear) !== 'Open'
    ) {

        return

    }


    selectedFiscalYear.value =
        fiscalYear

    showClose.value = true
}


function closeFiscalYear()
{
    if (
        !selectedFiscalYear.value
    ) {

        return

    }


    router.post(

        route(
            'fiscal-years.close',
            selectedFiscalYear.value.id
        ),

        {},

        {

            preserveScroll: true,

            onSuccess: () => {

                showClose.value = false

                selectedFiscalYear.value = null

                success(
                    'Fiscal year closed successfully.'
                )

            },

            onError: (errors) => {

                console.log(
                    'CLOSE FISCAL YEAR ERROR:',
                    errors
                )

                error(
                    errors?.fiscal_year
                    ?? 'Failed to close fiscal year.'
                )

            },

        }

    )
}


function closeClose()
{
    showClose.value = false

    selectedFiscalYear.value = null
}


/*
|--------------------------------------------------------------------------
| Reopen Fiscal Year
|--------------------------------------------------------------------------
*/

const showReopen = ref(false)

const reopenMessage = computed(() => {

    if (
        !selectedFiscalYear.value
    ) {

        return ''

    }


    return `Are you sure you want to reopen fiscal year "${selectedFiscalYear.value.year}"?`

})


function openReopen(fiscalYear)
{
    if (
        statusValue(fiscalYear) !== 'Closed'
    ) {

        return

    }


    selectedFiscalYear.value =
        fiscalYear

    showReopen.value = true
}


function reopenFiscalYear()
{
    if (
        !selectedFiscalYear.value
    ) {

        return

    }


    router.post(

        route(
            'fiscal-years.reopen',
            selectedFiscalYear.value.id
        ),

        {},

        {

            preserveScroll: true,

            onSuccess: () => {

                showReopen.value = false

                selectedFiscalYear.value = null

                success(
                    'Fiscal year reopened successfully.'
                )

            },

            onError: (errors) => {

                console.log(
                    'REOPEN FISCAL YEAR ERROR:',
                    errors
                )

                error(
                    errors?.fiscal_year
                    ?? 'Failed to reopen fiscal year.'
                )

            },

        }

    )
}


function closeReopen()
{
    showReopen.value = false

    selectedFiscalYear.value = null
}


/*
|--------------------------------------------------------------------------
| Delete
|--------------------------------------------------------------------------
*/

const deleteItem = ref(null)

const showDelete = ref(false)


const deleteMessage = computed(() => {

    if (
        !deleteItem.value
    ) {

        return ''

    }


    return `Are you sure you want to delete fiscal year "${deleteItem.value.year}"?`

})


function openDelete(fiscalYear)
{
    if (
        statusValue(fiscalYear) !== 'Open'
    ) {

        return

    }


    deleteItem.value =
        fiscalYear

    showDelete.value = true
}


function closeDelete()
{
    deleteItem.value = null

    showDelete.value = false
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
            'fiscal-years.destroy',
            deleteItem.value.id
        ),

        {

            preserveScroll: true,

            onSuccess: () => {

                closeDelete()

                success(
                    'Fiscal Year deleted successfully.'
                )

            },

            onError: () => {

                error(
                    'Failed to delete Fiscal Year.'
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

            <PageHeader

                icon="📅"

                title="Fiscal Year"

                subtitle="Manage accounting fiscal years and financial periods."

            />


            <!-- ===================================================== -->
            <!-- Stats -->
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

                    title="Total Fiscal Years"

                    :value="
                        summary.total_fiscal_years
                    "

                    icon="📅"

                />


                <StatsCard

                    title="Open"

                    :value="
                        summary.open_fiscal_years
                    "

                    icon="🟢"

                />


                <StatsCard

                    title="Closed"

                    :value="
                        summary.closed_fiscal_years
                    "

                    icon="🔒"

                />


                <StatsCard

                    title="Current Year"

                    :value="
                        summary.current_year ?? '-'
                    "

                    icon="⭐"

                />

            </div>


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

                            placeholder="Search fiscal year..."

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

                            Add Fiscal Year

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

                        text="Loading Fiscal Years..."

                    />


                    <DataTable

                        v-if="
                            fiscalYears.data?.length
                        "

                        sticky-header

                        max-height="650px"

                    >

                        <DataTableHead sticky>

                            <DataTableHeaderCell

                                sortable

                                column="year"

                                :sort="sort"

                                :direction="direction"

                                @sort="sortBy"

                                width="140px"

                            >

                                Fiscal Year

                            </DataTableHeaderCell>


                            <DataTableHeaderCell

                                sortable

                                column="start_date"

                                :sort="sort"

                                :direction="direction"

                                @sort="sortBy"

                                width="160px"

                            >

                                Start Date

                            </DataTableHeaderCell>


                            <DataTableHeaderCell

                                sortable

                                column="end_date"

                                :sort="sort"

                                :direction="direction"

                                @sort="sortBy"

                                width="160px"

                            >

                                End Date

                            </DataTableHeaderCell>


                            <DataTableHeaderCell

                                width="120px"

                                align="center"

                            >

                                Periods

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

                                v-for="
                                    fiscalYear
                                    in fiscalYears.data
                                "

                                :key="
                                    fiscalYear.id
                                "
                            >

                                <!-- Fiscal Year -->

                                <DataTableCell>

                                    <span
                                        class="
                                            font-semibold
                                            text-gray-900
                                        "
                                    >

                                        {{
                                            fiscalYear.year
                                            ?? '-'
                                        }}

                                    </span>


                                    <div
                                        v-if="
                                            fiscalYear.description
                                        "
                                        class="
                                            mt-0.5
                                            text-xs
                                            text-gray-500
                                        "
                                    >

                                        {{
                                            fiscalYear.description
                                        }}

                                    </div>

                                </DataTableCell>


                                <!-- Start Date -->

                                <DataTableCell>

                                    {{
                                        formatDate(
                                            fiscalYear.start_date
                                        )
                                    }}

                                </DataTableCell>


                                <!-- End Date -->

                                <DataTableCell>

                                    {{
                                        formatDate(
                                            fiscalYear.end_date
                                        )
                                    }}

                                </DataTableCell>


                                <!-- Periods -->

                                <DataTableCell
                                    align="center"
                                >

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
                                            periodCount(
                                                fiscalYear
                                            )
                                        }}

                                        Periods

                                    </span>

                                </DataTableCell>


                                <!-- Status -->

                                <DataTableCell
                                    align="center"
                                >

                                    <StatusBadge

                                        :status="
                                            statusValue(
                                                fiscalYear
                                            )
                                        "

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

                                        :show-edit="
                                            statusValue(
                                                fiscalYear
                                            ) === 'Open'
                                        "

                                        :show-delete="
                                            statusValue(
                                                fiscalYear
                                            ) === 'Open'
                                        "

                                        :show-close="
                                            statusValue(
                                                fiscalYear
                                            ) === 'Open'
                                        "

                                        :show-reopen="
                                            statusValue(
                                                fiscalYear
                                            ) === 'Closed'
                                        "

                                        @view="
                                            showFiscalYear(
                                                fiscalYear
                                            )
                                        "

                                        @edit="
                                            editFiscalYear(
                                                fiscalYear
                                            )
                                        "

                                        @close="
                                            openClose(
                                                fiscalYear
                                            )
                                        "

                                        @reopen="
                                            openReopen(
                                                fiscalYear
                                            )
                                        "

                                        @delete="
                                            openDelete(
                                                fiscalYear
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

                        icon="📅"

                        title="No Fiscal Years Found"

                        description="
                            There are no fiscal years available.
                            Create your first fiscal year to start
                            the accounting cycle.
                        "
                    >

                        <template #action>

                            <BaseButton
                                @click="create"
                            >

                                Create Fiscal Year

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

                        :data="fiscalYears"

                        label="Fiscal Years"

                    />

                </div>

            </Card>

        </div>

    </AppLayout>


    <!-- ============================================================= -->
    <!-- Delete -->
    <!-- ============================================================= -->

    <ConfirmDeleteModal

        :show="showDelete"

        title="Delete Fiscal Year"

        :message="deleteMessage"

        confirm-text="Delete"

        confirm-variant="danger"

        @close="closeDelete"

        @confirm="confirmDelete"

    />


    <!-- ============================================================= -->
    <!-- Close -->
    <!-- ============================================================= -->

    <ConfirmDeleteModal

        :show="showClose"

        title="Close Fiscal Year"

        :message="closeMessage"

        confirm-text="Close"

        confirm-variant="warning"

        @close="closeClose"

        @confirm="closeFiscalYear"

    />


    <!-- ============================================================= -->
    <!-- Reopen -->
    <!-- ============================================================= -->

    <ConfirmDeleteModal

        :show="showReopen"

        title="Reopen Fiscal Year"

        :message="reopenMessage"

        confirm-text="Reopen"

        confirm-variant="success"

        @close="closeReopen"

        @confirm="reopenFiscalYear"

    />

</template>