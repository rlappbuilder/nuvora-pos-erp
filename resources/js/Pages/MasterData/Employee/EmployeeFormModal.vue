<script setup>
import {
    computed,
    ref,
    watch,
} from 'vue'

import { success } from '@/Utils'
import { router } from '@inertiajs/vue3'

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

const processing = ref(false)
const errors = ref({})
const loadingCode = ref(false)

const form = ref({
    employee_code: '',
    name: '',
    phone: '',
    email: '',
    position: '',
    join_date: '',
    status: true,
})

const isEdit = computed(() => {
    return !!props.employee
})

const modalTitle = computed(() => {
    return isEdit.value
        ? 'Edit Employee'
        : 'Add Employee'
})

const modalDescription = computed(() => {
    return isEdit.value
        ? 'Update employee information.'
        : 'Create a new employee record.'
})

/*
|--------------------------------------------------------------------------
| Preview Employee Code
|--------------------------------------------------------------------------
*/

const loadPreviewCode = async () => {
    if (isEdit.value) {
        return
    }

    loadingCode.value = true

    try {
        const response = await fetch(
            route('employees.preview-code'),
            {
                headers: {
                    Accept: 'application/json',
                },
            }
        )

        if (!response.ok) {
            throw new Error(
                'Failed to load employee code.'
            )
        }

        const data =
            await response.json()

        form.value.employee_code =
            data.code ?? ''
    } catch (error) {
        form.value.employee_code = ''
    } finally {
        loadingCode.value = false
    }
}

/*
|--------------------------------------------------------------------------
| Reset
|--------------------------------------------------------------------------
*/

const resetForm = () => {
    form.value = {
        employee_code: '',
        name: '',
        phone: '',
        email: '',
        position: '',
        join_date: '',
        status: true,
    }

    errors.value = {}
    loadingCode.value = false
}

/*
|--------------------------------------------------------------------------
| Populate Edit
|--------------------------------------------------------------------------
*/

const populateEmployee = () => {
    if (!props.employee) {
        resetForm()

        loadPreviewCode()

        return
    }

    const employee =
        props.employee

    form.value = {
        employee_code:
            employee.employee_code ?? '',

        name:
            employee.name ?? '',

        phone:
            employee.phone ?? '',

        email:
            employee.email ?? '',

        position:
            employee.position ?? '',

        join_date:
            employee.join_date ?? '',

        status:
            Boolean(employee.status),
    }

    errors.value = {}
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
| Validation Error
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
        name:
            form.value.name,

        phone:
            form.value.phone,

        email:
            form.value.email,

        position:
            form.value.position,

        join_date:
            form.value.join_date,

        status:
            form.value.status,
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
                    ? 'Employee updated successfully.'
                    : 'Employee created successfully.'
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
                'employees.update',
                props.employee.id
            ),
            data,
            options
        )
    } else {
        router.post(
            route(
                'employees.store'
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

        if (props.employee) {
            populateEmployee()
        } else {
            resetForm()
            loadPreviewCode()
        }
    }
)

watch(
    () => props.employee,
    employee => {
        if (!props.show) {
            return
        }

        if (employee) {
            populateEmployee()
        } else {
            resetForm()
            loadPreviewCode()
        }
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
                class="relative flex w-full max-w-2xl max-h-[92vh] flex-col overflow-hidden rounded-xl bg-white shadow-2xl"
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
                        <!-- Employee Code -->
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700"
                            >
                                Employee Code
                            </label>

                            <div
                                class="relative"
                            >
                                <input
                                    v-model="
                                        form.employee_code
                                    "
                                    type="text"
                                    readonly
                                    class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm font-medium text-gray-600 outline-none"
                                />

                                <div
                                    v-if="loadingCode"
                                    class="absolute inset-y-0 right-3 flex items-center"
                                >
                                    <svg
                                        class="h-4 w-4 animate-spin text-gray-400"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <circle
                                            class="opacity-25"
                                            cx="12"
                                            cy="12"
                                            r="10"
                                            stroke="currentColor"
                                            stroke-width="4"
                                        />

                                        <path
                                            class="opacity-75"
                                            fill="currentColor"
                                            d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                                        />
                                    </svg>
                                </div>
                            </div>

                            <p
                                class="mt-1 text-xs text-gray-400"
                            >
                                Employee code is generated automatically.
                            </p>

                            <p
                                v-if="
                                    fieldError(
                                        'employee_code'
                                    )
                                "
                                class="mt-1 text-xs text-red-600"
                            >
                                {{
                                    fieldError(
                                        'employee_code'
                                    )
                                }}
                            </p>
                        </div>

                        <!-- Name -->
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700"
                            >
                                Employee Name
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                v-model="
                                    form.name
                                "
                                type="text"
                                autocomplete="name"
                                placeholder="e.g. Andi Pratama"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                            />

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

                        <!-- Phone -->
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700"
                            >
                                Phone
                            </label>

                            <input
                                v-model="
                                    form.phone
                                "
                                type="text"
                                autocomplete="tel"
                                placeholder="e.g. 081234567890"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                            />

                            <p
                                v-if="
                                    fieldError(
                                        'phone'
                                    )
                                "
                                class="mt-1 text-xs text-red-600"
                            >
                                {{
                                    fieldError(
                                        'phone'
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

                        <!-- Position -->
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700"
                            >
                                Position
                            </label>

                            <input
                                v-model="
                                    form.position
                                "
                                type="text"
                                placeholder="e.g. Cashier"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                            />

                            <p
                                v-if="
                                    fieldError(
                                        'position'
                                    )
                                "
                                class="mt-1 text-xs text-red-600"
                            >
                                {{
                                    fieldError(
                                        'position'
                                    )
                                }}
                            </p>
                        </div>

                        <!-- Join Date -->
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700"
                            >
                                Join Date
                            </label>

                            <input
                                v-model="
                                    form.join_date
                                "
                                type="date"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                            />

                            <p
                                v-if="
                                    fieldError(
                                        'join_date'
                                    )
                                "
                                class="mt-1 text-xs text-red-600"
                            >
                                {{
                                    fieldError(
                                        'join_date'
                                    )
                                }}
                            </p>
                        </div>

                        <!-- Status -->
                        <div
                            class="md:col-span-2"
                        >
                            <label
                                class="mb-1.5 block text-sm font-medium text-gray-700"
                            >
                                Status
                                <span class="text-red-500">*</span>
                            </label>

                            <select
                                v-model="
                                    form.status
                                "
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                            >
                                <option :value="true">
                                    Active
                                </option>

                                <option :value="false">
                                    Inactive
                                </option>
                            </select>

                            <p
                                v-if="
                                    fieldError(
                                        'status'
                                    )
                                "
                                class="mt-1 text-xs text-red-600"
                            >
                                {{
                                    fieldError(
                                        'status'
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
                        :disabled="
                            processing ||
                            loadingCode
                        "
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
                                    : 'Create Employee'
                            }}
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>