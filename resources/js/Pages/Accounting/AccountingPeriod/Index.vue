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

import AppLayout from '@/Layouts/AppLayout.vue'

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

import ActionDropdown
    from '@/Components/Action/ActionDropdown.vue'

import ConfirmDeleteModal
    from '@/Components/Modal/ConfirmDeleteModal.vue'

import LoadingOverlay
    from '@/Components/Feedback/LoadingOverlay.vue'

import {
    success,
    error,
} from '@/Utils'


const props = defineProps({

    periods: {

        type: Object,

        default: () => ({
            data: [],
        }),

    },

    fiscalYears: {

        type: Array,

        default: () => [],

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

    fiscal_year_id:
        props.filters?.fiscal_year_id
        ?? '',

    per_page:
        props.filters?.per_page
        ?? 12,

})


const sort = ref(

    props.filters?.sort
    ?? 'period_number'

)


const direction = ref(

    props.filters?.direction
    ?? 'asc'

)


let debounceTimer = null


/*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/

const pageTitle = computed(
    () => 'Accounting Period'
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
            'accounting-periods.index'
        ),

        {

            search:
                filters.search,

            status:
                filters.status,

            fiscal_year_id:
                filters.fiscal_year_id,

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


watch(

    () => filters.fiscal_year_id,

    () => {

        loadData()

    }

)


function refresh()
{
    filters.search = ''

    filters.status = ''

    filters.fiscal_year_id = ''

    filters.per_page = 12

    sort.value = 'period_number'

    direction.value = 'asc'

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

function showPeriod(period)
{
    router.visit(

        route(
            'accounting-periods.show',
            period.id
        )

    )
}


/*
|--------------------------------------------------------------------------
| Close / Reopen
|--------------------------------------------------------------------------
*/

const selectedPeriod = ref(null)

const showAction = ref(false)

const actionType = ref(null)


const actionTitle = computed(() => {

    if (
        actionType.value === 'close'
    ) {

        return 'Close Accounting Period'

    }

    if (
        actionType.value === 'reopen'
    ) {

        return 'Reopen Accounting Period'

    }

    return ''

})


const actionMessage = computed(() => {

    if (
        !selectedPeriod.value
    ) {

        return ''

    }


    if (
        actionType.value === 'close'
    ) {

        return `Are you sure you want to close accounting period "${selectedPeriod.value.name}"?`

    }


    if (
        actionType.value === 'reopen'
    ) {

        return `Are you sure you want to reopen accounting period "${selectedPeriod.value.name}"?`

    }


    return ''

})


const actionConfirmText = computed(() => {

    return actionType.value === 'close'
        ? 'Close'
        : 'Reopen'

})


const actionVariant = computed(() => {

    return actionType.value === 'close'
        ? 'warning'
        : 'success'

})


function openClose(period)
{
    selectedPeriod.value = period

    actionType.value = 'close'

    showAction.value = true
}


function openReopen(period)
{
    selectedPeriod.value = period

    actionType.value = 'reopen'

    showAction.value = true
}


function closeAction()
{
    selectedPeriod.value = null

    actionType.value = null

    showAction.value = false
}


function confirmAction()
{
    if (
        !selectedPeriod.value
    ) {

        return

    }


    const periodId =
        selectedPeriod.value.id


    const routeName =
        actionType.value === 'close'
            ? 'accounting-periods.close'
            : 'accounting-periods.reopen'


    router.post(

        route(
            routeName,
            periodId
        ),

        {},

        {

            preserveScroll: true,

            onSuccess: () => {

                closeAction()

                success(

                    actionType.value === 'close'
                        ? 'Accounting period closed successfully.'
                        : 'Accounting period reopened successfully.'

                )

            },

            onError: () => {

                error(

                    actionType.value === 'close'
                        ? 'Failed to close accounting period.'
                        : 'Failed to reopen accounting period.'

                )

            },

        }

    )

}


/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

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


function periodName(period)
{
    return (
        period?.name
        ?? `Period ${period?.period_number ?? '-'}`
    )
}


function fiscalYear(period)
{
    return (
        period?.fiscalYear?.year
        ?? '-'
    )
}


function companyName(period)
{
    return (
        period?.company?.company_name
        ?? '-'
    )
}


function isOpen(period)
{
    return period?.status === 'Open'
}


function isClosed(period)
{
    return period?.status === 'Closed'
}
const fiscalYearOptions = computed(() => {

    return [

        {
            id: '',
            year: 'All Fiscal Years',
        },

        ...props.fiscalYears,

    ]

})
watch(

    () => filters.fiscal_year_id,

    () => {

        loadData()

    }

)
</script>


<template>

    <Head
        :title="pageTitle"
    />


    <AppLayout>

        <div class="space-y-6">

            <!-- ===================================================== -->
            <!-- Header -->
            <!-- ===================================================== -->

            <PageHeader

                icon="📆"

                title="Accounting Period"

                subtitle="Manage monthly accounting periods."

            />


            <!-- ===================================================== -->
            <!-- Main Card -->
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

        placeholder="Search accounting period..."

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

        v-model="filters.fiscal_year_id"

        :options="fiscalYearOptions"

        :get-label="
            item =>
            item.year
        "

        :get-value="
            item =>
            item.id
        "

        placeholder="All Fiscal Years"

    />

    <SearchableSelect

        v-model="filters.status"

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

                        text="Loading Accounting Periods..."

                    />


                    <DataTable

                        v-if="
                            periods.data?.length
                        "

                        sticky-header

                        max-height="650px"

                    >

                        <DataTableHead sticky>

                            <!-- Period -->

                            <DataTableHeaderCell

                                sortable

                                column="period_number"

                                :sort="sort"

                                :direction="direction"

                                @sort="sortBy"

                                width="180px"

                            >

                                Period

                            </DataTableHeaderCell>


                            <!-- Fiscal Year -->

                            <DataTableHeaderCell

                                sortable

                                column="name"

                                :sort="sort"

                                :direction="direction"

                                @sort="sortBy"

                                width="180px"

                            >

                                Fiscal Year

                            </DataTableHeaderCell>


                            <!-- Company -->

                            <DataTableHeaderCell

                                width="220px"

                            >

                                Company

                            </DataTableHeaderCell>


                            <!-- Start -->

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


                            <!-- End -->

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


                            <!-- Status -->

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


                            <!-- Actions -->

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
                                    period in periods.data
                                "

                                :key="
                                    period.id
                                "
                            >

                                <!-- Period -->

                                <DataTableCell>

                                    <div
                                        class="
                                            font-semibold
                                            text-gray-900
                                        "
                                    >

                                        {{
                                            periodName(
                                                period
                                            )
                                        }}

                                    </div>


                                    <div
                                        class="
                                            mt-0.5
                                            text-xs
                                            text-gray-500
                                        "
                                    >

                                        Period
                                        {{
                                            period.period_number
                                            ?? '-'
                                        }}

                                    </div>

                                </DataTableCell>

                                <DataTableCell>

                                    <div class="flex flex-col">

                                        <span
                                            class="
                                                font-semibold
                                                text-gray-900
                                            "
                                        >
                                            {{
                                                period.fiscal_year?.year
                                                ?? '-'
                                            }}
                                        </span>

                                    </div>

                                </DataTableCell>


                                <!-- Company -->

                                <DataTableCell>

                                    {{
                                        companyName(
                                            period
                                        )
                                    }}

                                </DataTableCell>


                                <!-- Start Date -->

                                <DataTableCell>

                                    {{
                                        formatDate(
                                            period.start_date
                                        )
                                    }}

                                </DataTableCell>


                                <!-- End Date -->

                                <DataTableCell>

                                    {{
                                        formatDate(
                                            period.end_date
                                        )
                                    }}

                                </DataTableCell>


                                <!-- Status -->

                                <DataTableCell
                                    align="center"
                                >

                                    <StatusBadge

                                        :status="
                                            period.status
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

                                        :show-edit="false"

                                        :show-delete="false"

                                        :show-submit="false"

                                        :show-approve="false"

                                        :show-send="false"

                                        :show-confirm="false"

                                        :show-cancel="false"

                                        :show-post="false"

                                        :show-reject="false"

                                        :show-close="
                                            isOpen(period)
                                        "

                                        :show-reopen="
                                            isClosed(period)
                                        "

                                        @view="
                                            showPeriod(
                                                period
                                            )
                                        "

                                        @close="
                                            openClose(
                                                period
                                            )
                                        "

                                        @reopen="
                                            openReopen(
                                                period
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

                        icon="📆"

                        title="No Accounting Periods Found"

                        description="
                            There are no accounting periods available.
                        "

                    />

                </div>


                <!-- ================================================= -->
                <!-- Pagination -->
                <!-- ================================================= -->

                <div
                    class="mt-6"
                >

                    <TablePagination

                        :data="periods"

                        label="Accounting Periods"

                    />

                </div>

            </Card>

        </div>

    </AppLayout>


    <!-- ============================================================= -->
    <!-- Close / Reopen Modal -->
    <!-- ============================================================= -->

    <ConfirmDeleteModal

        :show="showAction"

        :title="actionTitle"

        :message="actionMessage"

        :confirm-text="actionConfirmText"

        :confirm-variant="actionVariant"

        @close="closeAction"

        @confirm="confirmAction"

    />

</template>