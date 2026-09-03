<script setup>

import {
    ref,
    reactive,
    computed,
    watch,
    onMounted,
    onUnmounted,
    toRefs,
} from 'vue'

import {
    router,
    useForm,
} from '@inertiajs/vue3'

import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/Layout/PageHeader.vue'
import Card from '@/Components/Layout/Card.vue'
import StatsCard from '@/Components/Card/StatsCard.vue'
import BaseButton from '@/Components/Button/BaseButton.vue'
import ActionDropdown from '@/Components/Action/ActionDropdown.vue'
import BulkActionDropdown from '@/Components/Bulk/BulkActionDropdown.vue'
import DataTable from '@/Components/Table/DataTable.vue'
import DataTableHead from '@/Components/Table/DataTableHead.vue'
import DataTableBody from '@/Components/Table/DataTableBody.vue'
import DataTableHeaderCell from '@/Components/Table/DataTableHeaderCell.vue'
import DataTableRow from '@/Components/Table/DataTableRow.vue'
import DataTableCell from '@/Components/Table/DataTableCell.vue'
import TablePagination from '@/Components/Table/TablePagination.vue'
import TableEmpty from '@/Components/Table/TableEmpty.vue'
import StatusBadge from '@/Components/Display/StatusBadge.vue'
import SearchableSelect from '@/Components/Form/SearchableSelect.vue'

import {
    LoadingOverlay,
} from '@/Components/Feedback'

import {
    PlusIcon,
} from '@heroicons/vue/24/solid'

import {
    success,
    error,
    currency,
    formatDate,
} from '@/Utils'

import FlatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'

import JournalEntryForm from './Partials/JournalEntryForm.vue'
import JournalEntryViewModal from './Partials/JournalEntryViewModal.vue'
import JournalEntryPostModal from './Partials/JournalEntryPostModal.vue'

import ConfirmDeleteModal from '@/Components/Modal/ConfirmDeleteModal.vue'


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/
const props = defineProps({

    journalEntries: {
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
            posted: 0,
            reversed: 0,
        }),
    },

    companyId: {
        type: [Number, String],
        default: null,
    },

    branches: {
        type: Array,
        default: () => [],
    },

    accountingJournals: {
        type: Array,
        default: () => [],
    },

    fiscalYears: {
        type: Array,
        default: () => [],
    },

    accountingPeriods: {
        type: Array,
        default: () => [],
    },

    chartOfAccounts: {
        type: Array,
        default: () => [],
    },

    filters: {
        type: Object,
        default: () => ({}),
    },

    previewNumber: {
        type: String,
        default: '',
    },

})

console.log(
    '=== JOURNAL ENTRY DEBUG ==='
)

console.log(
    'previewNumber:',
    props.previewNumber
)

console.log(
    'accountingJournals:',
    props.accountingJournals
)

console.log(
    'fiscalYears:',
    props.fiscalYears
)

console.log(
    'accountingPeriods:',
    props.accountingPeriods
)

console.log(
    'chartOfAccounts:',
    props.chartOfAccounts
)
const {
    journalEntries,
    statistics,
} = toRefs(props)


/*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/

const pageTitle = computed(
    () => 'Journal Entries'
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
| Filters
|--------------------------------------------------------------------------
*/

const filters = reactive({

    search:
        props.filters?.search ?? '',

    branch_id:
        props.filters?.branch_id ?? '',

    accounting_journal_id:
        props.filters?.accounting_journal_id ?? '',

    fiscal_year_id:
        props.filters?.fiscal_year_id ?? '',

    accounting_period_id:
        props.filters?.accounting_period_id ?? '',

    status:
        props.filters?.status ?? '',

    date_from:
        props.filters?.date_from ?? '',

    date_to:
        props.filters?.date_to ?? '',

    per_page:
        props.filters?.per_page ?? 10,

})


let debounceTimer = null


function loadData()
{

    router.get(

        route(
            'journal-entries.index'
        ),

        filters,

        {

            preserveState: true,

            preserveScroll: true,

            replace: true,

        }

    )

}


/*
|--------------------------------------------------------------------------
| Filter Watchers
|--------------------------------------------------------------------------
*/

watch(

    () => filters.search,

    () => {

        clearTimeout(
            debounceTimer
        )

        debounceTimer =
            setTimeout(() => {

                loadData()

            }, 500)

    }

)


watch(

    () => filters.branch_id,

    () => {

        loadData()

    }

)


watch(

    () =>
        filters.accounting_journal_id,

    () => {

        loadData()

    }

)


watch(

    () =>
        filters.fiscal_year_id,

    () => {

        loadData()

    }

)


watch(

    () =>
        filters.accounting_period_id,

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

    () => filters.date_from,

    () => {

        loadData()

    }

)


watch(

    () => filters.date_to,

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

            accounting_journal_id: '',

            fiscal_year_id: '',

            accounting_period_id: '',

            status: '',

            date_from: '',

            date_to: '',

            per_page: 10,

        }

    )

    dateRange.value = ''

    loadData()

}


/*
|--------------------------------------------------------------------------
| Date Range
|--------------------------------------------------------------------------
*/

const dateRange = ref('')


function formatDateForFilter(date)
{

    const year =
        date.getFullYear()


    const month =
        String(
            date.getMonth() + 1
        ).padStart(
            2,
            '0'
        )


    const day =
        String(
            date.getDate()
        ).padStart(
            2,
            '0'
        )


    return `${year}-${month}-${day}`

}


function handleDateRangeChange(
    selectedDates
)
{

    if (!selectedDates.length) {

        filters.date_from = ''

        filters.date_to = ''

        loadData()

        return

    }


    filters.date_from =
        formatDateForFilter(
            selectedDates[0]
        )


    filters.date_to =
        selectedDates.length > 1

            ? formatDateForFilter(
                selectedDates[1]
            )

            : formatDateForFilter(
                selectedDates[0]
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
        value: 'Posted',
        label: 'Posted',
    },

    {
        value: 'Reversed',
        label: 'Reversed',
    },

]


/*
|--------------------------------------------------------------------------
| Selection
|--------------------------------------------------------------------------
*/

const selectedRows = ref([])

const selectAllRef = ref(null)


const isAllSelected = computed(() => {

    const totalRows =
        journalEntries.value
            ?.data
            ?.length ?? 0


    return (

        totalRows > 0 &&

        selectedRows.value.length ===
            totalRows

    )

})


const isIndeterminate = computed(() => {

    const totalRows =
        journalEntries.value
            ?.data
            ?.length ?? 0


    return (

        selectedRows.value.length > 0 &&

        selectedRows.value.length <
            totalRows

    )

})


watch(

    isIndeterminate,

    (value) => {

        if (
            selectAllRef.value
        ) {

            selectAllRef.value.indeterminate =
                value

        }

    },

    {
        immediate: true,
    }

)


function toggleSelectAll(event)
{

    if (
        event.target.checked
    ) {

        selectedRows.value =
            journalEntries.value
                ?.data
                ?.map(
                    item => item.id
                ) ?? []

    } else {

        selectedRows.value = []

    }

}


/*
|--------------------------------------------------------------------------
| View State
|--------------------------------------------------------------------------
*/

const view = ref('list')

const formMode = ref('create')

const editingItem = ref(null)


/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/

const createEmptyLine = () => ({

    account_id: null,

    description: null,

    debit: 0,

    credit: 0,

})

const today = new Date()
    .toISOString()
    .slice(0, 10)

const form = useForm({

    number:
        props.previewNumber ?? '',

    company_id:
        props.companyId ?? null,

    branch_id:
        null,

    accounting_journal_id:
        null,

    fiscal_year_id:
        null,

    accounting_period_id:
        null,

    entry_date:
        today,

    reference:
        null,

    description:
        null,

    lines: [
        createEmptyLine(),
    ],

})
function resetCreateForm()
{
    form.reset()

    form.clearErrors()

    form.number =
        props.previewNumber ?? ''

    form.company_id =
        props.companyId ?? null

    form.branch_id =
        null

    form.accounting_journal_id =
        null

    form.fiscal_year_id =
        null

    form.accounting_period_id =
        null

    form.entry_date =
        new Date()
            .toISOString()
            .slice(0, 10)

    form.reference =
        null

    form.description =
        null

    form.lines = [
        createEmptyLine(),
    ]
}
function create()
{
    formMode.value = 'create'

    editingItem.value = null

    form.clearErrors()

    /*
    |--------------------------------------------------------------------------
    | Date
    |--------------------------------------------------------------------------
    */

    const entryDate =
        new Date()
            .toISOString()
            .slice(0, 10)


    /*
    |--------------------------------------------------------------------------
    | Fiscal Year
    |--------------------------------------------------------------------------
    */

    const fiscalYear =
        props.fiscalYears.find(
            fiscalYear => {

                const startDate =
                    String(
                        fiscalYear.start_date
                    ).slice(0, 10)

                const endDate =
                    String(
                        fiscalYear.end_date
                    ).slice(0, 10)

                return (
                    entryDate >= startDate &&
                    entryDate <= endDate
                )

            }
        )


    /*
    |--------------------------------------------------------------------------
    | Accounting Period
    |--------------------------------------------------------------------------
    */

    const accountingPeriod =
        props.accountingPeriods.find(
            period => {

                const startDate =
                    String(
                        period.start_date
                    ).slice(0, 10)

                const endDate =
                    String(
                        period.end_date
                    ).slice(0, 10)

                return (
                    Number(
                        period.fiscal_year_id
                    ) ===
                    Number(
                        fiscalYear?.id
                    )
                    &&
                    entryDate >= startDate
                    &&
                    entryDate <= endDate
                )

            }
        )


    /*
    |--------------------------------------------------------------------------
    | Reset Form
    |--------------------------------------------------------------------------
    */

    form.reset()

    form.number =
        props.previewNumber ?? ''

    form.company_id =
        props.companyId ?? null

    form.branch_id =
        null

    form.accounting_journal_id =
        null

    form.fiscal_year_id =
        fiscalYear?.id ?? null

    form.accounting_period_id =
        accountingPeriod?.id ?? null

    form.entry_date =
        entryDate

    form.reference =
        null

    form.description =
        null

    form.lines = [
        createEmptyLine(),
    ]


    /*
    |--------------------------------------------------------------------------
    | DEBUG
    |--------------------------------------------------------------------------
    */

    console.log(
        '=== CREATE JOURNAL ENTRY DEFAULT ==='
    )

    console.log(
        'entryDate:',
        entryDate
    )

    console.log(
        'fiscalYear:',
        fiscalYear
    )

    console.log(
        'accountingPeriod:',
        accountingPeriod
    )

    console.log(
        'form fiscal_year_id:',
        form.fiscal_year_id
    )

    console.log(
        'form accounting_period_id:',
        form.accounting_period_id
    )


    /*
    |--------------------------------------------------------------------------
    | Show Form
    |--------------------------------------------------------------------------
    */

    view.value = 'form'
}
/*
|--------------------------------------------------------------------------
| Cancel Form
|--------------------------------------------------------------------------
*/

function cancelForm()
{

    form.reset()

    form.clearErrors()

    editingItem.value =
        null

    view.value =
        'list'

}


/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/

function submit()
{

    if (
        formMode.value ===
        'create'
    ) {

        form.post(

            route(
                'journal-entries.store'
            ),

            {

                preserveScroll: true,

                onSuccess: () => {

                    success(

                        'Success',

                        'Journal Entry created successfully.'

                    )

                    view.value =
                        'list'

                },

                onError: (errors) => {

                    console.error(

                        'CREATE JOURNAL ENTRY ERRORS:',

                        errors

                    )

                    error(

                        'Failed to create Journal Entry.'

                    )

                },

            }

        )

        return

    }


    form.put(

        route(

            'journal-entries.update',

            editingItem.value.id

        ),

        {

            preserveScroll: true,

            onSuccess: () => {

                success(

                    'Success',

                    'Journal Entry updated successfully.'

                )

                view.value =
                    'list'

            },

            onError: (errors) => {

                console.error(

                    'UPDATE JOURNAL ENTRY ERRORS:',

                    errors

                )

                error(

                    'Failed to update Journal Entry.'

                )

            },

        }

    )

}
function submitAndNew()
{
    form.post(
        route('journal-entries.store'),
        {
            preserveScroll: true,

            onSuccess: () => {

                success(
                    'Success',
                    'Journal entry created successfully.'
                )

                resetCreateForm()

            },
        }
    )
}
/*
|--------------------------------------------------------------------------
| Edit
|--------------------------------------------------------------------------
*/

function editJournalEntry(
    item
)
{

    /*
    |--------------------------------------------------------------------------
    | Validate Status
    |--------------------------------------------------------------------------
    */

    if (
        item.status !== 'Draft'
    ) {

        error(

            'Only Draft Journal Entry can be edited.'

        )

        return

    }


    /*
    |--------------------------------------------------------------------------
    | Edit State
    |--------------------------------------------------------------------------
    */

    editingItem.value =
        item


    formMode.value =
        'edit'


    form.clearErrors()


    /*
    |--------------------------------------------------------------------------
    | Header
    |--------------------------------------------------------------------------
    */

    form.code =
        item.code


    form.branch_id =
        item.branch_id


    form.accounting_journal_id =
        item.accounting_journal_id


    form.fiscal_year_id =
        item.fiscal_year_id


    form.accounting_period_id =
        item.accounting_period_id


    form.entry_date =
        item.entry_date

            ? String(
                item.entry_date
            ).slice(
                0,
                10
            )

            : null


    form.reference =
        item.reference ?? null


    form.description =
        item.description ?? null


    /*
    |--------------------------------------------------------------------------
    | Lines
    |--------------------------------------------------------------------------
    */

    form.lines =
        item.lines?.map(
            line => ({

                account_id:
                    line.account_id,

                description:
                    line.description ?? null,

                debit:
                    Number(
                        line.debit ?? 0
                    ),

                credit:
                    Number(
                        line.credit ?? 0
                    ),

            })
        ) ?? [

            createEmptyLine(),

        ]


    view.value =
        'form'

}


/*
|--------------------------------------------------------------------------
| View
|--------------------------------------------------------------------------
*/

const viewLoading = ref(false)

const showView = ref(false)

const viewItem = ref(null)


async function openView(item)
{

    viewLoading.value =
        true


    viewItem.value =
        null


    showView.value =
        true


    try {

        const response =
            await fetch(

                route(

                    'journal-entries.data',

                    item.id

                ),

                {

                    headers: {

                        Accept:
                            'application/json',

                        'X-Requested-With':
                            'XMLHttpRequest',

                    },

                }

            )


        if (
            !response.ok
        ) {

            throw new Error(

                `HTTP ${response.status}`

            )

        }


        const responseData =
            await response.json()


        console.log(

            'JOURNAL ENTRY VIEW RESPONSE:',

            responseData

        )


        viewItem.value =
            responseData.data


    } catch (
        exception
    ) {

        console.error(

            'JOURNAL ENTRY VIEW ERROR:',

            exception

        )


        showView.value =
            false


        viewItem.value =
            null


        error(

            'Failed to load Journal Entry detail.'

        )

    } finally {

        viewLoading.value =
            false

    }

}


function closeView()
{

    viewItem.value =
        null

    showView.value =
        false

}


/*
|--------------------------------------------------------------------------
| Delete
|--------------------------------------------------------------------------
*/

const deleteItem = ref(null)

const showDelete = ref(false)


function openDelete(item)
{

    if (
        item.status !==
        'Draft'
    ) {

        error(

            'Only Draft Journal Entry can be deleted.'

        )

        return

    }


    deleteItem.value =
        item


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


const deleteMessage = computed(() => {

    if (
        !deleteItem.value
    ) {

        return ''

    }


    return `Are you sure you want to delete "${deleteItem.value.code}"?`

})


function confirmDelete()
{

    if (
        !deleteItem.value
    ) {

        return

    }


    router.delete(

        route(

            'journal-entries.destroy',

            deleteItem.value.id

        ),

        {

            preserveScroll: true,

            onSuccess: () => {

                closeDelete()


                success(

                    'Success',

                    'Journal Entry deleted successfully.'

                )

            },

            onError: () => {

                error(

                    'Failed to delete Journal Entry.'

                )

            },

        }

    )

}


/*
|--------------------------------------------------------------------------
| Bulk Delete
|--------------------------------------------------------------------------
*/

const showBulkDelete =
    ref(false)


function openBulkDelete()
{

    if (
        !selectedRows.value.length
    ) {

        return

    }


    showBulkDelete.value =
        true

}


const bulkDeleteMessage =
    computed(() => {

        const total =
            selectedRows.value.length


        if (!total) {

            return ''

        }


        return `Are you sure you want to delete ${total} selected Journal Entry document(s)?`

    })


function bulkDelete()
{

    router.delete(

        route(
            'journal-entries.bulk-delete'
        ),

        {

            data: {

                ids:
                    selectedRows.value,

            },

            preserveScroll: true,

            onSuccess: () => {

                showBulkDelete.value =
                    false


                selectedRows.value =
                    []


                success(

                    'Success',

                    'Journal Entry deleted successfully.'

                )

            },

        }

    )

}


/*
|--------------------------------------------------------------------------
| Post
|--------------------------------------------------------------------------
*/

const postItem = ref(null)

const showPost = ref(false)
const postLoading = ref(false)

async function openPost(item)
{

    if (
        item.status !==
        'Draft'
    ) {

        error(

            'Only Draft Journal Entry can be posted.'

        )

        return

    }


    postLoading.value =
        true


    try {

        const response =
            await axios.get(

                route(

                    'journal-entries.data',

                    item.id

                )

            )


        postItem.value =
            response.data.data


        showPost.value =
            true

    }

    catch (e) {

        error(

            'Failed to load Journal Entry details.'

        )

    }

    finally {

        postLoading.value =
            false

    }

}

function closePost()
{

    postItem.value =
        null

    showPost.value =
        false

}


function confirmPost()
{

    if (
        !postItem.value
    ) {

        return

    }


    router.post(

        route(

            'journal-entries.post',

            postItem.value.id

        ),

        {},

        {

            preserveScroll: true,

            onSuccess: () => {

                closePost()


                success(

                    'Success',

                    'Journal Entry posted successfully.'

                )

            },

            onError: () => {

                error(

                    'Failed to post Journal Entry.'

                )

            },

        }

    )

}


const postMessage =
    computed(() => {

        if (
            !postItem.value
        ) {

            return ''

        }


        return `Are you sure you want to post "${postItem.value.code}"? Once posted, this Journal Entry cannot be edited.`

    })


/*
|--------------------------------------------------------------------------
| Selection Helpers
|--------------------------------------------------------------------------
*/

function canEdit(item)
{

    return item.status ===
        'Draft'

}


function canPost(item)
{

    return item.status ===
        'Draft'

}


function canDelete(item)
{

    return item.status ===
        'Draft'

}


/*
|--------------------------------------------------------------------------
| Sorting
|--------------------------------------------------------------------------
*/

const sort = ref(

    props.filters?.sort_by ??
        'id'

)


const direction = ref(

    props.filters?.sort_direction ??
        'desc'

)


function sortBy(column)
{

    if (
        sort.value ===
        column
    ) {

        direction.value =

            direction.value ===
            'asc'

                ? 'desc'

                : 'asc'

    } else {

        sort.value =
            column

        direction.value =
            'asc'

    }


    router.get(

        route(
            'journal-entries.index'
        ),

        {

            search:
                filters.search,

            branch_id:
                filters.branch_id,

            accounting_journal_id:
                filters.accounting_journal_id,

            fiscal_year_id:
                filters.fiscal_year_id,

            accounting_period_id:
                filters.accounting_period_id,

            status:
                filters.status,

            date_from:
                filters.date_from,

            date_to:
                filters.date_to,

            per_page:
                filters.per_page,

            sort_by:
                sort.value,

            sort_direction:
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
| Filtered Options
|--------------------------------------------------------------------------
*/

const filteredAccountingPeriods =
    computed(() => {

        if (
            !filters.fiscal_year_id
        ) {

            return props.accountingPeriods

        }


        return props.accountingPeriods
            .filter(

                period =>
                    Number(
                        period.fiscal_year_id
                    ) ===
                    Number(
                        filters.fiscal_year_id
                    )

            )

    })


/*
|--------------------------------------------------------------------------
| Lifecycle
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

watch(
    () => form.branch_id,
    (branchId) => {

        if (!branchId) {

            form.company_id = null

            form.accounting_journal_id = null

            form.fiscal_year_id = null

            form.accounting_period_id = null

            return
        }

        /*
        |--------------------------------------------------------------------------
        | Resolve Company
        |--------------------------------------------------------------------------
        */

        const branch =
            props.branches.find(
                item =>
                    Number(item.id) ===
                    Number(branchId)
            )

        form.company_id =
            branch?.company_id ?? null

        /*
        |--------------------------------------------------------------------------
        | Fiscal Year
        |--------------------------------------------------------------------------
        */

        const entryDate =
            String(
                form.entry_date ?? ''
            ).slice(0, 10)

        const fiscalYear =
            props.fiscalYears.find(
                year => {

                    const start =
                        String(
                            year.start_date
                        ).slice(0, 10)

                    const end =
                        String(
                            year.end_date
                        ).slice(0, 10)

                    return (
                        entryDate >= start &&
                        entryDate <= end
                    )

                }
            )

        form.fiscal_year_id =
            fiscalYear?.id ?? null

        /*
        |--------------------------------------------------------------------------
        | Accounting Period
        |--------------------------------------------------------------------------
        */

        const period =
            props.accountingPeriods.find(
                item => {

                    const start =
                        String(
                            item.start_date
                        ).slice(0, 10)

                    const end =
                        String(
                            item.end_date
                        ).slice(0, 10)

                    return (
                        Number(
                            item.fiscal_year_id
                        ) ===
                        Number(
                            form.fiscal_year_id
                        )
                        &&
                        entryDate >= start
                        &&
                        entryDate <= end
                    )

                }
            )

        form.accounting_period_id =
            period?.id ?? null

    }
)
</script>


<template>

<AppLayout>

    <Transition
        name="page"
        mode="out-in"
    >


        <!-- ===================================================== -->
        <!-- LIST -->
        <!-- ===================================================== -->

        <div
            v-if="view === 'list'"
            key="list"
        >

            <div class="space-y-6">


                <!-- ================================================= -->
                <!-- Statistics -->
                <!-- ================================================= -->

                <div
                    class="
                        grid
                        grid-cols-1
                        gap-4
                        md:grid-cols-4
                    "
                >

                    <StatsCard
                        title="Total Journal Entries"
                        :value="
                            statistics?.total ?? 0
                        "
                        icon="📒"
                    />


                    <StatsCard
                        title="Draft"
                        :value="
                            statistics?.draft ?? 0
                        "
                        icon="📝"
                    />


                    <StatsCard
                        title="Posted"
                        :value="
                            statistics?.posted ?? 0
                        "
                        icon="✅"
                    />


                    <StatsCard
                        title="Reversed"
                        :value="
                            statistics?.reversed ?? 0
                        "
                        icon="↩️"
                    />

                </div>

            </div>


            <!-- ================================================= -->
            <!-- List Card -->
            <!-- ================================================= -->

            <Card class="mt-4">


                <!-- ================================================= -->
                <!-- Toolbar -->
                <!-- ================================================= -->

                <div>


                    <!-- TOP ROW -->

                    <div
                        class="
                            flex
                            flex-col
                            gap-3
                            lg:flex-row
                            lg:items-center
                            lg:justify-between
                        "
                    >


                        <!-- Search -->

                        <div
                            class="
                                flex
                                flex-1
                                flex-col
                                gap-3
                                lg:flex-row
                                lg:items-center
                            "
                        >

                            <input
                                v-model="
                                    filters.search
                                "
                                type="text"
                                placeholder="Search code or reference..."
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


                            <!-- Date Range -->

                            <FlatPickr
                                v-model="
                                    dateRange
                                "
                                :config="{
                                    mode: 'range',
                                    dateFormat: 'Y-m-d',
                                }"
                                placeholder="Date Range"
                                class="
                                    w-full
                                    rounded-xl
                                    border
                                    border-gray-300
                                    px-4
                                    py-2.5
                                    text-sm
                                    lg:w-56
                                "
                                @on-change="
                                    handleDateRangeChange
                                "
                            />

                        </div>


                        <!-- Actions -->

                        <div
                            class="
                                flex
                                w-full
                                flex-col
                                gap-2
                                lg:w-auto
                                lg:flex-row
                                lg:items-center
                            "
                        >

                            <BaseButton
                                variant="secondary"
                                class="
                                    w-full
                                    shrink-0
                                    whitespace-nowrap
                                    lg:w-auto
                                "
                                @click="refresh"
                            >
                                Refresh
                            </BaseButton>


                            <BaseButton
                                class="
                                    w-full
                                    shrink-0
                                    whitespace-nowrap
                                    lg:w-auto
                                "
                                @click="create"
                            >

                                <template #icon>

                                    <PlusIcon
                                        class="h-5 w-5"
                                    />

                                </template>

                                Add

                            </BaseButton>


                            <BulkActionDropdown
                                :count="
                                    selectedRows.length
                                "
                                :disabled="
                                    selectedRows.length === 0
                                "
                                :actions="[
                                    'export',
                                ]"
                                class="w-full lg:w-auto"
                            />

                        </div>

                    </div>


                    <!-- BOTTOM ROW -->

                    <div
                        class="
                            mt-3
                            flex
                            flex-col
                            gap-3
                            lg:flex-row
                            lg:items-center
                        "
                    >


                        <!-- Branch -->

                        <SearchableSelect
                            v-model="
                                filters.branch_id
                            "
                            :options="
                                branches
                            "
                            label="label"
                            value-key="id"
                            placeholder="All Branches"
                            class="w-full lg:w-48"
                        />


                        <!-- Accounting Journal -->

                        <SearchableSelect
                            v-model="
                                filters.accounting_journal_id
                            "
                            :options="
                                accountingJournals
                            "
                            label="label"
                            value-key="id"
                            placeholder="All Journals"
                            class="w-full lg:w-52"
                        />


                        <!-- Fiscal Year -->

                        <SearchableSelect
                            v-model="
                                filters.fiscal_year_id
                            "
                            :options="
                                fiscalYears
                            "
                            label="label"
                            value-key="id"
                            placeholder="All Fiscal Years"
                            class="w-full lg:w-48"
                        />


                        <!-- Period -->

                        <SearchableSelect
                            v-model="
                                filters.accounting_period_id
                            "
                            :options="
                                filteredAccountingPeriods
                            "
                            label="label"
                            value-key="id"
                            placeholder="All Periods"
                            class="w-full lg:w-48"
                        />


                        <!-- Status -->

                        <SearchableSelect
                            v-model="
                                filters.status
                            "
                            :options="
                                statusOptions
                            "
                            label="label"
                            value-key="value"
                            placeholder="All Status"
                            class="w-full lg:w-40"
                        />

                    </div>

                </div>


                <!-- ================================================= -->
                <!-- Table -->
                <!-- ================================================= -->

                <div class="mt-6">


                    <LoadingOverlay
                        :show="loading"
                        text="Loading Journal Entries..."
                    />


                    <DataTable
                        v-if="
                            journalEntries?.data?.length
                        "
                        sticky-header
                        max-height="650px"
                    >

                        <DataTableHead sticky>


                            <!-- Select -->

                            <DataTableHeaderCell
                                width="60px"
                                align="center"
                            >

                                <input
                                    ref="selectAllRef"
                                    type="checkbox"
                                    :checked="
                                        isAllSelected
                                    "
                                    @change="
                                        toggleSelectAll
                                    "
                                    class="rounded border-gray-300"
                                />

                            </DataTableHeaderCell>


                            <!-- Code -->

                            <DataTableHeaderCell
                                sortable
                                column="code"
                                :sort="sort"
                                :direction="direction"
                                @sort="sortBy"
                                width="190px"
                            >
                                Code
                            </DataTableHeaderCell>


                            <!-- Date -->

                            <DataTableHeaderCell
                                sortable
                                column="entry_date"
                                :sort="sort"
                                :direction="direction"
                                @sort="sortBy"
                                width="150px"
                            >
                                Entry Date
                            </DataTableHeaderCell>


                            <!-- Journal -->

                            <DataTableHeaderCell
                                width="180px"
                            >
                                Journal
                            </DataTableHeaderCell>


                            <!-- Branch -->

                            <DataTableHeaderCell
                                width="180px"
                            >
                                Branch
                            </DataTableHeaderCell>


                            <!-- Reference -->

                            <DataTableHeaderCell
                                width="180px"
                            >
                                Reference
                            </DataTableHeaderCell>


                            <!-- Description -->

                            <DataTableHeaderCell
                                width="250px"
                            >
                                Description
                            </DataTableHeaderCell>


                            <!-- Total -->

                            <DataTableHeaderCell
                                width="180px"
                                align="right"
                            >
                                Amount
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
                                    item in journalEntries.data
                                "
                                :key="item.id"
                            >


                                <!-- Checkbox -->

                                <DataTableCell
                                    align="center"
                                >

                                    <input
                                        v-model="
                                            selectedRows
                                        "
                                        :value="
                                            item.id
                                        "
                                        type="checkbox"
                                        class="rounded border-gray-300"
                                    />

                                </DataTableCell>


                                <!-- Code -->

                                <DataTableCell>

                                    <span
                                        class="
                                            font-medium
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            item.code
                                        }}
                                    </span>

                                </DataTableCell>


                                <!-- Date -->

                                <DataTableCell>

                                    <span
                                        class="
                                            text-sm
                                            text-gray-700
                                        "
                                    >
                                        {{
                                            formatDate(
                                                item.entry_date
                                            )
                                        }}
                                    </span>

                                </DataTableCell>


                                <!-- Journal -->

                                <DataTableCell>

                                    <div
                                        class="
                                            font-semibold
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            item.accounting_journal?.name
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
                                            item.accounting_journal?.code
                                            ?? '-'
                                        }}
                                    </div>

                                </DataTableCell>


                                <!-- Branch -->

                                <DataTableCell>

                                    {{
                                        item.branch?.name
                                        ?? '-'
                                    }}

                                </DataTableCell>


                                <!-- Reference -->

                                <DataTableCell>

                                    {{
                                        item.reference
                                        ?? '-'
                                    }}

                                </DataTableCell>


                                <!-- Description -->

                                <DataTableCell>

                                    <span
                                        class="
                                            text-sm
                                            text-gray-700
                                        "
                                    >
                                        {{
                                            item.description
                                            ?? '-'
                                        }}
                                    </span>

                                </DataTableCell>


                                <!-- Amount -->

                                <DataTableCell
                                    align="right"
                                >

                                    <span
                                        class="
                                            text-sm
                                            font-medium
                                            text-gray-900
                                        "
                                    >

                                        {{
                                            currency(
                                                item.total_debit
                                                ?? 0
                                            )
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

                                        @edit="
                                            editJournalEntry(item)
                                        "

                                        @post="
                                            openPost(item)
                                        "

                                        @delete="
                                            openDelete(item)
                                        "

                                        :showEdit="
                                            canEdit(item)
                                        "

                                        :showDuplicate="false"

                                        :showPost="
                                            canPost(item)
                                        "

                                        :showReject="false"

                                        :showExport="false"

                                        :showHistory="false"

                                        :showDelete="
                                            canDelete(item)
                                        "

                                    />

                                </DataTableCell>

                            </DataTableRow>

                        </DataTableBody>

                    </DataTable>


                    <!-- Empty -->

                    <TableEmpty
                        v-else
                        icon="📒"
                        title="No Journal Entries Found"
                        description="There are no Journal Entry transactions available."
                    >

                        <template #action>

                            <BaseButton
                                @click="create"
                            >
                                Create Journal Entry
                            </BaseButton>

                        </template>

                    </TableEmpty>

                </div>


                <!-- ================================================= -->
                <!-- Pagination -->
                <!-- ================================================= -->

                <div class="mt-6">

                    <TablePagination
                        :data="
                            journalEntries
                        "
                        label="Journal Entry"
                    />

                </div>

            </Card>

        </div>


        <!-- ===================================================== -->
        <!-- FORM -->
        <!-- ===================================================== -->

        <div
            v-else-if="
                view === 'form'
            "
            key="form"
            class="space-y-6"
        >

            <PageHeader
                icon="📒"
                :title="
                    formMode === 'create'
                        ? 'Create Journal Entry'
                        : 'Edit Journal Entry'
                "
                :subtitle="
                    formMode === 'create'
                        ? 'Create a new Journal Entry transaction.'
                        : 'Update Journal Entry transaction.'
                "
            />


            <Card>

                <JournalEntryForm

                    :form="form"

                    :branches="
                        branches
                    "

                    :accounting-journals="
                        accountingJournals
                    "

                    :fiscal-years="
                        fiscalYears
                    "

                    :accounting-periods="
                        accountingPeriods
                    "

                    :chart-of-accounts="
                        chartOfAccounts
                    "

                    :mode="
                        formMode
                    "

                    @submit="
                        submit
                    "

                    @submit-and-new="
                        submitAndNew
                    "

                    @cancel="
                        cancelForm
                    "

                />

            </Card>

        </div>

    </Transition>

</AppLayout>


<!-- ========================================================= -->
<!-- Delete -->
<!-- ========================================================= -->

<ConfirmDeleteModal

    :show="
        showDelete
    "

    title="Delete Journal Entry"

    :message="
        deleteMessage
    "

    confirm-text="Delete"

    @close="
        closeDelete
    "

    @confirm="
        confirmDelete
    "

/>


<!-- ========================================================= -->
<!-- View -->
<!-- ========================================================= -->

<JournalEntryViewModal

    :show="
        showView
    "

    :journal-entry="
        viewItem
    "

    :loading="
        viewLoading
    "

    @close="
        closeView
    "

/>


<!-- ========================================================= -->
<!-- Post -->
<!-- ========================================================= -->
<JournalEntryPostModal
    :show="showPost"
    :journal-entry="postItem"
    :loading="postLoading"
    @close="closePost"
    @confirm="confirmPost"
/>

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

</style>