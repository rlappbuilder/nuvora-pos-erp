<script setup>
import {
    computed,
    onMounted,
    ref,
    watch,
} from 'vue'

import { useForm } from '@inertiajs/vue3'
import SearchableSelect from '@/Components/Form/SearchableSelect.vue'
import FlatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },

    settlement: {
        type: Object,
        default: null,
    },

    previewNumber: {
        type: String,
        default: '-',
    },

    branches: {
        type: Array,
        default: () => [],
    },
})

const emit = defineEmits([
    'close',
    'success',
])

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

const today = () => {
    const date = new Date()

    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')

    return `${year}-${month}-${day}`
}

const flatpickrConfig = {
    dateFormat: 'Y-m-d',
    allowInput: true,
    clickOpens: true,
}

const parseAmount = (value) => {
    if (
        value === null ||
        value === undefined ||
        value === ''
    ) {
        return 0
    }

    const amount = Number(value)

    return Number.isFinite(amount) ? amount : 0
}

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(parseAmount(value))
}

const formatAmount = (value) => {
    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(parseAmount(value))
}

const normalizeAmount = (value) => {
    let raw = String(value ?? '')
        .replace(/\s/g, '')
        .replace(/[^\d.,]/g, '')

    if (!raw) {
        return ''
    }

    if (raw.includes(',')) {
        raw = raw
            .replace(/\./g, '')
            .replace(',', '.')
    } else {
        const dots = raw.match(/\./g) || []

        if (dots.length > 1) {
            raw = raw.replace(/\./g, '')
        } else if (dots.length === 1) {
            const decimalPart = raw.split('.')[1]

            if (decimalPart.length === 3) {
                raw = raw.replace('.', '')
            }
        }
    }

    const parts = raw.split('.')

    if (parts.length > 2) {
        raw = `${parts.shift()}.${parts.join('')}`
    }

    return raw
}
/*
|--------------------------------------------------------------------------
| Accounts
|--------------------------------------------------------------------------
*/

const accounts = ref([])
const loadingAccounts = ref(false)
const accountError = ref('')
/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/

const form = useForm({
    settlement_date: props.settlement?.settlement_date ?? today(),

    from_branch_id: props.settlement?.from_branch_id ?? '',
    to_branch_id: props.settlement?.to_branch_id ?? '',

    from_account_id: props.settlement?.from_account_id ?? '',

    amount: parseAmount(props.settlement?.amount),

    description: props.settlement?.description ?? '',

    details: [],
})

const isEditing = computed(() => {
    return Boolean(props.settlement?.id)
})

const modalTitle = computed(() => {
    return isEditing.value
        ? 'Edit Inter-Branch Settlement'
        : 'Create Inter-Branch Settlement'
})

const documentNumber = computed(() => {
    return props.settlement?.settlement_number
        ?? props.previewNumber
        ?? '-'
})

/*
|--------------------------------------------------------------------------
| Eligible Fund Transfers
|--------------------------------------------------------------------------
*/

const eligibleTransfers = ref([])
const loadingTransfers = ref(false)
const transferError = ref('')

/*
|--------------------------------------------------------------------------
| Load Eligible Fund Transfers
|--------------------------------------------------------------------------
*/

const loadEligibleTransfers = async (
    preserveExistingDetails = Boolean(props.settlement?.id)
) => {
    loadingTransfers.value = true
    transferError.value = ''

    try {
        const fromBranchId = form.from_branch_id
        const toBranchId = form.to_branch_id

        /*
        |--------------------------------------------------------------------------
        | Validasi Cabang
        |--------------------------------------------------------------------------
        */

        if (
            !fromBranchId ||
            !toBranchId ||
            String(fromBranchId) === String(toBranchId)
        ) {
            eligibleTransfers.value = []

            if (!preserveExistingDetails) {
                form.details = []
                form.amount = 0
            }

            return
        }

        /*
        |--------------------------------------------------------------------------
        | Simpan Detail Existing Sebelum Memuat Ulang Data
        |--------------------------------------------------------------------------
        */

        const savedDetails = preserveExistingDetails
            ? (props.settlement?.details ?? [])
                .map((detail) => ({
                    fund_transfer_header_id:
                        detail.fund_transfer_header_id ??
                        detail.fund_transfer?.id ??
                        null,

                    amount: parseAmount(detail.amount),
                }))
                .filter((detail) => detail.fund_transfer_header_id)
            : []

        /*
        |--------------------------------------------------------------------------
        | Fetch Eligible Fund Transfers
        |--------------------------------------------------------------------------
        */

        const params = new URLSearchParams({
            from_branch_id: String(fromBranchId),
            to_branch_id: String(toBranchId),
        })

        const response = await fetch(
            `/accounting/inter-branch-settlements/eligible-transfers?${params.toString()}`,
            {
                headers: {
                    Accept: 'application/json',
                },
            }
        )

        if (!response.ok) {
            throw new Error(
                'Gagal memuat daftar Fund Transfer yang tersedia.'
            )
        }

        const result = await response.json()

        const transfers = Array.isArray(result)
            ? result
            : result.data ??
              result.transfers ??
              result.eligible_transfers ??
              []

        /*
        |--------------------------------------------------------------------------
        | Pertahankan Fund Transfer yang Sudah Tersimpan
        |--------------------------------------------------------------------------
        */

        if (preserveExistingDetails) {
            const availableTransferIds = new Set(
                transfers.map((transfer) => String(transfer.id))
            )

            const savedTransfers = (
                props.settlement?.details ?? []
            )
                .map((detail) => {
                    const transfer = detail.fund_transfer

                    if (!transfer?.id) {
                        return null
                    }

                    if (
                        availableTransferIds.has(
                            String(transfer.id)
                        )
                    ) {
                        return null
                    }

                    return transfer
                })
                .filter(Boolean)

            eligibleTransfers.value = [
                ...transfers,
                ...savedTransfers,
            ]

            form.details = savedDetails

            syncTotalAmount()
        } else {
            eligibleTransfers.value = transfers
            form.details = []
            form.amount = 0
        }
    } catch (exception) {
        console.error(
            'Error loading eligible fund transfers:',
            exception
        )

        /*
        |--------------------------------------------------------------------------
        | Jangan Hapus Detail Existing Ketika API Gagal
        |--------------------------------------------------------------------------
        */

        if (!preserveExistingDetails) {
            eligibleTransfers.value = []
            form.details = []
            form.amount = 0
        } else {
            // Pertahankan transfer yang sudah tersedia dari props.
            eligibleTransfers.value = (
                props.settlement?.details ?? []
            )
                .map((detail) => detail.fund_transfer)
                .filter((transfer) => transfer?.id)

            form.details = (
                props.settlement?.details ?? []
            )
                .map((detail) => ({
                    fund_transfer_header_id:
                        detail.fund_transfer_header_id ??
                        detail.fund_transfer?.id ??
                        null,

                    amount: parseAmount(detail.amount),
                }))
                .filter((detail) => detail.fund_transfer_header_id)

            syncTotalAmount()
        }

        transferError.value =
            exception.message ||
            'Terjadi kesalahan saat memuat Fund Transfer.'
    } finally {
        loadingTransfers.value = false
    }
}


/*
|--------------------------------------------------------------------------
| Initialize
|--------------------------------------------------------------------------
*/

const initializeForm = async () => {
    form.clearErrors()

    transferError.value = ''
    accountError.value = ''

    form.settlement_date =
        props.settlement?.settlement_date ?? today()

    form.from_branch_id =
        props.settlement?.from_branch_id ?? ''

    form.to_branch_id =
        props.settlement?.to_branch_id ?? ''

    form.from_account_id =
        props.settlement?.from_account_id ?? ''

    form.description =
        props.settlement?.description ?? ''

    form.details = (
        props.settlement?.details ?? []
    )
        .map((detail) => ({
            fund_transfer_header_id:
                detail.fund_transfer_header_id ??
                detail.fund_transfer?.id ??
                null,

            amount: parseAmount(detail.amount),
        }))
        .filter((detail) => detail.fund_transfer_header_id)

    form.amount = parseAmount(props.settlement?.amount)

    accounts.value = []
    eligibleTransfers.value = []

    await Promise.all([
        loadAccounts(),
        loadEligibleTransfers(
            Boolean(props.settlement?.id)
        ),
    ])
}

onMounted(() => {
    if (props.show) {
        initializeForm()
    }
})

watch(
    () => props.show,
    (show) => {
        if (show) {
            initializeForm()
        }
    }
)

/*
|--------------------------------------------------------------------------
| Selected Transfers
|--------------------------------------------------------------------------
*/

const selectedTransferIds = computed(() => {
    return form.details.map(
        (detail) => String(detail.fund_transfer_header_id)
    )
})

/*
|--------------------------------------------------------------------------
| Find Fund Transfer
|--------------------------------------------------------------------------
*/

const getTransfer = (transferId) => {
    if (transferId === null || transferId === undefined) {
        return undefined
    }

    return eligibleTransfers.value.find(
        (transfer) =>
            String(transfer.id) === String(transferId)
    )
}


/*
|--------------------------------------------------------------------------
| Check Selected Fund Transfer
|--------------------------------------------------------------------------
*/

const isTransferSelected = (transferId) => {
    if (transferId === null || transferId === undefined) {
        return false
    }

    return form.details.some(
        (detail) =>
            String(detail.fund_transfer_header_id) ===
            String(transferId)
    )
}
const getOutstandingAmount = (transfer) => {
    return parseAmount(
        transfer?.outstanding_amount
        ?? transfer?.remaining_amount
        ?? transfer?.remaining_balance
        ?? transfer?.balance_due
        ?? 0
    )
}

const selectedTransfers = computed(() => {
    return form.details.map((detail) => {
        const transfer = getTransfer(
            detail.fund_transfer_header_id
        )

        return {
            ...detail,
            transfer,
            outstanding_amount: getOutstandingAmount(transfer),
        }
    })
})

const totalAmount = computed(() => {
    return form.details.reduce(
        (total, detail) => total + parseAmount(detail.amount),
        0
    )
})

const toggleTransfer = (transfer) => {
    if (isTransferSelected(transfer.id)) {
        form.details = form.details.filter(
            (detail) =>
                String(detail.fund_transfer_header_id) !==
                String(transfer.id)
        )
    } else {
        form.details.push({
            fund_transfer_header_id: transfer.id,
            amount: getOutstandingAmount(transfer),
        })
    }

    syncTotalAmount()
}

const updateDetailAmount = (detail, value) => {
    const normalized = normalizeAmount(value)
    const amount = parseAmount(normalized)
    const outstanding = detail.outstanding_amount

    if (amount < 0 || amount > outstanding) {
        return
    }

    const target = form.details.find(
        (item) =>
            String(item.fund_transfer_header_id) ===
            String(detail.fund_transfer_header_id)
    )

    if (target) {
        target.amount = amount
        syncTotalAmount()
    }
}

const syncTotalAmount = () => {
    form.amount = Number(totalAmount.value.toFixed(2))
}

const loadAccounts = async () => {
    accounts.value = []
    accountError.value = ''

    if (!form.from_branch_id) {
        form.from_account_id = ''
        return
    }

    loadingAccounts.value = true

    try {
        const query = new URLSearchParams({
            branch_id: String(form.from_branch_id),
        })

        const url =
            `/accounting/inter-branch-settlements/accounts?${query.toString()}`

        console.log('[Settlement Accounts] Request URL:', url)

        const response = await fetch(url, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })

        console.log('[Settlement Accounts] HTTP Status:', response.status)

        const result = await response.json()

        console.log(
            '[Settlement Accounts] Response Data:',
            JSON.stringify(result, null, 2)
        )

        if (!response.ok) {
            throw new Error(
                result.message ?? 'Failed to load payment accounts.'
            )
        }

        accounts.value = Array.isArray(result)
            ? result
            : Array.isArray(result.accounts)
                ? result.accounts
                : Array.isArray(result.data)
                    ? result.data
                    : []

        const savedAccountExists = accounts.value.some(
            (account) =>
                String(account.id) === String(form.from_account_id)
        )

        if (!savedAccountExists) {
            form.from_account_id = ''
        }

        console.log(
            '[Settlement Accounts] Account Count:',
            accounts.value.length
        )
    } catch (error) {
        console.error('[Settlement Accounts] Error:', error)

        accounts.value = []
        form.from_account_id = ''
        accountError.value = error.message
    } finally {
        loadingAccounts.value = false
    }
}
const formatDateTime = (value) => {
    if (!value) {
        return '-'
    }

    // Tangani format ISO dari backend, termasuk microseconds.
    const date = new Date(value)

    if (Number.isNaN(date.getTime())) {
        return String(value)
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
        timeZone: 'Asia/Jakarta',
    }).format(date)
}
/*
|--------------------------------------------------------------------------
| Branch Changes
|--------------------------------------------------------------------------
*/

const handleBranchChange = async () => {
    form.from_account_id = ''
    form.details = []
    form.amount = 0

    await Promise.all([
        loadAccounts(),
        loadEligibleTransfers(false),
    ])
}

/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

const canSubmit = computed(() => {
    if (form.processing || loadingTransfers.value) {
        return false
    }

    if (!form.settlement_date) {
        return false
    }

    if (!form.from_branch_id || !form.to_branch_id) {
        return false
    }

    if (
        String(form.from_branch_id) ===
        String(form.to_branch_id)
    ) {
        return false
    }

    if (!form.from_account_id) {
        return false
    }

    if (form.details.length === 0) {
        return false
    }

    if (form.details.some((detail) => {
        const transfer = getTransfer(
            detail.fund_transfer_header_id
        )

        const amount = parseAmount(detail.amount)

        return (
            !transfer ||
            amount <= 0 ||
            amount > getOutstandingAmount(transfer)
        )
    })) {
        return false
    }

    return totalAmount.value > 0
})

const validationMessage = computed(() => {
    if (
        form.from_branch_id &&
        form.to_branch_id &&
        String(form.from_branch_id) === String(form.to_branch_id)
    ) {
        return 'Payer Branch and Recipient Branch must be different.'
    }

    if (form.details.length === 0) {
        return 'Select at least one eligible Fund Transfer.'
    }

    if (form.details.some((detail) => {
        const transfer = getTransfer(
            detail.fund_transfer_header_id
        )

        return (
            parseAmount(detail.amount) <= 0 ||
            parseAmount(detail.amount) > getOutstandingAmount(transfer)
        )
    })) {
        return 'Each settlement amount must be greater than zero and cannot exceed the outstanding balance.'
    }

    return ''
})

/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/

const submit = () => {
    if (!canSubmit.value) {
        return
    }

    const options = {
        preserveScroll: true,

        onSuccess: () => {
            emit('success')
        },

        onError: (errors) => {
            console.error('[Settlement] Validation Errors:', errors)
        },
    }

    if (isEditing.value) {
        form.put(
            `/accounting/inter-branch-settlements/${props.settlement.id}`,
            options
        )

        return
    }

    form.post(
        '/accounting/inter-branch-settlements',
        options
    )
}

const close = () => {
    if (form.processing) {
        return
    }

    emit('close')
}
</script>

<template>
    <Teleport to="body">
        <div
            v-if="show"
            class="fixed inset-0 z-[100] flex items-center justify-center overflow-x-hidden p-2 sm:p-4"
        >
            <!-- Backdrop -->
            <div
                class="absolute inset-0 bg-slate-950/50 backdrop-blur-[2px]"
                @click="close"
            ></div>

            <!-- Modal -->
            <div
                class="relative flex max-h-[94dvh] w-full min-w-0 max-w-5xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl"
            >
                <!-- Header -->
                <div class="shrink-0 border-b border-slate-200 bg-white px-6 py-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="mb-1 flex items-center gap-2">
                                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
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
                                            d="M17 8l4 4-4 4M3 12h18M7 16l-4-4 4-4"
                                        />
                                    </svg>
                                </div>

                                <h2 class="text-lg font-semibold text-slate-900">
                                    {{ modalTitle }}
                                </h2>
                            </div>

                            <p class="text-sm text-slate-500">
                                Settle outstanding inter-branch receivables and payables.
                            </p>

                            <p class="mt-2 text-xs font-medium text-slate-400">
                                Settlement Number:
                                <span class="font-semibold text-slate-700">
                                    {{ documentNumber }}
                                </span>
                            </p>
                        </div>

                        <button
                            type="button"
                            class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                            :disabled="form.processing"
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
                                    d="M6 18 18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Body -->
             <div class="min-h-0 min-w-0 flex-1 overflow-x-hidden overflow-y-auto">
                    <div class="min-w-0 space-y-6 p-3 sm:p-6">
                        <!-- Settlement Information -->
                        <section>
                        <!-- Section Header -->
                        <div class="mb-4">
                            <h3 class="text-sm font-semibold text-slate-900">
                                Settlement Information
                            </h3>

                            <p class="mt-0.5 text-xs text-slate-500">
                                Enter the payment details and select the branches involved in this settlement.
                            </p>
                        </div>

                        <div class="space-y-5">
                            <!-- Row 1: Settlement Date & Payment Account -->
                            <div class="grid grid-cols-1 gap-4 sm:gap-5 lg:grid-cols-2">
                                <!-- Settlement Date -->
                                <div>
                                    <label
                                        class="mb-1.5 block text-sm font-medium text-slate-700"
                                    >
                                        Settlement Date
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <FlatPickr
                                        v-model="form.settlement_date"
                                        :config="flatpickrConfig"
                                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-emerald-500 focus:ring-emerald-500/10"
                                        placeholder="Select settlement date"
                                        :disabled="form.processing"
                                    />

                                    <p
                                        v-if="form.errors.settlement_date"
                                        class="mt-1 text-xs text-red-600"
                                    >
                                        {{ form.errors.settlement_date }}
                                    </p>
                                </div>

                                <!-- Payment Account -->
                                <div>
                                    <label
                                        class="mb-1.5 block text-sm font-medium text-slate-700"
                                    >
                                        Payment Account
                                        <span class="text-red-500">*</span>
                                    </label>

                                   <SearchableSelect
                                        v-model="form.from_account_id"
                                        :options="accounts ?? []"
                                        :get-label="account =>
                                            `${account.code} - ${account.name}`"
                                        :get-value="account => account.id"
                                        placeholder="Select payment account"
                                        :disabled="
                                            form.processing ||
                                            loadingAccounts ||
                                            !form.from_branch_id
                                        "
                                        :loading="loadingAccounts
                                    "
                                    />

                                    <p
                                        v-if="form.errors.from_account_id"
                                        class="mt-1 text-xs text-red-600"
                                    >
                                        {{ form.errors.from_account_id }}
                                    </p>

                                    <p
                                        v-if="accountError"
                                        class="mt-1 text-xs text-red-600"
                                    >
                                        {{ accountError }}
                                    </p>
                                </div>
                            </div>

                            <!-- Row 2: Payer Branch & Recipient Branch -->
                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                <!-- Payer Branch -->
                                <div>
                                    <label
                                        class="mb-1.5 block text-sm font-medium text-slate-700"
                                    >
                                        Payer Branch
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <SearchableSelect
                                        v-model="form.from_branch_id"
                                        :options="branches ?? []"
                                        :get-label="branch =>
                                            branch.code
                                                ? `${branch.code} - ${branch.name}`
                                                : branch.name"
                                        :get-value="branch => branch.id"
                                        placeholder="Select payer branch"
                                        :disabled="form.processing || isEditing"
                                        @update:model-value="handleBranchChange"
                                    />

                                    <p
                                        v-if="form.errors.from_branch_id"
                                        class="mt-1 text-xs text-red-600"
                                    >
                                        {{ form.errors.from_branch_id }}
                                    </p>
                                </div>

                                <!-- Recipient Branch -->
                                <div>
                                    <label
                                        class="mb-1.5 block text-sm font-medium text-slate-700"
                                    >
                                        Recipient Branch
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <SearchableSelect
                                        v-model="form.to_branch_id"
                                        :options="branches ?? []"
                                        :get-label="branch =>
                                            branch.code
                                                ? `${branch.code} - ${branch.name}`
                                                : branch.name"
                                        :get-value="branch => branch.id"
                                        placeholder="Select recipient branch"
                                        :disabled="form.processing || isEditing"
                                        @update:model-value="handleBranchChange"
                                    />

                                    <p
                                        v-if="form.errors.to_branch_id"
                                        class="mt-1 text-xs text-red-600"
                                    >
                                        {{ form.errors.to_branch_id }}
                                    </p>
                                </div>
                            </div>

                            <!-- Row 3: Description Full Width -->
                            <div>
                                <label
                                    class="mb-1.5 block text-sm font-medium text-slate-700"
                                >
                                    Description
                                </label>

                                <textarea
                                    v-model="form.description"
                                    rows="3"
                                    class="w-full resize-y rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500/10"
                                    placeholder="Enter settlement description or payment reference..."
                                    :disabled="form.processing"
                                ></textarea>

                                <p
                                    v-if="form.errors.description"
                                    class="mt-1 text-xs text-red-600"
                                >
                                    {{ form.errors.description }}
                                </p>
                            </div>
                        </div>
                    </section>

                        <!-- Fund Transfer Selection -->
                        <section>
                            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                                <div>
                                    <h3 class="text-sm font-semibold text-slate-900">
                                        Fund Transfers to Settle
                                    </h3>

                                    <p class="mt-0.5 text-xs text-slate-500">
                                        Select one or more eligible transfers between these branches.
                                    </p>
                                </div>

                                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                                    {{ form.details.length }} selected
                                </span>
                            </div>

                            <div
                                v-if="transferError"
                                class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                            >
                                {{ transferError }}
                            </div>

                            <div
                                v-if="!form.from_branch_id || !form.to_branch_id"
                                class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-10 text-center"
                            >
                                <p class="text-sm font-medium text-slate-700">
                                    Select both branches first
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    Eligible Fund Transfers will appear here.
                                </p>
                            </div>

                            <div
                                v-else-if="loadingTransfers"
                                class="rounded-xl border border-slate-200 px-4 py-10 text-center"
                            >
                                <p class="text-sm text-slate-500">
                                    Loading eligible Fund Transfers...
                                </p>
                            </div>

                            <div
                                v-else-if="eligibleTransfers.length === 0"
                                class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-10 text-center"
                            >
                                <p class="text-sm font-medium text-slate-700">
                                    No eligible Fund Transfers
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    Only receivable-mode transfers with an outstanding balance can be settled.
                                </p>
                            </div>

                            <div
                                v-else
                                class="overflow-hidden rounded-xl border border-slate-200"
                            >
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-slate-200">
                                        <thead class="bg-slate-50">
                                            <tr>
                                                <th class="w-12 px-4 py-3 text-center">
                                                    <span class="sr-only">Select</span>
                                                </th>

                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                    Fund Transfer
                                                </th>

                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                    Date
                                                </th>

                                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                    Outstanding
                                                </th>
                                            </tr>
                                        </thead>

                                        <tbody class="divide-y divide-slate-100 bg-white">
                                            <tr
                                                v-for="transfer in eligibleTransfers"
                                                :key="transfer.id"
                                                class="transition hover:bg-slate-50"
                                                :class="{
                                                    'bg-emerald-50/50': isTransferSelected(transfer.id),
                                                }"
                                            >
                                                <td class="px-4 py-3 text-center">
                                                    <input
                                                        type="checkbox"
                                                        :checked="isTransferSelected(transfer.id)"
                                                        :disabled="form.processing"
                                                        class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                                                        @change="toggleTransfer(transfer)"
                                                    />
                                                </td>

                                                <!-- Fund Transfer -->
                                                <td class="px-4 py-3">
                                                    <p class="text-sm font-semibold text-slate-800">
                                                        {{
                                                            transfer.code
                                                            ?? transfer.transfer_number
                                                            ?? transfer.fund_transfer_number
                                                            ?? '-'
                                                        }}
                                                    </p>

                                                    <p class="mt-0.5 text-xs text-slate-500">
                                                        {{ transfer.description || 'No description' }}
                                                    </p>
                                                </td>

                                                <!-- Date -->
                                                <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">
                                                    {{ formatDateTime(transfer.transfer_date) }}
                                                </td>

                                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-semibold tabular-nums text-slate-800">
                                                    {{ formatCurrency(getOutstandingAmount(transfer)) }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <p
                                v-if="form.errors.details"
                                class="mt-2 text-xs text-red-600"
                            >
                                {{ form.errors.details }}
                            </p>
                        </section>

                        <!-- Selected Transfer Amounts -->
                        <section v-if="selectedTransfers.length">
                            <div class="mb-3">
                                <h3 class="text-sm font-semibold text-slate-900">
                                    Settlement Amounts
                                </h3>

                                <p class="mt-0.5 text-xs text-slate-500">
                                    Enter the amount to settle for each selected Fund Transfer.
                                </p>
                            </div>

                            <div class="overflow-hidden rounded-xl border border-slate-200">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-slate-200">
                                        <thead class="bg-slate-50">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                    Fund Transfer
                                                </th>

                                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                    Outstanding
                                                </th>

                                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                    Settlement Amount
                                                </th>

                                                <th class="w-12 px-4 py-3"></th>
                                            </tr>
                                        </thead>

                                        <tbody class="divide-y divide-slate-100 bg-white">
                                            <tr
                                                v-for="detail in selectedTransfers"
                                                :key="detail.fund_transfer_header_id"
                                            >
                                                <td class="px-4 py-3">
                                                    <p class="text-sm font-semibold text-slate-800">
                                                        {{
                                                            detail.transfer?.code
                                                            ?? detail.transfer?.transfer_number
                                                            ?? detail.transfer?.fund_transfer_number
                                                            ?? '-'
                                                        }}
                                                    </p>
                                                </td>

                                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm tabular-nums text-slate-600">
                                                    {{ formatCurrency(detail.outstanding_amount) }}
                                                </td>

                                                <td class="px-4 py-3">
                                                    <input
                                                        :value="formatAmount(detail.amount)"
                                                        type="text"
                                                        inputmode="decimal"
                                                        class="w-44 rounded-lg border border-slate-300 px-3 py-2 text-right text-sm tabular-nums text-slate-800 outline-none focus:border-emerald-500"
                                                        :disabled="form.processing"
                                                        @change="updateDetailAmount(
                                                            detail,
                                                            $event.target.value
                                                        )"
                                                    />
                                                </td>

                                                <td class="px-4 py-3 text-center">
                                                    <button
                                                        type="button"
                                                        class="rounded-lg p-2 text-slate-400 transition hover:bg-red-50 hover:text-red-600"
                                                        title="Remove transfer"
                                                        :disabled="form.processing"
                                                        @click="toggleTransfer({
                                                            id: detail.fund_transfer_header_id,
                                                        })"
                                                    >
                                                        <svg
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            class="h-4 w-4"
                                                            viewBox="0 0 24 24"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            stroke-width="2"
                                                        >
                                                            <path
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                d="M6 18 18 6M6 6l12 12"
                                                            />
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <p
                                v-if="form.errors.amount"
                                class="mt-2 text-xs text-red-600"
                            >
                                {{ form.errors.amount }}
                            </p>
                        </section>

                        <!-- Total -->
                        <section>
                            <div class="rounded-xl border border-emerald-200 bg-emerald-50/70 p-5">
                                <div class="flex flex-wrap items-center justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-medium text-slate-600">
                                            Total Settlement
                                        </p>

                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ form.details.length }} Fund Transfer selected
                                        </p>
                                    </div>

                                    <p class="text-2xl font-bold tabular-nums text-emerald-800">
                                        {{ formatCurrency(totalAmount) }}
                                    </p>
                                </div>
                            </div>

                            <div
                                v-if="validationMessage"
                                class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800"
                            >
                                {{ validationMessage }}
                            </div>
                        </section>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex shrink-0 items-center justify-between gap-3 border-t border-slate-200 bg-slate-50/70 px-6 py-4">
                    <p class="hidden text-xs text-slate-500 sm:block">
                        Save as draft before submitting for approval.
                    </p>

                    <div class="ml-auto flex items-center gap-3">
                        <button
                            type="button"
                            class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="form.processing"
                            @click="close"
                        >
                            Cancel
                        </button>

                        <button
                            type="button"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="form.processing"                            @click="submit"
                        >
                            <svg
                                v-if="form.processing"
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 animate-spin"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 3v3m6.36-.36-2.12 2.12M21 12h-3m.36 6.36-2.12-2.12M12 21v-3m-6.36.36 2.12-2.12M3 12h3m-.36-6.36 2.12 2.12"
                                />
                            </svg>

                            <span>
                                {{
                                    form.processing
                                        ? 'Saving...'
                                        : isEditing
                                            ? 'Save Changes'
                                            : 'Save Draft'
                                }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>