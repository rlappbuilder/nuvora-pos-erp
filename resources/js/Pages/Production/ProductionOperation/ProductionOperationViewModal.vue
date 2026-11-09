<script setup>
import {
    computed,
    ref,
    watch,
} from 'vue'

import BaseModal
    from '@/Components/Modal/BaseModal.vue'

import BaseButton
    from '@/Components/Button/BaseButton.vue'

import StatusBadge
    from '@/Components/Display/StatusBadge.vue'


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

    operation: {
        type: Object,
        default: null,
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

const operationCode = computed(() => {

    return (
        props.operation?.code
        ?? '-'
    )

})


const operationName = computed(() => {

    return (
        props.operation?.name
        ?? '-'
    )

})


const description = computed(() => {

    return (
        props.operation?.description
        ?? '-'
    )

})


const sortOrder = computed(() => {

    return (
        props.operation?.sort_order
        ?? 0
    )

})


const status = computed(() => {

    return Boolean(
        props.operation?.is_active
    )

})


const creatorName = computed(() => {

    return (
        props.operation?.creator?.name
        ?? '-'
    )

})


const updaterName = computed(() => {

    return (
        props.operation?.updater?.name
        ?? '-'
    )

})


/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function parseDate(value) {

    if (!value) {
        return null
    }

    let date = null

    if (typeof value === 'string') {

        date = parseISO(value)

    } else {

        date = new Date(value)

    }

    if (isValid(date)) {
        return date
    }

    const fallback = new Date(value)

    return isValid(fallback)
        ? fallback
        : null

}


function setTab(tab) {

    activeTab.value = tab

}


function emitEdit() {

    if (!props.operation) {
        return
    }

    emit(
        'edit',
        props.operation
    )

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
</script>


<template>

    <BaseModal
        :show="show"
        title="Production Operation"
        size="2xl"
        @close="emit('close')"
    >

        <div class="flex min-h-0 flex-col">


            <!-- ================================================= -->
            <!-- Document Header -->
            <!-- ================================================= -->

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
                        Production Master Data
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
                            {{ operationCode }}
                        </h2>

                        <StatusBadge
                            :status="status"
                        />

                    </div>


                    <div class="mt-1.5 text-xs text-slate-500">

                        Operation Name

                        <span
                            class="
                                ml-1 font-semibold
                                text-slate-700
                            "
                        >
                            {{ operationName }}
                        </span>

                    </div>

                </div>


                <!-- Sort Order -->

                <div
                    class="
                        shrink-0 rounded-xl
                        border border-blue-100
                        bg-blue-50/60 px-5 py-3.5
                        sm:min-w-[190px] sm:text-right
                    "
                >

                    <div
                        class="
                            text-[10px] font-semibold
                            uppercase tracking-[0.14em]
                            text-blue-500
                        "
                    >
                        Sort Order
                    </div>

                    <div
                        class="
                            mt-1 text-xl font-bold
                            tabular-nums text-blue-700
                        "
                    >
                        {{ sortOrder }}
                    </div>

                </div>

            </div>


            <!-- ================================================= -->
            <!-- Tabs -->
            <!-- ================================================= -->

            <div class="mt-5 border-b border-slate-200">

                <nav class="-mb-px flex gap-6 overflow-x-auto">

                    <button
                        v-for="tab in [
                            {
                                key: 'overview',
                                label: 'Overview',
                            },
                            {
                                key: 'workflow',
                                label: 'Workflow',
                            },
                            {
                                key: 'audit',
                                label: 'Audit Timeline',
                            },
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


            <!-- ================================================= -->
            <!-- Content -->
            <!-- ================================================= -->

            <div class="mt-5 min-h-[300px]">


                <!-- ================================================= -->
                <!-- No Data -->
                <!-- ================================================= -->

                <div
                    v-if="!operation"
                    class="
                        flex min-h-[300px]
                        items-center justify-center
                        text-sm text-slate-500
                    "
                >

                    Production operation data is not available.

                </div>


                <!-- ================================================= -->
                <!-- Overview -->
                <!-- ================================================= -->

                <div
                    v-else-if="activeTab === 'overview'"
                    class="space-y-6"
                >


                    <!-- ============================================= -->
                    <!-- Operation Information -->
                    <!-- ============================================= -->

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
                                    Operation Information
                                </h3>

                            </div>

                            <p
                                class="
                                    mt-1 pl-3 text-xs
                                    text-slate-500
                                "
                            >
                                Production operation master information.
                            </p>

                        </div>


                        <div
                            class="
                                overflow-hidden rounded-xl
                                border border-slate-200 bg-white
                            "
                        >

                            <div class="grid grid-cols-1 sm:grid-cols-2">


                                <!-- Operation Code -->

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
                                        Operation Code
                                    </div>

                                    <div
                                        class="
                                            mt-1 text-sm font-bold
                                            text-blue-700
                                        "
                                    >
                                        {{ operationCode }}
                                    </div>

                                </div>


                                <!-- Operation Name -->

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
                                        Operation Name
                                    </div>

                                    <div
                                        class="
                                            mt-1 text-sm font-semibold
                                            text-slate-700
                                        "
                                    >
                                        {{ operationName }}
                                    </div>

                                </div>


                                <!-- Sort Order -->

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
                                        Sort Order
                                    </div>

                                    <div
                                        class="
                                            mt-1 text-sm font-medium
                                            text-slate-700
                                        "
                                    >
                                        {{ sortOrder }}
                                    </div>

                                </div>


                                <!-- Status -->

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
                                        Status
                                    </div>

                                    <div class="mt-1">

                                        <StatusBadge
                                            :status="status"
                                        />

                                    </div>

                                </div>


                                <!-- Description -->

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
                                        {{ description }}
                                    </div>

                                </div>

                            </div>

                        </div>

                    </section>



                    <!-- ============================================= -->
                    <!-- Audit Information -->
                    <!-- ============================================= -->

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
                                    Audit Information
                                </h3>

                            </div>

                            <p
                                class="
                                    mt-1 pl-3 text-xs
                                    text-slate-500
                                "
                            >
                                Record creation and latest update information.
                            </p>

                        </div>


                        <div
                            class="
                                overflow-hidden rounded-xl
                                border border-slate-200 bg-white
                            "
                        >

                            <div class="grid grid-cols-1 sm:grid-cols-2">


                                <!-- Created By -->

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
                                        Created By
                                    </div>

                                    <div
                                        class="
                                            mt-1 text-sm font-medium
                                            text-slate-700
                                        "
                                    >
                                        {{ creatorName }}
                                    </div>

                                </div>


                                <!-- Created At -->

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
                                                operation.created_at
                                            )
                                        }}
                                    </div>

                                </div>


                                <!-- Last Updated By -->

                                <div
                                    class="
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
                                        Last Updated By
                                    </div>

                                    <div
                                        class="
                                            mt-1 text-sm font-medium
                                            text-slate-700
                                        "
                                    >
                                        {{ updaterName }}
                                    </div>

                                </div>


                                <!-- Last Updated At -->

                                <div class="px-4 py-3.5">

                                    <div
                                        class="
                                            text-[10px] font-semibold
                                            uppercase tracking-[0.12em]
                                            text-slate-400
                                        "
                                    >
                                        Last Updated At
                                    </div>

                                    <div
                                        class="
                                            mt-1 text-sm font-medium
                                            text-slate-700
                                        "
                                    >
                                        {{
                                            formatDateTime(
                                                operation.updated_at
                                            )
                                        }}
                                    </div>

                                </div>

                            </div>

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
                                Workflow
                            </h3>

                        </div>

                        <p
                            class="
                                mt-1 pl-3 text-xs
                                text-slate-500
                            "
                        >
                            Production operation status information.
                        </p>

                    </div>


                    <div
                        class="
                            rounded-xl border border-slate-200
                            bg-white p-6
                        "
                    >

                        <div
                            class="
                                flex flex-col items-center
                                justify-center py-8 text-center
                            "
                        >

                            <div
                                class="
                                    flex h-12 w-12
                                    items-center justify-center
                                    rounded-full bg-blue-50
                                    text-blue-600
                                "
                            >

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    class="h-6 w-6"
                                >
                                    <circle
                                        cx="12"
                                        cy="12"
                                        r="9"
                                    />

                                    <path
                                        d="M12 8v4l2.5 2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />

                                </svg>

                            </div>


                            <h4
                                class="
                                    mt-4 text-sm font-semibold
                                    text-slate-800
                                "
                            >
                                No Workflow Activities
                            </h4>

                            <p
                                class="
                                    mt-1 max-w-sm text-xs
                                    leading-5 text-slate-500
                                "
                            >
                                Production Operation is master data
                                and does not currently use a document
                                approval workflow.
                            </p>

                        </div>

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

                        <p
                            class="
                                mt-1 pl-3 text-xs
                                text-slate-500
                            "
                        >
                            Record creation and update history.
                        </p>

                    </div>


                    <div
                        class="
                            rounded-xl border border-slate-200
                            bg-white p-6
                        "
                    >

                        <div
                            class="
                                flex flex-col items-center
                                justify-center py-8 text-center
                            "
                        >

                            <div
                                class="
                                    flex h-12 w-12
                                    items-center justify-center
                                    rounded-full bg-slate-100
                                    text-slate-500
                                "
                            >

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    class="h-6 w-6"
                                >
                                    <path
                                        d="M3 12a9 9 0 1 0 3-6.7"
                                        stroke-linecap="round"
                                    />

                                    <path
                                        d="M3 4v5h5"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />

                                    <path
                                        d="M12 7v5l3 2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />

                                </svg>

                            </div>


                            <h4
                                class="
                                    mt-4 text-sm font-semibold
                                    text-slate-800
                                "
                            >
                                Audit History Not Available
                            </h4>

                            <p
                                class="
                                    mt-1 max-w-sm text-xs
                                    leading-5 text-slate-500
                                "
                            >
                                Detailed audit activities have not been
                                configured for this master data.
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- ================================================= -->
        <!-- Footer -->
        <!-- ================================================= -->

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
                    {{ operationCode }}
                </div>


                <div class="flex flex-wrap justify-end gap-2">

                    <BaseButton
                        variant="secondary"
                        :disabled="processing"
                        @click="emit('close')"
                    >
                        Close
                    </BaseButton>


                    <BaseButton
                        variant="primary"
                        :disabled="processing || !operation"
                        @click="emitEdit"
                    >
                        Edit Operation
                    </BaseButton>

                </div>

            </div>

        </template>

    </BaseModal>

</template>