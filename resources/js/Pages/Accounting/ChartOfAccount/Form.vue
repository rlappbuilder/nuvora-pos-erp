<script setup>

import FormSection from '@/Components/Form/FormSection.vue'
import FormField from '@/Components/Form/FormField.vue'
import FormInput from '@/Components/Form/FormInput.vue'
import FormTextarea from '@/Components/Form/FormTextarea.vue'
import SearchableSelect from '@/Components/Form/SearchableSelect.vue'
import CurrencyInput from '@/Components/Form/CurrencyInput.vue'
import FormCheckbox from '@/Components/Form/FormCheckbox.vue'
import BaseButton from '@/Components/Button/BaseButton.vue'
const props = defineProps({

    form: {

        type: Object,

        required: true,

    },

    companies: {

        type: Array,

        default: () => [],

    },

    branches: {

        type: Array,

        default: () => [],

    },

    accountCategories: {

        type: Array,

        default: () => [],

    },

    parentAccounts: {

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
<!-- Organization -->
<!-- ========================================================= -->

<FormSection

    icon="🏢"

    title="Organization"

    description="Assign company and branch."

    :columns="1"

>

    <FormField

        label="Company"

        required

        :error="form.errors.company_id"

    >

        <SearchableSelect

            v-model="form.company_id"

            :options="companies"

            :get-label="item => item.company_name"

            :get-value="item => item.id"

            placeholder="Select Company"

        />

    </FormField>

    

</FormSection>
<!-- ========================================================= -->
<!-- General Information -->
<!-- ========================================================= -->

<FormSection

    icon="ℹ️"

    title="General Information"

    description="Basic information for this Chart of Account."

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

    <!-- Account Name -->

    <FormField

        label="Account Name"

        required

        :error="form.errors.name"

    >

        <FormInput

            v-model="form.name"

            placeholder="Example: Cash On Hand"

        />

    </FormField>

    <!-- Category -->

    <FormField

        label="Category"

        required

        :error="form.errors.account_category_id"

    >

       <SearchableSelect
            v-model="form.account_category_id"
            :options="accountCategories"
            :get-label="item => item.name"
            :get-value="item => item.id"
            placeholder="Select Category"
        />
    </FormField>

    <!-- Parent Account -->

    <FormField

        label="Parent Account"

        :error="form.errors.parent_id"

    >

        <SearchableSelect
            v-model="form.parent_id"
            :options="parentAccounts"
            :get-label="item => `${item.code} - ${item.name}`"
            :get-value="item => item.id"
            placeholder="Select Parent Account"
        />

    </FormField>

</FormSection>
<!-- ========================================================= -->
<!-- Accounting Information -->
<!-- ========================================================= -->

<FormSection

    icon="📒"

    title="Accounting Information"

    description="Configure accounting settings."

    :columns="2"

>

    <!-- Normal Balance -->

    <FormField

        label="Normal Balance"

        required

        :error="form.errors.normal_balance"

    >

        <SearchableSelect

            v-model="form.normal_balance"

            :options="[

                {

                    id: 'Debit',

                    name: 'Debit'

                },

                {

                    id: 'Credit',

                    name: 'Credit'

                }

            ]"

            :get-label="item => item.name"

            :get-value="item => item.id"

            placeholder="Select Normal Balance"

        />

    </FormField>

    <!-- Opening Balance -->

    <FormField

        label="Opening Balance"

        :error="form.errors.opening_balance"

    >

        <CurrencyInput

            v-model="form.opening_balance"

            currency="IDR"

            placeholder="Example: 1000000"

        />

    </FormField>

</FormSection>
<!-- ========================================================= -->
<!-- Settings -->
<!-- ========================================================= -->

<FormSection

    icon="⚙️"

    title="Settings"

    description="Configure account behavior."

    :columns="1"

>

    <div

        class="
            grid
            grid-cols-1
            md:grid-cols-3
            gap-6
        "

    >

        <FormCheckbox

            v-model="form.is_header"

            label="Header Account"

            description="This account acts as a parent account."

            variant="switch"

        />

        <FormCheckbox

            v-model="form.allow_transaction"

            label="Allow Transaction"

            description="Allow journal transactions on this account."

            variant="switch"

        />

        <FormCheckbox

            v-model="form.is_active"

            label="Active"

            description="Enable or disable this account."

            variant="switch"

        />

    </div>

</FormSection>
<!-- ========================================================= -->
<!-- Description -->
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