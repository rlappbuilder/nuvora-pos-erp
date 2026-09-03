<script setup>
import { ref,reactive,computed, watch, onMounted,onUnmounted, toRefs,} from 'vue'
import { router,} from '@inertiajs/vue3'

import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/Layout/PageHeader.vue'
import Card from '@/Components/Layout/Card.vue'
import StatsCard from '@/Components/Card/StatsCard.vue'
import BaseButton from '@/Components/Button/BaseButton.vue'
import DataTable from '@/Components/Table/DataTable.vue'
import DataTableHead from '@/Components/Table/DataTableHead.vue'
import DataTableBody from '@/Components/Table/DataTableBody.vue'
import DataTableHeaderCell from '@/Components/Table/DataTableHeaderCell.vue'
import DataTableRow from '@/Components/Table/DataTableRow.vue'
import DataTableCell from '@/Components/Table/DataTableCell.vue'
import TableEmpty from '@/Components/Table/TableEmpty.vue'
import SearchableSelect from '@/Components/Form/SearchableSelect.vue'
import {LoadingOverlay,} from '@/Components/Feedback'
import { currency,} from '@/Utils'
import FlatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'

/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    trialBalance: {
        type: Array,
        default: () => [],
    },

    statistics: {
        type: Object,
        default: () => ({
            total_accounts: 0,
            total_debit: 0,
            total_credit: 0,
        }),
    },

    branches: {
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

    filters: {
        type: Object,
        default: () => ({}),
    },

})


const {
    trialBalance,
    statistics,
} = toRefs(props)


/*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/

const pageTitle = computed(
    () => 'Trial Balance'
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

    branch_id:
        props.filters?.branch_id ?? '',

    fiscal_year_id:
        props.filters?.fiscal_year_id ?? '',

    accounting_period_id:
        props.filters?.accounting_period_id ?? '',

    as_of_date:
        props.filters?.as_of_date ?? '',

})


/*
|--------------------------------------------------------------------------
| Load Data
|--------------------------------------------------------------------------
*/

function loadData()
{

    router.get(

        route(
            'trial-balance.index'
        ),

        filters,

        {

            preserveState: true,

            preserveScroll: true,

            replace: true,

        }

    )

}
function handleAsOfDateChange()
{
    loadData()
}

/*
|--------------------------------------------------------------------------
| Filtered Accounting Periods
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
| Refresh
|--------------------------------------------------------------------------
*/

function refresh()
{

    Object.assign(

        filters,

        {

            branch_id: '',
            fiscal_year_id: '',
            accounting_period_id: '',
            as_of_date: '',

        }

    )

    loadData()

}


/*
|--------------------------------------------------------------------------
| Fiscal Year Change
|--------------------------------------------------------------------------
*/

function handleFiscalYearChange()
{

    if (
        filters.accounting_period_id
        &&
        !filteredAccountingPeriods.value.some(

            period =>
                Number(period.id) ===
                Number(
                    filters.accounting_period_id
                )

        )
    ) {

        filters.accounting_period_id = ''

    }

}
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
    () => filters.fiscal_year_id,
    () => {
        handleFiscalYearChange()
        loadData()
    }
)

watch(
    () => filters.accounting_period_id,
    () => {
        loadData()
    }
)

watch(
    () => filters.as_of_date,
    () => {
        loadData()
    }
)
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

</script>


<template>

<AppLayout>

    <div class="space-y-6">


        <!-- ===================================================== -->
        <!-- Page Header -->
        <!-- ===================================================== -->

        <PageHeader
            icon="⚖️"
            :title="pageTitle"
            subtitle="Trial balance summary of all posting accounts."
        />


        <!-- ===================================================== -->
        <!-- Statistics -->
        <!-- ===================================================== -->

        <div
            class="
                grid
                grid-cols-1
                gap-4
                md:grid-cols-3
            "
        >

            <StatsCard
                title="Total Accounts"
                :value="
                    statistics?.total_accounts
                    ?? 0
                "
                icon="📒"
            />

            <StatsCard
                title="Total Debit"
                :value="
                    statistics?.total_debit
                    ?? 0
                "
                icon="↘️"
                format="currency"
            />

            <StatsCard
                title="Total Credit"
                :value="
                    statistics?.total_credit
                    ?? 0
                "
                icon="↗️"
                format="currency"
            />

        </div>


        <!-- ===================================================== -->
        <!-- List Card -->
        <!-- ===================================================== -->

        <Card>


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


                    <!-- As Of Date -->

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

                     <FlatPickr
                        v-model="
                            filters.as_of_date
                        "
                        :config="{
                            dateFormat: 'Y-m-d',
                            allowInput: true,
                        }"
                        placeholder="As Of Date"
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
                            handleAsOfDateChange
                        "
                    />

                    </div>


                    <!-- Actions -->

                    <div
                        class="
                            flex
                            w-full
                            lg:w-auto
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

                    </div>

                </div>


                <!-- ================================================= -->
                <!-- FILTER ROW -->
                <!-- ================================================= -->

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
                        @update:model-value="
                            handleFiscalYearChange
                        "
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

                </div>

            </div>


            <!-- ================================================= -->
            <!-- Table -->
            <!-- ================================================= -->

            <div class="mt-6">


                <LoadingOverlay
                    :show="loading"
                    text="Loading Trial Balance..."
                />


                <DataTable
                    v-if="
                        trialBalance?.length
                    "
                    sticky-header
                    max-height="650px"
                >

                    <DataTableHead sticky>


                        <!-- Account -->

                        <DataTableHeaderCell
                            width="250px"
                        >
                            Account
                        </DataTableHeaderCell>


                        <!-- Group -->

                        <DataTableHeaderCell
                            width="180px"
                        >
                            Group
                        </DataTableHeaderCell>


                        <!-- Type -->

                        <DataTableHeaderCell
                            width="180px"
                        >
                            Type
                        </DataTableHeaderCell>


                        <!-- Category -->

                        <DataTableHeaderCell
                            width="200px"
                        >
                            Category
                        </DataTableHeaderCell>


                        <!-- Debit -->

                        <DataTableHeaderCell
                            width="180px"
                            align="right"
                        >
                            Debit
                        </DataTableHeaderCell>


                        <!-- Credit -->

                        <DataTableHeaderCell
                            width="180px"
                            align="right"
                        >
                            Credit
                        </DataTableHeaderCell>

                    </DataTableHead>


                    <DataTableBody>

                        <DataTableRow
                            v-for="
                                item in trialBalance
                            "
                            :key="
                                item.id
                            "
                        >


                            <!-- Account -->

                            <DataTableCell>

                                <div
                                    class="
                                        font-semibold
                                        text-gray-900
                                    "
                                >
                                    {{
                                        item.code
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
                                        item.name
                                    }}
                                </div>

                            </DataTableCell>


                            <!-- Group -->

                            <DataTableCell>

                                <span
                                    class="
                                        text-sm
                                        text-gray-700
                                    "
                                >
                                    {{
                                        item.account_group
                                        ?? '-'
                                    }}
                                </span>

                            </DataTableCell>


                            <!-- Type -->

                            <DataTableCell>

                                <span
                                    class="
                                        text-sm
                                        text-gray-700
                                    "
                                >
                                    {{
                                        item.account_type
                                        ?? '-'
                                    }}
                                </span>

                            </DataTableCell>


                            <!-- Category -->

                            <DataTableCell>

                                <span
                                    class="
                                        text-sm
                                        text-gray-700
                                    "
                                >
                                    {{
                                        item.account_category
                                        ?? '-'
                                    }}
                                </span>

                            </DataTableCell>


                            <!-- Debit -->

                            <DataTableCell
                                align="right"
                            >

                                <span
                                    class="
                                        whitespace-nowrap
                                        text-sm
                                        font-medium
                                        text-gray-900
                                    "
                                >
                                    {{
                                        currency(
                                            item.ending_debit
                                            ?? 0
                                        )
                                    }}
                                </span>

                            </DataTableCell>


                            <!-- Credit -->

                            <DataTableCell
                                align="right"
                            >

                                <span
                                    class="
                                        whitespace-nowrap
                                        text-sm
                                        font-medium
                                        text-gray-900
                                    "
                                >
                                    {{
                                        currency(
                                            item.ending_credit
                                            ?? 0
                                        )
                                    }}
                                </span>

                            </DataTableCell>

                        </DataTableRow>


                        <!-- ================================================= -->
                        <!-- TOTAL -->
                        <!-- ================================================= -->

                        <DataTableRow>

                            <DataTableCell
                                colspan="4"
                            >

                                <span
                                    class="
                                        text-sm
                                        font-bold
                                        text-gray-900
                                    "
                                >
                                    TOTAL
                                </span>

                            </DataTableCell>


                            <DataTableCell
                                align="right"
                            >

                                <span
                                    class="
                                        whitespace-nowrap
                                        text-sm
                                        font-bold
                                        text-gray-900
                                    "
                                >
                                    {{
                                        currency(
                                            statistics?.total_debit
                                            ?? 0
                                        )
                                    }}
                                </span>

                            </DataTableCell>


                            <DataTableCell
                                align="right"
                            >

                                <span
                                    class="
                                        whitespace-nowrap
                                        text-sm
                                        font-bold
                                        text-gray-900
                                    "
                                >
                                    {{
                                        currency(
                                            statistics?.total_credit
                                            ?? 0
                                        )
                                    }}
                                </span>

                            </DataTableCell>

                        </DataTableRow>

                    </DataTableBody>

                </DataTable>


                <!-- ================================================= -->
                <!-- Empty -->
                <!-- ================================================= -->

                <TableEmpty
                    v-else
                    icon="⚖️"
                    title="No Trial Balance Data Found"
                    description="There are no posting account balances matching the selected filters."
                />

            </div>

        </Card>

    </div>

</AppLayout>

</template>