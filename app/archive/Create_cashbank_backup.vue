<script setup>

import AuthenticatedLayout

    from '@/Layouts/AuthenticatedLayout.vue'

import {

    Head,

    Link,

    useForm

}

from '@inertiajs/vue3'

import {

    computed,

    watch

}

from 'vue'

import SearchableSelect

    from '@/Components/Form/SearchableSelect.vue'

import CurrencyInput

    from '@/Components/CurrencyInput.vue'
    


const props = defineProps({

    generatedCode: String,

    companies: Array,

    branches: Array,

})

const form = useForm({

    company_id: null,

    branch_id: null,

    code:

        props.generatedCode,

    name: '',

    type: 'Cash',

    bank_name: '',

    bank_branch: '',

    account_number: '',

    account_holder: '',

    opening_balance: 0,

    current_balance: 0,

    coa_id: '',

    description: '',

    status: true,

})

const typeOptions = [

    {

        id: 'Cash',

        name: 'Cash'

    },

    {

        id: 'Bank',

        name: 'Bank'

    }

]

const statusOptions = [

    {

        id: true,

        name: 'Active'

    },

    {

        id: false,

        name: 'Inactive'

    }

]

const companyOptions = computed(() =>

    props.companies.map(

        company => ({

            id: company.id,

            name: company.company_name

        })

    )

) 
const branchOptions = computed(

    () =>

        props.branches

            .filter(

                branch =>

                    !form.company_id ||

                    branch.company_id ==

                    form.company_id

            )

            .map(

                branch => ({

                    id: branch.id,

                    name: branch.name

                })

            )

)

const showBankSection = computed(

    () =>

        form.type === 'Bank'

)
watch(

    () => form.opening_balance,

    (

        value

    ) => {

        form.current_balance = value

    }

)
const submit = () => {

    form.post(

        route(

            'cash-banks.store'

        )

    )

}

</script>
<template>

    <Head

        title="Create Cash Bank"

    />

    <AuthenticatedLayout>

                <template #header>

                    <div

                        class="
                            flex
                            items-center
                            justify-between
                        "

                    >

                        <div>

                            <h2

                                class="
                                    text-2xl
                                    font-bold
                                    text-gray-800
                                "

                            >

                                Create Cash Bank

                            </h2>

                            <p

                                class="
                                    mt-1
                                    text-sm
                                    text-gray-500
                                "

                            >

                                Create a new Cash & Bank account.

                            </p>

                        </div>

                        <div

                            class="
                                flex
                                items-center
                                gap-3
                            "

                        >

                            <Link

                                :href="

                                    route(

                                        'cash-banks.index'

                                    )

                                "

                                class="
                                    rounded-xl
                                    border
                                    border-gray-300
                                    bg-white
                                    px-5
                                    py-2.5
                                    text-sm
                                    font-medium
                                    text-gray-700
                                    hover:bg-gray-100
                                "

                            >

                                Cancel

                            </Link>

                            <button

                                type="submit"

                                form="cash-bank-form"

                                :disabled="

                                    form.processing

                                "

                                class="
                                    rounded-xl
                                    bg-indigo-600
                                    px-6
                                    py-2.5
                                    text-sm
                                    font-semibold
                                    text-white
                                    hover:bg-indigo-700
                                    disabled:cursor-not-allowed
                                    disabled:opacity-50
                                "

                            >

                                Save

                            </button>

                        </div>

                    </div>

                </template>
            

                    <div

                        class="
                            py-8
                        "

                    >

                        <div

                            class="
                                mx-auto
                                max-w-7xl
                                px-6
                            "

                        >

                            <form

                                id="cash-bank-form"

                                @submit.prevent="

                                    submit

                                "

                            >
                                                <!-- Organization -->

                                                <div

                                                   class="
                                                    rounded-2xl
                                                    border
                                                    border-gray-200
                                                    bg-white
                                                    shadow-sm
                                                    overflow-visible
                                                    "

                                                >

                                                            <div

                                                                class="
                                                                    border-b
                                                                    border-gray-200
                                                                    bg-gray-50
                                                                    px-6
                                                                    py-4
                                                                "

                                                            >

                                                                <h3

                                                                    class="
                                                                        text-lg
                                                                        font-semibold
                                                                        text-gray-800
                                                                    "

                                                                >

                                                                    Organization

                                                                </h3>

                                                                <p

                                                                    class="
                                                                        mt-1
                                                                        text-sm
                                                                        text-gray-500
                                                                    "

                                                                >

                                                                    Assign this Cash & Bank account to a company and branch.

                                                                </p>

                                                            </div>

                                                            <div

                                                                class="
                                                                    grid
                                                                    grid-cols-1
                                                                    gap-6
                                                                    p-6
                                                                    md:grid-cols-2
                                                                    relative w-full
                                                                "

                                                            >

                                                                <!-- Company -->

                                                                <div >

                                                                    <label

                                                                        class="
                                                                            mb-2
                                                                            block
                                                                            text-sm
                                                                            font-medium
                                                                            text-gray-700
                                                                        "

                                                                    >

                                                                        Company

                                                                    </label>

                                                          <SearchableSelect

                                                                v-model="form.company_id"

                                                                :options="props.companies"

                                                                label="company_name"

                                                                valueKey="id"

                                                                placeholder="Select Company"

                                                            />

                                                                    <div

                                                                        v-if="

                                                                            form.errors.company_id

                                                                              "
                                                                            class="
                                                                                    mt-1
                                                                                    text-sm
                                                                                    text-red-500
                                                                                "
                                                                    >

                                                                        {{ form.errors.company_id }}

                                                                    </div>

                                                                </div>

                                                                <!-- Branch -->

                                                                <div>

                                                                    <label

                                                                        class="
                                                                            mb-2
                                                                            block
                                                                            text-sm
                                                                            font-medium
                                                                            text-gray-700
                                                                        "

                                                                    >

                                                                        Branch

                                                                    </label>

                                                                <SearchableSelect

                                                                    v-model="form.branch_id"

                                                                    :options="props.branches"

                                                                    label="name"

                                                                    valueKey="id"

                                                                    placeholder="Select Branch"

                                                                />

                                                                    <div

                                                                        v-if="

                                                                            form.errors.branch_id

                                                                        "

                                                                        class="
                                                                            mt-1
                                                                            text-sm
                                                                            text-red-500
                                                                        "

                                                                    >

                                                                        {{ form.errors.branch_id }}

                                                                    </div>

                                                                </div>

                                                        </div>

                                                </div>
                                    <!-- DIVE COMPLEETE-->

                                    <div

                                        class="mt-8"

                                    >

                                                       <!-- General Information -->

                                                            <div

                                                                class="
                                                                    overflow-visible
                                                                    rounded-2xl
                                                                    border
                                                                    border-gray-200
                                                                    bg-white
                                                                    shadow-sm
                                                                "

                                                            >

                                                                <div

                                                                    class="
                                                                        border-b
                                                                        border-gray-200
                                                                        bg-gray-50
                                                                        px-6
                                                                        py-4
                                                                    "

                                                                >

                                                                    <h3

                                                                        class="
                                                                            text-lg
                                                                            font-semibold
                                                                            text-gray-800
                                                                        "

                                                                    >

                                                                        General Information

                                                                    </h3>

                                                                    <p

                                                                        class="
                                                                            mt-1
                                                                            text-sm
                                                                            text-gray-500
                                                                        "

                                                                    >

                                                                        Basic information for this Cash & Bank account.

                                                                    </p>

                                                                </div>

                                                                <div

                                                                    class="
                                                                        grid
                                                                        grid-cols-1
                                                                        gap-6
                                                                        p-6
                                                                        md:grid-cols-2
                                                                    "

                                                                >

                                                                        <!-- Code -->

                                                                        <div>

                                                                            <label

                                                                                class="
                                                                                    mb-2
                                                                                    block
                                                                                    text-sm
                                                                                    font-medium
                                                                                    text-gray-700
                                                                                "

                                                                            >

                                                                                Code

                                                                            </label>

                                                                            <input

                                                                                :value="

                                                                                    form.code

                                                                                "

                                                                                readonly

                                                                                class="
                                                                                    w-full
                                                                                    rounded-xl
                                                                                    border
                                                                                    border-gray-300
                                                                                    bg-gray-100
                                                                                    px-4
                                                                                    py-2.5
                                                                                    text-gray-600
                                                                                "

                                                                            >

                                                                            <p

                                                                                class="
                                                                                    mt-1
                                                                                    text-xs
                                                                                    text-gray-400
                                                                                "

                                                                            >

                                                                                Generated automatically.

                                                                            </p>

                                                                        </div>

                                                                        <!-- Account Name -->

                                                                        <div>

                                                                            <label

                                                                                class="
                                                                                    mb-2
                                                                                    block
                                                                                    text-sm
                                                                                    font-medium
                                                                                    text-gray-700
                                                                                "

                                                                            >

                                                                                Account Name

                                                                                <span

                                                                                    class="text-red-500"

                                                                                >

                                                                                    *

                                                                                </span>

                                                                            </label>

                                                                            <input

                                                                                v-model="

                                                                                    form.name

                                                                                "

                                                                                type="text"

                                                                                class="
                                                                                    w-full
                                                                                    rounded-xl
                                                                                    border
                                                                                    border-gray-300
                                                                                    px-4
                                                                                    py-2.5
                                                                                    focus:border-indigo-500
                                                                                    focus:ring-indigo-500
                                                                                "

                                                                            >

                                                                            <div

                                                                                v-if="

                                                                                    form.errors.name

                                                                                "

                                                                                class="
                                                                                    mt-1
                                                                                    text-sm
                                                                                    text-red-500
                                                                                "

                                                                            >

                                                                                {{ form.errors.name }}

                                                                            </div>

                                                                        </div>

                                                                        <!-- Type -->

                                                                        <div>

                                                                            <label

                                                                                class="
                                                                                    mb-2
                                                                                    block
                                                                                    text-sm
                                                                                    font-medium
                                                                                    text-gray-700
                                                                                "

                                                                            >

                                                                                Type

                                                                            </label>

                                                                            <SearchableSelect

                                                                                v-model="

                                                                                    form.type

                                                                                "

                                                                                :options="

                                                                                    typeOptions

                                                                                "

                                                                                placeholder="Select Type"

                                                                            />

                                                                            <div

                                                                                v-if="

                                                                                    form.errors.type

                                                                                "

                                                                                class="
                                                                                    mt-1
                                                                                    text-sm
                                                                                    text-red-500
                                                                                "

                                                                            >

                                                                                {{ form.errors.type }}

                                                                            </div>

                                                                        </div>

                                                                        <!-- Status -->

                                                                        <div>

                                                                            <label

                                                                                class="
                                                                                    mb-2
                                                                                    block
                                                                                    text-sm
                                                                                    font-medium
                                                                                    text-gray-700
                                                                                "

                                                                            >

                                                                                Status

                                                                            </label>

                                                                            <SearchableSelect

                                                                                v-model="

                                                                                    form.status

                                                                                "

                                                                                :options="

                                                                                    statusOptions

                                                                                "

                                                                                placeholder="Select Status"

                                                                            />

                                                                            <div

                                                                                v-if="

                                                                                    form.errors.status

                                                                                "

                                                                                class="
                                                                                    mt-1
                                                                                    text-sm
                                                                                    text-red-500
                                                                                "

                                                                            >

                                                                                {{ form.errors.status }}

                                                                            </div>

                                                                        </div>

                                                                  </div>

                                                                 </div>
                                                             <!-- </div> -->   
                                                        <div

                                                            class="mt-8"

                                                        >
                                                                            <!-- Accounting Information -->

                                                                    <div

                                                                      class="
                                                                            rounded-2xl
                                                                            border
                                                                            border-gray-200
                                                                            bg-white
                                                                            shadow-sm
                                                                            overflow-visible
                                                                            "

                                                                    >

                                                                            <div

                                                                                class="
                                                                                    border-b
                                                                                    border-gray-200
                                                                                    bg-gray-50
                                                                                    px-6
                                                                                    py-4
                                                                                "

                                                                            >

                                                                                <h3

                                                                                    class="
                                                                                        text-lg
                                                                                        font-semibold
                                                                                        text-gray-800
                                                                                    "

                                                                                >

                                                                                    Accounting Information

                                                                                </h3>

                                                                                <p

                                                                                    class="
                                                                                        mt-1
                                                                                        text-sm
                                                                                        text-gray-500
                                                                                    "

                                                                                >

                                                                                    Link this Cash & Bank account to the Chart of Accounts.

                                                                                </p>

                                                                            </div>

                                                                            <div

                                                                                class="
                                                                                    p-6
                                                                                "

                                                                            >

                                                                                <label

                                                                                    class="
                                                                                        mb-2
                                                                                        block
                                                                                        text-sm
                                                                                        font-medium
                                                                                        text-gray-700
                                                                                    "

                                                                                >

                                                                                    COA Account

                                                                                </label>

                                                                                <SearchableSelect

                                                                                    disabled

                                                                                    :options="[]"

                                                                                    placeholder="Coming Soon"

                                                                                />

                                                                                <p

                                                                                    class="
                                                                                        mt-2
                                                                                        text-xs
                                                                                        text-gray-400
                                                                                    "

                                                                                >

                                                                                    This feature will be available after the
                                                                                    Chart of Accounts module is completed.

                                                                                </p>

                                                                            </div>

                                                                    </div>

                                                            <div
                                                                
                                                                class="mt-8"

                                                                >
                                                                                    <!-- Bank Information -->

                                                                                    <div

                                                                                        v-if="showBankSection"

                                                                                        class="
                                                                                                rounded-2xl
                                                                                                border
                                                                                                border-gray-200
                                                                                                bg-white
                                                                                                shadow-sm
                                                                                                overflow-visible
                                                                                                "

                                                                                    >

                                                                                        <div

                                                                                            class="
                                                                                                border-b
                                                                                                border-gray-200
                                                                                                bg-gray-50
                                                                                                px-6
                                                                                                py-4
                                                                                            "

                                                                                        >

                                                                                            <h3

                                                                                                class="
                                                                                                    text-lg
                                                                                                    font-semibold
                                                                                                    text-gray-800
                                                                                                "

                                                                                            >

                                                                                                Bank Information

                                                                                            </h3>

                                                                                            <p

                                                                                                class="
                                                                                                    mt-1
                                                                                                    text-sm
                                                                                                    text-gray-500
                                                                                                "

                                                                                            >

                                                                                                Enter bank account information.

                                                                                            </p>

                                                                                        </div>

                                                                                        <div

                                                                                            class="
                                                                                                grid
                                                                                                grid-cols-1
                                                                                                gap-6
                                                                                                p-6
                                                                                                md:grid-cols-2
                                                                                            "

                                                                                        >

                                                                                            <!-- Bank Name -->

                                                                                            <div>

                                                                                                <label

                                                                                                    class="
                                                                                                        mb-2
                                                                                                        block
                                                                                                        text-sm
                                                                                                        font-medium
                                                                                                        text-gray-700
                                                                                                    "

                                                                                                >

                                                                                                    Bank Name

                                                                                                </label>

                                                                                                <input

                                                                                                    v-model="

                                                                                                        form.bank_name

                                                                                                    "

                                                                                                    type="text"

                                                                                                    class="
                                                                                                        w-full
                                                                                                        rounded-xl
                                                                                                        border
                                                                                                        border-gray-300
                                                                                                        px-4
                                                                                                        py-2.5
                                                                                                        focus:border-indigo-500
                                                                                                        focus:ring-indigo-500
                                                                                                    "

                                                                                                >

                                                                                                <div

                                                                                                    v-if="

                                                                                                        form.errors.bank_name

                                                                                                    "

                                                                                                    class="
                                                                                                        mt-1
                                                                                                        text-sm
                                                                                                        text-red-500
                                                                                                    "

                                                                                                >

                                                                                                    {{ form.errors.bank_name }}

                                                                                                </div>

                                                                                            </div>

                                                                                            <!-- Bank Branch -->

                                                                                            <div>

                                                                                                <label

                                                                                                    class="
                                                                                                        mb-2
                                                                                                        block
                                                                                                        text-sm
                                                                                                        font-medium
                                                                                                        text-gray-700
                                                                                                    "

                                                                                                >

                                                                                                    Bank Branch

                                                                                                </label>

                                                                                                <input

                                                                                                    v-model="

                                                                                                        form.bank_branch

                                                                                                    "

                                                                                                    type="text"

                                                                                                    class="
                                                                                                        w-full
                                                                                                        rounded-xl
                                                                                                        border
                                                                                                        border-gray-300
                                                                                                        px-4
                                                                                                        py-2.5
                                                                                                        focus:border-indigo-500
                                                                                                        focus:ring-indigo-500
                                                                                                    "

                                                                                                >

                                                                                                <div

                                                                                                    v-if="

                                                                                                        form.errors.bank_branch

                                                                                                    "

                                                                                                    class="
                                                                                                        mt-1
                                                                                                        text-sm
                                                                                                        text-red-500
                                                                                                    "

                                                                                                >

                                                                                                    {{ form.errors.bank_branch }}

                                                                                                </div>

                                                                                            </div>

                                                                                            <!-- Account Number -->

                                                                                            <div>

                                                                                                <label

                                                                                                    class="
                                                                                                        mb-2
                                                                                                        block
                                                                                                        text-sm
                                                                                                        font-medium
                                                                                                        text-gray-700
                                                                                                    "

                                                                                                >

                                                                                                    Account Number

                                                                                                </label>

                                                                                                <input

                                                                                                    v-model="

                                                                                                        form.account_number

                                                                                                    "

                                                                                                    type="text"

                                                                                                    class="
                                                                                                        w-full
                                                                                                        rounded-xl
                                                                                                        border
                                                                                                        border-gray-300
                                                                                                        px-4
                                                                                                        py-2.5
                                                                                                        focus:border-indigo-500
                                                                                                        focus:ring-indigo-500
                                                                                                    "

                                                                                                >

                                                                                                <div

                                                                                                    v-if="

                                                                                                        form.errors.account_number

                                                                                                    "

                                                                                                    class="
                                                                                                        mt-1
                                                                                                        text-sm
                                                                                                        text-red-500
                                                                                                    "

                                                                                                >

                                                                                                    {{ form.errors.account_number }}

                                                                                                </div>

                                                                                            </div>

                                                                                            <!-- Account Holder -->

                                                                                            <div>

                                                                                                <label

                                                                                                    class="
                                                                                                        mb-2
                                                                                                        block
                                                                                                        text-sm
                                                                                                        font-medium
                                                                                                        text-gray-700
                                                                                                    "

                                                                                                >

                                                                                                    Account Holder

                                                                                                    </label>

                                                                                                        <input

                                                                                                            v-model="

                                                                                                                form.account_holder

                                                                                                            "

                                                                                                            type="text"

                                                                                                            class="
                                                                                                                w-full
                                                                                                                rounded-xl
                                                                                                                border
                                                                                                                border-gray-300
                                                                                                                px-4
                                                                                                                py-2.5
                                                                                                                focus:border-indigo-500
                                                                                                                focus:ring-indigo-500
                                                                                                            "

                                                                                                        >

                                                                                                        <div

                                                                                                            v-if="

                                                                                                                form.errors.account_holder

                                                                                                            "

                                                                                                            class="
                                                                                                                mt-1
                                                                                                                text-sm
                                                                                                                text-red-500
                                                                                                            "

                                                                                                        >

                                                                                                            {{ form.errors.account_holder }}

                                                                                                        </div>

                                                                                                    </div>

                                                                                                </div>

                                                                                            </div>

                                                                                                <div

                                                                                                    class="mt-8"

                                                                                                >
                                                                                                        <!-- Financial Information -->

                                                                                                <div

                                                                                                   class="
                                                                                                    rounded-2xl
                                                                                                    border
                                                                                                    border-gray-200
                                                                                                    bg-white
                                                                                                    shadow-sm
                                                                                                    overflow-visible
                                                                                                    "

                                                                                                >

                                                                                                            <div

                                                                                                                class="
                                                                                                                    border-b
                                                                                                                    border-gray-200
                                                                                                                    bg-gray-50
                                                                                                                    px-6
                                                                                                                    py-4
                                                                                                                "

                                                                                                            >

                                                                                                                <h3

                                                                                                                    class="
                                                                                                                        text-lg
                                                                                                                        font-semibold
                                                                                                                        text-gray-800
                                                                                                                    "

                                                                                                                >

                                                                                                                    Financial Information

                                                                                                                </h3>

                                                                                                                <p

                                                                                                                    class="
                                                                                                                        mt-1
                                                                                                                        text-sm
                                                                                                                        text-gray-500
                                                                                                                    "

                                                                                                                >

                                                                                                                    Define the opening balance for this Cash & Bank account.

                                                                                                                </p>

                                                                                                            </div>
                                                                                                                <!-- pas div-->
                                            <div

                                                class="
                                                    grid
                                                    grid-cols-1
                                                    gap-6
                                                    p-6
                                                    md:grid-cols-2
                                                "

                                            >

                                                <!-- Opening Balance -->

                                                 <div>

                                                    <label

                                                        class="
                                                            mb-2
                                                            block
                                                            text-sm
                                                            font-medium
                                                            text-gray-700
                                                        "

                                                    >

                                                    Opening Balance

                                                    </label>

                                                    <CurrencyInput

                                                        v-model="

                                                            form.opening_balance

                                                        "

                                                    />

                                                    <div

                                                        v-if="

                                                            form.errors.opening_balance

                                                        "

                                                        class="
                                                            mt-1
                                                            text-sm
                                                            text-red-500
                                                        "

                                                        >

                                                        {{ form.errors.opening_balance }}

                                                     </div>

                                                </div>

                                                <!-- Current Balance -->

                                                <div>

                                                    <label

                                                        class="
                                                            mb-2
                                                            block
                                                            text-sm
                                                            font-medium
                                                            text-gray-700
                                                        "

                                                    >

                                                        Current Balance

                                                    </label>

                                                    <CurrencyInput

                                                        :model-value="

                                                            form.opening_balance

                                                        "

                                                        disabled

                                                    />

                                                    <p

                                                        class="
                                                            mt-2
                                                            text-xs
                                                            text-gray-400
                                                        "

                                                    >

                                                        Current Balance will automatically follow
                                                        the Opening Balance when the account is created.

                                                    </p>

                                                </div>
                                            
                                          </div>

                                        </div>
                                         <!--- div aman dan pas-->
                                            <div

                                                class="mt-8"

                                            >
                                                <!-- Other Information -->

                                                    <div

                                                       class="
                                                        rounded-2xl
                                                        border
                                                        border-gray-200
                                                        bg-white
                                                        shadow-sm
                                                        overflow-visible
                                                        "

                                                    >

                                                        <div

                                                            class="
                                                                border-b
                                                                border-gray-200
                                                                bg-gray-50
                                                                px-6
                                                                py-4
                                                            "

                                                        >

                                                            <h3

                                                                class="
                                                                    text-lg
                                                                    font-semibold
                                                                    text-gray-800
                                                                "

                                                            >

                                                                Other Information

                                                            </h3>

                                                            <p

                                                                class="
                                                                    mt-1
                                                                    text-sm
                                                                    text-gray-500
                                                                "

                                                            >

                                                                Additional information about this Cash & Bank account.

                                                            </p>

                                                        </div>

                                                        <div

                                                            class="
                                                                p-6
                                                            "

                                                        >

                                                            <label

                                                                class="
                                                                    mb-2
                                                                    block
                                                                    text-sm
                                                                    font-medium
                                                                    text-gray-700
                                                                "

                                                            >

                                                                Description

                                                            </label>

                                                            <textarea

                                                                v-model="

                                                                    form.description

                                                                "

                                                                rows="4"

                                                                class="
                                                                    w-full
                                                                    rounded-xl
                                                                    border
                                                                    border-gray-300
                                                                    px-4
                                                                    py-3
                                                                    focus:border-indigo-500
                                                                    focus:ring-indigo-500
                                                                "

                                                                placeholder="Enter description..."

                                                            />

                                                            <div

                                                                v-if="

                                                                    form.errors.description

                                                                "

                                                                class="
                                                                    mt-1
                                                                    text-sm
                                                                    text-red-500
                                                                "

                                                            >

                                                                {{ form.errors.description }}

                                                            </div>
                                                        
                                                        </div>
                                                    
                                                    </div>
                                                
                                                </div>
                                            </div>
                                        </div>
                                   </div> 
                                </div>
                            
                              </form>
                        </div>
                    </div>
                
    </AuthenticatedLayout>

</template>
               