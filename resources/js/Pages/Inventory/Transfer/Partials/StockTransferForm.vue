<script setup>

import FormSection from '@/Components/Form/FormSection.vue'
import FormField from '@/Components/Form/FormField.vue'
import FormInput from '@/Components/Form/FormInput.vue'
import FormTextarea from '@/Components/Form/FormTextarea.vue'
import BaseButton from '@/Components/Button/BaseButton.vue'
import SearchableSelect from '@/Components/Form/SearchableSelect.vue'
//import axios from 'axios'
import {DatePicker,CurrencyInput,} from '@/Components/Form'
import { ref,computed,watch } from 'vue'
import { formatCurrency } from '@/Utils/currency'

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

    mode: {
        type: String,
        default: 'create',
    },

})


const form = props.form
console.log(
    '========== STOCK TRANSFER FORM INIT =========='
)

console.log(
    'PROPS FORM:',
    props.form
)

console.log(
    'PROPS FROM BRANCH:',
    props.form?.from_branch_id
)

console.log(
    'PROPS FROM WAREHOUSE:',
    props.form?.from_warehouse_id
)

console.log(
    'PROPS TO BRANCH:',
    props.form?.to_branch_id
)

console.log(
    'PROPS TO WAREHOUSE:',
    props.form?.to_warehouse_id
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
                'stock-transfers.warehouse-stocks',
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
            'STOCK TRANSFER WAREHOUSE STOCKS:',
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
            'Failed to load stock transfer warehouse stocks:',
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
        !form.from_warehouse_id ||
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

    console.log(
        '========== UPDATE STOCK TRANSFER =========='
    )

    console.log(
        'WAREHOUSE:',
        form.from_warehouse_id
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
        'WAREHOUSE STOCKS BEFORE GET:',
        JSON.parse(
            JSON.stringify(
                warehouseStocks.value
            )
        )
    )

    const stock = getStock(
        detail.product_variant_id,
        detail.unit_id
    )

    console.log(
        'MATCHED STOCK:',
        stock
    )

    if (!stock) {

        detail.available_qty = 0
        detail.unit_cost = 0

        return
    }

    detail.available_qty =
        Number(
            stock.on_hand_qty ?? 0
        )

    detail.unit_cost =
        Number(
            stock.average_cost ?? 0
        )

}
watch(
    () => form.from_warehouse_id,
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
watch(
    () => props.mode,
    async (mode) => {

        if (
            mode !== 'edit'
        ) {
            return
        }

        if (
            !form.from_warehouse_id
        ) {
            return
        }

        if (
            warehouseStocks.value.length
        ) {
            return
        }

        await loadWarehouseStocks(
            form.from_warehouse_id
        )

    },
    {
        immediate: true,
    }
)

/*
|--------------------------------------------------------------------------
| Warehouse Options
|--------------------------------------------------------------------------
*/

const fromWarehouses = computed(() => {

    console.log(
        'FROM FILTER:',
        {
            branch_id: form.from_branch_id,
            warehouses: props.warehouses,
        }
    )

    if (!form.from_branch_id) {
        return []
    }

    const result = props.warehouses.filter(
        warehouse =>
            Number(warehouse.branch_id) ===
            Number(form.from_branch_id)
    )

    console.log(
        'FROM FILTER RESULT:',
        result
    )

    return result
})

const toWarehouses = computed(() => {

    console.log(
        'TO FILTER:',
        {
            branch_id: form.to_branch_id,
            warehouses: props.warehouses,
        }
    )

    if (!form.to_branch_id) {
        return []
    }

    const result = props.warehouses.filter(
        warehouse =>
            Number(warehouse.branch_id) ===
            Number(form.to_branch_id)
    )

    console.log(
        'TO FILTER RESULT:',
        result
    )

    return result
})

/*
|--------------------------------------------------------------------------
| Branch → Warehouse
|--------------------------------------------------------------------------
*/

watch(
    () => form.from_branch_id,
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

        form.from_warehouse_id =
            null

        warehouseStocks.value =
            []

    }
)
watch(
    [
        () => form.from_branch_id,
        () => form.from_warehouse_id,
    ],
    async ([
        branchId,
        warehouseId,
    ]) => {

        if (
            !branchId ||
            !warehouseId
        ) {

            return

        }


        const warehouseExists =
            fromWarehouses.value.some(
                warehouse =>
                    Number(warehouse.id) ===
                    Number(warehouseId)
            )


        if (
            !warehouseExists
        ) {

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
    () => form.to_branch_id,
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

        form.to_warehouse_id =
            null

    }
)
watch(
    () => form.from_warehouse_id,
    value => {

        console.log(
            'EDIT FROM WAREHOUSE:',
            value,
            typeof value
        )

        console.log(
            'FROM WAREHOUSES:',
            fromWarehouses.value
        )

    },
    {
        immediate: true,
    }
)
watch(
    () => form.to_warehouse_id,
    value => {

        console.log(
            'EDIT TO WAREHOUSE:',
            value,
            typeof value
        )

        console.log(
            'TO WAREHOUSES:',
            toWarehouses.value
        )

    },
    {
        immediate: true,
    }
)
</script>

<template>

    <form @submit.prevent="emit('submit')">

        <!-- ========================================================= -->
        <!-- Stock Transfer Information -->
        <!-- ========================================================= -->

        <FormSection
            icon="📦"
            title="Stock Transfer Information"
            description="Basic information about this Stock Transfer transaction."
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


            <!-- From Branch -->

            <FormField
                label="From Branch"
                required
                :error="form.errors.from_branch_id"
            >

                <SearchableSelect
                    v-model="form.from_branch_id"
                    :options="branches"
                    label="label"
                    value-key="id"
                    placeholder="Select Source Branch"
                />

            </FormField>


            <!-- From Warehouse -->

            <FormField
                label="From Warehouse"
                required
                :error="form.errors.from_warehouse_id"
            >

            <SearchableSelect
                v-model="form.from_warehouse_id"
                :options="fromWarehouses"
                label="label"
                value-key="id"
                placeholder="Select Source Warehouse"
            />

            </FormField>


            <!-- To Branch -->

            <FormField
                label="To Branch"
                required
                :error="form.errors.to_branch_id"
            >

                <SearchableSelect
                    v-model="form.to_branch_id"
                    :options="branches"
                    label="label"
                    value-key="id"
                    placeholder="Select Destination Branch"
                />

            </FormField>


            <!-- To Warehouse -->

            <FormField
                label="To Warehouse"
                required
                :error="form.errors.to_warehouse_id"
            >

                <SearchableSelect
                    v-model="form.to_warehouse_id"
                    :options="toWarehouses"
                    label="label"
                    value-key="id"
                    placeholder="Select Destination Warehouse"
                />

            </FormField>

        </FormSection>
        <!-- ========================================================= -->
        <!-- Stock Details -->
        <!-- ========================================================= -->

        <FormSection
            icon="📋"
            title="Stock Details"
            description="Select products and quantities to transfer from the source warehouse."
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
                            Qty
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
                                :disabled="loadingStocks || !form.from_warehouse_id"
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
                            />

                        </FormField>


                        <!-- Available -->

                        <FormField
                            label="Available"
                        >
                        <FormInput
                            :model-value="detail.available_qty"
                            type="number"
                            readonly
                        />

                        </FormField>


                        <!-- Quantity -->

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
                                v-model="detail.qty"
                                type="number"
                                min="0.01"
                                step="0.01"
                                placeholder="0"
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
                                        (
                                            Number(detail.qty ?? 0)
                                            *
                                            Number(
                                                getStock(
                                                    detail.product_variant_id,
                                                    detail.unit_id
                                                )?.average_cost ?? 0
                                            )
                                        )
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
            description="Additional information about this Stock Transfer transaction."
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