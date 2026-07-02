<script setup>

import FormSection from '@/Components/Form/FormSection.vue'

import FormInput from '@/Components/Form/FormInput.vue'

import CurrencyInput from '@/Components/Form/CurrencyInput.vue'

import FormTextarea from '@/Components/Form/FormTextarea.vue'

import SearchableSelect from '@/Components/Form/SearchableSelect.vue'

import FormField from '@/Components/Form/FormField.vue'

const props = defineProps({

    form: {

        type: Object,

        required: true

    },

    companies: {

        type: Array,

        default: () => []

    },

    branches: {

        type: Array,

        default: () => []

    },

    coaAccounts: {

        type: Array,

        default: () => []

    },

    mode: {

        type: String,

        default: 'create'

    }

})

</script>
<template>

        <!-- ========================================================= -->
        <!-- Organization -->
        <!-- ========================================================= -->

        <FormSection

            icon="🏢"

            title="Organization"

            description="Assign company and branch."

            :columns="2"

        >

            <FormField

                label="Company"

                required

                :error="form.errors.company_id"

            >

                <SearchableSelect

                    v-model="form.company_id"

                    :options="companies"

                    :get-label="

                        item =>

                        item.company_name

                    "

                    :get-value="

                        item =>

                        item.id

                    "

                    placeholder="Select Company"

                />

            </FormField>

            <FormField

                label="Branch"

                required

                :error="form.errors.branch_id"

            >

                <SearchableSelect

                    v-model="form.branch_id"

                    :options="branches"

                    :get-label="

                        item =>

                        item.name

                    "

                    :get-value="

                        item =>

                        item.id

                    "

                    placeholder="Select Branch"

                />

            </FormField>
                    
        </FormSection>
            <!-- ========================================================= -->
            <!-- General Information -->
            <!-- ========================================================= -->

            <FormSection

                icon="ℹ️"

                title="General Information"

                description="Basic information for this Cash & Bank account."

                :columns="2"

            >

                <FormField

                    label="Code"

                >

                    <FormInput

                        v-model="form.code"

                        readonly

                    />

                </FormField>

                <FormField

                    label="Account Name"

                    required

                    :error="form.errors.name"

                >

                    <FormInput

                        v-model="form.name"

                        placeholder="Example: Cashier Cash"

                    />

                </FormField>

                <FormField

                    label="Type"

                    required

                    :error="form.errors.type"

                >

                    <SearchableSelect

                        v-model="form.type"

                        :options="[

                            {

                                id: 'Cash',

                                name: 'Cash'

                            },

                            {

                                id: 'Bank',

                                name: 'Bank'

                            }

                        ]"

                        :get-label="

                            item =>

                            item.name

                        "

                        :get-value="

                            item =>

                            item.id

                        "

                        placeholder="Select Type"

                    />

                </FormField>

                <FormField

                    label="Status"

                    required

                    :error="form.errors.status"

                >

                    <SearchableSelect

                        v-model="form.status"

                        :options="[

                            {

                                id: true,

                                name: 'Active'

                            },

                            {

                                id: false,

                                name: 'Inactive'

                            }

                        ]"

                        :get-label="

                            item =>

                            item.name

                        "

                        :get-value="

                            item =>

                            item.id

                        "

                        placeholder="Select Status"

                    />

                </FormField>

            </FormSection>
            <!-- ========================================================= -->
            <!-- Bank Information -->
            <!-- ========================================================= -->

            <FormSection

                icon="🏦"

                title="Bank Information"

                description="Fill bank information if account type is Bank."

                :columns="2"

            >

                <!-- Bank Name -->

                <FormField

                    label="Bank Name"

                    :error="form.errors.bank_name"

                >

                    <FormInput

                        v-model="form.bank_name"

                        placeholder="Example: BCA"

                        :disabled="

                            form.type === 'Cash'

                        "

                    />

                </FormField>

                <!-- Bank Branch -->

                <FormField

                    label="Bank Branch"

                    :error="form.errors.bank_branch"

                >

                    <FormInput

                        v-model="form.bank_branch"

                        placeholder="Example: Bandung"

                        :disabled="

                            form.type === 'Cash'

                        "

                    />

                </FormField>

                <!-- Account Number -->

                <FormField

                    label="Account Number"

                    :error="form.errors.account_number"

                >

                    <FormInput

                        v-model="form.account_number"

                        placeholder="Example: 1234567890"

                        :disabled="

                            form.type === 'Cash'

                        "

                    />

                </FormField>

                <!-- Account Holder -->

                <FormField

                    label="Account Holder"

                    :error="form.errors.account_holder"

                >

                    <FormInput

                        v-model="form.account_holder"

                        placeholder="Example: PT Nuvora Technologies Indonesia"

                        :disabled="

                            form.type === 'Cash'

                        "

                    />

                </FormField>

            </FormSection>
        <!-- ========================================================= -->
        <!-- Accounting -->
        <!-- ========================================================= -->

        <FormSection

            icon="📒"

            title="Accounting"

            description="Accounting information and opening balance."

            :columns="2"

        >

            <!-- Chart Of Account -->

            <FormField

                label="Chart Of Account"

                required

                :error="form.errors.coa_id"

            >

                <SearchableSelect

                    v-model="form.coa_id"

                    :options="coaAccounts"

                    :get-label="

                        item =>

                        item.account_name

                    "

                    :get-value="

                        item =>

                        item.id

                    "

                    placeholder="Select Chart of Account"

                    readonly

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

                    placeholder="Example: 10000000"

                />

            </FormField>

        </FormSection>
        <!-- ========================================================= -->
        <!-- Description -->
        <!-- ========================================================= -->

        <FormSection

            icon="📝"

            title="Description"

            description="Additional information."

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
</template>