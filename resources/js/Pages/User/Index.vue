<script setup>
import { computed, ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import UserFormModal from './UserFormModal.vue'
import UserViewModal from './UserViewModal.vue'
const props = defineProps({
    users: {
        type: Object,
        required: true,
    },

    filters: {
        type: Object,
        default: () => ({
            search: '',
            per_page: 10,
        }),
    },

    companies: {
        type: Array,
        default: () => [],
    },

    branches: {
        type: Array,
        default: () => [],
    },

    roles: {
        type: Array,
        default: () => [],
    },

    employees: {
    type: Array,
    default: () => [],
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

const perPage = ref(
    props.filters?.per_page ?? 10
)

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

const initials = (name) => {
    if (!name) {
        return 'U'
    }

    return name
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map(word => word.charAt(0).toUpperCase())
        .join('')
}

const userRole = (user) => {
    return user.roles?.[0]?.name ?? 'No Role'
}

const branchNames = (user) => {
    return user.branches ?? []
}

const defaultBranchName = (user) => {
    return user.default_branch?.name ?? '-'
}

/*
|--------------------------------------------------------------------------
| Search
|--------------------------------------------------------------------------
*/

const applySearch = () => {
    router.get(
        route('users.index'),
        {
            search: search.value,
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

const deleteUser = (user) => {
    if (
        !confirm(
            `Hapus user "${user.name}"?`
        )
    ) {
        return
    }

    router.delete(
        route(
            'users.destroy',
            user.id
        ),
        {
            preserveScroll: true,
        }
    )
}
const showModal = ref(false)
const editingUser = ref(null)

const openCreateModal = () => {
    editingUser.value = null
    showModal.value = true
}

const openEditModal = (user) => {
    editingUser.value = user
    showModal.value = true
}

const closeModal = () => {
    showModal.value = false
    editingUser.value = null
}

const showViewModal = ref(false)
const viewingUser = ref(null)
const openViewModal = (user) => {
    viewingUser.value = user
    showViewModal.value = true
}

const closeViewModal = () => {
    showViewModal.value = false
    viewingUser.value = null
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
                        User Management
                    </h1>

                    <p
                        class="
                            mt-1
                            text-sm
                            text-gray-500
                        "
                    >
                        Manage users, roles, and branch access.
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
                    + Add User
                </button>
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
                            placeholder="Search user name or email..."
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
                        v-if="search"
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
                v-if="users.data.length === 0"
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
                    No users found
                </h3>

                <p
                    class="
                        mt-1
                        text-sm
                        text-gray-500
                    "
                >
                    Create your first user to get started.
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
                    Add User
                </button>
            </div>

            <!-- User Cards -->
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
                    v-for="user in users.data"
                    :key="user.id"
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
                    <!-- User Header -->
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
                            {{ initials(user.name) }}
                        </div>

                        <!-- Name / Role -->
                        <div class="min-w-0 flex-1">
                            <h3
                                class="
                                    truncate
                                    text-sm
                                    font-semibold
                                    text-gray-900
                                "
                                :title="user.name"
                            >
                                {{ user.name }}
                            </h3>

                            <p
                                class="
                                    mt-0.5
                                    truncate
                                    text-xs
                                    text-gray-500
                                "
                            >
                                {{ userRole(user) }}
                            </p>
                        </div>

                        <!-- Status -->
                        <span
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
                    </div>

                    <!-- User Info -->
                    <div class="mt-4 space-y-2.5">

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
                                :title="user.email"
                            >
                                {{ user.email }}
                            </p>
                        </div>

                        <!-- Company -->
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
                                Company
                            </p>

                            <p
                                class="
                                    mt-0.5
                                    truncate
                                    text-xs
                                    text-gray-700
                                "
                            >
                                {{
                                    user.company?.company_name
                                    ?? '-'
                                }}
                            </p>
                        </div>

                        <!-- Default Branch -->
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
                                Default Branch
                            </p>

                            <p
                                class="
                                    mt-0.5
                                    truncate
                                    text-xs
                                    text-gray-700
                                "
                            >
                                {{ defaultBranchName(user) }}
                            </p>
                        </div>

                        <!-- Branch Access -->
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
                                Branch Access
                            </p>

                            <div
                                class="
                                    mt-1
                                    flex
                                    flex-wrap
                                    gap-1
                                "
                            >
                                <span
                                    v-for="branch in branchNames(user)"
                                    :key="branch.id"
                                    class="
                                        rounded-md
                                        bg-gray-100
                                        px-2
                                        py-1
                                        text-[10px]
                                        font-medium
                                        text-gray-600
                                    "
                                >
                                    {{ branch.code }}
                                </span>

                                <span
                                    v-if="branchNames(user).length === 0"
                                    class="
                                        text-xs
                                        text-gray-400
                                    "
                                >
                                    No branch
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
                                    'users.show',
                                    user.id
                                )
                            "
                            class="
                                text-xs
                                font-medium
                                text-blue-600
                                hover:text-blue-700
                            "
                            @click.prevent="
                                openViewModal(user)
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
                                    openEditModal(user)
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
                                @click="deleteUser(user)"
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
                    users.links &&
                    users.links.length > 3
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
                        {{ users.from ?? 0 }}
                    </span>
                    -
                    <span class="font-medium">
                        {{ users.to ?? 0 }}
                    </span>
                    of
                    <span class="font-medium">
                        {{ users.total ?? 0 }}
                    </span>
                    users
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
                        v-for="link in users.links"
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
    <UserFormModal
    :show="showModal"
    :user="editingUser"
    :companies="companies"
    :branches="branches"
    :roles="roles"
    :employees="employees"
    @close="closeModal"
    />

    <UserViewModal
    :show="showViewModal"
    :user="viewingUser"
    @close="closeViewModal"
    />
</template>