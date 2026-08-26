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

    purchaseOrders: {
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

    purchase_order_detail_id:
        null,

    product_variant_id:
        null,

    unit_id:
        null,

    ordered_qty:
        0,

    received_qty:
        0,

    rejected_qty:
        0,

    remaining_qty:
        0,

    unit_cost:
        0,

    total_cost:
        0,

    description:
        null,

})


/*
|--------------------------------------------------------------------------
| Find Purchase Order
|--------------------------------------------------------------------------
*/

const selectedPurchaseOrder = computed(() => {

    if (
        !form.purchase_order_id
    ) {

        return null

    }


    return props.purchaseOrders.find(

        item =>
            Number(item.id) ===
            Number(form.purchase_order_id)

    ) ?? null

})


/*
|--------------------------------------------------------------------------
| Select Purchase Order
|--------------------------------------------------------------------------
*/

const selectPurchaseOrder = (
    purchaseOrderId
) => {

    /*
    |--------------------------------------------------------------------------
    | Clear
    |--------------------------------------------------------------------------
    */

    if (
        !purchaseOrderId
    ) {

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
    | Find Purchase Order
    |--------------------------------------------------------------------------
    */

    const purchaseOrder =
        props.purchaseOrders.find(

            item =>
                Number(item.id) ===
                Number(purchaseOrderId)

        )


    if (
        !purchaseOrder
    ) {

        return

    }


    /*
    |--------------------------------------------------------------------------
    | Copy PO Header
    |--------------------------------------------------------------------------
    */

    form.company_id =
        purchaseOrder.company_id

    form.branch_id =
        purchaseOrder.branch_id

    form.warehouse_id =
        purchaseOrder.warehouse_id

    form.supplier_id =
        purchaseOrder.supplier_id


    /*
    |--------------------------------------------------------------------------
    | Copy PO Details
    |--------------------------------------------------------------------------
    */

    const details =
        purchaseOrder.details ?? []


    form.details =
        details
            .filter(
                detail =>
                    Number(
                        detail.remaining_qty || 0
                    ) > 0
            )
            .map(
                detail => ({

                    purchase_order_detail_id:
                        detail.id,

                    product_variant_id:
                        detail.product_variant_id,

                    unit_id:
                        detail.unit_id,

                    ordered_qty:
                        Number(
                            detail.qty || 0
                        ),

                    received_qty:
                        0,

                    rejected_qty:
                        0,

                    remaining_qty:
                        Number(
                            detail.remaining_qty || 0
                        ),

                    unit_cost:
                        Number(
                            detail.unit_price || 0
                        ),

                    total_cost:
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

    const receivedQty =
        Number(
            detail.received_qty || 0
        )

    const unitCost =
        Number(
            detail.unit_cost || 0
        )


    detail.total_cost =
        Number(
            (
                receivedQty *
                unitCost
            ).toFixed(2)
        )

}


/*
|--------------------------------------------------------------------------
| Validate Received Quantity
|--------------------------------------------------------------------------
*/

const validateReceivedQty = (
    detail
) => {

    const remainingQty =
        Number(
            detail.remaining_qty || 0
        )

    let receivedQty =
        Number(
            detail.received_qty || 0
        )


    if (
        receivedQty < 0
    ) {

        receivedQty = 0

    }


    if (
        receivedQty >
        remainingQty
    ) {

        receivedQty =
            remainingQty

    }


    detail.received_qty =
        receivedQty


    calculateDetail(
        detail
    )

}


/*
|--------------------------------------------------------------------------
| Validate Rejected Quantity
|--------------------------------------------------------------------------
*/

const validateRejectedQty = (
    detail
) => {

    let rejectedQty =
        Number(
            detail.rejected_qty || 0
        )


    if (
        rejectedQty < 0
    ) {

        rejectedQty = 0

    }


    detail.rejected_qty =
        rejectedQty

}


/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

const totalItems = computed(() => {

    return form.details.length

})


const totalOrderedQuantity = computed(() => {

    return form.details.reduce(

        (total, detail) =>

            total +
            Number(
                detail.ordered_qty || 0
            ),

        0

    )

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


const totalRejectedQuantity = computed(() => {

    return form.details.reduce(

        (total, detail) =>

            total +
            Number(
                detail.rejected_qty || 0
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
        <!-- Goods Receipt Information -->
        <!-- ========================================================= -->

        <FormSection
            icon="📋"
            title="Goods Receipt Information"
            description="Basic information about this goods receipt."
            :columns="2"
        >

            <!-- ===================================================== -->
            <!-- GRN Number -->
            <!-- ===================================================== -->

            <FormField
                label="GRN Number"
                :error="
                    form.errors.grn_number
                "
            >

                <FormInput
                    v-model="form.grn_number"
                    readonly
                    placeholder="Auto generated"
                />

            </FormField>


            <!-- ===================================================== -->
            <!-- Receipt Date -->
            <!-- ===================================================== -->

            <FormField
                label="Receipt Date"
                required
                :error="
                    form.errors.receipt_date
                "
            >

                <FlatPickr
                    v-model="form.receipt_date"
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
            <!-- Purchase Order -->
            <!-- ===================================================== -->

            <FormField
                label="Purchase Order"
                required
                :error="
                    form.errors.purchase_order_id
                "
            >

                <SearchableSelect
                    v-model="
                        form.purchase_order_id
                    "
                    :options="purchaseOrders"
                    label="label"
                    value-key="id"
                    placeholder="Select Purchase Order"
                    :disabled="
                        mode === 'edit'
                    "
                    @update:modelValue="
                        selectPurchaseOrder
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
                        selectedPurchaseOrder
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
                        selectedPurchaseOrder
                            ?.warehouse
                            ?.name
                            ?? '-'
                    "
                    readonly
                    placeholder="Warehouse"
                />

            </FormField>


            <!-- ===================================================== -->
            <!-- Supplier DO -->
            <!-- ===================================================== -->

            <FormField
                label="Supplier DO Number"
                :error="
                    form.errors.supplier_do_number
                "
            >

                <FormInput
                    v-model="
                        form.supplier_do_number
                    "
                    placeholder="Enter supplier delivery order number"
                />

            </FormField>

        </FormSection>


        <!-- ========================================================= -->
        <!-- Receipt Details -->
        <!-- ========================================================= -->

        <FormSection
            icon="📦"
            title="Receipt Details"
            description="Select quantities received against the purchase order."
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
                            min-w-[1200px]
                            p-3
                        "
                    >

                        <!-- ================================================= -->
                        <!-- Table Header -->
                        <!-- ================================================= -->

                        <div
                            class="
                                grid
                                grid-cols-[minmax(260px,2fr)_90px_110px_120px_120px_120px_140px_140px]
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
                                Ordered
                            </div>

                            <div>
                                Previously Received
                            </div>

                            <div>
                                Remaining
                            </div>

                            <div>
                                Receive Qty
                            </div>

                            <div>
                                Reject Qty
                            </div>

                            <div class="text-right">
                                Unit Cost
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
                                grid-cols-[minmax(260px,2fr)_90px_110px_120px_120px_120px_140px_140px]
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
                                        selectedPurchaseOrder
                                            ?.details
                                            ?.find(
                                                poDetail =>
                                                    Number(
                                                        poDetail.id
                                                    ) ===
                                                    Number(
                                                        detail.purchase_order_detail_id
                                                    )
                                            )
                                            ?.variant
                                            ?.sku
                                            ? `${selectedPurchaseOrder.details.find(
                                                poDetail =>
                                                    Number(
                                                        poDetail.id
                                                    ) ===
                                                    Number(
                                                        detail.purchase_order_detail_id
                                                    )
                                            )?.variant?.sku} - ${selectedPurchaseOrder.details.find(
                                                poDetail =>
                                                    Number(
                                                        poDetail.id
                                                    ) ===
                                                    Number(
                                                        detail.purchase_order_detail_id
                                                    )
                                            )?.product?.name ?? ''} ${selectedPurchaseOrder.details.find(
                                                poDetail =>
                                                    Number(
                                                        poDetail.id
                                                    ) ===
                                                    Number(
                                                        detail.purchase_order_detail_id
                                                    )
                                            )?.variant?.name ?? ''}`
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
                                        selectedPurchaseOrder
                                            ?.details
                                            ?.find(
                                                poDetail =>
                                                    Number(
                                                        poDetail.id
                                                    ) ===
                                                    Number(
                                                        detail.purchase_order_detail_id
                                                    )
                                            )
                                            ?.unit
                                            ?.name
                                            ?? '-'
                                    "
                                    readonly
                                />

                            </FormField>


                            <!-- ================================================= -->
                            <!-- Ordered -->
                            <!-- ================================================= -->

                            <FormField
                                label="Ordered"
                            >

                                <FormInput
                                    :model-value="
                                        detail.ordered_qty
                                    "
                                    readonly
                                />

                            </FormField>


                            <!-- ================================================= -->
                            <!-- Previously Received -->
                            <!-- ================================================= -->

                            <FormField
                                label="Received"
                            >

                                <FormInput
                                    :model-value="
                                        Number(
                                            detail.ordered_qty || 0
                                        ) -
                                        Number(
                                            detail.remaining_qty || 0
                                        )
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
                                        detail.remaining_qty
                                    "
                                    readonly
                                />

                            </FormField>


                            <!-- ================================================= -->
                            <!-- Receive Qty -->
                            <!-- ================================================= -->

                            <FormField
                                label="Receive Qty"
                                required
                                :error="
                                    form.errors[
                                        `details.${index}.received_qty`
                                    ]
                                "
                            >

                                <FormInput
                                    v-model="
                                        detail.received_qty
                                    "
                                    type="number"
                                    min="0"
                                    :max="
                                        detail.remaining_qty
                                    "
                                    step="0.01"
                                    placeholder="0"
                                    @input="
                                        validateReceivedQty(detail)
                                    "
                                />

                            </FormField>


                            <!-- ================================================= -->
                            <!-- Reject Qty -->
                            <!-- ================================================= -->

                            <FormField
                                label="Reject Qty"
                                :error="
                                    form.errors[
                                        `details.${index}.rejected_qty`
                                    ]
                                "
                            >

                                <FormInput
                                    v-model="
                                        detail.rejected_qty
                                    "
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    placeholder="0"
                                    @input="
                                        validateRejectedQty(detail)
                                    "
                                />

                            </FormField>


                            <!-- ================================================= -->
                            <!-- Unit Cost -->
                            <!-- ================================================= -->

                            <FormField
                                label="Unit Cost"
                                required
                                :error="
                                    form.errors[
                                        `details.${index}.unit_cost`
                                    ]
                                "
                            >

                                <FormInput
                                    v-model="
                                        detail.unit_cost
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
            description="Additional information about this goods receipt."
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
                        Ordered Quantity
                    </span>

                    <span class="font-medium">
                        {{ totalOrderedQuantity }}
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
                        Rejected Quantity
                    </span>

                    <span class="font-medium">
                        {{ totalRejectedQuantity }}
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