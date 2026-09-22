<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import Swal from 'sweetalert2'
import BaseToast from '@/Components/UI/BaseToast.vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const page = usePage()

const props = defineProps({
    warehouses: Object
})

const deletewarehouse = async (id) => {

    const result = await Swal.fire({
        title: 'Delete Warehouse?',
        text: 'This warehouse will be deleted. Are you sure?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
    })

    if (!result.isConfirmed) {
        return
    }

    router.delete(
        route(
            'warehouses.destroy',
            id
        ),
        {
            preserveScroll: true,
        }
    )
}
</script>

<template>

    <Head title="Warehouse Management" />

    <AppLayout>

        <template #header>

            <div
                class="flex items-center justify-between"
            >

                <div>

                    <h2
                        class="text-3xl font-bold text-gray-800"
                    >
                        Warehouse Management
                    </h2>

                    <p
                        class="mt-1 text-sm text-gray-500"
                    >
                        Manage company warehouses and locations.
                    </p>

                </div>

            </div>

        </template>


        <!-- =====================================================
             EMPTY STATE
        ====================================================== -->

        <div

            v-if="warehouses.data.length == 0"

            class="rounded-3xl bg-white p-16 text-center shadow-sm"

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
                🏭
            </div>

            <h3
                class="text-2xl font-bold text-gray-800"
            >
                No warehouse available
            </h3>

            <p
                class="mt-2 text-gray-500"
            >
                Create your first warehouse
            </p>

            <div class="mt-8">

                <Link
                    :href="route('warehouses.create')"
                    class="
                        rounded-xl
                        bg-blue-600
                        px-6
                        py-3
                        text-white
                        hover:bg-blue-700
                    "
                >
                    + Add Warehouse
                </Link>

            </div>

        </div>


        <!-- =====================================================
             WAREHOUSE CARDS
        ====================================================== -->

        <div

            v-else

            class="
                grid
                gap-6
                md:grid-cols-2
                xl:grid-cols-3
            "

        >

            <!-- =================================================
                 EXISTING WAREHOUSES
            ================================================== -->

            <div

                v-for="warehouse in warehouses.data"

                :key="warehouse.id"

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


                <div
                    class="p-8"
                >

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
                        {{ warehouse.name }}
                    </h3>


                    <p
                        class="
                            mt-2
                            text-center
                            text-gray-500
                        "
                    >
                        {{ warehouse.code }}
                    </p>


                    <div
                        class="my-6 border-t"
                    ></div>


                    <!-- Analytics -->

                    <div class="space-y-3">

                        <!-- Products -->

                        <div
                            class="
                                flex
                                justify-between
                                text-sm
                            "
                        >

                            <span
                                class="
                                    font-bold
                                    text-blue-600
                                "
                            >
                                Products
                            </span>

                            <span
                                class="font-semibold"
                            >
                                {{ warehouse.total_products }}
                            </span>

                        </div>


                        <!-- Current Stock -->

                        <div
                            class="
                                flex
                                justify-between
                                text-sm
                            "
                        >

                            <span
                                class="
                                    font-bold
                                    text-green-600
                                "
                            >
                                Current Stock
                            </span>

                            <span
                                class="
                                    font-semibold
                                    text-green-600
                                "
                            >
                                {{ warehouse.current_stock }} PCS
                            </span>

                        </div>


                        <!-- Last Movement -->

                        <div
                            class="
                                flex
                                justify-between
                                text-sm
                            "
                        >

                            <span
                                class="
                                    font-bold
                                    text-red-600
                                "
                            >
                                Last Movement
                            </span>

                            <span
                                class="
                                    text-xs
                                    text-gray-600
                                "
                            >

                                {{
                                    warehouse.last_movement

                                        ? new Date(
                                            warehouse.last_movement
                                        ).toLocaleDateString(
                                            'id-ID'
                                        )

                                        : '-'
                                }}

                            </span>

                        </div>


                        <!-- Status -->

                        <div
                            class="
                                flex
                                justify-between
                                text-sm
                            "
                        >

                            <span
                                class="
                                    font-bold
                                    text-yellow-600
                                "
                            >
                                Status
                            </span>

                            <span
                                class="
                                    rounded-full
                                    bg-green-100
                                    px-2
                                    py-1
                                    text-xs
                                    font-medium
                                    text-green-700
                                "
                            >
                                Active
                            </span>

                        </div>

                    </div>


                    <hr class="my-4">


                    <!-- Company -->

                    <div class="flex">

                        <div class="w-24 text-gray-500">
                            Company
                        </div>

                        <div>
                            {{
                                warehouse.branch?.company?.company_name
                                || '-'
                            }}
                        </div>

                    </div>


                    <!-- Branch -->

                    <div class="flex">

                        <div class="w-24 text-gray-500">
                            Branch
                        </div>

                        <div>
                            {{
                                warehouse.branch?.name
                                || '-'
                            }}
                        </div>

                    </div>


                    <!-- Type -->

                    <div class="flex">

                        <div class="w-24 text-gray-500">
                            Type
                        </div>

                        <div>
                            {{
                                warehouse.warehouse_type
                                || '-'
                            }}
                        </div>

                    </div>


                    <!-- PIC -->

                    <div class="flex">

                        <div class="w-24 text-gray-500">
                            PIC
                        </div>

                        <div>
                            {{
                                warehouse.pic_name
                                || '-'
                            }}
                        </div>

                    </div>


                    <!-- Phone -->

                    <div class="flex">

                        <div class="w-24 text-gray-500">
                            Phone
                        </div>

                        <div>
                            {{
                                warehouse.phone
                                || '-'
                            }}
                        </div>

                    </div>


                    <div
                        class="my-6 border-t"
                    ></div>


                    <div
                        class="
                            text-center
                            text-sm
                            text-gray-500
                        "
                    >
                        {{ warehouse.warehouse_type }}
                    </div>


                    <div
                        class="my-6 border-t"
                    ></div>


                    <!-- Actions -->

                    <div
                        class="
                            flex
                            justify-center
                            gap-3
                        "
                    >

                        <Link
                            :href="route(
                                'warehouses.show',
                                warehouse.id
                            )"
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
                            :href="route(
                                'warehouses.edit',
                                warehouse.id
                            )"
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
                                deletewarehouse(
                                    warehouse.id
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


            <!-- =================================================
                 ADD WAREHOUSE CARD
                 PALING AKHIR
            ================================================== -->

            <Link

                :href="route('warehouses.create')"

                class="
                    group
                    flex
                    min-h-full
                    flex-col
                    overflow-hidden
                    rounded-3xl
                    bg-white
                    shadow-sm
                    transition
                    hover:-translate-y-1
                    hover:shadow-lg
                "

            >

                <div
                    class="h-2 bg-blue-600"
                ></div>


                <div
                    class="
                        flex
                        flex-1
                        flex-col
                        items-center
                        justify-center
                        p-8
                        text-center
                    "
                >

                    <!-- Logo -->

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
                            transition
                            group-hover:bg-blue-50
                        "
                    >
                        ➕
                    </div>


                    <!-- Title -->

                    <h3
                        class="
                            text-2xl
                            font-bold
                            text-gray-800
                            transition
                            group-hover:text-blue-600
                        "
                    >
                        Add Warehouse
                    </h3>


                    <p
                        class="
                            mt-2
                            text-sm
                            text-gray-500
                        "
                    >
                        Create a new warehouse for your company.
                    </p>


                    <div
                        class="
                            mt-8
                            rounded-xl
                            bg-blue-600
                            px-6
                            py-3
                            text-sm
                            font-medium
                            text-white
                            transition
                            group-hover:bg-blue-700
                        "
                    >
                        + Add Warehouse
                    </div>

                </div>

            </Link>

        </div>


        <BaseToast
            :show="page.props.flash.success"
            :message="page.props.flash.success"
        />

    </AppLayout>

</template>