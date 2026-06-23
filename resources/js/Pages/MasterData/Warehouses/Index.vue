<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import BaseToast from '@/Components/UI/BaseToast.vue'

const page = usePage()

const props = defineProps({

    warehouses: Object

})

const deletewarehouse  = (id) => {

    if (
        confirm(
            'Are you sure you want to delete this warehouse ?'
        )
    ) {

        router.delete(

            route(
                'warehouses.destroy',
                id
            )

        )

    }

}
</script>

<template>

    <Head title="Warehouse Management" />

    <AuthenticatedLayout>

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

                <Link

                    :href="route('warehouses.create')"

                    class="rounded-xl bg-blue-600 px-5 py-3 font-medium text-white transition hover:bg-blue-700"

                >

                    + Add warehouses

                </Link>

            </div>

        </template>

        <!-- Empty State -->

        <div

            v-if="warehouses.data.length == 0"

            class="rounded-3xl bg-white p-16 text-center shadow-sm"

        >

            <div
                class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-slate-100 text-5xl"
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

            <div
                class="mt-8"
            >

                <Link

                    :href="route('warehouses.create')"

                    class="rounded-xl bg-blue-600 px-6 py-3 text-white hover:bg-blue-700"

                >

                    + Add warehouses

                </Link>

            </div>

        </div>

        <!-- Company Card -->

        <div

            v-else

            class="grid gap-6 md:grid-cols-2 xl:grid-cols-3"

        >

            <div

                v-for="warehouse in warehouses.data"

                :key="warehouse.id"

                class="overflow-hidden rounded-3xl bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg"

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
                        class="mb-5 flex justify-center"
                    >

                        <div

                            class="flex h-20 w-20 items-center justify-center rounded-full bg-slate-100 text-4xl"

                        >

                            🏢

                        </div>

                    </div>

                    <!-- Name -->

                    <h3
                        class="text-center text-2xl font-bold text-gray-800"
                    >

                        {{ warehouse.name }}

                    </h3>

                    <p
                        class="mt-2 text-center text-gray-500"
                    >

                        {{ warehouse.code }}

                    </p>

                    <div
                        class="my-6 border-t"
                     >
                    </div>
                <div class="space-y-3">
                    <!-- tempel card analityc-->
                     <div
                            class="
                                flex
                                justify-between
                                text-sm
                            "
                        >

                            <span
                                class="text-blue-600 font-bold"
                            >
                                Products
                            </span>

                            <span
                                class="font-semibold"
                            >
                                {{
                                    warehouse.total_products
                                }}
                            </span>

                        </div>

                        <div
                            class="
                                flex
                                justify-between
                                text-sm
                            "
                        >

                            <span
                                class=text-green-600 font-bold
                            >
                                Current Stock
                            </span>

                            <span
                                class="
                                    font-semibold
                                    text-green-600
                                "
                            >
                                {{
                                    warehouse.current_stock
                                }}  PCS
                            </span>

                        </div>

                        <div
                            class="
                                flex
                                justify-between
                                text-sm
                            "
                        >

                            <span
                                class="text-red-600 font-bold"
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
                        <!-- status -->
                         <div
                                class="
                                    flex
                                    justify-between
                                    text-sm
                                "
                            >

                                <span
                                    class="text-yellow-600 font-bold"
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
                         <!-- end status-->
                    <!-- card analitycs-->
                     <hr>
             <div class="flex">

        <div class="w-24 text-gray-500">
            Company
        </div>

        <div>
            {{ warehouse.branch?.company?.company_name || '-' }}
        </div>

    </div>

    <div class="flex">

        <div class="w-24 text-gray-500">
            Branch
        </div>

        <div>
            {{ warehouse.branch?.name || '-' }}
        </div>

    </div>

    <div class="flex">

        <div class="w-24 text-gray-500">
            Type
        </div>

        <div>
            {{ warehouse.warehouse_type || '-' }}
        </div>

    </div>
<div class="flex">

    <div class="w-24 text-gray-500">
        PIC
    </div>

    <div>
        {{ warehouse.pic_name || '-' }}
    </div>

</div>

<div class="flex">

    <div class="w-24 text-gray-500">
        Phone
    </div>

    <div>
        {{ warehouse.phone || '-' }}
    </div>

</div>

</div>
<div
    class="my-6 border-t"
></div>

<div
    class="text-center text-sm text-gray-500"
>

  {{ warehouse.warehouse_type }}

</div>

<div
    class="my-6 border-t"
></div>
                   
                   
                   

                    <!-- Action -->

                    <div
                        class="flex justify-center gap-3"
                    >

                        <Link

                            :href="route(
                                'warehouses.show',
                                warehouse.id
                            )"

                            class="rounded-xl bg-slate-600 px-4 py-2 text-sm text-white hover:bg-slate-700"

                        >

                            View

                        </Link>

                        <Link

                            :href="route(
                                'warehouses.edit',
                              warehouse.id
                            )"

                            class="rounded-xl bg-amber-500 px-4 py-2 text-sm text-white hover:bg-amber-600"

                        >

                            Edit

                        </Link>

                        <button

                            @click="
                                deletewarehouse(
                                  warehouse.id
                                )
                            "

                            class="rounded-xl bg-red-500 px-4 py-2 text-sm text-white hover:bg-red-600"

                        >

                            Delete

                        </button>

                    </div>

                </div>

            </div>

        </div>

        <BaseToast

            :show="page.props.flash.success"

            :message="page.props.flash.success"

        />

    </AuthenticatedLayout>

</template>