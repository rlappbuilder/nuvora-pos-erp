<script setup>
import {
    computed,
    onMounted,
    ref,
    watch,
} from 'vue'

import { useForm } from '@inertiajs/vue3'

import FlatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },

    transfer: {
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

const getTransferType = (transfer) => {
    if (!transfer) {
        return 'inter_branch'
    }

    if (
        transfer.to_branch_id === null ||
        transfer.to_branch_id === undefined
    ) {
        return 'branch_to_company'
    }

    if (
        String(transfer.from_branch_id) ===
        String(transfer.to_branch_id)
    ) {
        return 'same_branch'
    }

    return 'inter_branch'
}

/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/

const form = useForm({
    transfer_date: props.transfer?.transfer_date ?? today(),

    from_branch_id: props.transfer?.from_branch_id ?? '',
    from_account_id: props.transfer?.from_account_id ?? '',

    to_branch_id: props.transfer?.to_branch_id ?? '',
    to_account_id: props.transfer?.to_account_id ?? '',

    transfer_mode: props.transfer?.transfer_mode ?? 'receivable',

    amount: Number(props.transfer?.amount ?? 0),

    description: props.transfer?.description ?? '',
})
const transferModeOptions = [
    {
        value: 'receivable',
        label: 'Receivable — Piutang/Utang Antar-Cabang',
    },
    {
        value: 'allocation',
        label: 'Allocation — Alokasi Antar-Cabang',
    },
]
const transferType = ref(
    getTransferType(props.transfer)
)

const sourceAccounts = ref([])
const destinationAccounts = ref([])

const loadingSourceAccounts = ref(false)
const loadingDestinationAccounts = ref(false)

const accountError = ref('')

const isEditing = computed(() => {
    return Boolean(props.transfer?.id)
})

const modalTitle = computed(() => {
    return isEditing.value
        ? 'Edit Fund Transfer'
        : 'Create Fund Transfer'
})

const documentNumber = computed(() => {
    return props.transfer?.code
        ?? props.previewNumber
        ?? '-'
})

const isCompanyDestination = computed(() => {
    return transferType.value === 'branch_to_company'
})

const isSameBranch = computed(() => {
    return transferType.value === 'same_branch'
})

const transferTypeOptions = [
    {
        value: 'inter_branch',
        label: 'Antar Cabang',
    },
    {
        value: 'same_branch',
        label: 'Dalam Cabang yang Sama',
    },
    {
        value: 'branch_to_company',
        label: 'Cabang ke Company',
    },
]

/*
|--------------------------------------------------------------------------
| Amount
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Amount
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Amount
|--------------------------------------------------------------------------
*/

const amountInput = ref('')
const amountFocused = ref(false)

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

const transferAmount = computed(() => {
    const amount = parseAmount(form.amount)

    return amount > 0 ? amount : 0
})

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(parseAmount(value))
}

const formatAmount = (value) => {
    if (
        value === null ||
        value === undefined ||
        value === ''
    ) {
        return ''
    }

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

const handleAmountFocus = () => {
    amountFocused.value = true
    amountInput.value = ''
    form.amount = 0
}

const handleAmountInput = (event) => {
    const raw = normalizeAmount(event.target.value)

    amountInput.value = raw

    if (raw === '' || raw === '.') {
        form.amount = 0
        return
    }

    const amount = Number(raw)

    if (Number.isFinite(amount)) {
        form.amount = amount
    }
}

const handleAmountBlur = () => {
    amountFocused.value = false

    amountInput.value = formatAmount(form.amount)
}

/*
|--------------------------------------------------------------------------
| Branch Helpers
|--------------------------------------------------------------------------
*/

const getBranchName = (branchId) => {
    const branch = props.branches.find(
        (item) => String(item.id) === String(branchId)
    )

    return branch?.name ?? '-'
}

const branchOptions = computed(() => {
    return props.branches
})

/*
|--------------------------------------------------------------------------
| Account Mapping
|--------------------------------------------------------------------------
|
| Branch account:
|   context=branch
|   branch_id={branch_id}
|
| Company account:
|   context=company
|
| The endpoint returns mapped accounts only.
|
*/

const fetchAccounts = async (context, branchId = null) => {
    const query = new URLSearchParams({
        context,
    })

    if (context === 'branch' && branchId) {
        query.set('branch_id', branchId)
    }

    const response = await fetch(
        `${route('fund-transfers.accounts')}?${query.toString()}`,
        {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        }
    )

    if (!response.ok) {
        throw new Error('Failed to load transfer accounts.')
    }

    const result = await response.json()

    if (Array.isArray(result)) {
        return result
    }

    if (Array.isArray(result.accounts)) {
        return result.accounts
    }

    if (Array.isArray(result.data)) {
        return result.data
    }

    return []
}

const loadSourceAccounts = async ({
    preserveSelection = false,
} = {}) => {
    const branchId = form.from_branch_id

    if (!branchId) {
        sourceAccounts.value = []
        form.from_account_id = ''

        return
    }

    const previousAccountId = preserveSelection
        ? form.from_account_id
        : ''

    loadingSourceAccounts.value = true

    try {
        const accounts = await fetchAccounts(
            'branch',
            branchId
        )

        sourceAccounts.value = accounts

        const accountExists = accounts.some(
            (account) =>
                String(account.id) ===
                String(previousAccountId)
        )

        form.from_account_id = accountExists
            ? previousAccountId
            : ''
    } catch (error) {
        sourceAccounts.value = []
        form.from_account_id = ''
        accountError.value = error.message
    } finally {
        loadingSourceAccounts.value = false
    }
}

const loadDestinationAccounts = async ({
    preserveSelection = false,
} = {}) => {
    const previousAccountId = preserveSelection
        ? form.to_account_id
        : ''

    if (isCompanyDestination.value) {
        loadingDestinationAccounts.value = true

        try {
            const accounts = await fetchAccounts('company')

            destinationAccounts.value = accounts

            const accountExists = accounts.some(
                (account) =>
                    String(account.id) ===
                    String(previousAccountId)
            )

            form.to_account_id = accountExists
                ? previousAccountId
                : ''
        } catch (error) {
            destinationAccounts.value = []
            form.to_account_id = ''
            accountError.value = error.message
        } finally {
            loadingDestinationAccounts.value = false
        }

        return
    }

    const branchId = form.to_branch_id

    if (!branchId) {
        destinationAccounts.value = []
        form.to_account_id = ''

        return
    }

    loadingDestinationAccounts.value = true

    try {
        const accounts = await fetchAccounts(
            'branch',
            branchId
        )

        destinationAccounts.value = accounts

        const accountExists = accounts.some(
            (account) =>
                String(account.id) ===
                String(previousAccountId)
        )

        form.to_account_id = accountExists
            ? previousAccountId
            : ''
    } catch (error) {
        destinationAccounts.value = []
        form.to_account_id = ''
        accountError.value = error.message
    } finally {
        loadingDestinationAccounts.value = false
    }
}

/*
|--------------------------------------------------------------------------
| Transfer Type
|--------------------------------------------------------------------------
*/

const changeTransferType = async () => {
    accountError.value = ''

    form.from_branch_id = ''
    form.from_account_id = ''

    form.to_branch_id = ''
    form.to_account_id = ''

    sourceAccounts.value = []
    destinationAccounts.value = []

    if (isCompanyDestination.value) {
        form.to_branch_id = ''
    }

    await loadSourceAccounts()
    await loadDestinationAccounts()
}

const handleSourceBranchChange = async () => {
    accountError.value = ''

    form.from_account_id = ''
    sourceAccounts.value = []

    if (isSameBranch.value) {
        form.to_branch_id = form.from_branch_id
        form.to_account_id = ''
        destinationAccounts.value = []
    }

    if (transferType.value === 'inter_branch') {
        if (
            form.from_branch_id &&
            String(form.from_branch_id) ===
                String(form.to_branch_id)
        ) {
            form.to_branch_id = ''
            form.to_account_id = ''
            destinationAccounts.value = []
        }
    }

    await loadSourceAccounts()
    await loadDestinationAccounts()
}

const handleDestinationBranchChange = async () => {
    accountError.value = ''

    form.to_account_id = ''
    destinationAccounts.value = []

    await loadDestinationAccounts()
}

/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

const canSubmit = computed(() => {
    if (form.processing) {
        return false
    }

    if (!form.transfer_date) {
        return false
    }

    if (!form.from_branch_id) {
        return false
    }

    if (!form.from_account_id) {
        return false
    }

    if (!form.to_account_id) {
        return false
    }

    if (transferAmount.value <= 0) {
        return false
    }

    if (transferType.value === 'inter_branch') {
        if (!form.to_branch_id) {
            return false
        }

        if (
            String(form.from_branch_id) ===
            String(form.to_branch_id)
        ) {
            return false
        }
    }

    if (transferType.value === 'same_branch') {
        if (
            String(form.from_branch_id) !==
            String(form.to_branch_id)
        ) {
            return false
        }
    }

    if (
        form.from_account_id &&
        form.to_account_id &&
        String(form.from_account_id) ===
            String(form.to_account_id)
    ) {
        return false
    }

    return true
})

const validationMessage = computed(() => {
    if (
        form.from_account_id &&
        form.to_account_id &&
        String(form.from_account_id) ===
            String(form.to_account_id)
    ) {
        return 'Source and destination accounts must be different.'
    }

    if (
        transferType.value === 'inter_branch' &&
        form.from_branch_id &&
        form.to_branch_id &&
        String(form.from_branch_id) ===
            String(form.to_branch_id)
    ) {
        return 'Source and destination branches must be different.'
    }

    return ''
})

/*
|--------------------------------------------------------------------------
| Reset / Initialize
|--------------------------------------------------------------------------
*/

const initializeForm = async () => {
    accountError.value = ''

    form.clearErrors()

    transferType.value = getTransferType(props.transfer)

    form.transfer_date =
        props.transfer?.transfer_date ?? today()

    form.from_branch_id =
        props.transfer?.from_branch_id ?? ''

    form.from_account_id =
        props.transfer?.from_account_id ?? ''

    form.to_branch_id =
        props.transfer?.to_branch_id ?? ''

    form.to_account_id =
        props.transfer?.to_account_id ?? ''

    form.transfer_mode =
        props.transfer?.transfer_mode ?? 'receivable'

    form.amount = Number(
    props.transfer?.amount ?? 0
    )

    amountInput.value = formatAmount(form.amount)
    amountFocused.value = false

    form.description =
        props.transfer?.description ?? ''

    sourceAccounts.value = []
    destinationAccounts.value = []

    await loadSourceAccounts({
        preserveSelection: isEditing.value,
    })

    await loadDestinationAccounts({
        preserveSelection: isEditing.value,
    })
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
| Submit
|--------------------------------------------------------------------------
*/

const submit = () => {
    if (!canSubmit.value) {
        return
    }

    accountError.value = ''

    const options = {
        preserveScroll: true,

        onSuccess: () => {
            emit('success')
        },
    }

    if (isEditing.value) {
        form.put(
            route(
                'fund-transfers.update',
                props.transfer.id
            ),
            options
        )

        return
    }

    form.post(
        route('fund-transfers.store'),
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
            class="fixed inset-0 z-[100] flex items-center justify-center p-4"
        >
            <!-- Backdrop -->
            <div
                class="absolute inset-0 bg-slate-950/50 backdrop-blur-[2px]"
                @click="close"
            ></div>

            <!-- Modal -->
            <div
                class="relative w-full max-w-4xl overflow-hidden rounded-2xl bg-white shadow-2xl"
            >
                <!-- Header -->
                <div
                    class="border-b border-slate-200 bg-white px-6 py-5"
                >
                    <div
                        class="flex items-start justify-between gap-4"
                    >
                        <div>
                            <div
                                class="mb-1 flex items-center gap-2"
                            >
                                <div
                                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"
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
                                            d="M12 8v8m-4-4h8M4 6h16M4 18h16"
                                        />
                                    </svg>
                                </div>

                                <h2
                                    class="text-lg font-semibold text-slate-900"
                                >
                                    {{ modalTitle }}
                                </h2>
                            </div>

                            <p class="text-sm text-slate-500">
                                Manage internal fund transfers between branches and company accounts.
                            </p>

                            <p
                                class="mt-2 text-xs font-medium text-slate-400"
                            >
                                Transfer Number:
                                <span
                                    class="font-semibold text-slate-700"
                                >
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
                <div
                    class="max-h-[calc(100vh-220px)] overflow-y-auto"
                >
                    <div class="space-y-6 p-6">

                        <!-- Transfer Information -->
                        <section>
                            <div class="mb-3">
                                <h3
                                    class="text-sm font-semibold text-slate-900"
                                >
                                    Transfer Information
                                </h3>

                                <p
                                    class="mt-0.5 text-xs text-slate-500"
                                >
                                    Enter the main information for this transfer.
                                </p>
                            </div>

                            <div
                                class="grid grid-cols-1 gap-5 md:grid-cols-2"
                            >
                                <!-- Transfer Date -->
                                <div>
                                    <label
                                        class="mb-1.5 block text-sm font-medium text-slate-700"
                                    >
                                        Transfer Date
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <FlatPickr
                                        v-model="form.transfer_date"
                                        :config="flatpickrConfig"
                                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-emerald-500 focus:ring-emerald-500/10"
                                        placeholder="Select transfer date"
                                        :disabled="form.processing"
                                    />

                                    <p
                                        v-if="form.errors.transfer_date"
                                        class="mt-1 text-xs text-red-600"
                                    >
                                        {{ form.errors.transfer_date }}
                                    </p>
                                </div>

                                <!-- Transfer Type -->
                                <div>
                                    <label
                                        class="mb-1.5 block text-sm font-medium text-slate-700"
                                    >
                                        Transfer Type
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <select
                                        v-model="transferType"
                                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-emerald-500 focus:ring-emerald-500/10"
                                        :disabled="form.processing"
                                        @change="changeTransferType"
                                    >
                                        <option
                                            v-for="option in transferTypeOptions"
                                            :key="option.value"
                                            :value="option.value"
                                        >
                                            {{ option.label }}
                                        </option>
                                    </select>
                                    <!-- Transfer Mode -->

                                </div>
<!-- Transfer Mode -->
<div>
    <label
        class="mb-1.5 block text-sm font-medium text-slate-700"
    >
        Transfer Mode
        <span class="text-red-500">*</span>
    </label>

    <select
        v-model="form.transfer_mode"
        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-emerald-500 focus:ring-emerald-500/10"
        :disabled="form.processing"
    >
        <option
            v-for="option in transferModeOptions"
            :key="option.value"
            :value="option.value"
        >
            {{ option.label }}
        </option>
    </select>

    <p
        v-if="form.errors.transfer_mode"
        class="mt-1 text-xs text-red-600"
    >
        {{ form.errors.transfer_mode }}
    </p>

    <p class="mt-1 text-xs text-slate-500">
        Receivable digunakan untuk transaksi yang menimbulkan
        kewajiban antar-cabang. Allocation digunakan untuk
        alokasi internal tanpa kewajiban pembayaran kembali.
    </p>
</div>

<!-- Description: di samping Transfer Mode -->
<div>
    <label
        class="mb-1.5 block text-sm font-medium text-slate-700"
    >
        Description
    </label>

    <textarea
        v-model="form.description"
        rows="2"
        class="w-full resize-y rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500/10"
        placeholder="Optional transfer description"
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

                        <!-- Transfer Accounts -->
                        <section>
                            <div class="mb-3">
                                <h3
                                    class="text-sm font-semibold text-slate-900"
                                >
                                    Transfer Accounts
                                </h3>

                                <p
                                    class="mt-0.5 text-xs text-slate-500"
                                >
                                    Select the source and destination accounts.
                                </p>
                            </div>

                            <div
                                class="grid grid-cols-1 gap-4 md:grid-cols-2"
                            >
                                <!-- From -->
                                <div
                                    class="rounded-xl border border-slate-200 bg-slate-50/70 p-4"
                                >
                                    <div
                                        class="mb-4 flex items-center gap-2"
                                    >
                                        <div
                                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"
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
                                                    d="M19 12H5m7 7-7-7 7-7"
                                                />
                                            </svg>
                                        </div>

                                        <div>
                                            <h4
                                                class="text-sm font-semibold text-slate-900"
                                            >
                                                From
                                            </h4>

                                            <p
                                                class="text-xs text-slate-500"
                                            >
                                                Source of funds
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Source Branch -->
                                    <div>
                                        <label
                                            class="mb-1.5 block text-sm font-medium text-slate-700"
                                        >
                                            Source Branch
                                            <span class="text-red-500">*</span>
                                        </label>

                                        <select
                                            v-model="form.from_branch_id"
                                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-emerald-500 focus:ring-emerald-500/10"
                                            :disabled="form.processing"
                                            @change="handleSourceBranchChange"
                                        >
                                            <option value="">
                                                Select source branch
                                            </option>

                                            <option
                                                v-for="branch in branchOptions"
                                                :key="branch.id"
                                                :value="branch.id"
                                            >
                                                {{ branch.name }}
                                            </option>
                                        </select>

                                        <p
                                            v-if="form.errors.from_branch_id"
                                            class="mt-1 text-xs text-red-600"
                                        >
                                            {{ form.errors.from_branch_id }}
                                        </p>
                                    </div>

                                    <!-- Source Account -->
                                    <div class="mt-4">
                                        <label
                                            class="mb-1.5 block text-sm font-medium text-slate-700"
                                        >
                                            Source Account
                                            <span class="text-red-500">*</span>
                                        </label>

                                        <select
                                            v-model="form.from_account_id"
                                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-emerald-500 focus:ring-emerald-500/10"
                                            :disabled="
                                                form.processing ||
                                                !form.from_branch_id ||
                                                loadingSourceAccounts
                                            "
                                        >
                                            <option value="">
                                                {{
                                                    loadingSourceAccounts
                                                        ? 'Loading accounts...'
                                                        : 'Select source account'
                                                }}
                                            </option>

                                            <option
                                                v-for="account in sourceAccounts"
                                                :key="account.id"
                                                :value="account.id"
                                            >
                                                {{ account.code }} -
                                                {{ account.name }}
                                            </option>
                                        </select>

                                        <p
                                            v-if="form.errors.from_account_id"
                                            class="mt-1 text-xs text-red-600"
                                        >
                                            {{ form.errors.from_account_id }}
                                        </p>
                                    </div>
                                </div>

                                <!-- To -->
                                <div
                                    class="rounded-xl border border-slate-200 bg-slate-50/70 p-4"
                                >
                                    <div
                                        class="mb-4 flex items-center gap-2"
                                    >
                                        <div
                                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600"
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
                                                    d="M5 12h14m-7-7 7 7-7 7"
                                                />
                                            </svg>
                                        </div>

                                        <div>
                                            <h4
                                                class="text-sm font-semibold text-slate-900"
                                            >
                                                To
                                            </h4>

                                            <p
                                                class="text-xs text-slate-500"
                                            >
                                                Destination of funds
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Destination Branch -->
                                    <div>
                                        <label
                                            class="mb-1.5 block text-sm font-medium text-slate-700"
                                        >
                                            Destination
                                            <span class="text-red-500">*</span>
                                        </label>

                                        <!-- Company destination -->
                                        <div
                                            v-if="isCompanyDestination"
                                            class="flex min-h-[42px] items-center rounded-lg border border-slate-200 bg-slate-100 px-3 py-2.5 text-sm font-medium text-slate-700"
                                        >
                                            Company
                                        </div>

                                        <!-- Same branch -->
                                        <div
                                            v-else-if="isSameBranch"
                                            class="flex min-h-[42px] items-center rounded-lg border border-slate-200 bg-slate-100 px-3 py-2.5 text-sm font-medium text-slate-700"
                                        >
                                            {{
                                                getBranchName(
                                                    form.from_branch_id
                                                )
                                            }}
                                        </div>

                                        <!-- Inter-branch -->
                                        <select
                                            v-else
                                            v-model="form.to_branch_id"
                                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-emerald-500 focus:ring-emerald-500/10"
                                            :disabled="
                                                form.processing ||
                                                !form.from_branch_id
                                            "
                                            @change="handleDestinationBranchChange"
                                        >
                                            <option value="">
                                                Select destination branch
                                            </option>

                                            <option
                                                v-for="branch in branchOptions.filter(
                                                    (item) =>
                                                        String(item.id) !==
                                                        String(form.from_branch_id)
                                                )"
                                                :key="branch.id"
                                                :value="branch.id"
                                            >
                                                {{ branch.name }}
                                            </option>
                                        </select>

                                        <p
                                            v-if="form.errors.to_branch_id"
                                            class="mt-1 text-xs text-red-600"
                                        >
                                            {{ form.errors.to_branch_id }}
                                        </p>
                                    </div>

                                    <!-- Destination Account -->
                                    <div class="mt-4">
                                        <label
                                            class="mb-1.5 block text-sm font-medium text-slate-700"
                                        >
                                            {{
                                                isCompanyDestination
                                                    ? 'Company Cash Account'
                                                    : 'Destination Account'
                                            }}

                                            <span class="text-red-500">*</span>
                                        </label>

                                        <select
                                            v-model="form.to_account_id"
                                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-emerald-500 focus:ring-emerald-500/10"
                                            :disabled="
                                                form.processing ||
                                                loadingDestinationAccounts ||
                                                (
                                                    !isCompanyDestination &&
                                                    !form.to_branch_id
                                                )
                                            "
                                        >
                                            <option value="">
                                                {{
                                                    loadingDestinationAccounts
                                                        ? 'Loading accounts...'
                                                        : 'Select destination account'
                                                }}
                                            </option>

                                            <option
                                                v-for="account in destinationAccounts"
                                                :key="account.id"
                                                :value="account.id"
                                            >
                                                {{ account.code }} -
                                                {{ account.name }}
                                            </option>
                                        </select>

                                        <p
                                            v-if="form.errors.to_account_id"
                                            class="mt-1 text-xs text-red-600"
                                        >
                                            {{ form.errors.to_account_id }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Account Error -->
                            <div
                                v-if="accountError"
                                class="mt-4 flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="mt-0.5 h-4 w-4 shrink-0"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 9v4m0 4h.01M10.29 3.86l-8.24 14A2 2 0 003.78 21h16.44a2 2 0 001.73-3.14l-8.24-14a2 2 0 00-3.42 0z"
                                    />
                                </svg>

                                <span>{{ accountError }}</span>
                            </div>
                        </section>

                        <!-- Transfer Amount -->
                        <section>
                            <div class="mb-3">
                                <h3
                                    class="text-sm font-semibold text-slate-900"
                                >
                                    Transfer Amount
                                </h3>

                                <p
                                    class="mt-0.5 text-xs text-slate-500"
                                >
                                    Enter the amount to transfer.
                                </p>
                            </div>

                            <div
                                class="rounded-xl border border-emerald-200 bg-emerald-50/70 p-5"
                            >
                                <label
                                    class="mb-1.5 block text-sm font-medium text-slate-700"
                                >
                                    Amount
                                    <span class="text-red-500">*</span>
                                </label>

                                <div class="relative">
                                    <span
                                        class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-medium text-slate-400"
                                    >
                                        Rp
                                    </span>

                                  <input
                                        :value="amountInput"
                                        type="text"
                                        inputmode="decimal"
                                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-3 pl-10 text-right text-lg font-semibold tabular-nums text-slate-800 outline-none transition focus:border-emerald-500 focus:ring-emerald-500/10"
                                        placeholder="0,00"
                                        :disabled="form.processing"
                                        @focus="handleAmountFocus"
                                        @input="handleAmountInput"
                                        @blur="handleAmountBlur"
                                    />
                                </div>

                                <p
                                    v-if="form.errors.amount"
                                    class="mt-1 text-xs text-red-600"
                                >
                                    {{ form.errors.amount }}
                                </p>

                                <div
                                    class="mt-3 flex items-center justify-between gap-3"
                                >
                                    <span
                                        class="text-xs text-slate-500"
                                    >
                                        Transfer total
                                    </span>

                                    <span
                                        class="text-sm font-semibold text-emerald-800"
                                    >
                                        {{ formatCurrency(transferAmount) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Validation Warning -->
                            <div
                                v-if="validationMessage"
                                class="mt-4 flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="mt-0.5 h-4 w-4 shrink-0"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 9v4m0 4h.01M10.29 3.86l-8.24 14A2 2 0 003.78 21h16.44a2 2 0 001.73-3.14l-8.24-14a2 2 0 00-3.42 0z"
                                    />
                                </svg>

                                <span>{{ validationMessage }}</span>
                            </div>
                        </section>
                    </div>
                </div>

                <!-- Footer -->
                <div
                    class="flex items-center justify-end gap-3 border-t border-slate-200 bg-slate-50/70 px-6 py-4"
                >
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
                        :disabled="!canSubmit"
                        @click="submit"
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
    </Teleport>
</template>