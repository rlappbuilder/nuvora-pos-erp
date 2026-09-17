<script setup>

import {
    computed,
    ref,
} from 'vue'

import {
    router,
} from '@inertiajs/vue3'

import AppLayout from '@/Layouts/AppLayout.vue'

import BaseButton from '@/Components/Button/BaseButton.vue'
import StatusBadge from '@/Components/Display/StatusBadge.vue'

import DataTable from '@/Components/Table/DataTable.vue'
import DataTableHead from '@/Components/Table/DataTableHead.vue'
import DataTableBody from '@/Components/Table/DataTableBody.vue'
import DataTableHeaderCell from '@/Components/Table/DataTableHeaderCell.vue'
import DataTableRow from '@/Components/Table/DataTableRow.vue'
import DataTableCell from '@/Components/Table/DataTableCell.vue'

import WorkflowTimeline
    from '@/Components/Workflow/WorkflowTimeline.vue'

import AuditTrail
    from '@/Components/Workflow/AuditTrail.vue'

import {
    formatDate,
} from '@/Utils'


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    settlement: {

        type: Object,

        default: () => ({}),

    },

})


/*
|--------------------------------------------------------------------------
| Tabs
|--------------------------------------------------------------------------
*/

const activeTab = ref('detail')


/*
|--------------------------------------------------------------------------
| Currency
|--------------------------------------------------------------------------
*/

const formatAmount = (value) => {

    return new Intl.NumberFormat(

        'id-ID',

        {

            minimumFractionDigits: 0,

            maximumFractionDigits: 0,

        }

    ).format(

        Number(value || 0)

    )

}


/*
|--------------------------------------------------------------------------
| Detail Items
|--------------------------------------------------------------------------
*/

const totalItems = computed(() => {

    return (
        props.settlement?.details
            ?.reduce(
                (
                    total,
                    detail
                ) => {

                    return total +
                        Number(
                            detail.qty_sold || 0
                        )

                },
                0
            )
        ?? 0
    )

})


/*
|--------------------------------------------------------------------------
| Activities
|--------------------------------------------------------------------------
*/

const activities = computed(() => {

    return (
        props.settlement?.activities
        ?? []
    )

})


/*
|--------------------------------------------------------------------------
| Actions
|--------------------------------------------------------------------------
*/

function back()
{

    router.get(
        route(
            'consignment-settlements.index'
        )
    )

}


function print()
{

    window.open(

        route(
            'consignment-settlements.print',
            props.settlement.id
        ),

        '_blank'

    )

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
                    Consignment Settlement
                </h1>


                <p
                    class="
                        mt-1
                        text-sm
                        text-gray-500
                    "
                >
                    Settlement transaction detail.
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
                    @click="back"
                >
                    Back
                </BaseButton>


                <BaseButton
                    @click="print"
                >
                    Print
                </BaseButton>

            </div>

        </div>


        <!-- ========================================================= -->
        <!-- Summary -->
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

            <div
                class="
                    grid
                    grid-cols-1
                    divide-y
                    divide-gray-100
                    sm:grid-cols-2
                    sm:divide-x
                    sm:divide-y-0
                    lg:grid-cols-4
                "
            >

                <!-- Settlement -->

                <div class="px-4 py-4">

                    <div
                        class="
                            text-xs
                            font-medium
                            text-gray-500
                        "
                    >
                        Settlement
                    </div>


                    <div
                        class="
                            mt-1
                            font-semibold
                            text-gray-900
                        "
                    >
                        {{
                            settlement.settlement_number
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
                            settlement.settlement_date
                                ? formatDate(
                                    settlement.settlement_date
                                )
                                : '-'
                        }}
                    </div>

                </div>


                <!-- Reseller -->

                <div class="px-4 py-4">

                    <div
                        class="
                            text-xs
                            font-medium
                            text-gray-500
                        "
                    >
                        Reseller
                    </div>


                    <div
                        class="
                            mt-1
                            font-semibold
                            text-gray-900
                        "
                    >
                        {{
                            settlement.reseller?.name
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
                            settlement.reseller?.reseller_code
                            ?? '-'
                        }}
                    </div>

                </div>


                <!-- Location -->

                <div class="px-4 py-4">

                    <div
                        class="
                            text-xs
                            font-medium
                            text-gray-500
                        "
                    >
                        Branch / Warehouse
                    </div>


                    <div
                        class="
                            mt-1
                            font-semibold
                            text-gray-900
                        "
                    >
                        {{
                            settlement.branch?.name
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
                            settlement.warehouse?.name
                            ?? '-'
                        }}
                    </div>

                </div>


                <!-- Status -->

                <div class="px-4 py-4">

                    <div
                        class="
                            text-xs
                            font-medium
                            text-gray-500
                        "
                    >
                        Status
                    </div>


                    <div class="mt-2">

                        <StatusBadge
                            :status="
                                settlement.status
                            "
                        />

                    </div>

                </div>

            </div>

        </div>


        <!-- ========================================================= -->
        <!-- Period -->
        <!-- ========================================================= -->

        <div
            class="
                rounded-xl
                border
                border-gray-100
                bg-white
                px-4
                py-3
                shadow-sm
            "
        >

            <div
                class="
                    flex
                    flex-col
                    gap-2
                    sm:flex-row
                    sm:items-center
                "
            >

                <div
                    class="
                        text-xs
                        font-medium
                        text-gray-500
                    "
                >
                    Settlement Period
                </div>


                <div
                    class="
                        text-sm
                        font-medium
                        text-gray-900
                    "
                >

                    {{
                        settlement.period_from
                            ? formatDate(
                                settlement.period_from
                            )
                            : '-'
                    }}

                    <span
                        class="mx-1 text-gray-400"
                    >
                        →
                    </span>

                    {{
                        settlement.period_to
                            ? formatDate(
                                settlement.period_to
                            )
                            : '-'
                    }}

                </div>

            </div>

        </div>


        <!-- ========================================================= -->
        <!-- Tabs -->
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

            <!-- Tab Navigation -->

            <div
                class="
                    flex
                    overflow-x-auto
                    border-b
                    border-gray-100
                "
            >

                <button
                    type="button"
                    class="
                        whitespace-nowrap
                        border-b-2
                        px-5
                        py-3
                        text-sm
                        font-medium
                        transition
                    "
                    :class="
                        activeTab === 'detail'
                            ? `
                                border-blue-600
                                text-blue-600
                            `
                            : `
                                border-transparent
                                text-gray-500
                                hover:text-gray-700
                            `
                    "
                    @click="
                        activeTab = 'detail'
                    "
                >
                    Settlement Detail
                </button>


                <button
                    type="button"
                    class="
                        whitespace-nowrap
                        border-b-2
                        px-5
                        py-3
                        text-sm
                        font-medium
                        transition
                    "
                    :class="
                        activeTab === 'workflow'
                            ? `
                                border-blue-600
                                text-blue-600
                            `
                            : `
                                border-transparent
                                text-gray-500
                                hover:text-gray-700
                            `
                    "
                    @click="
                        activeTab = 'workflow'
                    "
                >
                    Workflow Timeline
                </button>


                <button
                    type="button"
                    class="
                        whitespace-nowrap
                        border-b-2
                        px-5
                        py-3
                        text-sm
                        font-medium
                        transition
                    "
                    :class="
                        activeTab === 'audit'
                            ? `
                                border-blue-600
                                text-blue-600
                            `
                            : `
                                border-transparent
                                text-gray-500
                                hover:text-gray-700
                            `
                    "
                    @click="
                        activeTab = 'audit'
                    "
                >
                    Audit Trail
                </button>

            </div>


            <!-- ===================================================== -->
            <!-- Settlement Detail Tab -->
            <!-- ===================================================== -->

            <div
                v-if="
                    activeTab === 'detail'
                "
                class="space-y-4 p-4"
            >

                <!-- Details -->

                <div
                    class="
                        overflow-hidden
                        rounded-xl
                        border
                        border-gray-100
                        bg-white
                    "
                >

                    <div
                        class="
                            flex
                            items-center
                            justify-between
                            border-b
                            border-gray-100
                            px-4
                            py-3
                        "
                    >

                        <div>

                            <div
                                class="
                                    text-sm
                                    font-semibold
                                    text-gray-900
                                "
                            >
                                Settlement Details
                            </div>


                            <div
                                class="
                                    mt-0.5
                                    text-xs
                                    text-gray-500
                                "
                            >
                                {{ totalItems }} Items
                            </div>

                        </div>

                    </div>


                    <div
                        v-if="
                            settlement.details?.length
                        "
                        class="overflow-x-auto"
                    >

                        <DataTable>

                            <DataTableHead>

                                <DataTableHeaderCell
                                    width="50px"
                                    align="center"
                                >
                                    #
                                </DataTableHeaderCell>


                                <DataTableHeaderCell
                                    width="280px"
                                >
                                    Product
                                </DataTableHeaderCell>


                                <DataTableHeaderCell
                                    width="120px"
                                >
                                    SKU
                                </DataTableHeaderCell>


                                <DataTableHeaderCell
                                    width="100px"
                                >
                                    Unit
                                </DataTableHeaderCell>


                                <DataTableHeaderCell
                                    width="100px"
                                    align="right"
                                >
                                    Qty
                                </DataTableHeaderCell>


                                <DataTableHeaderCell
                                    width="160px"
                                    align="right"
                                >
                                    Unit Price
                                </DataTableHeaderCell>


                                <DataTableHeaderCell
                                    width="180px"
                                    align="right"
                                >
                                    Total
                                </DataTableHeaderCell>

                            </DataTableHead>


                            <DataTableBody>

                                <DataTableRow
                                    v-for="
                                        (
                                            detail,
                                            index
                                        ) in
                                        (
                                            settlement.details
                                            ?? []
                                        )
                                    "
                                    :key="
                                        detail.id
                                        ?? index
                                    "
                                >

                                    <!-- Number -->

                                    <DataTableCell
                                        align="center"
                                    >

                                        <span
                                            class="
                                                text-xs
                                                text-gray-500
                                            "
                                        >
                                            {{ index + 1 }}
                                        </span>

                                    </DataTableCell>


                                    <!-- Product -->

                                    <DataTableCell>

                                        <div
                                            class="
                                                font-medium
                                                text-gray-900
                                            "
                                        >
                                            {{
                                                detail.variant?.product?.name
                                                ?? detail.variant?.name
                                                ?? '-'
                                            }}
                                        </div>

                                    </DataTableCell>


                                    <!-- SKU -->

                                    <DataTableCell>

                                        <span
                                            class="
                                                text-sm
                                                text-gray-600
                                            "
                                        >
                                            {{
                                                detail.variant?.sku
                                                ?? '-'
                                            }}
                                        </span>

                                    </DataTableCell>


                                    <!-- Unit -->

                                    <DataTableCell>

                                        <span
                                            class="
                                                text-sm
                                                text-gray-600
                                            "
                                        >
                                            {{
                                                detail.unit?.name
                                                ?? '-'
                                            }}
                                        </span>

                                    </DataTableCell>


                                    <!-- Qty -->

                                    <DataTableCell
                                        align="right"
                                    >

                                        <span
                                            class="
                                                font-medium
                                                tabular-nums
                                                text-gray-900
                                            "
                                        >
                                            {{
                                                detail.qty_sold
                                                ?? 0
                                            }}
                                        </span>

                                    </DataTableCell>


                                    <!-- Unit Price -->

                                    <DataTableCell
                                        align="right"
                                    >

                                        <span
                                            class="
                                                tabular-nums
                                                text-gray-700
                                            "
                                        >
                                            Rp
                                            {{
                                                formatAmount(
                                                    detail.unit_price
                                                )
                                            }}
                                        </span>

                                    </DataTableCell>


                                    <!-- Total -->

                                    <DataTableCell
                                        align="right"
                                    >

                                        <span
                                            class="
                                                font-medium
                                                tabular-nums
                                                text-gray-900
                                            "
                                        >
                                            Rp
                                            {{
                                                formatAmount(
                                                    detail.total_amount
                                                )
                                            }}
                                        </span>

                                    </DataTableCell>

                                </DataTableRow>

                            </DataTableBody>

                        </DataTable>

                    </div>


                    <div
                        v-else
                        class="
                            px-4
                            py-8
                            text-center
                            text-sm
                            text-gray-500
                        "
                    >
                        No settlement details.
                    </div>

                </div>


                <!-- Totals -->

                <div
                    class="
                        rounded-xl
                        border
                        border-gray-100
                        bg-white
                    "
                >

                    <div
                        class="
                            ml-auto
                            w-full
                            max-w-md
                            space-y-2
                            px-4
                            py-4
                        "
                    >

                        <div
                            class="
                                flex
                                items-center
                                justify-between
                                text-sm
                                text-gray-600
                            "
                        >

                            <span>
                                Subtotal
                            </span>

                            <span class="tabular-nums">
                                Rp
                                {{
                                    formatAmount(
                                        settlement.subtotal
                                    )
                                }}
                            </span>

                        </div>


                        <div
                            class="
                                flex
                                items-center
                                justify-between
                                text-sm
                                text-gray-600
                            "
                        >

                            <span>
                                Adjustment
                            </span>

                            <span class="tabular-nums">
                                Rp
                                {{
                                    formatAmount(
                                        settlement.adjustment_amount
                                    )
                                }}
                            </span>

                        </div>


                        <div
                            class="
                                flex
                                items-center
                                justify-between
                                border-t
                                border-gray-100
                                pt-2
                                text-sm
                                font-semibold
                                text-gray-900
                            "
                        >

                            <span>
                                Grand Total
                            </span>

                            <span class="tabular-nums">
                                Rp
                                {{
                                    formatAmount(
                                        settlement.grand_total
                                    )
                                }}
                            </span>

                        </div>


                        <div
                            class="
                                flex
                                items-center
                                justify-between
                                pt-2
                                text-sm
                                text-gray-600
                            "
                        >

                            <span>
                                Payment
                            </span>

                            <span class="tabular-nums">
                                Rp
                                {{
                                    formatAmount(
                                        settlement.payment_amount
                                    )
                                }}
                            </span>

                        </div>


                        <div
                            class="
                                flex
                                items-center
                                justify-between
                                text-sm
                                font-medium
                                text-gray-900
                            "
                        >

                            <span>
                                Receivable
                            </span>

                            <span class="tabular-nums">
                                Rp
                                {{
                                    formatAmount(
                                        settlement.receivable_amount
                                    )
                                }}
                            </span>

                        </div>


                        <div
                            class="
                                flex
                                items-center
                                justify-between
                                pt-1
                                text-xs
                                text-gray-500
                            "
                        >

                            <span>
                                Payment Status
                            </span>

                            <span>
                                {{
                                    settlement.payment_status
                                    ?? '-'
                                }}
                            </span>

                        </div>

                    </div>

                </div>


                <!-- Remarks -->

                <div
                    v-if="
                        settlement.remarks
                    "
                    class="
                        rounded-xl
                        border
                        border-gray-100
                        bg-white
                        px-4
                        py-3
                    "
                >

                    <div
                        class="
                            text-xs
                            font-medium
                            text-gray-500
                        "
                    >
                        Remarks
                    </div>


                    <div
                        class="
                            mt-1
                            whitespace-pre-line
                            text-sm
                            text-gray-700
                        "
                    >
                        {{ settlement.remarks }}
                    </div>

                </div>

            </div>


            <!-- ===================================================== -->
            <!-- Workflow Timeline Tab -->
            <!-- ===================================================== -->

            <div
                v-else-if="
                    activeTab === 'workflow'
                "
                class="p-4"
            >

                <WorkflowTimeline
                    :activities="
                        activities
                    "
                />

            </div>


            <!-- ===================================================== -->
            <!-- Audit Trail Tab -->
            <!-- ===================================================== -->

            <div
                v-else-if="
                    activeTab === 'audit'
                "
                class="p-4"
            >

                <AuditTrail
                    :activities="
                        activities
                    "
                />

            </div>

        </div>

    </div>

</AppLayout>

</template>