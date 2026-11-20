<script setup>
import FormSection from '@/Components/Form/FormSection.vue'
import FormField from '@/Components/Form/FormField.vue'
import FormInput from '@/Components/Form/FormInput.vue'
import FormTextarea from '@/Components/Form/FormTextarea.vue'
import FormCheckbox from '@/Components/Form/FormCheckbox.vue'
import BaseButton from '@/Components/Button/BaseButton.vue'

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
        <!-- Category Information -->
        <!-- ========================================================= -->

        <FormSection
            icon="📂"
            title="Category Information"
            description="Basic information for this category."
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

            <!-- Category Name -->

            <FormField
                label="Category Name"
                required
                :error="form.errors.name"
            >
                <FormInput
                    v-model="form.name"
                    placeholder="Example: Cotton Fabric"
                />
            </FormField>

            <!-- Category Type -->

            <FormField
                label="Category Type"
                required
                :error="form.errors.category_type"
            >
                <select
                    v-model="form.category_type"
                    required
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                >
                    <option value="" disabled>
                        Select Category Type
                    </option>

                    <option value="raw_material">
                        Raw Material
                    </option>

                    <option value="finished_goods">
                        Finished Goods
                    </option>
                </select>
            </FormField>

        </FormSection>

        <!-- ========================================================= -->
        <!-- Description -->
        <!-- ========================================================= -->

        <FormSection
            icon="📝"
            title="Description"
            description="Additional information about this category."
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
        <!-- Settings -->
        <!-- ========================================================= -->

        <FormSection
            icon="⚙️"
            title="Settings"
            description="Configure category settings."
            :columns="1"
        >
            <FormCheckbox
                v-model="form.is_active"
                label="Active"
                description="Enable or disable this category."
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