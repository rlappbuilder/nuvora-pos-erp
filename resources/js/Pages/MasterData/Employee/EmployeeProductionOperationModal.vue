<script setup>
import { ref, computed, watch, onBeforeUnmount } from 'vue'
import axios from 'axios'
import SearchableSelect from '@/Components/Form/SearchableSelect.vue'
import Swal from 'sweetalert2'
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

const emit = defineEmits(['close'])

/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const assignments = ref([])
const operations = ref([])
const productVariants = ref([])

const loading = ref(false)
const processing = ref(false)

const editingId = ref(null)
const managingRatesId = ref(null)
const editingRateId = ref(null)

const form = ref({
    production_operation_id: '',
    is_active: true,
})

const rateForm = ref({
    product_variant_id: '',
    wage_rate: 0,
    is_active: true,
})

const errors = ref({})
const rateErrors = ref({})

/*
|--------------------------------------------------------------------------
| Computed
|--------------------------------------------------------------------------
*/

const isEditing = computed(() => editingId.value !== null)

const isEditingRate = computed(() => editingRateId.value !== null)

const activeCount = computed(() => {
    return assignments.value.filter(item => item.is_active).length
})

const availableOperations = computed(() => {
    const assignedIds = assignments.value
        .filter(item => !item.deleted_at)
        .map(item => Number(item.production_operation_id))

    return operations.value
        .filter(operation => {
            return (
                Number(operation.id) === Number(form.value.production_operation_id) ||
                !assignedIds.includes(Number(operation.id))
            )
        })
        .map(operation => ({
            ...operation,
            label: `${operation.code ? operation.code + ' - ' : ''}${operation.name}`,
        }))
})

const availableProductVariants = computed(() => {
    const assignment = assignments.value.find(
        item => Number(item.id) === Number(managingRatesId.value)
    )

    const existingVariantIds = (assignment?.rates ?? [])
        .filter(rate => !rate.deleted_at)
        .map(rate => Number(rate.product_variant_id))

    return productVariants.value
        .filter(variant => {
            return (
                Number(variant.id) === Number(rateForm.value.product_variant_id) ||
                !existingVariantIds.includes(Number(variant.id))
            )
        })
        .map(variant => ({
            ...variant,
            label: [
                variant.product?.name ?? 'Unknown Product',
                variant.name,
                variant.sku ? `SKU: ${variant.sku}` : null,
            ].filter(Boolean).join(' - '),
        }))
})

const currentRateAssignment = computed(() => {
    return assignments.value.find(
        item => Number(item.id) === Number(managingRatesId.value)
    ) ?? null
})

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

const resetForm = () => {
    editingId.value = null

    form.value = {
        production_operation_id: '',
        is_active: true,
    }

    errors.value = {}
}

const resetRateForm = () => {
    editingRateId.value = null

    rateForm.value = {
        product_variant_id: '',
        wage_rate: 0,
        is_active: true,
    }

    rateErrors.value = {}
}

const closeModal = () => {
    if (processing.value) return

    emit('close')
}

const getError = (field) => {
    return errors.value[field]?.[0] ?? ''
}

const getRateError = (field) => {
    return rateErrors.value[field]?.[0] ?? ''
}

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 2,
    }).format(Number(value) || 0)
}

const operationName = (assignment) => {
    return (
        assignment.production_operation?.name ??
        assignment.productionOperation?.name ??
        'Unknown Operation'
    )
}

const operationCode = (assignment) => {
    return (
        assignment.production_operation?.code ??
        assignment.productionOperation?.code ??
        '-'
    )
}

const variantName = (rate) => {
    return rate.product_variant?.name ??
        rate.productVariant?.name ??
        'Unknown Variant'
}

const variantSku = (rate) => {
    return rate.product_variant?.sku ??
        rate.productVariant?.sku ??
        '-'
}

const variantProductName = (rate) => {
    return (
        rate.product_variant?.product?.name ??
        rate.productVariant?.product?.name ??
        'Unknown Product'
    )
}

/*
|--------------------------------------------------------------------------
| Load Data
|--------------------------------------------------------------------------
*/

const loadData = async () => {
    if (!props.employee?.id) return

    loading.value = true
    errors.value = {}
    rateErrors.value = {}

    try {
        const response = await axios.get(
            route(
                'employees.production-operations.index',
                props.employee.id
            )
        )

        const data = response.data.data ?? response.data

        assignments.value = data.assignments ?? []
        operations.value = data.operations ?? []
        productVariants.value = data.productVariants ?? []

    } catch (error) {
        console.error(
            'Failed to load employee production operations:',
            error
        )

        alert(
            error.response?.data?.message ??
            'Failed to load production operations.'
        )
    } finally {
        loading.value = false
    }
}

/*
|--------------------------------------------------------------------------
| Create Assignment
|--------------------------------------------------------------------------
*/

const openCreate = () => {
    resetForm()
}

/*
|--------------------------------------------------------------------------
| Edit Assignment
|--------------------------------------------------------------------------
*/

const openEdit = (assignment) => {
    resetForm()

    editingId.value = assignment.id

    form.value = {
        production_operation_id: assignment.production_operation_id,
        is_active: Boolean(assignment.is_active),
    }
}

/*
|--------------------------------------------------------------------------
| Save Assignment
|--------------------------------------------------------------------------
*/

const saveAssignment = async () => {
    errors.value = {}

    if (
        !isEditing.value &&
        !form.value.production_operation_id
    ) {
        errors.value.production_operation_id = [
            'Please select a production operation.',
        ]

        return
    }

    processing.value = true

    const payload = {
        production_operation_id: form.value.production_operation_id,
        is_active: form.value.is_active,
    }

    try {
        if (isEditing.value) {
            await axios.put(
                route(
                    'employees.production-operations.update',
                    [
                        props.employee.id,
                        editingId.value,
                    ]
                ),
                {
                    is_active: payload.is_active,
                }
            )
        } else {
            await axios.post(
                route(
                    'employees.production-operations.store',
                    props.employee.id
                ),
                payload
            )
        }

        resetForm()

        await loadData()

    } catch (error) {
        console.error('ASSIGNMENT STATUS:', error.response?.status)
        console.error('ASSIGNMENT ERRORS:', error.response?.data?.errors)
        console.error('ASSIGNMENT RESPONSE:', error.response?.data)

        if (error.response?.status === 422) {
            errors.value = error.response.data.errors ?? {}
        } else {
            alert(
                error.response?.data?.message ??
                'Failed to save production operation.'
            )
        }
    } finally {
        processing.value = false
    }
}

/*
|--------------------------------------------------------------------------
| Remove Assignment
|--------------------------------------------------------------------------
*/

const removeAssignment = async (assignment) => {
    const result = await Swal.fire({
        title: 'Remove Production Operation?',
        text: `Are you sure you want to remove "${operationName(assignment)}" from this employee?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, Remove',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
    })

    if (!result.isConfirmed) return

    processing.value = true

    try {
        await axios.delete(
            route(
                'employees.production-operations.destroy',
                [
                    props.employee.id,
                    assignment.id,
                ]
            )
        )

        if (editingId.value === assignment.id) {
            resetForm()
        }

        if (managingRatesId.value === assignment.id) {
            managingRatesId.value = null
            resetRateForm()
        }

        await loadData()

        await Swal.fire({
            icon: 'success',
            title: 'Removed',
            text: 'Production operation removed successfully.',
            confirmButtonColor: '#2563eb',
        })

    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Failed',
            text: error.response?.data?.message ??
                'Failed to remove production operation.',
            confirmButtonColor: '#2563eb',
        })
    } finally {
        processing.value = false
    }
}

/*
|--------------------------------------------------------------------------
| Manage Rates
|--------------------------------------------------------------------------
*/

const openManageRates = (assignment) => {
    resetRateForm()

    managingRatesId.value =
        managingRatesId.value === assignment.id
            ? null
            : assignment.id
}

const openCreateRate = () => {
    resetRateForm()
}

const openEditRate = (rate) => {
    editingRateId.value = rate.id

    rateForm.value = {
        product_variant_id: rate.product_variant_id,
        wage_rate: Number(rate.wage_rate ?? 0),
        is_active: Boolean(rate.is_active),
    }

    rateErrors.value = {}
}

/*
|--------------------------------------------------------------------------
| Save Rate
|--------------------------------------------------------------------------
*/

const saveRate = async () => {
    rateErrors.value = {}

    if (
        !isEditingRate.value &&
        !rateForm.value.product_variant_id
    ) {
        rateErrors.value.product_variant_id = [
            'Please select a product variant.',
        ]

        return
    }

    if (
        rateForm.value.wage_rate === '' ||
        rateForm.value.wage_rate === null ||
        Number(rateForm.value.wage_rate) < 0 ||
        !Number.isFinite(Number(rateForm.value.wage_rate))
    ) {
        rateErrors.value.wage_rate = [
            'Wage rate must be zero or greater.',
        ]

        return
    }

    const assignment = currentRateAssignment.value

    if (!assignment) return

    processing.value = true

    const payload = {
        wage_rate: Number(rateForm.value.wage_rate),
        is_active: rateForm.value.is_active,
    }

    try {
        if (isEditingRate.value) {
            await axios.put(
                route(
                    'employees.production-operations.rates.update',
                    [
                        props.employee.id,
                        assignment.id,
                        editingRateId.value,
                    ]
                ),
                payload
            )
        } else {
            await axios.post(
                route(
                    'employees.production-operations.rates.store',
                    [
                        props.employee.id,
                        assignment.id,
                    ]
                ),
                {
                    ...payload,
                    product_variant_id: rateForm.value.product_variant_id,
                }
            )
        }

        resetRateForm()

        await loadData()

    } catch (error) {
        console.error('RATE STATUS:', error.response?.status)
        console.error('RATE ERRORS:', error.response?.data?.errors)
        console.error('RATE RESPONSE:', error.response?.data)

        if (error.response?.status === 422) {
            rateErrors.value = error.response.data.errors ?? {}
        } else {
            alert(
                error.response?.data?.message ??
                'Failed to save production operation rate.'
            )
        }
    } finally {
        processing.value = false
    }
}

/*
|--------------------------------------------------------------------------
| Remove Rate
|--------------------------------------------------------------------------
*/

const removeRate = async (rate) => {
    const assignment = currentRateAssignment.value

    if (!assignment) return

    const result = await Swal.fire({
        title: 'Remove Wage Rate?',
        text: `Are you sure you want to remove the wage rate for "${variantProductName(rate)} - ${variantName(rate)}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, Remove',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
    })

    if (!result.isConfirmed) return

    processing.value = true

    try {
        await axios.delete(
            route(
                'employees.production-operations.rates.destroy',
                [
                    props.employee.id,
                    assignment.id,
                    rate.id,
                ]
            )
        )

        if (editingRateId.value === rate.id) {
            resetRateForm()
        }

        await loadData()

        await Swal.fire({
            icon: 'success',
            title: 'Removed',
            text: 'Wage rate removed successfully.',
            confirmButtonColor: '#2563eb',
        })

    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Failed',
            text: error.response?.data?.message ??
                'Failed to remove wage rate.',
            confirmButtonColor: '#2563eb',
        })
    } finally {
        processing.value = false
    }
}

/*
|--------------------------------------------------------------------------
| Watchers
|--------------------------------------------------------------------------
*/

watch(
    () => [
        props.show,
        props.employee?.id,
    ],
    ([show]) => {
        if (show && props.employee?.id) {
            resetForm()
            resetRateForm()
            managingRatesId.value = null
            loadData()
        }
    }
)

/*
|--------------------------------------------------------------------------
| Keyboard
|--------------------------------------------------------------------------
*/

const handleEscape = (event) => {
    if (event.key === 'Escape' && props.show) {
        closeModal()
    }
}

watch(
    () => props.show,
    (show) => {
        if (show) {
            window.addEventListener('keydown', handleEscape)
        } else {
            window.removeEventListener('keydown', handleEscape)
        }
    }
)

onBeforeUnmount(() => {
    window.removeEventListener('keydown', handleEscape)
})
</script>

<template>
    <Teleport to="body">
        <div
            v-if="show"
            class="
                fixed inset-0 z-[100]
                flex items-center justify-center
                overflow-y-auto
                bg-black/50
                p-3 sm:p-6
            "
            @click.self="closeModal"
        >
            <div
                class="
                    relative w-full max-w-3xl
                    overflow-hidden rounded-xl
                    bg-white shadow-xl
                "
            >
                <!-- Header -->
                <div
                    class="
                        flex items-start justify-between
                        border-b border-gray-200
                        px-5 py-4
                    "
                >
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">
                            Production Operations
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            Manage production operations and variant-based wage rates assigned to this employee.
                        </p>

                        <div
                            v-if="employee"
                            class="mt-2 flex flex-wrap items-center gap-2"
                        >
                            <span
                                class="
                                    rounded-md bg-blue-50
                                    px-2 py-1 text-xs
                                    font-medium text-blue-700
                                "
                            >
                                {{ employee.employee_code }}
                            </span>

                            <span class="text-xs font-medium text-gray-700">
                                {{ employee.name }}
                            </span>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="
                            rounded-lg p-2
                            text-gray-400
                            hover:bg-gray-100 hover:text-gray-600
                        "
                        :disabled="processing"
                        @click="closeModal"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>

                <!-- Content -->
                <div class="max-h-[68vh] overflow-y-auto p-5">

                    <!-- Statistics -->
                    <div class="mb-5 grid grid-cols-2 gap-3">
                        <div
                            class="
                                rounded-lg border border-gray-200
                                bg-gray-50 p-3
                            "
                        >
                            <p class="text-xs text-gray-500">
                                Total Assigned
                            </p>

                            <p class="mt-1 text-xl font-semibold text-gray-900">
                                {{ assignments.length }}
                            </p>
                        </div>

                        <div
                            class="
                                rounded-lg border border-gray-200
                                bg-green-50 p-3
                            "
                        >
                            <p class="text-xs text-gray-500">
                                Active Operations
                            </p>

                            <p class="mt-1 text-xl font-semibold text-green-700">
                                {{ activeCount }}
                            </p>
                        </div>
                    </div>

                    <!-- Loading -->
                    <div
                        v-if="loading"
                        class="flex flex-col items-center py-12"
                    >
                        <div
                            class="
                                h-8 w-8 animate-spin rounded-full
                                border-4 border-blue-100
                                border-t-blue-600
                            "
                        />

                        <p class="mt-3 text-sm text-gray-500">
                            Loading production operations...
                        </p>
                    </div>

                    <template v-else>

                        <!-- Assignment Form -->
                        <div
                            class="
                                rounded-xl border border-gray-200
                                bg-gray-50 p-4
                            "
                        >
                            <div
                                class="
                                    mb-4 flex items-center
                                    justify-between gap-3
                                "
                            >
                                <h3 class="text-sm font-semibold text-gray-900">
                                    {{ isEditing ? 'Edit Assignment' : 'Assign Production Operation' }}
                                </h3>

                                <button
                                    v-if="isEditing"
                                    type="button"
                                    class="text-xs font-medium text-gray-500 hover:text-gray-800"
                                    :disabled="processing"
                                    @click="resetForm"
                                >
                                    Cancel Edit
                                </button>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                                <!-- Operation -->
                                <div class="sm:col-span-2">
                                    <label
                                        class="
                                            mb-1.5 block text-xs
                                            font-medium text-gray-700
                                        "
                                    >
                                        Production Operation
                                        <span
                                            v-if="!isEditing"
                                            class="text-red-500"
                                        >
                                            *
                                        </span>
                                    </label>

                                    <SearchableSelect
                                        v-model="form.production_operation_id"
                                        :options="availableOperations"
                                        label="label"
                                        value-key="id"
                                        placeholder="Select Production Operation"
                                        :disabled="isEditing || processing"
                                        :error="getError('production_operation_id')"
                                    />

                                    <p
                                        v-if="getError('production_operation_id')"
                                        class="mt-1 text-xs text-red-600"
                                    >
                                        {{ getError('production_operation_id') }}
                                    </p>
                                </div>

                                <!-- Status -->
                                <div>
                                    <label
                                        class="
                                            mb-1.5 block text-xs
                                            font-medium text-gray-700
                                        "
                                    >
                                        Assignment Status
                                    </label>

                                    <label
                                        class="
                                            flex h-[42px] cursor-pointer
                                            items-center gap-2
                                            rounded-lg border border-gray-300
                                            bg-white px-3
                                        "
                                    >
                                        <input
                                            v-model="form.is_active"
                                            type="checkbox"
                                            :disabled="processing"
                                            class="
                                                h-4 w-4 rounded
                                                border-gray-300
                                                text-blue-600
                                                focus:ring-blue-500
                                            "
                                        />

                                        <span class="text-sm text-gray-700">
                                            Active
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="mt-4 flex justify-end">
                                <button
                                    type="button"
                                    :disabled="processing"
                                    class="
                                        inline-flex items-center
                                        gap-2 rounded-lg
                                        bg-blue-600 px-4 py-2.5
                                        text-sm font-medium text-white
                                        transition hover:bg-blue-700
                                        disabled:cursor-not-allowed
                                        disabled:opacity-50
                                    "
                                    @click="saveAssignment"
                                >
                                    <span
                                        v-if="processing"
                                        class="
                                            h-4 w-4 animate-spin
                                            rounded-full border-2
                                            border-white/40
                                            border-t-white
                                        "
                                    />

                                    {{ processing ? 'Saving...' : isEditing ? 'Update Assignment' : 'Add Operation' }}
                                </button>
                            </div>
                        </div>

                        <!-- Assignment List -->
                        <div class="mt-6">
                            <div
                                class="
                                    mb-3 flex items-center
                                    justify-between gap-2
                                "
                            >
                                <h3 class="text-sm font-semibold text-gray-900">
                                    Assigned Operations
                                </h3>

                                <span class="text-xs text-gray-500">
                                    {{ assignments.length }} records
                                </span>
                            </div>

                            <!-- Empty -->
                            <div
                                v-if="assignments.length === 0"
                                class="
                                    rounded-xl border border-dashed
                                    border-gray-300 px-4 py-10
                                    text-center
                                "
                            >
                                <div class="text-3xl">⚙️</div>

                                <p class="mt-2 text-sm font-medium text-gray-700">
                                    No production operations assigned
                                </p>

                                <p class="mt-1 text-xs text-gray-500">
                                    Select an operation above to assign it to this employee.
                                </p>
                            </div>

                            <!-- Assignment Cards -->
                            <div
                                v-else
                                class="space-y-3"
                            >
                                <div
                                    v-for="assignment in assignments"
                                    :key="assignment.id"
                                    class="
                                        overflow-hidden rounded-lg
                                        border border-gray-200 bg-white
                                    "
                                >
                                    <!-- Assignment Header -->
                                    <div
                                        class="
                                            flex flex-wrap items-center
                                            justify-between gap-3
                                            p-4
                                        "
                                    >
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-semibold text-gray-800">
                                                {{ operationName(assignment) }}
                                            </p>

                                            <p class="mt-0.5 text-xs text-gray-500">
                                                {{ operationCode(assignment) }}
                                            </p>

                                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                                <span
                                                    :class="
                                                        assignment.is_active
                                                            ? 'bg-green-100 text-green-700'
                                                            : 'bg-gray-100 text-gray-600'
                                                    "
                                                    class="
                                                        inline-flex rounded-full
                                                        px-2.5 py-1
                                                        text-[10px] font-medium
                                                    "
                                                >
                                                    {{ assignment.is_active ? 'Active' : 'Inactive' }}
                                                </span>

                                                <span class="text-[10px] text-gray-500">
                                                    {{ (assignment.rates ?? []).length }} variant rates
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Actions -->
                                        <div class="flex flex-wrap items-center justify-end gap-1">
                                            <button
                                                type="button"
                                                :disabled="processing"
                                                :class="
                                                    managingRatesId === assignment.id
                                                        ? 'bg-blue-100 text-blue-700'
                                                        : 'text-blue-600 hover:bg-blue-50'
                                                "
                                                class="
                                                    rounded-md px-2.5 py-2
                                                    text-xs font-medium
                                                    disabled:opacity-50
                                                "
                                                @click="openManageRates(assignment)"
                                            >
                                                {{ managingRatesId === assignment.id ? 'Hide Rates' : 'Manage Rates' }}
                                            </button>

                                            <button
                                                type="button"
                                                :disabled="processing"
                                                class="
                                                    rounded-md px-2.5 py-2
                                                    text-xs font-medium
                                                    text-gray-600
                                                    hover:bg-gray-100
                                                    disabled:opacity-50
                                                "
                                                @click="openEdit(assignment)"
                                            >
                                                Edit
                                            </button>

                                            <button
                                                type="button"
                                                :disabled="processing"
                                                class="
                                                    rounded-md px-2.5 py-2
                                                    text-xs font-medium
                                                    text-red-600
                                                    hover:bg-red-50
                                                    disabled:opacity-50
                                                "
                                                @click="removeAssignment(assignment)"
                                            >
                                                Remove
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Rate Management -->
                                    <div
                                        v-if="managingRatesId === assignment.id"
                                        class="
                                            border-t border-gray-200
                                            bg-gray-50 p-4
                                        "
                                    >
                                        <div
                                            class="
                                                mb-4 flex flex-wrap
                                                items-center justify-between gap-2
                                            "
                                        >
                                            <div>
                                                <h4 class="text-sm font-semibold text-gray-800">
                                                    Product Variant Wage Rates
                                                </h4>

                                                <p class="mt-1 text-xs text-gray-500">
                                                    Set an individual wage rate for each product variant.
                                                </p>
                                            </div>

                                            <button
                                                v-if="isEditingRate"
                                                type="button"
                                                class="
                                                    text-xs font-medium
                                                    text-gray-500
                                                    hover:text-gray-800
                                                "
                                                :disabled="processing"
                                                @click="resetRateForm"
                                            >
                                                Cancel Edit
                                            </button>
                                        </div>

                                        <!-- Rate Form -->
                                        <div
                                            class="
                                                mb-4 rounded-lg
                                                border border-gray-200
                                                bg-white p-3
                                            "
                                        >
                                            <h5 class="mb-3 text-xs font-semibold text-gray-800">
                                                {{ isEditingRate ? 'Edit Wage Rate' : 'Add Variant Wage Rate' }}
                                            </h5>

                                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">

                                                <!-- Product Variant -->
                                                <div class="sm:col-span-2">
                                                    <label
                                                        class="
                                                            mb-1.5 block text-xs
                                                            font-medium text-gray-700
                                                        "
                                                    >
                                                        Product Variant
                                                        <span
                                                            v-if="!isEditingRate"
                                                            class="text-red-500"
                                                        >
                                                            *
                                                        </span>
                                                    </label>

                                                    <SearchableSelect
                                                        v-model="rateForm.product_variant_id"
                                                        :options="availableProductVariants"
                                                        label="label"
                                                        value-key="id"
                                                        placeholder="Select Product Variant"
                                                        :disabled="isEditingRate || processing"
                                                        :error="getRateError('product_variant_id')"
                                                    />

                                                    <p
                                                        v-if="getRateError('product_variant_id')"
                                                        class="mt-1 text-xs text-red-600"
                                                    >
                                                        {{ getRateError('product_variant_id') }}
                                                    </p>
                                                </div>

                                                <!-- Wage Rate -->
                                                <div>
                                                    <label
                                                        class="
                                                            mb-1.5 block text-xs
                                                            font-medium text-gray-700
                                                        "
                                                    >
                                                        Wage Rate
                                                        <span class="text-red-500">*</span>
                                                    </label>

                                                    <input
                                                        v-model="rateForm.wage_rate"
                                                        type="number"
                                                        min="0"
                                                        step="0.01"
                                                        placeholder="0.00"
                                                        :disabled="processing"
                                                        class="
                                                            w-full rounded-lg
                                                            border border-gray-300
                                                            bg-white px-3 py-2.5
                                                            text-sm outline-none
                                                            focus:border-blue-500
                                                            focus:ring-2
                                                            focus:ring-blue-100
                                                            disabled:bg-gray-100
                                                        "
                                                    />

                                                    <p
                                                        v-if="getRateError('wage_rate')"
                                                        class="mt-1 text-xs text-red-600"
                                                    >
                                                        {{ getRateError('wage_rate') }}
                                                    </p>
                                                </div>

                                                <!-- Status -->
                                                <div>
                                                    <label
                                                        class="
                                                            mb-1.5 block text-xs
                                                            font-medium text-gray-700
                                                        "
                                                    >
                                                        Rate Status
                                                    </label>

                                                    <label
                                                        class="
                                                            flex h-[42px] cursor-pointer
                                                            items-center gap-2
                                                            rounded-lg border border-gray-300
                                                            bg-white px-3
                                                        "
                                                    >
                                                        <input
                                                            v-model="rateForm.is_active"
                                                            type="checkbox"
                                                            :disabled="processing"
                                                            class="
                                                                h-4 w-4 rounded
                                                                border-gray-300
                                                                text-blue-600
                                                                focus:ring-blue-500
                                                            "
                                                        />

                                                        <span class="text-sm text-gray-700">
                                                            Active
                                                        </span>
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="mt-3 flex justify-end">
                                                <button
                                                    type="button"
                                                    :disabled="processing"
                                                    class="
                                                        inline-flex items-center
                                                        gap-2 rounded-lg
                                                        bg-blue-600 px-3 py-2
                                                        text-xs font-medium text-white
                                                        hover:bg-blue-700
                                                        disabled:opacity-50
                                                    "
                                                    @click="saveRate"
                                                >
                                                    <span
                                                        v-if="processing"
                                                        class="
                                                            h-3.5 w-3.5 animate-spin
                                                            rounded-full border-2
                                                            border-white/40
                                                            border-t-white
                                                        "
                                                    />

                                                    {{ processing ? 'Saving...' : isEditingRate ? 'Update Rate' : 'Add Rate' }}
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Rate List -->
                                        <div>
                                            <div
                                                v-if="!(assignment.rates ?? []).length"
                                                class="
                                                    rounded-lg border border-dashed
                                                    border-gray-300 bg-white
                                                    px-4 py-7 text-center
                                                "
                                            >
                                                <p class="text-xs font-medium text-gray-700">
                                                    No variant wage rates configured
                                                </p>

                                                <p class="mt-1 text-[11px] text-gray-500">
                                                    Add a Product Variant and its wage rate above.
                                                </p>
                                            </div>

                                            <div
                                                v-else
                                                class="
                                                    overflow-x-auto rounded-lg
                                                    border border-gray-200
                                                    bg-white
                                                "
                                            >
                                                <table class="min-w-full divide-y divide-gray-200">
                                                    <thead class="bg-gray-100">
                                                        <tr>
                                                            <th
                                                                class="
                                                                    px-3 py-2.5 text-left
                                                                    text-[10px] font-semibold
                                                                    uppercase tracking-wider
                                                                    text-gray-500
                                                                "
                                                            >
                                                                Product Variant
                                                            </th>

                                                            <th
                                                                class="
                                                                    px-3 py-2.5 text-right
                                                                    text-[10px] font-semibold
                                                                    uppercase tracking-wider
                                                                    text-gray-500
                                                                "
                                                            >
                                                                Wage Rate
                                                            </th>

                                                            <th
                                                                class="
                                                                    px-3 py-2.5 text-center
                                                                    text-[10px] font-semibold
                                                                    uppercase tracking-wider
                                                                    text-gray-500
                                                                "
                                                            >
                                                                Status
                                                            </th>

                                                            <th
                                                                class="
                                                                    px-3 py-2.5 text-right
                                                                    text-[10px] font-semibold
                                                                    uppercase tracking-wider
                                                                    text-gray-500
                                                                "
                                                            >
                                                                Actions
                                                            </th>
                                                        </tr>
                                                    </thead>

                                                    <tbody class="divide-y divide-gray-100">
                                                        <tr
                                                            v-for="rate in assignment.rates"
                                                            :key="rate.id"
                                                            class="hover:bg-gray-50"
                                                        >
                                                            <!-- Variant -->
                                                            <td class="px-3 py-3">
                                                                <p class="text-xs font-semibold text-gray-800">
                                                                    {{ variantProductName(rate) }}
                                                                </p>

                                                                <p class="mt-0.5 text-[11px] text-gray-600">
                                                                    {{ variantName(rate) }}
                                                                </p>

                                                                <p class="mt-0.5 text-[10px] text-gray-400">
                                                                    {{ variantSku(rate) }}
                                                                </p>
                                                            </td>

                                                            <!-- Wage Rate -->
                                                            <td
                                                                class="
                                                                    whitespace-nowrap
                                                                    px-3 py-3 text-right
                                                                    text-xs font-medium
                                                                    text-gray-700
                                                                "
                                                            >
                                                                {{ formatCurrency(rate.wage_rate) }}
                                                            </td>

                                                            <!-- Status -->
                                                            <td class="px-3 py-3 text-center">
                                                                <span
                                                                    :class="
                                                                        rate.is_active
                                                                            ? 'bg-green-100 text-green-700'
                                                                            : 'bg-gray-100 text-gray-600'
                                                                    "
                                                                    class="
                                                                        inline-flex rounded-full
                                                                        px-2 py-1
                                                                        text-[10px] font-medium
                                                                    "
                                                                >
                                                                    {{ rate.is_active ? 'Active' : 'Inactive' }}
                                                                </span>
                                                            </td>

                                                            <!-- Actions -->
                                                            <td class="whitespace-nowrap px-3 py-3 text-right">
                                                                <div class="flex justify-end gap-1">
                                                                    <button
                                                                        type="button"
                                                                        :disabled="processing"
                                                                        class="
                                                                            rounded-md px-2 py-1
                                                                            text-[11px] font-medium
                                                                            text-blue-600
                                                                            hover:bg-blue-50
                                                                            disabled:opacity-50
                                                                        "
                                                                        @click="openEditRate(rate)"
                                                                    >
                                                                        Edit
                                                                    </button>

                                                                    <button
                                                                        type="button"
                                                                        :disabled="processing"
                                                                        class="
                                                                            rounded-md px-2 py-1
                                                                            text-[11px] font-medium
                                                                            text-red-600
                                                                            hover:bg-red-50
                                                                            disabled:opacity-50
                                                                        "
                                                                        @click="removeRate(rate)"
                                                                    >
                                                                        Remove
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </template>
                </div>

                <!-- Footer -->
                <div
                    class="
                        flex justify-end
                        border-t border-gray-200
                        bg-gray-50 px-5 py-3
                    "
                >
                    <button
                        type="button"
                        :disabled="processing"
                        class="
                            rounded-lg border border-gray-300
                            bg-white px-4 py-2
                            text-sm font-medium text-gray-700
                            hover:bg-gray-100
                            disabled:opacity-50
                        "
                        @click="closeModal"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>