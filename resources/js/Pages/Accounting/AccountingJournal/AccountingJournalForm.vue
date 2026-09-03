<script setup>

import FormSection
    from '@/Components/Form/FormSection.vue'

import FormInput
    from '@/Components/Form/FormInput.vue'

import FormTextarea
    from '@/Components/Form/FormTextarea.vue'

import SearchableSelect
    from '@/Components/Form/SearchableSelect.vue'

import FormField
    from '@/Components/Form/FormField.vue'

import BaseButton
    from '@/Components/Button/BaseButton.vue'


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    form: {

        type: Object,

        required: true,

    },

    companies: {

        type: Array,

        default: () => [],

    },

    coaAccounts: {

        type: Array,

        default: () => [],

    },

    mode: {

        type: String,

        default: 'create',

    },

})


/*
|--------------------------------------------------------------------------
| Options
|--------------------------------------------------------------------------
*/

const journalTypes = [

    {
        id: 'General',
        name: 'General',
    },

    {
        id: 'Sales',
        name: 'Sales',
    },

    {
        id: 'Purchase',
        name: 'Purchase',
    },

    {
        id: 'Cash',
        name: 'Cash',
    },

    {
        id: 'Bank',
        name: 'Bank',
    },

    {
        id: 'Adjustment',
        name: 'Adjustment',
    },

    {
        id: 'Opening',
        name: 'Opening',
    },

]


const accountRoles = [

    {
        id: 'default',
        name: 'Default',
    },

    {
        id: 'cash',
        name: 'Cash',
    },

    {
        id: 'bank',
        name: 'Bank',
    },

    {
        id: 'receivable',
        name: 'Receivable',
    },

    {
        id: 'payable',
        name: 'Payable',
    },

    {
        id: 'revenue',
        name: 'Revenue',
    },

    {
        id: 'expense',
        name: 'Expense',
    },

    {
        id: 'tax',
        name: 'Tax',
    },

]


const statusOptions = [

    {
        id: true,
        name: 'Active',
    },

    {
        id: false,
        name: 'Inactive',
    },

]


/*
|--------------------------------------------------------------------------
| Account Configuration
|--------------------------------------------------------------------------
*/

function addAccount()
{
    props.form.accounts.push({

        account_id: null,

        role: 'default',

        is_default: false,

    })
}


function removeAccount(index)
{
    props.form.accounts.splice(
        index,
        1
    )
}

</script>


<template>

    <!-- ========================================================= -->
    <!-- Organization -->
    <!-- ========================================================= -->

    <FormSection

        icon="🏢"

        title="Organization"

        description="Assign this accounting journal to a company."

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

    </FormSection>


    <!-- ========================================================= -->
    <!-- Journal Information -->
    <!-- ========================================================= -->

    <FormSection

        icon="📒"

        title="Journal Information"

        description="Define the accounting journal identity and behavior."

        :columns="2"

    >

        <!-- Code -->

        <FormField

            label="Code"

            required

            :error="form.errors.code"

        >

            <FormInput

                v-model="form.code"

                type="text"

                maxlength="30"

                placeholder="Example: GEN"

            />

        </FormField>


        <!-- Name -->

        <FormField

            label="Name"

            required

            :error="form.errors.name"

        >

            <FormInput

                v-model="form.name"

                type="text"

                maxlength="100"

                placeholder="Example: General Journal"

            />

        </FormField>


        <!-- Type -->

        <FormField

            label="Type"

            required

            :error="form.errors.type"

        >

            <SearchableSelect

                v-model="form.type"

                :options="journalTypes"

                :get-label="
                    item =>
                    item.name
                "

                :get-value="
                    item =>
                    item.id
                "

                placeholder="Select Journal Type"

            />

        </FormField>


        <!-- Status -->

        <FormField

            label="Status"

            required

            :error="form.errors.is_active"

        >

            <SearchableSelect

                v-model="form.is_active"

                :options="statusOptions"

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
    <!-- Account Configuration -->
    <!-- ========================================================= -->

    <FormSection

        icon="📚"

        title="Account Configuration"

        description="Configure the chart of accounts used by this journal."

        :columns="1"

    >

        <div class="space-y-4">

            <!-- Empty -->

            <div

                v-if="
                    !form.accounts?.length
                "

                class="
                    rounded-xl
                    border
                    border-dashed
                    border-gray-300
                    bg-gray-50
                    px-6
                    py-8
                    text-center
                "

            >

                <div
                    class="
                        text-sm
                        font-medium
                        text-gray-700
                    "
                >

                    No accounts configured

                </div>

                <div
                    class="
                        mt-1
                        text-xs
                        text-gray-500
                    "
                >

                    Add an account to configure this journal.

                </div>

            </div>


            <!-- Account Rows -->

            <div

                v-for="
                    (account, index)
                    in form.accounts
                "

                :key="index"

                class="
                    grid
                    grid-cols-1
                    gap-4
                    rounded-xl
                    border
                    border-gray-200
                    bg-white
                    p-4
                    md:grid-cols-[180px_1fr_auto]
                    md:items-end
                "

            >

                <!-- Role -->

                <FormField

                    label="Role"

                    :error="
                        form.errors[
                            `accounts.${index}.role`
                        ]
                    "

                >

                    <SearchableSelect

                        v-model="account.role"

                        :options="accountRoles"

                        :get-label="
                            item =>
                            item.name
                        "

                        :get-value="
                            item =>
                            item.id
                        "

                        placeholder="Select Role"

                    />

                </FormField>


                <!-- Account -->

                <FormField

                    label="Account"

                    required

                    :error="
                        form.errors[
                            `accounts.${index}.account_id`
                        ]
                    "

                >

                    <SearchableSelect

                        v-model="account.account_id"

                        :options="coaAccounts"

                        :get-label="
                            item =>
                                `${item.code} - ${item.name}`
                        "

                        :get-value="
                            item =>
                            item.id
                        "

                        placeholder="Select Account"

                    />

                </FormField>


                <!-- Remove -->

                <BaseButton

                    type="button"

                    variant="secondary"

                    @click="
                        removeAccount(index)
                    "

                >

                    Remove

                </BaseButton>

            </div>


            <!-- Add Account -->

            <div>

                <BaseButton

                    type="button"

                    variant="secondary"

                    @click="addAccount"

                >

                    + Add Account

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

        description="Additional information about this accounting journal."

        :columns="1"

    >

        <FormField

            label="Description"

            :error="form.errors.description"

        >

            <FormTextarea

                v-model="form.description"

                :rows="4"

                maxlength="1000"

                placeholder="Write additional notes..."

            />

        </FormField>

    </FormSection>

</template>