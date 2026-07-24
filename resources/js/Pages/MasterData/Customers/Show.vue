<script setup>

import { Head, Link } from '@inertiajs/vue3'

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import AppLayout from '@/Layouts/AppLayout.vue'
const props = defineProps({

    customer: Object

})

</script>

<<template>

    <Head title="Customer Detail" />

    <AppLayout>

        <div class="space-y-6">

            <div
                class="flex items-center justify-between"
            >

                <div>

                    <h1
                        class="text-2xl font-bold"
                    >
                        Customer Detail
                    </h1>

                    <p
                        class="text-sm text-gray-500"
                    >
                        View Customer information
                    </p>

                </div>

                <Link

                    :href="
                        route(
                            'customers.index'
                        )
                    "

                    class="rounded-xl bg-blue-600 px-4 py-2 text-white hover:bg-gray-700"

                >

                    Back

                </Link>

            </div>

            <div
                class="rounded-2xl bg-white p-6 shadow"
            >

                <div
                    class="grid gap-6 md:grid-cols-2"
                >

                    <div>

                        <div
                            class="text-sm text-gray-500"
                        >
                            Customer Code
                        </div>

                        <div
                            class="font-medium"
                        >
                            {{ customer.customer_code }}
                        </div>

                    </div>

                    <div>

                        <div
                            class="text-sm text-gray-500"
                        >
                            Customer Name
                        </div>

                        <div
                            class="font-medium"
                        >
                            {{ customer.name }}
                        </div>

                    </div>

                    <div>

                        <div
                            class="text-sm text-gray-500"
                        >
                            Contact Person
                        </div>

                        <div
                            class="font-medium"
                        >
                            {{ customer.contact_person || '-' }}
                        </div>

                    </div>

                    <div>

                        <div
                            class="text-sm text-gray-500"
                        >
                            Phone
                        </div>

                        <div
                            class="font-medium"
                        >
                            {{ customer.phone || '-' }}
                        </div>

                    </div>

                    <div>

                        <div
                            class="text-sm text-gray-500"
                        >
                            Email
                        </div>

                        <div
                            class="font-medium"
                        >
                            {{ customer.email || '-' }}
                        </div>

                    </div>

                    <div>

                        <div
                            class="text-sm text-gray-500"
                        >
                            City
                        </div>

                        <div
                            class="font-medium"
                        >
                            {{ customer.city || '-' }}
                        </div>

                    </div>

                    <div>

                        <div
                            class="text-sm text-gray-500"
                        >
                            Tax Number
                        </div>

                        <div
                            class="font-medium"
                        >
                            {{ customer.tax_number || '-' }}
                        </div>

                    </div>

                    <div>

                        <div
                            class="text-sm text-gray-500"
                        >
                            Payment Term
                        </div>

                        <div
                            class="font-medium"
                        >
                            {{ customer.payment_term }} Days
                        </div>

                    </div>
                    <div>

                        <div
                            class="text-sm text-gray-500"
                        >
                            Credit Limit
                        </div>

                        <div
                            class="font-medium"
                        >
                           {{
                                Number(
                                    customer.credit_limit
                                ).toLocaleString(
                                    'id-ID'
                                )
                            }}
                        </div>

                    </div>

                    <div>

                        <div
                            class="text-sm text-gray-500"
                        >
                            Status
                        </div>

                        <div
                            class="font-medium"
                        >
                            {{
                                customer.status
                                    ? 'Active'
                                    : 'Inactive'
                            }}
                        </div>

                    </div>

                </div>

                <div
                    class="mt-6"
                >

                    <div
                        class="text-sm text-gray-500"
                    >
                        Address
                    </div>

                    <div
                        class="mt-1"
                    >
                        {{ customer.address || '-' }}
                    </div>

                </div>

            </div>

        </div>

    </AppLayout>

</template>