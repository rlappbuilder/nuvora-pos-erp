<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import BaseToast from '@/Components/UI/BaseToast.vue'

const page = usePage()

const props = defineProps({

    companies: Object

})

const deleteCompany = (id) => {

    if (
        confirm(
            'Are you sure you want to delete this company ?'
        )
    ) {

        router.delete(

            route(
                'companies.destroy',
                id
            )

        )

    }

}
</script>

<template>

    <Head title="Company Management" />

    <AuthenticatedLayout>

        <template #header>

            <div
                class="flex items-center justify-between"
            >

                <div>

                    <h2
                        class="text-3xl font-bold text-gray-800"
                    >
                        Company Management
                    </h2>

                    <p
                        class="mt-1 text-sm text-gray-500"
                    >
                        Manage your organization and business entities.
                    </p>

                </div>

                <Link

                    :href="route('companies.create')"

                    class="rounded-xl bg-blue-600 px-5 py-3 font-medium text-white transition hover:bg-blue-700"

                >

                    + Add Company

                </Link>

            </div>

        </template>

        <!-- Empty State -->

        <div

            v-if="companies.data.length == 0"

            class="rounded-3xl bg-white p-16 text-center shadow-sm"

        >

            <div
                class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-slate-100 text-5xl"
            >

                🏢

            </div>

            <h3
                class="text-2xl font-bold text-gray-800"
            >

                No company available

            </h3>

            <p
                class="mt-2 text-gray-500"
            >

                Create your first company profile.

            </p>

            <div
                class="mt-8"
            >

                <Link

                    :href="route('companies.create')"

                    class="rounded-xl bg-blue-600 px-6 py-3 text-white hover:bg-blue-700"

                >

                    + Add Company

                </Link>

            </div>

        </div>

        <!-- Company Card -->

        <div

            v-else

            class="grid gap-6 md:grid-cols-2 xl:grid-cols-3"

        >

            <div

                v-for="company in companies.data"

                :key="company.id"

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

                        {{ company.company_name }}

                    </h3>

                    <p
                        class="mt-2 text-center text-gray-500"
                    >

                        {{ company.company_code }}

                    </p>

                    <div
                        class="my-6 border-t"
                    ></div>

                    <div
                        class="space-y-3"
                    >

                        <div
                            class="flex"
                        >

                            <div
                                class="w-8"
                            >
                                📞
                            </div>

                            <div>
                                {{ company.phone ?? '-' }}
                            </div>

                        </div>

                        <div
                            class="flex"
                        >

                            <div
                                class="w-8"
                            >
                                ✉
                            </div>

                            <div>
                                {{ company.email ?? '-' }}
                            </div>

                        </div>

                        <div
                            class="flex"
                        >

                            <div
                                class="w-8"
                            >
                                📍
                            </div>

                            <div>

                                {{ company.city ?? '-' }}

                                <span
                                    v-if="company.province"
                                >

                                    ,

                                    {{ company.province }}

                                </span>

                            </div>

                        </div>

                    </div>

                    <div
                        class="my-6 border-t"
                    ></div>

                    <!-- Future Counter -->

                    <div
                        class="grid grid-cols-2 gap-4 text-center"
                    >

                        <div>

                            <div
                                class="text-2xl font-bold text-blue-600"
                            >

                                0

                            </div>

                            <div
                                class="text-sm text-gray-500"
                            >

                                Branches

                            </div>

                        </div>

                        <div>

                            <div
                                class="text-2xl font-bold text-blue-600"
                            >

                                0

                            </div>

                            <div
                                class="text-sm text-gray-500"
                            >

                                Warehouses

                            </div>

                        </div>

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
                                'companies.show',
                                company.id
                            )"

                            class="rounded-xl bg-slate-600 px-4 py-2 text-sm text-white hover:bg-slate-700"

                        >

                            View

                        </Link>

                        <Link

                            :href="route(
                                'companies.edit',
                                company.id
                            )"

                            class="rounded-xl bg-amber-500 px-4 py-2 text-sm text-white hover:bg-amber-600"

                        >

                            Edit

                        </Link>

                        <button

                            @click="
                                deleteCompany(
                                    company.id
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