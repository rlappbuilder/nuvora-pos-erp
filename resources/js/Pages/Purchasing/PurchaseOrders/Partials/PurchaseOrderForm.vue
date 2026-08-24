<script setup>

import FormSection from '@/Components/Form/FormSection.vue'
import FormField from '@/Components/Form/FormField.vue'
import FormInput from '@/Components/Form/FormInput.vue'
import FormTextarea from '@/Components/Form/FormTextarea.vue'
import BaseButton from '@/Components/Button/BaseButton.vue'
import SearchableSelect from '@/Components/Form/SearchableSelect.vue'
import { TrashIcon } from '@heroicons/vue/24/outline'
import FlatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'

import { computed } from 'vue'


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

    suppliers: {
        type: Array,
        default: () => [],
    },

    filteredVariants: {
        type: Array,
        default: () => [],
    },

    purchaseRequests: {
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

    purchase_request_detail_id:
        null,

    product_variant_id:
        null,

    unit_id:
        null,

    qty:
        1,

    unit_price:
        0,

    discount_rate:
        0,

    discount_amount:
        0,

    tax_rate:
        0,

    tax_amount:
        0,

    subtotal:
        0,

    total:
        0,

    description:
        null,

})


/*
|--------------------------------------------------------------------------
| Add Detail
|--------------------------------------------------------------------------
*/

const addDetail = () => {

    form.details.push({

        purchase_request_detail_id:
            null,

        product_variant_id:
            null,

        unit_id:
            null,

        qty:
            1,

        unit_price:
            0,

        discount_rate:
            0,

        discount_amount:
            0,

        tax_rate:
            0,

        tax_amount:
            0,

        subtotal:
            0,

        total:
            0,

        description:
            null,

    })

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

    if (!variantId) {

        return []

    }

    const variant =
        props.filteredVariants.find(

            item =>
                Number(item.id) ===
                Number(variantId)

        )

    return variant?.units ?? []

}


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

        return

    }


    detail.unit_id =
        null


    const units =
        getUnitsForVariant(
            detail.product_variant_id
        )


    const defaultUnit =
        units.find(
            unit =>
                unit.is_default
        )


    if (defaultUnit) {

        detail.unit_id =
            defaultUnit.id

    }

}

/*
|--------------------------------------------------------------------------
| Purchase Request Selected
|--------------------------------------------------------------------------
*/

const selectPurchaseRequest = (
    purchaseRequestId
) => {

    /*
    |--------------------------------------------------------------------------
    | Clear PR
    |--------------------------------------------------------------------------
    */

    if (!purchaseRequestId) {

        form.details = [

            createEmptyDetail(),

        ]

        return

    }


    /*
    |--------------------------------------------------------------------------
    | Find Purchase Request
    |--------------------------------------------------------------------------
    */

    const purchaseRequest =
        props.purchaseRequests.find(

            item =>
                Number(item.id) ===
                Number(purchaseRequestId)

        )


    if (
        !purchaseRequest
    ) {

        return

    }


    /*
    |--------------------------------------------------------------------------
    | Copy PR Header
    |--------------------------------------------------------------------------
    */

    form.branch_id =
        purchaseRequest.branch_id

    form.warehouse_id =
        purchaseRequest.warehouse_id

    form.required_date =
        purchaseRequest.required_date
            ?? null


    /*
    |--------------------------------------------------------------------------
    | Copy PR Details
    |--------------------------------------------------------------------------
    */

    const details =
        purchaseRequest.details ?? []


    form.details =
        details.map(

            detail => ({

                purchase_request_detail_id:
                    detail.id,

                product_variant_id:
                    detail.product_variant_id,

                unit_id:
                    detail.unit_id,

                qty:
                    Number(
                        detail.qty || 0
                    ),

                /*
                |--------------------------------------------------------------------------
                | Pricing is entered in PO
                |--------------------------------------------------------------------------
                */

                unit_price:
                    0,

                discount_rate:
                    0,

                discount_amount:
                    0,

                tax_rate:
                    0,

                tax_amount:
                    0,

                subtotal:
                    0,

                total:
                    0,

                description:
                    detail.description
                    ?? null,

            })

        )


    /*
    |--------------------------------------------------------------------------
    | Fallback
    |--------------------------------------------------------------------------
    */

    if (
        !form.details.length
    ) {

        form.details = [

            createEmptyDetail(),

        ]

    }

}

/*
|--------------------------------------------------------------------------
| Calculate Detail
|--------------------------------------------------------------------------
*/

const calculateDetail = (
    detail
) => {

    const qty =
        Number(
            detail.qty || 0
        )

    const unitPrice =
        Number(
            detail.unit_price || 0
        )

    const discountRate =
        Number(
            detail.discount_rate || 0
        )

    const taxRate =
        Number(
            detail.tax_rate || 0
        )


    /*
    |--------------------------------------------------------------------------
    | Gross
    |--------------------------------------------------------------------------
    */

    const gross =
        qty * unitPrice


    /*
    |--------------------------------------------------------------------------
    | Discount
    |--------------------------------------------------------------------------
    */

    const discountAmount =
        gross *
        discountRate /
        100


    /*
    |--------------------------------------------------------------------------
    | Subtotal
    |--------------------------------------------------------------------------
    */

    const calculatedSubtotal =
        gross -
        discountAmount


    /*
    |--------------------------------------------------------------------------
    | Tax
    |--------------------------------------------------------------------------
    */

    const taxAmount =
        calculatedSubtotal *
        taxRate /
        100


    /*
    |--------------------------------------------------------------------------
    | Total
    |--------------------------------------------------------------------------
    */

    const total =
        calculatedSubtotal +
        taxAmount


    detail.discount_amount =
        Number(
            discountAmount.toFixed(2)
        )

    detail.subtotal =
        Number(
            calculatedSubtotal.toFixed(2)
        )

    detail.tax_amount =
        Number(
            taxAmount.toFixed(2)
        )

    detail.total =
        Number(
            total.toFixed(2)
        )

}


/*
|--------------------------------------------------------------------------
| Gross Amount
|--------------------------------------------------------------------------
*/

const getGrossAmount = (
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

const totalItems = computed(() => {

    return form.details.length

})


const totalQuantity = computed(() => {

    return form.details.reduce(

        (total, detail) =>

            total +
            Number(
                detail.qty || 0
            ),

        0

    )

})


const grossAmount = computed(() => {

    return form.details.reduce(

        (total, detail) =>

            total +
            getGrossAmount(detail),

        0

    )

})


const discountAmount = computed(() => {

    return form.details.reduce(

        (total, detail) =>

            total +
            Number(
                detail.discount_amount || 0
            ),

        0

    )

})


const subtotal = computed(() => {

    return form.details.reduce(

        (total, detail) =>

            total +
            Number(
                detail.subtotal || 0
            ),

        0

    )

})


const taxAmount = computed(() => {

    return form.details.reduce(

        (total, detail) =>

            total +
            Number(
                detail.tax_amount || 0
            ),

        0

    )

})


const grandTotal = computed(() => {

    return form.details.reduce(

        (total, detail) =>

            total +
            Number(
                detail.total || 0
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

    <form
        @submit.prevent="handleSubmit"
    >

        <!-- ========================================================= -->
        <!-- Purchase Order Information -->
        <!-- ========================================================= -->

        <FormSection
            icon="📋"
            title="Purchase Order Information"
            description="Basic information about this purchase order."
            :columns="2"
        >

            <!-- Number -->

            <FormField
                label="Number"
                :error="
                    form.errors.number
                "
            >

                <FormInput
                    v-model="form.number"
                    readonly
                    placeholder="Auto generated"
                />

            </FormField>


            <!-- Order Date -->

            <FormField
                label="Order Date"
                required
                :error="
                    form.errors.order_date
                "
            >

                <FlatPickr
                    v-model="form.order_date"
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


            <!-- Required Date -->

            <FormField
                label="Required Date"
                :error="
                    form.errors.required_date
                "
            >

                <FlatPickr
                    v-model="form.required_date"
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


            <!-- Supplier -->

            <FormField
                label="Supplier"
                required
                :error="
                    form.errors.supplier_id
                "
            >

                <SearchableSelect
                    v-model="form.supplier_id"
                    :options="suppliers"
                    label="label"
                    value-key="id"
                    placeholder="Select Supplier"
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
                    v-model="form.branch_id"
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
                    v-model="form.warehouse_id"
                    :options="filteredWarehouses"
                    label="label"
                    value-key="id"
                    placeholder="Select Warehouse"
                />

            </FormField>


            <!-- Purchase Request -->

           <FormField
                label="Purchase Request"
                :error="form.errors.purchase_request_id"
            >

                <SearchableSelect
                    v-model="form.purchase_request_id"
                    :options="purchaseRequests"
                    label="label"
                    value-key="id"
                    placeholder="Select Purchase Request"
                    @update:modelValue="selectPurchaseRequest"
                />

            </FormField>

        </FormSection>


       <!-- ========================================================= -->
<!-- Order Details -->
<!-- ========================================================= -->

<FormSection
    icon="📦"
    title="Order Details"
    description="Add products, quantities, pricing and applicable taxes."
    :columns="1"
>

    <div class="space-y-3">

        <!-- ===================================================== -->
        <!-- Desktop / Tablet Horizontal Scroll -->
        <!-- ===================================================== -->

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
                    min-w-[1100px]
                    p-3
                "
            >

                <!-- ================================================= -->
                <!-- Table Header -->
                <!-- ================================================= -->

                <div
                    class="
                        grid
                        grid-cols-[minmax(260px,2fr)_80px_80px_130px_140px_90px_90px_140px_80px]
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
                        Unit Price
                    </div>

                    <div>
                        Amount
                    </div>

                    <div>
                        Disc %
                    </div>

                    <div>
                        Tax %
                    </div>

                    <div class="text-right">
                        Total
                    </div>

                    <div class="text-center">
                        Action
                    </div>

                </div>


                <!-- ================================================= -->
                <!-- Detail Rows -->
                <!-- ================================================= -->

                <div
                    v-for="(
                        detail,
                        index
                    ) in form.details"

                    :key="index"

                    class="
                        grid
                        grid-cols-[minmax(260px,2fr)_80px_80px_130px_140px_90px_90px_140px_80px]
                        items-start
                        gap-2
                        border-b
                        border-gray-100
                        px-2
                        py-3
                        last:border-b-0
                    "
                >

                    <!-- ================================================= -->
                    <!-- Product Variant -->
                    <!-- ================================================= -->

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
                                filteredVariants
                            "
                            label="label"
                            value-key="id"
                            placeholder="Select Variant"
                              :disabled="!!detail.purchase_request_detail_id"
                            @update:modelValue="
                                changeVariant(detail)
                            "
                        />

                    </FormField>


                    <!-- ================================================= -->
                    <!-- Unit -->
                    <!-- ================================================= -->

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
                            :disabled="!!detail.purchase_request_detail_id"
                        />

                    </FormField>


                    <!-- ================================================= -->
                    <!-- Qty -->
                    <!-- ================================================= -->

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
                            min="0"
                            step="0.01"
                            placeholder="0"
                            @input="
                                calculateDetail(detail)
                            "
                            :readonly="!!detail.purchase_request_detail_id"
                        />

                    </FormField>


                    <!-- ================================================= -->
                    <!-- Unit Price -->
                    <!-- ================================================= -->

                    <FormField
                        label="Unit Price"
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
                            step="0.01"
                            placeholder="0"
                            @input="
                                calculateDetail(detail)
                            "
                        />

                    </FormField>


                    <!-- ================================================= -->
                    <!-- Amount -->
                    <!-- ================================================= -->

                    <FormField
                        label="Amount"
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
                                font-medium
                                text-gray-700
                                whitespace-nowrap
                            "
                        >

                            {{
                                formatCurrency(
                                    getGrossAmount(detail)
                                )
                            }}

                        </div>

                    </FormField>


                    <!-- ================================================= -->
                    <!-- Discount -->
                    <!-- ================================================= -->

                    <FormField
                        label="Disc %"
                        :error="
                            form.errors[
                                `details.${index}.discount_rate`
                            ]
                        "
                    >

                        <FormInput
                            v-model="
                                detail.discount_rate
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


                    <!-- ================================================= -->
                    <!-- Tax -->
                    <!-- ================================================= -->

                    <FormField
                        label="Tax %"
                        :error="
                            form.errors[
                                `details.${index}.tax_rate`
                            ]
                        "
                    >

                        <FormInput
                            v-model="
                                detail.tax_rate
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


                    <!-- ================================================= -->
                    <!-- Total -->
                    <!-- ================================================= -->

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
                                    detail.total
                                )
                            }}

                        </div>

                    </FormField>


                    <!-- ================================================= -->
                    <!-- Remove -->
                    <!-- ================================================= -->

                    <div
                        class="
                            flex
                            min-h-[38px]
                            items-center
                            justify-center
                        "
                    >

                        <button
                            v-if="form.details.length > 1"
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
                            @click="removeDetail(index)"
                        >
                            <TrashIcon class="h-5 w-5" />
                        </button>

                    </div>

                </div>

            </div>

        </div>


        <!-- ===================================================== -->
        <!-- Mobile Hint -->
        <!-- ===================================================== -->

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

            <span>
                ←
            </span>

            <span>
                Geser tabel ke samping untuk melihat semua kolom
            </span>

            <span>
                →
            </span>

        </div>


        <!-- ===================================================== -->
        <!-- Add Product -->
        <!-- ===================================================== -->

        <div class="flex justify-start">

            <BaseButton
                type="button"
                variant="secondary"
                @click="addDetail"
            >

                + Add Product

            </BaseButton>

        </div>

    </div>

</FormSection>

        <!-- ========================================================= -->
        <!-- Description -->
        <!-- ========================================================= -->

        <FormSection
            icon="📝"
            title="Description"
            description="Additional information about this purchase order."
            :columns="1"
        >

            <FormField
                label="Description"
                :error="
                    form.errors.description
                "
            >

                <FormTextarea
                    v-model="
                        form.description
                    "
                    :rows="4"
                    placeholder="Write additional notes..."
                />

            </FormField>

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
                        w-full
                        max-w-md
                        rounded-xl
                        border
                        bg-gray-50
                        p-5
                    "
                >

                    <div class="flex justify-between py-2 text-sm">
                        <span>Total Items</span>
                        <span class="font-medium">
                            {{ totalItems }}
                        </span>
                    </div>

                    <div class="flex justify-between py-2 text-sm">
                        <span>Total Quantity</span>
                        <span class="font-medium">
                            {{ totalQuantity }}
                        </span>
                    </div>

                    <div
                        class="
                            mt-2
                            flex
                            justify-between
                            border-t
                            py-2
                            text-sm
                        "
                    >
                        <span>Gross Amount</span>

                        <span class="font-medium">
                            {{ formatCurrency(grossAmount) }}
                        </span>
                    </div>

                    <div class="flex justify-between py-2 text-sm">
                        <span>Discount</span>

                        <span class="font-medium">
                            {{ formatCurrency(discountAmount) }}
                        </span>
                    </div>

                    <div class="flex justify-between py-2 text-sm">
                        <span>Subtotal</span>

                        <span class="font-medium">
                            {{ formatCurrency(subtotal) }}
                        </span>
                    </div>

                    <div class="flex justify-between py-2 text-sm">
                        <span>Tax</span>

                        <span class="font-medium">
                            {{ formatCurrency(taxAmount) }}
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
                            Grand Total
                        </span>

                        <span>
                            {{ formatCurrency(grandTotal) }}
                        </span>
                    </div>

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