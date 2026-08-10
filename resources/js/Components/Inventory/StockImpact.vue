<script setup>

import {
    computed,
} from 'vue'

const props = defineProps({

    movements: {

        type: Array,

        default: () => [],

    },

})


/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function number(value)
{
    return Number(value ?? 0)
}


function formatNumber(value)
{
    return new Intl.NumberFormat(
        'id-ID',
        {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2,
        }
    ).format(
        number(value)
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
        number(value)
    )
}
function variantLabel(movement)
{
    const variant =
        movement.productVariant

    if (!variant) {

        return `Variant #${movement.product_variant_id}`

    }

    return [

        variant.sku,

        variant.product?.name,

        variant.name,

    ]
        .filter(Boolean)
        .join(' - ')
}

/*
|--------------------------------------------------------------------------
| Totals
|--------------------------------------------------------------------------
*/

const totalQtyIn = computed(() => {

    return props.movements.reduce(

        (total, movement) =>
            total +
            number(movement.qty_in),

        0

    )

})


const totalQtyOut = computed(() => {

    return props.movements.reduce(

        (total, movement) =>
            total +
            number(movement.qty_out),

        0

    )

})


const netQty = computed(() => {

    return (
        totalQtyIn.value -
        totalQtyOut.value
    )

})


const totalCost = computed(() => {

    return props.movements.reduce(

        (total, movement) =>
            total +
            number(movement.total_cost),

        0

    )

})

</script>

<template>

<div>

    <!-- Empty -->

    <div
        v-if="!movements.length"
        class="
            rounded-xl
            border
            border-gray-200
            bg-gray-50
            px-5
            py-8
            text-center
            text-sm
            text-gray-500
        "
    >

        No stock movement found.

    </div>


    <template v-else>

        <!-- ===================================================== -->
        <!-- Movement Table -->
        <!-- ===================================================== -->

        <div
            class="
                overflow-x-auto
                rounded-xl
                border
                border-gray-200
            "
        >

            <table
                class="
                    min-w-full
                    text-sm
                "
            >

                <thead
                    class="
                        border-b
                        border-gray-200
                        bg-gray-50
                    "
                >

                    <tr>

                        <th
                            class="
                                px-4
                                py-3
                                text-left
                                font-semibold
                                text-gray-600
                            "
                        >
                            Product Variant
                        </th>

                        <th
                            class="
                                px-4
                                py-3
                                text-left
                                font-semibold
                                text-gray-600
                            "
                        >
                            Unit
                        </th>

                        <th
                            class="
                                px-4
                                py-3
                                text-right
                                font-semibold
                                text-gray-600
                            "
                        >
                            Qty In
                        </th>

                        <th
                            class="
                                px-4
                                py-3
                                text-right
                                font-semibold
                                text-gray-600
                            "
                        >
                            Qty Out
                        </th>

                        <th
                            class="
                                px-4
                                py-3
                                text-right
                                font-semibold
                                text-gray-600
                            "
                        >
                            Net Qty
                        </th>

                        <th
                            class="
                                px-4
                                py-3
                                text-right
                                font-semibold
                                text-gray-600
                            "
                        >
                            Unit Cost
                        </th>

                        <th
                            class="
                                px-4
                                py-3
                                text-right
                                font-semibold
                                text-gray-600
                            "
                        >
                            Total Cost
                        </th>

                        <th
                            class="
                                px-4
                                py-3
                                text-right
                                font-semibold
                                text-gray-600
                            "
                        >
                            Balance
                        </th>

                    </tr>

                </thead>


                <tbody
                    class="
                        divide-y
                        divide-gray-100
                    "
                >

                    <tr
                        v-for="movement in movements"
                        :key="movement.id"
                        class="
                            hover:bg-gray-50
                        "
                    >

                        <!-- Product -->

     <td
    class="
        px-4
        py-3
    "
>

    <!-- SKU -->

    <div
        class="
            font-semibold
            text-gray-800
        "
    >

        {{
            movement.product_variant?.sku
            ??
            `Variant #${movement.product_variant_id}`
        }}

    </div>


    <!-- Product + Variant -->

    <div
        class="
            mt-0.5
            text-xs
            text-gray-500
        "
    >

        {{
            movement.product_variant
                ?.product?.name
            ?? '-'
        }}

        <span
            v-if="
                movement.product_variant?.name
            "
        >

            •
            {{
                movement.product_variant.name
            }}

        </span>

    </div>

</td>


                        <!-- Unit -->

                        <td
                            class="
                                px-4
                                py-3
                                text-gray-600
                            "
                        >

                            {{
                                movement.unit?.label
                                ??
                                movement.unit?.name
                                ??
                                `Unit #${movement.unit_id}`
                            }}

                        </td>


                        <!-- Qty In -->

                        <td
                            class="
                                px-4
                                py-3
                                text-right
                                font-semibold
                                text-emerald-600
                            "
                        >

                            +{{
                                formatNumber(
                                    movement.qty_in
                                )
                            }}

                        </td>


                        <!-- Qty Out -->

                        <td
                            class="
                                px-4
                                py-3
                                text-right
                                text-red-600
                            "
                        >

                            {{
                                formatNumber(
                                    movement.qty_out
                                )
                            }}

                        </td>


                        <!-- Net -->

                        <td
                            class="
                                px-4
                                py-3
                                text-right
                                font-semibold
                                text-gray-800
                            "
                        >

                            {{
                                formatNumber(
                                    number(
                                        movement.qty_in
                                    )
                                    -
                                    number(
                                        movement.qty_out
                                    )
                                )
                            }}

                        </td>


                        <!-- Unit Cost -->

                        <td
                            class="
                                px-4
                                py-3
                                text-right
                                text-gray-700
                            "
                        >

                            {{
                                formatCurrency(
                                    movement.unit_cost
                                )
                            }}

                        </td>


                        <!-- Total Cost -->

                        <td
                            class="
                                px-4
                                py-3
                                text-right
                                font-semibold
                                text-gray-800
                            "
                        >

                            {{
                                formatCurrency(
                                    movement.total_cost
                                )
                            }}

                        </td>


                        <!-- Balance -->

                        <td
                            class="
                                px-4
                                py-3
                                text-right
                                text-gray-600
                            "
                        >

                            {{
                                formatNumber(
                                    movement.balance_qty
                                )
                            }}

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>


        <!-- ===================================================== -->
        <!-- Summary -->
        <!-- ===================================================== -->

        <div
            class="
                mt-4
                grid
                gap-3
                sm:grid-cols-2
                lg:grid-cols-4
            "
        >

            <!-- Qty In -->

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
                    Total Qty In
                </div>

                <div
                    class="
                        mt-1
                        text-xl
                        font-bold
                        text-emerald-700
                    "
                >

                    +{{
                        formatNumber(
                            totalQtyIn
                        )
                    }}

                </div>

            </div>


            <!-- Qty Out -->

            <div
                class="
                    rounded-xl
                    border
                    border-red-100
                    bg-red-50
                    p-4
                "
            >

                <div
                    class="
                        text-xs
                        font-medium
                        text-red-600
                    "
                >
                    Total Qty Out
                </div>

                <div
                    class="
                        mt-1
                        text-xl
                        font-bold
                        text-red-700
                    "
                >

                    {{
                        formatNumber(
                            totalQtyOut
                        )
                    }}

                </div>

            </div>


            <!-- Net -->

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
                    Net Quantity
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
                            netQty
                        )
                    }}

                </div>

            </div>


            <!-- Total Cost -->

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
                    Total Cost
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

</div>

</template>