<script setup>
import FormSection from '@/Components/Form/FormSection.vue'
import FormField from '@/Components/Form/FormField.vue'
import FormInput from '@/Components/Form/FormInput.vue'
import FormTextarea from '@/Components/Form/FormTextarea.vue'
import FormCheckbox from '@/Components/Form/FormCheckbox.vue'
import BaseButton from '@/Components/Button/BaseButton.vue'
import SearchableSelect from '@/Components/Form/SearchableSelect.vue'
import AutoGenerateInput from '@/Components/Form/AutoGenerateInput.vue'
const props = defineProps({

    form: {
        type: Object,
        required: true,
    },

    mode: {
        type: String,
        default: 'create',
    },

    categories: {
        type: Array,
        default: () => [],
    },

    brands: {
        type: Array,
        default: () => [],
    },

    units: {
        type: Array,
        default: () => [],
    },

    previewCode: {
        type: String,
        default: '',
    },

})

const emit = defineEmits([
    'submit',
    'submitAndNew',
    'cancel',
])
</script>
<template>
<form @submit.prevent="emit('submit')">
        <!-- ========================================================= -->
    <!-- Product Information -->
    <!-- ========================================================= -->

        <FormSection
            icon="📦"
            title="Product Information"
            description="Basic information about this product."
            :columns="2"
        >
             <FormField
                label="Product Name"
                required
                :error="form.errors.name"
            >

                <FormInput
                    v-model="form.name"
                    placeholder="Product Name"
                />

            </FormField>

            
            <FormField label="Product Code">

                <FormInput
                   :model-value="
                        mode === 'edit'
                            ? form.code
                            : props.previewCode
                    "
                    readonly
                />

            </FormField>
             <FormField
                label="SKU"
                :error="form.errors.sku"
            >

               <AutoGenerateInput
                v-model="form.sku"
                :generate-route="route('products.generate-sku')"
                response-key="sku"
                placeholder="Generate or input SKU"
                :error="form.errors.sku"
            />

            </FormField>
            <FormField
                label="Brand"
                :error="form.errors.brand_id"
            >
                <SearchableSelect
                    v-model="form.brand_id"
                    :options="props.brands"
                    label="name"
                    value-key="id"
                    placeholder="Select Brand"
                />
            </FormField>

            <FormField
                label="Unit"
                required
                :error="form.errors.unit_id"
            >
                <SearchableSelect
                    v-model="form.unit_id"
                    :options="props.units"
                    label="name"
                    value-key="id"
                    placeholder="Select Unit"
                />
            </FormField>

            
           
            <FormField
                label="Category"
                required
                :error="form.errors.category_id"
            >
                <SearchableSelect
                    v-model="form.category_id"
                    :options="props.categories"
                    label="name"
                    value-key="id"
                    placeholder="Select Category"
                />
            </FormField>
            
            <FormField
                label="Product Type"
                required
                :error="form.errors.product_type"
            >

                <SearchableSelect
                    v-model="form.product_type"
                    :options="[
                        { label: 'Product', value: 'PRODUCT' },
                        { label: 'Service', value: 'SERVICE' },
                    ]"
                    label="label"
                    value-key="value"
                    placeholder="Select Product Type"
                />

            </FormField>
                <FormField
                    label="Minimum Stock"
                    :error="form.errors.minimum_stock"
                >
                    <FormInput
                        v-model="form.minimum_stock"
                        type="number"
                        min="0"
                        placeholder="0"
                    />
                    </FormField>
        </FormSection>
        <!-- inventory setting-->
         <FormSection
            icon="📦"
            title="Inventory Settings"
            description="Inventory configuration."
            :columns="1"
        >

           

            <div class="flex flex-col gap-4">

                <FormCheckbox
                    v-model="form.track_stock"
                    label="Track Stock"
                    description="Enable stock tracking."
                    variant="switch"
                />

                <FormCheckbox
                    v-model="form.is_sellable"
                    label="Sellable"
                    description="Allow this product to be sold."
                    variant="switch"
                />

                <FormCheckbox
                    v-model="form.is_purchasable"
                    label="Purchasable"
                    description="Allow this product to be purchased."
                    variant="switch"
                />

                <FormCheckbox
                    v-model="form.is_active"
                    label="Active"
                    description="Enable or disable this product."
                    variant="switch"
                />

            </div>

        </FormSection>
        <FormSection
            icon="📝"
            title="Description"
            description="Additional information about this product."
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
        <!-- Action -->
        <!-- ========================================================= -->

     <div
    class="
        flex
        items-center
        justify-end
        gap-3
        mt-8
        pt-6
        border-t
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
        {{ mode === 'edit' ? 'Update' : 'Save' }}
    </BaseButton>

    <BaseButton
        v-if="mode !== 'edit'"
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
