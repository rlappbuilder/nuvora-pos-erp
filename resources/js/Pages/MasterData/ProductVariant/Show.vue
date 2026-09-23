<script setup>

import {
    router,
} from '@inertiajs/vue3'

import AppLayout
    from '@/Layouts/AppLayout.vue'

import BaseButton
    from '@/Components/Button/BaseButton.vue'

import StatusBadge
    from '@/Components/Display/StatusBadge.vue'

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


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    variant: {

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

    router.get(
        route(
            'product-variants.index'
        )
    )

}


function edit()
{

    router.get(
        route(
            'product-variants.edit',
            props.variant.id
        )
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
                    Product Variant
                </h1>


                <p
                    class="
                        mt-1
                        text-sm
                        font-medium
                        text-gray-700
                    "
                >
                    {{
                        variant.sku
                        ?? '-'
                    }}

                    <span class="mx-1 text-gray-400">
                        ·
                    </span>

                    {{
                        variant.name
                        ?? '-'
                    }}
                </p>


                <p
                    class="
                        mt-0.5
                        text-sm
                        text-gray-500
                    "
                >
                    Product variant detail and attribute
                    configuration.
                </p>

            </div>


            <!-- Actions -->

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
                    @click="edit"
                >
                    Edit
                </BaseButton>

            </div>

        </div>


        <!-- ========================================================= -->
        <!-- General Information -->
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
                    mb-4
                    text-sm
                    font-semibold
                    text-gray-900
                "
            >
                General Information
            </div>


            <div
                class="
                    grid
                    grid-cols-1
                    gap-x-8
                    gap-y-4
                    sm:grid-cols-2
                "
            >

                <!-- SKU -->

                <div>

                    <div class="text-xs text-gray-500">
                        SKU
                    </div>

                    <div
                        class="
                            mt-1
                            text-sm
                            font-medium
                            text-gray-900
                        "
                    >
                        {{ variant.sku || '-' }}
                    </div>

                </div>


                <!-- Variant Name -->

                <div>

                    <div class="text-xs text-gray-500">
                        Variant Name
                    </div>

                    <div
                        class="
                            mt-1
                            text-sm
                            font-medium
                            text-gray-900
                        "
                    >
                        {{ variant.name || '-' }}
                    </div>

                </div>


                <!-- Barcode -->

                <div>

                    <div class="text-xs text-gray-500">
                        Barcode
                    </div>

                    <div class="mt-1 text-sm text-gray-900">
                        {{ variant.barcode || '-' }}
                    </div>

                </div>


                <!-- Sort Order -->

                <div>

                    <div class="text-xs text-gray-500">
                        Sort Order
                    </div>

                    <div class="mt-1 text-sm text-gray-900">
                        {{ variant.sort_order ?? '-' }}
                    </div>

                </div>


                <!-- Default -->

                <div>

                    <div class="text-xs text-gray-500">
                        Default Variant
                    </div>

                    <div class="mt-1">

                        <span
                            :class="[
                                'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold',
                                variant.is_default
                                    ? 'bg-green-100 text-green-700'
                                    : 'bg-gray-100 text-gray-700'
                            ]"
                        >
                            {{
                                variant.is_default
                                    ? '✓ Yes'
                                    : '✕ No'
                            }}
                        </span>

                    </div>

                </div>


                <!-- Status -->

                <div>

                    <div class="text-xs text-gray-500">
                        Status
                    </div>

                    <div class="mt-1">

                        <StatusBadge
                            :status="
                                variant.is_active
                            "
                        />

                    </div>

                </div>

            </div>

        </div>


        <!-- ========================================================= -->
        <!-- Product Information -->
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
                    mb-4
                    text-sm
                    font-semibold
                    text-gray-900
                "
            >
                Product Information
            </div>


            <div
                class="
                    grid
                    grid-cols-1
                    gap-x-8
                    gap-y-4
                    sm:grid-cols-2
                "
            >

                <!-- Product Code -->

                <div>

                    <div class="text-xs text-gray-500">
                        Product Code
                    </div>

                    <div
                        class="
                            mt-1
                            text-sm
                            font-medium
                            text-gray-900
                        "
                    >
                        {{
                            variant.product?.code
                            || '-'
                        }}
                    </div>

                </div>


                <!-- Product Name -->

                <div>

                    <div class="text-xs text-gray-500">
                        Product Name
                    </div>

                    <div
                        class="
                            mt-1
                            text-sm
                            font-medium
                            text-gray-900
                        "
                    >
                        {{
                            variant.product?.name
                            || '-'
                        }}
                    </div>

                </div>

            </div>

        </div>


        <!-- ========================================================= -->
        <!-- Variant Attributes -->
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
                        Variant Attributes
                    </div>


                    <div
                        class="
                            mt-0.5
                            text-xs
                            text-gray-500
                        "
                    >
                        {{
                            variant.values?.length
                            ?? 0
                        }}
                        Attributes
                    </div>

                </div>

            </div>


            <!-- Has Attributes -->

            <div
                v-if="
                    variant.values?.length
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


                        <DataTableHeaderCell>
                            Attribute
                        </DataTableHeaderCell>


                        <DataTableHeaderCell>
                            Value
                        </DataTableHeaderCell>

                    </DataTableHead>


                    <DataTableBody>

                        <DataTableRow
                            v-for="
                                (
                                    item,
                                    index
                                ) in
                                variant.values
                            "
                            :key="
                                item.id
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
                                    {{
                                        index + 1
                                    }}
                                </span>

                            </DataTableCell>


                            <!-- Attribute -->

                            <DataTableCell>

                                <div
                                    class="
                                        font-medium
                                        text-gray-900
                                    "
                                >
                                    {{
                                        item.attribute
                                            ?.display_name
                                        ??
                                        item.attribute
                                            ?.name
                                        ??
                                        '-'
                                    }}
                                </div>

                            </DataTableCell>


                            <!-- Value -->

                            <DataTableCell>

                                <span
                                    class="
                                        inline-flex
                                        items-center
                                        rounded-md
                                        bg-slate-50
                                        px-2.5
                                        py-1
                                        text-sm
                                        font-medium
                                        text-slate-700
                                    "
                                >
                                    {{
                                        item.attribute_value
                                            ?.display_value
                                        ??
                                        item.attribute_Value
                                            ?.value
                                        ??
                                        '-'
                                    }}
                                </span>

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
                    py-8
                    text-center
                    text-sm
                    text-gray-500
                "
            >
                No variant attributes.
            </div>

        </div>

    </div>

</AppLayout>

</template>