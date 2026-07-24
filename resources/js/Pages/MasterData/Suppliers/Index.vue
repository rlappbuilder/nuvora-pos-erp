<script setup>
import {
    Head,
    Link,
    router,
    usePage
} from '@inertiajs/vue3'

import AppLayout from '@/Layouts/AppLayout.vue'


const page = usePage()

const props = defineProps({

    suppliers: Object,

    filters: Object,

})

const deleteSupplier = (id) => {

    if (

        confirm(
            'Are you sure you want to delete this supplier?'
        )

    ) {

        router.delete(

            route(
                'suppliers.destroy',
                id
            )

        )

    }

}
</script>

<template>

    <Head title="Suppliers" />

    <AppLayout>

        <div class="space-y-6">

            <div class="flex items-center justify-between">

                <div>

                            <h1
                class="text-4xl font-bold text-slate-900"
            >
                Supplier Management
            </h1>

            <p
                class="mt-2 text-gray-500"
            >
                Manage supplier master data and business partners.
            </p>
            <div
                class="mb-6 flex items-center justify-between"
            >

                <input
                    type="text"
                    placeholder="Search Supplier..."
                    class="w-72 rounded-xl border px-4 py-3"
                />

                <Link

                    :href="
                        route(
                            'suppliers.create'
                        )
                    "

                    class="rounded-xl bg-blue-600 px-6 py-3 font-medium text-white"

                >

                    + Create Supplier

    </Link>

</div>
                </div>

               

            </div>
<!-- statistik-->
   <!-- Card Empty-->
<div
    class="mb-6 grid gap-4 md:grid-cols-3"
>

    <div
        class="rounded-2xl bg-white p-6 shadow-sm"
    >

        <div
            class="text-sm text-gray-500"
        >
            Total Supplier
        </div>

        <div
            class="mt-2 text-4xl font-bold"
        >
            {{ suppliers.total }}
        </div>

    </div>

    <div
        class="rounded-2xl bg-white p-5 shadow"
    >

        <div
            class="text-sm text-gray-500"
        >
            Active
        </div>

        <div
            class="mt-2 text-3xl font-bold text-green-600"
        >
            {{
                suppliers.data.filter(
                    item => item.status
                ).length
            }}
        </div>

    </div>

    <div
        class="rounded-2xl bg-white p-5 shadow"
    >

        <div
            class="text-sm text-gray-500"
        >
            Inactive
        </div>

        <div
            class="mt-2 text-3xl font-bold text-red-600"
        >
            {{
                suppliers.data.filter(
                    item => !item.status
                ).length
            }}
        </div>

    </div>

</div>
 <!-- end statik-->
            <div
                class="overflow-hidden rounded-2xl bg-white shadow"
            >

                <table
                    class="min-w-full"
                >

                    <thead
                        class="bg-gray-50"
                    >

                        <tr>

                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                            >
                                Code
                            </th>

                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                            >
                                Supplier
                            </th>

                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                            >
                                Phone
                            </th>

                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                            >
                                City
                            </th>

                            <th
                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                            >
                                Status
                            </th>

                            <th
                                class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500"
                            >
                                Action
                            </th>

                        </tr>

                    </thead>

                    <tbody
                        class="divide-y divide-gray-200 bg-white"
                    >
                    <!-- Supplier empty state-->
<tr
    v-if="
        suppliers.data.length === 0
    "
>

    <td
        colspan="7"
        class="px-6 py-16 text-center"
    >

        <div
            class="space-y-3"
        >

            <div
                class="text-5xl"
            >
                👥
            </div>

            <div
                class="text-lg font-semibold"
            >
                No Supplier Found
            </div>

            <div
                class="text-gray-500"
            >
                Create your first Supplier.
            </div>

            <Link

                :href="
                    route(
                        'suppliers.create'
                    )
                "

                class="inline-block rounded-xl bg-blue-600 px-5 py-2 text-white"

            >

                Create Supplier

            </Link>

        </div>

    </td>

</tr>
<!-- end customer empty state-->

                        <tr

                            v-for="
                                supplier
                                in suppliers.data
                            "

                            :key="
                                supplier.id
                            "

                        >

                            <td
                                class="px-6 py-4"
                            >
                                {{ supplier.supplier_code }}
                            </td>

                            <td
                                class="px-6 py-4"
                            >
                                {{ supplier.name }}
                            </td>

                            <td
                                class="px-6 py-4"
                            >
                                {{ supplier.phone || '-' }}
                            </td>

                            <td
                                class="px-6 py-4"
                            >
                                {{ supplier.city || '-' }}
                            </td>

                            <td
                                class="px-6 py-4"
                            >

                                <span

                                    class="rounded-full px-3 py-1 text-xs"

                                    :class="
                                        supplier.status
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-red-100 text-red-700'
                                    "

                                >

                                    {{
                                        supplier.status
                                        ? 'Active'
                                        : 'Inactive'
                                    }}

                                </span>

                            </td>

                                                            <td
                                    class="px-6 py-4 text-right"
                                >

                                    <div
                                        class="flex justify-end gap-2"
                                    >

                                        <Link

                                            :href="
                                                route(
                                                    'suppliers.show',
                                                    supplier.id
                                                )
                                            "

                                            class="rounded-lg bg-slate-600 px-3 py-2 text-sm text-white hover:bg-slate-700"

                                        >

                                            View

                                        </Link>

                                        <Link

                                            :href="
                                                route(
                                                    'suppliers.edit',
                                                    supplier.id
                                                )
                                            "

                                            class="rounded-lg bg-amber-500 px-3 py-2 text-sm text-white hover:bg-amber-600"

                                        >

                                            Edit

                                        </Link>

                                        <button

                                            @click="
                                                deleteSupplier(
                                                    supplier.id
                                                )
                                            "

                                            class="rounded-lg bg-red-600 px-3 py-2 text-sm text-white hover:bg-red-700"

                                        >

                                            Delete

                                        </button>

                                    </div>

                                </td>

                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

    </AppLayout>

</template>