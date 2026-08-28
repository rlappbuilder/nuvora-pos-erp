<script setup>

import FormSection from '@/Components/Form/FormSection.vue'
import FormField from '@/Components/Form/FormField.vue'
import FormInput from '@/Components/Form/FormInput.vue'
import FormTextarea from '@/Components/Form/FormTextarea.vue'
import BaseButton from '@/Components/Button/BaseButton.vue'
import SearchableSelect from '@/Components/Form/SearchableSelect.vue'
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

    goodsReceipts: {
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

    goods_receipt_detail_id:
        null,

    purchase_order_detail_id:
        null,

    product_variant_id:
        null,

    unit_id:
        null,

    received_qty:
        0,

    already_returned_qty:
        0,

    remaining_returnable_qty:
        0,

    returned_qty:
        0,

    unit_cost:
        0,

    total_cost:
        0,

    remarks:
        null,

})


/*
|--------------------------------------------------------------------------
| Selected Goods Receipt
|--------------------------------------------------------------------------
*/

const selectedGoodsReceipt = computed(() => {

    if (
        !form.goods_receipt_id
    ) {

        return null

    }


    return props.goodsReceipts.find(

        item =>
            Number(item.id) ===
            Number(form.goods_receipt_id)

    ) ?? null

})


/*
|--------------------------------------------------------------------------
| Select Goods Receipt
|--------------------------------------------------------------------------
*/

const selectGoodsReceipt = (
    goodsReceiptId
) => {

    /*
    |--------------------------------------------------------------------------
    | Clear
    |--------------------------------------------------------------------------
    */

    if (
        !goodsReceiptId
    ) {

        form.company_id =
            null

        form.branch_id =
            null

        form.purchase_order_id =
            null

        form.supplier_id =
            null

        form.warehouse_id =
            null

        form.details = [

            createEmptyDetail(),

        ]

        return

    }


    /*
    |--------------------------------------------------------------------------
    | Find Goods Receipt
    |--------------------------------------------------------------------------
    */

    const goodsReceipt =
        props.goodsReceipts.find(

            item =>
                Number(item.id) ===
                Number(goodsReceiptId)

        )


    if (
        !goodsReceipt
    ) {

        return

    }
console.log(
    'SELECTED GRN:',
    goodsReceipt
)

console.log(
    'GRN DETAILS:',
    goodsReceipt.details
)

    /*
    |--------------------------------------------------------------------------
    | Copy Header
    |--------------------------------------------------------------------------
    */

    form.company_id =
        goodsReceipt.company_id

    form.branch_id =
        goodsReceipt.branch_id

    form.purchase_order_id =
        goodsReceipt.purchase_order_id

    form.supplier_id =
        goodsReceipt.supplier_id

    form.warehouse_id =
        goodsReceipt.warehouse_id


    /*
    |--------------------------------------------------------------------------
    | Copy Details
    |--------------------------------------------------------------------------
    */

    const details =
        goodsReceipt.details ?? []


    form.details =
        details
            .filter(
                detail =>
                    Number(
                        detail.received_qty || 0
                    ) > 0
            )
            .map(
                detail => ({

                    goods_receipt_detail_id:
                        detail.id,

                    purchase_order_detail_id:
                        detail.purchase_order_detail_id,

                    product_variant_id:
                        detail.product_variant_id,

                    unit_id:
                        detail.unit_id,

                    received_qty:
                        Number(
                            detail.received_qty || 0
                        ),

                    already_returned_qty:
                        Number(
                            detail.already_returned_qty || 0
                        ),

                    remaining_returnable_qty:
                        Math.max(
                            0,
                            Number(
                                detail.received_qty || 0
                            ) -
                            Number(
                                detail.already_returned_qty || 0
                            )
                        ),

                    returned_qty:
                        0,

                    unit_cost:
                        Number(
                            detail.unit_cost || 0
                        ),

                    total_cost:
                        0,

                    remarks:
                        null,

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
| Find GRN Detail
|--------------------------------------------------------------------------
*/

const findGoodsReceiptDetail = (
    detail
) => {

    return selectedGoodsReceipt
        .value
        ?.details
        ?.find(
            grnDetail =>
                Number(
                    grnDetail.id
                ) ===
                Number(
                    detail.goods_receipt_detail_id
                )
        ) ?? null

}


/*
|--------------------------------------------------------------------------
| Calculate Detail
|--------------------------------------------------------------------------
*/

const calculateDetail = (
    detail
) => {

    const returnedQty =
        Number(
            detail.returned_qty || 0
        )

    const unitCost =
        Number(
            detail.unit_cost || 0
        )


    detail.total_cost =
        Number(
            (
                returnedQty *
                unitCost
            ).toFixed(2)
        )

}


/*
|--------------------------------------------------------------------------
| Validate Returned Quantity
|--------------------------------------------------------------------------
*/

const validateReturnedQty = (
    detail
) => {

    const remainingQty =
        Number(
            detail.remaining_returnable_qty || 0
        )

    let returnedQty =
        Number(
            detail.returned_qty || 0
        )


    if (
        returnedQty < 0
    ) {

        returnedQty = 0

    }


    if (
        returnedQty >
        remainingQty
    ) {

        returnedQty =
            remainingQty

    }


    detail.returned_qty =
        returnedQty


    calculateDetail(
        detail
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


const totalReceivedQuantity = computed(() => {

    return form.details.reduce(

        (total, detail) =>

            total +
            Number(
                detail.received_qty || 0
            ),

        0

    )

})


const totalAlreadyReturnedQuantity = computed(() => {

    return form.details.reduce(

        (total, detail) =>

            total +
            Number(
                detail.already_returned_qty || 0
            ),

        0

    )

})


const totalRemainingReturnableQuantity = computed(() => {

    return form.details.reduce(

        (total, detail) =>

            total +
            Number(
                detail.remaining_returnable_qty || 0
            ),

        0

    )

})


const totalReturnedQuantity = computed(() => {

    return form.details.reduce(

        (total, detail) =>

            total +
            Number(
                detail.returned_qty || 0
            ),

        0

    )

})


const totalCost = computed(() => {

    return form.details.reduce(

        (total, detail) =>

            total +
            Number(
                detail.total_cost || 0
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
        <!-- Purchase Return Information -->
        <!-- ========================================================= -->

        <FormSection
            icon="↩️"
            title="Purchase Return Information"
            description="Basic information about this purchase return."
            :columns="2"
        >

            <!-- ===================================================== -->
            <!-- Return Number -->
            <!-- ===================================================== -->

            <FormField
                label="Return Number"
                :error="
                    form.errors.return_number
                "
            >

                <FormInput
                    v-model="form.return_number"
                    readonly
                    placeholder="Auto generated"
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
                    v-model="form.return_date"
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


            <!-- ===================================================== -->
            <!-- Goods Receipt -->
            <!-- ===================================================== -->

            <FormField
                label="Goods Receipt"
                required
                :error="
                    form.errors.goods_receipt_id
                "
            >

                <SearchableSelect
                    v-model="
                        form.goods_receipt_id
                    "
                    :options="goodsReceipts"
                    label="label"
                    value-key="id"
                    placeholder="Select Goods Receipt"
                    :disabled="
                        mode === 'edit'
                    "
                    @update:modelValue="
                        selectGoodsReceipt
                    "
                />

            </FormField>


            <!-- ===================================================== -->
            <!-- Supplier -->
            <!-- ===================================================== -->

            <FormField
                label="Supplier"
                required
                :error="
                    form.errors.supplier_id
                "
            >

                <FormInput
                    :model-value="
                        selectedGoodsReceipt
                            ?.supplier
                            ?.name
                            ?? '-'
                    "
                    readonly
                    placeholder="Supplier"
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

                <FormInput
                    :model-value="
                        selectedGoodsReceipt
                            ?.warehouse
                            ?.name
                            ?? '-'
                    "
                    readonly
                    placeholder="Warehouse"
                />

            </FormField>


            <!-- ===================================================== -->
            <!-- Purchase Order -->
            <!-- ===================================================== -->

            <FormField
                label="Purchase Order"
            >

                <FormInput
                    :model-value="
                        selectedGoodsReceipt
                            ?.purchase_order
                            ?.number
                            ?? '-'
                    "
                    readonly
                    placeholder="Purchase Order"
                />

            </FormField>

        </FormSection>


        <!-- ========================================================= -->
        <!-- Return Details -->
        <!-- ========================================================= -->

        <FormSection
            icon="↩️"
            title="Return Details"
            description="Select quantities to return from the goods receipt."
            :columns="1"
        >

            <div class="space-y-3">

                <!-- ================================================= -->
                <!-- Horizontal Scroll -->
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
                            min-w-[1300px]
                            p-3
                        "
                    >

                        <!-- ================================================= -->
                        <!-- Table Header -->
                        <!-- ================================================= -->

                        <div
                            class="
                                grid
                                grid-cols-[minmax(260px,2fr)_90px_110px_130px_130px_130px_140px_140px]
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
                                Received
                            </div>

                            <div>
                                Already Returned
                            </div>

                            <div>
                                Remaining
                            </div>

                            <div>
                                Return Qty
                            </div>

                            <div>
                                Unit Cost
                            </div>

                            <div class="text-right">
                                Total Cost
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
                                grid-cols-[minmax(260px,2fr)_90px_110px_130px_130px_130px_140px_140px]
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
                            <!-- Product -->
                            <!-- ================================================= -->

                            <FormField
                                label="Product"
                                :error="
                                    form.errors[
                                        `details.${index}.product_variant_id`
                                    ]
                                "
                            >

                                <FormInput
                                    :model-value="
                                        findGoodsReceiptDetail(detail)
                                            ?.variant
                                            ?.sku
                                            ? `${findGoodsReceiptDetail(detail)?.variant?.sku} - ${findGoodsReceiptDetail(detail)?.product?.name ?? ''} ${findGoodsReceiptDetail(detail)?.variant?.name ?? ''}`
                                            : '-'
                                    "
                                    readonly
                                />

                            </FormField>


                            <!-- ================================================= -->
                            <!-- Unit -->
                            <!-- ================================================= -->

                            <FormField
                                label="Unit"
                                :error="
                                    form.errors[
                                        `details.${index}.unit_id`
                                    ]
                                "
                            >

                                <FormInput
                                    :model-value="
                                        findGoodsReceiptDetail(detail)
                                            ?.unit
                                            ?.name
                                            ?? '-'
                                    "
                                    readonly
                                />

                            </FormField>


                            <!-- ================================================= -->
                            <!-- Received -->
                            <!-- ================================================= -->

                            <FormField
                                label="Received"
                            >

                                <FormInput
                                    :model-value="
                                        detail.received_qty
                                    "
                                    readonly
                                />

                            </FormField>


                            <!-- ================================================= -->
                            <!-- Already Returned -->
                            <!-- ================================================= -->

                            <FormField
                                label="Returned"
                            >

                                <FormInput
                                    :model-value="
                                        detail.already_returned_qty
                                    "
                                    readonly
                                />

                            </FormField>


                            <!-- ================================================= -->
                            <!-- Remaining -->
                            <!-- ================================================= -->

                            <FormField
                                label="Remaining"
                            >

                                <FormInput
                                    :model-value="
                                        detail.remaining_returnable_qty
                                    "
                                    readonly
                                />

                            </FormField>


                            <!-- ================================================= -->
                            <!-- Return Qty -->
                            <!-- ================================================= -->

                            <FormField
                                label="Return Qty"
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
                                    :max="
                                        detail.remaining_returnable_qty
                                    "
                                    step="0.01"
                                    placeholder="0"
                                    @input="
                                        validateReturnedQty(detail)
                                    "
                                />

                            </FormField>


                            <!-- ================================================= -->
                            <!-- Unit Cost -->
                            <!-- ================================================= -->

                            <FormField
                                label="Unit Cost"
                            >

                                <FormInput
                                    :model-value="
                                        detail.unit_cost
                                    "
                                    readonly
                                />

                            </FormField>


                            <!-- ================================================= -->
                            <!-- Total Cost -->
                            <!-- ================================================= -->

                            <FormField
                                label="Total Cost"
                            >

                                <FormInput
                                    :model-value="
                                        detail.total_cost
                                    "
                                    readonly
                                />

                            </FormField>

                        </div>

                    </div>

                </div>


                <!-- ================================================= -->
                <!-- Mobile Hint -->
                <!-- ================================================= -->

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

            </div>

        </FormSection>


        <!-- ========================================================= -->
        <!-- Remarks -->
        <!-- ========================================================= -->

        <FormSection
            icon="📝"
            title="Remarks"
            description="Additional information about this purchase return."
            :columns="1"
        >

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

                <div class="flex justify-between py-2 text-sm">

                    <span>
                        Total Items
                    </span>

                    <span class="font-medium">
                        {{ totalItems }}
                    </span>

                </div>


                <div class="flex justify-between py-2 text-sm">

                    <span>
                        Received Quantity
                    </span>

                    <span class="font-medium">
                        {{ totalReceivedQuantity }}
                    </span>

                </div>


                <div class="flex justify-between py-2 text-sm">

                    <span>
                        Already Returned
                    </span>

                    <span class="font-medium">
                        {{ totalAlreadyReturnedQuantity }}
                    </span>

                </div>


                <div class="flex justify-between py-2 text-sm">

                    <span>
                        Remaining Returnable
                    </span>

                    <span class="font-medium">
                        {{ totalRemainingReturnableQuantity }}
                    </span>

                </div>


                <div class="flex justify-between py-2 text-sm">

                    <span>
                        Returned Quantity
                    </span>

                    <span class="font-medium">
                        {{ totalReturnedQuantity }}
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
                        Total Cost
                    </span>

                    <span>
                        {{ formatCurrency(totalCost) }}
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