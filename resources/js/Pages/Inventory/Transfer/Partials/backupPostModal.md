<script setup>

import FormSection from '@/Components/Form/FormSection.vue'
import FormField from '@/Components/Form/FormField.vue'
import FormInput from '@/Components/Form/FormInput.vue'
import FormTextarea from '@/Components/Form/FormTextarea.vue'
import BaseButton from '@/Components/Button/BaseButton.vue'
import SearchableSelect from '@/Components/Form/SearchableSelect.vue'
import axios from 'axios'
import {DatePicker,CurrencyInput,} from '@/Components/Form'
import { formatCurrency } from '@/Utils/currency'
import { ref,computed,watch } from 'vue'


const props = defineProps({

    form: {
        type: Object,
        required: true,
    },

    branches: {
        type: Array,
        default: () => [],
    },

    filteredVariants: {
        type: Array,
        default: () => [],
    },

    

    filteredWarehouses: {
    type: Array,
    default: () => [],
    },

    mode: {
        type: String,
        default: 'create',
    },

})


const form = props.form
const warehouseStocks = ref([])
const loadingStocks = ref(false)
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

        system_qty: 0,
        actual_qty: 0,
        difference_qty: 0,

        unit_cost: 0,
        total_cost: 0,

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

const calculateDifference = (detail) => {

    return (
        Number(detail.actual_qty || 0) -
        Number(detail.system_qty || 0)
    )
}

const detailTotal = (detail) => {

    return (
        calculateDifference(detail) *
        Number(detail.unit_cost || 0)
    )
}

const totalItems = computed(() => {

    return form.details.length
})
const totalQuantity = computed(() => {

    return form.details.reduce(
        (total, detail) =>
            total +
            Math.abs(
                calculateDifference(detail)
            ),
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
const getUnitsForVariant = (variantId) => {

    if (!variantId) {
        return []
    }

    const variant = props.filteredVariants.find(
        item => Number(item.id) === Number(variantId)
    )

    return variant?.units ?? []
}
const changeVariant = (detail) => {

    /*
    |--------------------------------------------------------------------------
    | Reset Previous Variant Data
    |--------------------------------------------------------------------------
    */

    detail.unit_id = null

    detail.system_qty = 0

    detail.actual_qty = null

    detail.difference_qty = 0

    detail.unit_cost = 0

    detail.total_cost = 0


    /*
    |--------------------------------------------------------------------------
    | No Variant Selected
    |--------------------------------------------------------------------------
    */

    if (!detail.product_variant_id) {
        return
    }


    /*
    |--------------------------------------------------------------------------
    | Get Units For Variant
    |--------------------------------------------------------------------------
    */

    const units = getUnitsForVariant(
        detail.product_variant_id
    )


    /*
    |--------------------------------------------------------------------------
    | Find Default Unit
    |--------------------------------------------------------------------------
    */

    const defaultUnit = units.find(
        unit => unit.is_default
    )


    /*
    |--------------------------------------------------------------------------
    | No Default Unit
    |--------------------------------------------------------------------------
    */

    if (!defaultUnit) {
        return
    }


    /*
    |--------------------------------------------------------------------------
    | Set Default Unit
    |--------------------------------------------------------------------------
    */

    detail.unit_id =
        defaultUnit.id


    /*
    |--------------------------------------------------------------------------
    | Refresh Stock Information
    |--------------------------------------------------------------------------
    */

    updateStockInfo(detail)

}
const loadWarehouseStocks = async (warehouseId) => {

    warehouseStocks.value = []

    if (!warehouseId) {
        return
    }

    loadingStocks.value = true

    try {

        const response = await fetch(
            route(
                'inventory-adjustments.warehouse-stocks',
                warehouseId
            )
        )

        if (!response.ok) {
            throw new Error(
                `HTTP ${response.status}`
            )
        }

        const result =
            await response.json()

        warehouseStocks.value =
            result.data ?? []

        console.log(
            'warehouse stocks:',
            warehouseStocks.value
        )

        /*
        |--------------------------------------------------------------------------
        | Refresh Existing Details
        |--------------------------------------------------------------------------
        */

        form.details.forEach(detail => {

            if (
                detail.product_variant_id &&
                detail.unit_id
            ) {

                updateStockInfo(detail)

            }

        })

    } catch (error) {

        console.error(
            'Failed to load warehouse stocks:',
            error
        )

    } finally {

        loadingStocks.value = false

    }
}
const findStock = (detail) => {

    if (
        !form.warehouse_id ||
        !detail.product_variant_id ||
        !detail.unit_id
    ) {

        return null

    }

    return warehouseStocks.value.find(

        stock =>

            Number(stock.product_variant_id) ===
                Number(detail.product_variant_id)

            &&

            Number(stock.unit_id) ===
                Number(detail.unit_id)

    ) ?? null

}

watch(
    () => form.warehouse_id,
    async (warehouseId) => {

        warehouseStocks.value = []

        if (!warehouseId) {

            form.details.forEach(detail => {

                detail.system_qty = 0
                detail.unit_cost = 0

            })

            return
        }

        await loadWarehouseStocks(
            warehouseId
        )

    },
    {
        immediate: true,
    }
)
watch(
    () => props.mode,
    async (mode) => {

        if (
            mode !== 'edit'
        ) {
            return
        }

        if (
            !form.warehouse_id
        ) {
            return
        }

        if (
            warehouseStocks.value.length
        ) {
            return
        }

        await loadWarehouseStocks(
            form.warehouse_id
        )

    },
    {
        immediate: true,
    }
)
const getStock = (
    productVariantId,
    unitId
) => {

    if (
        !form.warehouse_id ||
        !productVariantId ||
        !unitId
    ) {
        return null
    }

    return warehouseStocks.value.find(
        stock =>
            Number(
                stock.product_variant_id
            ) === Number(productVariantId)
            &&
            Number(
                stock.unit_id
            ) === Number(unitId)
    ) ?? null
}
const updateStockInfo = (detail) => {

    const stock = getStock(
        detail.product_variant_id,
        detail.unit_id
    )

    console.log(
        '========== UPDATE STOCK =========='
    )

    console.log(
        'VARIANT:',
        detail.product_variant_id
    )

    console.log(
        'UNIT:',
        detail.unit_id
    )

    console.log(
        'MATCHED STOCK:',
        stock
    )

    if (!stock) {

        detail.system_qty = 0
        detail.unit_cost = 0

        console.log(
            'SET SYSTEM = 0'
        )

        return
    }

    detail.system_qty =
        Number(stock.on_hand_qty ?? 0)

    detail.unit_cost =
        Number(stock.average_cost ?? 0)

    console.log(
        'SET SYSTEM:',
        detail.system_qty
    )

    console.log(
        'SET COST:',
        detail.unit_cost
    )

}
</script>

<template>

    <form @submit.prevent="emit('submit')">

        <!-- ========================================================= -->
        <!-- Opening Stock Information -->
        <!-- ========================================================= -->

        <FormSection
            icon="📦"
            title="Inventory Adjustment Information"
            description="Basic information about this Inventory Adsjustment transaction."
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
                :options="filteredWarehouses"
                label="label"
                value-key="id"
                placeholder="Select Warehouse"
                @update:modelValue="loadWarehouseStocks"
            />

            </FormField>

        </FormSection>


        <!-- ========================================================= -->
        <!-- Stock Details -->
        <!-- ========================================================= -->

        <FormSection
            icon="📋"
            title="Stock Details"
            description="Add products and Adjust Actual Stock quantities and costs."
            :columns="1"
        >
<div class="w-full overflow-x-auto">

    <div class="min-w-[1100px] space-y-4">

        <!-- Desktop Header -->

        <div
            class="
                hidden
                lg:grid
                lg:grid-cols-[2.5fr_1fr_1fr_1fr_1fr_1.3fr_1.4fr_auto]
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
                System Quantity
            </div>

            <div>
                Actual Quantity
            </div>

            <div>
                Difference
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
                lg:grid-cols-[2.5fr_1fr_1fr_1fr_1fr_1.3fr_1.4fr_auto]
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
                    :disabled="loadingStocks"
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
                v-model="detail.unit_id"
                :options="
                    getUnitsForVariant(
                        detail.product_variant_id
                    )
                "
                label="label"
                value-key="id"
                placeholder="Select Unit"
                @update:modelValue="updateStockInfo(detail)"
            />
            </FormField>


            <!-- System Quantity -->

            <FormField
                label="System Qty"
                :error="
                    form.errors[
                        `details.${index}.system_qty`
                    ]
                "
            >

                <FormInput
                    :model-value="detail.system_qty"
                    type="number"
                    readonly
                />

            </FormField>


            <!-- Actual Quantity -->

            <FormField
                label="Actual Qty"
                required
                :error="
                    form.errors[
                        `details.${index}.actual_qty`
                    ]
                "
            >

                <FormInput
                    v-model="detail.actual_qty"
                    type="number"
                    min="0"
                    step="0.01"
                    placeholder="0"
                />

            </FormField>


            <!-- Difference -->

            <FormField
                label="Difference"
            >

                <FormInput
                    :model-value="
                        calculateDifference(detail)
                    "
                    readonly
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
                    readonly
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

</div>
           

        </FormSection>


        <!-- ========================================================= -->
        <!-- Description -->
        <!-- ========================================================= -->

        <FormSection
            icon="📝"
            title="Description"
            description="Additional information about this Inventory Adjustment transaction."
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