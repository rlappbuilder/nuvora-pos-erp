<script setup>

import { Head } from '@inertiajs/vue3'

import { router } from '@inertiajs/vue3'

import {  ref } from 'vue'

import AuthenticatedLayout

from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({

    issue: Object,

})


const issue =

    props.issue 


    
const showCancelModal = ref(
    false
)

const cancelReason = ref('')

const cancelIssue = () => {

    showCancelModal.value = true

}

const confirmCancel = () => {

    router.post(

        route(

            'stock-issues.cancel',

            issue.id

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

            },

            onError: () => {

                showCancelModal.value = false

            }

        }

    )

}

const getStatusClass =
(
    status
) => {

    switch (
        status
    ) {

        case 'Draft':

            return
            'bg-yellow-100 text-yellow-800'

        case 'Posted':

            return
            'bg-green-100 text-green-800'

        case 'Complete':

            return
            'bg-blue-100 text-blue-800'

        case 'Cancelled':

            return
            'bg-red-100 text-red-800'

        default:

            return
            'bg-gray-100 text-gray-800'

    }

}

const totalQty = () => {

    return props.issue.details

    .reduce(

        (

            total,

            item

        ) =>

            total +

            Number(
                item.qty
            ),

        0

    )

}

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
const formatDateTime = (
    value
) => {

    if (

        !value

    ) {

        return '-'

    }

    return new Date(

        value

    ).toLocaleString(

        'en-GB',

        {

            day: '2-digit',

            month: 'short',

            year: 'numeric',

            hour: '2-digit',

            minute: '2-digit',

        }

    )

}


const postIssue = () => {

    router.post(

        route(

            'stock-issues.post',

            issue.id

        ),
           {},

        {

            preserveState: false,

            preserveScroll: true,

        }

    )

}
const completeIssue = () => {

    router.post(

        route(

            'stock-issues.complete',

            issue.id

        ),

        {},

        {

            preserveState: false,

            preserveScroll: true,

        }

    )

}

</script>
<template>

        <Head

            title="Stock Issue"

        />

    <AuthenticatedLayout>

            <template #header>

                <div>

                    <h2

                        class="
                            text-3xl
                            font-bold
                            text-gray-800
                        "

                    >

                        Stock Issue

                    </h2>

                    <p

                        class="
                            mt-1
                            text-sm
                            text-gray-500
                        "

                    >

                        Issue detail.

                    </p>

                </div>

            </template>

        <div

        class="
            py-6
        "

            >

                <div

                    class="
                        mx-auto
                        max-w-7xl
                        px-4
                        sm:px-4
                        lg:px-6
                    "
                >

                    <!-- action bar -->
                    <div

                    class="
                        rounded-3xl
                        bg-white
                        p-6
                        shadow-sm
                    "

                     >
                            <div

                                class="
                                    mb-6
                                    flex
                                    items-center
                                    justify-between
                                "

                            >

                                <div>

                                    <h1

                                        class="
                                            text-3xl
                                            font-bold
                                        "

                                    >

                                        {{

                                            issue.issue_number

                                        }}

                                    </h1>

                                    <p

                                        class="
                                            mt-1
                                            text-gray-500
                                        "

                                    >

                                        Stock Issue Document

                                    </p>

                                </div>

                                <div

                                    class="
                                        flex
                                        items-center
                                        gap-3
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
                                        rounded-full
                                        px-4
                                        py-2
                                        text-sm
                                        font-semibold
                                    "


                                    >

                                        {{

                                            issue.status

                                        }}

                                    </span>
                                    <button

                                            v-if="

                                                issue.status

                                                ===

                                                'Posted'

                                            "

                                            @click="

                                                completeIssue

                                            "

                                            type="button"

                                            class="
                                                rounded-2xl
                                                bg-indigo-600
                                                px-5
                                                py-2
                                                font-medium
                                                text-white
                                                transition
                                                hover:bg-indigo-700
                                            "

                                        >

                                            Complete

                                        </button>

                                    <button

                                        @click="

                                            router.visit(

                                                route(

                                                    'stock-issues.index'

                                                )

                                            )

                                        "

                                        class="
                                            rounded-2xl
                                            border
                                            px-5
                                            py-2
                                        "

                                    >

                                        Back

                                    </button>
                                <button

                                    v-if="
                                        issue.status
                                        ===
                                        'Draft'
                                    "

                                    @click="
                                        cancelIssue
                                    "

                                    type="button"

                                    class="
                                        rounded-2xl
                                        bg-red-600
                                        px-5
                                        py-2
                                        font-medium
                                        text-white
                                        transition
                                        hover:bg-red-700
                                    "

                                >

                                    Cancel

                                </button>
                                    <button

                                        v-if="
                                            issue.status
                                            ===
                                            'Draft'
                                        "

                                        @click="
                                            postIssue
                                        "

                                        type="button"

                                        class="
                                            rounded-2xl
                                            bg-green-600
                                            px-5
                                            py-2
                                            font-medium
                                            text-white
                                            transition
                                            hover:bg-green-700
                                        "

                                    >

                                        Post

                                    </button>
                                </div>

                            </div>
                    
                            <!-- end action bar-->
                            <!-- summary card-->
                                <div

                                    class="
                                        mb-6
                                        grid
                                        gap-6
                                        md:grid-cols-3
                                    "

                                >   
                                        <div

                                            class="
                                                rounded-3xl
                                                bg-white
                                                p-6
                                                shadow-sm
                                            "

                                        >

                                            <p

                                                class="
                                                    text-gray-500
                                                "

                                            >

                                                Products

                                            </p>

                                            <h3

                                                class="
                                                    mt-2
                                                    text-4xl
                                                    font-bold
                                                    text-blue-600
                                                "

                                            >

                                                {{

                                                    issue.details.length

                                                }}

                                            </h3>

                                        </div>
                                        <!-- trf qty-->
                                            <div

                                                class="
                                                    rounded-3xl
                                                    bg-white
                                                    p-6
                                                    shadow-sm
                                                "

                                            >

                                                <p

                                                    class="
                                                        text-gray-500
                                                    "

                                                >

                                                    Issue Qty

                                                </p>

                                                <h3

                                                    class="
                                                        mt-2
                                                        text-4xl
                                                        font-bold
                                                        text-green-600
                                                    "

                                                >

                                                    {{

                                                        totalQty()

                                                    }}

                                                </h3>

                                            </div>
                                        <!-- end trf qty-->
                                            <!-- trf value-->
                                            <div

                                                    class="
                                                        rounded-3xl
                                                        bg-white
                                                        p-6
                                                        shadow-sm
                                                    "

                                                >

                                                    <p

                                                        class="
                                                            text-gray-500
                                                        "

                                                    >

                                                        Issue Value

                                                    </p>

                                                <h3

                                                        class="
                                                            mt-2
                                                            whitespace-nowrap
                                                            text-4xl
                                                            font-bold
                                                            text-orange-500
                                                        "

                                                    >

                                                        Rp

                                                        {{

                                                            Number(

                                                                totalValue()

                                                            )

                                                            .toLocaleString(

                                                                'id-ID'

                                                            )

                                                        }}

                                                    </h3>

                                                </div>

                                            <!-- end trf value-->
                                    </div>
                            <!-- end summary card-->
                            <!-- issue information card-->
                             <div

                                    class="
                                        mb-6
                                        rounded-3xl
                                        bg-white
                                        p-6
                                        shadow-sm
                                    "

                                >

                                    <h2

                                        class="
                                            mb-6
                                            text-xl
                                            font-bold
                                        "

                                    >

                                        Stock Issue Information

                                    </h2>

                                    <div

                                        class="
                                            grid
                                            gap-6
                                            md:grid-cols-2
                                        "

                                    >

                                         <div>

                                            <p

                                                class="
                                                    text-sm
                                                    text-gray-500
                                                "

                                            >

                                                Issue Number

                                            </p>

                                            <p

                                                class="
                                                    mt-1
                                                    font-semibold
                                                "

                                            >

                                                {{

                                                    issue.issue_number

                                                }}

                                            </p>

                                        </div>
                                        <div>

                                            <p

                                                class="
                                                    text-sm
                                                    text-gray-500
                                                "

                                            >

                                               Issue  Date

                                            </p>

                                            <p

                                                class="
                                                    mt-1
                                                    font-semibold
                                                "

                                            >
                                                    {{

                                                        formatDateTime(

                                                            issue.issue_date
                                                        )

                                                    }}

                                                

                                            </p>

                                        </div>
                                        <div>

                                            <p
                                            class="
                                                text-sm
                                                text-gray-500
                                            "
                                        >
                                            Warehouse
                                        </p>

                                        <p
                                            class="
                                                mt-1
                                                font-semibold
                                            "
                                        >
                                            {{

                                                issue

                                                .warehouse

                                                ?.name

                                            }}
                                        </p>
                                        <!-- remark-->
                                         <br>
                                         <div

                                                class="
                                                    md:col-span-2
                                                "

                                            >

                                                <p

                                                    class="
                                                        text-sm
                                                        text-gray-500
                                                    "

                                                >

                                                    Remarks

                                                </p>

                                                <p

                                                    class="
                                                        mt-1
                                                        whitespace-pre-wrap
                                                    "

                                                >

                                                    {{

                                                        issue.remarks

                                                        ||

                                                        '-'

                                                    }}

                                                </p>

                                            </div>
                                            <!-- end remark-->
                                        </div>
                                        <div>

                                       <p
                                            class="
                                                text-sm
                                                text-gray-500
                                            "
                                        >
                                            Issue Type
                                        </p>

                                        <p
                                            class="
                                                mt-1
                                                font-semibold
                                            "
                                        >
                                            {{

                                                issue.issue_type

                                            }}
                                        </p>
                                        <br></br>
                                        <div>
                                        <p
                                            class="
                                                text-sm
                                                text-gray-500
                                            "
                                        >
                                            Reference Number
                                        </p>

                                        <p
                                            class="
                                                mt-1
                                                font-semibold
                                            "
                                        >
                                            {{

                                                issue.reference_number

                                                ||

                                                '-'

                                            }}
                                        </p>
                                        </div>
                                        </div>
                                     
                                        
                                    </div>
                                </div>
                            <!-- end tr information card-->
                             <!-- trf item card-->
                                <div

                                    class="
                                        mb-6
                                        rounded-3xl
                                        bg-white
                                        p-6
                                        shadow-sm
                                    "

                                >

                                    <div

                                        class="
                                            mb-6
                                            flex
                                            items-center
                                            justify-between
                                        "

                                    >

                                        <div>

                                            <h2

                                                class="
                                                    text-xl
                                                    font-bold
                                                "

                                            >

                                                Issue Items

                                            </h2>

                                            <p

                                                class="
                                                    text-sm
                                                    text-gray-500
                                                "

                                            >

                                                Products included in this Issue.

                                            </p>

                                        </div>

                                        <span

                                            class="
                                                rounded-full
                                                bg-blue-100
                                                px-4
                                                py-2
                                                text-sm
                                                font-medium
                                                text-blue-700
                                            "

                                        >

                                            {{

                                                issue.details.length

                                            }}

                                            Products

                                        </span>

                                    </div>
                                         <div

                                            class="
                                                overflow-x-auto
                                            "

                                        >

                                            <table

                                                class="
                                                    min-w-full
                                                "

                                            >
                                                 <thead class="
                                                            border-b
                                                            bg-gray-100
                                                        ">

                                                    <tr

                                                        class="
                                                            bg-gray-50
                                                        "

                                                    >

                                                        <th

                                                           class="w-16 px-4 py-4 text-left text-xs font-semibold uppercase text-gray-500"

                                                        >

                                                            Product

                                                        </th>

                                                        <th

                                                            class="w-16 px-4 py-4 text-left text-xs font-semibold uppercase text-gray-500"

                                                        >

                                                            Issue Qty

                                                        </th>

                                                        <th

                                                           class="w-16 px-4 py-4 text-left text-xs font-semibold uppercase text-gray-500"

                                                        >

                                                            Unit Cost

                                                        </th>

                                                        <th

                                                           class="w-16 px-4 py-4 text-left text-xs font-semibold uppercase text-gray-500"

                                                        >

                                                            Total Cost

                                                        </th>

                                                        <th

                                                           class="w-16 px-4 py-4 text-left text-xs font-semibold uppercase text-gray-500"

                                                        >

                                                            Remarks

                                                        </th>

                                                    </tr>

                                                </thead>
                                                <tbody>

                                                    <tr

                                                        v-for="

                                                            item

                                                            in

                                                            issue.details

                                                        "

                                                        :key="

                                                            item.id

                                                        "

                                                        class="
                                                            border-t
                                                        "

                                                        >
                                                        <td

                                                            class="
                                                                px-4
                                                                py-3
                                                                text-left
                                                                font-medium
                                                            "

                                                        >

                                                            {{

                                                                item.product?.name

                                                            }}

                                                        </td>
                                                        <td

                                                            class="
                                                                px-4
                                                                py-3
                                                                text-left
                                                                font-medium
                                                            "

                                                        >

                                                            {{

                                                                item.qty

                                                            }}

                                                        </td>
                                                        <td

                                                            class="
                                                                px-4
                                                                py-3
                                                                text-left
                                                            "

                                                        >

                                                            Rp

                                                            {{

                                                                Number(

                                                                    item.unit_cost

                                                                )

                                                                .toLocaleString(

                                                                    'id-ID'

                                                                )

                                                            }}

                                                        </td>
                                                        <td

                                                                class="
                                                                    px-4
                                                                    py-3
                                                                    text-left
                                                                    font-medium
                                                                    text-green-600
                                                                "

                                                            >

                                                                Rp

                                                                {{

                                                                    Number(

                                                                        item.total_cost

                                                                    )

                                                                    .toLocaleString(

                                                                        'id-ID'

                                                                    )

                                                                }}

                                                            </td>
                                                            <td

                                                                class="
                                                                    px-4
                                                                    py-3
                                                                "

                                                            >

                                                                {{

                                                                    item.remarks

                                                                    ||

                                                                    '-'

                                                                }}

                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                </div>
                             <!-- end trf item card-->
                              <!-- audit trail -->

                                    <div

                                        class="
                                            mb-6
                                            rounded-3xl
                                            bg-white
                                            p-6
                                            shadow-sm
                                        "

                                    >

                                        <h2

                                            class="
                                                mb-8
                                                text-xl
                                                font-bold
                                            "

                                        >

                                            Audit Trail

                                        </h2>

                                        <div

                                            class="
                                                grid
                                                gap-8
                                                md:grid-cols-2
                                            "

                                        >

                                            <div>

                                                <p

                                                    class="
                                                        text-gray-500
                                                    "

                                                >

                                                    Created By

                                                </p>

                                                <p

                                                    class="
                                                        mt-1
                                                        font-semibold
                                                    "

                                                >

                                                    {{

                                                        issue.creator?.name

                                                        ||

                                                        '-'

                                                    }}

                                                </p>

                                            </div>

                                            <div>

                                                <p

                                                    class="
                                                        text-gray-500
                                                    "

                                                >

                                                    Created At

                                                </p>

                                                <p

                                                    class="
                                                        mt-1
                                                        font-semibold
                                                    "

                                                >

                                                   {{

                                                        formatDateTime(

                                                            issue.created_at

                                                        )

                                                    }}

                                                </p>

                                            </div>

                                            <div>

                                                <p

                                                    class="
                                                        text-gray-500
                                                    "

                                                >

                                                    Posted By

                                                </p>

                                                <p

                                                    class="
                                                        mt-1
                                                        font-semibold
                                                    "

                                                >

                                                    {{

                                                        issue.poster?.name

                                                        ||

                                                        '-'

                                                    }}

                                                </p>

                                            </div>

                                            <div>

                                                <p

                                                    class="
                                                        text-gray-500
                                                    "

                                                >

                                                    Posted At

                                                </p>

                                                <p

                                                    class="
                                                        mt-1
                                                        font-semibold
                                                    "

                                                >

                                                    {{

                                                        formatDateTime(

                                                            issue.posted_at

                                                        )

                                                    }}

                                                </p>

                                            </div>

                                            <div>

                                                <p

                                                    class="
                                                        text-gray-500
                                                    "

                                                >

                                                    Completed By

                                                </p>

                                                <p

                                                    class="
                                                        mt-1
                                                        font-semibold
                                                    "

                                                >

                                                    {{

                                                        issue.completer?.name

                                                        ||

                                                        '-'

                                                    }}

                                                </p>

                                            </div>

                                            <div>

                                                <p

                                                    class="
                                                        text-gray-500
                                                    "

                                                >

                                                    Completed At

                                                </p>

                                                <p

                                                    class="
                                                        mt-1
                                                        font-semibold
                                                    "

                                                >

                                                   {{

                                                        formatDateTime(

                                                            issue.completed_at

                                                        )

                                                    }}

                                                </p>

                                            </div>

                                            <div>

                                                <p

                                                    class="
                                                        text-gray-500
                                                    "

                                                >

                                                    Cancelled By

                                                </p>

                                                <p

                                                    class="
                                                        mt-1
                                                        font-semibold
                                                    "

                                                >

                                                    {{

                                                        issue.canceller?.name

                                                        ||

                                                        '-'

                                                    }}

                                                </p>

                                            </div>

                                            <div>

                                                <p

                                                    class="
                                                        text-gray-500
                                                    "

                                                >

                                                    Cancelled At

                                                </p>

                                                <p

                                                    class="
                                                        mt-1
                                                        font-semibold
                                                    "

                                                >

                                                    {{

                                                            formatDateTime(

                                                                issue.cancelled_at

                                                            )

                                                        }}

                                                </p>

                                            </div>

                                        </div>

                                    </div>

                                    <!-- end audit trail -->

                                 
                                    <!-- workflow timeline -->

                                    <div

                                        class="
                                            rounded-3xl
                                            bg-white
                                            p-6
                                            shadow-sm
                                        "

                                    >

                                        <h2

                                            class="
                                                mb-8
                                                text-xl
                                                font-bold
                                            "

                                        >

                                            Workflow Timeline

                                        </h2>

                                        <div

                                            class="
                                                space-y-8
                                            "

                                        >
                                        <div

                                            class="
                                                flex
                                                items-start
                                                gap-4
                                            "

                                        >

                                            <div

                                                class="
                                                    mt-1
                                                    h-4
                                                    w-4
                                                    rounded-full
                                                    bg-blue-500
                                                "

                                            />

                                            <div>

                                                <h3

                                                    class="
                                                        font-semibold
                                                    "

                                                >

                                                    Draft Created

                                                </h3>

                                                <p

                                                    class="
                                                        text-gray-500
                                                    "

                                                >

                                                   {{

                                                        formatDateTime(

                                                            issue.created_at

                                                        )

                                                    }}

                                                </p>

                                                <p

                                                    class="
                                                        font-medium
                                                    "

                                                >

                                                    By

                                                    {{

                                                        issue.creator?.name

                                                        ||

                                                        '-'

                                                    }}

                                                </p>

                                            </div>

                                        </div>
                                        <div

                                            v-if="
                                                issue.posted_at
                                            "

                                            class="
                                                flex
                                                items-start
                                                gap-4
                                            "

                                        >

                                            <div

                                                class="
                                                    mt-1
                                                    h-4
                                                    w-4
                                                    rounded-full
                                                    bg-green-500
                                                "

                                            />

                                            <div>

                                                <h3

                                                    class="
                                                        font-semibold
                                                    "

                                                >

                                                    issue Posted

                                                </h3>

                                                <p

                                                    class="
                                                        text-gray-500
                                                    "

                                                >

                                                   {{

                                                        formatDateTime(

                                                            issue.posted_at

                                                        )

                                                    }}

                                                </p>

                                                <p

                                                    class="
                                                        font-medium
                                                    "

                                                >

                                                    By

                                                    {{

                                                        issue.poster?.name

                                                        ||

                                                        '-'

                                                    }}

                                                </p>

                                            </div>

                                        </div>
                                        <div

                                            v-if="
                                                issue.completed_at
                                            "

                                            class="
                                                flex
                                                items-start
                                                gap-4
                                            "

                                        >

                                            <div

                                                class="
                                                    mt-1
                                                    h-4
                                                    w-4
                                                    rounded-full
                                                    bg-indigo-500
                                                "

                                            />

                                            <div>

                                                <h3

                                                    class="
                                                        font-semibold
                                                    "

                                                >

                                                    issue Completed

                                                </h3>

                                                <p

                                                    class="
                                                        text-gray-500
                                                    "

                                                >

                                                   {{

                                                        formatDateTime(

                                                            issue.completed_at

                                                        )

                                                    }}

                                                </p>

                                                <p

                                                    class="
                                                        font-medium
                                                    "

                                                >

                                                    By

                                                    {{

                                                        issue.completer?.name

                                                        ||

                                                        '-'

                                                    }}

                                                </p>

                                            </div>

                                        </div>
                                        <!-- issue cancelled at-->
                                        <div

                                            v-if="
                                                issue.cancelled_at
                                            "

                                            class="
                                                flex
                                                items-start
                                                gap-4
                                            "

                                        >

                                            <div

                                                class="
                                                    mt-1
                                                    h-4
                                                    w-4
                                                    rounded-full
                                                    bg-red-500
                                                "

                                            />

                                            <div>

                                                <h3

                                                    class="
                                                        font-semibold
                                                        text-red-600
                                                    "

                                                >

                                                    issue Cancelled

                                                </h3>

                                                <p

                                                    class="
                                                        text-gray-500
                                                    "

                                                >

                                                    {{

                                                        formatDateTime(

                                                            issue.cancelled_at

                                                        )

                                                    }}

                                                </p>

                                                <p

                                                    class="
                                                        font-medium
                                                    "

                                                >

                                                    By

                                                    {{

                                                        issue.canceller?.name

                                                        ||

                                                        '-'

                                                    }}

                                                </p>

                                                <div

                                                    v-if="
                                                        issue.cancel_reason
                                                    "

                                                    class="
                                                        mt-3
                                                        rounded-xl
                                                        bg-red-50
                                                        px-4
                                                        py-3
                                                        text-sm
                                                        text-red-700
                                                    "

                                                >

                                                    {{ issue.cancel_reason }}

                                                </div>

                                            </div>

                                        </div>
                                        <!-- issue cancelled at-->
                                    </div>
                                </div>
                                     <!-- end workflow timline-->
                    </div>
                </div>
        </div>
<!-- modal cancel-->

 <div

    v-if="
        showCancelModal
    "

    class="
        fixed inset-0
        flex items-center
        justify-center
        bg-black/50
    "

>

    <div
        class="
            w-full max-w-md
            rounded-xl
            bg-white
            p-6
        "
    >

        <h3
            class="
                mb-4
                text-lg
                font-bold
            "
        >

            Cancel Stock issue

        </h3>

        <textarea

            v-model="
                cancelReason
            "

            rows="4"

            class="
                w-full rounded-lg border
            "

            placeholder="
                Enter cancel reason
            "

        />

        <div
            class="
                mt-4 flex justify-end gap-2
            "
        >

            <button

                @click="
                    showCancelModal = false
                "

                class="
                    rounded-lg
                    border px-4 py-2
                "

            >

                Close

            </button>

            <button

                @click="
                    confirmCancel
                "

                class="
                    rounded-lg
                    bg-red-600
                    px-4 py-2
                    text-white
                "

            >

                Confirm Cancel

            </button>

        </div>

    </div>

</div>
<!-- end modal cancell-->
    </AuthenticatedLayout>
</template>
    

   