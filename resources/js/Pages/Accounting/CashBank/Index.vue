<script setup>

import AuthenticatedLayout
    from '@/Layouts/AuthenticatedLayout.vue'

import { Head }

    from '@inertiajs/vue3'

import {

    router,

    Link

}

from '@inertiajs/vue3'

import {

    ref,

    watch

}

from 'vue'
import SearchableSelect

    from '@/Components/SearchableSelect.vue'

import {

    EyeIcon,

    PencilSquareIcon,

    TrashIcon

}

from '@heroicons/vue/24/outline'
import Swal from 'sweetalert2'
const props = defineProps({

    cashBanks: Object,

    filters: Object,

    summary: Object,

})

const search = ref(

    props.filters.search ?? ''

)

const type = ref(

    props.filters.type ?? ''

)

const status = ref(

    props.filters.status ?? ''

)
const typeOptions = [

    {

        id: '',

        name: 'All Type'

    },

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

        id: '',

        name: 'All Status'

    },

    {

        id: 1,

        name: 'Active'

    },

    {

        id: 0,

        name: 'Inactive'

    }

]
watch(

    [

        search,

        type,

        status

    ],

    () => {

        router.get(

            route(

                'cash-banks.index'

            ),

            {

                search: search.value,

                type: type.value,

                status: status.value,

            },

            {

                preserveState: true,

                replace: true,

            }

        );

    }

);

const resetFilter = () => {

    search.value = ''

    type.value = ''

    status.value = ''

}
const formatCurrency = (value) => {

    return new Intl.NumberFormat(

        'id-ID',

        {

            style: 'currency',

            currency: 'IDR',

            minimumFractionDigits: 2

        }

    ).format(value ?? 0)

}
const formatCompactCurrency = (value) => {

    value = Number(value ?? 0)

    if (value >= 1000000000000) {

        return `Rp ${(value / 1000000000000).toFixed(2)} T`

    }

    if (value >= 1000000000) {

        return `Rp ${(value / 1000000000).toFixed(2)} M`

    }

    if (value >= 1000000) {

        return `Rp ${(value / 1000000).toFixed(2)} Jt`

    }

    if (value >= 1000) {

        return `Rp ${(value / 1000).toFixed(2)} Rb`

    }

    return formatCurrency(value)

}
const deleteCashBank = (id) => {

    Swal.fire({

        title: 'Delete Cash Bank?',

        text: 'This action cannot be undone.',

        icon: 'warning',

        showCancelButton: true,

        confirmButtonColor: '#dc2626',

        cancelButtonColor: '#6b7280',

        confirmButtonText: 'Yes, Delete',

        cancelButtonText: 'Cancel'

    }).then((result) => {

        if (result.isConfirmed) {

            router.delete(

                route('cash-banks.destroy', id),

                {

                    preserveScroll: true,

                    onSuccess: () => {

                        Swal.fire({

                            icon: 'success',

                            title: 'Deleted',

                            text: 'Cash Bank deleted successfully.',

                            timer: 1800,

                            showConfirmButton: false

                        })

                    },

                    onError: () => {

                        Swal.fire({

                            icon: 'error',

                            title: 'Failed',

                            text: 'Unable to delete Cash Bank.'

                        })

                    }

                }

            )

        }

    })

}
</script>
<template>

    <Head

        title="Cash & Bank"

    />

    <AuthenticatedLayout>

        <template #header>

            <div

                class="flex items-center justify-between"

            >

                <div>

                    <h2

                        class="text-2xl font-bold text-gray-900"

                    >

                        Cash & Bank

                    </h2>

                    <p

                        class="mt-1 text-sm text-gray-500"

                    >

                        Manage cash accounts and bank accounts.

                    </p>

                </div>

                <Link

                    :href="route('cash-banks.create')"

                    class="
                        inline-flex
                        items-center
                        rounded-xl
                        bg-indigo-600
                        px-5
                        py-3
                        text-sm
                        font-semibold
                        text-white
                        shadow-sm
                        transition
                        hover:bg-indigo-700
                    "

                >

                    + New Cash Bank

                </Link>

            </div>

        </template>

        <div

            class="py-6"

        >

            <div

                class="
                    mx-auto
                    max-w-7xl
                    px-4
                    sm:px-6
                    lg:px-8
                "

            >
                 <!-- Summary Card -->

                <div

                    class="
                        mb-6
                        grid
                        gap-6
                        md:grid-cols-2
                        xl:grid-cols-4
                    "

                >

                    <!-- Total Account -->

                    <div

                        class="
                            rounded-2xl
                            border
                            border-gray-100
                            bg-white
                            p-6
                            shadow-sm
                        "

                    >

                        <p

                            class="
                                text-sm
                                font-medium
                                text-gray-500
                            "

                        >

                            Total Account

                        </p>

                        <h3

                            class="
                                mt-2
                                text-3xl
                                font-bold
                                text-gray-900
                            "

                        >

                            {{ summary?.total_accounts ?? 0 }}

                        </h3>

                    </div>

                    <!-- Cash Account -->

                    <div

                        class="
                            rounded-2xl
                            border
                            border-gray-100
                            bg-white
                            p-6
                            shadow-sm
                        "

                    >

                        <p

                            class="
                                text-sm
                                font-medium
                                text-gray-500
                            "

                        >

                            Cash Account

                        </p>

                        <h3

                            class="
                                mt-2
                                text-3xl
                                font-bold
                                text-emerald-600
                            "

                        >

                            {{ summary?.cash_accounts ?? 0 }}

                        </h3>

                    </div>

                    <!-- Bank Account -->

                    <div

                        class="
                            rounded-2xl
                            border
                            border-gray-100
                            bg-white
                            p-6
                            shadow-sm
                        "

                    >

                        <p

                            class="
                                text-sm
                                font-medium
                                text-gray-500
                            "

                        >

                            Bank Account

                        </p>

                        <h3

                            class="
                                mt-2
                                text-3xl
                                font-bold
                                text-blue-600
                            "

                        >

                            {{ summary?.bank_accounts ?? 0 }}

                        </h3>

                    </div>

                    <!-- Current Balance -->

                    <div

                        class="
                            rounded-2xl
                            border
                            border-gray-100
                            bg-white
                            p-6
                            shadow-sm
                        "

                    >

                        <p

                            class="
                                text-sm
                                font-medium
                                text-gray-500
                            "

                        >

                            Current Balance

                        </p>

                    <h3

    :title="formatCurrency(summary.current_balance)"

    class="
        mt-2
        cursor-help
        truncate
        text-3xl
        font-bold
        text-indigo-600
    "

>

    {{ formatCompactCurrency(summary.current_balance) }}

</h3>

                    </div>

                </div>
                <!-- Filter Toolbar -->

                <div

                    class="
                        mb-6
                        rounded-2xl
                        border
                        border-gray-100
                        bg-white
                        p-5
                        shadow-sm
                    "

                >

                    <div

                        class="
                            flex
                            flex-col
                            gap-4
                            lg:flex-row
                            lg:items-end
                        "

                    >

                        <!-- Search -->

                        <div

                            class="w-full lg:w-96"

                        >

                            <label

                                class="
                                    mb-2
                                    block
                                    text-sm
                                    font-medium
                                    text-gray-600
                                "

                            >

                                Search

                            </label>

                            <input

                                v-model="search"

                                type="text"

                                placeholder="Search code or account name..."

                                class="
                                    w-full
                                    rounded-xl
                                    border-gray-300
                                    shadow-sm
                                    focus:border-indigo-500
                                    focus:ring-indigo-500
                                "

                            />

                        </div>

                        <!-- Type -->

                        <div

                            class="w-full lg:w-56"

                        >

                            <label

                                class="
                                    mb-2
                                    block
                                    text-sm
                                    font-medium
                                    text-gray-600
                                "

                            >

                                Type

                            </label>

                            <SearchableSelect

                               v-model="type"

                            :options="typeOptions"

                            placeholder="All Type"

                        />

                        </div>

                        <!-- Status -->

                        <div

                            class="w-full lg:w-56"

                        >

                            <label

                                class="
                                    mb-2
                                    block
                                    text-sm
                                    font-medium
                                    text-gray-600
                                "

                            >

                                Status

                            </label>

                            <SearchableSelect

                                v-model="status"

                                :options="statusOptions"

                                placeholder="All Status"

                            />

                        </div>

                        <!-- Reset -->

                        <div>

                           <button

                            @click="resetFilter"

                            type="button"

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

                            Reset

                        </button>

                        </div>

                    </div>

                </div>
                <!-- end filter bar-->
                 <!-- Data Table -->

<div

    class="
        overflow-hidden
        rounded-2xl
        border
        border-gray-100
        bg-white
        shadow-sm
    "

>

    <div

        class="overflow-x-auto"

    >

        <table

            class="min-w-full"

        >

            <!-- Table Header -->

            <thead

                class="bg-gray-100
                sticky
                top-0
                z-10
                "
            >

                <tr>

                    <th

                        class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-600"

                    >

                        No

                    </th>

                    <th

                        class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-600"

                    >

                        Code

                    </th>

                    <th

                        class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-600"

                    >

                        Account Name

                    </th>

                    <th

                        class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-600"

                    >

                        Type

                    </th>

                    <th

                        class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-600"

                    >

                        Bank

                    </th>

                    <th

                        class="px-5 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-600"

                    >

                        Account No

                    </th>

                    <th

                        class="px-5 py-4 text-right text-xs font-bold uppercase tracking-wider text-gray-600"

                    >

                        Balance

                    </th>

                    <th

                        class="px-5 py-4 text-center text-xs font-bold uppercase tracking-wider text-gray-600"

                    >

                        Status

                    </th>

                    <th

                        class="px-5 py-4 text-center text-xs font-bold uppercase tracking-wider text-gray-600"

                    >

                        Action

                    </th>

                </tr>

            </thead>

            <!-- Table Body -->

            <tbody>

                <tr v-if="cashBanks.data.length === 0">

    <td

        colspan="9"

        class="px-6 py-16"

    >

        <div

            class="flex flex-col items-center justify-center"

        >

            <svg

                xmlns="http://www.w3.org/2000/svg"

                fill="none"

                viewBox="0 0 24 24"

                stroke-width="1.5"

                stroke="currentColor"

                class="h-16 w-16 text-gray-300"

            >

                <path

                    stroke-linecap="round"

                    stroke-linejoin="round"

                    d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"

                />

                <path

                    stroke-linecap="round"

                    stroke-linejoin="round"

                    d="M9.75 9.75h4.5v4.5h-4.5z"

                />

            </svg>

            <h3

                class="mt-4 text-lg font-semibold text-gray-700"

            >

                No Cash Bank Found

            </h3>

            <p

                class="mt-2 text-sm text-gray-500"

            >

                Click the button below to create your first Cash Bank.

            </p>

            <Link

                :href="route('cash-banks.create')"

                class="
                    mt-6
                    rounded-xl
                    bg-indigo-600
                    px-5
                    py-3
                    text-white
                    hover:bg-indigo-700
                "

            >

                + New Cash Bank

            </Link>

        </div>

    </td>

</tr>

              <tr

                    v-for="(item,index) in cashBanks.data"

                    :key="item.id"

                    :class="[

                        index % 2 === 0

                            ? 'bg-white'

                            : 'bg-gray-50',

                        'hover:bg-blue-50 transition-colors duration-150'

                    ]"

                 >

                    <!-- No -->

                    <td

                        class="px-5 py-4"

                    >

                       {{
                            cashBanks.from + index
                        }}

                    </td>

                    <!-- Code -->

                    <td

                        class="px-5 py-4 font-semibold text-blue-600"

                    >

                        {{ item.code }}

                    </td>

                    <!-- Name -->

                    <td

                        class="px-5 py-4"

                    >

                        {{ item.name }}

                    </td>

                    <!-- Type -->

                    <td

                        class="px-5 py-4"

                    >

                        {{ item.type }}

                    </td>

                    <!-- Bank -->

                    <td

                        class="px-5 py-4"

                    >

                        {{ item.bank_name }}

                    </td>

                    <!-- Account -->

                    <td

                        class="px-5 py-4"

                    >

                        {{ item.account_number }}

                    </td>

                    <!-- Balance -->

                    <td

                        class="px-5 py-4 text-right font-semibold text-emerald-600"

                    >

                        {{ formatCurrency(item.current_balance) }}

                    </td>

                    <!-- Status -->
                    
                        <!-- Badge nanti -->
                            <td

                                class="px-5 py-4 text-center"

                            >

                                <span

                                    v-if="item.status"

                                    class="
                                        inline-flex
                                        rounded-full
                                        bg-green-100
                                        px-3
                                        py-1
                                        text-xs
                                        font-semibold
                                        text-green-700
                                    "

                                >

                                    Active

                                </span>

                                <span

                                    v-else

                                    class="
                                        inline-flex
                                        rounded-full
                                        bg-red-100
                                        px-3
                                        py-1
                                        text-xs
                                        font-semibold
                                        text-red-700
                                    "

                                >

                                    Inactive

                                </span>

                            </td>
                    <!-- Action -->
                        <!-- Heroicon nanti -->

                            <td

                                class="px-5 py-4"

                            >

                                <div

                                    class="
                                        flex
                                        items-center
                                        justify-center
                                        gap-2
                                    "

                                >

                                    <Link

                                        :href="route('cash-banks.show',item.id)"

                                        class="text-blue-600 hover:text-blue-800
                                        cursor-pointer
                                        "

                                    >

                                        <EyeIcon class="h-5 w-5"/>

                                    </Link>

                                    <Link

                                        :href="route('cash-banks.edit',item.id)"

                                        class="text-amber-600 hover:text-amber-800"

                                    >

                                        <PencilSquareIcon class="h-5 w-5"/>

                                    </Link>
                                    <!-- button delete-->
                                  <button

                                    @click="deleteCashBank(item.id)"

                                    class="
                                        text-red-600
                                        hover:text-red-800
                                    "

                                >

                                    <TrashIcon

                                        class="h-5 w-5"

                                    />

                                </button>

                                </div>

                            </td>
                </tr>

            </tbody>

        </table>
<div

    v-if="cashBanks.links.length > 3"

    class="
        border-t
        bg-white
        px-6
        py-4
    "

>

    <div

        class="flex justify-end"

    >

        <template

            v-for="link in cashBanks.links"

            :key="link.label"

        >

            <Link

                v-if="link.url"

                :href="link.url"

                v-html="link.label"

                class="
                    mx-1
                    rounded-lg
                    border
                    px-4
                    py-2
                    text-sm
                    hover:bg-indigo-50
                "

                :class="{

                    'bg-indigo-600 text-white':

                        link.active

                }"

            />

            <span

                v-else

                v-html="link.label"

                class="
                    mx-1
                    px-4
                    py-2
                    text-gray-400
                "

            />

        </template>

    </div>

</div>
    </div>

</div>
                 <!-- end data table-->
            </div>

        </div>

    </AuthenticatedLayout>

</template>