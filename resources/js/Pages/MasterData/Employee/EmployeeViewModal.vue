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

    employee: {
        type: Object,
        default: null,
    },
})

const emit = defineEmits([
    'close',
])

const activeTab = ref(
    'employee'
)

const tabs = [
    {
        key: 'employee',
        label: 'Employee Information',
    },
    {
        key: 'system',
        label: 'System Information',
    },
]

/*
|--------------------------------------------------------------------------
| Employee
|--------------------------------------------------------------------------
*/

const employee = computed(() => {
    return props.employee ?? null
})

/*
|--------------------------------------------------------------------------
| User Account
|--------------------------------------------------------------------------
*/

const user = computed(() => {
    return employee.value?.user ?? null
})

const hasUserAccount = computed(() => {
    return !!user.value
})

const username = computed(() => {
    return user.value?.name ?? '-'
})

const userAccountStatus = computed(() => {
    return hasUserAccount.value
        ? 'Assigned'
        : 'Not Assigned'
})

/*
|--------------------------------------------------------------------------
| Status
|--------------------------------------------------------------------------
*/

const isActive = computed(() => {
    return Boolean(
        employee.value?.status
    )
})

const statusLabel = computed(() => {
    return isActive.value
        ? 'Active'
        : 'Inactive'
})

/*
|--------------------------------------------------------------------------
| Date
|--------------------------------------------------------------------------
*/

const formatDate = (value) => {
    if (!value) {
        return '-'
    }

    const date = new Date(value)

    if (
        Number.isNaN(
            date.getTime()
        )
    ) {
        return value
    }

    return new Intl.DateTimeFormat(
        'en-GB',
        {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        }
    ).format(date)
}

const formatDateTime = (value) => {
    if (!value) {
        return '-'
    }

    const date = new Date(value)

    if (
        Number.isNaN(
            date.getTime()
        )
    ) {
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

const formattedJoinDate = computed(() => {
    return formatDate(
        employee.value?.join_date
    )
})

const formattedCreatedAt = computed(() => {
    return formatDateTime(
        employee.value?.created_at
    )
})

const formattedUpdatedAt = computed(() => {
    return formatDateTime(
        employee.value?.updated_at
    )
})

/*
|--------------------------------------------------------------------------
| Close
|--------------------------------------------------------------------------
*/

const close = () => {
    emit('close')
}

/*
|--------------------------------------------------------------------------
| Tab
|--------------------------------------------------------------------------
*/

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
                            Employee Detail
                        </h2>

                        <p
                            class="mt-1 text-sm text-gray-500"
                        >
                            View employee information
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
                    <!-- Employee Header -->
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
                                            employee?.name ??
                                            '-'
                                        }}
                                    </h3>

                                    <span
                                        class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700"
                                    >
                                        {{
                                            employee?.position ??
                                            'Employee'
                                        }}
                                    </span>
                                </div>

                                <div
                                    class="mt-1 flex items-center gap-2"
                                >
                                    <span
                                        class="text-xs text-gray-400"
                                    >
                                        {{
                                            employee?.employee_code ??
                                            '-'
                                        }}
                                    </span>

                                    <span
                                        class="text-gray-300"
                                    >
                                        •
                                    </span>

                                    <span
                                        class="flex items-center gap-1.5 text-xs font-medium"
                                        :class="
                                            isActive
                                                ? 'text-emerald-600'
                                                : 'text-gray-500'
                                        "
                                    >
                                        <span
                                            class="h-2 w-2 rounded-full"
                                            :class="
                                                isActive
                                                    ? 'bg-emerald-500'
                                                    : 'bg-gray-400'
                                            "
                                        />

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

                    <!-- Employee Information -->
                    <div
                        v-if="
                            activeTab ===
                            'employee'
                        "
                        class="px-6 py-5"
                    >
                        <h4
                            class="mb-4 text-xs font-semibold uppercase tracking-wider text-gray-400"
                        >
                            Employee Information
                        </h4>

                        <div
                            class="divide-y divide-gray-100 rounded-lg border border-gray-200"
                        >
                            <!-- Employee Code -->
                            <div
                                class="grid grid-cols-1 gap-1 px-4 py-3 sm:grid-cols-3 sm:gap-4"
                            >
                                <div
                                    class="text-sm font-medium text-gray-500"
                                >
                                    Employee Code
                                </div>

                                <div
                                    class="text-sm font-medium text-gray-900 sm:col-span-2"
                                >
                                    {{
                                        employee?.employee_code ??
                                        '-'
                                    }}
                                </div>
                            </div>

                            <!-- Name -->
                            <div
                                class="grid grid-cols-1 gap-1 px-4 py-3 sm:grid-cols-3 sm:gap-4"
                            >
                                <div
                                    class="text-sm font-medium text-gray-500"
                                >
                                    Employee Name
                                </div>

                                <div
                                    class="text-sm text-gray-900 sm:col-span-2"
                                >
                                    {{
                                        employee?.name ??
                                        '-'
                                    }}
                                </div>
                            </div>

                            <!-- Phone -->
                            <div
                                class="grid grid-cols-1 gap-1 px-4 py-3 sm:grid-cols-3 sm:gap-4"
                            >
                                <div
                                    class="text-sm font-medium text-gray-500"
                                >
                                    Phone
                                </div>

                                <div
                                    class="text-sm text-gray-900 sm:col-span-2"
                                >
                                    {{
                                        employee?.phone ??
                                        '-'
                                    }}
                                </div>
                            </div>

                            <!-- Email -->
                            <div
                                class="grid grid-cols-1 gap-1 px-4 py-3 sm:gap-4 sm:grid-cols-3"
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
                                        employee?.email ??
                                        '-'
                                    }}
                                </div>
                            </div>

                            <!-- Position -->
                            <div
                                class="grid grid-cols-1 gap-1 px-4 py-3 sm:grid-cols-3 sm:gap-4"
                            >
                                <div
                                    class="text-sm font-medium text-gray-500"
                                >
                                    Position
                                </div>

                                <div
                                    class="text-sm text-gray-900 sm:col-span-2"
                                >
                                    {{
                                        employee?.position ??
                                        '-'
                                    }}
                                </div>
                            </div>

                            <!-- Join Date -->
                            <div
                                class="grid grid-cols-1 gap-1 px-4 py-3 sm:grid-cols-3 sm:gap-4"
                            >
                                <div
                                    class="text-sm font-medium text-gray-500"
                                >
                                    Join Date
                                </div>

                                <div
                                    class="text-sm text-gray-900 sm:col-span-2"
                                >
                                    {{
                                        formattedJoinDate
                                    }}
                                </div>
                            </div>
                        </div>

                        <!-- User Account -->
                        <div class="mt-6">
                            <h4
                                class="mb-4 text-xs font-semibold uppercase tracking-wider text-gray-400"
                            >
                                User Account
                            </h4>

                            <div
                                class="divide-y divide-gray-100 rounded-lg border border-gray-200"
                            >
                                <!-- Account Status -->
                                <div
                                    class="grid grid-cols-1 gap-1 px-4 py-3 sm:grid-cols-3 sm:gap-4"
                                >
                                    <div
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Account Status
                                    </div>

                                    <div
                                        class="sm:col-span-2"
                                    >
                                        <span
                                            v-if="
                                                hasUserAccount
                                            "
                                            class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700"
                                        >
                                            Assigned
                                        </span>

                                        <span
                                            v-else
                                            class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600"
                                        >
                                            Not Assigned
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
                                            username
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
                            <!-- Employee ID -->
                            <div
                                class="grid grid-cols-1 gap-1 px-4 py-3 sm:grid-cols-3 sm:gap-4"
                            >
                                <div
                                    class="text-sm font-medium text-gray-500"
                                >
                                    Employee ID
                                </div>

                                <div
                                    class="text-sm text-gray-900 sm:col-span-2"
                                >
                                    #{{ employee?.id ?? '-' }}
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
                                            isActive
                                                ? 'text-emerald-600'
                                                : 'text-gray-500'
                                        "
                                    >
                                        <span
                                            class="h-2 w-2 rounded-full"
                                            :class="
                                                isActive
                                                    ? 'bg-emerald-500'
                                                    : 'bg-gray-400'
                                            "
                                        />

                                        {{
                                            statusLabel
                                        }}
                                    </span>
                                </div>
                            </div>

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
                                    {{
                                        user?.id
                                            ? '#' +
                                              user.id
                                            : '-'
                                    }}
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