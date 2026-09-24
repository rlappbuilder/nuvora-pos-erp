<script setup>
import {computed,ref, watch,} from 'vue'
import { success, error } from '@/Utils'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },

    user: {
        type: Object,
        default: null,
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

const emit = defineEmits([
    'close',
])

const processing = ref(false)
const errors = ref({})
const showPassword = ref(false)

const form = ref({
    employee_id: '',
    name: '',
    email: '',
    company_id: '',
    role: '',
    branch_ids: [],
    default_branch_id: '',
    password: '',
    password_confirmation: '',
})

const isEdit = computed(() => {
    return !!props.user
})

const modalTitle = computed(() => {
    return isEdit.value
        ? 'Edit User'
        : 'Add User'
})

const modalDescription = computed(() => {
    return isEdit.value
        ? 'Update user account and access settings.'
        : 'Create a new user account and assign access.'
})

/*
|--------------------------------------------------------------------------
| Employees
|--------------------------------------------------------------------------
*/

const availableEmployees = computed(() => {
    const employees = [
        ...props.employees,
    ]

    /*
     * Saat edit, employee yang sedang terhubung
     * mungkin sudah tidak masuk daftar employee
     * karena query backend hanya mengambil
     * employee dengan user_id NULL.
     *
     * Masukkan employee current user secara manual.
     */
    if (
        isEdit.value &&
        props.user?.employee
    ) {
        const currentEmployee =
            props.user.employee

        const exists = employees.some(
            employee =>
                Number(employee.id) ===
                Number(currentEmployee.id)
        )

        if (!exists) {
            employees.unshift(
                currentEmployee
            )
        }
    }

    return employees
})

const selectedEmployee = computed(() => {
    if (!form.value.employee_id) {
        return null
    }

    return (
        availableEmployees.value.find(
            employee =>
                Number(employee.id) ===
                Number(form.value.employee_id)
        ) ?? null
    )
})

/*
|--------------------------------------------------------------------------
| Branches
|--------------------------------------------------------------------------
*/

const availableBranches = computed(() => {
    if (!form.value.company_id) {
        return []
    }

    return props.branches.filter(
        branch =>
            Number(branch.company_id) ===
            Number(form.value.company_id)
    )
})

const selectedBranches = computed(() => {
    return availableBranches.value.filter(
        branch =>
            form.value.branch_ids.some(
                branchId =>
                    Number(branchId) ===
                    Number(branch.id)
            )
    )
})

/*
|--------------------------------------------------------------------------
| Username
|--------------------------------------------------------------------------
*/

const generateUsername = (name) => {
    return String(name ?? '')
        .trim()
        .toLowerCase()
        .replace(/\s+/g, '.')
        .replace(
            /[^a-z0-9._-]/g,
            ''
        )
        .replace(
            /\.{2,}/g,
            '.'
        )
        .replace(
            /^[._-]+|[._-]+$/g,
            ''
        )
}

/*
|--------------------------------------------------------------------------
| Employee Selection
|--------------------------------------------------------------------------
*/

const selectEmployee = () => {
    const employee =
        selectedEmployee.value

    if (!employee) {
        return
    }

    /*
     * Employee name digunakan untuk
     * membuat username default.
     *
     * Contoh:
     * Andi Pratama
     * ↓
     * andi.pratama
     */
    form.value.name =
        generateUsername(
            employee.name
        )

    /*
     * Email Employee menjadi
     * default email User.
     */
    form.value.email =
        employee.email ?? ''
}

/*
|--------------------------------------------------------------------------
| Reset
|--------------------------------------------------------------------------
*/

const resetForm = () => {
    form.value = {
        employee_id: '',
        name: '',
        email: '',
        company_id: '',
        role: '',
        branch_ids: [],
        default_branch_id: '',
        password: '',
        password_confirmation: '',
    }

    errors.value = {}

    showPassword.value = false
}

/*
|--------------------------------------------------------------------------
| Populate Edit
|--------------------------------------------------------------------------
*/

const populateUser = () => {
    if (!props.user) {
        resetForm()

        return
    }

    const user =
        props.user

    const employeeId =
        user.employee?.id ?? ''

    const branchIds =
        Array.isArray(user.branches)
            ? user.branches.map(
                branch =>
                    Number(branch.id)
            )
            : []

    const defaultBranch =
        Array.isArray(user.branches)
            ? user.branches.find(
                branch =>
                    branch.pivot?.is_default
            )
            : null

    const role =
        Array.isArray(user.roles)
            ? user.roles[0]?.name ?? ''
            : ''

    form.value = {
        employee_id:
            employeeId,

        /*
         * name adalah username.
         */
        name:
            user.name ?? '',

        email:
            user.email ?? '',

        company_id:
            user.company_id ?? '',

        role:
            role,

        branch_ids:
            branchIds,

        default_branch_id:
            defaultBranch?.id ?? '',

        /*
         * Password sengaja kosong
         * saat Edit.
         */
        password: '',

        password_confirmation: '',
    }

    errors.value = {}

    /*
     * Password tidak boleh langsung terlihat
     * ketika modal dibuka.
     */
    showPassword.value = false
}

/*
|--------------------------------------------------------------------------
| Close
|--------------------------------------------------------------------------
*/

const close = () => {
    if (processing.value) {
        return
    }

    emit('close')
}

/*
|--------------------------------------------------------------------------
| Branch Selection
|--------------------------------------------------------------------------
*/

const toggleBranch = (branchId) => {
    const id =
        Number(branchId)

    const exists =
        form.value.branch_ids.some(
            currentId =>
                Number(currentId) === id
        )

    if (exists) {
        form.value.branch_ids =
            form.value.branch_ids.filter(
                currentId =>
                    Number(currentId) !== id
            )

        /*
         * Kalau branch yang dihapus
         * adalah default branch,
         * kosongkan default.
         */
        if (
            Number(
                form.value.default_branch_id
            ) === id
        ) {
            form.value.default_branch_id =
                ''
        }

        return
    }

    form.value.branch_ids.push(id)
}

const isBranchSelected = (
    branchId
) => {
    return form.value.branch_ids.some(
        currentId =>
            Number(currentId) ===
            Number(branchId)
    )
}

/*
|--------------------------------------------------------------------------
| Company Change
|--------------------------------------------------------------------------
*/

const handleCompanyChange = () => {
    const validBranchIds =
        availableBranches.value.map(
            branch =>
                Number(branch.id)
        )

    form.value.branch_ids =
        form.value.branch_ids.filter(
            branchId =>
                validBranchIds.includes(
                    Number(branchId)
                )
        )

    if (
        !validBranchIds.includes(
            Number(
                form.value.default_branch_id
            )
        )
    ) {
        form.value.default_branch_id =
            ''
    }
}

/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

const fieldError = (field) => {
    const error =
        errors.value?.[field]

    if (Array.isArray(error)) {
        return error[0] ?? ''
    }

    return error ?? ''
}

/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/

const submit = () => {
    processing.value = true
    errors.value = {}

    const data = {
        employee_id:
            form.value.employee_id,

        name:
            form.value.name,

        email:
            form.value.email,

        company_id:
            form.value.company_id,

        role:
            form.value.role,

        branch_ids:
            form.value.branch_ids,

        default_branch_id:
            form.value.default_branch_id,
    }

    /*
     * Password hanya dikirim saat Create,
     * atau kalau nanti secara eksplisit
     * digunakan untuk update password.
     *
     * Saat ini Edit tidak memiliki
     * password field, jadi tidak dikirim.
     */
    if (!isEdit.value) {
        data.password =
            form.value.password

        data.password_confirmation =
            form.value.password_confirmation
    }

    const options = {
        preserveScroll: true,

        onError: validationErrors => {
            errors.value =
                validationErrors

            processing.value = false
        },

        onSuccess: () => {
    processing.value = false

    success(
        isEdit.value
            ? 'User updated successfully.'
            : 'User created successfully.'
    )

    emit('close')
},
        onFinish: () => {
            processing.value = false
        },
    }

    if (isEdit.value) {
        router.put(
            route(
                'users.update',
                props.user.id
            ),
            data,
            options
        )
    } else {
        router.post(
            route(
                'users.store'
            ),
            data,
            options
        )
    }
}

/*
|--------------------------------------------------------------------------
| Watchers
|--------------------------------------------------------------------------
*/

watch(
    () => props.show,
    show => {
        if (!show) {
            return
        }

        if (props.user) {
            populateUser()
        } else {
            resetForm()
        }
    }
)

watch(
    () => props.user,
    user => {
        if (!props.show) {
            return
        }

        if (user) {
            populateUser()
        } else {
            resetForm()
        }
    }
)

watch(
    () => form.value.company_id,
    () => {
        if (!props.show) {
            return
        }

        handleCompanyChange()
    }
)
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
                            {{ modalTitle }}
                        </h2>

                        <p
                            class="mt-1 text-sm text-gray-500"
                        >
                            {{ modalDescription }}
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
                    class="flex-1 overflow-y-auto px-6 py-5"
                >
                    <div
                        class="grid grid-cols-1 gap-5 md:grid-cols-2"
                    >
                        <!-- Employee -->
                        <div
                            class="md:col-span-2"
                        >
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700"
                            >
                                Employee
                                <span class="text-red-500">*</span>
                            </label>

                            <select
                                v-model="
                                    form.employee_id
                                "
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                                @change="selectEmployee"
                            >
                                <option value="">
                                    Select Employee
                                </option>

                                <option
                                    v-for="employee in availableEmployees"
                                    :key="employee.id"
                                    :value="employee.id"
                                >
                                    {{
                                        employee.employee_code
                                    }}
                                    -
                                    {{
                                        employee.name
                                    }}
                                </option>
                            </select>

                            <p
                                v-if="
                                    fieldError(
                                        'employee_id'
                                    )
                                "
                                class="mt-1 text-xs text-red-600"
                            >
                                {{
                                    fieldError(
                                        'employee_id'
                                    )
                                }}
                            </p>
                        </div>

                        <!-- Username -->
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700"
                            >
                                Username
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                v-model="
                                    form.name
                                "
                                type="text"
                                autocomplete="username"
                                placeholder="e.g. andi.pratama"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                            />

                            <p
                                class="mt-1 text-xs text-gray-400"
                            >
                                Gunakan huruf, angka,
                                titik, underscore,
                                atau tanda minus.
                            </p>

                            <p
                                v-if="
                                    fieldError(
                                        'name'
                                    )
                                "
                                class="mt-1 text-xs text-red-600"
                            >
                                {{
                                    fieldError(
                                        'name'
                                    )
                                }}
                            </p>
                        </div>

                        <!-- Email -->
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700"
                            >
                                Email
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                v-model="
                                    form.email
                                "
                                type="email"
                                autocomplete="email"
                                placeholder="employee@example.com"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                            />

                            <p
                                v-if="
                                    fieldError(
                                        'email'
                                    )
                                "
                                class="mt-1 text-xs text-red-600"
                            >
                                {{
                                    fieldError(
                                        'email'
                                    )
                                }}
                            </p>
                        </div>

                        <!-- Company -->
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700"
                            >
                                Company
                                <span class="text-red-500">*</span>
                            </label>

                            <select
                                v-model="
                                    form.company_id
                                "
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                            >
                                <option value="">
                                    Select Company
                                </option>

                                <option
                                    v-for="company in companies"
                                    :key="company.id"
                                    :value="company.id"
                                >
                                    {{
                                        company.company_name
                                    }}
                                </option>
                            </select>

                            <p
                                v-if="
                                    fieldError(
                                        'company_id'
                                    )
                                "
                                class="mt-1 text-xs text-red-600"
                            >
                                {{
                                    fieldError(
                                        'company_id'
                                    )
                                }}
                            </p>
                        </div>

                        <!-- Role -->
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700"
                            >
                                Role
                                <span class="text-red-500">*</span>
                            </label>

                            <select
                                v-model="
                                    form.role
                                "
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                            >
                                <option value="">
                                    Select Role
                                </option>

                                <option
                                    v-for="role in roles"
                                    :key="role.id"
                                    :value="role.name"
                                >
                                    {{ role.name }}
                                </option>
                            </select>

                            <p
                                v-if="
                                    fieldError(
                                        'role'
                                    )
                                "
                                class="mt-1 text-xs text-red-600"
                            >
                                {{
                                    fieldError(
                                        'role'
                                    )
                                }}
                            </p>
                        </div>

                        <!-- Password -->
                        <template v-if="!isEdit">
                            <div>
                                <label
                                    class="mb-1.5 block text-sm font-medium text-gray-700"
                                >
                                    Password
                                    <span class="text-red-500">*</span>
                                </label>

                                <input
                                    v-model="
                                        form.password
                                    "
                                    :type="
                                        showPassword
                                            ? 'text'
                                            : 'password'
                                    "
                                    autocomplete="new-password"
                                    placeholder="Minimum 8 characters"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                                />

                                <p
                                    v-if="
                                        fieldError(
                                            'password'
                                        )
                                    "
                                    class="mt-1 text-xs text-red-600"
                                >
                                    {{
                                        fieldError(
                                            'password'
                                        )
                                    }}
                                </p>
                            </div>

                            <!-- Confirm Password -->
                            <div>
                                <label
                                    class="mb-1.5 block text-sm font-medium text-gray-700"
                                >
                                    Confirm Password
                                    <span class="text-red-500">*</span>
                                </label>

                                <input
                                    v-model="
                                        form.password_confirmation
                                    "
                                    :type="
                                        showPassword
                                            ? 'text'
                                            : 'password'
                                    "
                                    autocomplete="new-password"
                                    placeholder="Repeat password"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                                />

                                <p
                                    v-if="
                                        fieldError(
                                            'password_confirmation'
                                        )
                                    "
                                    class="mt-1 text-xs text-red-600"
                                >
                                    {{
                                        fieldError(
                                            'password_confirmation'
                                        )
                                    }}
                                </p>

                                <!-- Show Password -->
                                <label
                                    class="mt-3 inline-flex cursor-pointer select-none items-center gap-2"
                                >
                                    <input
                                        v-model="
                                            showPassword
                                        "
                                        type="checkbox"
                                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    />

                                    <span
                                        class="text-xs text-gray-600"
                                    >
                                        Show password
                                    </span>
                                </label>
                            </div>
                        </template>

                        <!-- Branch Access -->
                        <div
                            class="md:col-span-2"
                        >
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700"
                            >
                                Branch Access
                                <span class="text-red-500">*</span>
                            </label>

                            <div
                                v-if="
                                    form.company_id &&
                                    availableBranches.length
                                "
                                class="grid grid-cols-1 gap-2 rounded-lg border border-gray-200 bg-gray-50 p-3 sm:grid-cols-2"
                            >
                                <label
                                    v-for="branch in availableBranches"
                                    :key="branch.id"
                                    class="flex cursor-pointer items-center gap-3 rounded-lg border border-gray-200 bg-white px-3 py-2.5 transition hover:border-blue-300"
                                >
                                    <input
                                        type="checkbox"
                                        :checked="
                                            isBranchSelected(
                                                branch.id
                                            )
                                        "
                                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        @change="
                                            toggleBranch(
                                                branch.id
                                            )
                                        "
                                    />

                                    <div>
                                        <div
                                            class="text-sm font-medium text-gray-700"
                                        >
                                            {{
                                                branch.name
                                            }}
                                        </div>

                                        <div
                                            class="text-xs text-gray-400"
                                        >
                                            {{
                                                branch.code
                                            }}
                                        </div>
                                    </div>
                                </label>
                            </div>

                            <div
                                v-else
                                class="rounded-lg border border-dashed border-gray-300 bg-gray-50 px-4 py-4 text-center text-sm text-gray-400"
                            >
                                {{
                                    form.company_id
                                        ? 'No active branches available for this company.'
                                        : 'Select a company first.'
                                }}
                            </div>

                            <p
                                v-if="
                                    fieldError(
                                        'branch_ids'
                                    )
                                "
                                class="mt-1 text-xs text-red-600"
                            >
                                {{
                                    fieldError(
                                        'branch_ids'
                                    )
                                }}
                            </p>

                            <p
                                v-if="
                                    fieldError(
                                        'branch_ids.0'
                                    )
                                "
                                class="mt-1 text-xs text-red-600"
                            >
                                {{
                                    fieldError(
                                        'branch_ids.0'
                                    )
                                }}
                            </p>
                        </div>

                        <!-- Default Branch -->
                        <div
                            class="md:col-span-2"
                        >
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700"
                            >
                                Default Branch
                                <span class="text-red-500">*</span>
                            </label>

                            <select
                                v-model="
                                    form.default_branch_id
                                "
                                :disabled="
                                    !selectedBranches.length
                                "
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 disabled:cursor-not-allowed disabled:bg-gray-100"
                            >
                                <option value="">
                                    Select Default Branch
                                </option>

                                <option
                                    v-for="branch in selectedBranches"
                                    :key="branch.id"
                                    :value="branch.id"
                                >
                                    {{
                                        branch.code
                                    }}
                                    -
                                    {{
                                        branch.name
                                    }}
                                </option>
                            </select>

                            <p
                                v-if="
                                    fieldError(
                                        'default_branch_id'
                                    )
                                "
                                class="mt-1 text-xs text-red-600"
                            >
                                {{
                                    fieldError(
                                        'default_branch_id'
                                    )
                                }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div
                    class="flex shrink-0 items-center justify-end gap-3 border-t border-gray-200 bg-gray-50 px-6 py-4"
                >
                    <button
                        type="button"
                        :disabled="processing"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-50"
                        @click="close"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        :disabled="processing"
                        class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                        @click="submit"
                    >
                        <span
                            v-if="processing"
                        >
                            Saving...
                        </span>

                        <span
                            v-else
                        >
                            {{
                                isEdit
                                    ? 'Save Changes'
                                    : 'Create User'
                            }}
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>