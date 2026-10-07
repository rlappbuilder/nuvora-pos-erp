<script setup>
import {
    computed,
    onMounted,
    reactive,
    watch,
} from 'vue'

import FormSection from '@/Components/Form/FormSection.vue'
import FormField from '@/Components/Form/FormField.vue'
import FormInput from '@/Components/Form/FormInput.vue'
import FormTextarea from '@/Components/Form/FormTextarea.vue'
import BaseButton from '@/Components/Button/BaseButton.vue'
import SearchableSelect from '@/Components/Form/SearchableSelect.vue'

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

    activeBranchId: {
        type: [Number, String, null],
        default: null,
    },

    mode: {
        type: String,
        default: 'create',
    },

    show: {
    type: Boolean,
    default: false,
},

})

const emit = defineEmits([
    'submit',
    'submitAndNew',
    'cancel',
])

/*
|--------------------------------------------------------------------------
| Amount Input Buffer
|--------------------------------------------------------------------------
|
| Jangan langsung format value ketika user sedang mengetik.
| Format hanya ketika blur.
|
*/

const amountInputs = reactive({})

/*
|--------------------------------------------------------------------------
| Line Key
|--------------------------------------------------------------------------
*/

const createLineKey = () => {
    return `${Date.now()}-${Math.random()
        .toString(36)
        .slice(2)}`
}

const ensureLineKey = (line) => {

    if (!line._key) {
        line._key = createLineKey()
    }

    return line._key
}

/*
|--------------------------------------------------------------------------
| Create Empty Line
|--------------------------------------------------------------------------
*/

const createEmptyLine = () => ({
    _key: createLineKey(),
    account_id: null,
    debit: 0,
    credit: 0,
})

/*
|--------------------------------------------------------------------------
| Amount Helpers
|--------------------------------------------------------------------------
*/

const amountKey = (line, type) => {

    ensureLineKey(line)

    return `${line._key}-${type}`
}

/*
|--------------------------------------------------------------------------
| Format Amount
|--------------------------------------------------------------------------
|
| Internal:
| 1000.20
|
| Display:
| 1.000,20
|
*/

const formatAmount = (value) => {

    const amount = Number(value ?? 0)

    if (!Number.isFinite(amount)) {
        return ''
    }

    return new Intl.NumberFormat(
        'id-ID',
        {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }
    ).format(amount)
}

/*
|--------------------------------------------------------------------------
| Parse Amount
|--------------------------------------------------------------------------
|
| Supported:
|
| 1000.20
| 1,000.20
| 1000,20
| 1.000,20
|
*/

const parseAmount = (value) => {

    if (
        value === null ||
        value === undefined ||
        value === ''
    ) {
        return 0
    }

    let input = String(value)
        .trim()
        .replace(/\s/g, '')

    if (!input) {
        return 0
    }

    /*
    |--------------------------------------------------------------------------
    | Dot + Comma
    |--------------------------------------------------------------------------
    */

    if (
        input.includes('.') &&
        input.includes(',')
    ) {

        const lastDot =
            input.lastIndexOf('.')

        const lastComma =
            input.lastIndexOf(',')

        /*
        | 1.000,20
        */

        if (lastComma > lastDot) {

            input = input
                .replace(/\./g, '')
                .replace(',', '.')

        }

        /*
        | 1,000.20
        */

        else {

            input = input
                .replace(/,/g, '')

        }

    }

    /*
    |--------------------------------------------------------------------------
    | Comma Only
    |--------------------------------------------------------------------------
    */

    else if (input.includes(',')) {

        const parts =
            input.split(',')

        const decimalPart =
            parts[parts.length - 1]

        /*
        | 1000,20
        */

        if (
            decimalPart &&
            decimalPart.length <= 2
        ) {

            input =
                input.replace(',', '.')

        }

        /*
        | 1,000
        */

        else {

            input =
                input.replace(/,/g, '')

        }

    }

    /*
    |--------------------------------------------------------------------------
    | Dot Only
    |--------------------------------------------------------------------------
    */

    else if (input.includes('.')) {

        const parts =
            input.split('.')

        const decimalPart =
            parts[parts.length - 1]

        /*
        | 1000.20
        */

        if (
            decimalPart &&
            decimalPart.length <= 2
        ) {

            // Keep decimal

        }

        /*
        | 1.000
        */

        else {

            input =
                input.replace(/\./g, '')

        }

    }

    const amount =
        Number(input)

    return Number.isFinite(amount)
        ? amount
        : 0
}

/*
|--------------------------------------------------------------------------
| Amount Input Focus
|--------------------------------------------------------------------------
*/

const focusAmount = (
    line,
    type,
    event
) => {

    ensureLineKey(line)

    const key =
        amountKey(line, type)

    const value =
        type === 'debit'
            ? line.debit
            : line.credit

    amountInputs[key] =
        value > 0
            ? String(value)
            : ''

    event.target.value =
        amountInputs[key]
}

/*
|--------------------------------------------------------------------------
| Debit Input
|--------------------------------------------------------------------------
*/

const updateDebit = (
    line,
    event
) => {

    ensureLineKey(line)

    const value =
        event.target.value

    const key =
        amountKey(line, 'debit')

    amountInputs[key] =
        value

    const amount =
        parseAmount(value)

    line.debit =
        amount

    if (amount > 0) {

        line.credit = 0

        amountInputs[
            amountKey(line, 'credit')
        ] = ''

    }

}

/*
|--------------------------------------------------------------------------
| Credit Input
|--------------------------------------------------------------------------
*/

const updateCredit = (
    line,
    event
) => {

    ensureLineKey(line)

    const value =
        event.target.value

    const key =
        amountKey(line, 'credit')

    amountInputs[key] =
        value

    const amount =
        parseAmount(value)

    line.credit =
        amount

    if (amount > 0) {

        line.debit = 0

        amountInputs[
            amountKey(line, 'debit')
        ] = ''

    }

}

/*
|--------------------------------------------------------------------------
| Amount Blur
|--------------------------------------------------------------------------
*/

const blurAmount = (
    line,
    type
) => {

    ensureLineKey(line)

    const key =
        amountKey(line, type)

    const value =
        type === 'debit'
            ? line.debit
            : line.credit

    amountInputs[key] =
        value > 0
            ? formatAmount(value)
            : ''

}

/*
|--------------------------------------------------------------------------
| Display Amount
|--------------------------------------------------------------------------
*/

const displayAmount = (
    line,
    type
) => {

    ensureLineKey(line)

    const key =
        amountKey(line, type)

    if (
        Object.prototype.hasOwnProperty.call(
            amountInputs,
            key
        )
    ) {
        return amountInputs[key]
    }

    const value =
        type === 'debit'
            ? line.debit
            : line.credit

    return value > 0
        ? formatAmount(value)
        : ''
}

/*
|--------------------------------------------------------------------------
| Lines
|--------------------------------------------------------------------------
*/

const lines = computed(() => {

    if (!Array.isArray(props.form.lines)) {
        props.form.lines = []
    }

    return props.form.lines

})

/*
|--------------------------------------------------------------------------
| Add Line
|--------------------------------------------------------------------------
*/

const addLine = () => {

    props.form.lines.push(
        createEmptyLine()
    )

}

/*
|--------------------------------------------------------------------------
| Remove Line
|--------------------------------------------------------------------------
*/

const removeLine = (index) => {

    if (
        props.form.lines.length <= 2
    ) {
        return
    }

    const line =
        props.form.lines[index]

    if (line?._key) {

        delete amountInputs[
            amountKey(line, 'debit')
        ]

        delete amountInputs[
            amountKey(line, 'credit')
        ]

    }

    props.form.lines.splice(
        index,
        1
    )

}

/*
|--------------------------------------------------------------------------
| Totals
|--------------------------------------------------------------------------
*/
const totalDebit = computed(() => {
    return lines.value.reduce(
        (total, line) => total + Number(line.debit || 0),
        0
    )
})

const totalCredit = computed(() => {
    return lines.value.reduce(
        (total, line) => total + Number(line.credit || 0),
        0
    )
})

const balance = computed(() => {
    return totalDebit.value - totalCredit.value
})

const isBalanced = computed(() => {
    return Math.abs(balance.value) < 0.01
})


/*
|--------------------------------------------------------------------------
| Fiscal Year
|--------------------------------------------------------------------------
*/
const setFiscalYearFromEntryDate = () => {

    if (!props.form.entry_date) {
        return
    }

    const year = new Date(
        props.form.entry_date
    ).getFullYear()

    const fiscalYear = props.fiscalYears.find(
        item => Number(item.year) === Number(year)
    )

    if (fiscalYear) {
        props.form.fiscal_year_id = fiscalYear.id
    }
}
/*
|--------------------------------------------------------------------------
| Accounting Periods
|--------------------------------------------------------------------------
*/

const filteredAccountingPeriods = computed(() => {

    if (!props.form.fiscal_year_id) {
        return []
    }

    return props.accountingPeriods.filter(
        period =>
            Number(period.fiscal_year_id) ===
            Number(props.form.fiscal_year_id)
    )

})
const activeBranchName = computed(() => {
    const branchId =
        props.form.branch_id ??
        props.activeBranchId

    const branch = props.branches.find(
        item => Number(item.id) === Number(branchId)
    )

    return branch?.label ?? ''
})

const fiscalYearLabel = computed(() => {
    const fiscalYear = props.fiscalYears.find(
        item => Number(item.id) === Number(props.form.fiscal_year_id)
    )

    return fiscalYear?.label ?? ''
})

const accountingPeriodLabel = computed(() => {
    const period = props.accountingPeriods.find(
        item => Number(item.id) === Number(props.form.accounting_period_id)
    )

    return period?.label ?? period?.name ?? ''
})
/*
|--------------------------------------------------------------------------
| Accounting Period
|--------------------------------------------------------------------------
*/

const setAccountingPeriodFromEntryDate = () => {

    const entryDate =
        String(
            props.form.entry_date ?? ''
        ).slice(0, 10)

    if (
        !entryDate ||
        !props.form.fiscal_year_id
    ) {
        props.form.accounting_period_id =
            null

        return
    }

    const period =
        filteredAccountingPeriods.value.find(
            item => {

                const start =
                    String(
                        item.start_date
                    ).slice(0, 10)

                const end =
                    String(
                        item.end_date
                    ).slice(0, 10)

                return (
                    entryDate >= start &&
                    entryDate <= end
                )

            }
        )

    props.form.accounting_period_id =
        period?.id ?? null

}

/*
|--------------------------------------------------------------------------
| Entry Date Change
|--------------------------------------------------------------------------
*/

const handleEntryDateChange = () => {

    setFiscalYearFromEntryDate()

    setAccountingPeriodFromEntryDate()

}

const setActiveBranch = () => {
    if (props.mode !== 'create') return

    const branchId = props.activeBranchId

    console.log('SET ACTIVE BRANCH:', branchId)

    if (!branchId) return

    props.form.branch_id = branchId

    console.log(
        'FORM BRANCH AFTER SET:',
        props.form.branch_id
    )

    const branch = props.branches.find(
        item => Number(item.id) === Number(branchId)
    )

    console.log(
        'FOUND BRANCH:',
        branch
    )

    if (branch?.company_id) {
        props.form.company_id = branch.company_id
    }
}

/*
|--------------------------------------------------------------------------
| Entry Date Watcher
|--------------------------------------------------------------------------
*/

watch(
    () => props.form.entry_date,
    () => {

        setFiscalYearFromEntryDate()

        setAccountingPeriodFromEntryDate()

    }
)

/*
|--------------------------------------------------------------------------
| Active Branch Watcher
|--------------------------------------------------------------------------
*/

watch(
    () => props.activeBranchId,
    () => {

        if (props.mode === 'create') {
            setActiveBranch()
        }

    },
    {
        immediate: true,
    }
)

/*
|--------------------------------------------------------------------------
| Mode Watcher
|--------------------------------------------------------------------------
*/

watch(
    () => props.mode,
    (mode) => {

        if (mode === 'create') {
            setActiveBranch()
        }

    }
)

/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/

const submit = () => {

    if (!isBalanced.value) {
        return
    }

    emit('submit')

}

/*
|--------------------------------------------------------------------------
| Submit & New
|--------------------------------------------------------------------------
*/

const submitAndNew = () => {

    if (!isBalanced.value) {
        return
    }

    emit('submitAndNew')

}

/*
|--------------------------------------------------------------------------
| Cancel
|--------------------------------------------------------------------------
*/

const cancel = () => {

    emit('cancel')

}

/*
|--------------------------------------------------------------------------
| Initialize Lines
|--------------------------------------------------------------------------
*/

onMounted(() => {
   console.log('ACTIVE BRANCH ID:', props.activeBranchId)
    console.log('BRANCHES:', props.branches)
    console.log('FISCAL YEARS:', props.fiscalYears)
    console.log('FORM:', props.form)
    if (
        !Array.isArray(props.form.lines)
    ) {
        props.form.lines = []
    }

    props.form.lines.forEach(
        line => ensureLineKey(line)
    )

    /*
    |--------------------------------------------------------------------------
    | Create Mode Defaults
    |--------------------------------------------------------------------------
    */

    if (props.mode === 'create') {

        if (!props.form.entry_date) {

            const today =
                new Date()

            const year =
                today.getFullYear()

            const month =
                String(
                    today.getMonth() + 1
                ).padStart(2, '0')

            const day =
                String(
                    today.getDate()
                ).padStart(2, '0')

            props.form.entry_date =
                `${year}-${month}-${day}`

        }

        setActiveBranch()

        setFiscalYearFromEntryDate()

        setAccountingPeriodFromEntryDate()

    }

})

/*
|--------------------------------------------------------------------------
| Expose Helpers To Template
|--------------------------------------------------------------------------
|
| Semua function di <script setup> otomatis
| tersedia di template.
|--------------------------------------------------------------------------
*/
</script>
<template>
    <Teleport to="body">

        <div
            v-if="show"
            class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 p-4"
            @click.self="cancel"
        >

            <div
                class="w-full max-w-5xl overflow-hidden rounded-2xl bg-white shadow-2xl"
            >

<!--
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
-->

<div
    class="border-b border-slate-100 px-6 py-4"
>

    <div
        class="flex items-center gap-5"
    >

        <!-- TITLE -->

        <div class="min-w-0 shrink-0">

            <h2
                class="text-lg font-semibold text-slate-900"
            >
                {{
                    mode === 'create'
                        ? 'New Journal Entry'
                        : 'Edit Journal Entry'
                }}
            </h2>

            <p
                class="mt-1 text-sm text-slate-500"
            >
                Record a manual accounting transaction
            </p>

        </div>


        <!-- TRANSACTION CONTEXT -->

        <div
            class="ml-auto flex items-start gap-5"
        >

            <!-- Branch -->

            <div class="min-w-[140px]">

                <label
                    class="block text-xs font-medium text-slate-500"
                >
                    Branch
                </label>

                <div
                    class="mt-1 text-sm font-semibold text-slate-800"
                >
                    {{ activeBranchName || '-' }}
                </div>

            </div>


            <!-- Fiscal Year -->

            <div class="min-w-[80px]">

                <label
                    class="block text-xs font-medium text-slate-500"
                >
                    Fiscal Year
                </label>

                <div
                    class="mt-1 text-sm font-semibold text-slate-800"
                >
                    {{ fiscalYearLabel || '-' }}
                </div>

            </div>


            <!-- Accounting Period -->

            <div class="min-w-[120px]">

                <label
                    class="block text-xs font-medium text-slate-500"
                >
                    Accounting Period
                </label>

                <div
                    class="mt-1 text-sm font-semibold text-slate-800"
                >
                    {{ accountingPeriodLabel || '-' }}
                </div>

            </div>

        </div>


        <!-- CLOSE -->

        <button
            type="button"
            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
            @click="cancel"
        >
            ×
        </button>

    </div>

</div>


                <!--
                |--------------------------------------------------------------------------
                | BODY
                |--------------------------------------------------------------------------
                -->

                <form
                    class="max-h-[calc(90vh-145px)] overflow-y-auto px-6 py-5"
                    @submit.prevent="submit"
                >

                    <!--
                    |--------------------------------------------------------------------------
                    | ENTRY INFORMATION
                    |--------------------------------------------------------------------------
                    -->

                    <div>

                        <div class="mb-4">

                            <h3
                                class="text-sm font-semibold text-slate-900"
                            >
                                Entry Information
                            </h3>

                            <p
                                class="mt-1 text-xs text-slate-500"
                            >
                                Basic information for this journal entry.
                            </p>

                        </div>


                        <div
                            class="grid grid-cols-1 gap-4 md:grid-cols-2"
                        >

                            <!-- Number -->

                            <div>

                                <label
                                    class="mb-1.5 block text-sm font-medium text-slate-700"
                                >
                                    Number
                                </label>

                                <FormInput
                                    v-model="form.code"
                                    readonly
                                    placeholder="Auto generated"
                                />

                            </div>


                            <!-- Entry Date -->

                            <div>

                                <label
                                    class="mb-1.5 block text-sm font-medium text-slate-700"
                                >
                                    Entry Date
                                    <span class="text-red-500">*</span>
                                </label>

                                <FlatPickr
                                    v-model="form.entry_date"
                                    :config="{
                                        dateFormat: 'Y-m-d',
                                        allowInput: true,
                                        clickOpens: true,
                                    }"
                                    class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                    @change="handleEntryDateChange"
                                />

                            </div>


                            <!-- Accounting Journal -->

                            <div>

                                <label
                                    class="mb-1.5 block text-sm font-medium text-slate-700"
                                >
                                    Accounting Journal
                                    <span class="text-red-500">*</span>
                                </label>

                                <SearchableSelect
                                    v-model="form.accounting_journal_id"
                                    :options="accountingJournals"
                                    label-key="name"
                                    value-key="id"
                                    placeholder="Select journal"
                                />

                            </div>


                            <!-- Reference -->

                            <div>

                                <label
                                    class="mb-1.5 block text-sm font-medium text-slate-700"
                                >
                                    Reference
                                </label>

                                <FormInput
                                    v-model="form.reference"
                                    placeholder="Optional reference"
                                />

                            </div>


                            <!-- Description -->

                            <div
                                class="md:col-span-2"
                            >

                                <label
                                    class="mb-1.5 block text-sm font-medium text-slate-700"
                                >
                                    Description
                                    <span class="text-red-500">*</span>
                                </label>

                                <FormInput
                                    v-model="form.description"
                                    placeholder="Describe this journal entry"
                                />

                            </div>

                        </div>

                    </div>


                    <!--
                    |--------------------------------------------------------------------------
                    | JOURNAL LINES
                    |--------------------------------------------------------------------------
                    -->

                    <div class="mt-6">

                        <div class="mb-4">

                            <h3
                                class="text-sm font-semibold text-slate-900"
                            >
                                Journal Lines
                            </h3>

                            <p
                                class="mt-1 text-xs text-slate-500"
                            >
                                Enter the accounts and amounts for this transaction.
                            </p>

                        </div>


                        <!-- TABLE -->

                        <div
                            class="overflow-x-auto rounded-xl border border-slate-200"
                        >

                            <table
                                class="w-full min-w-[720px] border-collapse text-sm"
                            >

                                <thead>

                                    <tr
                                        class="border-b border-slate-200 bg-slate-50"
                                    >

                                        <th
                                            class="px-3 py-3 text-left font-semibold text-slate-700"
                                        >
                                            Account
                                        </th>

                                        <th
                                            class="w-44 px-3 py-3 text-right font-semibold text-slate-700"
                                        >
                                            Debit
                                        </th>

                                        <th
                                            class="w-44 px-3 py-3 text-right font-semibold text-slate-700"
                                        >
                                            Credit
                                        </th>

                                        <th
                                            class="w-16 px-3 py-3 text-center font-semibold text-slate-700"
                                        >
                                            Action
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>

                                    <tr
                                        v-for="(
                                            line,
                                            index
                                        ) in lines"
                                        :key="line._key"
                                        class="border-b border-slate-100 last:border-b-0"
                                    >

                                        <!-- ACCOUNT -->

                                        <td class="px-3 py-2">

                                            <SearchableSelect
                                                v-model="line.account_id"
                                                :options="chartOfAccounts"
                                                label-key="name"
                                                value-key="id"
                                                placeholder="Select account"
                                            />

                                        </td>


                                        <!-- DEBIT -->

                                        <td class="px-3 py-2">

                                            <input
                                                type="text"
                                                inputmode="decimal"
                                                autocomplete="off"
                                                :value="
                                                    displayAmount(
                                                        line,
                                                        'debit'
                                                    )
                                                "
                                                placeholder="0,00"
                                                class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-right text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                                @focus="
                                                    focusAmount(
                                                        line,
                                                        'debit',
                                                        $event
                                                    )
                                                "
                                                @input="
                                                    updateDebit(
                                                        line,
                                                        $event
                                                    )
                                                "
                                                @blur="
                                                    blurAmount(
                                                        line,
                                                        'debit'
                                                    )
                                                "
                                            />

                                        </td>


                                        <!-- CREDIT -->

                                        <td class="px-3 py-2">

                                            <input
                                                type="text"
                                                inputmode="decimal"
                                                autocomplete="off"
                                                :value="
                                                    displayAmount(
                                                        line,
                                                        'credit'
                                                    )
                                                "
                                                placeholder="0,00"
                                                class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-right text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                                @focus="
                                                    focusAmount(
                                                        line,
                                                        'credit',
                                                        $event
                                                    )
                                                "
                                                @input="
                                                    updateCredit(
                                                        line,
                                                        $event
                                                    )
                                                "
                                                @blur="
                                                    blurAmount(
                                                        line,
                                                        'credit'
                                                    )
                                                "
                                            />

                                        </td>


                                        <!-- ACTION -->

                                        <td
                                            class="px-3 py-2 text-center"
                                        >

                                            <button
                                                type="button"
                                                :disabled="lines.length <= 2"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-red-500 transition hover:bg-red-50 hover:text-red-700 disabled:cursor-not-allowed disabled:opacity-30"
                                                title="Remove line"
                                                @click.stop="removeLine(index)"
                                            >

                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="2"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    class="h-4 w-4"
                                                >
                                                    <path d="M3 6h18" />
                                                    <path d="M8 6V4h8v2" />
                                                    <path d="M19 6l-1 14H6L5 6" />
                                                    <path d="M10 11v5" />
                                                    <path d="M14 11v5" />
                                                </svg>

                                            </button>

                                        </td>

                                    </tr>

                                </tbody>

                            </table>

                        </div>


                        <!-- ADD LINE -->

                        <div class="mt-3">

                            <button
                                type="button"
                                class="inline-flex items-center rounded-lg px-3 py-2 text-sm font-semibold text-blue-600 transition hover:bg-blue-50"
                                @click="addLine"
                            >
                                + Add Line
                            </button>

                        </div>


                        <!-- TOTALS -->

                        <div
                            class="mt-4 border-t border-slate-100 pt-4"
                        >

                            <div
                                class="flex items-center justify-end gap-6 text-sm"
                            >

                                <!-- TOTAL DEBIT -->

                                <div
                                    class="flex items-center gap-2"
                                >

                                    <span class="text-slate-500">
                                        Total Debit
                                    </span>

                                    <span
                                        class="font-semibold tabular-nums text-slate-800"
                                    >
                                        {{ formatAmount(totalDebit) }}
                                    </span>

                                </div>


                                <!-- TOTAL CREDIT -->

                                <div
                                    class="flex items-center gap-2"
                                >

                                    <span class="text-slate-500">
                                        Total Credit
                                    </span>

                                    <span
                                        class="font-semibold tabular-nums text-slate-800"
                                    >
                                        {{ formatAmount(totalCredit) }}
                                    </span>

                                </div>


                                <!-- BALANCE -->

                                <div
                                    class="flex items-center gap-2"
                                >

                                    <span class="text-slate-500">
                                        Balance
                                    </span>

                                    <span
                                        :class="
                                            isBalanced
                                                ? 'text-green-600'
                                                : 'text-red-600'
                                        "
                                        class="font-bold tabular-nums"
                                    >
                                        {{ formatAmount(Math.abs(balance)) }}
                                    </span>

                                </div>

                            </div>


                            <!-- STATUS -->

                            <div
                                class="mt-3 flex justify-end"
                            >

                                <span
                                    v-if="isBalanced"
                                    class="rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700"
                                >
                                    Balanced
                                </span>

                                <span
                                    v-else
                                    class="rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-700"
                                >
                                    Not Balanced
                                </span>

                            </div>

                        </div>

                    </div>

                </form>


                <!--
                |--------------------------------------------------------------------------
                | FOOTER
                |--------------------------------------------------------------------------
                -->

                <div
                    class="flex items-center justify-end gap-2 border-t border-slate-100 px-6 py-3"
                >

                    <button
                        type="button"
                        class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100"
                        @click="cancel"
                    >
                        Cancel
                    </button>


                    <button
                        type="button"
                        :disabled="!isBalanced"
                        class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
                        @click="submitAndNew"
                    >
                        Save & New
                    </button>


                    <button
                        type="button"
                        :disabled="!isBalanced"
                        class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-slate-300"
                        @click="submit"
                    >
                        {{
                            mode === 'create'
                                ? 'Save'
                                : 'Update'
                        }}
                    </button>

                </div>

            </div>

        </div>

    </Teleport>
</template>





