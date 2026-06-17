<script setup>

import { Head, Link } from '@inertiajs/vue3'

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({

    supplier: Object

})

</script>

<<template>

    <Head title="Supplier Detail" />

    <AuthenticatedLayout>

        <div class="space-y-6">

            <div
                class="flex items-center justify-between"
            >

                <div>

                    <h1
                        class="text-2xl font-bold"
                    >
                        Supplier Detail
                    </h1>

                    <p
                        class="text-sm text-gray-500"
                    >
                        View supplier information
                    </p>

                </div>

                <Link

                    :href="
                        route(
                            'suppliers.index'
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
                            Supplier Code
                        </div>

                        <div
                            class="font-medium"
                        >
                            {{ supplier.supplier_code }}
                        </div>

                    </div>

                    <div>

                        <div
                            class="text-sm text-gray-500"
                        >
                            Supplier Name
                        </div>

                        <div
                            class="font-medium"
                        >
                            {{ supplier.name }}
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
                            {{ supplier.contact_person || '-' }}
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
                            {{ supplier.phone || '-' }}
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
                            {{ supplier.email || '-' }}
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
                            {{ supplier.city || '-' }}
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
                            {{ supplier.tax_number || '-' }}
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
                            {{ supplier.payment_term }} Days
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
                                supplier.status
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
                        {{ supplier.address || '-' }}
                    </div>

                </div>

            </div>

        </div>

    </AuthenticatedLayout>

</template>