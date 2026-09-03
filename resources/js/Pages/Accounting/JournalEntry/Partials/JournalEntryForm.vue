<script setup>

import FormSection from '@/Components/Form/FormSection.vue'
import FormField from '@/Components/Form/FormField.vue'
import FormInput from '@/Components/Form/FormInput.vue'
import FormTextarea from '@/Components/Form/FormTextarea.vue'
import BaseButton from '@/Components/Button/BaseButton.vue'
import SearchableSelect from '@/Components/Form/SearchableSelect.vue'
import { computed, watch, onMounted } from 'vue'
import { formatCurrency } from '@/Utils/currency'
import FlatPickr from 'vue-flatpickr-component'
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

    branches: {
        type: Array,
        default: () => [],
    },

    accountingJournals: {
        type: Array,
        default: () => [],
    },

    fiscalYears: {
        type: Array,
        default: () => [],
    },

    accountingPeriods: {
        type: Array,
        default: () => [],
    },

    chartOfAccounts: {
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
| Form
|--------------------------------------------------------------------------
*/

const form = props.form


const emit = defineEmits([
    'submit',
    'submitAndNew',
    'cancel',
])


/*
|--------------------------------------------------------------------------
| Detail
|--------------------------------------------------------------------------
*/

const createEmptyLine = () => ({

    account_id: null,

    description: null,

    debit: 0,

    credit: 0,

})


const addLine = () => {

    form.lines.push(
        createEmptyLine()
    )

}


const removeLine = (index) => {

    if (
        form.lines.length <= 1
    ) {

        return

    }

    form.lines.splice(
        index,
        1
    )

}


/*
|--------------------------------------------------------------------------
| Debit / Credit
|--------------------------------------------------------------------------
*/

const updateDebit = (line) => {

    const debit =
        Number(line.debit ?? 0)

    if (debit > 0) {

        line.credit = 0

    }

}


const updateCredit = (line) => {

    const credit =
        Number(line.credit ?? 0)

    if (credit > 0) {

        line.debit = 0

    }

}


/*
|--------------------------------------------------------------------------
| Totals
|--------------------------------------------------------------------------
*/

const totalDebit = computed(() => {

    return form.lines.reduce(

        (total, line) =>

            total +
            Number(
                line.debit ?? 0
            ),

        0

    )

})


const totalCredit = computed(() => {

    return form.lines.reduce(

        (total, line) =>

            total +
            Number(
                line.credit ?? 0
            ),

        0

    )

})


const balanceDifference = computed(() => {

    return (
        totalDebit.value -
        totalCredit.value
    )

})


const isBalanced = computed(() => {

    return (
        form.lines.length > 0 &&
        Math.round(
            totalDebit.value * 100
        ) ===
        Math.round(
            totalCredit.value * 100
        ) &&
        totalDebit.value > 0
    )

})


/*
|--------------------------------------------------------------------------
| Fiscal Year
|--------------------------------------------------------------------------
*/

const currentYear = new Date()
    .getFullYear()


const currentDate = () => {

    return new Date()
        .toISOString()
        .slice(0, 10)

}


/*
|--------------------------------------------------------------------------
| Filtered Accounting Periods
|--------------------------------------------------------------------------
*/

const filteredAccountingPeriods = computed(() => {

    if (!form.fiscal_year_id) {

        return []

    }

    return props.accountingPeriods.filter(
        period =>
            Number(period.fiscal_year_id) ===
            Number(form.fiscal_year_id)
    )

})


/*
|--------------------------------------------------------------------------
| Default Fiscal Year
|--------------------------------------------------------------------------
*/

const setDefaultFiscalYear = () => {

    /*
    |--------------------------------------------------------------------------
    | Do not override existing value
    |--------------------------------------------------------------------------
    */

    if (form.fiscal_year_id) {

        return

    }


    /*
    |--------------------------------------------------------------------------
    | Find Current Year
    |--------------------------------------------------------------------------
    */

    const currentFiscalYear =
        props.fiscalYears.find(
            fiscalYear =>
                Number(fiscalYear.year) ===
                Number(currentYear)
        )


    if (currentFiscalYear) {

        form.fiscal_year_id =
            currentFiscalYear.id

    }

}


/*
|--------------------------------------------------------------------------
| Default Accounting Period
|--------------------------------------------------------------------------
*/

const setDefaultAccountingPeriod = () => {

    if (
        !form.fiscal_year_id ||
        !props.accountingPeriods.length
    ) {

        return

    }


    /*
    |--------------------------------------------------------------------------
    | Do not override existing valid period
    |--------------------------------------------------------------------------
    */

    const existingPeriod =
        props.accountingPeriods.find(
            period =>
                Number(period.id) ===
                Number(
                    form.accounting_period_id
                )
                &&
                Number(
                    period.fiscal_year_id
                ) ===
                Number(
                    form.fiscal_year_id
                )
        )


    if (existingPeriod) {

        return

    }


    /*
    |--------------------------------------------------------------------------
    | Resolve Entry Date
    |--------------------------------------------------------------------------
    */

    const entryDate =
        form.entry_date ||
        currentDate()


    /*
    |--------------------------------------------------------------------------
    | Find Period By Entry Date
    |--------------------------------------------------------------------------
    */

    const currentPeriod =
        props.accountingPeriods.find(
            period =>

                Number(
                    period.fiscal_year_id
                ) ===
                Number(
                    form.fiscal_year_id
                )

                &&

                String(
                    period.start_date
                ).slice(0, 10) <=
                entryDate

                &&

                String(
                    period.end_date
                ).slice(0, 10) >=
                entryDate
        )


    if (currentPeriod) {

        form.accounting_period_id =
            currentPeriod.id

    }

}


/*
|--------------------------------------------------------------------------
| Fiscal Year Watch
|--------------------------------------------------------------------------
*/

watch(
    () => form.fiscal_year_id,
    (newYear, oldYear) => {

        /*
        |--------------------------------------------------------------------------
        | Initial hydration
        |--------------------------------------------------------------------------
        */

        if (
            newYear === oldYear
        ) {

            return

        }


        /*
        |--------------------------------------------------------------------------
        | Check Existing Period
        |--------------------------------------------------------------------------
        */

        const periodStillValid =
            props.accountingPeriods.some(

                period =>

                    Number(period.id) ===
                    Number(
                        form.accounting_period_id
                    )

                    &&

                    Number(
                        period.fiscal_year_id
                    ) ===
                    Number(newYear)

            )


        if (
            !periodStillValid
        ) {

            form.accounting_period_id =
                null

        }


        /*
        |--------------------------------------------------------------------------
        | Resolve Period For Selected Fiscal Year
        |--------------------------------------------------------------------------
        */

        setDefaultAccountingPeriod()

    }
)


/*
|--------------------------------------------------------------------------
| Entry Date Watch
|--------------------------------------------------------------------------
*/

watch(
    () => form.entry_date,
    () => {

        /*
        |--------------------------------------------------------------------------
        | Only resolve automatically when period
        | is empty.
        |--------------------------------------------------------------------------
        */

        if (
            !form.accounting_period_id
        ) {

            setDefaultAccountingPeriod()

        }

    }
)


/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/

const submit = () => {

    emit('submit')

}


/*
|--------------------------------------------------------------------------
| Lifecycle
|--------------------------------------------------------------------------
*/

onMounted(() => {

    /*
    |--------------------------------------------------------------------------
    | Create Mode
    |--------------------------------------------------------------------------
    */

    if (
        props.mode === 'create'
    ) {

        setDefaultFiscalYear()

        setDefaultAccountingPeriod()

    }

})

</script>


<template>

    <form
        @submit.prevent="submit"
    >

        <!-- ========================================================= -->
        <!-- Journal Entry Information -->
        <!-- ========================================================= -->

        <FormSection
            icon="📒"
            title="Journal Entry Information"
            description="Basic information about this journal entry."
            :columns="2"
        >

            <!-- Number -->

            <FormField
                label="Number"
                :error="form.errors.number"
            >

                <FormInput
                    v-model="form.number"
                    readonly
                    placeholder="Auto generated"
                />

            </FormField>


            <!-- Entry Date -->

            <FormField
                label="Entry Date"
                required
                :error="form.errors.entry_date"
            >

                <FlatPickr
                    v-model="form.entry_date"
                    class="
                        w-full
                        rounded-lg
                        border
                        border-gray-300
                        px-3
                        py-2
                        focus:border-blue-500
                        focus:ring-1
                        focus:ring-blue-500
                    "
                    :config="{
                        dateFormat: 'Y-m-d',
                        altInput: true,
                        altFormat: 'd M Y',
                        allowInput: true,
                    }"
                />

            </FormField>


            <!-- Branch -->

            <FormField
                label="Branch"
                required
                :error="form.errors.branch_id"
            >

                <SearchableSelect
                    v-model="form.branch_id"
                    :options="branches"
                    label="label"
                    value-key="id"
                    placeholder="Select Branch"
                />

            </FormField>


            <!-- Accounting Journal -->

            <FormField
                label="Accounting Journal"
                required
                :error="
                    form.errors.accounting_journal_id
                "
            >

                <SearchableSelect
                    v-model="
                        form.accounting_journal_id
                    "
                    :options="accountingJournals"
                    label="label"
                    value-key="id"
                    placeholder="Select Journal"
                />

            </FormField>


            <!-- Fiscal Year -->

            <FormField
                label="Fiscal Year"
                required
                :error="
                    form.errors.fiscal_year_id
                "
            >

                <SearchableSelect
                    v-model="
                        form.fiscal_year_id
                    "
                    :options="fiscalYears"
                    label="label"
                    value-key="id"
                    placeholder="Select Fiscal Year"
                />

            </FormField>


            <!-- Accounting Period -->

            <FormField
                label="Accounting Period"
                required
                :error="
                    form.errors.accounting_period_id
                "
            >

                <SearchableSelect
                    v-model="
                        form.accounting_period_id
                    "
                    :options="
                        filteredAccountingPeriods
                    "
                    label="label"
                    value-key="id"
                    placeholder="Select Period"
                    :disabled="
                        !form.fiscal_year_id
                    "
                />

            </FormField>


            <!-- Reference -->

            <FormField
                label="Reference"
                :error="form.errors.reference"
            >

                <FormInput
                    v-model="form.reference"
                    placeholder="Optional reference"
                />

            </FormField>

        </FormSection>


        <!-- ========================================================= -->
        <!-- Journal Lines -->
        <!-- ========================================================= -->

        <FormSection
            icon="📋"
            title="Journal Lines"
            description="Enter the accounts and debit / credit amounts for this journal entry."
            :columns="1"
        >

            <div class="w-full overflow-x-auto">

                <div
                    class="
                        min-w-[1100px]
                        space-y-4
                    "
                >

                    <!-- Header -->

                    <div
                        class="
                            hidden
                            lg:grid
                            lg:grid-cols-[2fr_2fr_1.2fr_1.2fr_auto]
                            gap-3
                            px-3
                            text-sm
                            font-medium
                            text-gray-600
                        "
                    >

                        <div>
                            Account
                        </div>

                        <div>
                            Description
                        </div>

                        <div>
                            Debit
                        </div>

                        <div>
                            Credit
                        </div>

                        <div></div>

                    </div>


                    <!-- Lines -->

                    <div
                        v-for="(line, index) in form.lines"
                        :key="index"
                        class="
                            rounded-xl
                            border
                            border-gray-200
                            p-4
                            space-y-4
                            lg:grid
                            lg:grid-cols-[2fr_2fr_1.2fr_1.2fr_auto]
                            lg:gap-3
                            lg:items-start
                            lg:space-y-0
                        "
                    >

                        <!-- Account -->

                        <FormField
                            label="Account"
                            required
                            :error="
                                form.errors[
                                    `lines.${index}.account_id`
                                ]
                            "
                        >

                            <SearchableSelect
                                v-model="
                                    line.account_id
                                "
                                :options="
                                    chartOfAccounts
                                "
                                label="label"
                                value-key="id"
                                placeholder="Select Account"
                            />

                        </FormField>


                        <!-- Description -->

                        <FormField
                            label="Description"
                            :error="
                                form.errors[
                                    `lines.${index}.description`
                                ]
                            "
                        >

                            <FormInput
                                v-model="
                                    line.description
                                "
                                placeholder="Line description"
                            />

                        </FormField>


                        <!-- Debit -->

                        <FormField
                            label="Debit"
                            required
                            :error="
                                form.errors[
                                    `lines.${index}.debit`
                                ]
                            "
                        >

                            <FormInput
                                v-model="
                                    line.debit
                                "
                                type="number"
                                min="0"
                                step="0.01"
                                placeholder="0.00"
                                @input="
                                    updateDebit(line)
                                "
                            />

                        </FormField>


                        <!-- Credit -->

                        <FormField
                            label="Credit"
                            required
                            :error="
                                form.errors[
                                    `lines.${index}.credit`
                                ]
                            "
                        >

                            <FormInput
                                v-model="
                                    line.credit
                                "
                                type="number"
                                min="0"
                                step="0.01"
                                placeholder="0.00"
                                @input="
                                    updateCredit(line)
                                "
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
                                    form.lines.length > 1
                                "
                                type="button"
                                variant="danger"
                                @click="
                                    removeLine(index)
                                "
                            >
                                Remove
                            </BaseButton>

                        </div>

                    </div>


                    <!-- Add Line -->

                    <div class="flex justify-start">

                        <BaseButton
                            type="button"
                            variant="secondary"
                            @click="addLine"
                        >
                            + Add Line
                        </BaseButton>

                    </div>

                </div>

            </div>

        </FormSection>


        <!-- ========================================================= -->
        <!-- Description -->
        <!-- ========================================================= -->

        <FormSection
            icon="📝"
            title="Description"
            description="Additional information about this journal entry."
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

                <!-- Total Debit -->

                <div
                    class="
                        flex
                        justify-between
                        py-2
                        text-sm
                    "
                >

                    <span>
                        Total Debit
                    </span>

                    <span class="font-medium">
                        {{
                            formatCurrency(
                                totalDebit
                            )
                        }}
                    </span>

                </div>


                <!-- Total Credit -->

                <div
                    class="
                        flex
                        justify-between
                        py-2
                        text-sm
                    "
                >

                    <span>
                        Total Credit
                    </span>

                    <span class="font-medium">
                        {{
                            formatCurrency(
                                totalCredit
                            )
                        }}
                    </span>

                </div>


                <!-- Balance -->

                <div
                    class="
                        mt-2
                        flex
                        justify-between
                        border-t
                        pt-3
                        text-base
                        font-semibold
                    "
                >

                    <span>
                        Balance
                    </span>

                    <span
                        :class="
                            isBalanced
                                ? 'text-green-600'
                                : 'text-red-600'
                        "
                    >
                        {{
                            formatCurrency(
                                balanceDifference
                            )
                        }}
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
                @click="emit('cancel')"
            >
                Cancel
            </BaseButton>


            <BaseButton
                type="submit"
                :loading="form.processing"
            >

                {{
                    mode === 'create'
                        ? 'Save'
                        : 'Update'
                }}

            </BaseButton>


            <BaseButton
                v-if="mode === 'create'"
                type="button"
                variant="success"
                :loading="form.processing"
                @click="
                    emit('submitAndNew')
                "
            >

                Save &amp; New

            </BaseButton>

        </div>

    </form>

</template>