<script setup>

import { Head, Link, router, usePage } from '@inertiajs/vue3'
import BaseToast from '@/Components/UI/BaseToast.vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import Swal from 'sweetalert2'

const page = usePage()

const props = defineProps({

    branches: Object

})

const deleteBranch = async (id) => {

    const result = await Swal.fire({

        title: 'Delete Branch?',

        text: 'This branch will be deleted. Are you sure?',

        icon: 'warning',

        showCancelButton: true,

        confirmButtonText: 'Yes, Delete',

        cancelButtonText: 'Cancel',

        reverseButtons: true,

    })

    if (!result.isConfirmed) return

    router.delete(

        route(
            'branches.destroy',
            id
        )

    )

}

</script>

<template>

    <Head title="Branch Management" />

    <AppLayout>

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
                        Branch Management
                    </h2>

                    <p
                        class="
                            mt-1
                            text-sm
                            text-gray-500
                        "
                    >
                        Manage company branches and locations.
                    </p>

                </div>

                <Link
                    :href="
                        route(
                            'branches.create'
                        )
                    "
                    class="
                        rounded-xl
                        bg-blue-600
                        px-5
                        py-3
                        font-medium
                        text-white
                        transition
                        hover:bg-blue-700
                    "
                >
                    + Add Branches
                </Link>

            </div>

        </template>


        <!-- Empty State -->

        <div
            v-if="branches.data.length == 0"
            class="
                rounded-3xl
                bg-white
                p-16
                text-center
                shadow-sm
            "
        >

            <div
                class="
                    mx-auto
                    mb-6
                    flex
                    h-24
                    w-24
                    items-center
                    justify-center
                    rounded-full
                    bg-slate-100
                    text-5xl
                "
            >
                🏢
            </div>

            <h3
                class="
                    text-2xl
                    font-bold
                    text-gray-800
                "
            >
                No No branch available
            </h3>

            <p
                class="
                    mt-2
                    text-gray-500
                "
            >
                Create your first Branch
            </p>

            <div class="mt-8">

                <Link
                    :href="
                        route(
                            'branches.create'
                        )
                    "
                    class="
                        rounded-xl
                        bg-blue-600
                        px-6
                        py-3
                        text-white
                        hover:bg-blue-700
                    "
                >
                    + Add Branches
                </Link>

            </div>

        </div>


        <!-- Branch Cards -->

        <div
            v-else
            class="
                grid
                gap-6
                md:grid-cols-2
                xl:grid-cols-3
            "
        >

            <!-- Branch Card -->

            <div
                v-for="branch in branches.data"
                :key="branch.id"
                class="
                    overflow-hidden
                    rounded-3xl
                    bg-white
                    shadow-sm
                    transition
                    hover:-translate-y-1
                    hover:shadow-lg
                "
            >

                <!-- Accent -->

                <div
                    class="h-2 bg-blue-600"
                ></div>

                <div class="p-8">

                    <!-- Logo -->

                    <div
                        class="
                            mb-5
                            flex
                            justify-center
                        "
                    >

                        <div
                            class="
                                flex
                                h-20
                                w-20
                                items-center
                                justify-center
                                rounded-full
                                bg-slate-100
                                text-4xl
                            "
                        >
                            🏢
                        </div>

                    </div>


                    <!-- Name -->

                    <h3
                        class="
                            text-center
                            text-2xl
                            font-bold
                            text-gray-800
                        "
                    >
                        {{ branch.name }}
                    </h3>

                    <p
                        class="
                            mt-2
                            text-center
                            text-gray-500
                        "
                    >
                        {{ branch.code }}
                    </p>


                    <div class="my-6 border-t"></div>


                    <!-- Information -->

                    <div class="space-y-3">

                        <div class="flex">

                            <div class="w-24 text-gray-500">
                                Company
                            </div>

                            <div>
                                {{ branch.company?.company_name || '-' }}
                            </div>

                        </div>

                        <div class="flex">

                            <div class="w-24 text-gray-500">
                                Manager
                            </div>

                            <div>
                                {{ branch.manager_name || '-' }}
                            </div>

                        </div>

                        <div class="flex">

                            <div class="w-24 text-gray-500">
                                Phone
                            </div>

                            <div>
                                {{ branch.phone || '-' }}
                            </div>

                        </div>

                        <div class="flex">

                            <div class="w-24 text-gray-500">
                                City
                            </div>

                            <div>
                                {{ branch.city || '-' }}
                            </div>

                        </div>

                    </div>


                    <div class="my-6 border-t"></div>


                    <div
                        class="
                            text-center
                            text-sm
                            text-gray-500
                        "
                    >
                        {{
                            branch.is_head_office
                                ? 'Head Office'
                                : 'Branch Office'
                        }}
                    </div>


                    <div class="my-6 border-t"></div>


                    <!-- Action -->

                    <div
                        class="
                            flex
                            justify-center
                            gap-3
                        "
                    >

                        <Link
                            :href="
                                route(
                                    'branches.show',
                                    branch.id
                                )
                            "
                            class="
                                rounded-xl
                                bg-slate-600
                                px-4
                                py-2
                                text-sm
                                text-white
                                hover:bg-slate-700
                            "
                        >
                            View
                        </Link>

                        <Link
                            :href="
                                route(
                                    'branches.edit',
                                    branch.id
                                )
                            "
                            class="
                                rounded-xl
                                bg-amber-500
                                px-4
                                py-2
                                text-sm
                                text-white
                                hover:bg-amber-600
                            "
                        >
                            Edit
                        </Link>

                        <button
                            type="button"
                            @click="
                                deleteBranch(
                                    branch.id
                                )
                            "
                            class="
                                rounded-xl
                                bg-red-500
                                px-4
                                py-2
                                text-sm
                                text-white
                                hover:bg-red-600
                            "
                        >
                            Delete
                        </button>

                    </div>

                </div>

            </div>


            <!-- =========================================================
                 Add Branch Card
            ========================================================== -->

            <Link
                :href="
                    route(
                        'branches.create'
                    )
                "
                class="
                    flex
                    min-h-[420px]
                    flex-col
                    items-center
                    justify-center
                    rounded-3xl
                    border-2
                    border-dashed
                    border-gray-200
                    bg-white
                    p-8
                    text-center
                    transition
                    hover:border-blue-300
                    hover:bg-gray-50
                "
            >

                <div
                    class="
                        mb-5
                        flex
                        h-20
                        w-20
                        items-center
                        justify-center
                        rounded-full
                        bg-slate-100
                        text-4xl
                    "
                >
                    +
                </div>

                <h3
                    class="
                        text-xl
                        font-semibold
                        text-gray-800
                    "
                >
                    Add Branch
                </h3>

                <p
                    class="
                        mt-2
                        text-sm
                        text-gray-500
                    "
                >
                    Create a new company branch.
                </p>

            </Link>

        </div>


        <BaseToast
            :show="page.props.flash.success"
            :message="page.props.flash.success"
        />

    </AppLayout>

</template>