<script setup>

import {

    Head,
    Link,
    router,

} from '@inertiajs/vue3'

import {

    ref,

} from 'vue'

import FlatPickr

from 'vue-flatpickr-component'

import 'flatpickr/dist/flatpickr.css'

import AuthenticatedLayout

from '@/Layouts/AuthenticatedLayout.vue'

import SearchableSelect

from '@/Components/SearchableSelect.vue'
import {

    EyeIcon,

} from '@heroicons/vue/24/outline'
const props = defineProps({

    issues: Object,

    filters: Object,

    summary: Object,

})

const dateConfig = {

    mode: 'range',

    dateFormat: 'Y-m-d',

}

const search = ref(

    props.filters.search ?? ''

)

const status = ref(

    props.filters.status ?? ''

)
const showCancelModal = ref(
    false
)

const cancelReason = ref('')

const cancelTransfer = () => {

    showCancelModal.value = true

}

const confirmCancel = () => {

    router.post(

        route(

            'stock-transfers.cancel',

            transfer.id

        ),

        {

            cancel_reason:

                cancelReason.value

        },

        {

            preserveState: false,

            preserveScroll: true,

            onSuccess: () => {

                showCancelModal.value = false

                cancelReason.value = ''

            }

        }

    )

}
const dateRange = ref(

    props.filters.date ?? ''

)
const totalValue = () => {

    return props.issue.details

    .reduce(

        (

            total,

            item

        ) =>

            total +

            Number(
                item.total_cost
            ),

        0

    )

}

const searchData = () => {

    router.get(

        route(

            'stock-issues.index'

        ),

        {

            search:

                search.value,

            status:

                status.value,

            date:

                dateRange.value,

        },

        {

            preserveState: true,

            preserveScroll: true,

        }

    )

}

const clearFilter = () => {

    search.value = ''

    status.value = ''

    dateRange.value = ''

    searchData()

}

const statusOptions = [

    {

        id: '',

        name: 'All Status',

    },

    {

        id: 'Draft',

        name: 'Draft',

    },

    {

        id: 'Posted',

        name: 'Posted',

    },

    {

        id: 'Completed',

        name: 'Completed',

    },

    {

        id: 'Cancelled',

        name: 'Cancelled',

    },

]

const getStatusClass = (

    status

) => {

    switch (

        status

    ) {

        case 'Draft':

            return 'bg-yellow-100 text-yellow-800'

        case 'Posted':

            return 'bg-green-100 text-green-800'

        case 'Completed':

            return 'bg-blue-100 text-blue-800'

        case 'Cancelled':

            return 'bg-red-100 text-red-800'

        default:

            return 'bg-gray-100 text-gray-800'

    }

}

const formatCurrency = (

    value

) => {

    return Number(

        value

    ).toLocaleString(

        'id-ID'

    )

}

const formatDate = (

    value

) => {

    if (

        !value

    ) {

        return '-'

    }

    return new Date(

        value

    ).toLocaleDateString(

        'en-GB',

        {

            day: '2-digit',

            month: 'short',

            year: 'numeric',

        }

    )

}

</script>
<template>
  
        <Head
            title="Inventory Issue"
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
                                    text-3xl
                                    font-bold
                                    text-gray-800
                                "

                            >

                                Stock Issues

                            </h2>

                            <p

                                class="
                                    mt-1
                                    text-sm
                                    text-gray-500
                                "

                            >

                                Manage inventory Issues between warehouses.

                            </p>

                        </div>

                        <Link

                            :href="
                                route(
                                    'stock-issues.create'
                                )
                            "

                            class="
                                rounded-2xl
                                bg-blue-600
                                px-6
                                py-3
                                font-medium
                                text-white
                                transition
                                hover:bg-blue-700
                            "

                        >

                            + New Issues

                        </Link>

                    </div>

                </template>
                <div

                            class="
                                mb-6
                                rounded-3xl
                                bg-white
                                p-6
                                shadow-sm
                            "

                        >
                         <!-- fileter bar-->
                        <div

                            class="
                                mb-6
                                rounded-3xl
                                bg-white
                                p-6
                                shadow-sm
                            "

                        >
                            <!-- summary card-->
                                <div

                                    class="
                                        mb-6
                                        grid
                                        gap-6
                                        md:grid-cols-4
                                    "

                                >

                                    <div

                                        class="
                                            rounded-3xl
                                            bg-yellow-50
                                            p-6
                                            shadow-sm
                                        "

                                    >

                                        <p

                                            class="
                                                text-sm
                                                text-yellow-700
                                            "

                                        >

                                            Draft

                                        </p>

                                        <h2

                                            class="
                                                mt-2
                                                text-3xl
                                                font-bold
                                                text-yellow-800
                                            "

                                        >

                                            {{ summary.draft }}

                                        </h2>

                                    </div>

                                    <div

                                        class="
                                            rounded-3xl
                                            bg-green-50
                                            p-6
                                            shadow-sm
                                        "

                                    >

                                        <p

                                            class="
                                                text-sm
                                                text-green-700
                                            "

                                        >

                                            Posted

                                        </p>

                                        <h2

                                            class="
                                                mt-2
                                                text-3xl
                                                font-bold
                                                text-green-800
                                            "

                                        >

                                            {{ summary.posted }}

                                        </h2>

                                    </div>

                                    <div

                                        class="
                                            rounded-3xl
                                            bg-blue-50
                                            p-6
                                            shadow-sm
                                        "

                                    >

                                        <p

                                            class="
                                                text-sm
                                                text-blue-700
                                            "

                                        >

                                            Completed

                                        </p>

                                        <h2

                                            class="
                                                mt-2
                                                text-3xl
                                                font-bold
                                                text-blue-800
                                            "

                                        >

                                            {{ summary.completed }}

                                        </h2>

                                    </div>

                                    <div

                                        class="
                                            rounded-3xl
                                            bg-red-50
                                            p-6
                                            shadow-sm
                                        "

                                    >

                                        <p

                                            class="
                                                text-sm
                                                text-red-700
                                            "

                                        >

                                            Cancelled

                                        </p>

                                        <h2

                                            class="
                                                mt-2
                                                text-3xl
                                                font-bold
                                                text-red-800
                                            "

                                        >

                                            {{ summary.cancelled }}

                                        </h2>

                                    </div>

                                </div>
                                <!-- end summary card-->
                </div>
                <!-- end summary bar-->
                    
                            <div

                                class="
                                    grid
                                    gap-4
                                    lg:grid-cols-12
                                "

                            >

                                <!-- Search -->

                                <div

                                    class="
                                        lg:col-span-4
                                    "

                                >

                                    <label

                                        class="
                                            mb-2
                                            block
                                            text-sm
                                            font-medium
                                        "

                                    >

                                        Search

                                    </label>

                                    <input

                                        v-model="
                                            search
                                        "

                                        type="text"

                                        placeholder="issues Number..."

                                        class="
                                            w-full
                                            rounded-xl
                                            border
                                            border-gray-300
                                            px-4
                                            py-3
                                            focus:border-blue-500
                                            focus:outline-none
                                        "

                                    >

                                </div>

                                <!-- Date -->

                                <div

                                    class="
                                        lg:col-span-3
                                    "

                                >

                                    <label

                                        class="
                                            mb-2
                                            block
                                            text-sm
                                            font-medium
                                        "

                                    >

                                        Date

                                    </label>

                                    <FlatPickr

                                        v-model="
                                            dateRange
                                        "

                                        :config="
                                            dateConfig
                                        "

                                        class="
                                            w-full
                                            rounded-xl
                                            border
                                            border-gray-300
                                            px-4
                                            py-3
                                        "

                                    />

                                </div>

                                <!-- Status -->

                                <div

                                    class="
                                        lg:col-span-3
                                    "

                                >

                                    <label

                                        class="
                                            mb-2
                                            block
                                            text-sm
                                            font-medium
                                        "

                                    >

                                        Status

                                    </label>

                                    <SearchableSelect

                                        v-model="
                                            status
                                        "

                                        :options="
                                            statusOptions
                                        "

                                        placeholder="All Status"

                                    />

                                </div>

                                <!-- Buttons -->

                                <div

                                    class="
                                        flex
                                        items-end
                                        gap-2
                                        lg:col-span-2
                                    "

                                >

                                    <button

                                        @click="
                                            searchData
                                        "

                                        class="
                                            flex-1
                                            rounded-xl
                                            bg-blue-600
                                            px-4
                                            py-3
                                            font-medium
                                            text-white
                                            transition
                                            hover:bg-blue-700
                                        "

                                    >

                                        Search

                                    </button>

                                    <button

                                        @click="
                                            clearFilter
                                        "

                                        class="
                                            rounded-xl
                                            bg-gray-200
                                            px-4
                                            py-3
                                            font-medium
                                            text-gray-700
                                            transition
                                            hover:bg-gray-300
                                        "

                                    >

                                        Clear

                                    </button>

                                </div>

                            </div>
                            <!-- entrepise table-->
                             <br>
                            <div

                                class="
                                    overflow-hidden
                                    rounded-3xl
                                    bg-white
                                    shadow-sm
                                "

                            >

                                <div

                                    class="
                                        overflow-x-auto
                                    "

                                >

                                    <table

                                        class="
                                            min-w-full
                                            table-fixed
                                        "

                                    >

                                        <thead

                                           class="
                                            border-b
                                            bg-gray-100
                                        "

                                        >

                                            <tr>

                                                <th class="w-16 px-4 py-4 text-left text-xs font-semibold uppercase text-gray-500">

                                                    No

                                                </th>

                                                <th class="w-36 px-4 py-4 text-left text-xs font-semibold uppercase text-gray-500">

                                                    Issues No

                                                </th>

                                                <th class="w-48 px-4 py-4 text-left text-xs font-semibold uppercase text-gray-500">

                                                    Date

                                                </th>

                                                <th class="w-30 px-4 py-4 text-left text-xs font-semibold uppercase text-gray-500">

                                                     Issue Type

                                                </th>

                                                <th class="w-48 px-4 py-4 text-center text-xs font-semibold uppercase text-gray-500">

                                                    Warehouse

                                                </th>

                                                <th class="w-24 px-4 py-4 text-center text-xs font-semibold uppercase text-gray-500">

                                                    Issue Value


                                                </th>

                                                <th class="w-40 px-4 py-4 text-center text-xs font-semibold uppercase text-gray-500">

                                                    Issue Type
                                                </th>

                                                <th class="w-32 px-4 py-4 text-center text-xs font-semibold uppercase text-gray-500">

                                                   Action

                                                </th>
                                            </tr>

                                        </thead>

                                        <tbody>

                                            <tr

                                                v-if="

                                                    !issues.data.length

                                                "

                                            >

                                                <td

                                                    colspan="10"

                                                    class="
                                                        py-16
                                                        text-center
                                                        text-gray-500
                                                    "

                                                >

                                                    No Stock Issues Found

                                                </td>

                                            </tr>

                                           <tr

                                                v-for="
                                                    (
                                                        issue,
                                                        index
                                                    )
                                                    in
                                                    issues.data
                                                "

                                                :key="
                                                    issue.id
                                                "

                                                :class="

                                                    index % 2

                                                    ?

                                                    'bg-gray-50 hover:bg-blue-50 transition'

                                                    :

                                                    'bg-white hover:bg-blue-50 transition'

                                                "

                                            >

                                                <td

                                                    class="
                                                        px-4
                                                        py-4
                                                    "

                                                >

                                                    {{

                                                        index + 1 +

                                                        (

                                                            (

                                                                issues.current_page - 1

                                                            )

                                                            *

                                                            issues.per_page

                                                        )

                                                    }}

                                                </td>

                                          <td

                                                class="
                                                    px-4
                                                    py-4
                                                "

                                            >

                                                <Link

                                                    :href="
                                                        route(
                                                            'stock-issues.show',
                                                            issue.id
                                                        )
                                                    "

                                                    class="
                                                        font-semibold
                                                        text-blue-600
                                                        transition
                                                        hover:underline
                                                    "

                                                >

                                                    {{

                                                        issue.issue_number

                                                    }}

                                                </Link>

                                            </td>

                                                <td

                                                    class="
                                                        px-4
                                                        py-4
                                                    "

                                                >

                                                    {{

                                                        formatDate(

                                                            issue.issue_date

                                                        )

                                                    }}

                                                </td>
  
                                                <td
                                                class="
                                                    px-4
                                                    py-4
                                                    text-center
                                                "
                                            >

                                                <div
                                                    class="
                                                        font-semibold
                                                    "
                                                >

                                                    {{

                                                        issue.issue_type

                                                    }}

                                                </div>

                                            </td>
                                            <td
                                                    class="
                                                        px-4
                                                        py-4
                                                    "

                                                >
                                                    <div

                                                            class="
                                                                text-center
                                                            "

                                                        >

                                                    <div

                                                        class="
                                                            font-semibold
                                                        "

                                                    >

                                                        {{

                                                            issue.warehouse

                                                        }}

                                                    </div>

                                                </div>
                                            </td>
                                                <td

                                                    class="
                                                        px-4
                                                        py-4
                                                        text-right
                                                        font-medium
                                                        text-green-600
                                                    "

                                                >

                                                    Rp

                                                    {{

                                                        formatCurrency(

                                                            issue.total_cost

                                                        )

                                                    }}

                                                </td>

                                                <td

                                                    class="
                                                        px-4
                                                        py-4
                                                        text-center
                                                    "

                                                >

                                                 <span

                                                    :class="
                                                        getStatusClass(
                                                            issue.status
                                                        )
                                                    "

                                                    class="
                                                        inline-flex
                                                        items-center
                                                        gap-2
                                                        rounded-full
                                                        px-3
                                                        py-1.5
                                                        text-xs
                                                        font-semibold
                                                    "

                                                >

                                                    <span

                                                        class="
                                                            h-2
                                                            w-2
                                                            rounded-full
                                                            bg-current
                                                        "

                                                    />

                                                    {{

                                                        issue.status

                                                    }}

                                                </span>

                                                </td>

                                                <td

                                                    class="
                                                        px-4
                                                        py-4
                                                        text-center
                                                    "

                                                >
                                                <Link

                                                    :href="
                                                        route(
                                                            'stock-issues.show',
                                                            issue.id
                                                        )
                                                    "

                                                    class="
                                                        inline-flex
                                                        items-center
                                                        justify-center
                                                        rounded-lg
                                                        p-2
                                                        text-blue-600
                                                        transition
                                                        hover:bg-blue-100
                                                    "

                                                >

                                                    <EyeIcon

                                                        class="
                                                            h-5
                                                            w-5
                                                        "

                                                    />

                                                </Link>

                                                </td>

                                            </tr>

                                        </tbody>

                                    </table>

                                </div>

                            </div>
                            <!-- end entreprise table-->
                             <!-- pagination-->
                            <div

                                class="
                                    flex
                                    items-center
                                    justify-between
                                    border-t
                                    bg-white
                                    px-6
                                    py-4
                                "

                            >

                                <div

                                    class="
                                        text-sm
                                        text-gray-500
                                    "

                                >

                                    Showing

                                    <span

                                        class="
                                            font-semibold
                                        "

                                    >

                                        {{ issues.from }}

                                    </span>

                                    -

                                    <span

                                        class="
                                            font-semibold
                                        "

                                    >

                                        {{ issues.to }}

                                    </span>

                                    of

                                    <span

                                        class="
                                            font-semibold
                                        "

                                    >

                                        {{ issues.total }}

                                    </span>

                                    issues

                                </div>

                                <div

                                    class="
                                        flex
                                        items-center
                                        gap-2
                                    "

                                >

                                    <template

                                        v-for="

                                            link

                                            in

                                            issues.links

                                        "

                                        :key="

                                            link.label

                                        "

                                    >

                                        <button

                                            v-if="
                                                link.url
                                            "

                                            @click="
                                                router.visit(
                                                    link.url,
                                                    {
                                                        preserveScroll: true,
                                                        preserveState: true,
                                                    }
                                                )
                                            "

                                            v-html="
                                                link.label
                                            "

                                            :class="

                                                link.active

                                                ?

                                                'bg-blue-600 text-white'

                                                :

                                                'bg-white text-gray-700 hover:bg-gray-100'

                                            "

                                            class="
                                                rounded-xl
                                                border
                                                px-4
                                                py-2
                                                text-sm
                                                transition
                                            "

                                        />

                                    </template>

                                </div>

                            </div>
                             <!-- pagination-->
                        </div>
                     <!-- end filter bar-->
    </AuthenticatedLayout>

</template>