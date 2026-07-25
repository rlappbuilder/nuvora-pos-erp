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
    <!-- Tax Information -->
    <!-- ========================================================= -->

 
    <FormSection
        icon="📂"
        title="Product Attribute Information"
        description="Basic information about this Product Attribute."
        :columns="2"
    >

        <!-- Code -->

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
                label="Atribute Name"
                required
                :error="form.errors.name"
            >

                <FormInput
                    v-model="form.name"
                    placeholder="Color"
                />

            </FormField>
            <FormField
                label="Display Name"
                :error="form.errors.display_name"
            >
                <FormInput
                    v-model="form.display_name"
                    placeholder="Example: Product Color"
                />
            </FormField>
        <FormField
            label="Input Type"
            required
            :error="form.errors.input_type"
        >
            <SearchableSelect
                v-model="form.input_type"
                :options="[
                    { label: 'Text', value: 'Text' },
                    { label: 'Select', value: 'Select' },
                    { label: 'Radio', value: 'Radio' },
                    { label: 'Button', value: 'Button' },
                    { label: 'Color', value: 'Color' },
                ]"
                label="label"
                value-key="value"
                placeholder="Select Input Type"
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
            description="Additional information about this Product Attribute."
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
            description="Configure Product Attribute settings."
            :columns="1"
        >

           <FormCheckbox
                v-model="form.is_required"
                label="Required"
                description="Users must fill this attribute."
                variant="switch"
            />
            <FormCheckbox
                v-model="form.is_active"
                label="Active"
                description="Enable or disable this currency."
                variant="switch"
            />
            <FormCheckbox
                v-model="form.is_variant"
                label="Variant Attribute"
                description="Use this attribute to generate product variants."
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
