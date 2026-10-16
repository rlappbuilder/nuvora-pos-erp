<script setup>
import {
    computed,
    ref,
    watch,
} from 'vue'

import WorkflowTimeline
    from '@/Components/Workflow/WorkflowTimeline.vue'

import BaseModal
    from '@/Components/Modal/BaseModal.vue'

import BaseButton
    from '@/Components/Button/BaseButton.vue'

import StatusBadge
    from '@/Components/Display/StatusBadge.vue'

import AuditTrail
    from '@/Components/Workflow/AuditTrail.vue'

/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },

    settlement: {
        type: Object,
        default: null,
    },

    loading: {
        type: Boolean,
        default: false,
    },

    processing: {
        type: Boolean,
        default: false,
    },

    currentBranchId: {
        type: [Number, String],
        default: null,
    },
})


/*
|--------------------------------------------------------------------------
| Emits
|--------------------------------------------------------------------------
*/

const emit = defineEmits([
    'close',
    'edit',
    'submit',
    'approve',
    'reject',
    'post',
    'cancel',
    'receive',
    'print',
    'refresh',
])

/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const activeTab = ref('overview')

const receivingAccounts = ref([])
const receivingAccountsLoading = ref(false)
const selectedReceivingAccountId = ref('')

async function loadReceivingAccounts() {
    receivingAccounts.value = []
    selectedReceivingAccountId.value = ''

    const branchId = props.settlement?.to_branch_id

    if (!branchId) {
        console.error('Receiving branch ID is missing.')
        return
    }

    receivingAccountsLoading.value = true

    try {
        const response = await fetch(
            `/accounting/inter-branch-settlements/accounts?branch_id=${encodeURIComponent(branchId)}`,
            {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            }
        )

        const result = await response.json()

        console.log('RECEIVING ACCOUNTS HTTP:', response.status)
        console.log('RECEIVING ACCOUNTS RESPONSE:', result)

        if (!response.ok) {
            throw new Error(
                result.message ?? 'Failed to load receiving accounts.'
            )
        }

        const accounts = Array.isArray(result.data)
            ? result.data
            : []

        receivingAccounts.value = accounts

        console.log(
            'RECEIVING ACCOUNTS LOADED:',
            receivingAccounts.value
        )
    } catch (exception) {
        console.error('LOAD RECEIVING ACCOUNTS ERROR:', exception)
        receivingAccounts.value = []
    } finally {
        receivingAccountsLoading.value = false
    }
}


/*
|--------------------------------------------------------------------------
| Watchers
|--------------------------------------------------------------------------
*/

watch(
    () => props.show,
    (show) => {
        if (show) {
            activeTab.value = 'overview'
        }
    }
)


/*
|--------------------------------------------------------------------------
| Computed
|--------------------------------------------------------------------------
*/

const status = computed(() => {
    return String(
        props.settlement?.status ?? ''
    ).toLowerCase()
})

const receiptStatus = computed(() => {
    return String(
        props.settlement?.receipt_status ?? 'pending'
    ).toLowerCase()
})

const documentNumber = computed(() => {
    return (
        props.settlement?.settlement_number
        ?? props.settlement?.code
        ?? '-'
    )
})

const activities = computed(() => {
    return (
        props.settlement?.activities
        ?? []
    )
})

const sourceBranch = computed(() => {
    return props.settlement?.from_branch?.name ?? '-'
})

const destinationBranch = computed(() => {
    return props.settlement?.to_branch?.name ?? '-'
})

const sourceAccount = computed(() => {
    return props.settlement?.from_account ?? null
})

const destinationAccount = computed(() => {
    return props.settlement?.to_account ?? null
})

const amount = computed(() => {
    return Number(props.settlement?.amount ?? 0)
})

const isRecipientBranch = computed(() => {
    const recipientBranchId =
        props.settlement?.to_branch_id

    const currentBranchId =
        props.currentBranchId

    if (
        recipientBranchId === null ||
        recipientBranchId === undefined ||
        currentBranchId === null ||
        currentBranchId === undefined
    ) {
        return false
    }

    return (
        String(recipientBranchId) ===
        String(currentBranchId)
    )
})

const canEdit = computed(() => {
    return [
        'draft',
        'rejected',
    ].includes(status.value)
})

const canSubmit = computed(() => {
    return [
        'draft',
        'rejected',
    ].includes(status.value)
})

const canApprove = computed(() => {
    return status.value === 'submitted'
})

const canReject = computed(() => {
    return status.value === 'submitted'
})

const canPost = computed(() => {
    return status.value === 'approved'
})

const canCancel = computed(() => {
    return [
        'draft',
        'submitted',
        'approved',
        'rejected',
    ].includes(status.value)
})
const canPrint = computed(() => {
    return (
        status.value === 'posted' ||
        receiptStatus.value === 'received'
    )
})

watch(
    () => props.settlement?.id,
    () => {
        selectedReceivingAccountId.value =
            props.settlement?.to_account_id
                ? String(props.settlement.to_account_id)
                : ''
    },
    { immediate: true }
)

const canReceive = computed(() => {
    return (
        status.value === 'posted' &&
        receiptStatus.value === 'pending' &&
        isRecipientBranch.value
    )
})
const canConfirmReceive = computed(() => {
    return (
        canReceive.value &&
        selectedReceivingAccountId.value !== '' &&
        selectedReceivingAccountId.value !== null &&
        selectedReceivingAccountId.value !== undefined
    )
})
watch(
    [
        () => props.show,
        () => props.settlement?.to_branch_id,
        () => props.currentBranchId,
        status,
        receiptStatus,
        isRecipientBranch,
    ],
    ([
        show,
        branchId,
        ,
        settlementStatus,
        currentReceiptStatus,
        recipientBranch,
    ]) => {
        if (
            show &&
            branchId &&
            settlementStatus === 'posted' &&
            currentReceiptStatus === 'pending' &&
            recipientBranch
        ) {
            loadReceivingAccounts()
        } else {
            receivingAccounts.value = []
            selectedReceivingAccountId.value = ''
        }
    },
    { immediate: true }
)
/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function formatCurrency(value) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(Number(value ?? 0))
}

function formatDate(value) {
    if (!value) {
        return '-'
    }

    const date = new Date(value)

    if (Number.isNaN(date.getTime())) {
        return '-'
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(date)
}

function formatDateTime(value) {
    if (!value) {
        return '-'
    }

    const date = new Date(value)

    if (Number.isNaN(date.getTime())) {
        return '-'
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date)
}

function setTab(tab) {
    activeTab.value = tab
}

function emitAction(action) {
    emit(action, props.settlement)
}
watch(
    () => props.settlement,
    (settlement) => {
        console.log(
            'SETTLEMENT:',
            settlement?.settlement_number
        )

        console.log(
            'SETTLEMENT ACTIVITIES:',
            settlement?.activities
        )

        console.log(
            'ACTIVITIES COUNT:',
            settlement?.activities?.length ?? 0
        )
    },
    { immediate: true }
)
</script>
<template>
    <BaseModal
        :show="show"
        title="Inter-Branch Settlement"
        size="xl"
        @close="emit('close')"
    >
        <div class="flex min-h-0 flex-col">

            <!-- Document Header -->

            <div
                class="
                    flex flex-col gap-4
                    border-b border-slate-200 pb-5
                    sm:flex-row sm:items-start
                    sm:justify-between
                "
            >
                <div class="min-w-0">
                    <div
                        class="
                            text-[11px] font-semibold
                            uppercase tracking-[0.14em]
                            text-blue-600
                        "
                    >
                        Inter-Branch Settlement
                    </div>

                    <div
                        class="
                            mt-1.5 flex flex-wrap
                            items-center gap-3
                        "
                    >
                        <h2
                            class="
                                text-lg font-bold
                                tracking-tight text-slate-900
                            "
                        >
                            {{ documentNumber }}
                        </h2>

                        <StatusBadge :status="status" />
                    </div>

                    <div class="mt-1.5 text-xs text-slate-500">
                        Settlement Date

                        <span
                            class="
                                ml-1 font-semibold
                                text-slate-700
                            "
                        >
                            {{
                                formatDate(
                                    settlement?.settlement_date
                                )
                            }}
                        </span>
                    </div>
                </div>

                <div
                    class="
                        shrink-0 rounded-xl
                        border border-blue-100
                        bg-blue-50/60 px-5 py-3.5
                        sm:min-w-[210px] sm:text-right
                    "
                >
                    <div
                        class="
                            text-[10px] font-semibold
                            uppercase tracking-[0.14em]
                            text-blue-500
                        "
                    >
                        Settlement Amount
                    </div>

                    <div
                        class="
                            mt-1 text-xl font-bold
                            tabular-nums text-blue-700
                        "
                    >
                        {{ formatCurrency(amount) }}
                    </div>
                </div>
            </div>


            <!-- Tabs -->

            <div class="mt-5 border-b border-slate-200">
                <nav class="-mb-px flex gap-6 overflow-x-auto">
                    <button
                        v-for="tab in [
                            { key: 'overview', label: 'Overview' },
                            { key: 'workflow', label: 'Workflow' },
                            { key: 'audit', label: 'Audit Timeline' },
                        ]"
                        :key="tab.key"
                        type="button"
                        class="
                            whitespace-nowrap border-b-2
                            px-1 pb-3 text-sm font-semibold
                            transition
                        "
                        :class="
                            activeTab === tab.key
                                ? 'border-blue-600 text-blue-700'
                                : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700'
                        "
                        @click="setTab(tab.key)"
                    >
                        {{ tab.label }}
                    </button>
                </nav>
            </div>


            <!-- Content -->

            <div
                class="
                    mt-5 min-h-[300px]
                    overflow-y-auto pr-1
                "
            >
                <!-- Loading -->

                <div
                    v-if="loading"
                    class="
                        flex min-h-[300px]
                        items-center justify-center
                    "
                >
                    <div
                        class="
                            flex flex-col items-center
                            gap-3 text-slate-500
                        "
                    >
                        <svg
                            class="h-7 w-7 animate-spin text-blue-600"
                            viewBox="0 0 24 24"
                            fill="none"
                        >
                            <circle
                                cx="12"
                                cy="12"
                                r="9"
                                stroke="currentColor"
                                stroke-width="3"
                                opacity=".2"
                            />

                            <path
                                d="M21 12a9 9 0 0 0-9-9"
                                stroke="currentColor"
                                stroke-width="3"
                            />
                        </svg>

                        <span class="text-sm">
                            Loading settlement...
                        </span>
                    </div>
                </div>


                <!-- No Data -->

                <div
                    v-else-if="!settlement"
                    class="
                        flex min-h-[300px]
                        items-center justify-center
                        text-sm text-slate-500
                    "
                >
                    Settlement data is not available.
                </div>


                <!-- Overview -->

                <div
                    v-else-if="activeTab === 'overview'"
                    class="space-y-6"
                >
                    <!-- Settlement Information -->

                    <section>
                        <div class="mb-4">
                            <div class="flex items-center gap-2">
                                <span
                                    class="
                                        h-5 w-1 rounded-full
                                        bg-blue-600
                                    "
                                />

                                <h3
                                    class="
                                        text-sm font-bold
                                        text-slate-900
                                    "
                                >
                                    Settlement Information
                                </h3>
                            </div>

                            <p
                                class="
                                    mt-1 pl-3 text-xs
                                    text-slate-500
                                "
                            >
                                Document and transaction information.
                            </p>
                        </div>

                        <div
                            class="
                                overflow-hidden rounded-xl
                                border border-slate-200 bg-white
                            "
                        >
                            <div class="grid grid-cols-1 sm:grid-cols-2">
                                <div
                                    class="
                                        border-b border-slate-100
                                        px-4 py-3.5 sm:border-r
                                    "
                                >
                                    <div
                                        class="
                                            text-[10px] font-semibold
                                            uppercase tracking-[0.12em]
                                            text-slate-400
                                        "
                                    >
                                        Settlement Number
                                    </div>

                                    <div
                                        class="
                                            mt-1 text-sm font-bold
                                            text-blue-700
                                        "
                                    >
                                        {{ documentNumber }}
                                    </div>
                                </div>

                                <div
                                    class="
                                        border-b border-slate-100
                                        px-4 py-3.5
                                    "
                                >
                                    <div
                                        class="
                                            text-[10px] font-semibold
                                            uppercase tracking-[0.12em]
                                            text-slate-400
                                        "
                                    >
                                        Settlement Date
                                    </div>

                                    <div
                                        class="
                                            mt-1 text-sm font-medium
                                            text-slate-700
                                        "
                                    >
                                        {{
                                            formatDate(
                                                settlement.settlement_date
                                            )
                                        }}
                                    </div>
                                </div>

                                <div
                                    class="
                                        border-b border-slate-100
                                        px-4 py-3.5 sm:border-r
                                    "
                                >
                                    <div
                                        class="
                                            text-[10px] font-semibold
                                            uppercase tracking-[0.12em]
                                            text-slate-400
                                        "
                                    >
                                        Created At
                                    </div>

                                    <div
                                        class="
                                            mt-1 text-sm font-medium
                                            text-slate-700
                                        "
                                    >
                                        {{
                                            formatDateTime(
                                                settlement.created_at
                                            )
                                        }}
                                    </div>
                                </div>

                                <div
                                    class="
                                        border-b border-slate-100
                                        px-4 py-3.5
                                    "
                                >
                                    <div
                                        class="
                                            text-[10px] font-semibold
                                            uppercase tracking-[0.12em]
                                            text-slate-400
                                        "
                                    >
                                        Receipt Status
                                    </div>

                                    <div class="mt-1">
                                        <StatusBadge
                                            :status="receiptStatus"
                                        />
                                    </div>
                                </div>

                                <div class="px-4 py-3.5 sm:col-span-2">
                                    <div
                                        class="
                                            text-[10px] font-semibold
                                            uppercase tracking-[0.12em]
                                            text-slate-400
                                        "
                                    >
                                        Description
                                    </div>

                                    <div
                                        class="
                                            mt-1 whitespace-pre-line
                                            text-sm leading-6
                                            text-slate-600
                                        "
                                    >
                                        {{ settlement.description ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>


                    <!-- Branches and Accounts -->

                    <section>
                        <div class="mb-4">
                            <div class="flex items-center gap-2">
                                <span
                                    class="
                                        h-5 w-1 rounded-full
                                        bg-blue-600
                                    "
                                />

                                <h3
                                    class="
                                        text-sm font-bold
                                        text-slate-900
                                    "
                                >
                                    Payment Details
                                </h3>
                            </div>

                            <p
                                class="
                                    mt-1 pl-3 text-xs
                                    text-slate-500
                                "
                            >
                                Payer and recipient branch information.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <!-- Payer -->

                            <div
                                class="
                                    overflow-hidden rounded-xl
                                    border border-slate-200 bg-white
                                "
                            >
                                <div
                                    class="
                                        border-b border-slate-200
                                        bg-slate-50 px-4 py-3
                                    "
                                >
                                    <div
                                        class="
                                            text-[10px] font-semibold
                                            uppercase tracking-[0.12em]
                                            text-slate-400
                                        "
                                    >
                                        Payer
                                    </div>

                                    <div
                                        class="
                                            mt-1 text-sm font-bold
                                            text-slate-900
                                        "
                                    >
                                        Paying Branch
                                    </div>
                                </div>

                                <div class="space-y-4 px-4 py-4">
                                    <div>
                                        <div
                                            class="
                                                text-[10px] font-semibold
                                                uppercase tracking-[0.12em]
                                                text-slate-400
                                            "
                                        >
                                            Branch
                                        </div>

                                        <div
                                            class="
                                                mt-1 text-sm font-semibold
                                                text-slate-800
                                            "
                                        >
                                            {{ sourceBranch }}
                                        </div>
                                    </div>

                                    <div>
                                        <div
                                            class="
                                                text-[10px] font-semibold
                                                uppercase tracking-[0.12em]
                                                text-slate-400
                                            "
                                        >
                                            Cash & Bank Account
                                        </div>

                                        <div
                                            class="
                                                mt-1 text-sm font-semibold
                                                text-slate-800
                                            "
                                        >
                                            {{ sourceAccount?.name ?? '-' }}
                                        </div>

                                        <div
                                            v-if="sourceAccount?.code"
                                            class="mt-1 text-xs text-slate-500"
                                        >
                                            {{ sourceAccount.code }}
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <!-- Recipient -->

                            <div
                                class="
                                    overflow-hidden rounded-xl
                                    border border-slate-200 bg-white
                                "
                            >
                                <div
                                    class="
                                        border-b border-slate-200
                                        bg-slate-50 px-4 py-3
                                    "
                                >
                                    <div
                                        class="
                                            text-[10px] font-semibold
                                            uppercase tracking-[0.12em]
                                            text-slate-400
                                        "
                                    >
                                        Recipient
                                    </div>

                                    <div
                                        class="
                                            mt-1 text-sm font-bold
                                            text-slate-900
                                        "
                                    >
                                        Receiving Branch
                                    </div>
                                </div>

                                <div class="space-y-4 px-4 py-4">
                                    <div>
                                        <div
                                            class="
                                                text-[10px] font-semibold
                                                uppercase tracking-[0.12em]
                                                text-slate-400
                                            "
                                        >
                                            Branch
                                        </div>

                                        <div
                                            class="
                                                mt-1 text-sm font-semibold
                                                text-slate-800
                                            "
                                        >
                                            {{ destinationBranch }}
                                        </div>
                                    </div>

                                    <div>
    
    <div
        class="
            text-[10px] font-semibold
            uppercase tracking-[0.12em]
            text-slate-400
        "
    >
        Cash & Bank Account
    </div>

    <template v-if="canReceive">
        <select
            v-model="selectedReceivingAccountId"
            :disabled="receivingAccountsLoading"
            required
            class="
                mt-2 w-full rounded-lg
                border border-slate-300
                bg-white px-3 py-2
                text-sm text-slate-700
                focus:border-blue-500
                focus:outline-none
                focus:ring-2 focus:ring-blue-100
                disabled:cursor-not-allowed
                disabled:bg-slate-100
            "
        >
            <option value="">
                {{
                    receivingAccountsLoading
                        ? 'Loading accounts...'
                        : 'Select receiving account'
                }}
            </option>

            <option
                v-for="account in receivingAccounts"
                :key="account.id"
                :value="String(account.id)"
            >
                {{ account.code }} - {{ account.name }}
            </option>
        </select>

        <p
            v-if="
                !receivingAccountsLoading &&
                receivingAccounts.length === 0
            "
            class="mt-2 text-xs text-red-600"
        >
            No Cash & Bank account is available for this branch.
        </p>

        <p
            v-if="canReceive && !selectedReceivingAccountId"
            class="mt-1 text-xs text-slate-500"
        >
            Receiving account is required.
        </p>
    </template>

    <template v-else>
        <div class="mt-1 text-sm font-semibold text-slate-800">
            {{ destinationAccount?.name ?? '-' }}
        </div>

        <div
            v-if="destinationAccount?.code"
            class="mt-1 text-xs text-slate-500"
        >
            {{ destinationAccount.code }}
        </div>
    </template>
</div>
                                       
                                </div>
                            </div>
                        </div>
                    </section>


                    <!-- Receipt Information -->

                    <section
                        v-if="
                            receiptStatus === 'received' ||
                            settlement.received_at
                        "
                        class="
                            rounded-xl border border-emerald-200
                            bg-emerald-50 px-4 py-4
                        "
                    >
                        <div
                            class="
                                text-xs font-bold
                                text-emerald-700
                            "
                        >
                            Payment Receipt
                        </div>

                        <div class="mt-2 grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <div
                                    class="
                                        text-[10px] font-semibold
                                        uppercase tracking-[0.12em]
                                        text-emerald-600
                                    "
                                >
                                    Received At
                                </div>

                                <div
                                    class="
                                        mt-1 text-sm font-semibold
                                        text-emerald-900
                                    "
                                >
                                    {{
                                        formatDateTime(
                                            settlement.received_at
                                        )
                                    }}
                                </div>
                            </div>

                            <div>
                                <div
                                    class="
                                        text-[10px] font-semibold
                                        uppercase tracking-[0.12em]
                                        text-emerald-600
                                    "
                                >
                                    Receipt Status
                                </div>

                                <div
                                    class="
                                        mt-1 text-sm font-semibold
                                        text-emerald-900
                                    "
                                >
                                    Received
                                </div>
                            </div>
                        </div>
                    </section>


                    <!-- Rejection Reason -->

                    <section
                        v-if="
                            status === 'rejected' &&
                            settlement.rejected_reason
                        "
                        class="
                            rounded-xl border border-red-200
                            bg-red-50 px-4 py-4
                        "
                    >
                        <div class="text-xs font-bold text-red-700">
                            Rejection Reason
                        </div>

                        <div
                            class="
                                mt-2 whitespace-pre-line
                                text-sm leading-6 text-red-800
                            "
                        >
                            {{ settlement.rejected_reason }}
                        </div>
                    </section>


                    <!-- Cancellation Reason -->

                    <section
                        v-if="
                            status === 'cancelled' &&
                            settlement.cancelled_reason
                        "
                        class="
                            rounded-xl border border-orange-200
                            bg-orange-50 px-4 py-4
                        "
                    >
                        <div class="text-xs font-bold text-orange-700">
                            Cancellation Reason
                        </div>

                        <div
                            class="
                                mt-2 whitespace-pre-line
                                text-sm leading-6 text-orange-800
                            "
                        >
                            {{ settlement.cancelled_reason }}
                        </div>
                    </section>
                </div>


                <!-- Workflow -->

                <div
                    v-else-if="activeTab === 'workflow'"
                    class="space-y-5"
                >
                    <div>
                        <div class="flex items-center gap-2">
                            <span
                                class="
                                    h-5 w-1 rounded-full
                                    bg-blue-600
                                "
                            />

                            <h3
                                class="
                                    text-sm font-bold
                                    text-slate-900
                                "
                            >
                                Workflow Timeline
                            </h3>
                        </div>

                        <p class="mt-1 pl-3 text-xs text-slate-500">
                            Settlement status progression and activities.
                        </p>
                    </div>

                    <div
                        class="
                            rounded-xl border border-slate-200
                            bg-white p-4
                        "
                    >
                        <WorkflowTimeline :activities="activities" />
                    </div>
                </div>


                <!-- Audit Timeline -->

                <div
                    v-else-if="activeTab === 'audit'"
                    class="space-y-5"
                >
                    <div>
                        <div class="flex items-center gap-2">
                            <span
                                class="
                                    h-5 w-1 rounded-full
                                    bg-blue-600
                                "
                            />

                            <h3
                                class="
                                    text-sm font-bold
                                    text-slate-900
                                "
                            >
                                Audit Timeline
                            </h3>
                        </div>

                        <p class="mt-1 pl-3 text-xs text-slate-500">
                            Complete activity history for this settlement.
                        </p>
                    </div>

                    <div
                        class="
                            rounded-xl border border-slate-200
                            bg-white p-4
                        "
                    >
                        <AuditTrail :activities="activities" />
                    </div>
                </div>
            </div>
        </div>


        <!-- Footer -->

        <template #footer>
            <div
                class="
                    flex w-full flex-col-reverse gap-3
                    sm:flex-row sm:items-center
                    sm:justify-between
                "
            >
                <div
                    class="
                        text-[11px] font-medium
                        tracking-wide text-slate-400
                    "
                >
                    {{ documentNumber }}
                </div>

                <div class="flex flex-wrap justify-end gap-2">
                    <BaseButton
                        v-if="canEdit"
                        variant="secondary"
                        :disabled="processing"
                        @click="emitAction('edit')"
                    >
                        Edit
                    </BaseButton>

                    <BaseButton
                        v-if="canSubmit"
                        variant="primary"
                        :disabled="processing"
                        @click="emitAction('submit')"
                    >
                        Submit
                    </BaseButton>

                    <BaseButton
                        v-if="canApprove"
                        variant="primary"
                        :disabled="processing"
                        @click="emitAction('approve')"
                    >
                        Approve
                    </BaseButton>

                    <BaseButton
                        v-if="canReject"
                        variant="danger"
                        :disabled="processing"
                        @click="emitAction('reject')"
                    >
                        Reject
                    </BaseButton>

                    <BaseButton
                        v-if="canPost"
                        variant="primary"
                        :disabled="processing"
                        @click="emitAction('post')"
                    >
                        Post Payment
                    </BaseButton>
                    <BaseButton
                        v-if="canPrint"
                        variant="secondary"
                        :disabled="processing"
                        @click="emitAction('print')"
                    >
                        Print
                    </BaseButton>
                    <!-- Receive hanya untuk cabang penerima -->

                    <BaseButton
                        v-if="canReceive"
                        variant="primary"
                        :disabled="
                            processing ||
                            !selectedReceivingAccountId
                        "
                        @click="
                            emit('receive', {
                                settlement,
                                to_account_id: selectedReceivingAccountId,
                            })
                        "
                    >
                        Receive Payment
                    </BaseButton>    
                    <BaseButton
                        v-if="canCancel"
                        variant="secondary"
                        :disabled="processing"
                        @click="emitAction('cancel')"
                    >
                        Cancel
                    </BaseButton>

                    <BaseButton
                        variant="secondary"
                        :disabled="processing"
                        @click="emit('close')"
                    >
                        Back
                    </BaseButton>
                </div>
            </div>
        </template>
    </BaseModal>
</template>