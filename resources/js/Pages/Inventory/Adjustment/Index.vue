<script setup>

import { Head } from '@inertiajs/vue3'

import { router } from '@inertiajs/vue3'

import { reactive } from 'vue'

import FlatPickr
from 'vue-flatpickr-component'

import 'flatpickr/dist/flatpickr.css'

import AuthenticatedLayout
from '@/Layouts/AuthenticatedLayout.vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({

    adjustments: Object,

    summary: Object,

    filters: Object,

})

const filter = reactive({

    search:

        props.filters.search
        ?? '',

    status:

        props.filters.status
        ?? '',

    date_from:

        props.filters.date_from
        ?? '',

    date_to:

        props.filters.date_to
        ?? '',

})

const dateConfig = {

    dateFormat:

        'Y-m-d',

}

const applyFilter = () => {

    router.get(

        route(

            'inventory-adjustments.index'

        ),

        filter,

        {

            preserveState: true,

            replace: true,

        }

    )

}

</script>
<template>

<Head
    title="Inventory Adjustment"
/>

<AppLayout>

            <template #header>

                <div>

                    <h2

                        class="
                            text-3xl
                            font-bold
                            text-gray-800
                        "

                    >

                        Inventory Adjustment

                    </h2>

                    <p

                        class="
                            mt-1
                            text-sm
                            text-gray-500
                        "

                    >

                        Manage stock adjustments.

                    </p>

                </div>

            </template>

            <div

                class="
                    grid
                    gap-6
                    md:grid-cols-4
                "

            >
                <!-- summary card -->
                 <div

                        class="
                            rounded-3xl
                            bg-white
                            p-6
                            shadow-sm
                        "

                    >

                        <p class="text-gray-500">

                            Draft

                        </p>

                        <h3

                            class="
                                mt-2
                                text-4xl
                                font-bold
                                text-yellow-600
                            "

                        >

                            {{ summary.draft }}

                        </h3>

                    </div>
                    <!-- summary posted-->
                     <div

                        class="
                            rounded-3xl
                            bg-white
                            p-6
                            shadow-sm
                        "

                    >

                        <p class="text-gray-500">

                            Posted

                        </p>

                        <h3

                            class="
                                mt-2
                                text-4xl
                                font-bold
                                text-green-600
                            "

                        >

                            {{ summary.posted }}

                        </h3>

                    </div>
                     <!-- end summary posted-->
                      <!-- summary cancelled-->
                       <div

                            class="
                                rounded-3xl
                                bg-white
                                p-6
                                shadow-sm
                            "

                        >

                            <p class="text-gray-500">

                                Cancelled

                            </p>

                            <h3

                                class="
                                    mt-2
                                    text-4xl
                                    font-bold
                                    text-red-600
                                "

                            >

                                {{ summary.cancelled }}

                            </h3>

                        </div>
                       <!-- end summary cancelled-->
                        <!-- summary total-->
                        <div

                            class="
                                rounded-3xl
                                bg-white
                                p-6
                                shadow-sm
                            "

                        >

                            <p class="text-gray-500">

                                Total Documents

                            </p>

                            <h3

                                class="
                                    mt-2
                                    text-4xl
                                    font-bold
                                    text-blue-600
                                "

                            >

                                {{ summary.total }}
                               

                            </h3>

                        </div>

                       

                 <!-- end summary card-->
        </div>
        <!-- filter card-->
       
                <div

                    class="
                        mt-6
                        rounded-3xl
                        bg-white
                        p-6
                        shadow-sm
                    "

                >

                    <div

                        class="
                            grid
                            gap-4
                            md:grid-cols-5
                        "

                    >
            <!-- end filter card-->
                    <!-- search-->
                    <div>

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
                                    filter.search
                                "

                                type="text"

                                placeholder="ADJ Number"

                                class="
                                    w-full
                                    rounded-xl
                                    border-gray-300
                                "

                            >

                        </div>
                    <!-- end search-->
                        <!-- status-->
                        <div>

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

                            <select

                                v-model="
                                    filter.status
                                "

                                class="
                                    w-full
                                    rounded-xl
                                    border-gray-300
                                "

                            >

                                <option value="">

                                    All Status

                                </option>

                                <option value="Draft">

                                    Draft

                                </option>

                                <option value="Posted">

                                    Posted

                                </option>

                                <option value="Cancelled">

                                    Cancelled

                                </option>

                            </select>

                        </div>
                        <!-- end status-->
                    <!-- date form-->
                        <div>

                            <label
                                class="
                                    mb-2
                                    block
                                    text-sm
                                    font-medium
                                "
                            >

                                Date From

                            </label>

                            <FlatPickr

                                v-model="
                                    filter.date_from
                                "

                                :config="
                                    dateConfig
                                "

                                class="
                                    w-full
                                    rounded-xl
                                    border-gray-300
                                "

                            />

                        </div>
                        <!-- end date from-->
                        <!-- date to-->
                        <div>

                            <label
                                class="
                                    mb-2
                                    block
                                    text-sm
                                    font-medium
                                "
                            >

                                Date To

                            </label>

                            <FlatPickr

                                v-model="
                                    filter.date_to
                                "

                                :config="
                                    dateConfig
                                "

                                class="
                                    w-full
                                    rounded-xl
                                    border-gray-300
                                "

                            />

                        </div>
              <!-- end date to-->
                    <!-- action-->
                        <div

                            class="
                                flex
                                items-end
                                gap-2
                            "

                        >

                            <button

                                @click="
                                    applyFilter
                                "

                                class="
                                    rounded-xl
                                    bg-blue-600
                                    px-5
                                    py-3
                                    text-white
                                "

                            >

                                Filter

                            </button>

                            <button

                                @click="
                                    $inertia.visit(
                                        route(
                                            'inventory-adjustments.create'
                                        )
                                    )
                                "

                                class="
                                    rounded-xl
                                    bg-green-600
                                    px-5
                                    py-3
                                    text-white
                                "

                            >

                                Create

                            </button>

                        </div>

                    </div>

                 </div>
                <!--  end action-->
                  <!-- Table Card -->

                    <div

                        class="
                            mt-6
                            overflow-hidden
                            rounded-3xl
                            bg-white
                            shadow-sm
                        "

                    >
                        <!-- empty state-->
                            <div

                                v-if="
                                    adjustments.data.length
                                    === 0
                                "

                                class="
                                    p-16
                                    text-center
                                "

                            >

                                <div

                                    class="
                                        text-6xl
                                    "

                                >

                                    📦

                                </div>

                                <h3

                                    class="
                                        mt-4
                                        text-2xl
                                        font-bold
                                        text-gray-800
                                    "

                                >

                                    No Adjustments Found

                                </h3>

                                <p

                                    class="
                                        mt-2
                                        text-gray-500
                                    "

                                >

                                    Create your first inventory adjustment.

                                </p>

                                <button

                                    @click="
                                        $inertia.visit(
                                            route(
                                                'inventory-adjustments.create'
                                            )
                                        )
                                    "

                                    class="
                                        mt-6
                                        rounded-2xl
                                        bg-blue-600
                                        px-6
                                        py-3
                                        text-white
                                    "

                                >

                                    Create Adjustment

                                </button>

                            </div>
                        <!-- end empty state-->
                         <!-- table detail-->
                          <div

                                    v-else

                                    class="
                                        overflow-x-auto
                                    "

                                >

                                <table

                                    class="
                                        min-w-full
                                    "

                                >
                                    <thead>

                                        <tr

                                            class="
                                                bg-gray-50
                                            "

                                        >

                                            <th
                                                class="
                                                    px-6
                                                    py-4
                                                    text-left
                                                "
                                            >
                                                Number
                                            </th>

                                            <th
                                                class="
                                                    px-6
                                                    py-4
                                                    text-left
                                                "
                                            >
                                                Warehouse
                                            </th>

                                            <th
                                                class="
                                                    px-6
                                                    py-4
                                                    text-left
                                                "
                                            >
                                                Date
                                            </th>

                                            <th
                                                class="
                                                    px-6
                                                    py-4
                                                    text-left
                                                "
                                            >
                                                Status
                                            </th>

                                            <th
                                                class="
                                                    px-6
                                                    py-4
                                                    text-left
                                                "
                                            >
                                                Created By
                                            </th>

                                            <th
                                                class="
                                                    px-6
                                                    py-4
                                                    text-center
                                                "
                                            >
                                                Action
                                            </th>

                                        </tr>

                                    </thead>
                                    <tbody>

                                        <tr

                                            v-for="
                                                adjustment
                                                in
                                                adjustments.data
                                            "

                                            :key="
                                                adjustment.id
                                            "

                                            class="
                                                border-t
                                                hover:bg-gray-50
                                            "

                                        >
                                        <td

                                            class="
                                                px-6
                                                py-4
                                                font-semibold
                                            "

                                        >

                                            {{

                                                adjustment.adjustment_number

                                            }}
                                        </td>
                                        <td

                                            class="
                                                px-6
                                                py-4
                                            "

                                        >

                                            {{

                                                adjustment.warehouse.name

                                            }}

                                        </td>
                                        <td

                                        class="
                                            px-6
                                            py-4
                                        "

                                    >

                                        {{

                                            adjustment.adjustment_date

                                        }}

                                    </td>
                                    <td

                                        class="
                                            px-6
                                            py-4
                                        "

                                    >

                                        <span

                                            class="
                                                rounded-full
                                                px-3
                                                py-1
                                                text-xs
                                                font-semibold
                                            "

                                            :class="

                                                adjustment.status
                                                === 'Draft'

                                                ? 'bg-yellow-100 text-yellow-800'

                                                : adjustment.status
                                                === 'Posted'

                                                ? 'bg-green-100 text-green-800'

                                                : 'bg-red-100 text-red-800'

                                            "

                                        >

                                            {{ adjustment.status }}

                                        </span>

                                    </td>
                                    <td

                                        class="
                                            px-6
                                            py-4
                                        "

                                    >

                                        {{

                                            adjustment.creator?.name

                                            ?? '-'

                                        }}

                                    </td>
                                    <td

                                        class="
                                            px-6
                                            py-4
                                            text-center
                                        "

                                    >

                                        <button

                                            @click="
                                                $inertia.visit(

                                                    route(

                                                        'inventory-adjustments.show',

                                                        adjustment.id

                                                    )

                                                )
                                            "

                                            class="
                                                rounded-xl
                                                bg-blue-600
                                                px-4
                                                py-2
                                                text-sm
                                                text-white
                                            "

                                        >

                                            View

                                        </button>

                                    </td>

                                 </tr>

                             </tbody>

                        </table>

                    </div>

                </div>
                    <!-- end tble-->
                     <div

                        v-if="
                            adjustments.links.length
                            > 3
                        "

                        class="
                            mt-6
                            flex
                            justify-center
                            gap-2
                        "

                    >

                        <template

                            v-for="
                                link
                                in
                                adjustments.links
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
                                    $inertia.visit(
                                        link.url
                                    )
                                "

                                v-html="
                                    link.label
                                "

                                class="
                                    rounded-xl
                                    border
                                    px-4
                                    py-2
                                "

                                :class="{

                                    'bg-blue-600 text-white':

                                    link.active

                                }"

                            />

                        </template>

                    </div>
</AppLayout>
</template>