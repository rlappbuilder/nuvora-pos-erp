<script setup>

import FormField from '@/Components/Form/FormField.vue'
import FormInput from '@/Components/Form/FormInput.vue'
import FormTextarea from '@/Components/Form/FormTextarea.vue'
import BaseButton from '@/Components/Button/BaseButton.vue'
import SearchableSelect from '@/Components/Form/SearchableSelect.vue'

import {
    formatDate,
} from '@/Utils'

import {
    PlusIcon,
    TrashIcon,
} from '@heroicons/vue/24/outline'

import FlatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'

import Swal from 'sweetalert2'

import {
    computed,
    onMounted,
    watch,
} from 'vue'


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    form: {
        type: Object,
        required: true,
    },

    branches: {
        type: Array,
        default: () => [],
    },

    warehouses: {
        type: Array,
        default: () => [],
    },

    resellers: {
        type: Array,
        default: () => [],
    },

    consignmentStocks: {
        type: Array,
        default: () => [],
    },

    settlements: {
        type: Array,
        default: () => [],
    },

})


const form = props.form


/*
|--------------------------------------------------------------------------
| Selected Reseller
|--------------------------------------------------------------------------
*/

const selectedReseller = computed(() => {

    return props.resellers.find(

        reseller =>

            Number(
                reseller.id
            ) ===
            Number(
                form.reseller_id
            )

    ) ?? null

})


/*
|--------------------------------------------------------------------------
| Emits
|--------------------------------------------------------------------------
*/

const emit = defineEmits([
    'submit',
    'cancel',
])


/*
|--------------------------------------------------------------------------
| Date Configuration
|--------------------------------------------------------------------------
*/

const dateConfig = {

    dateFormat:
        'Y-m-d',

    allowInput:
        true,

}


/*
|--------------------------------------------------------------------------
| Default Date
|--------------------------------------------------------------------------
*/

const getToday = () => {

    const date =
        new Date()


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


/*
|--------------------------------------------------------------------------
| Initialize Date
|--------------------------------------------------------------------------
*/

onMounted(() => {

    if (
        !form.return_date
    ) {

        form.return_date =
            getToday()

    }

})


/*
|--------------------------------------------------------------------------
| Empty Detail
|--------------------------------------------------------------------------
*/

const createEmptyDetail = () => ({

    product_variant_id:
        null,

    unit_id:
        null,

    returned_qty:
        0,

    unit_cost:
        0,

    total_cost:
        0,

    remarks:
        '',

})


/*
|--------------------------------------------------------------------------
| Add Detail
|--------------------------------------------------------------------------
*/

const addDetail = () => {

    form.details.push(
        createEmptyDetail()
    )

}


/*
|--------------------------------------------------------------------------
| Remove Detail
|--------------------------------------------------------------------------
*/

const removeDetail = (
    index
) => {

    if (
        form.details.length <= 1
    ) {

        return

    }


    form.details.splice(
        index,
        1
    )

}


/*
|--------------------------------------------------------------------------
| Available Stocks
|--------------------------------------------------------------------------
*/

const availableStocks = (
    currentDetail
) => {

    if (
        !form.reseller_id ||
        !form.branch_id ||
        !form.warehouse_id
    ) {

        return []

    }


    const currentProductId =
        Number(
            currentDetail.product_variant_id
        )


    const selectedIds =
        form.details
            .map(
                detail =>
                    Number(
                        detail.product_variant_id
                    )
            )
            .filter(
                id =>
                    id > 0 &&
                    id !== currentProductId
            )


    return props.consignmentStocks.filter(

        stock => {

            /*
            |--------------------------------------------------------------------------
            | Reseller
            |--------------------------------------------------------------------------
            */

            if (
                Number(
                    stock.reseller_id
                ) !==
                Number(
                    form.reseller_id
                )
            ) {

                return false

            }


            /*
            |--------------------------------------------------------------------------
            | Branch
            |--------------------------------------------------------------------------
            */

            if (
                Number(
                    stock.branch_id
                ) !==
                Number(
                    form.branch_id
                )
            ) {

                return false

            }


            /*
            |--------------------------------------------------------------------------
            | Warehouse
            |--------------------------------------------------------------------------
            */

            if (
                Number(
                    stock.warehouse_id
                ) !==
                Number(
                    form.warehouse_id
                )
            ) {

                return false

            }


            /*
            |--------------------------------------------------------------------------
            | Available Stock
            |--------------------------------------------------------------------------
            */

            if (
                Number(
                    stock.available_qty || 0
                ) <= 0
            ) {

                return false

            }


            /*
            |--------------------------------------------------------------------------
            | Current Product
            |--------------------------------------------------------------------------
            */

            if (
                Number(
                    stock.product_variant_id
                ) ===
                currentProductId
            ) {

                return true

            }


            /*
            |--------------------------------------------------------------------------
            | Prevent Duplicate Product
            |--------------------------------------------------------------------------
            */

            return !selectedIds.includes(
                Number(
                    stock.product_variant_id
                )
            )

        }

    )

}


/*
|--------------------------------------------------------------------------
| Available Settlements
|--------------------------------------------------------------------------
*/

const availableSettlements = computed(() => {

    if (
        !form.reseller_id ||
        !form.branch_id
    ) {

        return []

    }


    return props.settlements.filter(

        settlement =>

            Number(
                settlement.reseller_id
            ) ===
            Number(
                form.reseller_id
            )

            &&

            Number(
                settlement.branch_id
            ) ===
            Number(
                form.branch_id
            )

    )

})


/*
|--------------------------------------------------------------------------
| Has Available Product
|--------------------------------------------------------------------------
*/

const hasAvailableProduct = computed(() => {

    if (
        !form.reseller_id ||
        !form.branch_id ||
        !form.warehouse_id
    ) {

        return false

    }


    return props.consignmentStocks.some(

        stock =>

            Number(
                stock.reseller_id
            ) ===
            Number(
                form.reseller_id
            )

            &&

            Number(
                stock.branch_id
            ) ===
            Number(
                form.branch_id
            )

            &&

            Number(
                stock.warehouse_id
            ) ===
            Number(
                form.warehouse_id
            )

            &&

            Number(
                stock.available_qty || 0
            ) > 0

    )

})


/*
|--------------------------------------------------------------------------
| Context Changed
|--------------------------------------------------------------------------
*/

watch(

    [
        () => form.reseller_id,
        () => form.branch_id,
        () => form.warehouse_id,
    ],

    (
        newValues,
        oldValues
    ) => {

        if (
            JSON.stringify(newValues) ===
            JSON.stringify(oldValues)
        ) {

            return

        }


        /*
        |--------------------------------------------------------------------------
        | Reset Details
        |--------------------------------------------------------------------------
        */

        form.details = [
            createEmptyDetail()
        ]


        /*
        |--------------------------------------------------------------------------
        | Reset Settlement
        |--------------------------------------------------------------------------
        */

        form.settlement_header_id =
            null


        /*
        |--------------------------------------------------------------------------
        | Context Incomplete
        |--------------------------------------------------------------------------
        */

        if (
            !form.reseller_id ||
            !form.branch_id ||
            !form.warehouse_id
        ) {

            return

        }


        /*
        |--------------------------------------------------------------------------
        | No Available Product
        |--------------------------------------------------------------------------
        */

        if (
            !hasAvailableProduct.value
        ) {

            Swal.fire({

                icon:
                    'info',

                title:
                    'Reseller Has No Product',

                text:
                    'This reseller has no consignment product available for return in the selected branch and warehouse.',

                confirmButtonText:
                    'OK',

                buttonsStyling:
                    false,

                customClass: {

                    popup:
                        'rounded-2xl',

                    confirmButton:
                        'rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-medium text-white',

                },

            })

        }

    }

)


/*
|--------------------------------------------------------------------------
| Get Stock
|--------------------------------------------------------------------------
*/

const getStock = (
    productVariantId
) => {

    return props.consignmentStocks.find(

        stock =>

            Number(
                stock.product_variant_id
            ) ===
            Number(
                productVariantId
            )

            &&

            Number(
                stock.reseller_id
            ) ===
            Number(
                form.reseller_id
            )

            &&

            Number(
                stock.branch_id
            ) ===
            Number(
                form.branch_id
            )

            &&

            Number(
                stock.warehouse_id
            ) ===
            Number(
                form.warehouse_id
            )

    ) ?? null

}


/*
|--------------------------------------------------------------------------
| Product Changed
|--------------------------------------------------------------------------
*/

const changeProduct = (
    detail
) => {

    if (
        !detail.product_variant_id
    ) {

        detail.unit_id =
            null

        detail.returned_qty =
            0

        detail.unit_cost =
            0

        detail.total_cost =
            0

        return

    }


    /*
    |--------------------------------------------------------------------------
    | Duplicate Product
    |--------------------------------------------------------------------------
    */

    const duplicate =
        form.details.some(

            item =>

                item !== detail

                &&

                Number(
                    item.product_variant_id
                ) ===
                Number(
                    detail.product_variant_id
                )

        )


    if (
        duplicate
    ) {

        Swal.fire({

            icon:
                'warning',

            title:
                'Product Already Exist',

            text:
                'This product has already been added to the return.',

            confirmButtonText:
                'OK',

            buttonsStyling:
                false,

            customClass: {

                popup:
                    'rounded-2xl',

                confirmButton:
                    'rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-medium text-white',

            },

        }).then(() => {

            const index =
                form.details.indexOf(
                    detail
                )


            if (
                index !== -1
            ) {

                form.details.splice(
                    index,
                    1
                )

            }

        })


        return

    }


    const stock =
        getStock(
            detail.product_variant_id
        )


    if (
        !stock
    ) {

        detail.product_variant_id =
            null

        detail.unit_id =
            null

        detail.returned_qty =
            0

        detail.unit_cost =
            0

        detail.total_cost =
            0

        return

    }


    /*
    |--------------------------------------------------------------------------
    | Snapshot Stock
    |--------------------------------------------------------------------------
    */

    detail.unit_id =
        stock.unit_id


    detail.unit_cost =
        Number(
            stock.average_cost || 0
        )


    detail.returned_qty =
        Number(
            detail.returned_qty || 0
        )


    calculateDetail(
        detail
    )

}


/*
|--------------------------------------------------------------------------
| Calculate Detail
|--------------------------------------------------------------------------
*/

const calculateDetail = (
    detail
) => {

    let qty =
        Number(
            detail.returned_qty || 0
        )


    if (
        qty < 0
    ) {

        qty = 0

    }


    detail.returned_qty =
        qty


    const cost =
        Number(
            detail.unit_cost || 0
        )


    detail.total_cost =
        qty * cost

}


/*
|--------------------------------------------------------------------------
| Get Product Label
|--------------------------------------------------------------------------
*/

const getProductLabel = (
    stock
) => {

    if (
        stock.label
    ) {

        return stock.label

    }


    if (
        stock.product_name
    ) {

        return stock.product_variant_name
            ? `${stock.product_name} - ${stock.product_variant_name}`
            : stock.product_name

    }


    if (
        stock.variant_name
    ) {

        return stock.variant_name

    }


    return '-'

}


/*
|--------------------------------------------------------------------------
| Get Unit Label
|--------------------------------------------------------------------------
*/

const getUnitLabel = (
    detail
) => {

    const stock =
        getStock(
            detail.product_variant_id
        )


    return (
        stock?.unit?.name ??
        '-'
    )

}


/*
|--------------------------------------------------------------------------
| Available Quantity
|--------------------------------------------------------------------------
*/

const getAvailableQty = (
    detail
) => {

    const stock =
        getStock(
            detail.product_variant_id
        )


    return Number(
        stock?.available_qty || 0
    )

}


/*
|--------------------------------------------------------------------------
| Can Add Detail
|--------------------------------------------------------------------------
*/

const canAddDetail = computed(() => {

    if (
        !form.reseller_id ||
        !form.branch_id ||
        !form.warehouse_id
    ) {

        return false

    }


    const products =
        props.consignmentStocks.filter(

            stock =>

                Number(
                    stock.reseller_id
                ) ===
                Number(
                    form.reseller_id
                )

                &&

                Number(
                    stock.branch_id
                ) ===
                Number(
                    form.branch_id
                )

                &&

                Number(
                    stock.warehouse_id
                ) ===
                Number(
                    form.warehouse_id
                )

                &&

                Number(
                    stock.available_qty || 0
                ) > 0

        )


    const uniqueProductIds =
        [
            ...new Set(
                products.map(
                    stock =>
                        Number(
                            stock.product_variant_id
                        )
                )
            ),
        ]


    const selectedIds =
        form.details
            .map(
                detail =>
                    Number(
                        detail.product_variant_id
                    )
            )
            .filter(
                id =>
                    id > 0
            )


    const remainingProducts =
        uniqueProductIds.filter(

            productId =>
                !selectedIds.includes(
                    productId
                )

        )


    return (
        uniqueProductIds.length > 1
        &&
        remainingProducts.length > 0
    )

})


/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

const totalItems =
    computed(() => {

        return form.details.length

    })


const totalReturned =
    computed(() => {

        return form.details.reduce(

            (
                total,
                detail
            ) =>

                total +
                Number(
                    detail.returned_qty || 0
                ),

            0

        )

    })


const totalCost =
    computed(() => {

        return form.details.reduce(

            (
                total,
                detail
            ) =>

                total +
                Number(
                    detail.total_cost || 0
                ),

            0

        )

    })


/*
|--------------------------------------------------------------------------
| Quantity Validation
|--------------------------------------------------------------------------
*/

const quantityExceedsStock =
    computed(() => {

        return form.details.some(

            detail => {

                const qty =
                    Number(
                        detail.returned_qty || 0
                    )


                const available =
                    getAvailableQty(
                        detail
                    )


                return (
                    qty > available
                )

            }

        )

    })


/*
|--------------------------------------------------------------------------
| Currency
|--------------------------------------------------------------------------
*/

const formatCurrency = (
    value
) => {

    return new Intl.NumberFormat(
        'id-ID',
        {
            style:
                'currency',

            currency:
                'IDR',

            minimumFractionDigits:
                0,

            maximumFractionDigits:
                0,
        }
    ).format(
        Number(
            value || 0
        )
    )

}


/*
|--------------------------------------------------------------------------
| Number
|--------------------------------------------------------------------------
*/

const formatNumber = (
    value
) => {

    return new Intl.NumberFormat(
        'id-ID',
        {
            minimumFractionDigits:
                0,

            maximumFractionDigits:
                2,
        }
    ).format(
        Number(
            value || 0
        )
    )

}


/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/

const handleSubmit = () => {

    if (
        quantityExceedsStock.value
    ) {

        return

    }


    emit('submit')

}

</script>
<template>

    <form
        @submit.prevent="handleSubmit"
        class="space-y-6"
    >

        <!-- ========================================================= -->
        <!-- Header -->
        <!-- ========================================================= -->

        <div
            class="
                rounded-xl
                border
                border-gray-200
                bg-white
                px-5
                py-4
            "
        >

            <div
                class="
                    flex
                    flex-col
                    gap-1
                    sm:flex-row
                    sm:items-center
                    sm:justify-between
                "
            >

                <div>

                    <h1
                        class="
                            text-lg
                            font-semibold
                            tracking-tight
                            text-gray-900
                        "
                    >
                        Consignment Return
                    </h1>

                    <p
                        class="
                            text-sm
                            text-gray-500
                        "
                    >
                        Record goods returned from reseller consignment stock.
                    </p>

                </div>


                <div
                    class="
                        text-left
                        sm:text-right
                    "
                >

                    <div
                        class="
                            text-[11px]
                            font-medium
                            uppercase
                            tracking-wider
                            text-gray-400
                        "
                    >
                        Return No.
                    </div>

                    <div
                        class="
                            mt-0.5
                            text-base
                            font-semibold
                            text-gray-800
                        "
                    >
                        {{ form.return_number || 'Auto Generated' }}
                    </div>

                </div>

            </div>

        </div>


        <!-- ========================================================= -->
        <!-- Transaction Information -->
        <!-- ========================================================= -->

        <div
            class="
                rounded-xl
                border
                border-gray-200
                bg-white
                p-5
            "
        >

            <div
                class="
                    grid
                    grid-cols-1
                    gap-x-8
                    gap-y-5
                    lg:grid-cols-3
                "
            >

                <!-- ===================================================== -->
                <!-- Reseller -->
                <!-- ===================================================== -->

                <FormField
                    label="Reseller"
                    required
                    :error="
                        form.errors.reseller_id
                    "
                >

                    <SearchableSelect
                        v-model="
                            form.reseller_id
                        "
                        :options="
                            resellers
                        "
                        label="label"
                        value-key="id"
                        placeholder="Select reseller"
                    />

                </FormField>


                <!-- ===================================================== -->
                <!-- Contact Person -->
                <!-- ===================================================== -->

                <FormField
                    label="Contact Person"
                >

                    <div
                        class="
                            flex
                            min-h-[38px]
                            items-center
                            rounded-lg
                            border
                            border-gray-200
                            bg-gray-50
                            px-3
                            py-2
                            text-sm
                            text-gray-700
                        "
                    >

                        {{
                            selectedReseller?.contact_person
                            || '-'
                        }}

                    </div>

                </FormField>


                <!-- ===================================================== -->
                <!-- Branch -->
                <!-- ===================================================== -->

                <FormField
                    label="Branch"
                    required
                    :error="
                        form.errors.branch_id
                    "
                >

                    <SearchableSelect
                        v-model="
                            form.branch_id
                        "
                        :options="
                            branches
                        "
                        label="label"
                        value-key="id"
                        placeholder="Select branch"
                    />

                </FormField>


                <!-- ===================================================== -->
                <!-- Warehouse -->
                <!-- ===================================================== -->

                <FormField
                    label="Warehouse"
                    required
                    :error="
                        form.errors.warehouse_id
                    "
                >

                    <SearchableSelect
                        v-model="
                            form.warehouse_id
                        "
                        :options="
                            warehouses
                        "
                        label="label"
                        value-key="id"
                        placeholder="Select warehouse"
                    />

                </FormField>


                <!-- ===================================================== -->
                <!-- Return Date -->
                <!-- ===================================================== -->

                <FormField
                    label="Return Date"
                    required
                    :error="
                        form.errors.return_date
                    "
                >

                    <FlatPickr
                        v-model="
                            form.return_date
                        "
                        :config="
                            dateConfig
                        "
                        class="
                            w-full
                            rounded-lg
                            border
                            border-gray-300
                            bg-white
                            px-3
                            py-2
                            text-sm
                            text-gray-800
                            outline-none
                            transition
                            focus:border-blue-500
                            focus:ring-1
                            focus:ring-blue-500
                        "
                    />

                </FormField>


                <!-- ===================================================== -->
                <!-- Settlement -->
                <!-- ===================================================== -->

                <FormField
                    label="Settlement"
                    :error="
                        form.errors.settlement_header_id
                    "
                >

                   <SearchableSelect
                        v-model="
                            form.settlement_header_id
                        "
                        :options="
                            availableSettlements
                        "
                        label="label"
                        value-key="id"
                        placeholder="Select settlement (optional)"
                    />
                </FormField>

            </div>

        </div>


        <!-- ========================================================= -->
        <!-- Return Details -->
        <!-- ========================================================= -->

        <div
            class="
                rounded-xl
                border
                border-gray-200
                bg-white
                p-5
            "
        >

            <div
                class="
                    mb-4
                    flex
                    flex-col
                    gap-1
                    sm:flex-row
                    sm:items-center
                    sm:justify-between
                "
            >

                <div>

                    <h2
                        class="
                            text-sm
                            font-semibold
                            text-gray-900
                        "
                    >
                        Return Details
                    </h2>

                    <p
                        class="
                            text-xs
                            text-gray-500
                        "
                    >
                        Select products from reseller consignment stock and enter the returned quantity.
                    </p>

                </div>


                <div
                    class="
                        text-xs
                        text-gray-500
                    "
                >

                    {{ totalItems }} items

                </div>

            </div>


            <!-- No reseller -->

            <div
                v-if="
                    !form.reseller_id
                "
                class="
                    rounded-lg
                    border
                    border-dashed
                    border-gray-300
                    bg-gray-50
                    px-5
                    py-10
                    text-center
                "
            >

                <div
                    class="
                        text-sm
                        font-medium
                        text-gray-600
                    "
                >
                    Select a reseller first
                </div>

                <div
                    class="
                        mt-1
                        text-xs
                        text-gray-400
                    "
                >
                    Available consignment stock will appear here.
                </div>

            </div>


            <!-- Details -->

            <div
                v-else
                class="
                    overflow-x-auto
                    rounded-lg
                    border
                    border-gray-200
                "
            >

                <div
                    class="
                        min-w-[1080px]
                    "
                >

                    <!-- Table Header -->

                    <div
                        class="
                            grid
                            grid-cols-[minmax(250px,2fr)_110px_150px_150px_150px_55px]
                            items-center
                            gap-2
                            border-b
                            border-gray-200
                            bg-gray-50
                            px-3
                            py-2.5
                            text-[11px]
                            font-semibold
                            uppercase
                            tracking-wide
                            text-gray-500
                        "
                    >

                        <div>Product</div>

                        <div>Unit</div>

                        <div class="text-right">
                            Available
                        </div>

                        <div class="text-right">
                            Unit Cost
                        </div>

                        <div class="text-right">
                            Returned Qty
                        </div>

                        <div></div>

                    </div>


                    <!-- Rows -->

                    <div
                        v-for="(
                            detail,
                            index
                        ) in form.details"

                        :key="index"

                        class="
                            grid
                            grid-cols-[minmax(250px,2fr)_110px_150px_150px_150px_55px]
                            items-start
                            gap-2
                            border-b
                            border-gray-100
                            px-3
                            py-3
                            last:border-b-0
                        "
                    >

                        <!-- Product -->

                        <FormField
                            label="Product"
                            required
                            :error="
                                form.errors[
                                    `details.${index}.product_variant_id`
                                ]
                            "
                        >

                            <SearchableSelect
                                v-model="
                                    detail.product_variant_id
                                "
                                :options="
                                    availableStocks(detail)
                                "
                                label="label"
                                value-key="product_variant_id"
                                placeholder="Select product"
                                @update:modelValue="
                                    changeProduct(detail)
                                "
                            />

                        </FormField>


                        <!-- Unit -->

                        <FormField
                            label="Unit"
                        >

                            <div
                                class="
                                    flex
                                    min-h-[38px]
                                    items-center
                                    rounded-lg
                                    border
                                    border-gray-200
                                    bg-gray-50
                                    px-2
                                    py-1.5
                                    text-sm
                                    text-gray-700
                                "
                            >

                                {{
                                    getUnitLabel(
                                        detail
                                    )
                                }}

                            </div>

                        </FormField>


                        <!-- Available -->

                        <FormField
                            label="Available"
                        >

                            <div
                                class="
                                    flex
                                    min-h-[38px]
                                    items-center
                                    justify-end
                                    rounded-lg
                                    border
                                    border-gray-200
                                    bg-gray-50
                                    px-2
                                    text-sm
                                    tabular-nums
                                    text-gray-700
                                "
                            >

                                {{
                                    formatNumber(
                                        getAvailableQty(
                                            detail
                                        )
                                    )
                                }}

                            </div>

                        </FormField>


                        <!-- Unit Cost -->

                        <FormField
                            label="Unit Cost"
                        >

                            <div
                                class="
                                    flex
                                    min-h-[38px]
                                    items-center
                                    justify-end
                                    rounded-lg
                                    border
                                    border-gray-200
                                    bg-gray-50
                                    px-2
                                    text-sm
                                    tabular-nums
                                    text-gray-700
                                "
                            >

                                {{
                                    formatCurrency(
                                        detail.unit_cost
                                    )
                                }}

                            </div>

                        </FormField>


                        <!-- Returned Qty -->

                        <FormField
                            label="Returned Qty"
                            required
                            :error="
                                form.errors[
                                    `details.${index}.returned_qty`
                                ]
                            "
                        >

                            <FormInput
                                v-model="
                                    detail.returned_qty
                                "
                                type="number"
                                min="0"
                                step="0.01"
                                placeholder="0"
                                @input="
                                    calculateDetail(detail)
                                "
                            />

                        </FormField>


                        <!-- Remove -->

                        <div
                            class="
                                flex
                                min-h-[38px]
                                items-center
                                justify-center
                            "
                        >

                            <button
                                v-if="
                                    form.details.length > 1
                                "
                                type="button"
                                class="
                                    inline-flex
                                    h-9
                                    w-9
                                    items-center
                                    justify-center
                                    rounded-lg
                                    text-gray-400
                                    transition
                                    hover:bg-red-50
                                    hover:text-red-600
                                    focus:outline-none
                                    focus:ring-2
                                    focus:ring-red-500
                                "
                                title="Remove item"
                                @click="
                                    removeDetail(index)
                                "
                            >

                                <TrashIcon
                                    class="h-4.5 w-4.5"
                                />

                            </button>

                        </div>

                    </div>

                </div>

            </div>


            <!-- Add Item -->

            <div
                v-if="
                    canAddDetail
                "
                class="
                    mt-4
                    flex
                    justify-start
                "
            >

                <BaseButton
                    type="button"
                    variant="secondary"
                    @click="addDetail"
                >

                    <PlusIcon
                        class="mr-1.5 h-4 w-4"
                    />

                    Add Item

                </BaseButton>

            </div>

        </div>


        <!-- ========================================================= -->
        <!-- Bottom -->
        <!-- ========================================================= -->

        <div
            class="
                grid
                grid-cols-1
                gap-6
                lg:grid-cols-[1fr_380px]
            "
        >

            <!-- Remarks -->

            <div
                class="
                    rounded-xl
                    border
                    border-gray-200
                    bg-white
                    p-5
                "
            >

                <h2
                    class="
                        mb-3
                        text-sm
                        font-semibold
                        text-gray-900
                    "
                >
                    Remarks
                </h2>


                <FormTextarea
                    v-model="
                        form.remarks
                    "
                    :rows="5"
                    placeholder="Add remarks or notes..."
                />


                <p
                    v-if="
                        form.errors.remarks
                    "
                    class="
                        mt-1
                        text-xs
                        text-red-600
                    "
                >

                    {{ form.errors.remarks }}

                </p>

            </div>


            <!-- Return Summary -->

            <div
                class="
                    rounded-xl
                    border
                    border-gray-200
                    bg-white
                    p-5
                "
            >

                <h2
                    class="
                        mb-4
                        text-sm
                        font-semibold
                        text-gray-900
                    "
                >
                    Return Summary
                </h2>


                <!-- Items -->

                <div
                    class="
                        flex
                        justify-between
                        py-1.5
                        text-sm
                    "
                >

                    <span class="text-gray-500">
                        Total Items
                    </span>

                    <span
                        class="
                            font-medium
                            text-gray-800
                        "
                    >

                        {{ totalItems }}

                    </span>

                </div>


                <!-- Returned Qty -->

                <div
                    class="
                        flex
                        justify-between
                        py-1.5
                        text-sm
                    "
                >

                    <span class="text-gray-500">
                        Total Returned
                    </span>

                    <span
                        class="
                            font-medium
                            text-gray-800
                        "
                    >

                        {{ formatNumber(
                            totalReturned
                        ) }}

                    </span>

                </div>


                <!-- Total Cost -->

                <div
                    class="
                        mt-2
                        flex
                        justify-between
                        border-t
                        border-gray-100
                        pt-4
                    "
                >

                    <span
                        class="
                            text-sm
                            font-medium
                            text-gray-600
                        "
                    >
                        Total Cost
                    </span>

                    <span
                        class="
                            text-lg
                            font-semibold
                            text-gray-900
                        "
                    >

                        {{ formatCurrency(
                            totalCost
                        ) }}

                    </span>

                </div>


                <!-- Validation -->

                <div
                    v-if="
                        quantityExceedsStock
                    "
                    class="
                        mt-4
                        rounded-lg
                        border
                        border-red-200
                        bg-red-50
                        px-3
                        py-2
                        text-xs
                        text-red-600
                    "
                >

                    Returned quantity cannot be greater
                    than the available consignment stock.

                </div>


                <!-- Error -->

                <div
                    v-if="
                        form.errors.details
                    "
                    class="
                        mt-4
                        rounded-lg
                        border
                        border-red-200
                        bg-red-50
                        px-3
                        py-2
                        text-xs
                        text-red-600
                    "
                >

                    {{ form.errors.details }}

                </div>

            </div>

        </div>


        <!-- ========================================================= -->
        <!-- Actions -->
        <!-- ========================================================= -->

        <div
            class="
                flex
                flex-col-reverse
                gap-3
                border-t
                border-gray-200
                pt-5
                sm:flex-row
                sm:justify-end
            "
        >

            <BaseButton
                type="button"
                variant="secondary"
                :disabled="
                    form.processing
                "
                @click="
                    emit('cancel')
                "
            >

                Cancel

            </BaseButton>


            <BaseButton
                type="submit"
                :loading="
                    form.processing
                "
                :disabled="
                    quantityExceedsStock ||
                    !form.reseller_id ||
                    !form.branch_id ||
                    !form.warehouse_id ||
                    !form.return_date
                "
            >

                Save

            </BaseButton>

        </div>

    </form>

</template>