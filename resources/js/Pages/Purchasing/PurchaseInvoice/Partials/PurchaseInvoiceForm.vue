<script setup>

import FormSection from '@/Components/Form/FormSection.vue'
import FormField from '@/Components/Form/FormField.vue'
import FormInput from '@/Components/Form/FormInput.vue'
import FormTextarea from '@/Components/Form/FormTextarea.vue'
import BaseButton from '@/Components/Button/BaseButton.vue'
import SearchableSelect from '@/Components/Form/SearchableSelect.vue'
import FlatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'

import { computed,watch } from 'vue'


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

    suppliers: {
        type: Array,
        default: () => [],
    },

    warehouses: {
        type: Array,
        default: () => [],
    },

    paymentTerms: {
        type: Array,
        default: () => [],
    },

    currencies: {
        type: Array,
        default: () => [],
    },

    taxes: {
        type: Array,
        default: () => [],
    },

    mode: {
        type: String,
        default: 'create',
    },

})


const form = props.form

const selectedTax = computed(() => {

    if (!form.tax_id) {

        return null

    }

    return props.taxes.find(
        tax =>
            Number(tax.id) ===
            Number(form.tax_id)
    ) ?? null

})
/*
|--------------------------------------------------------------------------
| Payment Term
|--------------------------------------------------------------------------
*/

const selectedPaymentTerm = computed(() => {

    if (
        !form.payment_term_id
    ) {

        return null

    }


    return props.paymentTerms.find(

        term =>
            Number(term.id) ===
            Number(form.payment_term_id)

    ) ?? null

})


/*
|--------------------------------------------------------------------------
| Calculate Due Date
|--------------------------------------------------------------------------
*/

const calculateDueDate = () => {

    if (
        !form.invoice_date ||
        !selectedPaymentTerm.value
    ) {

        return

    }


    const invoiceDate =
        new Date(
            `${form.invoice_date}T00:00:00`
        )


    if (
        Number.isNaN(
            invoiceDate.getTime()
        )
    ) {

        return

    }


    const days =
        Number(
            selectedPaymentTerm.value.days || 0
        )


    invoiceDate.setDate(
        invoiceDate.getDate() +
        days
    )


    const year =
        invoiceDate.getFullYear()


    const month =
        String(
            invoiceDate.getMonth() + 1
        ).padStart(
            2,
            '0'
        )


    const day =
        String(
            invoiceDate.getDate()
        ).padStart(
            2,
            '0'
        )


    form.due_date =
        `${year}-${month}-${day}`

}


/*
|--------------------------------------------------------------------------
| Payment Term / Invoice Date Watch
|--------------------------------------------------------------------------
*/

watch(
    [
        () => form.invoice_date,
        () => form.payment_term_id,
    ],
    () => {

        calculateDueDate()

    }
)

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

    goods_receipt_detail_id:
        null,

    product_variant_id:
        null,

    unit_id:
        null,

    ordered_qty:
        0,

    received_qty:
        0,

    invoiced_qty:
        0,

    unit_price:
        0,

    discount_amount:
        0,

    tax_amount:
        0,

    subtotal:
        0,

    total_amount:
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
                detail => {

                    const receivedQty =
                        Number(
                            detail.received_qty || 0
                        )


                    const unitPrice =
                        Number(
                            detail.unit_cost || 0
                        )


                    return {

                        purchase_order_detail_id:
                            detail.purchase_order_detail_id,

                        goods_receipt_detail_id:
                            detail.id,

                        product_variant_id:
                            detail.product_variant_id,

                        unit_id:
                            detail.unit_id,

                        ordered_qty:
                            Number(
                                detail.ordered_qty || 0
                            ),

                        received_qty:
                            receivedQty,

                        invoiced_qty:
                            receivedQty,

                        unit_price:
                            unitPrice,

                        discount_amount:
                            0,

                        tax_amount:
                            0,

                        subtotal:
                            Number(
                                (
                                    receivedQty *
                                    unitPrice
                                ).toFixed(2)
                            ),

                        total_amount:
                            Number(
                                (
                                    receivedQty *
                                    unitPrice
                                ).toFixed(2)
                            ),

                        remarks:
                            null,

                    }

                }
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


    /*
    |--------------------------------------------------------------------------
    | Recalculate
    |--------------------------------------------------------------------------
    */

    form.details.forEach(
        detail => {

            calculateDetail(
                detail
            )

        }
    )

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

    const invoicedQty =
        Number(
            detail.invoiced_qty || 0
        )

    const unitPrice =
        Number(
            detail.unit_price || 0
        )

    let discountAmount =
        Number(
            detail.discount_amount || 0
        )

/*
|--------------------------------------------------------------------------
| Tax Change
|--------------------------------------------------------------------------
*/

watch(
    () => form.tax_id,
    () => {

        form.details.forEach(
            detail => {

                calculateDetail(
                    detail
                )

            }
        )

    }
)
    /*
    |--------------------------------------------------------------------------
    | Line Subtotal
    |--------------------------------------------------------------------------
    */

    const lineSubtotal =
        invoicedQty *
        unitPrice


    /*
    |--------------------------------------------------------------------------
    | Validate Discount
    |--------------------------------------------------------------------------
    */

    if (
        discountAmount < 0
    ) {

        discountAmount =
            0

    }


    if (
        discountAmount >
        lineSubtotal
    ) {

        discountAmount =
            lineSubtotal

    }


    /*
    |--------------------------------------------------------------------------
    | Amount After Discount
    |--------------------------------------------------------------------------
    */

    const lineAfterDiscount =
        lineSubtotal -
        discountAmount


    /*
    |--------------------------------------------------------------------------
    | Tax
    |--------------------------------------------------------------------------
    */

    const taxRate =
        Number(
            selectedTax.value?.rate ?? 0
        )


    const taxAmount =
        lineAfterDiscount *
        taxRate /
        100


    /*
    |--------------------------------------------------------------------------
    | Line Total
    |--------------------------------------------------------------------------
    */

    const lineTotal =
        lineAfterDiscount +
        taxAmount


    /*
    |--------------------------------------------------------------------------
    | Assign
    |--------------------------------------------------------------------------
    */

    detail.discount_amount =
        Number(
            discountAmount.toFixed(2)
        )

    detail.tax_amount =
        Number(
            taxAmount.toFixed(2)
        )

    detail.subtotal =
        Number(
            lineSubtotal.toFixed(2)
        )

    detail.total_amount =
        Number(
            lineTotal.toFixed(2)
        )

}


/*
|--------------------------------------------------------------------------
| Validate Invoice Quantity
|--------------------------------------------------------------------------
*/

const validateInvoicedQty = (
    detail
) => {

    const receivedQty =
        Number(
            detail.received_qty || 0
        )

    let invoicedQty =
        Number(
            detail.invoiced_qty || 0
        )


    if (
        invoicedQty < 0
    ) {

        invoicedQty =
            0

    }


    if (
        invoicedQty >
        receivedQty
    ) {

        invoicedQty =
            receivedQty

    }


    detail.invoiced_qty =
        invoicedQty


    calculateDetail(
        detail
    )

}


/*
|--------------------------------------------------------------------------
| Recalculate Detail
|--------------------------------------------------------------------------
*/

const recalculateDetail = (
    detail
) => {

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

    return form.details.filter(

        detail =>
            Number(
                detail.invoiced_qty || 0
            ) > 0

    ).length

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
                detail.total_amount || 0
            ),

        0

    )

})


const totalInvoicedQuantity = computed(() => {

    return form.details.reduce(

        (total, detail) =>

            total +
            Number(
                detail.invoiced_qty || 0
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

</script>
<template>

    <form
        @submit.prevent="handleSubmit"
    >

        <!-- ========================================================= -->
        <!-- Purchase Invoice Information -->
        <!-- ========================================================= -->

        <FormSection
            icon="🧾"
            title="Purchase Invoice Information"
            description="Basic information about this purchase invoice."
            :columns="2"
        >

            <!-- ===================================================== -->
            <!-- Internal Number -->
            <!-- ===================================================== -->

            <FormField
                label="Internal Number"
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


            <!-- ===================================================== -->
            <!-- Supplier Invoice Number -->
            <!-- ===================================================== -->

            <FormField
                label="Supplier Invoice Number"
                required
                :error="
                    form.errors.invoice_number
                "
            >

                <FormInput
                    v-model="form.invoice_number"
                    placeholder="Enter supplier invoice number"
                />

            </FormField>


            <!-- ===================================================== -->
            <!-- Invoice Date -->
            <!-- ===================================================== -->

            <FormField
                label="Invoice Date"
                required
                :error="
                    form.errors.invoice_date
                "
            >

                <FlatPickr
                    v-model="form.invoice_date"
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
            <!-- Due Date -->
            <!-- ===================================================== -->

            <FormField
                label="Due Date"
                :error="
                    form.errors.due_date
                "
            >

                <FormInput
                    :model-value="
                        form.due_date
                    "
                    readonly
                    placeholder="Auto calculated"
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


            <!-- ===================================================== -->
            <!-- Payment Term -->
            <!-- ===================================================== -->

            <FormField
                label="Payment Term"
                required
                :error="
                    form.errors.payment_term_id
                "
            >

                <SearchableSelect
                    v-model="
                        form.payment_term_id
                    "
                    :options="paymentTerms"
                    label="label"
                    value-key="id"
                    placeholder="Select Payment Term"
                />

            </FormField>


            <!-- ===================================================== -->
            <!-- Currency -->
            <!-- ===================================================== -->

            <FormField
                label="Currency"
                required
                :error="
                    form.errors.currency_id
                "
            >

                <SearchableSelect
                    v-model="
                        form.currency_id
                    "
                    :options="currencies"
                    label="label"
                    value-key="id"
                    placeholder="Select Currency"
                />

            </FormField>


            <!-- ===================================================== -->
            <!-- Tax -->
            <!-- ===================================================== -->

            <FormField
                label="Tax"
                :error="
                    form.errors.tax_id
                "
            >

                <SearchableSelect
                    v-model="
                        form.tax_id
                    "
                    :options="taxes"
                    label="label"
                    value-key="id"
                    placeholder="Select Tax"
                />

            </FormField>

        </FormSection>


        <!-- ========================================================= -->
        <!-- Invoice Details -->
        <!-- ========================================================= -->

        <FormSection
            icon="📦"
            title="Invoice Details"
            description="Invoice quantities and amounts based on the selected goods receipt."
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
                            min-w-[1550px]
                            p-3
                        "
                    >

                        <!-- ================================================= -->
                        <!-- Table Header -->
                        <!-- ================================================= -->

                        <div
                            class="
                                grid
                                grid-cols-[minmax(260px,2fr)_90px_110px_110px_110px_130px_130px_130px_140px_140px]
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
                                Received
                            </div>

                            <div>
                                Invoice Qty
                            </div>

                            <div>
                                Unit Price
                            </div>

                            <div>
                                Discount
                            </div>

                            <div>
                                Tax
                            </div>

                            <div>
                                Subtotal
                            </div>

                            <div class="text-right">
                                Total
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
                                grid-cols-[minmax(260px,2fr)_90px_110px_110px_110px_130px_130px_130px_140px_140px]
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
                            <!-- Invoice Qty -->
                            <!-- ================================================= -->

                            <FormField
                                label="Invoice Qty"
                                required
                                :error="
                                    form.errors[
                                        `details.${index}.invoiced_qty`
                                    ]
                                "
                            >

                                <FormInput
                                    v-model="
                                        detail.invoiced_qty
                                    "
                                    type="number"
                                    min="0"
                                    :max="
                                        detail.received_qty
                                    "
                                    step="0.01"
                                    placeholder="0"
                                    @input="
                                        validateInvoicedQty(detail)
                                    "
                                />

                            </FormField>


                            <!-- ================================================= -->
                            <!-- Unit Price -->
                            <!-- ================================================= -->

                            <FormField
                                label="Unit Price"
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
                                    @input="
                                        recalculateDetail(detail)
                                    "
                                />

                            </FormField>

                              <!-- ================================================= -->
                            <!-- Subtotal -->
                            <!-- ================================================= -->

                            <FormField
                                label="Subtotal"
                            >

                                <FormInput
                                    :model-value="
                                        formatCurrency(
                                            detail.subtotal
                                        )
                                    "
                                    readonly
                                />

                            </FormField>   
                            <!-- ================================================= -->
                            <!-- Discount -->
                            <!-- ================================================= -->

                            <FormField
                                label="Discount"
                            >

                                <FormInput
                                    v-model="
                                        detail.discount_amount
                                    "
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    @input="
                                        recalculateDetail(detail)
                                    "
                                />

                            </FormField>


                            <!-- ================================================= -->
                            <!-- Tax -->
                            <!-- ================================================= -->

                            <FormField
                                label="Tax"
                            >

                                <FormInput
                                    v-model="
                                        detail.tax_amount
                                    "
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    @input="
                                        recalculateDetail(detail)
                                    "
                                />

                            </FormField>


                           


                            <!-- ================================================= -->
                            <!-- Total -->
                            <!-- ================================================= -->

                            <FormField
                                label="Total"
                            >

                                <FormInput
                                    :model-value="
                                        formatCurrency(
                                            detail.total_amount
                                        )
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
            description="Additional information about this purchase invoice."
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

                <!-- Total Items -->

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


                <!-- Received -->

                <div
                    class="
                        flex
                        justify-between
                        py-2
                        text-sm
                    "
                >

                    <span>
                        Received Quantity
                    </span>

                    <span class="font-medium">
                        {{ totalReceivedQuantity }}
                    </span>

                </div>


                <!-- Invoiced -->

                <div
                    class="
                        flex
                        justify-between
                        py-2
                        text-sm
                    "
                >

                    <span>
                        Invoice Quantity
                    </span>

                    <span class="font-medium">
                        {{ totalInvoicedQuantity }}
                    </span>

                </div>


                <!-- Subtotal -->

                <div
                    class="
                        flex
                        justify-between
                        py-2
                        text-sm
                    "
                >

                    <span>
                        Subtotal
                    </span>

                    <span class="font-medium">
                        {{ formatCurrency(subtotal) }}
                    </span>

                </div>


                <!-- Discount -->

                <div
                    class="
                        flex
                        justify-between
                        py-2
                        text-sm
                    "
                >

                    <span>
                        Discount
                    </span>

                    <span class="font-medium">
                        {{ formatCurrency(discountAmount) }}
                    </span>

                </div>


                <!-- Tax -->

                <div
                    class="
                        flex
                        justify-between
                        py-2
                        text-sm
                    "
                >

                    <span>
                        Tax
                    </span>

                    <span class="font-medium">
                        {{ formatCurrency(taxAmount) }}
                    </span>

                </div>


                <!-- Grand Total -->

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