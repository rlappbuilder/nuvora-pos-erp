<script setup>
import FormSection from '@/Components/Form/FormSection.vue'
import FormField from '@/Components/Form/FormField.vue'
import FormInput from '@/Components/Form/FormInput.vue'
import FormTextarea from '@/Components/Form/FormTextarea.vue'
import BaseButton from '@/Components/Button/BaseButton.vue'
import SearchableSelect from '@/Components/Form/SearchableSelect.vue'

import {
    DatePicker,
    CurrencyInput,
} from '@/Components/Form'

import { formatCurrency } from '@/Utils/currency'
import { computed } from 'vue'

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

    filteredVariants: {
        type: Array,
        default: () => [],
    },

    filteredUnits: {
        type: Array,
        default: () => [],
    },

    mode: {
        type: String,
        default: 'create',
    },
})

const emit = defineEmits([
    'submit',
    'submitAndNew',
    'cancel',
])

/*
|--------------------------------------------------------------------------
| Detail
|--------------------------------------------------------------------------
*/

const addDetail = () => {

    form.details.push({
        product_variant_id: null,
        unit_id: null,
        qty: 1,
        unit_cost: 0,
        description: null,
    })
}

const removeDetail = (index) => {

    if (form.details.length <= 1) {
        return
    }

    form.details.splice(index, 1)
}

/*
|--------------------------------------------------------------------------
| Calculation
|--------------------------------------------------------------------------
*/

const detailTotal = (detail) => {

    return (
        Number(detail.qty || 0) *
        Number(detail.unit_cost || 0)
    )
}

const totalItems = computed(() => {

    return form.details.length
})

const totalQuantity = computed(() => {

    return form.details.reduce(
        (total, detail) =>
            total + Number(detail.qty || 0),
        0
    )
})

const totalCost = computed(() => {

    return form.details.reduce(
        (total, detail) =>
            total + detailTotal(detail),
        0
    )
})
</script>

<template>

    <form @submit.prevent="emit('submit')">

        <!-- ========================================================= -->
        <!-- Opening Stock Information -->
        <!-- ========================================================= -->

        <FormSection
            icon="📦"
            title="Opening Stock Information"
            description="Basic information about this opening stock transaction."
            :columns="2"
        >

            <!-- Number -->

            <FormField
                label="Number"
                :error="form.errors.number"
            >

                <FormInput
                    v-model="form.number"
                    readonly
                    placeholder="Auto generated"
                />

            </FormField>


            <!-- Transaction Date -->

            <FormField
                label="Transaction Date"
                required
                :error="form.errors.transaction_date"
            >

                <DatePicker
                    v-model="form.transaction_date"
                />

            </FormField>


            <!-- Branch -->

            <FormField
                label="Branch"
                required
                :error="form.errors.branch_id"
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
                :error="form.errors.warehouse_id"
            >

                <SearchableSelect
                    v-model="form.warehouse_id"
                    :options="warehouses"
                    label="label"
                    value-key="id"
                    placeholder="Select Warehouse"
                />

            </FormField>

        </FormSection>


        <!-- ========================================================= -->
        <!-- Stock Details -->
        <!-- ========================================================= -->

        <FormSection
            icon="📋"
            title="Stock Details"
            description="Add products and define opening quantities and costs."
            :columns="1"
        >

            <div class="space-y-4">

                <!-- Desktop Header -->

                <div
                    class="
                        hidden
                        lg:grid
                        lg:grid-cols-[2fr_1fr_1fr_1.5fr_1.5fr_auto]
                        gap-3
                        px-3
                        text-sm
                        font-medium
                        text-gray-600
                    "
                >

                    <div>
                        Product Variant
                    </div>

                    <div>
                        Unit
                    </div>

                    <div>
                        Quantity
                    </div>

                    <div>
                        Unit Cost
                    </div>

                    <div>
                        Total Cost
                    </div>

                    <div></div>

                </div>


                <!-- Detail Rows -->

                <div
                    v-for="(detail, index) in form.details"
                    :key="index"
                    class="
                        rounded-xl
                        border
                        border-gray-200
                        p-4
                        space-y-4
                        lg:grid
                        lg:grid-cols-[2fr_1fr_1fr_1.5fr_1.5fr_auto]
                        lg:gap-3
                        lg:items-start
                        lg:space-y-0
                    "
                >

                    <!-- Product Variant -->

                    <FormField
                        label="Product Variant"
                        required
                        :error="
                            form.errors[
                                `details.${index}.product_variant_id`
                            ]
                        "
                    >

                        <SearchableSelect
                            v-model="detail.product_variant_id"
                            :options="filteredVariants"
                            label="label"
                            value-key="id"
                            placeholder="Select Variant"
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
                            v-model="detail.unit_id"
                            :options="filteredUnits"
                            label="label"
                            value-key="id"
                            placeholder="Select Unit"
                        />

                    </FormField>


                    <!-- Quantity -->

                    <FormField
                        label="Quantity"
                        required
                        :error="
                            form.errors[
                                `details.${index}.qty`
                            ]
                        "
                    >

                        <FormInput
                            v-model="detail.qty"
                            type="number"
                            min="0"
                            step="0.01"
                            placeholder="0"
                        />

                    </FormField>


                    <!-- Unit Cost -->

                    <FormField
                        label="Unit Cost"
                        required
                        :error="
                            form.errors[
                                `details.${index}.unit_cost`
                            ]
                        "
                    >

                        <CurrencyInput
                            v-model="detail.unit_cost"
                        />

                    </FormField>


                    <!-- Total Cost -->

                    <FormField
                        label="Total Cost"
                    >

                        <FormInput
                            :model-value="
                                formatCurrency(
                                    detailTotal(detail)
                                )
                            "
                            readonly
                        />

                    </FormField>


                    <!-- Remove -->

                    <div
                        class="
                            flex
                            items-center
                            justify-end
                            lg:pt-7
                        "
                    >

                        <BaseButton
                            v-if="form.details.length > 1"
                            type="button"
                            variant="danger"
                            @click="removeDetail(index)"
                        >
                            Remove
                        </BaseButton>

                    </div>

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

            </div>

        </FormSection>


        <!-- ========================================================= -->
        <!-- Description -->
        <!-- ========================================================= -->

        <FormSection
            icon="📝"
            title="Description"
            description="Additional information about this opening stock transaction."
            :columns="1"
        >

            <FormField
                label="Description"
                :error="form.errors.description"
            >

                <FormTextarea
                    v-model="form.description"
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
                        mt-2
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
        <!-- Action -->
        <!-- ========================================================= -->

        <div
            class="
                flex
                justify-end
                gap-3
                mt-8
            "
        >

            <BaseButton
                type="button"
                variant="secondary"
                @click="emit('cancel')"
            >
                Cancel
            </BaseButton>


            <BaseButton
                type="submit"
                :loading="form.processing"
            >

                {{ mode === 'create' ? 'Save' : 'Update' }}

            </BaseButton>


            <BaseButton
                v-if="mode === 'create'"
                type="button"
                variant="success"
                :loading="form.processing"
                @click="emit('submitAndNew')"
            >

                Save &amp; New

            </BaseButton>

        </div>

    </form>

</template>