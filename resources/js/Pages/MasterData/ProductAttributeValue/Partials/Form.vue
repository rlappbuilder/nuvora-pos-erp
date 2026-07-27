<script setup>
import FormSection from '@/Components/Form/FormSection.vue'
import FormField from '@/Components/Form/FormField.vue'
import FormInput from '@/Components/Form/FormInput.vue'
import FormTextarea from '@/Components/Form/FormTextarea.vue'
import FormCheckbox from '@/Components/Form/FormCheckbox.vue'
import BaseButton from '@/Components/Button/BaseButton.vue'
import SearchableSelect from '@/Components/Form/SearchableSelect.vue'

const props = defineProps({
    form: {
        type: Object,
        required: true,
    },

    productAttributes: {
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
</script>
<template>
<form @submit.prevent="emit('submit')">
        <!-- ========================================================= -->
    <!-- PRoduct Attribute Value Information -->
    <!-- ========================================================= -->

 
    <FormSection
        icon="📂"
        title="Product Attribute Value Information"
        description="Basic information about this Product Attribute Value."
        :columns="2"
    >

        <!-- Code -->
         <FormField
                label="Product Attribute"
                required
                :error="form.errors.product_attribute_id"
            >
                <SearchableSelect
                    v-model="form.product_attribute_id"
                    :options="productAttributes"
                    label="name"
                    value-key="id"
                    placeholder="Select Product Attribute"
                />

            </FormField>
        <FormField
            label="Code"
            :error="form.errors.code"
        >

            <FormInput
                v-model="form.code"
                placeholder="example: COLOR, SIZE, MATERIAL, BRAND"
            />

        </FormField>

            <!-- Product Attribute Name -->

           
            <FormField
            label="Value"
            required
            :error="form.errors.value"
        >
            <FormInput
                v-model="form.value"
                placeholder="Example: Red"
            />
        </FormField>

        <FormField
            label="Display Value"
            :error="form.errors.display_value"
        >
            <FormInput
                v-model="form.display_value"
                placeholder="Example: Bright Red"
            />
        </FormField>
        <FormField
                label="Color Code"
                :error="form.errors.color_code"
            >
                <FormInput
                    v-model="form.color_code"
                    placeholder="#FF0000 ( Optional)"
                />
            </FormField>
            <FormField
                label="Sort Order"
                :error="form.errors.sort_order"
            >
                <FormInput
                    v-model="form.sort_order"
                    type="number"
                    min="0"
                />
            </FormField>
        </FormSection>
        <!-- ========================================================= -->
        <!-- Settings -->
        <!-- ========================================================= -->

        <FormSection
            icon="📝"
            title="Description"
            description="Additional information about this Product Attribute Value."
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


        <FormSection
            icon="⚙️"
            title="Settings"
            description="Configure Product Attribute Value settings."
            :columns="1"
        >

            <FormCheckbox
                v-model="form.is_active"
                label="Active"
                description="Enable or disable this Product Attribute Value."
                variant="switch"
            />
            
        </FormSection>
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
