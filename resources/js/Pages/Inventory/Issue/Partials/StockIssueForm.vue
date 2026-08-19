<script setup>

import FormSection from '@/Components/Form/FormSection.vue'
import FormField from '@/Components/Form/FormField.vue'
import FormInput from '@/Components/Form/FormInput.vue'
import FormTextarea from '@/Components/Form/FormTextarea.vue'
import BaseButton from '@/Components/Button/BaseButton.vue'
import SearchableSelect from '@/Components/Form/SearchableSelect.vue'
import { DatePicker } from '@/Components/Form'
import { ref, computed, watch } from 'vue'
import { formatCurrency } from '@/Utils/currency'
import { error } from '@/Utils'
import FlatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'
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

    issueTypeOptions: {
        type: Array,
        default: () => [],
    },

    mode: {
        type: String,
        default: 'create',
    },

})

const form = props.form
console.log(
    'PROPS FORM:',
    props.form
)


console.log(
    'LOCAL FORM:',
    form
)

console.log(
    'LOCAL FROM WAREHOUSE:',
    form?.from_warehouse_id
)

console.log(
    'LOCAL TO WAREHOUSE:',
    form?.to_warehouse_id
)
const warehouseStocks = ref([])
const loadingStocks = ref(false)
const qtyExceedingAvailable = ref({})
const emit = defineEmits([
    'submit',
    'submitAndNew',
    'cancel',
])
const validateQty = (detail, index) => {

    const qty =
        Number(detail.qty ?? 0)

    if (
        !qty ||
        qty <= 0
    ) {

        qtyExceedingAvailable.value[index] =
            false

        return

    }


    const stock =
        getStock(
            detail.product_variant_id,
            detail.unit_id
        )


    const available =
        Number(
            stock?.available_qty ?? 0
        )


    if (
        qty <= available
    ) {

        qtyExceedingAvailable.value[index] =
            false

        return

    }


    qtyExceedingAvailable.value[index] =
        true


    error(
        `Stock tidak mencukupi. Available: ${available}. Qty transfer otomatis disesuaikan menjadi ${available}.`
    )


    detail.qty =
        available


    setTimeout(() => {

        qtyExceedingAvailable.value[index] =
            false

    }, 500)

}
/*
|--------------------------------------------------------------------------
| Detail
|--------------------------------------------------------------------------
*/

const createEmptyDetail = () => ({
    product_variant_id: null,

    unit_id: null,

    available_qty: 0,

    qty: 0,

    unit_cost: 0,

    total_cost: 0,

    description: null,
})


const addDetail = () => {

    form.details.push(
        createEmptyDetail()
    )

}


const removeDetail = (index) => {

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
| Calculation
|--------------------------------------------------------------------------
*/

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
            total +
            Number(
                detail.qty || 0
            ),
        0
    )

})


const totalCost = computed(() => {

    return form.details.reduce(
        (total, detail) =>
            total +
            detailTotal(detail),
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
const changeVariant = (
    detail
) => {

    detail.unit_id = null

    detail.qty = 0

    detail.unit_cost = 0

    if (
        !detail.product_variant_id
    ) {

        return

    }

    const units =
        getUnitsForVariant(
            detail.product_variant_id
        )

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
        updateStockInfo(
            detail
        )
    }

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
                'stock-issues.warehouse-stocks',
                warehouseId
            ),
            {
                headers: {
                    Accept:
                        'application/json',

                    'X-Requested-With':
                        'XMLHttpRequest',
                },
            }
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
            'STOCK Issue WAREHOUSE STOCKS:',
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
            'Failed to load stock issue warehouse stocks:',
            error
        )

    } finally {

        loadingStocks.value = false

    }

}

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
            ) ===
            Number(
                productVariantId
            )

            &&

            Number(
                stock.unit_id
            ) ===
            Number(
                unitId
            )

    ) ?? null

}
const updateStockInfo = (detail) => {

    const stock =
        getStock(
            detail.product_variant_id,
            detail.unit_id
        )

    if (!stock) {

        detail.available_qty = 0

        detail.unit_cost = 0

        detail.total_cost = 0

        return

    }

    detail.available_qty =
        Number(
            stock.available_qty
            ?? 0
        )

    detail.unit_cost =
        Number(
            stock.average_cost
            ?? 0
        )

    detail.total_cost =
        Number(
            detail.qty ?? 0
        ) *
        Number(
            detail.unit_cost ?? 0
        )

}
watch(
    () => form.warehouse_id,
    async (warehouseId) => {

        warehouseStocks.value = []

        if (!warehouseId) {

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

const filteredWarehouses = computed(() => {

    if (!form.branch_id) {

        return []

    }

    return props.warehouses.filter(
        warehouse =>
            Number(warehouse.branch_id) ===
            Number(form.branch_id)
    )

})
watch(
    () => form.branch_id,
    (newBranch, oldBranch) => {

        /*
        |--------------------------------------------------------------------------
        | Skip Initial Edit Hydration
        |--------------------------------------------------------------------------
        */

        if (
            props.mode === 'edit' &&
            oldBranch === null &&
            newBranch !== null
        ) {

            return

        }

        /*
        |--------------------------------------------------------------------------
        | No Change
        |--------------------------------------------------------------------------
        */

        if (
            newBranch === oldBranch
        ) {

            return

        }

        /*
        |--------------------------------------------------------------------------
        | Branch Changed By User
        |--------------------------------------------------------------------------
        */

        form.warehouse_id = null

        warehouseStocks.value = []

    }
)
</script>

<template>

    <form @submit.prevent="emit('submit')">
        <!-- ========================================================= -->
        <!-- Stock Issue Information -->
        <!-- ========================================================= -->

        <FormSection
            icon="📦"
            title="Stock Issue Information"
            description="Basic information about this Stock Issue transaction."
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

                <FlatPickr
                    v-model="form.transaction_date"
                    class="
                        w-full
                        rounded-lg
                        border
                        border-gray-300
                        px-3
                        py-2
                        focus:border-blue-500
                        focus:ring-1
                        focus:ring-blue-500
                    "
                    :config="{
                        dateFormat: 'Y-m-d',
                        altInput: true,
                        altFormat: 'd M Y',
                        allowInput: true,
                    }"
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
                    :disabled="!form.branch_id"
                />

            </FormField>


            <!-- Issue Type -->

            <FormField
                label="Issue Type"
                required
                :error="form.errors.issue_type"
            >

                <SearchableSelect
                    v-model="form.issue_type"
                    :options="issueTypeOptions"
                    label="label"
                    value-key="value"
                    placeholder="Select Issue Type"
                />

            </FormField>

        </FormSection>
        <!-- ========================================================= -->
        <!-- Stock Details -->
        <!-- ========================================================= -->

        <FormSection
            icon="📋"
            title="Stock Details"
            description="Select products and quantities to issue from the selected warehouse."
            :columns="1"
        >

            <div class="w-full overflow-x-auto">

                <div class="min-w-[1100px] space-y-4">

                    <!-- Desktop Header -->

                    <div
                        class="
                            hidden
                            lg:grid
                            lg:grid-cols-[2.5fr_1fr_1.1fr_1.1fr_1.3fr_1.4fr_auto]
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
                            Available
                        </div>

                        <div>
                            Issue Qty
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
                            lg:grid-cols-[2.5fr_1fr_1.1fr_1.1fr_1.3fr_1.4fr_auto]
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
                                :disabled="
                                    loadingStocks ||
                                    !form.warehouse_id
                                "
                                @update:modelValue="
                                    changeVariant(detail)
                                "
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
                                :disabled="
                                    !detail.product_variant_id
                                "
                                @update:modelValue="
                                    updateStockInfo(detail)
                                "
                            />

                        </FormField>


                        <!-- Available -->

                        <FormField
                            label="Available"
                        >

                            <FormInput
                                :model-value="
                                    formatCurrency(
                                        detail.available_qty
                                    )
                                "
                                readonly
                            />

                        </FormField>


                        <!-- Issue Qty -->

                        <FormField
                            label="Issue Qty"
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
                                min="0.01"
                                step="0.01"
                                placeholder="0"
                                :class="{
                                    'border-red-500 ring-1 ring-red-500':
                                        qtyExceedingAvailable[index]
                                }"
                                @change="
                                    validateQty(
                                        detail,
                                        index
                                    )
                                "
                            />

                        </FormField>


                        <!-- Unit Cost -->

                        <FormField
                            label="Unit Cost"
                        >

                            <FormInput
                                :model-value="
                                    formatCurrency(
                                        detail.unit_cost
                                    )
                                "
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
                                v-if="
                                    form.details.length > 1
                                "
                                type="button"
                                variant="danger"
                                @click="
                                    removeDetail(index)
                                "
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
            description="Additional information about this Stock Issue transaction."
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