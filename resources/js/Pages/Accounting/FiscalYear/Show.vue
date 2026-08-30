<script setup>

import AppLayout
    from '@/Layouts/AppLayout.vue'

import {
    Head,
    router,
} from '@inertiajs/vue3'

import PageHeader
    from '@/Components/Layout/PageHeader.vue'

import Card
    from '@/Components/Layout/Card.vue'

import ActionBar
    from '@/Components/Layout/ActionBar.vue'

import ButtonGroup
    from '@/Components/Button/ButtonGroup.vue'

import BaseButton
    from '@/Components/Button/BaseButton.vue'

import DetailRow
    from '@/Components/Display/DetailRow.vue'

import StatusBadge
    from '@/Components/Display/StatusBadge.vue'

import DataTable
    from '@/Components/Table/DataTable.vue'

import DataTableHead
    from '@/Components/Table/DataTableHead.vue'

import DataTableHeaderCell
    from '@/Components/Table/DataTableHeaderCell.vue'

import DataTableBody
    from '@/Components/Table/DataTableBody.vue'

import DataTableRow
    from '@/Components/Table/DataTableRow.vue'

import DataTableCell
    from '@/Components/Table/DataTableCell.vue'

import {
    formatDate,
} from '@/Utils'


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    fiscalYear: {

        type: Object,

        required: true,

    },

})


/*
|--------------------------------------------------------------------------
| Navigation
|--------------------------------------------------------------------------
*/

function back()
{
    router.visit(

        route(
            'fiscal-years.index'
        )

    )
}


function edit()
{
    router.visit(

        route(
            'fiscal-years.edit',
            props.fiscalYear.id
        )

    )
}

</script>


<template>

    <Head
        title="Fiscal Year Detail"
    />


    <AppLayout>

        <div
            class="
                mx-auto
                max-w-7xl
                space-y-6
                px-6
                py-6
            "
        >

            <!-- ===================================================== -->
            <!-- Page Header -->
            <!-- ===================================================== -->

            <PageHeader

                icon="📅"

                title="Fiscal Year Detail"

                subtitle="View fiscal year and accounting period information."

            />


            <!-- ===================================================== -->
            <!-- Main Information -->
            <!-- ===================================================== -->

            <div
                class="
                    grid
                    grid-cols-1
                    gap-6
                    xl:grid-cols-2
                "
            >

                <!-- ================================================= -->
                <!-- Organization -->
                <!-- ================================================= -->

                <Card

                    icon="🏢"

                    title="Organization"

                >

                    <DetailRow

                        label="Company"

                        :value="
                            fiscalYear.company?.company_name
                            ?? '-'
                        "

                    />

                </Card>


                <!-- ================================================= -->
                <!-- Fiscal Year Information -->
                <!-- ================================================= -->

                <Card

                    icon="📅"

                    title="Fiscal Year Information"

                >

                    <DetailRow

                        label="Fiscal Year"

                        :value="
                            fiscalYear.year
                        "

                    />


                    <DetailRow

                        label="Start Date"

                        :value="
                            formatDate(
                                fiscalYear.start_date
                            )
                        "

                    />


                    <DetailRow

                        label="End Date"

                        :value="
                            formatDate(
                                fiscalYear.end_date
                            )
                        "

                    />


                    <DetailRow

                        label="Status"

                    >

                        <template #value>

                            <StatusBadge

                                :status="
                                    fiscalYear.status
                                "

                            />

                        </template>

                    </DetailRow>


                    <DetailRow

                        label="Closed At"

                        :value="
                            fiscalYear.closed_at
                                ? formatDate(
                                    fiscalYear.closed_at
                                )
                                : '-'
                        "

                    />

                </Card>


                <!-- ================================================= -->
                <!-- Accounting Periods -->
                <!-- ================================================= -->

                <Card

                    class="
                        xl:col-span-2
                    "

                    icon="🗓️"

                    title="Accounting Periods"

                >

                    <DataTable

                        v-if="
                            fiscalYear.periods?.length
                        "

                    >

                        <DataTableHead>

                            <DataTableHeaderCell
                                width="100px"
                                align="center"
                            >
                                Period
                            </DataTableHeaderCell>


                            <DataTableHeaderCell
                                width="220px"
                            >
                                Name
                            </DataTableHeaderCell>


                            <DataTableHeaderCell
                                width="180px"
                            >
                                Start Date
                            </DataTableHeaderCell>


                            <DataTableHeaderCell
                                width="180px"
                            >
                                End Date
                            </DataTableHeaderCell>


                            <DataTableHeaderCell
                                width="120px"
                                align="center"
                            >
                                Status
                            </DataTableHeaderCell>

                        </DataTableHead>


                        <DataTableBody>

                            <DataTableRow

                                v-for="
                                    period in fiscalYear.periods
                                "

                                :key="period.id"

                            >

                                <DataTableCell
                                    align="center"
                                >

                                    {{ period.period_number }}

                                </DataTableCell>


                                <DataTableCell>

                                    {{ period.name }}

                                </DataTableCell>


                                <DataTableCell>

                                    {{
                                        formatDate(
                                            period.start_date
                                        )
                                    }}

                                </DataTableCell>


                                <DataTableCell>

                                    {{
                                        formatDate(
                                            period.end_date
                                        )
                                    }}

                                </DataTableCell>


                                <DataTableCell
                                    align="center"
                                >

                                    <StatusBadge

                                        :status="
                                            period.status
                                        "

                                    />

                                </DataTableCell>

                            </DataTableRow>

                        </DataTableBody>

                    </DataTable>


                    <div
                        v-else
                        class="
                            py-8
                            text-center
                            text-sm
                            text-gray-500
                        "
                    >

                        No accounting periods available.

                    </div>

                </Card>


                <!-- ================================================= -->
                <!-- Description -->
                <!-- ================================================= -->

                <Card

                    icon="📝"

                    title="Description"

                >

                    <div
                        class="
                            whitespace-pre-line
                            text-sm
                            leading-7
                            text-gray-700
                        "
                    >

                        {{
                            fiscalYear.description
                            || '-'
                        }}

                    </div>

                </Card>


                <!-- ================================================= -->
                <!-- Audit Information -->
                <!-- ================================================= -->

                <Card

                    icon="👤"

                    title="Audit Information"

                >

                    <DetailRow

                        label="Created By"

                        :value="
                            fiscalYear.creator?.name
                            ?? '-'
                        "

                    />


                    <DetailRow

                        label="Updated By"

                        :value="
                            fiscalYear.updater?.name
                            ?? '-'
                        "

                    />


                    <DetailRow

                        label="Closed By"

                        :value="
                            fiscalYear.closer?.name
                            ?? '-'
                        "

                    />


                    <DetailRow

                        label="Created At"

                        :value="
                            formatDate(
                                fiscalYear.created_at
                            )
                        "

                    />


                    <DetailRow

                        label="Updated At"

                        :value="
                            formatDate(
                                fiscalYear.updated_at
                            )
                        "

                    />

                </Card>

            </div>


            <!-- ===================================================== -->
            <!-- Actions -->
            <!-- ===================================================== -->

            <ActionBar
                bordered
            >

                <ButtonGroup>

                    <BaseButton

                        variant="secondary"

                        @click="back"

                    >
                        Back
                    </BaseButton>


                    <BaseButton

                        v-if="
                            fiscalYear.status === 'Open'
                        "

                        @click="edit"

                    >
                        Edit
                    </BaseButton>

                </ButtonGroup>

            </ActionBar>

        </div>

    </AppLayout>

</template>