<script setup>
import { computed, ref, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import FlatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'
import Swal from 'sweetalert2'
import { success, error, formatDate } from '@/Utils'

import DataTable from '@/Components/Table/DataTable.vue'
import DataTableHead from '@/Components/Table/DataTableHead.vue'
import DataTableBody from '@/Components/Table/DataTableBody.vue'
import DataTableHeaderCell from '@/Components/Table/DataTableHeaderCell.vue'
import DataTableRow from '@/Components/Table/DataTableRow.vue'
import DataTableCell from '@/Components/Table/DataTableCell.vue'
import TablePagination from '@/Components/Table/TablePagination.vue'

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    transfer: {
        type: Object,
        default: null,
    },
    availableTransfers: {
        type: [Array, Object],
        default: () => [],
    },
    previewNumber: {
        type: String,
        default: '',
    },
})

const emit = defineEmits(['close', 'success'])

const form = useForm({
    stock_transfer_header_id: null,
    transaction_date: '',
    description: '',
    details: [],
})

const selectedTransfer = ref(null)
const validationError = ref('')

const transferOptions = computed(() => {
    const source = props.availableTransfers

    if (Array.isArray(source)) {
        return source
    }

    return source?.data ?? []
})

const documentNumber = computed(() => props.previewNumber || '-')

const totalItems = computed(() => {
    return form.details.filter(
        detail => toNumber(detail.return_qty) > 0
    ).length
})

const totalReturnQty = computed(() => {
    return form.details.reduce(
        (total, detail) => total + toNumber(detail.return_qty),
        0
    )
})

const totalReturnCost = computed(() => {
    return form.details.reduce(
        (total, detail) =>
            total +
            toNumber(detail.return_qty) *
            toNumber(detail.unit_cost),
        0
    )
})

function toNumber(value) {
    const number = Number(value ?? 0)

    return Number.isFinite(number) ? number : 0
}

function formatNumber(value) {
    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(toNumber(value))
}

function formatCurrency(value) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(toNumber(value))
}

function localDate() {
    const now = new Date()

    return [
        now.getFullYear(),
        String(now.getMonth() + 1).padStart(2, '0'),
        String(now.getDate()).padStart(2, '0'),
    ].join('-')
}

function getLocationName(value) {
    if (value === null || value === undefined || value === '') {
        return '-'
    }

    if (typeof value === 'object') {
        return value.name ?? value.label ?? '-'
    }

    return value
}

function getProductName(detail) {
    return (
        detail.product_name ??
        detail.variant?.product?.name ??
        detail.product?.name ??
        detail.variant?.name ??
        '-'
    )
}

function getVariantName(detail) {
    return (
        detail.variant_name ??
        detail.variant?.name ??
        ''
    )
}

function getSku(detail) {
    return (
        detail.sku ??
        detail.variant?.sku ??
        '-'
    )
}

function getUnitName(detail) {
    return (
        detail.unit_name ??
        detail.unit?.name ??
        '-'
    )
}

function getOriginalQty(detail) {
    return toNumber(detail.original_qty ?? detail.qty)
}

function getReturnedQty(detail) {
    return toNumber(detail.returned_qty)
}

function getRemainingQty(detail) {
    return toNumber(
        detail.remaining_qty ??
        detail.remaining_return_qty ??
        (
            getOriginalQty(detail) -
            getReturnedQty(detail)
        )
    )
}

function getAvailableQty(detail) {
    return toNumber(
        detail.available_qty ??
        detail.available_stock_qty ??
        detail.available_qty_in_warehouse
    )
}

function getMaxReturnQty(detail) {
    if (detail.max_return_qty !== undefined) {
        return toNumber(detail.max_return_qty)
    }

    return Math.min(
        getRemainingQty(detail),
        getAvailableQty(detail)
    )
}

function getUnitCost(detail) {
    return toNumber(
        detail.unit_cost ??
        detail.cost_per_unit
    )
}

function getTotalCost(detail) {
    return toNumber(detail.return_qty) * getUnitCost(detail)
}

function getTransferDate(transfer) {
    if (!transfer?.transaction_date) {
        return '-'
    }

    const date = String(transfer.transaction_date).slice(0, 10)
    const [year, month, day] = date.split('-')

    if (!year || !month || !day) {
        return '-'
    }

    return `${day}-${month}-${year}`
}
function getTransferId(transfer) {
    return transfer?.id ?? null
}

function getTransferNumber(transfer) {
    return transfer?.number ?? transfer?.transfer_number ?? '-'
}

function getReturnableDetails(transfer) {
    return (transfer?.details ?? [])
        .filter(detail => getMaxReturnQty(detail) > 0)
        .map(detail => ({
            stock_transfer_detail_id:
                detail.stock_transfer_detail_id ?? detail.id,

            product_variant_id:
                detail.product_variant_id ??
                detail.variant?.id ??
                null,

            product_name: getProductName(detail),
            variant_name: getVariantName(detail),
            sku: getSku(detail),

            unit_id: detail.unit_id ?? detail.unit?.id ?? null,
            unit_name: getUnitName(detail),

            original_qty: getOriginalQty(detail),
            returned_qty: getReturnedQty(detail),
            remaining_qty: getRemainingQty(detail),
            available_qty: getAvailableQty(detail),
            max_return_qty: getMaxReturnQty(detail),

            unit_cost: getUnitCost(detail),
            return_qty: '',
            description: '',
        }))
}

function resetForm() {
    form.reset()

    form.stock_transfer_header_id = null
    form.transaction_date = localDate()
    form.description = ''
    form.details = []

    selectedTransfer.value = null
    validationError.value = ''

    form.clearErrors()
}

function selectTransfer(transferId) {
    const transfer = transferOptions.value.find(
        item => String(getTransferId(item)) === String(transferId)
    )

    selectedTransfer.value = transfer ?? null

    form.stock_transfer_header_id = transfer
        ? getTransferId(transfer)
        : null

    form.details = transfer
        ? getReturnableDetails(transfer)
        : []

    validationError.value = ''
    form.clearErrors()
}

function updateReturnQty(detail, value) {
    const qty = value === '' ? '' : Number(value)

    if (qty !== '' && (!Number.isFinite(qty) || qty < 0)) {
        detail.return_qty = ''
        return
    }

    if (qty !== '' && qty > getMaxReturnQty(detail)) {
        detail.return_qty = getMaxReturnQty(detail)

        Swal.fire({
            icon: 'warning',
            title: 'Return Quantity Exceeded',
            text: `Maximum return quantity for ${detail.product_name} is ${formatNumber(getMaxReturnQty(detail))}.`,
        })

        return
    }

    detail.return_qty = qty
    validationError.value = ''
}

function validateForm() {
    if (!form.stock_transfer_header_id) {
        validationError.value = 'Please select a transfer number.'

        Swal.fire({
            icon: 'warning',
            title: 'Transfer Required',
            text: validationError.value,
        })

        return false
    }

    if (!form.transaction_date) {
        validationError.value = 'Please select a return date.'

        Swal.fire({
            icon: 'warning',
            title: 'Date Required',
            text: validationError.value,
        })

        return false
    }

    const selectedDetails = form.details.filter(
        detail => toNumber(detail.return_qty) > 0
    )

    if (!selectedDetails.length) {
        validationError.value =
            'Enter a return quantity for at least one product.'

        Swal.fire({
            icon: 'warning',
            title: 'Return Quantity Required',
            text: validationError.value,
        })

        return false
    }

    for (const detail of selectedDetails) {
        const qty = toNumber(detail.return_qty)
        const maxQty = getMaxReturnQty(detail)

        if (qty <= 0 || qty > maxQty) {
            validationError.value =
                `Invalid return quantity for ${detail.product_name}. Maximum: ${formatNumber(maxQty)}.`

            Swal.fire({
                icon: 'warning',
                title: 'Invalid Return Quantity',
                text: validationError.value,
            })

            return false
        }
    }

    return true
}

function submit() {
    if (form.processing || !validateForm()) {
        return
    }

    const payload = {
        stock_transfer_header_id: form.stock_transfer_header_id,
        transaction_date: form.transaction_date,
        description: form.description || null,

        details: form.details
            .filter(detail => toNumber(detail.return_qty) > 0)
            .map(detail => ({
                stock_transfer_detail_id:
                    detail.stock_transfer_detail_id,

                qty: toNumber(detail.return_qty),
                description: detail.description || null,
            })),
    }

    form.transform(() => payload).post(
        route('stock-transfer-returns.store'),
        {
            preserveScroll: true,

           onSuccess: () => {
                emit('success')
                resetForm()

                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Stock Transfer Return saved successfully.',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                })
            },

            onError: (errors) => {
                console.error(
                    'Stock Transfer Return validation:',
                    errors
                )

                const firstError = Object.values(errors)[0]

                Swal.fire({
                    icon: 'error',
                    title: 'Unable to Save Return',
                    text: firstError ||
                        'Please check the form and try again.',
                })
            },
        }
    )
}

function closeModal() {
    if (form.processing) {
        return
    }

    resetForm()
    emit('close')
}

watch(
    () => props.show,
    visible => {
        if (!visible) {
            return
        }

        resetForm()

        if (props.transfer?.id) {
            const transfer = transferOptions.value.find(
                item => String(getTransferId(item)) ===
                    String(props.transfer.id)
            )

            if (transfer) {
                selectTransfer(getTransferId(transfer))
            }
        }
    }
)

watch(
    () => props.transfer,
    transfer => {
        if (!props.show || !transfer?.id) {
            return
        }

        const existing = transferOptions.value.find(
            item => String(getTransferId(item)) === String(transfer.id)
        )

        if (existing) {
            selectTransfer(getTransferId(existing))
        }
    }
)

watch(
    () => props.previewNumber,
    number => {
        if (props.show && number) {
            // Preview is displayed reactively in the document header.
        }
    }
)
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 p-2 sm:p-5"
                @click.self="closeModal"
            >
                <div
                    class="flex max-h-[90vh] w-full max-w-6xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl"
                    role="dialog"
                    aria-modal="true"
                    aria-label="Stock Transfer Return Form"
                >
                    <!-- Header -->
                    <header
                        class="flex shrink-0 items-center justify-between gap-4 border-b border-slate-200 px-5 py-4 sm:px-7"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600"
                            >
                                <svg
                                    class="h-6 w-6"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M9 14 4 9l5-5"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M4 9h10a6 6 0 0 1 0 12h-3"
                                    />
                                </svg>
                            </div>

                            <div>
                                <h2 class="text-lg font-semibold text-slate-900">
                                    Stock Transfer Return
                                </h2>

                                <p class="text-sm text-slate-500">
                                    Return stock to the original sending branch.
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 disabled:opacity-50"
                            :disabled="form.processing"
                            @click="closeModal"
                        >
                            <svg
                                class="h-5 w-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m6 6 12 12M18 6 6 18"
                                />
                            </svg>
                        </button>
                    </header>

                    <!-- Form -->
                    <form
                        class="flex min-h-0 flex-1 flex-col"
                        @submit.prevent="submit"
                    >
                        <div class="min-h-0 flex-1 space-y-6 overflow-y-auto px-5 py-5 sm:px-7">
                            <!-- Return Information -->
                            <section>
                                <div class="mb-4 flex items-center justify-between">
                                    <h3 class="text-sm font-semibold text-slate-800">
                                        Return Information
                                    </h3>

                                    <span
                                        class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700"
                                    >
                                        Draft
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-slate-700">
                                            Return Number
                                        </label>

                                        <div class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm font-semibold text-slate-700">
                                            {{ documentNumber }}
                                        </div>
                                    </div>

                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-slate-700">
                                            Return Date
                                        </label>

                                    <FlatPickr
                                            v-model="form.transaction_date"
                                            :config="{
                                                dateFormat: 'Y-m-d',
                                                altInput: true,
                                                altFormat: 'd-m-Y',
                                                allowInput: false,
                                            }"
                                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                            placeholder="Select return date"
                                        />

                                        <p
                                            v-if="form.errors.transaction_date"
                                            class="mt-1 text-xs text-red-600"
                                        >
                                            {{ form.errors.transaction_date }}
                                        </p>
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="mb-1.5 block text-sm font-medium text-slate-700">
                                            Transfer Number
                                            <span class="text-red-500">*</span>
                                        </label>

                                        <select
                                            :value="form.stock_transfer_header_id ?? ''"
                                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                            @change="selectTransfer($event.target.value)"
                                        >
                                            <option value="">
                                                Select Transfer Number
                                            </option>

                                            <option
                                                v-for="item in transferOptions"
                                                :key="item.id"
                                                :value="item.id"
                                            >
                                                {{ getTransferNumber(item) }}
                                            </option>
                                        </select>

                                        <p
                                            v-if="form.errors.stock_transfer_header_id"
                                            class="mt-1 text-xs text-red-600"
                                        >
                                            {{ form.errors.stock_transfer_header_id }}
                                        </p>
                                    </div>
                                </div>
                            </section>

                            <!-- Original Transfer Information -->
                            <section v-if="selectedTransfer">
                                <h3 class="mb-4 text-sm font-semibold text-slate-800">
                                    Original Transfer Information
                                </h3>

                                <div class="grid grid-cols-1 gap-4 rounded-xl border border-slate-200 bg-slate-50/70 p-4 sm:grid-cols-2">
                                    <div>
                                        <label class="mb-1.5 block text-xs font-medium text-slate-500">
                                            Transfer Date
                                        </label>

                                        <div class="rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700">
                                            {{ getTransferDate(selectedTransfer) }}
                                        </div>
                                    </div>

                                    <div>
                                        <label class="mb-1.5 block text-xs font-medium text-slate-500">
                                            Transfer Number
                                        </label>

                                        <div class="rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm font-medium text-slate-700">
                                            {{ getTransferNumber(selectedTransfer) }}
                                        </div>
                                    </div>

                                    <div>
                                        <label class="mb-1.5 block text-xs font-medium text-slate-500">
                                            From Branch
                                        </label>

                                        <div class="rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700">
                                            {{ getLocationName(selectedTransfer.from_branch) }}
                                        </div>
                                    </div>

                                    <div>
                                        <label class="mb-1.5 block text-xs font-medium text-slate-500">
                                            From Warehouse
                                        </label>

                                        <div class="rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700">
                                            {{ getLocationName(selectedTransfer.from_warehouse) }}
                                        </div>
                                    </div>

                                    <div>
                                        <label class="mb-1.5 block text-xs font-medium text-slate-500">
                                            To Branch
                                        </label>

                                        <div class="rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700">
                                            {{ getLocationName(selectedTransfer.to_branch) }}
                                        </div>
                                    </div>

                                    <div>
                                        <label class="mb-1.5 block text-xs font-medium text-slate-500">
                                            To Warehouse
                                        </label>

                                        <div class="rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700">
                                            {{ getLocationName(selectedTransfer.to_warehouse) }}
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <!-- Products -->
<section>
    <div class="mb-3 flex items-center justify-between gap-3">
        <h3 class="text-sm font-semibold text-slate-800">
            Products to Return
        </h3>

        <span class="text-xs text-slate-500">
            {{ totalItems }} selected
        </span>
    </div>

    <div
        v-if="!selectedTransfer"
        class="rounded-xl border border-dashed border-slate-300 px-4 py-12 text-center"
    >
        <p class="font-medium text-slate-700">
            Select a transfer number
        </p>

        <p class="mt-1 text-sm text-slate-500">
            Available products will appear here automatically.
        </p>
    </div>

    <div
        v-else-if="!form.details.length"
        class="rounded-xl border border-slate-200 px-4 py-10 text-center"
    >
        <p class="font-medium text-slate-700">
            No products available for return
        </p>

        <p class="mt-1 text-sm text-slate-500">
            All quantities may have been returned or no stock remains.
        </p>
    </div>

 <div
    v-else
    class="min-w-0 overflow-hidden"
>
    <div class="w-full">
        <DataTable
            :bordered="true"
            :hover="true"
            :striped="false"
            sticky-header
            max-height="350px"
             max-width="1450px"
        >
            <DataTableHead sticky>
                <DataTableHeaderCell width="250px">
                    Product
                </DataTableHeaderCell>

                <DataTableHeaderCell
                    width="120px"
                    align="right"
                >
                    Transfer Qty
                </DataTableHeaderCell>

                <DataTableHeaderCell
                    width="130px"
                    align="right"
                >
                    Returned Qty
                </DataTableHeaderCell>

                <DataTableHeaderCell
                    width="130px"
                    align="right"
                >
                    Remaining Qty
                </DataTableHeaderCell>

                <DataTableHeaderCell width="100px">
                    Unit
                </DataTableHeaderCell>

                <DataTableHeaderCell width="150px">
                    Return Qty
                </DataTableHeaderCell>

                <DataTableHeaderCell
                    width="170px"
                    align="right"
                >
                    Unit Cost
                </DataTableHeaderCell>

                <DataTableHeaderCell
                    width="180px"
                    align="right"
                >
                    Total Cost
                </DataTableHeaderCell>
            </DataTableHead>

            <DataTableBody>
                <DataTableRow
                    v-for="detail in form.details"
                    :key="detail.stock_transfer_detail_id"
                >
                    <!-- Product -->
                    <DataTableCell>
                        <div class="min-w-0">
                            <div class="truncate font-medium text-gray-900">
                                {{ getProductName(detail) }}
                            </div>

                            <div
                                v-if="getVariantName(detail)"
                                class="mt-0.5 truncate text-xs text-gray-600"
                            >
                                {{ getVariantName(detail) }}
                            </div>

                            <div class="mt-1 truncate text-xs text-gray-500">
                                SKU: {{ getSku(detail) }}
                            </div>
                        </div>
                    </DataTableCell>

                    <!-- Transfer Qty -->
                    <DataTableCell align="right">
                        <span class="whitespace-nowrap tabular-nums">
                            {{ formatNumber(detail.original_qty) }}
                        </span>
                    </DataTableCell>

                    <!-- Returned Qty -->
                    <DataTableCell align="right">
                        <span class="whitespace-nowrap tabular-nums">
                            {{ formatNumber(detail.returned_qty) }}
                        </span>
                    </DataTableCell>

                    <!-- Remaining Qty -->
                    <DataTableCell align="right">
                        <div class="whitespace-nowrap font-medium tabular-nums text-gray-900">
                            {{ formatNumber(detail.remaining_qty) }}
                        </div>

                        <div class="mt-1 whitespace-nowrap text-xs text-gray-500">
                            Stock: {{ formatNumber(detail.available_qty) }}
                        </div>
                    </DataTableCell>

                    <!-- Unit -->
                    <DataTableCell>
                        <span class="whitespace-nowrap">
                            {{ detail.unit_name || '-' }}
                        </span>
                    </DataTableCell>

                    <!-- Return Qty -->
                    <DataTableCell>
                        <div class="w-28">
                            <input
                                :value="detail.return_qty"
                                type="number"
                                min="0"
                                :max="getMaxReturnQty(detail)"
                                step="1"
                                class="w-full rounded-lg border border-gray-300 px-2 py-2 text-right text-sm tabular-nums focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                @input="updateReturnQty(detail, $event.target.value)"
                            />

                            <div class="mt-1 whitespace-nowrap text-xs text-gray-500">
                                Max: {{ formatNumber(getMaxReturnQty(detail)) }}
                            </div>
                        </div>
                    </DataTableCell>

                    <!-- Unit Cost -->
                    <DataTableCell align="right">
                        <span class="whitespace-nowrap tabular-nums">
                            {{ formatCurrency(detail.unit_cost) }}
                        </span>
                    </DataTableCell>

                    <!-- Total Cost -->
                    <DataTableCell align="right">
                        <span class="block whitespace-nowrap text-right font-medium tabular-nums text-gray-900">
                            {{ formatCurrency(getTotalCost(detail)) }}
                        </span>
                    </DataTableCell>
                </DataTableRow>

                <!-- Total -->
                <DataTableRow>
                    <DataTableCell>
                        <span class="font-semibold text-gray-900">
                            Total
                        </span>
                    </DataTableCell>

                    <DataTableCell align="right">
                        <span class="whitespace-nowrap font-semibold tabular-nums">
                            {{ formatNumber(
                                form.details.reduce(
                                    (total, detail) =>
                                        total + toNumber(detail.original_qty),
                                    0
                                )
                            ) }}
                        </span>
                    </DataTableCell>

                    <DataTableCell align="right">
                        <span class="whitespace-nowrap font-semibold tabular-nums">
                            {{ formatNumber(
                                form.details.reduce(
                                    (total, detail) =>
                                        total + toNumber(detail.returned_qty),
                                    0
                                )
                            ) }}
                        </span>
                    </DataTableCell>

                    <DataTableCell align="right">
                        <span class="whitespace-nowrap font-semibold tabular-nums">
                            {{ formatNumber(
                                form.details.reduce(
                                    (total, detail) =>
                                        total + toNumber(detail.remaining_qty),
                                    0
                                )
                            ) }}
                        </span>
                    </DataTableCell>

                    <DataTableCell />

                    <DataTableCell align="right">
                        <span class="whitespace-nowrap font-semibold tabular-nums text-gray-900">
                            {{ formatNumber(totalReturnQty) }}
                        </span>
                    </DataTableCell>

                    <DataTableCell />

                    <DataTableCell align="right">
                        <span class="block whitespace-nowrap text-right font-semibold tabular-nums text-blue-700">
                            {{ formatCurrency(totalReturnCost) }}
                        </span>
                    </DataTableCell>
                </DataTableRow>
            </DataTableBody>
        </DataTable>
    </div>
</div>
   
                        <div
                            v-if="validationError"
                            class="mt-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700"
                        >
                            {{ validationError }}
                        </div>
                </section>

                            <!-- Summary -->
                            <section class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                <div class="rounded-xl border border-slate-200 p-4">
                                    <p class="text-xs font-medium text-slate-500">
                                        Total Items
                                    </p>

                                    <p class="mt-1 text-xl font-semibold text-slate-900">
                                        {{ totalItems }}
                                    </p>
                                </div>

                                <div class="rounded-xl border border-slate-200 p-4">
                                    <p class="text-xs font-medium text-slate-500">
                                        Total Return Qty
                                    </p>

                                    <p class="mt-1 text-xl font-semibold text-slate-900">
                                        {{ formatNumber(totalReturnQty) }}
                                    </p>
                                </div>

                                <div class="rounded-xl border border-blue-200 bg-blue-50/60 p-4">
                                    <p class="text-xs font-medium text-blue-700">
                                        Total Return Cost
                                    </p>

                                    <p class="mt-1 text-xl font-semibold text-blue-800">
                                        {{ formatCurrency(totalReturnCost) }}
                                    </p>
                                </div>
                            </section>

                            <!-- Description -->
                            <section>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">
                                    Description
                                </label>

                                <textarea
                                    v-model="form.description"
                                    rows="3"
                                    maxlength="5000"
                                    class="w-full resize-y rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                    placeholder="Enter return notes..."
                                />

                                <p
                                    v-if="form.errors.description"
                                    class="mt-1 text-xs text-red-600"
                                >
                                    {{ form.errors.description }}
                                </p>
                            </section>
                        </div>

                        <!-- Footer -->
                        <footer
                            class="flex shrink-0 flex-col-reverse gap-3 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-7"
                        >
                            <p class="text-xs text-slate-500">
                                Stock will change only after this return is posted.
                            </p>

                            <div class="flex items-center justify-end gap-3">
                                <button
                                    type="button"
                                    class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                                    :disabled="form.processing"
                                    @click="closeModal"
                                >
                                    Cancel
                                </button>

                                <button
                                    type="submit"
                                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                                    :disabled="form.processing"
                                >
                                    <svg
                                        v-if="form.processing"
                                        class="h-4 w-4 animate-spin"
                                        viewBox="0 0 24 24"
                                        fill="none"
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
                                            d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z"
                                        />
                                    </svg>

                                    {{ form.processing ? 'Saving...' : 'Save Draft' }}
                                </button>
                            </div>
                        </footer>
                    </form>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>