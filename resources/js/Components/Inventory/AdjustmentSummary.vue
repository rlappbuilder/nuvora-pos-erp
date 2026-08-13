<script setup>

import { computed } from 'vue'

const props = defineProps({

    details: {
        type: Array,
        default: () => [],
    },

})

/*
|--------------------------------------------------------------------------
| Totals
|--------------------------------------------------------------------------
*/

const totalItems = computed(() => {

    return props.details.length

})

const totalSystemQty = computed(() => {

    return props.details.reduce(
        (total, detail) =>
            total +
            Number(detail.system_qty || 0),
        0
    )

})

const totalActualQty = computed(() => {

    return props.details.reduce(
        (total, detail) =>
            total +
            Number(detail.actual_qty || 0),
        0
    )

})

const totalDifference = computed(() => {

    return props.details.reduce(
        (total, detail) =>
            total +
            Number(detail.difference_qty || 0),
        0
    )

})

const totalCost = computed(() => {

    return props.details.reduce(
        (total, detail) =>
            total +
            Number(detail.total_cost || 0),
        0
    )

})

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function formatNumber(value)
{
    return new Intl.NumberFormat(
        'id-ID',
        {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2,
        }
    ).format(
        Number(value || 0)
    )
}

function formatCurrency(value)
{
    return new Intl.NumberFormat(
        'id-ID',
        {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }
    ).format(
        Number(value || 0)
    )
}

</script>


<template>

<div class="space-y-3">

    <!-- ========================================================= -->
    <!-- Summary Cards -->
    <!-- ========================================================= -->

    <div
        class="
            grid
            gap-3
            sm:grid-cols-2
            lg:grid-cols-4
        "
    >

        <!-- Total Items -->

        <div
            class="
                rounded-xl
                border
                border-gray-200
                bg-gray-50
                p-4
            "
        >

            <div
                class="
                    text-xs
                    font-medium
                    text-gray-500
                "
            >
                Total Items
            </div>

            <div
                class="
                    mt-1
                    text-xl
                    font-bold
                    text-gray-800
                "
            >
                {{ totalItems }}
            </div>

        </div>


        <!-- System Quantity -->

        <div
            class="
                rounded-xl
                border
                border-indigo-100
                bg-indigo-50
                p-4
            "
        >

            <div
                class="
                    text-xs
                    font-medium
                    text-indigo-600
                "
            >
                System Quantity
            </div>

            <div
                class="
                    mt-1
                    text-xl
                    font-bold
                    text-indigo-700
                "
            >
                {{
                    formatNumber(
                        totalSystemQty
                    )
                }}
            </div>

        </div>


        <!-- Actual Quantity -->

        <div
            class="
                rounded-xl
                border
                border-emerald-100
                bg-emerald-50
                p-4
            "
        >

            <div
                class="
                    text-xs
                    font-medium
                    text-emerald-600
                "
            >
                Actual Quantity
            </div>

            <div
                class="
                    mt-1
                    text-xl
                    font-bold
                    text-emerald-700
                "
            >
                {{
                    formatNumber(
                        totalActualQty
                    )
                }}
            </div>

        </div>


        <!-- Difference -->

        <div
            class="
                rounded-xl
                border
                border-amber-100
                bg-amber-50
                p-4
            "
        >

            <div
                class="
                    text-xs
                    font-medium
                    text-amber-600
                "
            >
                Difference
            </div>

            <div
                class="
                    mt-1
                    text-xl
                    font-bold
                "
                :class="
                    totalDifference > 0
                        ? 'text-emerald-700'
                        : totalDifference < 0
                            ? 'text-red-700'
                            : 'text-gray-700'
                "
            >
                {{
                    totalDifference > 0
                        ? '+'
                        : ''
                }}{{
                    formatNumber(
                        totalDifference
                    )
                }}
            </div>

        </div>

    </div>


    <!-- ========================================================= -->
    <!-- Total Adjustment Cost -->
    <!-- ========================================================= -->

    <div
        class="
            rounded-xl
            border
            border-gray-200
            bg-gray-50
            p-4
        "
    >

        <div
            class="
                text-xs
                font-medium
                text-gray-500
            "
        >
            Total Adjustment Cost
        </div>

        <div
            class="
                mt-1
                text-xl
                font-bold
                text-gray-800
            "
        >
            {{
                formatCurrency(
                    totalCost
                )
            }}
        </div>

    </div>

</div>

</template>