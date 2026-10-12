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

    transfer: {
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
    'refresh',
])


/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const activeTab = ref('overview')


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
        props.transfer?.status ?? ''
    ).toLowerCase()
})
const documentNumber = computed(() => {
    return props.transfer?.transfer_number
        ?? props.transfer?.code
        ?? '-'
})

const activities = computed(() => {
    return (
        props.transfer?.activities
        ?? []
    )
})


const transferType = computed(() => {

    if (!props.transfer) {
        return '-'
    }

    if (
        props.transfer.to_branch_id === null ||
        props.transfer.to_branch_id === undefined
    ) {
        return 'Branch to Company'
    }

    if (
        String(props.transfer.from_branch_id) ===
        String(props.transfer.to_branch_id)
    ) {
        return 'Same Branch'
    }

    return 'Inter-Branch'
})


const sourceBranch = computed(() => {
    return props.transfer?.from_branch?.name ?? '-'
})


const destinationBranch = computed(() => {

    if (
        props.transfer?.to_branch_id === null ||
        props.transfer?.to_branch_id === undefined
    ) {
        return 'Company'
    }

    return props.transfer?.to_branch?.name ?? '-'
})


const sourceAccount = computed(() => {
    return props.transfer?.from_account ?? null
})


const destinationAccount = computed(() => {
    return props.transfer?.to_account ?? null
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


function formatDate(value)
{
    if (!value) {
        return '-'
    }

    const date = new Date(value)

    if (Number.isNaN(date.getTime())) {
        return '-'
    }

    return new Intl.DateTimeFormat(
        'id-ID',
        {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        }
    ).format(date)
}


function formatDateTime(value)
{
    if (!value) {
        return '-'
    }

    const date = new Date(value)

    if (Number.isNaN(date.getTime())) {
        return '-'
    }

    return new Intl.DateTimeFormat(
        'id-ID',
        {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        }
    ).format(date)
}


function setTab(tab)
{
    activeTab.value = tab
}


function emitAction(action)
{
    emit(action)
}

</script>


<template>

    <BaseModal
        :show="show"
        title="Fund Transfer"
        size="xl"
        @close="emit('close')"
    >

        <div class="flex min-h-0 flex-col">

            <!-- ===================================================== -->
            <!-- Document Header -->
            <!-- ===================================================== -->

            <div
                class="
                    flex
                    flex-col
                    gap-4
                    border-b
                    border-slate-200
                    pb-5
                    sm:flex-row
                    sm:items-start
                    sm:justify-between
                "
            >

                <div class="min-w-0">

                    <div
                        class="
                            text-[11px]
                            font-semibold
                            uppercase
                            tracking-[0.14em]
                            text-blue-600
                        "
                    >
                        Fund Transfer
                    </div>

                    <div
                        class="
                            mt-1.5
                            flex
                            flex-wrap
                            items-center
                            gap-3
                        "
                    >

                        <h2
                            class="
                                text-lg
                                font-bold
                                tracking-tight
                                text-slate-900
                            "
                        >
                            {{ documentNumber }}
                        </h2>

                        <StatusBadge
                            :status="status"
                        />

                    </div>

                    <div
                        class="
                            mt-1.5
                            text-xs
                            text-slate-500
                        "
                    >
                        Transfer Date

                        <span
                            class="
                                ml-1
                                font-semibold
                                text-slate-700
                            "
                        >
                            {{
                                formatDate(
                                    transfer?.transfer_date
                                )
                            }}
                        </span>
                    </div>

                </div>


                <!-- Amount -->

                <div
                    class="
                        shrink-0
                        rounded-xl
                        border
                        border-blue-100
                        bg-blue-50/60
                        px-5
                        py-3.5
                        sm:min-w-[210px]
                        sm:text-right
                    "
                >

                    <div
                        class="
                            text-[10px]
                            font-semibold
                            uppercase
                            tracking-[0.14em]
                            text-blue-500
                        "
                    >
                        Transfer Amount
                    </div>

                     <div
                        class="
                            mt-1
                            text-xl
                            font-bold
                            tabular-nums
                            text-blue-700
                        "
                    >
                        {{
                            formatCurrency(
                                transfer?.amount
                            )
                        }}
                    </div>

                </div>

            </div>


            <!-- ===================================================== -->
            <!-- Tabs -->
            <!-- ===================================================== -->

            <div
                class="
                    mt-5
                    border-b
                    border-slate-200
                "
            >

                <nav
                    class="
                        -mb-px
                        flex
                        gap-6
                        overflow-x-auto
                    "
                >

                    <button
                        type="button"
                        class="
                            whitespace-nowrap
                            border-b-2
                            px-1
                            pb-3
                            text-sm
                            font-semibold
                            transition
                        "
                        :class="
                            activeTab === 'overview'
                                ? 'border-blue-600 text-blue-700'
                                : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700'
                        "
                        @click="setTab('overview')"
                    >
                        Overview
                    </button>


                    <button
                        type="button"
                        class="
                            whitespace-nowrap
                            border-b-2
                            px-1
                            pb-3
                            text-sm
                            font-semibold
                            transition
                        "
                        :class="
                            activeTab === 'workflow'
                                ? 'border-blue-600 text-blue-700'
                                : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700'
                        "
                        @click="setTab('workflow')"
                    >
                        Workflow
                    </button>


                    <button
                        type="button"
                        class="
                            whitespace-nowrap
                            border-b-2
                            px-1
                            pb-3
                            text-sm
                            font-semibold
                            transition
                        "
                        :class="
                            activeTab === 'audit'
                                ? 'border-blue-600 text-blue-700'
                                : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700'
                        "
                        @click="setTab('audit')"
                    >
                        Audit Timeline
                    </button>

                </nav>

            </div>


            <!-- ===================================================== -->
            <!-- Content -->
            <!-- ===================================================== -->

            <div
                class="
                    mt-5
                    min-h-[300px]
                    overflow-y-auto
                    pr-1
                "
            >

                <!-- ================================================= -->
                <!-- Loading -->
                <!-- ================================================= -->

                <div
                    v-if="loading"
                    class="
                        flex
                        min-h-[300px]
                        items-center
                        justify-center
                    "
                >

                    <div
                        class="
                            flex
                            flex-col
                            items-center
                            gap-3
                            text-slate-500
                        "
                    >

                        <svg
                            class="
                                h-7
                                w-7
                                animate-spin
                                text-blue-600
                            "
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
                            Loading fund transfer...
                        </span>

                    </div>

                </div>


                <!-- ================================================= -->
                <!-- No Data -->
                <!-- ================================================= -->

                <div
                    v-else-if="!transfer"
                    class="
                        flex
                        min-h-[300px]
                        items-center
                        justify-center
                        text-sm
                        text-slate-500
                    "
                >
                    Fund transfer data is not available.
                </div>


                <!-- ================================================= -->
                <!-- Overview -->
                <!-- ================================================= -->

                <div
                    v-else-if="activeTab === 'overview'"
                    class="space-y-6"
                >

                    <!-- Transfer Information -->

                    <section>

                        <div class="mb-4">

                            <div
                                class="
                                    flex
                                    items-center
                                    gap-2
                                "
                            >

                                <span
                                    class="
                                        h-5
                                        w-1
                                        rounded-full
                                        bg-blue-600
                                    "
                                ></span>

                                <h3
                                    class="
                                        text-sm
                                        font-bold
                                        text-slate-900
                                    "
                                >
                                    Transfer Information
                                </h3>

                            </div>

                            <p
                                class="
                                    mt-1
                                    pl-3
                                    text-xs
                                    text-slate-500
                                "
                            >
                                Document and transaction information.
                            </p>

                        </div>


                        <div
                            class="
                                overflow-hidden
                                rounded-xl
                                border
                                border-slate-200
                                bg-white
                            "
                        >

                            <div
                                class="
                                    grid
                                    grid-cols-1
                                    sm:grid-cols-2
                                "
                            >

                                <!-- Transfer Number -->

                                <div
                                    class="
                                        border-b
                                        border-slate-100
                                        px-4
                                        py-3.5
                                        sm:border-r
                                    "
                                >

                                    <div
                                        class="
                                            text-[10px]
                                            font-semibold
                                            uppercase
                                            tracking-[0.12em]
                                            text-slate-400
                                        "
                                    >
                                        Transfer Number
                                    </div>

                                    <div
                                        class="
                                            mt-1
                                            text-sm
                                            font-bold
                                            text-blue-700
                                        "
                                    >
                                         {{ documentNumber }}
                                    </div>

                                </div>


                                <!-- Transfer Type -->

                                <div
                                    class="
                                        border-b
                                        border-slate-100
                                        px-4
                                        py-3.5
                                    "
                                >

                                    <div
                                        class="
                                            text-[10px]
                                            font-semibold
                                            uppercase
                                            tracking-[0.12em]
                                            text-slate-400
                                        "
                                    >
                                        Transfer Type
                                    </div>

                                    <div
                                        class="
                                            mt-1
                                            text-sm
                                            font-semibold
                                            text-slate-800
                                        "
                                    >
                                        {{ transferType }}
                                    </div>

                                </div>


                                <!-- Transfer Date -->

                                <div
                                    class="
                                        border-b
                                        border-slate-100
                                        px-4
                                        py-3.5
                                        sm:border-r
                                    "
                                >

                                    <div
                                        class="
                                            text-[10px]
                                            font-semibold
                                            uppercase
                                            tracking-[0.12em]
                                            text-slate-400
                                        "
                                    >
                                        Transfer Date
                                    </div>

                                    <div
                                        class="
                                            mt-1
                                            text-sm
                                            font-medium
                                            text-slate-700
                                        "
                                    >
                                        {{
                                            formatDate(
                                                transfer.transfer_date
                                            )
                                        }}
                                    </div>

                                </div>


                                <!-- Created At -->

                                <div
                                    class="
                                        border-b
                                        border-slate-100
                                        px-4
                                        py-3.5
                                    "
                                >

                                    <div
                                        class="
                                            text-[10px]
                                            font-semibold
                                            uppercase
                                            tracking-[0.12em]
                                            text-slate-400
                                        "
                                    >
                                        Created At
                                    </div>

                                    <div
                                        class="
                                            mt-1
                                            text-sm
                                            font-medium
                                            text-slate-700
                                        "
                                    >
                                        {{
                                            formatDateTime(
                                                transfer.created_at
                                            )
                                        }}
                                    </div>

                                </div>


                                <!-- Description -->

                                <div
                                    class="
                                        px-4
                                        py-3.5
                                        sm:col-span-2
                                    "
                                >

                                    <div
                                        class="
                                            text-[10px]
                                            font-semibold
                                            uppercase
                                            tracking-[0.12em]
                                            text-slate-400
                                        "
                                    >
                                        Description
                                    </div>

                                    <div
                                        class="
                                            mt-1
                                            whitespace-pre-line
                                            text-sm
                                            leading-6
                                            text-slate-600
                                        "
                                    >
                                        {{
                                            transfer.description
                                            ?? '-'
                                        }}
                                    </div>

                                </div>

                            </div>

                        </div>

                    </section>


                    <!-- Source and Destination -->

                    <section>

                        <div class="mb-4">

                            <div
                                class="
                                    flex
                                    items-center
                                    gap-2
                                "
                            >

                                <span
                                    class="
                                        h-5
                                        w-1
                                        rounded-full
                                        bg-blue-600
                                    "
                                ></span>

                                <h3
                                    class="
                                        text-sm
                                        font-bold
                                        text-slate-900
                                    "
                                >
                                    Transfer Accounts
                                </h3>

                            </div>

                            <p
                                class="
                                    mt-1
                                    pl-3
                                    text-xs
                                    text-slate-500
                                "
                            >
                                Source and destination of funds.
                            </p>

                        </div>


                        <div
                            class="
                                grid
                                grid-cols-1
                                gap-4
                                md:grid-cols-2
                            "
                        >

                            <!-- From -->

                            <div
                                class="
                                    overflow-hidden
                                    rounded-xl
                                    border
                                    border-slate-200
                                    bg-white
                                "
                            >

                                <div
                                    class="
                                        border-b
                                        border-slate-200
                                        bg-slate-50
                                        px-4
                                        py-3
                                    "
                                >

                                    <div
                                        class="
                                            text-[10px]
                                            font-semibold
                                            uppercase
                                            tracking-[0.12em]
                                            text-slate-400
                                        "
                                    >
                                        From
                                    </div>

                                    <div
                                        class="
                                            mt-1
                                            text-sm
                                            font-bold
                                            text-slate-900
                                        "
                                    >
                                        Source of Funds
                                    </div>

                                </div>


                                <div class="space-y-4 px-4 py-4">

                                    <div>

                                        <div
                                            class="
                                                text-[10px]
                                                font-semibold
                                                uppercase
                                                tracking-[0.12em]
                                                text-slate-400
                                            "
                                        >
                                            Branch
                                        </div>

                                        <div
                                            class="
                                                mt-1
                                                text-sm
                                                font-semibold
                                                text-slate-800
                                            "
                                        >
                                            {{ sourceBranch }}
                                        </div>

                                    </div>


                                    <div>

                                        <div
                                            class="
                                                text-[10px]
                                                font-semibold
                                                uppercase
                                                tracking-[0.12em]
                                                text-slate-400
                                            "
                                        >
                                            Cash & Bank Account
                                        </div>

                                        <div
                                            class="
                                                mt-1
                                                text-sm
                                                font-semibold
                                                text-slate-800
                                            "
                                        >
                                            {{
                                                sourceAccount?.name
                                                ?? '-'
                                            }}
                                        </div>

                                        <div
                                            v-if="sourceAccount?.code"
                                            class="
                                                mt-1
                                                text-xs
                                                text-slate-500
                                            "
                                        >
                                            {{ sourceAccount.code }}
                                        </div>

                                    </div>

                                </div>

                            </div>


                            <!-- To -->

                            <div
                                class="
                                    overflow-hidden
                                    rounded-xl
                                    border
                                    border-slate-200
                                    bg-white
                                "
                            >

                                <div
                                    class="
                                        border-b
                                        border-slate-200
                                        bg-slate-50
                                        px-4
                                        py-3
                                    "
                                >

                                    <div
                                        class="
                                            text-[10px]
                                            font-semibold
                                            uppercase
                                            tracking-[0.12em]
                                            text-slate-400
                                        "
                                    >
                                        To
                                    </div>

                                    <div
                                        class="
                                            mt-1
                                            text-sm
                                            font-bold
                                            text-slate-900
                                        "
                                    >
                                        Destination of Funds
                                    </div>

                                </div>


                                <div class="space-y-4 px-4 py-4">

                                    <div>

                                        <div
                                            class="
                                                text-[10px]
                                                font-semibold
                                                uppercase
                                                tracking-[0.12em]
                                                text-slate-400
                                            "
                                        >
                                            Destination
                                        </div>

                                        <div
                                            class="
                                                mt-1
                                                text-sm
                                                font-semibold
                                                text-slate-800
                                            "
                                        >
                                            {{ destinationBranch }}
                                        </div>

                                    </div>


                                    <div>

                                        <div
                                            class="
                                                text-[10px]
                                                font-semibold
                                                uppercase
                                                tracking-[0.12em]
                                                text-slate-400
                                            "
                                        >
                                            Cash & Bank Account
                                        </div>

                                        <div
                                            class="
                                                mt-1
                                                text-sm
                                                font-semibold
                                                text-slate-800
                                            "
                                        >
                                            {{
                                                destinationAccount?.name
                                                ?? '-'
                                            }}
                                        </div>

                                        <div
                                            v-if="destinationAccount?.code"
                                            class="
                                                mt-1
                                                text-xs
                                                text-slate-500
                                            "
                                        >
                                            {{ destinationAccount.code }}
                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </section>


                    <!-- Current Status -->

                    <section>

                        <div
                            class="
                                flex
                                items-center
                                justify-between
                                rounded-xl
                                border
                                border-slate-200
                                bg-slate-50
                                px-4
                                py-3.5
                            "
                        >

                            <div>

                                <div
                                    class="
                                        flex
                                        items-center
                                        gap-2
                                        text-sm
                                        font-bold
                                        text-slate-800
                                    "
                                >

                                    <span
                                        class="
                                            h-2
                                            w-2
                                            rounded-full
                                            bg-blue-500
                                        "
                                    ></span>

                                    Document Status

                                </div>

                                <div
                                    class="
                                        mt-0.5
                                        text-xs
                                        text-slate-500
                                    "
                                >
                                    Current workflow status.
                                </div>

                            </div>

                            <StatusBadge
                                :status="status"
                            />

                        </div>

                    </section>


                    <!-- Rejection -->

                    <section
                        v-if="
                            status === 'rejected' &&
                            transfer.rejected_reason
                        "
                        class="
                            rounded-xl
                            border
                            border-red-200
                            bg-red-50
                            px-4
                            py-4
                        "
                    >

                        <div
                            class="
                                flex
                                items-center
                                gap-2
                                text-xs
                                font-bold
                                text-red-700
                            "
                        >

                            <span
                                class="
                                    flex
                                    h-5
                                    w-5
                                    items-center
                                    justify-center
                                    rounded-full
                                    bg-red-100
                                "
                            >
                                !
                            </span>

                            Rejection Reason

                        </div>

                        <div
                            class="
                                mt-2
                                whitespace-pre-line
                                text-sm
                                leading-6
                                text-red-800
                            "
                        >
                            {{
                                transfer.rejected_reason
                            }}
                        </div>

                    </section>


                    <!-- Cancellation -->

                    <section
                        v-if="
                            status === 'cancelled' &&
                            transfer.cancelled_reason
                        "
                        class="
                            rounded-xl
                            border
                            border-orange-200
                            bg-orange-50
                            px-4
                            py-4
                        "
                    >

                        <div
                            class="
                                flex
                                items-center
                                gap-2
                                text-xs
                                font-bold
                                text-orange-700
                            "
                        >

                            <span
                                class="
                                    flex
                                    h-5
                                    w-5
                                    items-center
                                    justify-center
                                    rounded-full
                                    bg-orange-100
                                "
                            >
                                !
                            </span>

                            Cancellation Reason

                        </div>

                        <div
                            class="
                                mt-2
                                whitespace-pre-line
                                text-sm
                                leading-6
                                text-orange-800
                            "
                        >
                            {{
                                transfer.cancelled_reason
                            }}
                        </div>

                    </section>

                </div>


                <!-- ================================================= -->
                <!-- Workflow -->
                <!-- ================================================= -->

                <div
                    v-else-if="activeTab === 'workflow'"
                    class="space-y-5"
                >

                    <div>

                        <div
                            class="
                                flex
                                items-center
                                gap-2
                            "
                        >

                            <span
                                class="
                                    h-5
                                    w-1
                                    rounded-full
                                    bg-blue-600
                                "
                            ></span>

                            <h3
                                class="
                                    text-sm
                                    font-bold
                                    text-slate-900
                                "
                            >
                                Workflow Timeline
                            </h3>

                        </div>

                        <p
                            class="
                                mt-1
                                pl-3
                                text-xs
                                text-slate-500
                            "
                        >
                            Status progression and workflow activities for this transfer.
                        </p>

                    </div>


                    <div
                        class="
                            rounded-xl
                            border
                            border-slate-200
                            bg-white
                            p-4
                        "
                    >

                        <WorkflowTimeline
                            :activities="activities"
                        />

                    </div>

                </div>


                <!-- ================================================= -->
                <!-- Audit Timeline -->
                <!-- ================================================= -->

                <div
                    v-else-if="activeTab === 'audit'"
                    class="space-y-5"
                >

                    <div>

                        <div
                            class="
                                flex
                                items-center
                                gap-2
                            "
                        >

                            <span
                                class="
                                    h-5
                                    w-1
                                    rounded-full
                                    bg-blue-600
                                "
                            ></span>

                            <h3
                                class="
                                    text-sm
                                    font-bold
                                    text-slate-900
                                "
                            >
                                Audit Timeline
                            </h3>

                        </div>

                        <p
                            class="
                                mt-1
                                pl-3
                                text-xs
                                text-slate-500
                            "
                        >
                            Complete activity history for this fund transfer.
                        </p>

                    </div>


                    <div
                        class="
                            rounded-xl
                            border
                            border-slate-200
                            bg-white
                            p-4
                        "
                    >

                        <AuditTrail
                            :activities="activities"
                        />

                    </div>

                </div>

            </div>

        </div>


        <!-- ========================================================= -->
        <!-- Footer -->
        <!-- ========================================================= -->

        <template #footer>

            <div
                class="
                    flex
                    w-full
                    flex-col-reverse
                    gap-3
                    sm:flex-row
                    sm:items-center
                    sm:justify-between
                "
            >

                <div
                    class="
                        text-[11px]
                        font-medium
                        tracking-wide
                        text-slate-400
                    "
                >
                    {{ documentNumber }}
                </div>


                <div
                    class="
                        flex
                        flex-wrap
                        justify-end
                        gap-2
                    "
                >

                    <!-- Edit -->

                    <BaseButton
                        v-if="canEdit"
                        variant="secondary"
                        :disabled="processing"
                        @click="emitAction('edit')"
                    >
                        Edit
                    </BaseButton>


                    <!-- Submit -->

                    <BaseButton
                        v-if="canSubmit"
                        variant="primary"
                        :disabled="processing"
                        @click="emitAction('submit')"
                    >
                        Submit
                    </BaseButton>


                    <!-- Approve -->

                    <BaseButton
                        v-if="canApprove"
                        variant="primary"
                        :disabled="processing"
                        @click="emitAction('approve')"
                    >
                        Approve
                    </BaseButton>


                    <!-- Reject -->

                    <BaseButton
                        v-if="canReject"
                        variant="danger"
                        :disabled="processing"
                        @click="emitAction('reject')"
                    >
                        Reject
                    </BaseButton>


                    <!-- Post -->

                    <BaseButton
                        v-if="canPost"
                        variant="primary"
                        :disabled="processing"
                        @click="emitAction('post')"
                    >
                        Post
                    </BaseButton>


                    <!-- Cancel Transaction -->

                    <BaseButton
                        v-if="canCancel"
                        variant="secondary"
                        :disabled="processing"
                        @click="emitAction('cancel')"
                    >
                        Cancel
                    </BaseButton>


                    <!-- Close -->

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