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

import FlatPickr
    from 'vue-flatpickr-component'

import 'flatpickr/dist/flatpickr.css'


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

    mode: {

        type: String,

        default: 'create',

    },

})

</script>


<template>

    <!-- ========================================================= -->
    <!-- Organization -->
    <!-- ========================================================= -->

    <FormSection

        icon="🏢"

        title="Organization"

        description="Assign this fiscal year to a company."

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
    <!-- Fiscal Year Information -->
    <!-- ========================================================= -->

    <FormSection

        icon="📅"

        title="Fiscal Year Information"

        description="Define the fiscal year and its accounting period."

        :columns="2"

    >

        <!-- Fiscal Year -->

        <FormField

            label="Fiscal Year"

            required

            :error="form.errors.year"

        >

            <FormInput

                v-model="form.year"

                type="number"

                min="1900"

                max="9999"

                placeholder="Example: 2026"

            />

        </FormField>


        <!-- Status -->

        <FormField

            label="Status"

            required

            :error="form.errors.status"

        >

            <SearchableSelect

                v-model="form.status"

                :options="[

                    {
                        id: 'Open',
                        name: 'Open',
                    },

                    {
                        id: 'Closed',
                        name: 'Closed',
                    },

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


        <!-- Start Date -->

        <FormField

            label="Start Date"

            required

            :error="form.errors.start_date"

        >

            <FlatPickr

                v-model="form.start_date"

                :config="{

                    dateFormat: 'Y-m-d',

                }"

                class="
                    w-full
                    rounded-xl
                    border
                    border-gray-300
                    px-4
                    py-2.5
                    text-sm
                    focus:border-blue-500
                    focus:outline-none
                    focus:ring-2
                    focus:ring-blue-100
                "

                placeholder="Select start date"

            />

        </FormField>


        <!-- End Date -->

        <FormField

            label="End Date"

            required

            :error="form.errors.end_date"

        >

            <FlatPickr

                v-model="form.end_date"

                :config="{

                    dateFormat: 'Y-m-d',

                }"

                class="
                    w-full
                    rounded-xl
                    border
                    border-gray-300
                    px-4
                    py-2.5
                    text-sm
                    focus:border-blue-500
                    focus:outline-none
                    focus:ring-2
                    focus:ring-blue-100
                "

                placeholder="Select end date"

            />

        </FormField>

    </FormSection>


    <!-- ========================================================= -->
    <!-- Description -->
    <!-- ========================================================= -->

    <FormSection

        icon="📝"

        title="Description"

        description="Additional information about this fiscal year."

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