<script setup>

import FormSection from '@/Components/Form/FormSection.vue'
import FormField from '@/Components/Form/FormField.vue'
import FormInput from '@/Components/Form/FormInput.vue'
import FormTextarea from '@/Components/Form/FormTextarea.vue'
import BaseButton from '@/Components/Button/BaseButton.vue'
import SearchableSelect from '@/Components/Form/SearchableSelect.vue'

import {
    TrashIcon,
} from '@heroicons/vue/24/outline'

import FlatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'

import Swal from 'sweetalert2'

import {
    computed,
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

    filteredWarehouses: {
        type: Array,
        default: () => [],
    },

    resellers: {
        type: Array,
        default: () => [],
    },

    resellerPrices: {
        type: Array,
        default: () => [],
    },

    filteredVariants: {
        type: Array,
        default: () => [],
    },

    mode: {
        type: String,
        default: 'create',
    },

})


const form = props.form


/*
|--------------------------------------------------------------------------
| Emits
|--------------------------------------------------------------------------
*/

const emit = defineEmits([
    'submit',
    'submitAndNew',
    'cancel',
])


/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/

const handleSubmit = () => {

    emit('submit')

}


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

    qty:
        1,

    unit_price:
        0,

    total_price:
        0,

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
| Variant Units
|--------------------------------------------------------------------------
*/

const getUnitsForVariant = (
    variantId
) => {

    if (
        !variantId
    ) {

        return []

    }


    const variant =
        availableVariants.value.find(

            item =>

                Number(
                    item.id
                ) ===
                Number(
                    variantId
                )

        )


    return variant?.units ?? []

}


/*
|--------------------------------------------------------------------------
| Available Variants
|--------------------------------------------------------------------------
*/

const availableVariants = computed(() => {

    if (
        !form.reseller_id
    ) {

        return []

    }


    const productIds =
        props.resellerPrices
            .filter(

                price =>

                    Number(
                        price.reseller_id
                    ) ===
                    Number(
                        form.reseller_id
                    )

            )
            .map(

                price =>

                    Number(
                        price.product_id
                    )

            )


    return props.filteredVariants.filter(

        variant =>

            productIds.includes(
                Number(
                    variant.product_id
                )
            )

    )

})


/*
|--------------------------------------------------------------------------
| Variant Changed
|--------------------------------------------------------------------------
*/

const changeVariant = (
    detail
) => {

    if (
        !detail.product_variant_id
    ) {

        detail.unit_id =
            null

        detail.unit_price =
            0

        calculateDetail(
            detail
        )

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
                'This product has already been added to the consignment out.',

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


    detail.unit_id =
        null


    const variant =
        availableVariants.value.find(

            item =>

                Number(
                    item.id
                ) ===
                Number(
                    detail.product_variant_id
                )

        )


    const units =
        variant?.units ?? []


    const defaultUnit =
        units.find(
            unit =>
                unit.is_default
        )


    if (
        defaultUnit
    ) {

        detail.unit_id =
            defaultUnit.id

    }


    const resellerPrice =
        props.resellerPrices.find(

            price =>

                Number(
                    price.reseller_id
                ) ===
                Number(
                    form.reseller_id
                )

                &&

                Number(
                    price.product_id
                ) ===
                Number(
                    variant?.product_id
                )

        )


    detail.unit_price =
        Math.max(
            0,
            Number(
                resellerPrice?.price || 0
            )
        )


    /*
    |--------------------------------------------------------------------------
    | Ensure Valid Quantity
    |--------------------------------------------------------------------------
    */

    const qty =
        Number(
            detail.qty
        )


    if (
        !Number.isInteger(qty) ||
        qty < 1
    ) {

        detail.qty =
            1

    }


    calculateDetail(
        detail
    )

}


/*
|--------------------------------------------------------------------------
| Validate Quantity
|--------------------------------------------------------------------------
*/

const validateQuantity = (
    detail
) => {

    const qty =
        Number(
            detail.qty
        )


    /*
    |--------------------------------------------------------------------------
    | Empty / Zero / Negative / Decimal
    |--------------------------------------------------------------------------
    */

    if (
        !Number.isInteger(qty) ||
        qty < 1
    ) {

        Swal.fire({

            icon:
                'warning',

            title:
                'Invalid Quantity',

            text:
                'Quantity must be a positive whole number starting from 1.',

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

            detail.qty =
                1

            calculateDetail(
                detail
            )

        })


        return false

    }


    detail.qty =
        qty


    calculateDetail(
        detail
    )


    return true

}


/*
|--------------------------------------------------------------------------
| Validate Consignment Price
|--------------------------------------------------------------------------
*/

const validateUnitPrice = (
    detail
) => {

    let price =
        Number(
            detail.unit_price
        )


    if (
        Number.isNaN(price) ||
        price < 0
    ) {

        Swal.fire({

            icon:
                'warning',

            title:
                'Invalid Consignment Price',

            text:
                'Consignment price cannot be negative.',

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

            detail.unit_price =
                0

            calculateDetail(
                detail
            )

        })


        return false

    }


    detail.unit_price =
        price


    calculateDetail(
        detail
    )


    return true

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
            detail.qty || 0
        )


    let unitPrice =
        Number(
            detail.unit_price || 0
        )


    /*
    |--------------------------------------------------------------------------
    | Quantity Safety
    |--------------------------------------------------------------------------
    */

    if (
        !Number.isInteger(qty) ||
        qty < 1
    ) {

        qty =
            1

        detail.qty =
            qty

    }


    /*
    |--------------------------------------------------------------------------
    | Price Safety
    |--------------------------------------------------------------------------
    */

    if (
        Number.isNaN(unitPrice) ||
        unitPrice < 0
    ) {

        unitPrice =
            0

        detail.unit_price =
            unitPrice

    }


    detail.total_price =
        Number(
            (
                qty *
                unitPrice
            ).toFixed(2)
        )

}


/*
|--------------------------------------------------------------------------
| Gross Amount
|--------------------------------------------------------------------------
*/

const getDetailTotal = (
    detail
) => {

    return (
        Number(
            detail.qty || 0
        ) *
        Number(
            detail.unit_price || 0
        )
    )

}


/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

const totalItems =
    computed(() => {

        return form.details.length

    })


const totalQuantity =
    computed(() => {

        return form.details.reduce(

            (
                total,
                detail
            ) =>

                total +
                Number(
                    detail.qty || 0
                ),

            0

        )

    })


const totalValue =
    computed(() => {

        return form.details.reduce(

            (
                total,
                detail
            ) =>

                total +
                getDetailTotal(
                    detail
                ),

            0

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

</script>

<template>

    <form
        @submit.prevent="handleSubmit"
    >

        <!-- ========================================================= -->
        <!-- Consignment Out Information -->
        <!-- ========================================================= -->

        <FormSection
            icon="📋"
            title="Consignment Out Information"
            :columns="2"
        >

            <!-- Number -->

            <FormField
                label="Number"
                :error="
                    form.errors.consignment_out_number
                "
            >

                <FormInput
                    v-model="
                        form.consignment_out_number
                    "
                    readonly
                    placeholder="Auto generated"
                />

            </FormField>


            <!-- Transaction Date -->

            <FormField
                label="Transaction Date"
                required
                :error="
                    form.errors.transaction_date
                "
            >

                <FlatPickr
                    v-model="
                        form.transaction_date
                    "
                    :config="{
                        dateFormat: 'Y-m-d',
                        allowInput: true,
                    }"
                    class="
                        w-full
                        rounded-lg
                        border
                        px-3
                        py-2
                    "
                />

            </FormField>


            <!-- Reseller -->

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
                    :options="resellers"
                    label="label"
                    value-key="id"
                    placeholder="Select Reseller"
                />

            </FormField>


            <!-- Branch -->

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
                    :options="branches"
                    label="label"
                    value-key="id"
                    placeholder="Select Branch"
                />

            </FormField>


            <!-- Warehouse -->

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
                        filteredWarehouses
                    "
                    label="label"
                    value-key="id"
                    placeholder="Select Warehouse"
                />

            </FormField>


            <!-- Reference Number -->

            <FormField
                label="Reference Number"
                :error="
                    form.errors.reference_number
                "
            >

                <FormInput
                    v-model="
                        form.reference_number
                    "
                    placeholder="Optional reference number"
                />

            </FormField>

        </FormSection>


        <!-- ========================================================= -->
        <!-- Order Details -->
        <!-- ========================================================= -->

        <FormSection
            icon="📦"
            title="Order Details"
            description="Add products, quantities and consignment prices."
            :columns="1"
        >

            <div class="space-y-4">


                <!-- ================================================= -->
                <!-- Details Table -->
                <!-- ================================================= -->

                <div
                    class="
                        overflow-x-auto
                        rounded-xl
                        border
                        border-gray-200
                    "
                >

                    <div
                        class="
                            min-w-[850px]
                            p-3
                        "
                    >

                        <!-- ========================================= -->
                        <!-- Table Header -->
                        <!-- ========================================= -->

                        <div
                            class="
                                grid
                                grid-cols-[minmax(280px,2fr)_110px_100px_170px_170px_70px]
                                items-center
                                gap-2
                                border-b
                                border-gray-200
                                px-2
                                pb-2
                                text-xs
                                font-semibold
                                text-gray-500
                            "
                        >

                            <div>
                                Product Variant
                            </div>

                            <div>
                                Unit
                            </div>

                            <div>
                                Qty
                            </div>

                            <div>
                                Consignment Price
                            </div>

                            <div class="text-right">
                                Total
                            </div>

                            <div class="text-center">
                                Action
                            </div>

                        </div>


                        <!-- ========================================= -->
                        <!-- Detail Rows -->
                        <!-- ========================================= -->

                        <div
                            v-for="(
                                detail,
                                index
                            ) in form.details"

                            :key="index"

                            class="
                                grid
                                grid-cols-[minmax(280px,2fr)_110px_100px_170px_170px_70px]
                                items-start
                                gap-2
                                border-b
                                border-gray-100
                                px-2
                                py-3
                                last:border-b-0
                            "
                        >

                            <!-- Product Variant -->

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
                                    v-model="detail.product_variant_id"
                                    :options="availableVariants"
                                    label="label"
                                    @update:modelValue="changeVariant(detail)"
                                />

                            </FormField>


                            <!-- Unit -->

                            <FormField
                                label="Unit"
                                required
                                :error="
                                    form.errors[
                                        `details.${index}.unit_id`
                                    ]
                                "
                            >

                                <SearchableSelect
                                    v-model="
                                        detail.unit_id
                                    "
                                    :options="
                                        getUnitsForVariant(
                                            detail.product_variant_id
                                        )
                                    "
                                    label="label"
                                    value-key="id"
                                    placeholder="Unit"
                                />

                            </FormField>


                            <!-- Qty -->

                            <FormField
                                label="Qty"
                                required
                                :error="
                                    form.errors[
                                        `details.${index}.qty`
                                    ]
                                "
                            >

                                <FormInput
                                    v-model="
                                        detail.qty
                                    "
                                    type="number"
                                    min="1"
                                    step="1"
                                    placeholder="1"
                                    @change="
                                        validateQuantity(detail)
                                    "
                                    @input="
                                        calculateDetail(detail)
                                    "
                                />
                            </FormField>


                            <!-- Consignment Price -->

                            <FormField
                                label="Consignment Price"
                                required
                                :error="
                                    form.errors[
                                        `details.${index}.unit_price`
                                    ]
                                "
                            >

                               <FormInput
                                    v-model="
                                        detail.unit_price
                                    "
                                    type="number"
                                    min="0"
                                    step="1"
                                    placeholder="0"
                                    @change="
                                        validateUnitPrice(detail)
                                    "
                                    @input="
                                        calculateDetail(detail)
                                    "
                                />
                            </FormField>


                            <!-- Total -->

                            <FormField
                                label="Total"
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
                                        font-semibold
                                        text-gray-800
                                        whitespace-nowrap
                                    "
                                >

                                    {{
                                        formatCurrency(
                                            detail.total_price
                                        )
                                    }}

                                </div>

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
                                        text-red-600
                                        transition
                                        hover:bg-red-50
                                        hover:text-red-700
                                        focus:outline-none
                                        focus:ring-2
                                        focus:ring-red-500
                                        focus:ring-offset-1
                                    "
                                    title="Remove item"
                                    @click="
                                        removeDetail(index)
                                    "
                                >

                                    <TrashIcon
                                        class="h-5 w-5"
                                    />

                                </button>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- Mobile Hint -->

                <div
                    class="
                        flex
                        items-center
                        gap-2
                        text-xs
                        text-gray-400
                        lg:hidden
                    "
                >

                    <span>←</span>

                    <span>
                        Geser tabel ke samping untuk melihat semua kolom
                    </span>

                    <span>→</span>

                </div>


                <!-- Add Product -->

                <div class="flex justify-start">

                    <BaseButton
                        type="button"
                        variant="secondary"
                        @click="addDetail"
                    >

                        + Add Product

                    </BaseButton>

                </div>


                <!-- ================================================= -->
                <!-- Remarks -->
                <!-- ================================================= -->

                <FormField
                    label="Remarks"
                    :error="
                        form.errors.remarks
                    "
                >

                    <FormTextarea
                        v-model="
                            form.remarks
                        "
                        :rows="3"
                        placeholder="Add remarks or notes..."
                    />

                </FormField>

            </div>

        </FormSection>


        <!-- ========================================================= -->
        <!-- Summary -->
        <!-- ========================================================= -->

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
                    bg-gray-50
                    p-5
                "
            >

                <div
                    class="
                        flex
                        justify-between
                        py-2
                        text-sm
                    "
                >

                    <span>
                        Total Items
                    </span>

                    <span class="font-medium">
                        {{ totalItems }}
                    </span>

                </div>


                <div
                    class="
                        flex
                        justify-between
                        py-2
                        text-sm
                    "
                >

                    <span>
                        Total Quantity
                    </span>

                    <span class="font-medium">
                        {{ totalQuantity }}
                    </span>

                </div>


                <div
                    class="
                        flex
                        justify-between
                        border-t
                        pt-3
                        text-base
                        font-semibold
                    "
                >

                    <span>
                        Total Consignment Value
                    </span>

                    <span>
                        {{ formatCurrency(totalValue) }}
                    </span>

                </div>

            </div>

        </div>


        <!-- ========================================================= -->
        <!-- Actions -->
        <!-- ========================================================= -->

        <div
            class="
                mt-8
                flex
                flex-col
                justify-end
                gap-3
                sm:flex-row
            "
        >

            <BaseButton
                type="button"
                variant="secondary"
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
            >

                {{
                    mode === 'create'
                        ? 'Save'
                        : 'Update'
                }}

            </BaseButton>


            <BaseButton
                v-if="
                    mode === 'create'
                "
                type="button"
                variant="success"
                :loading="
                    form.processing
                "
                @click="
                    emit('submitAndNew')
                "
            >

                Save &amp; New

            </BaseButton>

        </div>

    </form>

</template>