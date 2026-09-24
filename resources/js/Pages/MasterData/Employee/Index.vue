<script setup>
import { ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

import EmployeeFormModal from './EmployeeFormModal.vue'
import EmployeeViewModal from './EmployeeViewModal.vue'

const props = defineProps({
    employees: {
        type: Object,
        required: true,
    },

    filters: {
        type: Object,
        default: () => ({
            search: '',
            status: '',
            per_page: 10,
            sort: 'employee_code',
            direction: 'asc',
        }),
    },

    stats: {
        type: Object,
        default: () => ({
            total: 0,
            active: 0,
            inactive: 0,
            assigned: 0,
            unassigned: 0,
            deleted: 0,
        }),
    },
})

/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const search = ref(
    props.filters?.search ?? ''
)

const status = ref(
    props.filters?.status ?? ''
)

const perPage = ref(
    props.filters?.per_page ?? 10
)

const showModal = ref(false)
const editingEmployee = ref(null)

const showViewModal = ref(false)
const viewingEmployee = ref(null)

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

const initials = (name) => {
    if (!name) {
        return 'E'
    }

    return name
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map(word =>
            word.charAt(0).toUpperCase()
        )
        .join('')
}

const userAccountStatus = (employee) => {
    return employee.user
        ? 'Assigned'
        : 'Not Assigned'
}

const username = (employee) => {
    return employee.user?.name ?? '-'
}

/*
|--------------------------------------------------------------------------
| Search
|--------------------------------------------------------------------------
*/

const applySearch = () => {
    router.get(
        route('employees.index'),
        {
            search: search.value,
            status: status.value,
            per_page: perPage.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    )
}

const clearSearch = () => {
    search.value = ''
    status.value = ''

    applySearch()
}

/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/

const changePage = (url) => {
    if (!url) {
        return
    }

    router.visit(url, {
        preserveState: true,
        preserveScroll: true,
    })
}

/*
|--------------------------------------------------------------------------
| Delete
|--------------------------------------------------------------------------
*/

const deleteEmployee = (employee) => {
    if (employee.user) {
        alert(
            'Employee cannot be deleted because it is linked to a user account.'
        )

        return
    }

    if (
        !confirm(
            `Hapus employee "${employee.name}"?`
        )
    ) {
        return
    }

    router.delete(
        route(
            'employees.destroy',
            employee.id
        ),
        {
            preserveScroll: true,
        }
    )
}

/*
|--------------------------------------------------------------------------
| Create / Edit
|--------------------------------------------------------------------------
*/

const openCreateModal = () => {
    editingEmployee.value = null
    showModal.value = true
}

const openEditModal = (employee) => {
    editingEmployee.value = employee
    showModal.value = true
}

const closeModal = () => {
    showModal.value = false
    editingEmployee.value = null
}

/*
|--------------------------------------------------------------------------
| View
|--------------------------------------------------------------------------
*/

const openViewModal = (employee) => {
    viewingEmployee.value = employee
    showViewModal.value = true
}

const closeViewModal = () => {
    showViewModal.value = false
    viewingEmployee.value = null
}
</script>

<template>
    <AppLayout>
        <div class="space-y-6">

            <!-- Header -->
            <div
                class="
                    flex
                    flex-col
                    gap-4
                    sm:flex-row
                    sm:items-center
                    sm:justify-between
                "
            >
                <div>
                    <h1
                        class="
                            text-2xl
                            font-semibold
                            text-gray-900
                        "
                    >
                        Employee Management
                    </h1>

                    <p
                        class="
                            mt-1
                            text-sm
                            text-gray-500
                        "
                    >
                        Manage employee information and user account assignments.
                    </p>
                </div>

                <button
                    type="button"
                    class="
                        inline-flex
                        items-center
                        justify-center
                        rounded-lg
                        bg-blue-600
                        px-4
                        py-2.5
                        text-sm
                        font-medium
                        text-white
                        transition
                        hover:bg-blue-700
                    "
                    @click="openCreateModal"
                >
                    + Add Employee
                </button>
            </div>

            <!-- Statistics -->
            <div
                class="
                    grid
                    grid-cols-2
                    gap-3
                    sm:grid-cols-3
                    lg:grid-cols-5
                "
            >
                <div
                    class="
                        rounded-xl
                        border
                        border-gray-200
                        bg-white
                        p-4
                        shadow-sm
                    "
                >
                    <p
                        class="
                            text-xs
                            font-medium
                            text-gray-500
                        "
                    >
                        Total
                    </p>

                    <p
                        class="
                            mt-1
                            text-xl
                            font-semibold
                            text-gray-900
                        "
                    >
                        {{ stats.total }}
                    </p>
                </div>

                <div
                    class="
                        rounded-xl
                        border
                        border-gray-200
                        bg-white
                        p-4
                        shadow-sm
                    "
                >
                    <p
                        class="
                            text-xs
                            font-medium
                            text-gray-500
                        "
                    >
                        Active
                    </p>

                    <p
                        class="
                            mt-1
                            text-xl
                            font-semibold
                            text-green-600
                        "
                    >
                        {{ stats.active }}
                    </p>
                </div>

                <div
                    class="
                        rounded-xl
                        border
                        border-gray-200
                        bg-white
                        p-4
                        shadow-sm
                    "
                >
                    <p
                        class="
                            text-xs
                            font-medium
                            text-gray-500
                        "
                    >
                        Inactive
                    </p>

                    <p
                        class="
                            mt-1
                            text-xl
                            font-semibold
                            text-gray-600
                        "
                    >
                        {{ stats.inactive }}
                    </p>
                </div>

                <div
                    class="
                        rounded-xl
                        border
                        border-gray-200
                        bg-white
                        p-4
                        shadow-sm
                    "
                >
                    <p
                        class="
                            text-xs
                            font-medium
                            text-gray-500
                        "
                    >
                        Assigned
                    </p>

                    <p
                        class="
                            mt-1
                            text-xl
                            font-semibold
                            text-blue-600
                        "
                    >
                        {{ stats.assigned }}
                    </p>
                </div>

                <div
                    class="
                        rounded-xl
                        border
                        border-gray-200
                        bg-white
                        p-4
                        shadow-sm
                    "
                >
                    <p
                        class="
                            text-xs
                            font-medium
                            text-gray-500
                        "
                    >
                        Unassigned
                    </p>

                    <p
                        class="
                            mt-1
                            text-xl
                            font-semibold
                            text-orange-600
                        "
                    >
                        {{ stats.unassigned }}
                    </p>
                </div>
            </div>

            <!-- Search -->
            <div
                class="
                    rounded-xl
                    border
                    border-gray-200
                    bg-white
                    p-4
                    shadow-sm
                "
            >
                <div
                    class="
                        flex
                        flex-col
                        gap-3
                        sm:flex-row
                    "
                >
                    <div class="flex-1">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search employee code, name, email, or position..."
                            class="
                                w-full
                                rounded-lg
                                border
                                border-gray-300
                                px-3
                                py-2.5
                                text-sm
                                outline-none
                                transition
                                focus:border-blue-500
                                focus:ring-2
                                focus:ring-blue-100
                            "
                            @keyup.enter="applySearch"
                        />
                    </div>

                    <select
                        v-model="status"
                        class="
                            rounded-lg
                            border
                            border-gray-300
                            px-3
                            py-2.5
                            text-sm
                            text-gray-700
                            outline-none
                            focus:border-blue-500
                            focus:ring-2
                            focus:ring-blue-100
                        "
                        @change="applySearch"
                    >
                        <option value="">
                            All Status
                        </option>

                        <option value="1">
                            Active
                        </option>

                        <option value="0">
                            Inactive
                        </option>
                    </select>

                    <button
                        type="button"
                        class="
                            rounded-lg
                            bg-blue-600
                            px-4
                            py-2.5
                            text-sm
                            font-medium
                            text-white
                            hover:bg-blue-700
                        "
                        @click="applySearch"
                    >
                        Search
                    </button>

                    <button
                        v-if="search || status"
                        type="button"
                        class="
                            rounded-lg
                            border
                            border-gray-300
                            px-4
                            py-2.5
                            text-sm
                            font-medium
                            text-gray-700
                            hover:bg-gray-50
                        "
                        @click="clearSearch"
                    >
                        Clear
                    </button>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-if="employees.data.length === 0"
                class="
                    rounded-xl
                    border
                    border-dashed
                    border-gray-300
                    bg-white
                    px-6
                    py-12
                    text-center
                "
            >
                <div
                    class="
                        mx-auto
                        flex
                        h-12
                        w-12
                        items-center
                        justify-center
                        rounded-full
                        bg-gray-100
                        text-xl
                    "
                >
                    👤
                </div>

                <h3
                    class="
                        mt-4
                        text-sm
                        font-semibold
                        text-gray-900
                    "
                >
                    No employees found
                </h3>

                <p
                    class="
                        mt-1
                        text-sm
                        text-gray-500
                    "
                >
                    Create your first employee to get started.
                </p>

                <button
                    type="button"
                    class="
                        mt-4
                        rounded-lg
                        bg-blue-600
                        px-4
                        py-2
                        text-sm
                        font-medium
                        text-white
                        hover:bg-blue-700
                    "
                    @click="openCreateModal"
                >
                    Add Employee
                </button>
            </div>

            <!-- Employee Cards -->
            <div
                v-else
                class="
                    grid
                    grid-cols-1
                    gap-4
                    md:grid-cols-2
                    xl:grid-cols-3
                    2xl:grid-cols-4
                "
            >
                <div
                    v-for="employee in employees.data"
                    :key="employee.id"
                    class="
                        rounded-xl
                        border
                        border-gray-200
                        bg-white
                        p-4
                        shadow-sm
                        transition
                        hover:shadow-md
                    "
                >
                    <!-- Employee Header -->
                    <div
                        class="
                            flex
                            items-start
                            gap-3
                        "
                    >
                        <!-- Avatar -->
                        <div
                            class="
                                flex
                                h-11
                                w-11
                                shrink-0
                                items-center
                                justify-center
                                rounded-full
                                bg-blue-100
                                text-sm
                                font-semibold
                                text-blue-700
                            "
                        >
                            {{ initials(employee.name) }}
                        </div>

                        <!-- Name -->
                        <div class="min-w-0 flex-1">
                            <h3
                                class="
                                    truncate
                                    text-sm
                                    font-semibold
                                    text-gray-900
                                "
                                :title="employee.name"
                            >
                                {{ employee.name }}
                            </h3>

                            <p
                                class="
                                    mt-0.5
                                    truncate
                                    text-xs
                                    text-gray-500
                                "
                            >
                                {{ employee.position ?? 'No Position' }}
                            </p>
                        </div>

                        <!-- Status -->
                        <span
                            v-if="employee.status"
                            class="
                                shrink-0
                                rounded-full
                                bg-green-100
                                px-2
                                py-1
                                text-[10px]
                                font-medium
                                text-green-700
                            "
                        >
                            Active
                        </span>

                        <span
                            v-else
                            class="
                                shrink-0
                                rounded-full
                                bg-gray-100
                                px-2
                                py-1
                                text-[10px]
                                font-medium
                                text-gray-600
                            "
                        >
                            Inactive
                        </span>
                    </div>

                    <!-- Employee Info -->
                    <div class="mt-4 space-y-2.5">

                        <!-- Employee Code -->
                        <div>
                            <p
                                class="
                                    text-[10px]
                                    font-medium
                                    uppercase
                                    tracking-wide
                                    text-gray-400
                                "
                            >
                                Employee Code
                            </p>

                            <p
                                class="
                                    mt-0.5
                                    text-xs
                                    font-semibold
                                    text-gray-700
                                "
                            >
                                {{ employee.employee_code }}
                            </p>
                        </div>

                        <!-- Email -->
                        <div>
                            <p
                                class="
                                    text-[10px]
                                    font-medium
                                    uppercase
                                    tracking-wide
                                    text-gray-400
                                "
                            >
                                Email
                            </p>

                            <p
                                class="
                                    mt-0.5
                                    truncate
                                    text-xs
                                    text-gray-700
                                "
                                :title="employee.email"
                            >
                                {{ employee.email ?? '-' }}
                            </p>
                        </div>

                        <!-- Phone -->
                        <div>
                            <p
                                class="
                                    text-[10px]
                                    font-medium
                                    uppercase
                                    tracking-wide
                                    text-gray-400
                                "
                            >
                                Phone
                            </p>

                            <p
                                class="
                                    mt-0.5
                                    text-xs
                                    text-gray-700
                                "
                            >
                                {{ employee.phone ?? '-' }}
                            </p>
                        </div>

                        <!-- User Account -->
                        <div>
                            <p
                                class="
                                    text-[10px]
                                    font-medium
                                    uppercase
                                    tracking-wide
                                    text-gray-400
                                "
                            >
                                User Account
                            </p>

                            <div
                                class="
                                    mt-1
                                    flex
                                    items-center
                                    gap-2
                                "
                            >
                                <span
                                    v-if="employee.user"
                                    class="
                                        rounded-md
                                        bg-blue-100
                                        px-2
                                        py-1
                                        text-[10px]
                                        font-medium
                                        text-blue-700
                                    "
                                >
                                    Assigned
                                </span>

                                <span
                                    v-else
                                    class="
                                        rounded-md
                                        bg-gray-100
                                        px-2
                                        py-1
                                        text-[10px]
                                        font-medium
                                        text-gray-500
                                    "
                                >
                                    Not Assigned
                                </span>

                                <span
                                    v-if="employee.user"
                                    class="
                                        truncate
                                        text-[10px]
                                        text-gray-500
                                    "
                                >
                                    {{ username(employee) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div
                        class="
                            mt-4
                            flex
                            items-center
                            justify-between
                            border-t
                            border-gray-100
                            pt-3
                        "
                    >
                        <Link
                            :href="
                                route(
                                    'employees.show',
                                    employee.id
                                )
                            "
                            class="
                                text-xs
                                font-medium
                                text-blue-600
                                hover:text-blue-700
                            "
                            @click.prevent="
                                openViewModal(employee)
                            "
                        >
                            View Details
                        </Link>

                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                class="
                                    rounded-md
                                    px-2.5
                                    py-1.5
                                    text-xs
                                    font-medium
                                    text-gray-600
                                    hover:bg-gray-100
                                "
                                @click="
                                    openEditModal(employee)
                                "
                            >
                                Edit
                            </button>

                            <button
                                type="button"
                                class="
                                    rounded-md
                                    px-2.5
                                    py-1.5
                                    text-xs
                                    font-medium
                                    text-red-600
                                    hover:bg-red-50
                                "
                                @click="
                                    deleteEmployee(employee)
                                "
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div
                v-if="
                    employees.links &&
                    employees.links.length > 3
                "
                class="
                    flex
                    flex-wrap
                    items-center
                    justify-between
                    gap-3
                    rounded-xl
                    border
                    border-gray-200
                    bg-white
                    px-4
                    py-3
                "
            >
                <p
                    class="
                        text-xs
                        text-gray-500
                    "
                >
                    Showing
                    <span class="font-medium">
                        {{ employees.from ?? 0 }}
                    </span>
                    -
                    <span class="font-medium">
                        {{ employees.to ?? 0 }}
                    </span>
                    of
                    <span class="font-medium">
                        {{ employees.total ?? 0 }}
                    </span>
                    employees
                </p>

                <div
                    class="
                        flex
                        flex-wrap
                        items-center
                        gap-1
                    "
                >
                    <button
                        v-for="link in employees.links"
                        :key="link.label"
                        type="button"
                        :disabled="!link.url"
                        class="
                            rounded-md
                            px-2.5
                            py-1.5
                            text-xs
                            transition
                        "
                        :class="[
                            link.active
                                ? 'bg-blue-600 text-white'
                                : link.url
                                    ? 'text-gray-600 hover:bg-gray-100'
                                    : 'cursor-not-allowed text-gray-300'
                        ]"
                        @click="changePage(link.url)"
                        v-html="link.label"
                    />
                </div>
            </div>

        </div>
    </AppLayout>

    <!-- Employee Form Modal -->
    <EmployeeFormModal
        :show="showModal"
        :employee="editingEmployee"
        @close="closeModal"
    />

    <!-- Employee View Modal -->
    <EmployeeViewModal
        :show="showViewModal"
        :employee="viewingEmployee"
        @close="closeViewModal"
    />
</template>