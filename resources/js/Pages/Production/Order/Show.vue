<script setup>

import {
    computed,
    ref,
} from 'vue'

import {
    router,
    Link,
} from '@inertiajs/vue3'

import AppLayout from '@/Layouts/AppLayout.vue'

import BaseButton from '@/Components/Button/BaseButton.vue'
import StatusBadge from '@/Components/Display/StatusBadge.vue'

import DataTable from '@/Components/Table/DataTable.vue'
import DataTableHead from '@/Components/Table/DataTableHead.vue'
import DataTableBody from '@/Components/Table/DataTableBody.vue'
import DataTableHeaderCell from '@/Components/Table/DataTableHeaderCell.vue'
import DataTableRow from '@/Components/Table/DataTableRow.vue'
import DataTableCell from '@/Components/Table/DataTableCell.vue'

import WorkflowTimeline
    from '@/Components/Workflow/WorkflowTimeline.vue'

import AuditTrail
    from '@/Components/Workflow/AuditTrail.vue'

import {
    formatDate,
} from '@/Utils'


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    title: {
        type: String,
        default: 'Production Order Detail',
    },

    order: {
        type: Object,
        default: () => ({}),
    },

})


/*
|--------------------------------------------------------------------------
| Tabs
|--------------------------------------------------------------------------
*/

const activeTab = ref('detail')


/*
|--------------------------------------------------------------------------
| Breadcrumb
|--------------------------------------------------------------------------
*/

const breadcrumbs = computed(() => {

    return [
        {
            label: 'Production',
            route: null,
        },
        {
            label: 'Production Orders',
            route: 'production-orders.index',
        },
        {
            label: props.order?.number ?? 'Production Order',
            route: null,
        },
    ]

})


/*
|--------------------------------------------------------------------------
| Data
|--------------------------------------------------------------------------
*/

const materials = computed(() => {
    return props.order?.materials ?? []
})


const operations = computed(() => {
    return props.order?.operations ?? []
})


const results = computed(() => {
    return props.order?.results ?? []
})


const details = computed(() => {
    return props.order?.details ?? null
})


const activities = computed(() => {
    return props.order?.activities ?? []
})


/*
|--------------------------------------------------------------------------
| Statistics
|--------------------------------------------------------------------------
*/

const totalMaterials = computed(() => {
    return materials.value.length
})


const totalOperations = computed(() => {
    return operations.value.length
})


const totalResults = computed(() => {
    return results.value.length
})


const totalRequiredQuantity = computed(() => {

    return materials.value.reduce(
        (total, material) => {
            return total + Number(material.required_quantity || 0)
        },
        0
    )

})


const totalIssuedQuantity = computed(() => {

    return materials.value.reduce(
        (total, material) => {
            return total + Number(material.issued_quantity || 0)
        },
        0
    )

})


const totalReturnedQuantity = computed(() => {

    return materials.value.reduce(
        (total, material) => {
            return total + Number(material.returned_quantity || 0)
        },
        0
    )

})


/*
|--------------------------------------------------------------------------
| Quantity Formatting
|--------------------------------------------------------------------------
*/

function formatQuantity(value)
{

    return new Intl.NumberFormat(
        'id-ID',
        {
            minimumFractionDigits: 0,
            maximumFractionDigits: 6,
        }
    ).format(
        Number(value || 0)
    )

}


function getOutstandingQuantity(material)
{

    const required = Number(
        material?.required_quantity || 0
    )

    const issued = Number(
        material?.issued_quantity || 0
    )

    const returned = Number(
        material?.returned_quantity || 0
    )

    return Math.max(
        0,
        required - issued + returned
    )

}


function getNetIssuedQuantity(material)
{

    const issued = Number(
        material?.issued_quantity || 0
    )

    const returned = Number(
        material?.returned_quantity || 0
    )

    return Math.max(
        0,
        issued - returned
    )

}


function getMaterialUsagePercentage(material)
{

    const required = Number(
        material?.required_quantity || 0
    )

    if (required <= 0) {
        return 0
    }

    return Math.min(
        100,
        (
            getNetIssuedQuantity(material) /
            required
        ) * 100
    )

}


/*
|--------------------------------------------------------------------------
| Display Helpers
|--------------------------------------------------------------------------
*/

function getVariantName(variant)
{

    if (!variant) {
        return '-'
    }

    return (
        variant.product?.name
        ?? variant.name
        ?? '-'
    )

}


function getWarehouseName(warehouse)
{

    if (!warehouse) {
        return '-'
    }

    return warehouse.name ?? '-'

}


function getOperationName(operation)
{

    return (
        operation?.productionOperation?.name
        ?? operation?.production_operation?.name
        ?? '-'
    )

}


function getMaterialOperationName(material)
{

    return (
        material?.productionOrderOperation
            ?.productionOperation?.name
        ?? 'General Material'
    )

}


function getVersionLabel(version)
{

    if (!version) {
        return '-'
    }

    return `Version ${version.version ?? '-'}`

}


function formatDuration(value)
{

    if (
        value === null ||
        value === undefined ||
        value === ''
    ) {
        return '-'
    }

    return `${formatQuantity(value)} min`

}


function formatDateTime(value)
{

    if (!value) {
        return '-'
    }

    return formatDate(value)

}


/*
|--------------------------------------------------------------------------
| Production Progress
|--------------------------------------------------------------------------
*/

const plannedQuantity = computed(() => {

    return Number(
        details.value?.planned_quantity || 0
    )

})


const producedQuantity = computed(() => {

    return Number(
        details.value?.produced_quantity || 0
    )

})


const acceptedQuantity = computed(() => {

    return Number(
        details.value?.accepted_quantity || 0
    )

})


const rejectedQuantity = computed(() => {

    return Number(
        details.value?.rejected_quantity || 0
    )

})


const productionProgress = computed(() => {

    if (plannedQuantity.value <= 0) {
        return 0
    }

    return Math.min(
        100,
        (
            producedQuantity.value /
            plannedQuantity.value
        ) * 100
    )

})


const acceptedProgress = computed(() => {

    if (plannedQuantity.value <= 0) {
        return 0
    }

    return Math.min(
        100,
        (
            acceptedQuantity.value /
            plannedQuantity.value
        ) * 100
    )

})


/*
|--------------------------------------------------------------------------
| Workflow Status
|--------------------------------------------------------------------------
*/

const currentStatus = computed(() => {

    return props.order?.status ?? 'Draft'

})


const isDraft = computed(() => {
    return currentStatus.value === 'Draft'
})


const isSubmitted = computed(() => {
    return currentStatus.value === 'Submitted'
})


const isApproved = computed(() => {
    return currentStatus.value === 'Approved'
})


const isInProgress = computed(() => {
    return currentStatus.value === 'In Progress'
})


const isCompleted = computed(() => {
    return currentStatus.value === 'Completed'
})


const isRejected = computed(() => {
    return currentStatus.value === 'Rejected'
})


const isCancelled = computed(() => {
    return currentStatus.value === 'Cancelled'
})


const isReadOnly = computed(() => {

    return (
        isCompleted.value ||
        isRejected.value ||
        isCancelled.value
    )

})


/*
|--------------------------------------------------------------------------
| Workflow Actions
|--------------------------------------------------------------------------
*/

const processing = ref(false)


function back()
{

    router.get(
        route('production-orders.index')
    )

}


function editOrder()
{

    if (!props.order?.id || !isDraft.value) {
        return
    }

    router.get(
        route(
            'production-orders.edit',
            props.order.id
        )
    )

}


function submitOrder()
{

    if (!props.order?.id || !isDraft.value) {
        return
    }

    if (!window.confirm(
        'Submit this Production Order for approval?'
    )) {
        return
    }

    processing.value = true

    router.post(
        route(
            'production-orders.submit',
            props.order.id
        ),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                processing.value = false
            },
        }
    )

}


function approveOrder()
{

    if (!props.order?.id || !isSubmitted.value) {
        return
    }

    if (!window.confirm(
        'Approve this Production Order?'
    )) {
        return
    }

    processing.value = true

    router.post(
        route(
            'production-orders.approve',
            props.order.id
        ),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                processing.value = false
            },
        }
    )

}


function startProduction()
{

    if (!props.order?.id || !isApproved.value) {
        return
    }

    if (!window.confirm(
        'Start production for this Production Order?'
    )) {
        return
    }

    processing.value = true

    router.post(
        route(
            'production-orders.start-production',
            props.order.id
        ),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                processing.value = false
            },
        }
    )

}


/*
|--------------------------------------------------------------------------
| Reject Modal
|--------------------------------------------------------------------------
*/

const showRejectModal = ref(false)

const rejectReason = ref('')


function openReject()
{

    if (!isSubmitted.value) {
        return
    }

    rejectReason.value = ''

    showRejectModal.value = true

}


function closeReject()
{

    if (processing.value) {
        return
    }

    showRejectModal.value = false

    rejectReason.value = ''

}


function confirmReject()
{

    if (
        !props.order?.id ||
        !isSubmitted.value ||
        !rejectReason.value.trim()
    ) {
        return
    }

    processing.value = true

    router.post(
        route(
            'production-orders.reject',
            props.order.id
        ),
        {
            rejected_reason: rejectReason.value.trim(),
        },
        {
            preserveScroll: true,

            onSuccess: () => {
                showRejectModal.value = false
                rejectReason.value = ''
            },

            onFinish: () => {
                processing.value = false
            },
        }
    )

}


/*
|--------------------------------------------------------------------------
| Cancel Modal
|--------------------------------------------------------------------------
*/

const showCancelModal = ref(false)

const cancelReason = ref('')


function openCancel()
{

    if (
        !isDraft.value &&
        !isSubmitted.value &&
        !isApproved.value
    ) {
        return
    }

    cancelReason.value = ''

    showCancelModal.value = true

}


function closeCancel()
{

    if (processing.value) {
        return
    }

    showCancelModal.value = false

    cancelReason.value = ''

}


function confirmCancel()
{

    if (
        !props.order?.id ||
        !cancelReason.value.trim()
    ) {
        return
    }

    processing.value = true

    router.post(
        route(
            'production-orders.cancel',
            props.order.id
        ),
        {
            cancelled_reason: cancelReason.value.trim(),
        },
        {
            preserveScroll: true,

            onSuccess: () => {
                showCancelModal.value = false
                cancelReason.value = ''
            },

            onFinish: () => {
                processing.value = false
            },
        }
    )

}


/*
|--------------------------------------------------------------------------
| Record Information
|--------------------------------------------------------------------------
*/

const creatorName = computed(() => {
    return props.order?.creator?.name ?? '-'
})


const updaterName = computed(() => {
    return props.order?.updater?.name ?? '-'
})


const submitterName = computed(() => {
    return props.order?.submitter?.name ?? '-'
})


const approverName = computed(() => {
    return props.order?.approver?.name ?? '-'
})


const rejectorName = computed(() => {
    return props.order?.rejector?.name ?? '-'
})


const cancellerName = computed(() => {
    return props.order?.canceller?.name ?? '-'
})


const deleterName = computed(() => {
    return props.order?.deleter?.name ?? '-'
})

</script>


<template>

<AppLayout>

    <div class="space-y-4">

        <!-- ========================================================= -->
        <!-- Breadcrumb -->
        <!-- ========================================================= -->

        <div
            class="
                flex
                flex-wrap
                items-center
                gap-2
                text-sm
                text-gray-500
            "
        >

            <template
                v-for="(item, index) in breadcrumbs"
                :key="index"
            >

                <Link
                    v-if="item.route"
                    :href="route(item.route)"
                    class="transition hover:text-blue-600"
                >
                    {{ item.label }}
                </Link>

                <span
                    v-else
                    class="font-medium text-gray-800"
                >
                    {{ item.label }}
                </span>

                <span
                    v-if="index < breadcrumbs.length - 1"
                    class="text-gray-300"
                >
                    /
                </span>

            </template>

        </div>


        <!-- ========================================================= -->
        <!-- Header -->
        <!-- ========================================================= -->

        <div
            class="
                flex
                flex-col
                gap-3
                sm:flex-row
                sm:items-center
                sm:justify-between
            "
        >

            <div>

                <h1 class="text-xl font-semibold text-gray-900">
                    Production Order
                </h1>

                <p class="mt-1 text-sm text-gray-500">
                    Production planning, material requirements, and execution tracking.
                </p>

            </div>


            <div class="flex flex-wrap items-center gap-2">

                <BaseButton
                    variant="secondary"
                    @click="back"
                >
                    Back
                </BaseButton>


                <BaseButton
                    v-if="isDraft"
                    variant="secondary"
                    @click="editOrder"
                >
                    Edit
                </BaseButton>


                <BaseButton
                    v-if="isDraft"
                    :disabled="processing"
                    @click="submitOrder"
                >
                    Submit
                </BaseButton>


                <BaseButton
                    v-if="isSubmitted"
                    :disabled="processing"
                    @click="approveOrder"
                >
                    Approve
                </BaseButton>


                <BaseButton
                    v-if="isSubmitted"
                    variant="danger"
                    :disabled="processing"
                    @click="openReject"
                >
                    Reject
                </BaseButton>


                <BaseButton
                    v-if="isApproved"
                    :disabled="processing"
                    @click="startProduction"
                >
                    Start Production
                </BaseButton>


                <BaseButton
                    v-if="
                        isDraft ||
                        isSubmitted ||
                        isApproved
                    "
                    variant="danger"
                    :disabled="processing"
                    @click="openCancel"
                >
                    Cancel Order
                </BaseButton>

            </div>

        </div>


        <!-- ========================================================= -->
        <!-- Summary -->
        <!-- ========================================================= -->

        <div
            class="
                overflow-hidden
                rounded-xl
                border
                border-gray-100
                bg-white
                shadow-sm
            "
        >

            <div
                class="
                    grid
                    grid-cols-1
                    divide-y
                    divide-gray-100
                    sm:grid-cols-2
                    sm:divide-x
                    sm:divide-y-0
                    lg:grid-cols-4
                "
            >

                <!-- Order Number -->

                <div class="px-4 py-4">

                    <div class="text-xs font-medium text-gray-500">
                        Production Order
                    </div>

                    <div class="mt-1 font-semibold text-gray-900">
                        {{ order.number ?? '-' }}
                    </div>

                    <div class="mt-0.5 text-xs text-gray-500">
                        {{ order.order_date ? formatDate(order.order_date) : '-' }}
                    </div>

                </div>


                <!-- Output Product -->

                <div class="px-4 py-4">

                    <div class="text-xs font-medium text-gray-500">
                        Output Product
                    </div>

                    <div class="mt-1 font-semibold text-gray-900">
                        {{
                            getVariantName(
                                details?.product_variant
                            )
                        }}
                    </div>

                    <div class="mt-0.5 text-xs text-gray-500">
                        SKU: {{ details?.product_variant?.sku ?? '-' }}
                    </div>

                </div>


                <!-- Branch -->

                <div class="px-4 py-4">

                    <div class="text-xs font-medium text-gray-500">
                        Branch
                    </div>

                    <div class="mt-1 font-semibold text-gray-900">
                        {{ order.branch?.name ?? '-' }}
                    </div>

                    <div class="mt-0.5 text-xs text-gray-500">
                        BOM: {{ order.production_bom?.code ?? '-' }}
                    </div>

                </div>


                <!-- Status -->

                <div class="px-4 py-4">

                    <div class="text-xs font-medium text-gray-500">
                        Production Order Status
                    </div>

                    <div class="mt-2">
                        <StatusBadge :status="order.status" />
                    </div>

                </div>

            </div>

        </div>


        <!-- ========================================================= -->
        <!-- Production Progress -->
        <!-- ========================================================= -->

        <div
            class="
                rounded-xl
                border
                border-gray-100
                bg-white
                p-4
                shadow-sm
            "
        >

            <div
                class="
                    flex
                    flex-col
                    gap-3
                    sm:flex-row
                    sm:items-center
                    sm:justify-between
                "
            >

                <div>

                    <div class="text-sm font-semibold text-gray-900">
                        Production Progress
                    </div>

                    <div class="mt-0.5 text-xs text-gray-500">
                        Actual production quantity against planned quantity.
                    </div>

                </div>


                <div class="text-sm font-semibold text-gray-900">
                    {{ formatQuantity(producedQuantity) }}
                    /
                    {{ formatQuantity(plannedQuantity) }}
                    {{ details?.unit?.name ?? '' }}
                </div>

            </div>


            <div class="mt-4 h-2 overflow-hidden rounded-full bg-gray-100">

                <div
                    class="h-full rounded-full bg-blue-600 transition-all"
                    :style="{
                        width: `${productionProgress}%`
                    }"
                />

            </div>


            <div
                class="
                    mt-2
                    flex
                    flex-wrap
                    justify-between
                    gap-2
                    text-xs
                    text-gray-500
                "
            >

                <span>
                    {{ formatQuantity(productionProgress) }}% Produced
                </span>

                <span>
                    {{ formatQuantity(acceptedQuantity) }}
                    {{ details?.unit?.name ?? '' }}
                    Accepted
                </span>

            </div>


            <div
                class="
                    mt-4
                    grid
                    grid-cols-1
                    gap-3
                    sm:grid-cols-3
                "
            >

                <div class="rounded-lg bg-gray-50 p-3">

                    <div class="text-xs text-gray-500">
                        Planned Quantity
                    </div>

                    <div class="mt-1 text-lg font-semibold text-gray-900">
                        {{ formatQuantity(plannedQuantity) }}
                    </div>

                </div>


                <div class="rounded-lg bg-gray-50 p-3">

                    <div class="text-xs text-gray-500">
                        Produced Quantity
                    </div>

                    <div class="mt-1 text-lg font-semibold text-gray-900">
                        {{ formatQuantity(producedQuantity) }}
                    </div>

                </div>


                <div class="rounded-lg bg-gray-50 p-3">

                    <div class="text-xs text-gray-500">
                        Accepted Quantity
                    </div>

                    <div class="mt-1 text-lg font-semibold text-gray-900">
                        {{ formatQuantity(acceptedQuantity) }}
                    </div>

                </div>

            </div>

        </div>


        <!-- ========================================================= -->
        <!-- Tabs -->
        <!-- ========================================================= -->

        <div
            class="
                overflow-hidden
                rounded-xl
                border
                border-gray-100
                bg-white
                shadow-sm
            "
        >

            <!-- Tab Navigation -->

            <div class="flex overflow-x-auto border-b border-gray-100">

                <button
                    type="button"
                    class="
                        whitespace-nowrap
                        border-b-2
                        px-5
                        py-3
                        text-sm
                        font-medium
                        transition
                    "
                    :class="
                        activeTab === 'detail'
                            ? 'border-blue-600 text-blue-600'
                            : 'border-transparent text-gray-500 hover:text-gray-700'
                    "
                    @click="activeTab = 'detail'"
                >
                    Order Detail
                </button>


                <button
                    type="button"
                    class="
                        whitespace-nowrap
                        border-b-2
                        px-5
                        py-3
                        text-sm
                        font-medium
                        transition
                    "
                    :class="
                        activeTab === 'materials'
                            ? 'border-blue-600 text-blue-600'
                            : 'border-transparent text-gray-500 hover:text-gray-700'
                    "
                    @click="activeTab = 'materials'"
                >
                    Material & Routing
                </button>


                <button
                    type="button"
                    class="
                        whitespace-nowrap
                        border-b-2
                        px-5
                        py-3
                        text-sm
                        font-medium
                        transition
                    "
                    :class="
                        activeTab === 'results'
                            ? 'border-blue-600 text-blue-600'
                            : 'border-transparent text-gray-500 hover:text-gray-700'
                    "
                    @click="activeTab = 'results'"
                >
                    Production Results
                    <span class="ml-1 text-xs text-gray-400">
                        ({{ totalResults }})
                    </span>
                </button>


                <button
                    type="button"
                    class="
                        whitespace-nowrap
                        border-b-2
                        px-5
                        py-3
                        text-sm
                        font-medium
                        transition
                    "
                    :class="
                        activeTab === 'workflow'
                            ? 'border-blue-600 text-blue-600'
                            : 'border-transparent text-gray-500 hover:text-gray-700'
                    "
                    @click="activeTab = 'workflow'"
                >
                    Workflow Timeline
                </button>


                <button
                    type="button"
                    class="
                        whitespace-nowrap
                        border-b-2
                        px-5
                        py-3
                        text-sm
                        font-medium
                        transition
                    "
                    :class="
                        activeTab === 'audit'
                            ? 'border-blue-600 text-blue-600'
                            : 'border-transparent text-gray-500 hover:text-gray-700'
                    "
                    @click="activeTab = 'audit'"
                >
                    Audit Trail
                </button>

            </div>


            <!-- ===================================================== -->
            <!-- Order Detail Tab -->
            <!-- ===================================================== -->

            <div
                v-if="activeTab === 'detail'"
                class="space-y-4 p-4"
            >

                <!-- General Information -->

                <div
                    class="
                        rounded-xl
                        border
                        border-gray-100
                        bg-white
                    "
                >

                    <div class="border-b border-gray-100 px-4 py-3">

                        <div class="text-sm font-semibold text-gray-900">
                            Production Order Information
                        </div>

                    </div>


                    <div
                        class="
                            grid
                            grid-cols-1
                            gap-4
                            p-4
                            sm:grid-cols-2
                            lg:grid-cols-3
                        "
                    >

                        <div>

                            <div class="text-xs text-gray-500">
                                Order Number
                            </div>

                            <div class="mt-1 text-sm font-medium text-gray-900">
                                {{ order.number ?? '-' }}
                            </div>

                        </div>


                        <div>

                            <div class="text-xs text-gray-500">
                                Order Date
                            </div>

                            <div class="mt-1 text-sm font-medium text-gray-900">
                                {{ order.order_date ? formatDate(order.order_date) : '-' }}
                            </div>

                        </div>


                        <div>

                            <div class="text-xs text-gray-500">
                                Status
                            </div>

                            <div class="mt-1">
                                <StatusBadge :status="order.status" />
                            </div>

                        </div>


                        <div>

                            <div class="text-xs text-gray-500">
                                Production BOM
                            </div>

                            <div class="mt-1 text-sm font-medium text-gray-900">
                                {{ order.production_bom?.code ?? '-' }}
                            </div>

                            <div class="text-xs text-gray-500">
                                {{ order.production_bom?.name ?? '-' }}
                            </div>

                        </div>


                        <div>

                            <div class="text-xs text-gray-500">
                                BOM Version
                            </div>

                            <div class="mt-1 text-sm font-medium text-gray-900">
                                {{
                                    getVersionLabel(
                                        order.production_bom_version
                                    )
                                }}
                            </div>

                        </div>


                        <div>

                            <div class="text-xs text-gray-500">
                                Branch
                            </div>

                            <div class="mt-1 text-sm font-medium text-gray-900">
                                {{ order.branch?.name ?? '-' }}
                            </div>

                        </div>


                        <div>

                            <div class="text-xs text-gray-500">
                                Source Warehouse
                            </div>

                            <div class="mt-1 text-sm font-medium text-gray-900">
                                {{ getWarehouseName(order.source_warehouse) }}
                            </div>

                        </div>


                        <div>

                            <div class="text-xs text-gray-500">
                                Destination Warehouse
                            </div>

                            <div class="mt-1 text-sm font-medium text-gray-900">
                                {{ getWarehouseName(order.destination_warehouse) }}
                            </div>

                        </div>


                        <div>

                            <div class="text-xs text-gray-500">
                                Planned Quantity
                            </div>

                            <div class="mt-1 text-sm font-medium text-gray-900">
                                {{ formatQuantity(plannedQuantity) }}
                                {{ details?.unit?.name ?? '' }}
                            </div>

                        </div>


                        <div>

                            <div class="text-xs text-gray-500">
                                Planned Start Date
                            </div>

                            <div class="mt-1 text-sm font-medium text-gray-900">
                                {{
                                    order.planned_start_date
                                        ? formatDate(order.planned_start_date)
                                        : '-'
                                }}
                            </div>

                        </div>


                        <div>

                            <div class="text-xs text-gray-500">
                                Planned End Date
                            </div>

                            <div class="mt-1 text-sm font-medium text-gray-900">
                                {{
                                    order.planned_end_date
                                        ? formatDate(order.planned_end_date)
                                        : '-'
                                }}
                            </div>

                        </div>


                        <div>

                            <div class="text-xs text-gray-500">
                                Actual Start Date
                            </div>

                            <div class="mt-1 text-sm font-medium text-gray-900">
                                {{
                                    order.actual_start_date
                                        ? formatDate(order.actual_start_date)
                                        : '-'
                                }}
                            </div>

                        </div>


                        <div>

                            <div class="text-xs text-gray-500">
                                Actual End Date
                            </div>

                            <div class="mt-1 text-sm font-medium text-gray-900">
                                {{
                                    order.actual_end_date
                                        ? formatDate(order.actual_end_date)
                                        : '-'
                                }}
                            </div>

                        </div>


                        <div
                            v-if="order.description"
                            class="sm:col-span-2 lg:col-span-3"
                        >

                            <div class="text-xs text-gray-500">
                                Description
                            </div>

                            <div
                                class="
                                    mt-1
                                    whitespace-pre-line
                                    text-sm
                                    text-gray-700
                                "
                            >
                                {{ order.description }}
                            </div>

                        </div>

                    </div>

                </div>


                <!-- Rejection / Cancellation Information -->

                <div
                    v-if="isRejected || isCancelled"
                    class="
                        rounded-xl
                        border
                        border-gray-100
                        bg-white
                        p-4
                    "
                >

                    <div class="text-sm font-semibold text-gray-900">
                        {{ isRejected ? 'Rejection Information' : 'Cancellation Information' }}
                    </div>


                    <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2">

                        <div>

                            <div class="text-xs text-gray-500">
                                {{ isRejected ? 'Rejected By' : 'Cancelled By' }}
                            </div>

                            <div class="mt-1 text-sm font-medium text-gray-900">
                                {{ isRejected ? rejectorName : cancellerName }}
                            </div>

                        </div>


                        <div>

                            <div class="text-xs text-gray-500">
                                {{ isRejected ? 'Rejected At' : 'Cancelled At' }}
                            </div>

                            <div class="mt-1 text-sm font-medium text-gray-900">
                                {{
                                    isRejected
                                        ? formatDateTime(order.rejected_at)
                                        : formatDateTime(order.cancelled_at)
                                }}
                            </div>

                        </div>


                        <div class="sm:col-span-2">

                            <div class="text-xs text-gray-500">
                                Reason
                            </div>

                            <div class="mt-1 whitespace-pre-line text-sm text-gray-700">
                                {{
                                    isRejected
                                        ? order.rejected_reason ?? '-'
                                        : order.cancelled_reason ?? '-'
                                }}
                            </div>

                        </div>

                    </div>

                </div>


                <!-- Approval Information -->

                <div
                    v-if="order.approved_at || order.submitted_at"
                    class="
                        rounded-xl
                        border
                        border-gray-100
                        bg-white
                        p-4
                    "
                >

                    <div class="text-sm font-semibold text-gray-900">
                        Workflow Information
                    </div>


                    <div
                        class="
                            mt-3
                            grid
                            grid-cols-1
                            gap-4
                            sm:grid-cols-2
                        "
                    >

                        <div>

                            <div class="text-xs text-gray-500">
                                Submitted By
                            </div>

                            <div class="mt-1 text-sm font-medium text-gray-900">
                                {{ submitterName }}
                            </div>

                            <div class="text-xs text-gray-500">
                                {{ formatDateTime(order.submitted_at) }}
                            </div>

                        </div>


                        <div>

                            <div class="text-xs text-gray-500">
                                Approved By
                            </div>

                            <div class="mt-1 text-sm font-medium text-gray-900">
                                {{ approverName }}
                            </div>

                            <div class="text-xs text-gray-500">
                                {{ formatDateTime(order.approved_at) }}
                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- ===================================================== -->
            <!-- Material & Routing Tab -->
            <!-- ===================================================== -->

            <div
                v-else-if="activeTab === 'materials'"
                class="space-y-4 p-4"
            >

                <!-- Material Summary -->

                <div
                    class="
                        grid
                        grid-cols-1
                        gap-3
                        sm:grid-cols-2
                        lg:grid-cols-4
                    "
                >

                    <div class="rounded-lg border border-gray-100 p-3">

                        <div class="text-xs text-gray-500">
                            Total Materials
                        </div>

                        <div class="mt-1 text-lg font-semibold text-gray-900">
                            {{ totalMaterials }}
                        </div>

                    </div>


                    <div class="rounded-lg border border-gray-100 p-3">

                        <div class="text-xs text-gray-500">
                            Required Quantity
                        </div>

                        <div class="mt-1 text-lg font-semibold text-gray-900">
                            {{ formatQuantity(totalRequiredQuantity) }}
                        </div>

                    </div>


                    <div class="rounded-lg border border-gray-100 p-3">

                        <div class="text-xs text-gray-500">
                            Issued Quantity
                        </div>

                        <div class="mt-1 text-lg font-semibold text-gray-900">
                            {{ formatQuantity(totalIssuedQuantity) }}
                        </div>

                    </div>


                    <div class="rounded-lg border border-gray-100 p-3">

                        <div class="text-xs text-gray-500">
                            Returned Quantity
                        </div>

                        <div class="mt-1 text-lg font-semibold text-gray-900">
                            {{ formatQuantity(totalReturnedQuantity) }}
                        </div>

                    </div>

                </div>


                <!-- Material Snapshot -->

                <div
                    class="
                        overflow-hidden
                        rounded-xl
                        border
                        border-gray-100
                        bg-white
                    "
                >

                    <div class="border-b border-gray-100 px-4 py-3">

                        <div class="text-sm font-semibold text-gray-900">
                            Material Requirements
                        </div>

                        <div class="mt-0.5 text-xs text-gray-500">
                            Snapshot of BOM materials when this Production Order was created.
                        </div>

                    </div>


                    <div
                        v-if="materials.length"
                        class="overflow-x-auto"
                    >

                        <DataTable>

                            <DataTableHead>

                                <DataTableHeaderCell
                                    width="50px"
                                    align="center"
                                >
                                    #
                                </DataTableHeaderCell>


                                <DataTableHeaderCell width="220px">
                                    Material
                                </DataTableHeaderCell>


                                <DataTableHeaderCell width="170px">
                                    SKU
                                </DataTableHeaderCell>


                                <DataTableHeaderCell
                                    width="130px"
                                    align="right"
                                >
                                    BOM Qty
                                </DataTableHeaderCell>


                                <DataTableHeaderCell
                                    width="100px"
                                    align="right"
                                >
                                    Scrap %
                                </DataTableHeaderCell>


                                <DataTableHeaderCell
                                    width="140px"
                                    align="right"
                                >
                                    Required
                                </DataTableHeaderCell>


                                <DataTableHeaderCell
                                    width="130px"
                                    align="right"
                                >
                                    Issued
                                </DataTableHeaderCell>


                                <DataTableHeaderCell
                                    width="130px"
                                    align="right"
                                >
                                    Returned
                                </DataTableHeaderCell>


                                <DataTableHeaderCell
                                    width="140px"
                                    align="right"
                                >
                                    Net Issued
                                </DataTableHeaderCell>


                                <DataTableHeaderCell
                                    width="140px"
                                    align="right"
                                >
                                    Outstanding
                                </DataTableHeaderCell>


                                <DataTableHeaderCell width="160px">
                                    Operation
                                </DataTableHeaderCell>

                            </DataTableHead>


                            <DataTableBody>

                                <DataTableRow
                                    v-for="(material, index) in materials"
                                    :key="material.id ?? index"
                                >

                                    <DataTableCell align="center">

                                        <span class="text-xs text-gray-500">
                                            {{ index + 1 }}
                                        </span>

                                    </DataTableCell>


                                    <DataTableCell>

                                        <div class="font-medium text-gray-900">
                                            {{
                                                getVariantName(
                                                    material.component_variant
                                                )
                                            }}
                                        </div>

                                    </DataTableCell>


                                    <DataTableCell>

                                        <span class="text-sm text-gray-600">
                                            {{ material.component_variant?.sku ?? '-' }}
                                        </span>

                                    </DataTableCell>


                                    <DataTableCell align="right">

                                        <span class="tabular-nums text-gray-700">
                                            {{ formatQuantity(material.bom_qty) }}
                                        </span>

                                    </DataTableCell>


                                    <DataTableCell align="right">

                                        <span class="tabular-nums text-gray-700">
                                            {{ formatQuantity(material.scrap_percent) }}%
                                        </span>

                                    </DataTableCell>


                                    <DataTableCell align="right">

                                        <span class="font-medium tabular-nums text-gray-900">
                                            {{ formatQuantity(material.required_quantity) }}
                                        </span>

                                        <div class="text-xs text-gray-500">
                                            {{ material.unit?.name ?? '' }}
                                        </div>

                                    </DataTableCell>


                                    <DataTableCell align="right">

                                        <span class="tabular-nums text-gray-700">
                                            {{ formatQuantity(material.issued_quantity) }}
                                        </span>

                                    </DataTableCell>


                                    <DataTableCell align="right">

                                        <span class="tabular-nums text-gray-700">
                                            {{ formatQuantity(material.returned_quantity) }}
                                        </span>

                                    </DataTableCell>


                                    <DataTableCell align="right">

                                        <span class="font-medium tabular-nums text-gray-900">
                                            {{ formatQuantity(getNetIssuedQuantity(material)) }}
                                        </span>

                                    </DataTableCell>


                                    <DataTableCell align="right">

                                        <span class="font-medium tabular-nums text-gray-900">
                                            {{ formatQuantity(getOutstandingQuantity(material)) }}
                                        </span>

                                    </DataTableCell>


                                    <DataTableCell>

                                        <span class="text-sm text-gray-600">
                                            {{ getMaterialOperationName(material) }}
                                        </span>

                                    </DataTableCell>

                                </DataTableRow>

                            </DataTableBody>

                        </DataTable>

                    </div>


                    <div
                        v-else
                        class="
                            px-4
                            py-8
                            text-center
                            text-sm
                            text-gray-500
                        "
                    >
                        No material requirements found.
                    </div>

                </div>


                <!-- Production Routing -->

                <div
                    class="
                        overflow-hidden
                        rounded-xl
                        border
                        border-gray-100
                        bg-white
                    "
                >

                    <div class="border-b border-gray-100 px-4 py-3">

                        <div class="text-sm font-semibold text-gray-900">
                            Production Routing
                        </div>

                        <div class="mt-0.5 text-xs text-gray-500">
                            {{ totalOperations }} Production Operations
                        </div>

                    </div>


                    <div
                        v-if="operations.length"
                        class="overflow-x-auto"
                    >

                        <DataTable>

                            <DataTableHead>

                                <DataTableHeaderCell
                                    width="60px"
                                    align="center"
                                >
                                    #
                                </DataTableHeaderCell>


                                <DataTableHeaderCell width="220px">
                                    Production Operation
                                </DataTableHeaderCell>


                                <DataTableHeaderCell
                                    width="150px"
                                    align="right"
                                >
                                    Planned Duration
                                </DataTableHeaderCell>


                                <DataTableHeaderCell width="350px">
                                    Instructions
                                </DataTableHeaderCell>

                            </DataTableHead>


                            <DataTableBody>

                                <DataTableRow
                                    v-for="(operation, index) in operations"
                                    :key="operation.id ?? index"
                                >

                                    <DataTableCell align="center">

                                        <span class="text-xs text-gray-500">
                                            {{ operation.sequence ?? index + 1 }}
                                        </span>

                                    </DataTableCell>


                                    <DataTableCell>

                                        <span class="font-medium text-gray-900">
                                            {{ getOperationName(operation) }}
                                        </span>

                                    </DataTableCell>


                                    <DataTableCell align="right">

                                        <span class="tabular-nums text-gray-700">
                                            {{ formatDuration(operation.planned_duration_minutes) }}
                                        </span>

                                    </DataTableCell>


                                    <DataTableCell>

                                        <span class="whitespace-pre-line text-sm text-gray-600">
                                            {{ operation.instructions ?? '-' }}
                                        </span>

                                    </DataTableCell>

                                </DataTableRow>

                            </DataTableBody>

                        </DataTable>

                    </div>


                    <div
                        v-else
                        class="
                            px-4
                            py-8
                            text-center
                            text-sm
                            text-gray-500
                        "
                    >
                        No production operations.
                    </div>

                </div>

            </div>


            <!-- ===================================================== -->
            <!-- Production Results Tab -->
            <!-- ===================================================== -->

            <div
                v-else-if="activeTab === 'results'"
                class="space-y-4 p-4"
            >

                <div
                    class="
                        grid
                        grid-cols-1
                        gap-3
                        sm:grid-cols-2
                        lg:grid-cols-4
                    "
                >

                    <div class="rounded-lg border border-gray-100 p-3">

                        <div class="text-xs text-gray-500">
                            Production Results
                        </div>

                        <div class="mt-1 text-lg font-semibold text-gray-900">
                            {{ totalResults }}
                        </div>

                    </div>


                    <div class="rounded-lg border border-gray-100 p-3">

                        <div class="text-xs text-gray-500">
                            Produced
                        </div>

                        <div class="mt-1 text-lg font-semibold text-gray-900">
                            {{ formatQuantity(producedQuantity) }}
                        </div>

                    </div>


                    <div class="rounded-lg border border-gray-100 p-3">

                        <div class="text-xs text-gray-500">
                            Accepted
                        </div>

                        <div class="mt-1 text-lg font-semibold text-gray-900">
                            {{ formatQuantity(acceptedQuantity) }}
                        </div>

                    </div>


                    <div class="rounded-lg border border-gray-100 p-3">

                        <div class="text-xs text-gray-500">
                            Rejected
                        </div>

                        <div class="mt-1 text-lg font-semibold text-gray-900">
                            {{ formatQuantity(rejectedQuantity) }}
                        </div>

                    </div>

                </div>


                <div
                    class="
                        overflow-hidden
                        rounded-xl
                        border
                        border-gray-100
                        bg-white
                    "
                >

                    <div class="border-b border-gray-100 px-4 py-3">

                        <div class="text-sm font-semibold text-gray-900">
                            Production Result History
                        </div>

                        <div class="mt-0.5 text-xs text-gray-500">
                            Production output transactions linked to this order.
                        </div>

                    </div>


                    <div
                        v-if="results.length"
                        class="overflow-x-auto"
                    >

                        <DataTable>

                            <DataTableHead>

                                <DataTableHeaderCell width="160px">
                                    Result Number
                                </DataTableHeaderCell>


                                <DataTableHeaderCell width="130px">
                                    Result Date
                                </DataTableHeaderCell>


                                <DataTableHeaderCell width="120px">
                                    Status
                                </DataTableHeaderCell>


                                <DataTableHeaderCell
                                    width="140px"
                                    align="right"
                                >
                                    Produced
                                </DataTableHeaderCell>


                                <DataTableHeaderCell
                                    width="140px"
                                    align="right"
                                >
                                    Accepted
                                </DataTableHeaderCell>


                                <DataTableHeaderCell
                                    width="140px"
                                    align="right"
                                >
                                    Rejected
                                </DataTableHeaderCell>


                                <DataTableHeaderCell width="150px">
                                    QC Inspected By
                                </DataTableHeaderCell>

                            </DataTableHead>


                            <DataTableBody>

                                <DataTableRow
                                    v-for="result in results"
                                    :key="result.id"
                                >

                                    <DataTableCell>

                                        <span class="font-medium text-gray-900">
                                            {{ result.number ?? '-' }}
                                        </span>

                                    </DataTableCell>


                                    <DataTableCell>

                                        <span class="text-sm text-gray-600">
                                            {{
                                                result.result_date
                                                    ? formatDate(result.result_date)
                                                    : '-'
                                            }}
                                        </span>

                                    </DataTableCell>


                                    <DataTableCell>

                                        <StatusBadge :status="result.status" />

                                    </DataTableCell>


                                    <DataTableCell align="right">

                                        <span class="tabular-nums text-gray-900">
                                            {{ formatQuantity(result.detail?.produced_quantity) }}
                                        </span>

                                    </DataTableCell>


                                    <DataTableCell align="right">

                                        <span class="font-medium tabular-nums text-gray-900">
                                            {{ formatQuantity(result.detail?.accepted_quantity) }}
                                        </span>

                                    </DataTableCell>


                                    <DataTableCell align="right">

                                        <span class="tabular-nums text-gray-700">
                                            {{ formatQuantity(result.detail?.rejected_quantity) }}
                                        </span>

                                    </DataTableCell>


                                    <DataTableCell>

                                        <span class="text-sm text-gray-600">
                                            {{ result.detail?.qc_inspector?.name ?? '-' }}
                                        </span>

                                    </DataTableCell>

                                </DataTableRow>

                            </DataTableBody>

                        </DataTable>

                    </div>


                    <div
                        v-else
                        class="
                            px-4
                            py-8
                            text-center
                            text-sm
                            text-gray-500
                        "
                    >
                        No production results found.
                    </div>

                </div>

            </div>


            <!-- ===================================================== -->
            <!-- Workflow Timeline Tab -->
            <!-- ===================================================== -->

            <div
                v-else-if="activeTab === 'workflow'"
                class="p-4"
            >

                <WorkflowTimeline
                    :activities="activities"
                />

            </div>


            <!-- ===================================================== -->
            <!-- Audit Trail Tab -->
            <!-- ===================================================== -->

            <div
                v-else-if="activeTab === 'audit'"
                class="space-y-4 p-4"
            >

                <AuditTrail
                    :activities="activities"
                />


                <div
                    class="
                        rounded-xl
                        border
                        border-gray-100
                        bg-white
                        p-4
                    "
                >

                    <div class="text-sm font-semibold text-gray-900">
                        Production Order Record Information
                    </div>

                    <div
                        class="
                            grid
                            grid-cols-1
                            gap-4
                            sm:grid-cols-2
                            lg:grid-cols-4
                        "
                    >

                        <!-- Created By -->

                        <div>

                            <div class="text-xs text-gray-500">
                                Created By
                            </div>

                            <div class="mt-1 text-sm font-medium text-gray-900">
                                {{ creatorName }}
                            </div>

                        </div>


                        <!-- Updated By -->

                        <div>

                            <div class="text-xs text-gray-500">
                                Updated By
                            </div>

                            <div class="mt-1 text-sm font-medium text-gray-900">
                                {{ updaterName }}
                            </div>

                        </div>


                        <!-- Submitted By -->

                        <div>

                            <div class="text-xs text-gray-500">
                                Submitted By
                            </div>

                            <div class="mt-1 text-sm font-medium text-gray-900">
                                {{ submitterName }}
                            </div>

                        </div>


                        <!-- Approved By -->

                        <div>

                            <div class="text-xs text-gray-500">
                                Approved By
                            </div>

                            <div class="mt-1 text-sm font-medium text-gray-900">
                                {{ approverName }}
                            </div>

                        </div>


                        <!-- Rejected By -->

                        <div>

                            <div class="text-xs text-gray-500">
                                Rejected By
                            </div>

                            <div class="mt-1 text-sm font-medium text-gray-900">
                                {{ rejectorName }}
                            </div>

                        </div>


                        <!-- Cancelled By -->

                        <div>

                            <div class="text-xs text-gray-500">
                                Cancelled By
                            </div>

                            <div class="mt-1 text-sm font-medium text-gray-900">
                                {{ cancellerName }}
                            </div>

                        </div>


                        <!-- Deleted By -->

                        <div>

                            <div class="text-xs text-gray-500">
                                Deleted By
                            </div>

                            <div class="mt-1 text-sm font-medium text-gray-900">
                                {{ deleterName }}
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- ============================================================= -->
    <!-- Reject Modal -->
    <!-- ============================================================= -->

    <Teleport to="body">

        <div
            v-if="showRejectModal"
            class="
                fixed
                inset-0
                z-[100]
                flex
                items-center
                justify-center
                bg-black/50
                p-4
            "
            @click.self="closeReject"
        >

            <div
                class="
                    w-full
                    max-w-lg
                    rounded-xl
                    bg-white
                    shadow-xl
                "
            >

                <div class="border-b border-gray-100 px-5 py-4">

                    <h3 class="text-base font-semibold text-gray-900">
                        Reject Production Order
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Please provide the reason for rejecting this Production Order.
                    </p>

                </div>


                <div class="p-5">

                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Rejection Reason
                    </label>

                    <textarea
                        v-model="rejectReason"
                        rows="4"
                        maxlength="2000"
                        class="
                            w-full
                            rounded-lg
                            border
                            border-gray-300
                            px-3
                            py-2
                            text-sm
                            text-gray-900
                            outline-none
                            transition
                            focus:border-blue-500
                            focus:ring-2
                            focus:ring-blue-100
                        "
                        placeholder="Enter rejection reason..."
                        :disabled="processing"
                    />

                </div>


                <div class="flex justify-end gap-2 border-t border-gray-100 px-5 py-4">

                    <BaseButton
                        variant="secondary"
                        :disabled="processing"
                        @click="closeReject"
                    >
                        Close
                    </BaseButton>


                    <BaseButton
                        variant="danger"
                        :disabled="
                            processing ||
                            !rejectReason.trim()
                        "
                        @click="confirmReject"
                    >
                        Confirm Reject
                    </BaseButton>

                </div>

            </div>

        </div>


        <!-- ========================================================= -->
        <!-- Cancel Modal -->
        <!-- ========================================================= -->

        <div
            v-if="showCancelModal"
            class="
                fixed
                inset-0
                z-[100]
                flex
                items-center
                justify-center
                bg-black/50
                p-4
            "
            @click.self="closeCancel"
        >

            <div
                class="
                    w-full
                    max-w-lg
                    rounded-xl
                    bg-white
                    shadow-xl
                "
            >

                <div class="border-b border-gray-100 px-5 py-4">

                    <h3 class="text-base font-semibold text-gray-900">
                        Cancel Production Order
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Please provide the reason for cancelling this Production Order.
                    </p>

                </div>


                <div class="p-5">

                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Cancellation Reason
                    </label>

                    <textarea
                        v-model="cancelReason"
                        rows="4"
                        maxlength="2000"
                        class="
                            w-full
                            rounded-lg
                            border
                            border-gray-300
                            px-3
                            py-2
                            text-sm
                            text-gray-900
                            outline-none
                            transition
                            focus:border-blue-500
                            focus:ring-2
                            focus:ring-blue-100
                        "
                        placeholder="Enter cancellation reason..."
                        :disabled="processing"
                    />

                </div>


                <div class="flex justify-end gap-2 border-t border-gray-100 px-5 py-4">

                    <BaseButton
                        variant="secondary"
                        :disabled="processing"
                        @click="closeCancel"
                    >
                        Close
                    </BaseButton>


                    <BaseButton
                        variant="danger"
                        :disabled="
                            processing ||
                            !cancelReason.trim()
                        "
                        @click="confirmCancel"
                    >
                        Confirm Cancel
                    </BaseButton>

                </div>

            </div>

        </div>

    </Teleport>

</AppLayout>

</template>
                    
                      