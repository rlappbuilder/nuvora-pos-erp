<script setup>
import {
    computed,
    onBeforeUnmount,
    reactive,
    ref,
    watch,
} from 'vue'
import { useForm } from '@inertiajs/vue3'
import FlatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'
import { success, error } from '@/Utils'
import StockTransferProductSearchModal from './StockTransferProductSearchModal.vue'

const props = defineProps({
    show: { type: Boolean, default: false },
    transfer: { type: Object, default: null },
    branches: { type: Array, default: () => [] },
    warehouses: { type: Array, default: () => [] },
    variants: { type: Array, default: () => [] },
    units: { type: Array, default: () => [] },
    previewNumber: { type: String, default: '' },
    activeBranchId: { type: [Number, String, null], default: null },
    processing: { type: Boolean, default: false },
})

const emit = defineEmits(['close', 'success'])
const isEdit = computed(() => Boolean(props.transfer?.id))

const form = useForm({
    number: '',
    transaction_date: '',
    from_branch_id: null,
    from_warehouse_id: null,
    to_branch_id: null,
    to_warehouse_id: null,
    description: '',
    details: [],
})

const warehouseStocks = ref([])
const stocksLoading = ref(false)
const stockError = ref('')
const showProductSearch = ref(false)
const searchInput = ref(null)
const currentTime = ref(new Date())
const qtyInputs = reactive({})
const previousQty = reactive({})
let clockInterval = null
let stockRequestId = 0

const documentNumber = computed(() =>
    form.number || props.previewNumber || 'Generating...'
)

const headerDateTime = computed(() =>
    new Intl.DateTimeFormat('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
    }).format(currentTime.value)
)

function localDate() {
    const now = new Date()

    return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`
}

function toNumber(value) {
    const number = Number(value ?? 0)

    return Number.isFinite(number) ? number : 0
}

function formatQty(value) {
    return new Intl.NumberFormat('id-ID', {
        maximumFractionDigits: 4,
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

function sameId(left, right) {
    return left !== null &&
        left !== undefined &&
        right !== null &&
        right !== undefined &&
        String(left) === String(right)
}

function createEmptyForm() {
    return {
        number: props.previewNumber || '',
        transaction_date: localDate(),
        from_branch_id: props.activeBranchId || null,
        from_warehouse_id: null,
        to_branch_id: null,
        to_warehouse_id: null,
        description: '',
        details: [],
    }
}

function clearQtyBuffers() {
    Object.keys(qtyInputs).forEach((key) => {
        delete qtyInputs[key]
    })

    Object.keys(previousQty).forEach((key) => {
        delete previousQty[key]
    })
}

function resetForm() {
    const defaults = createEmptyForm()

    form.clearErrors()
    form.defaults(defaults)
    form.reset()
    form.clearErrors()

    warehouseStocks.value = []
    stockError.value = ''

    clearQtyBuffers()
}

function populateForm(transfer) {
    form.clearErrors()

    form.number = transfer.number ?? ''

    form.transaction_date = transfer.transaction_date
        ? String(transfer.transaction_date).slice(0, 10)
        : localDate()

    form.from_branch_id = transfer.from_branch_id ?? null
    form.from_warehouse_id = transfer.from_warehouse_id ?? null
    form.to_branch_id = transfer.to_branch_id ?? null
    form.to_warehouse_id = transfer.to_warehouse_id ?? null
    form.description = transfer.description ?? ''

    form.details = Array.isArray(transfer.details)
        ? transfer.details.map((detail, index) => {
            const qty = toNumber(detail.qty ?? detail.quantity)
            const unitId = detail.unit_id ?? detail.unit?.id ?? null

            return {
                _key: `existing-${detail.id ?? index}`,
                product_variant_id:
                    detail.product_variant_id ??
                    detail.variant?.id ??
                    null,
                unit_id: unitId,
                qty,
                unit_cost: toNumber(detail.unit_cost),
                total_cost: toNumber(
                    detail.total_cost ??
                    qty * toNumber(detail.unit_cost)
                ),
                available_qty: toNumber(detail.available_qty),
                stock_unit_id:
                    detail.stock_unit_id ??
                    unitId,
                stock_conversion_factor: 1,
                conversion_factor: 1,
            }
        })
        : []

    clearQtyBuffers()

    form.details.forEach((detail) => {
        qtyInputs[detail._key] = String(detail.qty)
        previousQty[detail._key] = detail.qty
    })

    form.defaults({
        number: form.number,
        transaction_date: form.transaction_date,
        from_branch_id: form.from_branch_id,
        from_warehouse_id: form.from_warehouse_id,
        to_branch_id: form.to_branch_id,
        to_warehouse_id: form.to_warehouse_id,
        description: form.description,
        details: form.details.map((detail) => ({ ...detail })),
    })
}

const destinationBranches = computed(() =>
    props.branches.filter(
        (branch) => !sameId(branch.id, form.from_branch_id)
    )
)

const sourceWarehouses = computed(() =>
    props.warehouses.filter(
        (warehouse) =>
            sameId(warehouse.branch_id, form.from_branch_id)
    )
)

const destinationWarehouses = computed(() =>
    props.warehouses.filter(
        (warehouse) =>
            sameId(warehouse.branch_id, form.to_branch_id)
    )
)

function getVariant(productVariantId) {
    return props.variants.find(
        (variant) => sameId(variant.id, productVariantId)
    )
}

function getVariantUnits(productVariantId) {
    return getVariant(productVariantId)?.units ?? []
}

function getUnitLabel(unitId, productVariantId = null) {
    const variantUnit = productVariantId
        ? getVariantUnits(productVariantId).find(
            (unit) => sameId(unit.id, unitId)
        )
        : null

    if (variantUnit?.label) {
        return variantUnit.label
    }

    const unit = props.units.find(
        (item) => sameId(item.id, unitId)
    )

    return unit?.name ?? '—'
}

function getProductName(stock) {
    return stock?.variant?.product?.name ??
        stock?.variant?.name ??
        'Unnamed Product'
}

function getSku(stock) {
    return stock?.variant?.sku ?? '—'
}

function getUnitFactor(productVariantId, unitId) {
    const unit = getVariantUnits(productVariantId).find(
        (item) => sameId(item.id, unitId)
    )

    return toNumber(unit?.conversion_factor) || 1
}

function getStockForDetail(detail) {
    return warehouseStocks.value.find(
        (stock) =>
            sameId(
                stock.product_variant_id,
                detail.product_variant_id
            )
    )
}

function getAvailableInSelectedUnit(detail) {
    const stockFactor =
        toNumber(detail.stock_conversion_factor) || 1

    const selectedFactor =
        toNumber(detail.conversion_factor) || 1

    return (
        toNumber(detail.available_qty) *
        stockFactor /
        selectedFactor
    )
}

function getSelectedUnitCost(detail) {
    const stockFactor =
        toNumber(detail.stock_conversion_factor) || 1

    const selectedFactor =
        toNumber(detail.conversion_factor) || 1

    return (
        toNumber(detail.unit_cost) *
        selectedFactor /
        stockFactor
    )
}

function getAvailableLabel(detail) {
    return `${formatQty(getAvailableInSelectedUnit(detail))} ${getUnitLabel(
        detail.unit_id,
        detail.product_variant_id
    )}`
}

function recalculateDetail(detail) {
    detail.total_cost =
        toNumber(detail.qty) *
        getSelectedUnitCost(detail)
}

function refreshExistingDetails() {
    form.details.forEach((detail) => {
        const stock = warehouseStocks.value.find(
            (item) =>
                sameId(
                    item.product_variant_id,
                    detail.product_variant_id
                ) &&
                sameId(
                    item.unit_id,
                    detail.stock_unit_id ?? detail.unit_id
                )
        )

        if (!stock) return

        detail.available_qty = toNumber(stock.available_qty)
        detail.unit_cost = toNumber(stock.average_cost)
        detail.stock_unit_id = stock.unit_id

        detail.stock_conversion_factor = getUnitFactor(
            detail.product_variant_id,
            stock.unit_id
        )

        detail.conversion_factor = getUnitFactor(
            detail.product_variant_id,
            detail.unit_id
        )

        recalculateDetail(detail)
    })
}

async function loadWarehouseStocks(warehouseId) {
    const requestId = ++stockRequestId

    warehouseStocks.value = []
    stockError.value = ''

    if (!warehouseId) {
        stocksLoading.value = false
        return
    }

    stocksLoading.value = true

    try {
        const response = await fetch(
            route('stock-transfers.warehouse-stocks', warehouseId),
            {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            }
        )

        if (!response.ok) {
            throw new Error('Failed to load warehouse stock.')
        }

        const result = await response.json()

        if (requestId !== stockRequestId) return

        warehouseStocks.value = Array.isArray(result.data)
            ? result.data
            : []

        refreshExistingDetails()
    } catch (exception) {
        if (requestId !== stockRequestId) return

        stockError.value =
            exception.message ||
            'Unable to load stock for the selected warehouse.'

        error('Failed to load warehouse stock.')
    } finally {
        if (requestId === stockRequestId) {
            stocksLoading.value = false
        }
    }
}

watch(
    () => form.from_branch_id,
    (branchId, oldBranchId) => {
        if (sameId(branchId, oldBranchId)) return

        if (
            form.from_warehouse_id &&
            !props.warehouses.some(
                (warehouse) =>
                    sameId(
                        warehouse.id,
                        form.from_warehouse_id
                    ) &&
                    sameId(warehouse.branch_id, branchId)
            )
        ) {
            form.from_warehouse_id = null
        }

        if (sameId(form.to_branch_id, branchId)) {
            form.to_branch_id = null
            form.to_warehouse_id = null
        }

        form.clearErrors(
            'from_branch_id',
            'to_branch_id',
            'from_warehouse_id',
            'to_warehouse_id'
        )

        form.details = []

        clearQtyBuffers()
        warehouseStocks.value = []
    }
)

watch(
    () => form.to_branch_id,
    (branchId) => {
        if (
            form.to_warehouse_id &&
            !props.warehouses.some(
                (warehouse) =>
                    sameId(
                        warehouse.id,
                        form.to_warehouse_id
                    ) &&
                    sameId(warehouse.branch_id, branchId)
            )
        ) {
            form.to_warehouse_id = null
        }

        form.clearErrors(
            'to_branch_id',
            'to_warehouse_id'
        )
    }
)

watch(
    () => form.from_warehouse_id,
    async (warehouseId, oldWarehouseId) => {
        if (sameId(warehouseId, oldWarehouseId)) return

        if (oldWarehouseId && form.details.length) {
            form.details = []
            clearQtyBuffers()
        }

        await loadWarehouseStocks(warehouseId)
    }
)

function openProductSearch() {
    form.clearErrors('details')

    if (!form.from_warehouse_id) {
        form.setError(
            'from_warehouse_id',
            'Please select a source warehouse first.'
        )

        error('Please select a source warehouse first.')
        return
    }

    showProductSearch.value = true
}

function closeProductSearch() {
    showProductSearch.value = false
    searchInput.value?.focus()
}

function addStock(stock) {
    if (!stock || toNumber(stock.available_qty) <= 0) {
        error('No available stock for this product.')
        return
    }

    const variantId = stock.product_variant_id
    const stockUnitId = stock.unit_id

    const existing = form.details.find(
        (detail) =>
            sameId(
                detail.product_variant_id,
                variantId
            ) &&
            sameId(detail.unit_id, stockUnitId)
    )

    if (existing) {
        const available = getAvailableInSelectedUnit(existing)
        const nextQty = toNumber(existing.qty) + 1

        if (nextQty > available) {
            showStockError(available)
            return
        }

        existing.qty = nextQty
        qtyInputs[existing._key] = String(nextQty)
        previousQty[existing._key] = nextQty

        recalculateDetail(existing)
        showProductSearch.value = false

        return
    }

    const factor = getUnitFactor(
        variantId,
        stockUnitId
    )

    const detail = {
        _key: `stock-${variantId}-${stockUnitId}-${Date.now()}`,
        product_variant_id: variantId,
        unit_id: stockUnitId,
        qty: 1,
        unit_cost: toNumber(stock.average_cost),
        total_cost: toNumber(stock.average_cost),
        available_qty: toNumber(stock.available_qty),
        stock_unit_id: stockUnitId,
        stock_conversion_factor: factor,
        conversion_factor: factor,
    }

    form.details.push(detail)

    qtyInputs[detail._key] = '1'
    previousQty[detail._key] = 1

    showProductSearch.value = false
    form.clearErrors('details')
}

function showStockError(available) {
    error(
        `Stock available only ${formatQty(available)}. Cannot transfer over available stock.`
    )
}

function handleQtyInput(detail, event) {
    const raw = event.target.value
    const key = detail._key

    qtyInputs[key] = raw

    // Allow empty values and decimal input while the user is typing.
    if (raw === '' || raw === '.' || raw === ',') {
        detail.qty = 0
        recalculateDetail(detail)
        return
    }

    const parsed = Number(raw)

    if (!Number.isFinite(parsed) || parsed < 0) {
        qtyInputs[key] = String(
            previousQty[key] ?? detail.qty
        )

        event.target.value = qtyInputs[key]
        return
    }

    const available = getAvailableInSelectedUnit(detail)

    if (parsed > available) {
        qtyInputs[key] = String(
            previousQty[key] ?? detail.qty
        )

        event.target.value = qtyInputs[key]
        showStockError(available)
        return
    }

    detail.qty = parsed
    previousQty[key] = parsed

    recalculateDetail(detail)
}

function handleQtyBlur(detail) {
    const key = detail._key
    const raw = qtyInputs[key]
    const parsed = raw === '' ? 0 : Number(raw)

    if (
        !Number.isFinite(parsed) ||
        parsed < 0 ||
        parsed > getAvailableInSelectedUnit(detail)
    ) {
        detail.qty = toNumber(previousQty[key])
    } else {
        detail.qty = parsed
        previousQty[key] = parsed
    }

    qtyInputs[key] = String(detail.qty)

    recalculateDetail(detail)
}

function changeUnit(detail, unitId) {
    const oldFactor =
        toNumber(detail.conversion_factor) || 1

    const newFactor = getUnitFactor(
        detail.product_variant_id,
        unitId
    )

    const qtyInBase =
        toNumber(detail.qty) * oldFactor

    detail.unit_id = unitId
    detail.conversion_factor = newFactor
    detail.qty = qtyInBase / newFactor

    const available = getAvailableInSelectedUnit(detail)

    if (detail.qty > available) {
        detail.qty = available
        showStockError(available)
    }

    qtyInputs[detail._key] = String(detail.qty)
    previousQty[detail._key] = detail.qty

    recalculateDetail(detail)
}

function removeDetail(index) {
    const detail = form.details[index]

    if (detail?._key) {
        delete qtyInputs[detail._key]
        delete previousQty[detail._key]
    }

    form.details.splice(index, 1)
}

const totalItems = computed(
    () => form.details.length
)

const totalQuantity = computed(
    () =>
        form.details.reduce(
            (total, detail) =>
                total + toNumber(detail.qty),
            0
        )
)

const estimatedTotal = computed(
    () =>
        form.details.reduce(
            (total, detail) =>
                total + toNumber(detail.total_cost),
            0
        )
)

function validateForm() {
    form.clearErrors()

    const requiredFields = [
        [
            'from_branch_id',
            'Please select a source branch.',
        ],
        [
            'from_warehouse_id',
            'Please select a source warehouse.',
        ],
        [
            'to_branch_id',
            'Please select a destination branch.',
        ],
        [
            'to_warehouse_id',
            'Please select a destination warehouse.',
        ],
        [
            'transaction_date',
            'Please select a transaction date.',
        ],
    ]

    for (const [field, message] of requiredFields) {
        if (!form[field]) {
            form.setError(field, message)
            error(message)
            return false
        }
    }

    if (
        sameId(
            form.from_branch_id,
            form.to_branch_id
        )
    ) {
        form.setError(
            'to_branch_id',
            'Source and destination branches must be different.'
        )

        error(
            'Source and destination branches must be different.'
        )

        return false
    }

    if (
        sameId(
            form.from_warehouse_id,
            form.to_warehouse_id
        )
    ) {
        form.setError(
            'to_warehouse_id',
            'Source and destination warehouses must be different.'
        )

        error(
            'Source and destination warehouses must be different.'
        )

        return false
    }

    if (!form.details.length) {
        form.setError(
            'details',
            'Please add at least one product.'
        )

        error('Please add at least one product.')
        return false
    }

    const invalidDetail = form.details.some(
        (detail) =>
            !detail.product_variant_id ||
            !detail.unit_id ||
            toNumber(detail.qty) <= 0 ||
            toNumber(detail.qty) >
                getAvailableInSelectedUnit(detail)
    )

    if (invalidDetail) {
        form.setError(
            'details',
            'Please check product quantities and available stock.'
        )

        error(
            'Please check product quantities and available stock.'
        )

        return false
    }

    return true
}

function payloadDetails() {
    return form.details.map((detail) => ({
        product_variant_id: detail.product_variant_id,
        unit_id: detail.unit_id,
        qty: toNumber(detail.qty),
        unit_cost: toNumber(
            getSelectedUnitCost(detail)
        ),
        total_cost: toNumber(detail.total_cost),
    }))
}

function submit(saveAndNew = false) {
    if (
        form.processing ||
        props.processing ||
        !validateForm()
    ) {
        return
    }

    const payload = {
        number: form.number,
        transaction_date: form.transaction_date,
        from_branch_id: form.from_branch_id,
        from_warehouse_id: form.from_warehouse_id,
        to_branch_id: form.to_branch_id,
        to_warehouse_id: form.to_warehouse_id,
        description: form.description,
        details: payloadDetails(),
    }

    const options = {
        preserveScroll: true,

        onSuccess: () => {
            success(
                'Success',
                isEdit.value
                    ? 'Stock Transfer updated successfully.'
                    : 'Stock Transfer created successfully.'
            )

            if (saveAndNew && !isEdit.value) {
                emit('success', { saveAndNew: true })

                resetForm()

                form.number = props.previewNumber || ''
                form.transaction_date = localDate()
                form.from_branch_id =
                    props.activeBranchId || null

                return
            }

            emit('success', { saveAndNew: false })
        },

        onError: (errors) => {
            console.error(
                'STOCK TRANSFER SAVE ERRORS:',
                errors
            )

            error(
                isEdit.value
                    ? 'Failed to update Stock Transfer.'
                    : 'Failed to create Stock Transfer.'
            )
        },
    }

    if (isEdit.value) {
        form.transform(() => payload).put(
            route(
                'stock-transfers.update',
                props.transfer.id
            ),
            options
        )
    } else {
        form.transform(() => payload).post(
            route('stock-transfers.store'),
            options
        )
    }
}

function submitAndNew() {
    submit(true)
}

function closeModal() {
    if (form.processing || props.processing) return

    showProductSearch.value = false
    emit('close')
}

function handleGlobalKeydown(event) {
    if (!props.show) return

    if (event.key === 'F2') {
        event.preventDefault()
        openProductSearch()
    }

    if (
        event.key === 'Escape' &&
        !showProductSearch.value
    ) {
        closeModal()
    }
}

watch(
    () => props.show,
    (visible) => {
        if (!visible) {
            showProductSearch.value = false
            return
        }

        currentTime.value = new Date()

        if (props.transfer) {
            populateForm(props.transfer)
        } else {
            resetForm()
            form.from_branch_id =
                props.activeBranchId || null
        }

        if (form.from_warehouse_id) {
            loadWarehouseStocks(
                form.from_warehouse_id
            )
        }
    }
)

watch(
    () => props.previewNumber,
    (number) => {
        if (
            !isEdit.value &&
            number &&
            !props.transfer
        ) {
            form.number = number
        }
    }
)

watch(
    () => props.activeBranchId,
    (branchId) => {
        if (
            !props.show ||
            isEdit.value ||
            !branchId
        ) {
            return
        }

        form.from_branch_id = branchId
    }
)

watch(
    () => props.transfer,
    (transfer) => {
        if (props.show && transfer) {
            populateForm(transfer)
        }
    }
)

if (typeof window !== 'undefined') {
    clockInterval = window.setInterval(() => {
        currentTime.value = new Date()
    }, 1000)

    window.addEventListener(
        'keydown',
        handleGlobalKeydown
    )
}

onBeforeUnmount(() => {
    if (clockInterval) {
        window.clearInterval(clockInterval)
    }

    window.removeEventListener(
        'keydown',
        handleGlobalKeydown
    )
})
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
                    class="flex max-h-[96vh] w-full max-w-7xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl"
                    role="dialog"
                    aria-modal="true"
                    aria-label="Stock Transfer Form"
                >
                    <!-- Header -->
                    <header
                        class="flex items-start gap-4 border-b border-slate-200 px-4 py-4 sm:px-7"
                    >
                        <div class="flex min-w-0 flex-1 items-center gap-3">
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
                                        d="m12 3 9 5-9 5-9-5 9-5Z"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M3 8v8l9 5 9-5V8M12 13v8"
                                    />
                                </svg>
                            </div>

                            <div class="min-w-0">
                                <h2 class="text-lg font-semibold text-slate-900">
                                    {{ isEdit ? 'Edit Stock Transfer' : 'New Stock Transfer' }}
                                </h2>

                                <p class="mt-1 text-sm text-slate-500">
                                    Transfer inventory between branches
                                </p>
                            </div>
                        </div>

                        <div class="flex shrink-0 items-center gap-3">
                            <div
                                class="whitespace-nowrap pt-1 text-right text-sm font-medium tabular-nums text-slate-600"
                            >
                                {{ headerDateTime }}
                            </div>

                            <button
                                type="button"
                                class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700"
                                :disabled="form.processing || processing"
                                aria-label="Close modal"
                                @click="closeModal"
                            >
                                <svg
                                    class="h-5 w-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M6 6l12 12M18 6 6 18"
                                    />
                                </svg>
                            </button>
                        </div>
                    </header>

                    <form
                        class="flex min-h-0 flex-1 flex-col"
                        @submit.prevent="submit(false)"
                    >
                        <div
                            class="min-h-0 flex-1 space-y-5 overflow-y-auto px-4 py-5 sm:px-7"
                        >
                            <!-- Transfer Information -->
                            <section
                                class="rounded-xl border border-slate-200 p-4 sm:p-5"
                            >
                                <div class="mb-4 flex items-center gap-2">
                                    <span
                                        class="h-5 w-1 rounded-full bg-blue-600"
                                    ></span>

                                    <h3
                                        class="text-sm font-semibold uppercase tracking-wide text-slate-700"
                                    >
                                        Stock Transfer Information
                                    </h3>
                                </div>

                                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                                    <!-- Transaction Number -->
                                    <div>
                                        <label
                                            class="mb-1.5 block text-sm font-medium text-slate-700"
                                        >
                                            Transaction Number
                                        </label>

                                        <input
                                            :value="documentNumber"
                                            type="text"
                                            readonly
                                            class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700"
                                        />
                                    </div>

                                    <!-- Transaction Date -->
                                    <div>
                                        <label
                                            class="mb-1.5 block text-sm font-medium text-slate-700"
                                        >
                                            Transaction Date
                                            <span class="text-red-500">*</span>
                                        </label>

                                        <FlatPickr
                                            v-model="form.transaction_date"
                                            :config="{
                                                dateFormat: 'Y-m-d',
                                                allowInput: true,
                                                clickOpens: true,
                                            }"
                                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                        />

                                        <p
                                            v-if="form.errors.transaction_date"
                                            class="mt-1 text-xs text-red-600"
                                        >
                                            {{ form.errors.transaction_date }}
                                        </p>
                                    </div>

                                    <!-- From Branch -->
                                    <div>
                                        <label
                                            class="mb-1.5 block text-sm font-medium text-slate-700"
                                        >
                                            From Branch
                                            <span class="text-red-500">*</span>
                                        </label>

                                        <select
                                            v-model="form.from_branch_id"
                                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                        >
                                            <option :value="null">
                                                Select source branch
                                            </option>

                                            <option
                                                v-for="branch in branches"
                                                :key="branch.id"
                                                :value="branch.id"
                                            >
                                                {{ branch.label ?? branch.name }}
                                            </option>
                                        </select>

                                        <p
                                            v-if="form.errors.from_branch_id"
                                            class="mt-1 text-xs text-red-600"
                                        >
                                            {{ form.errors.from_branch_id }}
                                        </p>
                                    </div>

                                    <!-- To Branch -->
                                    <div>
                                        <label
                                            class="mb-1.5 block text-sm font-medium text-slate-700"
                                        >
                                            To Branch
                                            <span class="text-red-500">*</span>
                                        </label>

                                        <select
                                            v-model="form.to_branch_id"
                                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                        >
                                            <option :value="null">
                                                Select destination branch
                                            </option>

                                            <option
                                                v-for="branch in destinationBranches"
                                                :key="branch.id"
                                                :value="branch.id"
                                            >
                                                {{ branch.label ?? branch.name }}
                                            </option>
                                        </select>

                                        <p
                                            v-if="form.errors.to_branch_id"
                                            class="mt-1 text-xs text-red-600"
                                        >
                                            {{ form.errors.to_branch_id }}
                                        </p>
                                    </div>

                                    <!-- From Warehouse -->
                                    <div>
                                        <label
                                            class="mb-1.5 block text-sm font-medium text-slate-700"
                                        >
                                            From Warehouse
                                            <span class="text-red-500">*</span>
                                        </label>

                                        <select
                                            v-model="form.from_warehouse_id"
                                            :disabled="!form.from_branch_id"
                                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 disabled:bg-slate-100"
                                        >
                                            <option :value="null">
                                                Select source warehouse
                                            </option>

                                            <option
                                                v-for="warehouse in sourceWarehouses"
                                                :key="warehouse.id"
                                                :value="warehouse.id"
                                            >
                                                {{ warehouse.label ?? warehouse.name }}
                                            </option>
                                        </select>

                                        <p
                                            v-if="form.errors.from_warehouse_id"
                                            class="mt-1 text-xs text-red-600"
                                        >
                                            {{ form.errors.from_warehouse_id }}
                                        </p>
                                    </div>

                                    <!-- To Warehouse -->
                                    <div>
                                        <label
                                            class="mb-1.5 block text-sm font-medium text-slate-700"
                                        >
                                            To Warehouse
                                            <span class="text-red-500">*</span>
                                        </label>

                                        <select
                                            v-model="form.to_warehouse_id"
                                            :disabled="!form.to_branch_id"
                                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 disabled:bg-slate-100"
                                        >
                                            <option :value="null">
                                                Select destination warehouse
                                            </option>

                                            <option
                                                v-for="warehouse in destinationWarehouses"
                                                :key="warehouse.id"
                                                :value="warehouse.id"
                                            >
                                                {{ warehouse.label ?? warehouse.name }}
                                            </option>
                                        </select>

                                        <p
                                            v-if="form.errors.to_warehouse_id"
                                            class="mt-1 text-xs text-red-600"
                                        >
                                            {{ form.errors.to_warehouse_id }}
                                        </p>
                                    </div>
                                </div>
                            </section>

                            <!-- Product Details -->
                            <section
                                class="overflow-hidden rounded-xl border border-slate-200"
                            >
                                <div
                                    class="flex flex-col gap-3 border-b border-slate-200 p-4 sm:flex-row sm:items-center sm:justify-between sm:px-5"
                                >
                                    <div>
                                        <h3 class="text-base font-semibold text-slate-900">
                                            Stock Details
                                        </h3>

                                        <p class="mt-1 text-sm text-slate-500">
                                            Search and add products from the source warehouse.
                                        </p>
                                    </div>

                                    <button
                                        ref="searchInput"
                                        type="button"
                                        :disabled="!form.from_warehouse_id || stocksLoading"
                                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-slate-300"
                                        @click="openProductSearch"
                                    >
                                        <svg
                                            class="h-4 w-4"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <circle cx="11" cy="11" r="7" />
                                            <path
                                                stroke-linecap="round"
                                                d="m16 16 4 4"
                                            />
                                        </svg>

                                        Search Product (F2)
                                    </button>
                                </div>

                                <div
                                    v-if="stockError"
                                    class="m-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                                >
                                    {{ stockError }}
                                </div>

                                <div
                                    v-if="stocksLoading"
                                    class="px-5 py-10 text-center text-sm text-slate-500"
                                >
                                    Loading warehouse stock...
                                </div>

                                <div
                                    v-else-if="!form.from_warehouse_id"
                                    class="px-5 py-12 text-center"
                                >
                                    <p class="font-medium text-slate-700">
                                        Select a source warehouse
                                    </p>

                                    <p class="mt-1 text-sm text-slate-500">
                                        Products will be available after you select the source warehouse.
                                    </p>
                                </div>

                                <div
                                    v-else-if="!form.details.length"
                                    class="px-5 py-12 text-center"
                                >
                                    <div
                                        class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-blue-50 text-blue-600"
                                    >
                                        <svg
                                            class="h-6 w-6"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                        >
                                            <circle cx="11" cy="11" r="7" />
                                            <path
                                                stroke-linecap="round"
                                                d="m16 16 4 4"
                                            />
                                        </svg>
                                    </div>

                                    <p class="font-medium text-slate-800">
                                        No product found
                                    </p>

                                    <p class="mt-1 text-sm text-slate-500">
                                        Search or scan a product to begin stock transfer.
                                    </p>

                                    <button
                                        type="button"
                                        class="mt-4 text-sm font-semibold text-blue-600 hover:text-blue-700"
                                        @click="openProductSearch"
                                    >
                                        + Add Product
                                    </button>
                                </div>

                                <div v-else class="overflow-x-auto">
                                    <table class="w-full min-w-[1000px] text-left text-sm">
                                        <thead
                                            class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"
                                        >
                                            <tr>
                                                <th class="px-4 py-3">Product</th>
                                                <th class="px-4 py-3">Available Stock</th>
                                                <th class="px-4 py-3">Unit</th>
                                                <th class="px-4 py-3">Transfer Qty</th>
                                                <th class="px-4 py-3 text-right">Unit Cost</th>
                                                <th class="px-4 py-3 text-right">Total Cost</th>
                                                <th class="px-4 py-3 text-center">Action</th>
                                            </tr>
                                        </thead>

                                        <tbody class="divide-y divide-slate-100">
                                            <tr
                                                v-for="(detail, index) in form.details"
                                                :key="detail._key"
                                                class="hover:bg-slate-50/70"
                                            >
                                                <td class="px-4 py-4">
                                                    <div class="font-medium text-slate-900">
                                                        {{ getProductName(getStockForDetail(detail)) }}
                                                    </div>

                                                    <div class="mt-1 text-xs text-slate-500">
                                                        {{ getSku(getStockForDetail(detail)) }}
                                                    </div>
                                                </td>

                                                <td class="whitespace-nowrap px-4 py-4 text-slate-700">
                                                    {{ getAvailableLabel(detail) }}
                                                </td>

                                                <td class="px-4 py-4">
                                                    <select
                                                        :value="detail.unit_id"
                                                        class="w-32 rounded-lg border border-slate-300 bg-white px-2 py-2 text-sm"
                                                        @change="changeUnit(detail, $event.target.value)"
                                                    >
                                                        <option
                                                            v-for="unit in getVariantUnits(detail.product_variant_id)"
                                                            :key="unit.id"
                                                            :value="unit.id"
                                                        >
                                                            {{ unit.label }}
                                                        </option>
                                                    </select>
                                                </td>

                                                <td class="px-4 py-4">
                                                    <input
                                                        :value="qtyInputs[detail._key] ?? String(detail.qty)"
                                                        type="text"
                                                        inputmode="decimal"
                                                        autocomplete="off"
                                                        class="w-28 rounded-lg border border-slate-300 px-3 py-2 text-right text-sm tabular-nums focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                                        @focus="previousQty[detail._key] = detail.qty"
                                                        @input="handleQtyInput(detail, $event)"
                                                        @blur="handleQtyBlur(detail)"
                                                    />
                                                </td>

                                                <td class="whitespace-nowrap px-4 py-4 text-right text-slate-600">
                                                    {{ formatCurrency(getSelectedUnitCost(detail)) }}
                                                </td>

                                                <td class="whitespace-nowrap px-4 py-4 text-right font-medium text-slate-900">
                                                    {{ formatCurrency(detail.total_cost) }}
                                                </td>

                                                <td class="px-4 py-4 text-center">
                                                    <button
                                                        type="button"
                                                        class="rounded-lg p-2 text-red-500 transition hover:bg-red-50"
                                                        aria-label="Remove product"
                                                        @click="removeDetail(index)"
                                                    >
                                                        <svg
                                                            class="h-4 w-4"
                                                            viewBox="0 0 24 24"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            stroke-width="1.8"
                                                        >
                                                            <path
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                d="M4 7h16M10 11v6m4-6v6M5 7l1 13h12l1-13M9 7V4h6v3"
                                                            />
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div
                                    v-if="form.errors.details"
                                    class="border-t border-red-100 bg-red-50 px-4 py-3 text-sm text-red-700"
                                >
                                    {{ form.errors.details }}
                                </div>
                            </section>

                            <!-- Description and Summary -->
                            <section class="grid grid-cols-1 gap-5 lg:grid-cols-5">
                                <div class="lg:col-span-3">
                                    <label
                                        class="mb-2 block text-sm font-semibold text-slate-700"
                                    >
                                        Description
                                    </label>

                                    <textarea
                                        v-model="form.description"
                                        rows="5"
                                        maxlength="500"
                                        placeholder="Add notes about this stock transfer..."
                                        class="w-full resize-y rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                    ></textarea>

                                    <div class="mt-1 text-right text-xs text-slate-400">
                                        {{ form.description.length }}/500
                                    </div>
                                </div>

                               <div
                                    class="rounded-xl border border-blue-100 bg-blue-50/70 p-5 lg:col-span-2"
                                >
                                    <h3 class="mb-4 text-base font-semibold text-slate-900">
                                        Summary
                                    </h3>

                                    <dl class="space-y-3">
                                        <div class="flex items-center justify-between gap-4">
                                            <dt class="text-sm text-slate-600">
                                                Total Items
                                            </dt>

                                            <dd class="font-semibold text-slate-900">
                                                {{ totalItems }}
                                            </dd>
                                        </div>

                                        <div class="flex items-center justify-between gap-4">
                                            <dt class="text-sm text-slate-600">
                                                Total Quantity
                                            </dt>

                                            <dd class="font-semibold text-slate-900">
                                                {{ formatQty(totalQuantity) }}
                                            </dd>
                                        </div>

                                        <div class="border-t border-blue-100 pt-3">
                                            <div class="flex items-start justify-between gap-4">
                                                <dt class="text-sm font-semibold text-slate-700">
                                                    Estimated Total
                                                </dt>

                                                <dd class="text-right text-lg font-bold text-blue-700">
                                                    {{ formatCurrency(estimatedTotal) }}
                                                </dd>
                                            </div>
                                        </div>
                                    </dl>
                                </div>
                            </section>
                        </div>

                        <!-- Footer -->
                        <footer
                            class="flex flex-col-reverse gap-3 border-t border-slate-200 bg-white px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-7"
                        >
                            <button
                                type="button"
                                :disabled="form.processing || processing"
                                class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 disabled:opacity-50"
                                @click="closeModal"
                            >
                                Cancel
                            </button>

                            <div class="flex flex-col gap-3 sm:flex-row">
                                <button
                                    type="button"
                                    :disabled="form.processing || processing"
                                    class="rounded-lg border border-blue-600 px-5 py-2.5 text-sm font-semibold text-blue-700 transition hover:bg-blue-50 disabled:opacity-50"
                                    @click="submitAndNew"
                                >
                                    {{ form.processing ? 'Saving...' : 'Save & New' }}
                                </button>

                                <button
                                    type="submit"
                                    :disabled="form.processing || processing"
                                    class="rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    {{ form.processing ? 'Saving...' : 'Save' }}
                                </button>
                            </div>
                        </footer>
                    </form>
                </div>
            </div>
        </Transition>
    </Teleport>

    <StockTransferProductSearchModal
        :show="showProductSearch"
        :stocks="warehouseStocks"
        @close="closeProductSearch"
        @select="addStock"
    />
</template>

                                   