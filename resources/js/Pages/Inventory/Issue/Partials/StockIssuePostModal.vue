<script setup>

import BaseButton from '@/Components/Button/BaseButton.vue'
import { formatDate } from '@/Utils'
import { formatCurrency } from '@/Utils/currency'

/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    show: {
        type: Boolean,
        default: false,
    },

    issue: {
        type: Object,
        default: null,
    },

    loading: {
        type: Boolean,
        default: false,
    },

})


/*
|--------------------------------------------------------------------------
| Emits
|--------------------------------------------------------------------------
*/

const emit = defineEmits([
    'close',
    'confirm',
])


/*
|--------------------------------------------------------------------------
| Actions
|--------------------------------------------------------------------------
*/

function closePost()
{
    emit('close')
}


function confirmPost()
{
    emit('confirm')
}


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
        Number(value ?? 0)
    )
}


function totalItems()
{
    return (
        props.issue?.details?.length
        ?? 0
    )
}


function totalQuantity()
{
    return (
        props.issue?.details?.reduce(
            (
                total,
                detail
            ) =>
                total +
                Number(
                    detail.qty ?? 0
                ),
            0
        )
        ?? 0
    )
}


function totalCost()
{
    return (
        props.issue?.details?.reduce(
            (
                total,
                detail
            ) =>
                total +
                Number(
                    detail.total_cost ?? 0
                ),
            0
        )
        ?? 0
    )
}

</script>


<template>

    <Teleport to="body">

        <div
            v-if="show"
            class="
                fixed
                inset-0
                z-[100]
                flex
                items-center
                justify-center
                bg-black/40
                px-4
                py-6
            "
            @click.self="closePost"
        >

            <div
                class="
                    flex
                    max-h-[90vh]
                    w-full
                    max-w-5xl
                    flex-col
                    overflow-hidden
                    rounded-2xl
                    bg-white
                    shadow-2xl
                "
            >

                <!-- ================================================= -->
                <!-- Header -->
                <!-- ================================================= -->

                <div
                    class="
                        flex
                        items-center
                        justify-between
                        border-b
                        border-gray-200
                        px-6
                        py-5
                    "
                >

                    <div>

                        <h2
                            class="
                                text-xl
                                font-semibold
                                text-gray-900
                            "
                        >
                            Review Stock Issue
                        </h2>

                        <p
                            class="
                                mt-1
                                text-sm
                                text-gray-500
                            "
                        >
                            Review the stock issue before
                            posting it to inventory.
                        </p>

                    </div>


                    <button
                        type="button"
                        class="
                            rounded-lg
                            p-2
                            text-gray-400
                            transition
                            hover:bg-gray-100
                            hover:text-gray-700
                        "
                        @click="closePost"
                    >
                        ✕
                    </button>

                </div>


                <!-- ================================================= -->
                <!-- Body -->
                <!-- ================================================= -->

                <div
                    class="
                        flex-1
                        overflow-y-auto
                        p-6
                    "
                >

                    <template v-if="issue">

                        <!-- ========================================= -->
                        <!-- Stock Issue Information -->
                        <!-- ========================================= -->

                        <div
                            class="
                                grid
                                grid-cols-1
                                gap-4
                                rounded-xl
                                border
                                border-gray-200
                                bg-gray-50
                                p-5
                                md:grid-cols-2
                                lg:grid-cols-3
                            "
                        >

                            <!-- Number -->

                            <div>

                                <div
                                    class="
                                        text-xs
                                        font-medium
                                        uppercase
                                        tracking-wide
                                        text-gray-500
                                    "
                                >
                                    Number
                                </div>

                                <div
                                    class="
                                        mt-1
                                        font-semibold
                                        text-gray-900
                                    "
                                >
                                    {{ issue.number }}
                                </div>

                            </div>


                            <!-- Transaction Date -->

                            <div>

                                <div
                                    class="
                                        text-xs
                                        font-medium
                                        uppercase
                                        tracking-wide
                                        text-gray-500
                                    "
                                >
                                    Transaction Date
                                </div>

                                <div
                                    class="
                                        mt-1
                                        font-semibold
                                        text-gray-900
                                    "
                                >
                                    {{
                                        formatDate(
                                            issue.transaction_date
                                        )
                                    }}
                                </div>

                            </div>


                            <!-- Branch -->

                            <div>

                                <div
                                    class="
                                        text-xs
                                        font-medium
                                        uppercase
                                        tracking-wide
                                        text-gray-500
                                    "
                                >
                                    Branch
                                </div>

                                <div
                                    class="
                                        mt-1
                                        font-semibold
                                        text-gray-900
                                    "
                                >
                                    {{
                                        issue.branch?.name
                                        ?? '-'
                                    }}
                                </div>

                            </div>


                            <!-- Warehouse -->

                            <div>

                                <div
                                    class="
                                        text-xs
                                        font-medium
                                        uppercase
                                        tracking-wide
                                        text-gray-500
                                    "
                                >
                                    Warehouse
                                </div>

                                <div
                                    class="
                                        mt-1
                                        font-semibold
                                        text-gray-900
                                    "
                                >
                                    {{
                                        issue.warehouse?.name
                                        ?? '-'
                                    }}
                                </div>

                            </div>


                            <!-- Issue Type -->

                            <div>

                                <div
                                    class="
                                        text-xs
                                        font-medium
                                        uppercase
                                        tracking-wide
                                        text-gray-500
                                    "
                                >
                                    Issue Type
                                </div>

                                <div
                                    class="
                                        mt-1
                                        font-semibold
                                        text-gray-900
                                    "
                                >
                                    {{
                                        issue.issue_type
                                        ?? '-'
                                    }}
                                </div>

                            </div>

                        </div>


                        <!-- ========================================= -->
                        <!-- Details -->
                        <!-- ========================================= -->

                        <div class="mt-6">

                            <div
                                class="
                                    mb-3
                                    text-base
                                    font-semibold
                                    text-gray-900
                                "
                            >
                                Stock Issue Details
                            </div>


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
                                        divide-y
                                        divide-gray-200
                                    "
                                >

                                    <thead
                                        class="bg-gray-50"
                                    >

                                        <tr>

                                            <th
                                                class="
                                                    px-4
                                                    py-3
                                                    text-left
                                                    text-xs
                                                    font-semibold
                                                    uppercase
                                                    tracking-wide
                                                    text-gray-500
                                                "
                                            >
                                                Product Variant
                                            </th>


                                            <th
                                                class="
                                                    px-4
                                                    py-3
                                                    text-left
                                                    text-xs
                                                    font-semibold
                                                    uppercase
                                                    tracking-wide
                                                    text-gray-500
                                                "
                                            >
                                                Unit
                                            </th>


                                            <th
                                                class="
                                                    px-4
                                                    py-3
                                                    text-right
                                                    text-xs
                                                    font-semibold
                                                    uppercase
                                                    tracking-wide
                                                    text-gray-500
                                                "
                                            >
                                                Issue Qty
                                            </th>


                                            <th
                                                class="
                                                    px-4
                                                    py-3
                                                    text-right
                                                    text-xs
                                                    font-semibold
                                                    uppercase
                                                    tracking-wide
                                                    text-gray-500
                                                "
                                            >
                                                Unit Cost
                                            </th>


                                            <th
                                                class="
                                                    px-4
                                                    py-3
                                                    text-right
                                                    text-xs
                                                    font-semibold
                                                    uppercase
                                                    tracking-wide
                                                    text-gray-500
                                                "
                                            >
                                                Total Cost
                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody
                                        class="
                                            divide-y
                                            divide-gray-100
                                            bg-white
                                        "
                                    >

                                        <tr
                                            v-for="
                                                (
                                                    detail,
                                                    index
                                                )
                                                in issue.details
                                            "
                                            :key="
                                                detail.id
                                                ?? index
                                            "
                                        >

                                            <!-- Product -->

                                            <td
                                                class="
                                                    whitespace-nowrap
                                                    px-4
                                                    py-3
                                                "
                                            >

                                                <div
                                                    class="
                                                        font-medium
                                                        text-gray-900
                                                    "
                                                >
                                                    {{
                                                        detail
                                                            .variant
                                                            ?.sku
                                                        ?? '-'
                                                    }}
                                                </div>

                                                <div
                                                    class="
                                                        text-xs
                                                        text-gray-500
                                                    "
                                                >
                                                    {{
                                                        detail
                                                            .variant
                                                            ?.product
                                                            ?.name

                                                        ??

                                                        detail
                                                            .variant
                                                            ?.name

                                                        ??
                                                        '-'
                                                    }}
                                                </div>

                                            </td>


                                            <!-- Unit -->

                                            <td
                                                class="
                                                    whitespace-nowrap
                                                    px-4
                                                    py-3
                                                    text-sm
                                                    text-gray-700
                                                "
                                            >
                                                {{
                                                    detail
                                                        .unit
                                                        ?.name
                                                    ?? '-'
                                                }}
                                            </td>


                                            <!-- Issue Quantity -->

                                            <td
                                                class="
                                                    whitespace-nowrap
                                                    px-4
                                                    py-3
                                                    text-right
                                                    text-sm
                                                    font-semibold
                                                    text-gray-900
                                                "
                                            >
                                                {{
                                                    formatNumber(
                                                        detail.qty
                                                    )
                                                }}
                                            </td>


                                            <!-- Unit Cost -->

                                            <td
                                                class="
                                                    whitespace-nowrap
                                                    px-4
                                                    py-3
                                                    text-right
                                                    text-sm
                                                    text-gray-700
                                                "
                                            >
                                                {{
                                                    formatCurrency(
                                                        detail.unit_cost
                                                    )
                                                }}
                                            </td>


                                            <!-- Total Cost -->

                                            <td
                                                class="
                                                    whitespace-nowrap
                                                    px-4
                                                    py-3
                                                    text-right
                                                    text-sm
                                                    font-semibold
                                                    text-gray-900
                                                "
                                            >
                                                {{
                                                    formatCurrency(
                                                        detail.total_cost
                                                    )
                                                }}
                                            </td>

                                        </tr>

                                    </tbody>

                                </table>

                            </div>

                        </div>


                        <!-- ========================================= -->
                        <!-- Description -->
                        <!-- ========================================= -->

                        <div
                            v-if="issue.description"
                            class="
                                mt-6
                                rounded-xl
                                border
                                border-gray-200
                                bg-white
                                p-5
                            "
                        >

                            <div
                                class="
                                    text-xs
                                    font-medium
                                    uppercase
                                    tracking-wide
                                    text-gray-500
                                "
                            >
                                Description
                            </div>

                            <div
                                class="
                                    mt-2
                                    whitespace-pre-line
                                    text-sm
                                    text-gray-700
                                "
                            >
                                {{ issue.description }}
                            </div>

                        </div>


                        <!-- ========================================= -->
                        <!-- Summary -->
                        <!-- ========================================= -->

                        <div
                            class="
                                mt-6
                                flex
                                justify-end
                            "
                        >

                            <div
                                class="
                                    w-full
                                    max-w-md
                                    rounded-xl
                                    border
                                    border-gray-200
                                    bg-gray-50
                                    p-5
                                "
                            >

                                <!-- Total Items -->

                                <div
                                    class="
                                        flex
                                        justify-between
                                        py-2
                                        text-sm
                                    "
                                >

                                    <span
                                        class="
                                            text-gray-600
                                        "
                                    >
                                        Total Items
                                    </span>

                                    <span
                                        class="
                                            font-semibold
                                        "
                                    >
                                        {{ totalItems() }}
                                    </span>

                                </div>


                                <!-- Total Quantity -->

                                <div
                                    class="
                                        flex
                                        justify-between
                                        py-2
                                        text-sm
                                    "
                                >

                                    <span
                                        class="
                                            text-gray-600
                                        "
                                    >
                                        Total Quantity
                                    </span>

                                    <span
                                        class="
                                            font-semibold
                                        "
                                    >
                                        {{
                                            formatNumber(
                                                totalQuantity()
                                            )
                                        }}
                                    </span>

                                </div>


                                <!-- Total Cost -->

                                <div
                                    class="
                                        mt-2
                                        flex
                                        justify-between
                                        border-t
                                        border-gray-200
                                        pt-3
                                    "
                                >

                                    <span
                                        class="
                                            font-semibold
                                            text-gray-900
                                        "
                                    >
                                        Total Cost
                                    </span>

                                    <span
                                        class="
                                            text-lg
                                            font-bold
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            formatCurrency(
                                                totalCost()
                                            )
                                        }}
                                    </span>

                                </div>

                            </div>

                        </div>


                        <!-- ========================================= -->
                        <!-- Warning -->
                        <!-- ========================================= -->

                        <div
                            class="
                                mt-6
                                rounded-xl
                                border
                                border-amber-200
                                bg-amber-50
                                p-4
                            "
                        >

                            <div
                                class="
                                    text-sm
                                    font-semibold
                                    text-amber-800
                                "
                            >
                                Before posting
                            </div>

                            <p
                                class="
                                    mt-1
                                    text-sm
                                    text-amber-700
                                "
                            >
                                Please make sure the warehouse, issue type,
                                products, and quantities are correct.
                                Posting this transaction will issue the stock
                                from the selected warehouse and create
                                inventory movements.
                            </p>

                        </div>

                        </template>

                        </div>


                        <!-- ================================================= -->
                        <!-- Footer -->
                        <!-- ================================================= -->

                        <div
                            class="
                                flex
                                items-center
                                justify-end
                                gap-3
                                border-t
                                border-gray-200
                                bg-gray-50
                                px-6
                                py-4
                            "
                        >

                            <BaseButton
                                type="button"
                                variant="secondary"
                                @click="closePost"
                            >
                                Cancel
                            </BaseButton>


                            <BaseButton
                                type="button"
                                variant="success"
                                :loading="loading"
                                :disabled="
                                    !issue ||
                                    loading
                                "
                                @click="confirmPost"
                            >
                                Post Stock Issue
                            </BaseButton>

                        </div>

            </div>

        </div>

    </Teleport>

</template>