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

import {computed,watch} from 'vue'


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

    companies: {
        type: Array,
        default: () => [],
    },

    branches: {
        type: Array,
        default: () => [],
    },

    suppliers: {
        type: Array,
        default: () => [],
    },

    paymentAccounts: {
        type: Array,
        default: () => [],
    },

    purchaseInvoices: {
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

    purchase_invoice_header_id:
        null,

    invoice_amount:
        0,

    previous_paid_amount:
        0,

    previous_outstanding_amount:
        0,

    payment_amount:
        0,

    remarks:
        null,

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
| Invoice Selected
|--------------------------------------------------------------------------
*/

const selectInvoice = (
    detail,
    invoiceId
) => {

    if (!invoiceId) {

        detail.invoice_amount =
            0

        detail.previous_paid_amount =
            0

        detail.previous_outstanding_amount =
            0

        return

    }


    const invoice =
        props.purchaseInvoices.find(

            item =>
                Number(item.id) ===
                Number(invoiceId)

        )


    if (
        !invoice
    ) {

        return

    }


    detail.invoice_amount =
        Number(
            invoice.grand_total || 0
        )

    detail.previous_paid_amount =
        Number(
            invoice.paid_amount || 0
        )

    detail.previous_outstanding_amount =
        Number(
            invoice.outstanding_amount || 0
        )


    /*
    |--------------------------------------------------------------------------
    | Default Payment Amount
    |--------------------------------------------------------------------------
    */

    if (
        !detail.payment_amount ||
        Number(detail.payment_amount) <= 0
    ) {

        detail.payment_amount =
            Number(
                invoice.outstanding_amount || 0
            )

    }

}


const filteredPurchaseInvoices = computed(() => {
    if (!form.supplier_id || !form.branch_id) {
        return []
    }

    return props.purchaseInvoices.filter(invoice =>
        Number(invoice.supplier_id) === Number(form.supplier_id) &&
        Number(invoice.branch_id) === Number(form.branch_id) &&
        ['Posted', 'Partially Paid'].includes(invoice.status) &&
        Number(invoice.outstanding_amount || 0) > 0
    )
})

const getAvailableInvoices = (currentDetail) => {
    const selectedIds = form.details
        .filter(detail => detail !== currentDetail)
        .map(detail => Number(detail.purchase_invoice_header_id))

    return filteredPurchaseInvoices.value.filter(
        invoice => !selectedIds.includes(Number(invoice.id))
    )
}
watch(
    () => [
        form.supplier_id,
        form.branch_id,
    ],
    () => {
        form.details.forEach(detail => {
            const invoice = filteredPurchaseInvoices.value.find(
                item =>
                    Number(item.id) ===
                    Number(detail.purchase_invoice_header_id)
            )

            if (!invoice) {
                detail.purchase_invoice_header_id = null
                detail.invoice_amount = 0
                detail.previous_paid_amount = 0
                detail.previous_outstanding_amount = 0
                detail.payment_amount = 0
            }
        })
    }
)

/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

const totalItems = computed(() => {

    return form.details.length

})


const totalPayment = computed(() => {

    return form.details.reduce(

        (total, detail) =>

            total +
            Number(
                detail.payment_amount || 0
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
        <!-- Purchase Payment Information -->
        <!-- ========================================================= -->

        <FormSection
            icon="💳"
            title="Purchase Payment Information"
            description="Basic information about this purchase payment."
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


            <!-- Payment Date -->

            <FormField
                label="Payment Date"
                required
                :error="
                    form.errors.payment_date
                "
            >

                <FlatPickr
                    v-model="form.payment_date"
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

       <!-- Payment Method -->

            <FormField
                label="Payment Method"
                required
                :error="form.errors.payment_method"
            >

                <SearchableSelect
                    v-model="form.payment_method"
                    :options="[
                        { value: 'Cash', label: 'Cash' },
                        { value: 'Transfer', label: 'Transfer' },
                        { value: 'QRIS', label: 'QRIS' },
                    ]"
                    label="label"
                    value-key="value"
                    placeholder="Select payment method"
                />

            </FormField>
            <!-- Payment Account -->

            <FormField
                label="Payment Account"
                required
                :error="
                    form.errors.payment_account_id
                "
            >

                <SearchableSelect
                    v-model="form.payment_account_id"
                    :options="paymentAccounts"
                    label="label"
                    value-key="id"
                    placeholder="Select Payment Account"
                />

            </FormField>

        </FormSection>


        <!-- ========================================================= -->
        <!-- Payment Details -->
        <!-- ========================================================= -->

        <FormSection
            icon="🧾"
            title="Payment Details"
            description="Select purchase invoices and enter the payment amount."
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
                            min-w-[1100px]
                            p-3
                        "
                    >

                        <!-- ============================================= -->
                        <!-- Header -->
                        <!-- ============================================= -->

                        <div
                            class="
                                grid
                                grid-cols-[minmax(260px,2fr)_140px_140px_160px_160px_260px_80px]
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
                                Purchase Invoice
                            </div>

                            <div class="text-right">
                                Invoice Amount
                            </div>

                            <div class="text-right">
                                Previous Paid
                            </div>

                            <div class="text-right">
                                Outstanding
                            </div>

                            <div>
                                Payment Amount
                            </div>

                            <div>
                                Remarks
                            </div>

                            <div class="text-center">
                                Action
                            </div>

                        </div>


                        <!-- ============================================= -->
                        <!-- Rows -->
                        <!-- ============================================= -->

                        <div
                            v-for="(
                                detail,
                                index
                            ) in form.details"

                            :key="index"

                            class="
                                grid
                                grid-cols-[minmax(260px,2fr)_140px_140px_160px_160px_260px_80px]
                                items-start
                                gap-2
                                border-b
                                border-gray-100
                                px-2
                                py-3
                                last:border-b-0
                            "
                        >

                            <!-- ========================================= -->
                            <!-- Invoice -->
                            <!-- ========================================= -->

                           <FormField
                                label="Purchase Invoice"
                                required
                                :error="
                                    form.errors[
                                        `details.${index}.purchase_invoice_header_id`
                                    ]
                                "
                            >

                                <SearchableSelect
                                    v-model="detail.purchase_invoice_header_id"
                                    :options="getAvailableInvoices(detail)"
                                    label="number"
                                    value-key="id"
                                    placeholder="Select purchase invoice"
                                    :disabled="
                                        !form.supplier_id ||
                                        !form.branch_id
                                    "
                                    @update:modelValue="
                                        selectInvoice(detail, $event)
                                    "
                                />

                            </FormField>


                            <!-- ========================================= -->
                            <!-- Invoice Amount -->
                            <!-- ========================================= -->

                            <FormField
                                label="Invoice Amount"
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
                                            detail.invoice_amount
                                        )
                                    }}

                                </div>

                            </FormField>


                            <!-- ========================================= -->
                            <!-- Previous Paid -->
                            <!-- ========================================= -->

                            <FormField
                                label="Previous Paid"
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
                                            detail.previous_paid_amount
                                        )
                                    }}

                                </div>

                            </FormField>


                            <!-- ========================================= -->
                            <!-- Outstanding -->
                            <!-- ========================================= -->

                            <FormField
                                label="Outstanding"
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
                                            detail.previous_outstanding_amount
                                        )
                                    }}

                                </div>

                            </FormField>


                            <!-- ========================================= -->
                            <!-- Payment Amount -->
                            <!-- ========================================= -->

                            <FormField
                                label="Payment Amount"
                                required
                                :error="
                                    form.errors[
                                        `details.${index}.payment_amount`
                                    ]
                                "
                            >

                                <FormInput
                                    v-model="
                                        detail.payment_amount
                                    "
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    placeholder="0"
                                />

                            </FormField>


                            <!-- ========================================= -->
                            <!-- Remarks -->
                            <!-- ========================================= -->

                            <FormField
                                label="Remarks"
                                :error="
                                    form.errors[
                                        `details.${index}.remarks`
                                    ]
                                "
                            >

                                <FormInput
                                    v-model="
                                        detail.remarks
                                    "
                                    placeholder="Optional"
                                />

                            </FormField>


                            <!-- ========================================= -->
                            <!-- Remove -->
                            <!-- ========================================= -->

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
                                    title="Remove invoice"
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


                <!-- ================================================= -->
                <!-- Add Invoice -->
                <!-- ================================================= -->

                <div class="flex justify-start">

                    <BaseButton
                        type="button"
                        variant="secondary"
                        @click="addDetail"
                    >

                        + Add Invoice

                    </BaseButton>

                </div>

            </div>

        </FormSection>


        <!-- ========================================================= -->
        <!-- Remarks -->
        <!-- ========================================================= -->

        <FormSection
            icon="📝"
            title="Remarks"
            description="Additional information about this purchase payment."
            :columns="1"
        >

            <FormField
                label="Remarks"
                :error="
                    form.errors.remarks
                "
            >

                <FormTextarea
                    v-model="form.remarks"
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
                        flex
                        justify-between
                        py-2
                        text-sm
                    "
                >

                    <span>
                        Total Invoices
                    </span>

                    <span class="font-medium">
                        {{ totalItems }}
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
                        Total Payment
                    </span>

                    <span>
                        {{ formatCurrency(totalPayment) }}
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