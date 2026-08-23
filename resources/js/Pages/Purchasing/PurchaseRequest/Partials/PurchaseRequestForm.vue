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

    priorities: {
        type: Array,
        default: () => [],
    },

    mode: {
        type: String,
        default: 'create',
    },

})


const form = props.form

const emit = defineEmits([
    'submit',
    'submitAndNew',
    'cancel',
])

const handleSubmit = () => {

    console.log('PURCHASE REQUEST FORM SUBMIT')

    emit('submit')

}


/*
|--------------------------------------------------------------------------
| Detail
|--------------------------------------------------------------------------
*/

const addDetail = () => {

    form.details.push({

        product_variant_id:
            null,

        unit_id:
            null,

        qty:
            1,

        description:
            null,

    })

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

    /*
    |--------------------------------------------------------------------------
    | Variant Cleared
    |--------------------------------------------------------------------------
    */

    if (
        !detail.product_variant_id
    ) {

        detail.unit_id =
            null

        detail.qty =
            0

        detail.description =
            null

        return

    }


    /*
    |--------------------------------------------------------------------------
    | Variant Changed
    |--------------------------------------------------------------------------
    */

    detail.unit_id =
        null

    detail.qty =
        0


    /*
    |--------------------------------------------------------------------------
    | Get Variant Units
    |--------------------------------------------------------------------------
    */

    const units =
        getUnitsForVariant(
            detail.product_variant_id
        )


    const defaultUnit =
        units.find(
            unit =>
                unit.is_default
        )


    /*
    |--------------------------------------------------------------------------
    | Set Default Unit
    |--------------------------------------------------------------------------
    */

    if (defaultUnit) {

        detail.unit_id =
            defaultUnit.id

    }

}

</script>


<template>

    <form @submit.prevent="handleSubmit">

        <!-- ========================================================= -->
        <!-- Purchase Request Information -->
        <!-- ========================================================= -->

        <FormSection
            icon="📋"
            title="Purchase Request Information"
            description="Basic information about this purchase request."
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


            <!-- Request Date -->

                
                <FormField
                label="Request Date"
                required
                :error="form.errors.request_date"
            >
                <FlatPickr
                    v-model="form.request_date"
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
                    required
                    :error="form.errors.required_date"
                >
                    <FlatPickr
                        v-model="form.required_date"
                        class="
                            w-full
                            rounded-lg
                            border
                            px-3
                            py-2
                        "
                    />
                </FormField>

          


            <!-- Priority -->

            <FormField
                label="Priority"
                required
                :error="
                    form.errors.priority
                "
            >

                <SearchableSelect
                    v-model="form.priority"
                    :options="priorities"
                    label="label"
                    value-key="value"
                    placeholder="Select Priority"
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

        </FormSection>


        <!-- ========================================================= -->
        <!-- Request Details -->
        <!-- ========================================================= -->

        <FormSection
            icon="📦"
            title="Request Details"
            description="Add products and define the requested quantities."
            :columns="1"
        >

            <div class="space-y-4">

                <!-- Desktop Header -->

                <div
                    class="
                        hidden
                        lg:grid
                        lg:grid-cols-[2fr_1fr_1fr_auto]
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

                    <div></div>

                </div>


                <!-- Detail Rows -->

                <div
                    v-for="
                        (detail, index)
                        in form.details
                    "
                    :key="index"
                    class="
                        rounded-xl
                        border
                        border-gray-200
                        p-4
                        space-y-4
                        lg:grid
                        lg:grid-cols-[2fr_1fr_1fr_auto]
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
                            v-model="
                                detail.product_variant_id
                            "
                            :options="
                                filteredVariants
                            "
                            label="label"
                            value-key="id"
                            placeholder="Select Variant"
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
                            v-model="
                                detail.qty
                            "
                            type="number"
                            min="0"
                            step="0.01"
                            placeholder="0"
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

                <div
                    class="flex justify-start"
                >

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
            description="Additional information about this purchase request."
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
                        flex
                        justify-between
                        py-2
                        text-sm
                    "
                >

                    <span>
                        Total Items
                    </span>

                    <span
                        class="font-medium"
                    >
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

                    <span
                        class="font-medium"
                    >
                        {{ totalQuantity }}
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