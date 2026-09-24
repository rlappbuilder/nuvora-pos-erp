<script setup>
import {
    computed,
    ref,
} from 'vue'

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },

    user: {
        type: Object,
        default: null,
    },
})

const emit = defineEmits([
    'close',
])

const activeTab = ref(
    'account'
)

const tabs = [
    {
        key: 'account',
        label: 'Account Information',
    },
    {
        key: 'system',
        label: 'System Information',
    },
]

const roles = computed(() => {
    if (
        !props.user?.roles ||
        !Array.isArray(
            props.user.roles
        )
    ) {
        return []
    }

    return props.user.roles
})

const primaryRole = computed(() => {
    return roles.value[0]?.name ?? '-'
})

const employee = computed(() => {
    return props.user?.employee ?? null
})

const branches = computed(() => {
    if (
        !props.user?.branches ||
        !Array.isArray(
            props.user.branches
        )
    ) {
        return []
    }

    return props.user.branches
})

const defaultBranch = computed(() => {
    return (
        branches.value.find(
            branch =>
                branch.pivot?.is_default
        ) ?? null
    )
})

const formatDateTime = (value) => {
    if (!value) {
        return '-'
    }

    const date = new Date(value)

    if (Number.isNaN(date.getTime())) {
        return value
    }

    return new Intl.DateTimeFormat(
        'en-GB',
        {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        }
    ).format(date)
}

const formattedCreatedAt = computed(() => {
    return formatDateTime(
        props.user?.created_at
    )
})

const formattedUpdatedAt = computed(() => {
    return formatDateTime(
        props.user?.updated_at
    )
})

const statusLabel = computed(() => {
    if (props.user?.deleted_at) {
        return 'Deleted'
    }

    return 'Active'
})

const isDeleted = computed(() => {
    return !!props.user?.deleted_at
})

const close = () => {
    emit('close')
}

const setTab = (tab) => {
    activeTab.value = tab
}
</script>

<template>
    <Teleport to="body">
        <div
            v-if="show"
            class="fixed inset-0 z-[100] flex items-center justify-center p-4"
        >
            <!-- Backdrop -->
            <div
                class="absolute inset-0 bg-black/40"
                @click="close"
            />

            <!-- Modal -->
            <div
                class="relative flex w-full max-w-3xl max-h-[92vh] flex-col overflow-hidden rounded-xl bg-white shadow-2xl"
            >
                <!-- Header -->
                <div
                    class="flex shrink-0 items-start justify-between border-b border-gray-200 px-6 py-4"
                >
                    <div>
                        <h2
                            class="text-lg font-semibold text-gray-900"
                        >
                            User Detail
                        </h2>

                        <p
                            class="mt-1 text-sm text-gray-500"
                        >
                            View user account information
                        </p>
                    </div>

                    <button
                        type="button"
                        class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
                        @click="close"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>

                <!-- Body -->
                <div
                    class="flex-1 overflow-y-auto"
                >
                    <!-- User Header -->
                    <div
                        class="border-b border-gray-200 px-6 py-5"
                    >
                        <div
                            class="flex items-center gap-4"
                        >
                            <!-- Avatar -->
                            <div
                                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-50 text-blue-600"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"
                                    />
                                </svg>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div
                                    class="flex flex-wrap items-center gap-2"
                                >
                                    <h3
                                        class="truncate text-base font-semibold text-gray-900"
                                    >
                                        {{
                                            user?.name ??
                                            '-'
                                        }}
                                    </h3>

                                    <span
                                        class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700"
                                    >
                                        {{
                                            primaryRole
                                        }}
                                    </span>
                                </div>

                                <div
                                    class="mt-1 flex items-center gap-1.5"
                                >
                                    <span
                                        class="h-2 w-2 rounded-full"
                                        :class="
                                            isDeleted
                                                ? 'bg-red-500'
                                                : 'bg-emerald-500'
                                        "
                                    />

                                    <span
                                        class="text-xs font-medium"
                                        :class="
                                            isDeleted
                                                ? 'text-red-600'
                                                : 'text-emerald-600'
                                        "
                                    >
                                        {{
                                            statusLabel
                                        }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabs -->
                    <div
                        class="border-b border-gray-200 px-6"
                    >
                        <div
                            class="flex gap-6"
                        >
                            <button
                                v-for="tab in tabs"
                                :key="tab.key"
                                type="button"
                                class="relative py-3 text-sm font-medium transition"
                                :class="
                                    activeTab === tab.key
                                        ? 'text-blue-600'
                                        : 'text-gray-500 hover:text-gray-700'
                                "
                                @click="
                                    setTab(
                                        tab.key
                                    )
                                "
                            >
                                {{ tab.label }}

                                <span
                                    v-if="
                                        activeTab ===
                                        tab.key
                                    "
                                    class="absolute inset-x-0 bottom-0 h-0.5 rounded-full bg-blue-600"
                                />
                            </button>
                        </div>
                    </div>

                    <!-- Account Information -->
                    <div
                        v-if="
                            activeTab ===
                            'account'
                        "
                        class="px-6 py-5"
                    >
                        <!-- Account Information -->
                        <div>
                            <h4
                                class="mb-4 text-xs font-semibold uppercase tracking-wider text-gray-400"
                            >
                                Account Information
                            </h4>

                            <div
                                class="divide-y divide-gray-100 rounded-lg border border-gray-200"
                            >
                                <!-- Employee -->
                                <div
                                    class="grid grid-cols-1 gap-1 px-4 py-3 sm:grid-cols-3 sm:gap-4"
                                >
                                    <div
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Employee
                                    </div>

                                    <div
                                        class="text-sm text-gray-900 sm:col-span-2"
                                    >
                                        <template
                                            v-if="
                                                employee
                                            "
                                        >
                                            {{
                                                employee.employee_code
                                            }}
                                            -
                                            {{
                                                employee.name
                                            }}
                                        </template>

                                        <span
                                            v-else
                                            class="text-gray-400"
                                        >
                                            -
                                        </span>
                                    </div>
                                </div>

                                <!-- Username -->
                                <div
                                    class="grid grid-cols-1 gap-1 px-4 py-3 sm:grid-cols-3 sm:gap-4"
                                >
                                    <div
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Username
                                    </div>

                                    <div
                                        class="text-sm text-gray-900 sm:col-span-2"
                                    >
                                        {{
                                            user?.name ??
                                            '-'
                                        }}
                                    </div>
                                </div>

                                <!-- Email -->
                                <div
                                    class="grid grid-cols-1 gap-1 px-4 py-3 sm:grid-cols-3 sm:gap-4"
                                >
                                    <div
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Email
                                    </div>

                                    <div
                                        class="break-all text-sm text-gray-900 sm:col-span-2"
                                    >
                                        {{
                                            user?.email ??
                                            '-'
                                        }}
                                    </div>
                                </div>

                                <!-- Role -->
                                <div
                                    class="grid grid-cols-1 gap-1 px-4 py-3 sm:grid-cols-3 sm:gap-4"
                                >
                                    <div
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Role
                                    </div>

                                    <div
                                        class="text-sm text-gray-900 sm:col-span-2"
                                    >
                                        {{
                                            primaryRole
                                        }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Access -->
                        <div class="mt-6">
                            <h4
                                class="mb-4 text-xs font-semibold uppercase tracking-wider text-gray-400"
                            >
                                Access
                            </h4>

                            <div
                                class="divide-y divide-gray-100 rounded-lg border border-gray-200"
                            >
                                <!-- Company -->
                                <div
                                    class="grid grid-cols-1 gap-1 px-4 py-3 sm:grid-cols-3 sm:gap-4"
                                >
                                    <div
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Company
                                    </div>

                                    <div
                                        class="text-sm text-gray-900 sm:col-span-2"
                                    >
                                        {{
                                            user?.company?.company_name ??
                                            '-'
                                        }}
                                    </div>
                                </div>

                                <!-- Branch Access -->
                                <div
                                    class="grid grid-cols-1 gap-1 px-4 py-3 sm:grid-cols-3 sm:gap-4"
                                >
                                    <div
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Branch Access
                                    </div>

                                    <div
                                        class="flex flex-wrap gap-2 sm:col-span-2"
                                    >
                                        <span
                                            v-if="
                                                !branches.length
                                            "
                                            class="text-sm text-gray-400"
                                        >
                                            -
                                        </span>

                                        <span
                                            v-for="branch in branches"
                                            :key="branch.id"
                                            class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700"
                                        >
                                            {{
                                                branch.name
                                            }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Default Branch -->
                                <div
                                    class="grid grid-cols-1 gap-1 px-4 py-3 sm:grid-cols-3 sm:gap-4"
                                >
                                    <div
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Default Branch
                                    </div>

                                    <div
                                        class="text-sm text-gray-900 sm:col-span-2"
                                    >
                                        {{
                                            defaultBranch?.name ??
                                            '-'
                                        }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- System Information -->
                    <div
                        v-else
                        class="px-6 py-5"
                    >
                        <h4
                            class="mb-4 text-xs font-semibold uppercase tracking-wider text-gray-400"
                        >
                            System Information
                        </h4>

                        <div
                            class="divide-y divide-gray-100 rounded-lg border border-gray-200"
                        >
                            <!-- User ID -->
                            <div
                                class="grid grid-cols-1 gap-1 px-4 py-3 sm:grid-cols-3 sm:gap-4"
                            >
                                <div
                                    class="text-sm font-medium text-gray-500"
                                >
                                    User ID
                                </div>

                                <div
                                    class="text-sm text-gray-900 sm:col-span-2"
                                >
                                    #{{ user?.id ?? '-' }}
                                </div>
                            </div>

                            <!-- Status -->
                            <div
                                class="grid grid-cols-1 gap-1 px-4 py-3 sm:grid-cols-3 sm:gap-4"
                            >
                                <div
                                    class="text-sm font-medium text-gray-500"
                                >
                                    Status
                                </div>

                                <div
                                    class="sm:col-span-2"
                                >
                                    <span
                                        class="inline-flex items-center gap-1.5 text-sm font-medium"
                                        :class="
                                            isDeleted
                                                ? 'text-red-600'
                                                : 'text-emerald-600'
                                        "
                                    >
                                        <span
                                            class="h-2 w-2 rounded-full"
                                            :class="
                                                isDeleted
                                                    ? 'bg-red-500'
                                                    : 'bg-emerald-500'
                                            "
                                        />

                                        {{
                                            statusLabel
                                        }}
                                    </span>
                                </div>
                            </div>

                            <!-- Created -->
                            <div
                                class="grid grid-cols-1 gap-1 px-4 py-3 sm:grid-cols-3 sm:gap-4"
                            >
                                <div
                                    class="text-sm font-medium text-gray-500"
                                >
                                    Created
                                </div>

                                <div
                                    class="text-sm text-gray-900 sm:col-span-2"
                                >
                                    {{
                                        formattedCreatedAt
                                    }}
                                </div>
                            </div>

                            <!-- Updated -->
                            <div
                                class="grid grid-cols-1 gap-1 px-4 py-3 sm:grid-cols-3 sm:gap-4"
                            >
                                <div
                                    class="text-sm font-medium text-gray-500"
                                >
                                    Updated
                                </div>

                                <div
                                    class="text-sm text-gray-900 sm:col-span-2"
                                >
                                    {{
                                        formattedUpdatedAt
                                    }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div
                    class="flex shrink-0 items-center justify-end gap-3 border-t border-gray-200 bg-gray-50 px-6 py-4"
                >
                    <button
                        type="button"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-100"
                        @click="close"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>