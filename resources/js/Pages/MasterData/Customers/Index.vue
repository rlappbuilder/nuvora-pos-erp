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

    customers: Object,

    filters: Object,

})

const deleteSupplier = (id) => {

    if (

        confirm(
            'Are you sure you want to delete this customer?'
        )

    ) {

        router.delete(

            route(
                'customers.destroy',
                id
            )

        )

    }

}
</script>

<template>

    <Head title="Customers" />

    <AuthenticatedLayout>
        

        <!-- batas template -->
        <div class="space-y-6">

            <div class="flex items-center justify-between">

                <div>

                   <h1
                        class="text-4xl font-bold text-slate-900"
                    >
                        Customer Management
                    </h1>

                    <p
                        class="mt-2 text-gray-500"
                    >
                        Manage Customer master data and business partners.
                    </p>
            <div
                class="mb-4 flex justify-between"
            >

                <input

                    type="text"

                    placeholder="Search Customer..."

                    class="w-72 rounded-xl border border-gray-300 px-4 py-2"

                        />

                    </div>
                            </div>

                            <Link

                                :href="
                                    route(
                                        'customers.create'
                                    )
                                "

                                class="rounded-xl bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"

                            >

                                Create Customer

                            </Link>

                        </div>
            <!-- Card Empty-->
        <div
    class="mb-6 grid gap-4 md:grid-cols-3"
>

    <div
        class="rounded-2xl bg-white p-5 shadow"
    >

        <div
            class="text-sm text-gray-500"
        >
            Total Customer
        </div>

        <div
            class="mt-2 text-3xl font-bold"
        >
            {{ customers.total }}
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
                customers.data.filter(
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
                customers.data.filter(
                    item => !item.status
                ).length
            }}
        </div>

    </div>

</div>
            <!-- end card -->
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
                                customer
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
<!-- Customer empty state-->
<tr
    v-if="
        customers.data.length === 0
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
                No Customer Found
            </div>

            <div
                class="text-gray-500"
            >
                Create your first customer.
            </div>

            <Link

                :href="
                    route(
                        'customers.create'
                    )
                "

                class="inline-block rounded-xl bg-blue-600 px-5 py-2 text-white"

            >

                Create Customer

            </Link>

        </div>

    </td>

</tr>
<!-- end customer empty state-->
                        <tr

                            v-for="
                                customer
                                in customers.data
                            "

                            :key="
                                customer.id
                            "

                        >

                            <td
                                class="px-6 py-4"
                            >
                                {{ customer.customer_code }}
                            </td>

                            <td
                                class="px-6 py-4"
                            >
                                {{ customer.name }}
                            </td>

                            <td
                                class="px-6 py-4"
                            >
                                {{ customer.phone || '-' }}
                            </td>

                            <td
                                class="px-6 py-4"
                            >
                                {{ customer.city || '-' }}
                            </td>

                            <td
                                class="px-6 py-4"
                            >

                                <span

                                    class="rounded-full px-3 py-1 text-xs"

                                    :class="
                                        customer.status
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-red-100 text-red-700'
                                    "

                                >

                                    {{
                                        customer.status
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
                                                    'customers.show',
                                                    customer.id
                                                )
                                            "

                                            class="rounded-lg bg-slate-600 px-3 py-2 text-sm text-white hover:bg-slate-700"

                                        >

                                            View

                                        </Link>

                                        <Link

                                            :href="
                                                route(
                                                    'customers.edit',
                                                    customer.id
                                                )
                                            "

                                            class="rounded-lg bg-amber-500 px-3 py-2 text-sm text-white hover:bg-amber-600"

                                        >

                                            Edit

                                        </Link>

                                        <button

                                            @click="
                                                deleteSupplier(
                                                    customer.id
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