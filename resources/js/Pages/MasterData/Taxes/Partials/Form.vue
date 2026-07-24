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
        title="Tax Information"
        description="Basic information for this Tax."
        :columns="2"
    >

        <!-- Code -->

        <FormField
            label="Code"
            :error="form.errors.code"
        >

            <FormInput
                v-model="form.code"
                readonly
            />

        </FormField>

            <!-- Tax Name -->

            <FormField
                label="Tax Name"
                required
                :error="form.errors.name"
            >

                <FormInput
                    v-model="form.name"
                    placeholder="Example: Ppn,VAT dll.."
                />

            </FormField>
            <FormField
                label="Type"
                required
                :error="form.errors.type"
                >
            <SearchableSelect
                v-model="form.type"
                label="label"
                value-key="value"
                :options="[
                    { label: 'Percentage', value: 'Percentage' },
                    { label: 'Fixed', value: 'Fixed' },
                ]"
            />

            </FormField>

            <FormField
                label="Rate"
                required
                :error="form.errors.rate"
            >

                <FormInput
                    v-model="form.rate"
                    type="number"
                    min="0"
                    step="0.01"
                    placeholder="11.00"
                />

            </FormField>
        </FormSection>
        <!-- ========================================================= -->
        <!-- Settings -->
        <!-- ========================================================= -->

        <FormSection
            icon="📝"
            title="Description"
            description="Additional information about this account."
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
            description="Configure Tax settings."
            :columns="1"
        >

            <FormCheckbox
            v-model="form.is_default"
            label="Default Tax"
            description="Use this tax as the default tax."
            variant="switch"
            />

            <FormCheckbox
                v-model="form.is_active"
                label="Active"
                description="Enable or disable this tax."
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
