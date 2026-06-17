<script setup>
import {
    Head,
    Link,
    router,
    usePage
} from '@inertiajs/vue3'

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import BaseToast from '@/Components/UI/BaseToast.vue'

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

    <AuthenticatedLayout>

        <div class="space-y-6">

            <div class="flex items-center justify-between">

                <div>

                    <h1
                        class="text-2xl font-bold text-gray-900"
                    >
                        Supplier List
                    </h1>

                    <p
                        class="mt-1 text-sm text-gray-500"
                    >
                        Manage supplier master data
                    </p>

                </div>

                <Link

                    :href="
                        route(
                            'suppliers.create'
                        )
                    "

                    class="rounded-xl bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"

                >

                    Create Supplier

                </Link>

            </div>

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

    </AuthenticatedLayout>

</template>