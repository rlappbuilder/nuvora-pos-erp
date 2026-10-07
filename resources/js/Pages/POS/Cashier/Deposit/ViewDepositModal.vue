<script setup>

import {
    computed,
    ref,
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

    deposit: {
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
    'approve',
    'reject',
    'submit',
    'receive',
    'post',
    'cancel',
])


/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const activeTab = ref('overview')


/*
|--------------------------------------------------------------------------
| Computed
|--------------------------------------------------------------------------
*/

const status = computed(() => {
    return String(
        props.deposit?.status ?? ''
    ).toLowerCase()
})


const activities = computed(() => {

    return (
        props.deposit?.activities
        ?? []
    )

})


/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function formatCurrency(value)
{
    return new Intl.NumberFormat(
        'id-ID',
        {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }
    ).format(
        Number(value || 0)
    )
}


function formatDate(value)
{
    if (!value) {

        return '-'

    }

    return new Intl.DateTimeFormat(
        'id-ID',
        {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        }
    ).format(
        new Date(value)
    )
}


function formatDateTime(value)
{
    if (!value) {

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
    ).format(
        new Date(value)
    )
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
        title="Cash Deposit"
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
                        Cash Deposit
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
                            {{
                                deposit?.deposit_number
                                ?? '-'
                            }}
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
                        Cashier session

                        <span
                            class="
                                ml-1
                                font-semibold
                                text-slate-700
                            "
                        >
                            {{
                                deposit?.session_number
                                ?? '-'
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
                        Deposit Amount
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
                                deposit?.amount
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
                        @click="
                            setTab('overview')
                        "
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
                        @click="
                            setTab('workflow')
                        "
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
                        @click="
                            setTab('audit')
                        "
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

                        <span
                            class="text-sm"
                        >
                            Loading deposit...
                        </span>

                    </div>

                </div>


                <!-- ================================================= -->
                <!-- No Data -->
                <!-- ================================================= -->

                <div
                    v-else-if="!deposit"
                    class="
                        flex
                        min-h-[300px]
                        items-center
                        justify-center
                        text-sm
                        text-slate-500
                    "
                >
                    Cash deposit data is not available.
                </div>


                <!-- ================================================= -->
                <!-- Overview -->
                <!-- ================================================= -->

                <div
                    v-else-if="
                        activeTab === 'overview'
                    "
                    class="space-y-6"
                >

                    <!-- Deposit Information -->

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
                                    Deposit Information
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
                                Transaction and destination
                                information.
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

                                <!-- ================================================= -->
                                <!-- Row 1 - Deposit Number -->
                                <!-- ================================================= -->

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
                                        Deposit Number
                                    </div>

                                    <div
                                        class="
                                            mt-1
                                            text-sm
                                            font-bold
                                            text-blue-700
                                        "
                                    >
                                        {{
                                            deposit.deposit_number
                                            ?? '-'
                                        }}
                                    </div>

                                </div>


                                <!-- ================================================= -->
                                <!-- Row 1 - Cashier Session -->
                                <!-- ================================================= -->

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
                                        Cashier Session
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
                                            deposit.session_number
                                            ?? '-'
                                        }}
                                    </div>

                                </div>


                                <!-- ================================================= -->
                                <!-- Row 2 - Deposit Date -->
                                <!-- ================================================= -->

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
                                        Deposit Date
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
                                                deposit.deposited_at
                                            )
                                        }}
                                    </div>

                                </div>


                                <!-- ================================================= -->
                                <!-- Row 2 - Created At -->
                                <!-- ================================================= -->

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
                                                deposit.created_at
                                            )
                                        }}
                                    </div>

                                </div>


                                <!-- ================================================= -->
                                <!-- Row 3 - Destination Account -->
                                <!-- ================================================= -->

                                <div
                                    class="
                                        border-b
                                        border-slate-100
                                        px-4
                                        py-3.5
                                        sm:border-r
                                        sm:border-b-0
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
                                        Destination Account
                                    </div>

                                    <div
                                        class="
                                            mt-1
                                            flex
                                            flex-wrap
                                            items-baseline
                                            gap-2
                                        "
                                    >

                                        <span
                                            class="
                                                text-sm
                                                font-semibold
                                                text-slate-800
                                            "
                                        >
                                            {{
                                                deposit
                                                    .destination_account
                                                    ?.name
                                                ?? '-'
                                            }}
                                        </span>

                                        <span
                                            class="
                                                rounded-md
                                                bg-slate-100
                                                px-1.5
                                                py-0.5
                                                text-[10px]
                                                font-semibold
                                                text-slate-500
                                            "
                                        >
                                            {{
                                                deposit
                                                    .destination_account
                                                    ?.code
                                                ?? ''
                                            }}
                                        </span>

                                    </div>

                                </div>


                                <!-- ================================================= -->
                                <!-- Row 3 - Note -->
                                <!-- ================================================= -->

                                <div
                                    class="
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
                                        Note
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
                                            deposit.note
                                            ?? '-'
                                        }}
                                    </div>

                                </div>

                            </div>

                        </div>

                    </section>


                    <!-- ================================================= -->
                    <!-- Current Status -->
                    <!-- ================================================= -->

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


                    <!-- ================================================= -->
                    <!-- Rejection -->
                    <!-- ================================================= -->

                    <section
                        v-if="
                            status === 'rejected' &&
                            deposit.rejected_reason
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
                                deposit.rejected_reason
                            }}
                        </div>

                    </section>


                    <!-- ================================================= -->
                    <!-- Cancellation -->
                    <!-- ================================================= -->

                    <section
                        v-if="
                            status === 'cancelled' &&
                            deposit.cancelled_reason
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
                                deposit.cancelled_reason
                            }}
                        </div>

                    </section>

                </div>


                <!-- ================================================= -->
                <!-- Workflow -->
                <!-- ================================================= -->

                <div
                    v-else-if="
                        activeTab === 'workflow'
                    "
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
                            Status progression and workflow
                            activities for this deposit.
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
                    v-else-if="
                        activeTab === 'audit'
                    "
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
                            Complete activity history for
                            this cash deposit.
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
                    {{
                        deposit?.deposit_number
                        ?? ''
                    }}
                </div>


                <div
                    class="
                        flex
                        flex-wrap
                        justify-end
                        gap-2
                    "
                >

                    <!-- Cancel -->

                    <BaseButton
                        v-if="
                            [
                                'draft',
                                'submitted',
                                'approved',
                                'rejected',
                            ].includes(status)
                        "
                        variant="secondary"
                        :disabled="processing"
                        @click="
                            emitAction('cancel')
                        "
                    >
                        Cancel
                    </BaseButton>


                    <!-- Reject -->

                    <BaseButton
                        v-if="
                            status ===
                            'submitted'
                        "
                        variant="danger"
                        :disabled="processing"
                        @click="
                            emitAction('reject')
                        "
                    >
                        Reject
                    </BaseButton>


                    <!-- Approve -->

                    <BaseButton
                        v-if="
                            status ===
                            'submitted'
                        "
                        variant="primary"
                        :disabled="processing"
                        @click="
                            emitAction('approve')
                        "
                    >
                        Approve
                    </BaseButton>


                    <!-- Resubmit -->

                    <BaseButton
                        v-if="
                            status ===
                            'rejected'
                        "
                        variant="primary"
                        :disabled="processing"
                        @click="
                            emitAction('submit')
                        "
                    >
                        Resubmit
                    </BaseButton>


                    <!-- Receive -->

                    <BaseButton
                        v-if="
                            status ===
                            'approved'
                        "
                        variant="primary"
                        :disabled="processing"
                        @click="
                            emitAction('receive')
                        "
                    >
                        Receive
                    </BaseButton>


                    <!-- Post -->

                    <BaseButton
                        v-if="
                            status ===
                            'received'
                        "
                        variant="primary"
                        :disabled="processing"
                        @click="
                            emitAction('post')
                        "
                    >
                        Post
                    </BaseButton>


                    <!-- Close -->

                    <BaseButton
                        variant="secondary"
                        :disabled="processing"
                        @click="
                            emit('close')
                        "
                    >
                        Close
                    </BaseButton>

                </div>

            </div>

        </template>

    </BaseModal>

</template>