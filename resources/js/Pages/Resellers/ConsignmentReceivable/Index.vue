<script setup>

import {ref,reactive,computed,watch,onMounted,onUnmounted,toRefs,} from 'vue'
import {router,} from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import BaseButton from '@/Components/Button/BaseButton.vue'
import ActionDropdown from '@/Components/Action/ActionDropdown.vue'
import DataTable from '@/Components/Table/DataTable.vue'
import DataTableHead from '@/Components/Table/DataTableHead.vue'
import DataTableBody from '@/Components/Table/DataTableBody.vue'
import DataTableHeaderCell from '@/Components/Table/DataTableHeaderCell.vue'
import DataTableRow from '@/Components/Table/DataTableRow.vue'
import DataTableCell from '@/Components/Table/DataTableCell.vue'
import TablePagination from '@/Components/Table/TablePagination.vue'
import StatusBadge from '@/Components/Display/StatusBadge.vue'
import SearchableSelect from '@/Components/Form/SearchableSelect.vue'

import {LoadingOverlay,} from '@/Components/Feedback'

import {PlusIcon,} from '@heroicons/vue/24/solid'

import {success,error,formatDate,} from '@/Utils'

import FlatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'


const props = defineProps({

    receivables: {

        type: Object,

        default: () => ({

            data: [],

            current_page: 1,

            last_page: 1,

            per_page: 10,

            total: 0,

        }),

    },


    statistics: {

        type: Object,

        default: () => ({

            total: 0,

            draft: 0,

            submitted: 0,

            rejected: 0,

            approved: 0,

            posted: 0,

            cancelled: 0,

            total_transaction: 0,

        }),

    },


    branches: {

        type: Array,

        default: () => [],

    },


    resellers: {

        type: Array,

        default: () => [],

    },


    filters: {

        type: Object,

        default: () => ({}),

    },

})


const {
    receivables,
    statistics,
} = toRefs(props)


/*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/

const pageTitle = computed(
    () => 'Consignment Receivable'
)


/*
|--------------------------------------------------------------------------
| Loading
|--------------------------------------------------------------------------
*/

const loading = ref(false)

let removeStartListener
let removeFinishListener


const startLoading = () => {

    loading.value = true

}


const stopLoading = () => {

    loading.value = false

}


/*
|--------------------------------------------------------------------------
| Inertia Loading
|--------------------------------------------------------------------------
*/

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

})


/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

const filters = reactive({

    search:
        props.filters?.search ?? '',

    branch_id:
        props.filters?.branch_id ?? '',

    reseller_id:
        props.filters?.reseller_id ?? '',

    status:
        props.filters?.status ?? '',

    per_page:
        props.filters?.per_page ?? 10,

    date_from:
        props.filters?.date_from ?? '',

    date_to:
        props.filters?.date_to ?? '',

    date_range:
        props.filters?.date_from
        && props.filters?.date_to

            ? `${props.filters.date_from} to ${props.filters.date_to}`

            : props.filters?.date_from ?? '',

})


let debounceTimer = null


function loadData()
{

    let dateFrom = ''

    let dateTo = ''


    if (filters.date_range) {

        const dates =
            filters.date_range.split(
                ' to '
            )


        dateFrom =
            dates[0] ?? ''


        dateTo =
            dates[1]
            ?? dates[0]
            ?? ''

    }


    router.get(

        route(
            'consignment-receivables.index'
        ),

        {

            search:
                filters.search,

            branch_id:
                filters.branch_id,

            reseller_id:
                filters.reseller_id,

            status:
                filters.status,

            per_page:
                filters.per_page,

            date_from:
                dateFrom,

            date_to:
                dateTo,

            sort_by:
                sort.value,

            sort_direction:
                direction.value,

        },

        {

            preserveState:
                true,

            preserveScroll:
                true,

            replace:
                true,

        }

    )

}


/*
|--------------------------------------------------------------------------
| Search Watcher
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


/*
|--------------------------------------------------------------------------
| Filter Watchers
|--------------------------------------------------------------------------
*/

watch(

    () => filters.branch_id,

    () => {

        loadData()

    }

)


watch(

    () => filters.reseller_id,

    () => {

        loadData()

    }

)


watch(

    () => filters.status,

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

    () => filters.date_range,

    () => {

        loadData()

    }

)


/*
|--------------------------------------------------------------------------
| Refresh
|--------------------------------------------------------------------------
*/

function refresh()
{

    Object.assign(

        filters,

        {

            search: '',

            branch_id: '',

            reseller_id: '',

            status: '',

            per_page: 10,

            date_range: '',

        }

    )


    loadData()

}


/*
|--------------------------------------------------------------------------
| Status
|--------------------------------------------------------------------------
*/

const statusOptions = [

    {
        value: '',
        label: 'All Status',
    },

    {
        value: 'Draft',
        label: 'Draft',
    },

    {
        value: 'Submitted',
        label: 'Submitted',
    },

    {
        value: 'Rejected',
        label: 'Rejected',
    },

    {
        value: 'Approved',
        label: 'Approved',
    },

    {
        value: 'Posted',
        label: 'Posted',
    },

    {
        value: 'Cancelled',
        label: 'Cancelled',
    },

]


/*
|--------------------------------------------------------------------------
| Sorting
|--------------------------------------------------------------------------
*/

const sort =
    ref(
        props.filters?.sort_by ?? 'id'
    )


const direction =
    ref(
        props.filters?.sort_direction ?? 'desc'
    )


function sortBy(column)
{

    if (
        sort.value === column
    ) {

        direction.value =
            direction.value === 'asc'
                ? 'desc'
                : 'asc'

    } else {

        sort.value =
            column

        direction.value =
            'asc'

    }


    loadData()

}


/*
|--------------------------------------------------------------------------
| Actions
|--------------------------------------------------------------------------
*/

function openView(item)
{

    router.get(

        route(
            'consignment-receivables.show',
            item.id
        )

    )

}


function create()
{

    router.get(

        route(
            'consignment-receivables.create'
        )

    )

}
/*
|--------------------------------------------------------------------------
| Currency
|--------------------------------------------------------------------------
*/

const formatAmount = (value) => {

    return new Intl.NumberFormat(

        'id-ID',

        {

            minimumFractionDigits:
                0,

            maximumFractionDigits:
                0,

        }

    ).format(

        Number(value || 0)

    )

}


/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

const summaryCards = computed(() => [

    {

        key:
            'total',

        label:
            'Total Receivable',

        value:
            statistics.value?.total ?? 0,

        classes:
            'border-gray-100 bg-white',

        accent:
            'bg-gray-400',

    },

    {

        key:
            'posted',

        label:
            'Posted',

        value:
            statistics.value?.posted ?? 0,

        classes:
            'border-gray-100 bg-white',

        accent:
            'bg-gray-400',

    },

    {

        key:
            'cancelled',

        label:
            'Cancelled',

        value:
            statistics.value?.cancelled ?? 0,

        classes:
            'border-gray-100 bg-white',

        accent:
            'bg-gray-400',

    },

    {

        key:
            'total_transaction',

        label:
            'Total Transaction',

        value:
            statistics.value?.total_transaction ?? 0,

        classes:
            'border-gray-100 bg-white',

        accent:
            'bg-gray-400',

    },

])


/*
|--------------------------------------------------------------------------
| Summary Selection
|--------------------------------------------------------------------------
*/

const activeSummary =
    ref(null)


function selectSummary(key)
{

    activeSummary.value =
        activeSummary.value === key
            ? null
            : key


    if (
        key === 'posted'
    ) {

        filters.status =
            activeSummary.value
                ? 'Posted'
                : ''

        return

    }


    if (
        key === 'cancelled'
    ) {

        filters.status =
            activeSummary.value
                ? 'Cancelled'
                : ''

        return

    }


    if (
        key === 'total'
        ||
        key === 'total_transaction'
    ) {

        filters.status = ''

    }

}

</script>
<template>

<AppLayout>

    <div
        class="space-y-4"
    >

        <!-- ========================================================= -->
        <!-- Header -->
        <!-- ========================================================= -->

        <div
            class="
                flex
                flex-col
                gap-3
                sm:flex-row
                sm:items-center
                sm:justify-between
            "
        >

            <div>

                <h1
                    class="
                        text-xl
                        font-semibold
                        text-gray-900
                    "
                >
                    Consignment Receivable
                </h1>


                <p
                    class="
                        mt-1
                        text-sm
                        text-gray-500
                    "
                >
                    Manage consignment receivable transactions.
                </p>

            </div>


            <div
                class="
                    flex
                    items-center
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


        <!-- ========================================================= -->
        <!-- Filter -->
        <!-- ========================================================= -->

        <div
            class="
                rounded-xl
                border
                border-gray-100
                bg-white
                p-4
                shadow-sm
            "
        >

            <div
                class="
                    grid
                    grid-cols-1
                    gap-3
                    md:grid-cols-2
                    lg:grid-cols-4
                "
            >

                <!-- Date -->

                <div>

                    <label
                        class="
                            mb-1
                            block
                            text-xs
                            font-medium
                            text-gray-600
                        "
                    >
                        Payment Date
                    </label>


                    <FlatPickr
                        v-model="filters.date_range"
                        :config="{
                            mode: 'range',
                            dateFormat: 'Y-m-d',
                            allowInput: true,
                        }"
                        placeholder="Payment Date"
                        class="
                            w-full
                            rounded-lg
                            border
                            border-gray-200
                            px-3
                            py-2
                            text-sm
                        "
                    />

                </div>


                <!-- Reseller -->

                <div>

                    <label
                        class="
                            mb-1
                            block
                            text-xs
                            font-medium
                            text-gray-600
                        "
                    >
                        Reseller
                    </label>


                    <SearchableSelect
                        v-model="filters.reseller_id"
                        :options="resellers"
                        label="label"
                        value-key="id"
                        placeholder="All Resellers"
                    />

                </div>


                <!-- Branch -->

                <div>

                    <label
                        class="
                            mb-1
                            block
                            text-xs
                            font-medium
                            text-gray-600
                        "
                    >
                        Branch
                    </label>


                    <SearchableSelect
                        v-model="filters.branch_id"
                        :options="branches"
                        label="label"
                        value-key="id"
                        placeholder="All Branches"
                    />

                </div>


                <!-- Status -->

                <div>

                    <label
                        class="
                            mb-1
                            block
                            text-xs
                            font-medium
                            text-gray-600
                        "
                    >
                        Status
                    </label>


                    <SearchableSelect
                        v-model="filters.status"
                        :options="statusOptions"
                        label="label"
                        value-key="value"
                        placeholder="All Status"
                    />

                </div>

            </div>


            <!-- Search -->

            <div
                class="
                    mt-3
                    flex
                    flex-col
                    gap-1
                    sm:flex-row
                "
            >

                <input
                    v-model="filters.search"
                    type="text"
                    placeholder="Search receivable number, reseller..."
                    class="
                        min-w-0
                        flex-1
                        rounded-lg
                        border
                        border-gray-200
                        px-3
                        py-2
                        text-sm
                    "
                />

            </div>

        </div>


        <!-- ========================================================= -->
        <!-- Summary -->
        <!-- ========================================================= -->

        <div
            class="
                rounded-xl
                border
                border-gray-100
                bg-white
                p-3
                shadow-sm
            "
        >

            <div
                class="
                    grid
                    grid-cols-2
                    gap-2
                    sm:grid-cols-4
                "
            >

                <button
                    v-for="card in summaryCards"
                    :key="card.key"
                    type="button"
                    class="
                        relative
                        overflow-hidden
                        rounded-lg
                        border
                        px-4
                        py-3
                        text-left
                        transition
                        hover:-translate-y-px
                        hover:shadow-sm
                    "
                    :class="[
                        card.classes,

                        activeSummary === card.key
                            ? 'ring-2 ring-gray-300 ring-offset-1'
                            : '',
                    ]"
                    @click="selectSummary(card.key)"
                >

                    <span
                        class="
                            absolute
                            inset-y-0
                            left-0
                            w-1
                        "
                        :class="card.accent"
                    ></span>


                    <div
                        class="
                            text-xs
                            font-medium
                            text-gray-500
                        "
                    >
                        {{ card.label }}
                    </div>


                    <div
                        class="
                            mt-1
                            text-base
                            font-semibold
                            tabular-nums
                            text-gray-900
                        "
                    >

                        <template
                            v-if="
                                card.key ===
                                'total_transaction'
                            "
                        >

                            
                            {{
                                formatAmount(
                                    card.value
                                )
                            }}

                        </template>


                        <template
                            v-else
                        >

                            {{
                                formatAmount(
                                    card.value
                                )
                            }}

                        </template>

                    </div>

                </button>

            </div>

        </div>
        <!-- ========================================================= -->
        <!-- Table -->
        <!-- ========================================================= -->

        <div
            class="
                overflow-hidden
                rounded-xl
                border
                border-gray-100
                bg-white
                shadow-sm
            "
        >

            <!-- Toolbar -->

            <div
                class="
                    border-b
                    border-gray-100
                    px-4
                    py-3
                "
            >

                <div
                    class="
                        text-xs
                        text-gray-500
                    "
                >
                    Consignment Receivable Transactions
                </div>

            </div>


            <!-- Loading -->

            <LoadingOverlay
                :show="loading"
                text="Loading Consignment Receivable..."
            />


            <!-- Data -->

            <div
                v-if="
                    receivables?.data?.length
                "
                class="overflow-x-auto"
            >

                <DataTable
                    sticky-header
                    max-height="650px"
                >

                    <DataTableHead sticky>

                        <!-- Receivable -->

                        <DataTableHeaderCell
                            sortable
                            column="number"
                            :sort="sort"
                            :direction="direction"
                            @sort="sortBy"
                            width="180px"
                        >
                            Receivable
                        </DataTableHeaderCell>


                        <!-- Reseller -->

                        <DataTableHeaderCell
                            width="240px"
                        >
                            Reseller
                        </DataTableHeaderCell>


                        <!-- Branch -->

                        <DataTableHeaderCell
                            width="220px"
                        >
                            Branch
                        </DataTableHeaderCell>


                        <!-- Payment -->

                        <DataTableHeaderCell
                            width="190px"
                            align="right"
                        >
                            Payment
                        </DataTableHeaderCell>


                        <!-- Payment Method -->

                        <DataTableHeaderCell
                            width="180px"
                        >
                            Payment Method
                        </DataTableHeaderCell>


                        <!-- Status -->

                        <DataTableHeaderCell
                            width="120px"
                            align="center"
                        >
                            Status
                        </DataTableHeaderCell>


                        <!-- Actions -->

                        <DataTableHeaderCell
                            width="90px"
                            align="center"
                        >
                            Actions
                        </DataTableHeaderCell>

                    </DataTableHead>


                    <DataTableBody>

                        <DataTableRow
                            v-for="
                                item in
                                (
                                    receivables?.data
                                    ?? []
                                )
                            "
                            :key="item.id"
                        >

                            <!-- Receivable -->

                            <DataTableCell>

                                <div
                                    class="
                                        font-medium
                                        text-gray-900
                                    "
                                >
                                    {{
                                        item.number
                                        ?? '-'
                                    }}
                                </div>


                                <div
                                    class="
                                        mt-0.5
                                        text-xs
                                        text-gray-500
                                    "
                                >
                                    {{
                                        item.payment_date
                                            ? formatDate(
                                                item.payment_date
                                            )
                                            : '-'
                                    }}
                                </div>

                            </DataTableCell>


                            <!-- Reseller -->

                            <DataTableCell>

                                <div
                                    class="
                                        font-medium
                                        text-gray-900
                                    "
                                >
                                    {{
                                        item.reseller?.name
                                        ?? '-'
                                    }}
                                </div>


                                <div
                                    class="
                                        mt-0.5
                                        text-xs
                                        text-gray-500
                                    "
                                >
                                    {{
                                        item.reseller?.reseller_code
                                        ?? '-'
                                    }}
                                </div>

                            </DataTableCell>


                            <!-- Branch -->

                            <DataTableCell>

                                <div
                                    class="
                                        font-medium
                                        text-gray-900
                                    "
                                >
                                    {{
                                        item.branch?.name
                                        ?? '-'
                                    }}
                                </div>

                            </DataTableCell>


                            <!-- Payment -->

                            <DataTableCell
                                align="right"
                            >

                                <div
                                    class="
                                        font-medium
                                        tabular-nums
                                        text-gray-900
                                    "
                                >
                                    Rp
                                    {{
                                        formatAmount(
                                            item.total_amount
                                        )
                                    }}
                                </div>


                                <div
                                    class="
                                        mt-0.5
                                        text-xs
                                        tabular-nums
                                        text-gray-500
                                    "
                                >
                                    {{
                                        item.total_items
                                        ?? 0
                                    }}
                                    Settlements
                                </div>

                            </DataTableCell>


                            <!-- Payment Method -->

                            <DataTableCell>

                                <span
                                    class="
                                        text-sm
                                        text-gray-700
                                    "
                                >
                                    {{
                                        item.payment_method
                                        ?? '-'
                                    }}
                                </span>

                            </DataTableCell>


                            <!-- Status -->

                            <DataTableCell
                                align="center"
                            >

                                <StatusBadge
                                    :status="
                                        item.status
                                    "
                                />

                            </DataTableCell>


                            <!-- Actions -->

                            <DataTableCell
                                align="center"
                            >

                                <ActionDropdown

                                    @view="
                                        openView(item)
                                    "

                                    :showEdit="
                                        false
                                    "

                                    :showDuplicate="
                                        false
                                    "

                                    :showSubmit="
                                        false
                                    "

                                    :showApprove="
                                        false
                                    "

                                    :showReject="
                                        false
                                    "

                                    :showPost="
                                        false
                                    "

                                    :showCancel="
                                        false
                                    "

                                    :showExport="
                                        false
                                    "

                                    :showPrint="
                                        false
                                    "

                                    :showPdf="
                                        false
                                    "

                                    :showExcel="
                                        false
                                    "

                                    :showHistory="
                                        false
                                    "

                                    :showDelete="
                                        false
                                    "

                                />

                            </DataTableCell>

                        </DataTableRow>

                    </DataTableBody>

                </DataTable>

            </div>


            <!-- Empty -->

            <div
                v-else
                class="
                    px-4
                    py-10
                    text-center
                "
            >

                <div
                    class="
                        text-sm
                        font-medium
                        text-gray-700
                    "
                >
                    No Consignment Receivable
                </div>


                <div
                    class="
                        mt-1
                        text-sm
                        text-gray-500
                    "
                >
                    No consignment receivable transactions found.
                </div>

            </div>

        </div>


        <!-- ========================================================= -->
        <!-- Pagination -->
        <!-- ========================================================= -->

        <div>

            <TablePagination
                :data="receivables"
                label="Consignment Receivable"
            />

        </div>

    </div>

</AppLayout>

</template>


<style scoped>

.page-enter-active,
.page-leave-active {

    transition:
        opacity 0.2s ease,
        transform 0.2s ease;

}


.page-enter-from {

    opacity: 0;

    transform:
        translateY(8px);

}


.page-leave-to {

    opacity: 0;

    transform:
        translateY(-8px);

}


@media print {

    @page {

        size:
            A4 landscape;

        margin:
            12mm;

    }


    body {

        background:
            white !important;

    }


    .print-table {

        width:
            100% !important;

    }


    .print-table tr {

        break-inside:
            avoid;

        page-break-inside:
            avoid;

    }

}

</style>