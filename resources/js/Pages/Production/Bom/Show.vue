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
        default: 'Bill of Materials Detail',
    },

    bom: {
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
            label: 'Bill of Materials',
            route: 'production-boms.index',
        },
        {
            label: props.bom?.code ?? 'BOM',
            route: null,
        },
    ]

})


/*
|--------------------------------------------------------------------------
| Versions
|--------------------------------------------------------------------------
*/

const versions = computed(() => {

    return props.bom?.versions ?? []

})


const selectedVersionId = ref(null)


const currentVersion = computed(() => {

    if (!versions.value.length) {
        return null
    }

    if (selectedVersionId.value) {

        const selected = versions.value.find(
            version =>
                Number(version.id) ===
                Number(selectedVersionId.value)
        )

        if (selected) {
            return selected
        }

    }

    return (
        versions.value.find(
            version => version.status === 'active'
        )
        ?? versions.value[0]
    )

})


function selectVersion(version)
{
    selectedVersionId.value = version.id
}


/*
|--------------------------------------------------------------------------
| Materials and Operations
|--------------------------------------------------------------------------
*/

const materials = computed(() => {

    return currentVersion.value?.materials ?? []

})


const operations = computed(() => {

    return currentVersion.value?.operations ?? []

})


const totalMaterials = computed(() => {

    return materials.value.length

})


const totalOperations = computed(() => {

    return operations.value.length

})


const totalGrossQuantity = computed(() => {

    return materials.value.reduce(
        (total, material) => {

            return total + getGrossQuantity(material)

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
            maximumFractionDigits: 4,
        }
    ).format(
        Number(value || 0)
    )

}


function formatPercentage(value)
{

    return new Intl.NumberFormat(
        'id-ID',
        {
            minimumFractionDigits: 0,
            maximumFractionDigits: 4,
        }
    ).format(
        Number(value || 0)
    )

}


function getGrossQuantity(material)
{

    const qty = Number(material?.qty || 0)

    const scrap = Number(material?.scrap_percent || 0)

    if (scrap >= 100 || scrap < 0) {
        return 0
    }

    return qty / (1 - scrap / 100)

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


function getOperationName(operation)
{
    return (
        operation?.production_operation?.name
        ?? operation?.productionOperation?.name
        ?? '-'
    )
}


function getMaterialOperationName(material)
{

    return (
        material?.productionBomOperation
            ?.productionOperation?.name
        ?? 'General Material'
    )

}


function getVersionLabel(version)
{

    return `Version ${version?.version ?? '-'}`

}


/*
|--------------------------------------------------------------------------
| Activities
|--------------------------------------------------------------------------
*/

const activities = computed(() => {

    return props.bom?.activities ?? []

})


/*
|--------------------------------------------------------------------------
| Actions
|--------------------------------------------------------------------------
*/

function back()
{

    router.get(
        route('production-boms.index')
    )

}


function editBom()
{

    if (!props.bom?.id) {
        return
    }

    router.get(
        route(
            'production-boms.edit',
            props.bom.id
        )
    )

}


function createRevision()
{

    if (!props.bom?.id) {
        return
    }

    router.post(
        route(
            'production-boms.revision',
            props.bom.id
        )
    )

}


function activateVersion(version)
{

    if (!version?.id) {
        return
    }

    if (!window.confirm(
        `Activate ${getVersionLabel(version)}?`
    )) {
        return
    }

    router.post(
        route(
            'production-boms.activate-version',
            version.id
        )
    )

}


function deactivateBom()
{

    if (!props.bom?.id) {
        return
    }

    if (!window.confirm(
        'Are you sure you want to deactivate this BOM?'
    )) {
        return
    }

    router.post(
        route(
            'production-boms.deactivate',
            props.bom.id
        )
    )

}


/*
|--------------------------------------------------------------------------
| Version Actions
|--------------------------------------------------------------------------
*/

const hasDraftVersion = computed(() => {

    return versions.value.some(
        version => version.status === 'draft'
    )

})


const canEditCurrentVersion = computed(() => {

    return currentVersion.value?.status === 'draft'

})


const canActivateCurrentVersion = computed(() => {

    return currentVersion.value?.status === 'draft'
        && !props.bom?.is_active

})

function printBom()
{
    if (!props.bom?.id || !currentVersion.value?.id) {
        return
    }

    window.open(
        route('production-boms.print', {
            bom: props.bom.id,
            version: currentVersion.value.id,
        }),
        '_blank'
    )
}
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
                    class="
                        transition
                        hover:text-blue-600
                    "
                >
                    {{ item.label }}
                </Link>


                <span
                    v-else
                    class="
                        font-medium
                        text-gray-800
                    "
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

                <h1
                    class="
                        text-xl
                        font-semibold
                        text-gray-900
                    "
                >
                    Bill of Materials
                </h1>


                <p
                    class="
                        mt-1
                        text-sm
                        text-gray-500
                    "
                >
                    Production BOM detail and material composition.
                </p>

            </div>


            <div
                class="
                    flex
                    flex-wrap
                    items-center
                    gap-2
                "
            >

                <BaseButton
                    variant="secondary"
                    @click="back"
                >
                    Back
                </BaseButton>
                
                <BaseButton
                    variant="secondary"
                    @click="printBom"
                >
                    <span class="flex items-center gap-2">
                        <icon name="printer" />
                        Print BOM
                    </span>
                </BaseButton>

                <BaseButton
                    v-if="canEditCurrentVersion"
                    variant="secondary"
                    @click="editBom"
                >
                    Edit
                </BaseButton>


                <BaseButton
                    v-if="
                        currentVersion &&
                        currentVersion.status !== 'draft'
                    "
                    variant="secondary"
                    @click="createRevision"
                >
                    New Revision
                </BaseButton>


                <BaseButton
                    v-if="bom.is_active"
                    variant="secondary"
                    @click="deactivateBom"
                >
                    Deactivate
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

                <!-- BOM -->

                <div class="px-4 py-4">

                    <div
                        class="
                            text-xs
                            font-medium
                            text-gray-500
                        "
                    >
                        BOM
                    </div>


                    <div
                        class="
                            mt-1
                            font-semibold
                            text-gray-900
                        "
                    >
                        {{ bom.code ?? '-' }}
                    </div>


                    <div
                        class="
                            mt-0.5
                            text-xs
                            text-gray-500
                        "
                    >
                        {{ bom.name ?? '-' }}
                    </div>

                </div>


                <!-- Output Product -->

                <div class="px-4 py-4">

                    <div
                        class="
                            text-xs
                            font-medium
                            text-gray-500
                        "
                    >
                        Output Product
                    </div>


                    <div
                        class="
                            mt-1
                            font-semibold
                            text-gray-900
                        "
                    >
                        {{
                            getVariantName(
                                bom.product_variant
                            )
                        }}
                    </div>


                    <div
                        class="
                            mt-0.5
                            text-xs
                            text-gray-500
                        "
                    >
                        SKU:
                        {{
                            bom.product_variant?.sku
                            ?? '-'
                        }}
                    </div>

                </div>


                <!-- Branch -->

                <div class="px-4 py-4">

                    <div
                        class="
                            text-xs
                            font-medium
                            text-gray-500
                        "
                    >
                        Branch
                    </div>


                    <div
                        class="
                            mt-1
                            font-semibold
                            text-gray-900
                        "
                    >
                        {{ bom.branch?.name ?? '-' }}
                    </div>


                    <div
                        class="
                            mt-0.5
                            text-xs
                            text-gray-500
                        "
                    >
                        {{
                            bom.bom_type === 'alternative'
                                ? 'Alternative BOM'
                                : 'Standard BOM'
                        }}
                    </div>

                </div>


                <!-- Status -->

                <div class="px-4 py-4">

                    <div
                        class="
                            text-xs
                            font-medium
                            text-gray-500
                        "
                    >
                        BOM Status
                    </div>


                    <div class="mt-2">

                        <StatusBadge
                            :status="
                                bom.is_active
                                    ? 'active'
                                    : 'inactive'
                            "
                        />

                    </div>

                </div>

            </div>

        </div>


        <!-- ========================================================= -->
        <!-- Current Version Summary -->
        <!-- ========================================================= -->

        <div
            v-if="currentVersion"
            class="
                rounded-xl
                border
                border-gray-100
                bg-white
                px-4
                py-3
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

                    <div
                        class="
                            text-xs
                            font-medium
                            text-gray-500
                        "
                    >
                        Selected BOM Version
                    </div>


                    <div
                        class="
                            mt-1
                            flex
                            flex-wrap
                            items-center
                            gap-2
                        "
                    >

                        <span
                            class="
                                text-sm
                                font-semibold
                                text-gray-900
                            "
                        >
                            {{ getVersionLabel(currentVersion) }}
                        </span>


                        <StatusBadge
                            :status="currentVersion.status"
                        />

                    </div>

                </div>


                <div
                    class="
                        flex
                        flex-wrap
                        gap-x-5
                        gap-y-2
                        text-sm
                    "
                >

                    <div>

                        <div class="text-xs text-gray-500">
                            Output Quantity
                        </div>

                        <div class="mt-0.5 font-semibold text-gray-900">

                            {{ formatQuantity(currentVersion.output_qty) }}

                            {{ currentVersion.output_unit?.name ?? '' }}

                        </div>

                    </div>


                    <div>

                        <div class="text-xs text-gray-500">
                            Effective From
                        </div>

                        <div class="mt-0.5 font-medium text-gray-900">
                            {{
                                currentVersion.effective_from
                                    ? formatDate(currentVersion.effective_from)
                                    : '-'
                            }}
                        </div>

                    </div>


                    <div>

                        <div class="text-xs text-gray-500">
                            Effective Until
                        </div>

                        <div class="mt-0.5 font-medium text-gray-900">
                            {{
                                currentVersion.effective_until
                                    ? formatDate(currentVersion.effective_until)
                                    : '-'
                            }}
                        </div>

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

            <div
                class="
                    flex
                    overflow-x-auto
                    border-b
                    border-gray-100
                "
            >

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
                    BOM Detail
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
                        activeTab === 'versions'
                            ? 'border-blue-600 text-blue-600'
                            : 'border-transparent text-gray-500 hover:text-gray-700'
                    "
                    @click="activeTab = 'versions'"
                >
                    Version History
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
            <!-- BOM Detail Tab -->
            <!-- ===================================================== -->

            <div
                v-if="activeTab === 'detail'"
                class="space-y-4 p-4"
            >

                <!-- BOM Information -->

                <div
                    class="
                        rounded-xl
                        border
                        border-gray-100
                        bg-white
                    "
                >

                    <div
                        class="
                            border-b
                            border-gray-100
                            px-4
                            py-3
                        "
                    >

                        <div
                            class="
                                text-sm
                                font-semibold
                                text-gray-900
                            "
                        >
                            BOM Information
                        </div>

                    </div>


                    <div
                        class="
                            grid
                            grid-cols-1
                            gap-4
                            p-4
                            sm:grid-cols-2
                        "
                    >

                        <div>

                            <div class="text-xs text-gray-500">
                                BOM Name
                            </div>

                            <div class="mt-1 text-sm font-medium text-gray-900">
                                {{ bom.name ?? '-' }}
                            </div>

                        </div>


                        <div>

                            <div class="text-xs text-gray-500">
                                BOM Type
                            </div>

                            <div class="mt-1 text-sm font-medium text-gray-900">
                                {{
                                    bom.bom_type === 'alternative'
                                        ? 'Alternative'
                                        : 'Standard'
                                }}
                            </div>

                        </div>


                        <div>

                            <div class="text-xs text-gray-500">
                                Output Product
                            </div>

                            <div class="mt-1 text-sm font-medium text-gray-900">
                                {{
                                    getVariantName(
                                        bom.product_variant
                                    )
                                }}
                            </div>

                        </div>


                        <div>

                            <div class="text-xs text-gray-500">
                                Output SKU
                            </div>

                            <div class="mt-1 text-sm font-medium text-gray-900">
                                {{ bom.product_variant?.sku ?? '-' }}
                            </div>

                        </div>


                        <div>

                            <div class="text-xs text-gray-500">
                                Branch
                            </div>

                            <div class="mt-1 text-sm font-medium text-gray-900">
                                {{ bom.branch?.name ?? '-' }}
                            </div>

                        </div>


                        <div>

                            <div class="text-xs text-gray-500">
                                Version
                            </div>

                            <div class="mt-1 text-sm font-medium text-gray-900">
                                {{ getVersionLabel(currentVersion) }}
                            </div>

                        </div>


                        <div
                            v-if="bom.description"
                            class="sm:col-span-2"
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
                                {{ bom.description }}
                            </div>

                        </div>

                    </div>

                </div>


                <!-- Materials -->

                <div
                    class="
                        overflow-hidden
                        rounded-xl
                        border
                        border-gray-100
                        bg-white
                    "
                >

                    <div
                        class="
                            flex
                            flex-col
                            gap-2
                            border-b
                            border-gray-100
                            px-4
                            py-3
                            sm:flex-row
                            sm:items-center
                            sm:justify-between
                        "
                    >

                        <div>

                            <div
                                class="
                                    text-sm
                                    font-semibold
                                    text-gray-900
                                "
                            >
                                Material Composition
                            </div>


                            <div
                                class="
                                    mt-0.5
                                    text-xs
                                    text-gray-500
                                "
                            >
                                {{ totalMaterials }} Materials
                            </div>

                        </div>


                        <div class="text-xs text-gray-500">
                            Based on {{ getVersionLabel(currentVersion) }}
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


                                <DataTableHeaderCell width="250px">
                                    Material
                                </DataTableHeaderCell>


                                <DataTableHeaderCell width="170px">
                                    SKU
                                </DataTableHeaderCell>

                                <DataTableHeaderCell
                                    width="110px"
                                    align="right"
                                >
                                    Net Qty
                                </DataTableHeaderCell>
                                <DataTableHeaderCell width="90px">
                                    Unit
                                </DataTableHeaderCell>

                                <DataTableHeaderCell
                                    width="100px"
                                    align="right"
                                >
                                    Scrap %
                                </DataTableHeaderCell>


                                <DataTableHeaderCell
                                    width="120px"
                                    align="right"
                                >
                                    Gross Qty
                                </DataTableHeaderCell>


                                <DataTableHeaderCell width="180px">
                                    Production Operation
                                </DataTableHeaderCell>


                                <DataTableHeaderCell width="200px">
                                    Notes
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
                                            {{
                                                material.component_variant?.sku
                                                ?? '-'
                                            }}
                                        </span>

                                    </DataTableCell>
                                    <DataTableCell align="right">

                                        <span class="tabular-nums text-gray-900">
                                            {{ formatQuantity(material.qty) }}
                                        </span>

                                    </DataTableCell>

                                    <DataTableCell>

                                        <span class="text-sm text-gray-600">
                                            {{ material.unit?.name ?? '-' }}
                                        </span>

                                    </DataTableCell>


                                    


                                    <DataTableCell align="right">

                                        <span class="tabular-nums text-gray-700">
                                            {{ formatPercentage(material.scrap_percent) }}%
                                        </span>

                                    </DataTableCell>


                                    <DataTableCell align="right">

                                        <span class="font-medium tabular-nums text-gray-900">
                                            {{ formatQuantity(getGrossQuantity(material)) }}
                                        </span>

                                    </DataTableCell>


                                    <DataTableCell>

                                        <span class="text-sm text-gray-600">
                                            {{ getMaterialOperationName(material) }}
                                        </span>

                                    </DataTableCell>


                                    <DataTableCell>

                                        <span class="text-sm text-gray-600">
                                            {{ material.notes ?? '-' }}
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
                        No BOM materials.
                    </div>


                    <div
                        v-if="materials.length"
                        class="
                            flex
                            flex-col
                            gap-1
                            border-t
                            border-gray-100
                            px-4
                            py-3
                            text-sm
                            sm:flex-row
                            sm:items-center
                            sm:justify-between
                        "
                    >

                        <span class="text-gray-500">
                            Total Materials: {{ totalMaterials }}
                        </span>


                        <span class="font-semibold text-gray-900">
                            Total Gross Quantity:
                            {{ formatQuantity(totalGrossQuantity) }}
                        </span>

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

                    <div
                        class="
                            flex
                            flex-col
                            gap-2
                            border-b
                            border-gray-100
                            px-4
                            py-3
                            sm:flex-row
                            sm:items-center
                            sm:justify-between
                        "
                    >

                        <div>

                            <div
                                class="
                                    text-sm
                                    font-semibold
                                    text-gray-900
                                "
                            >
                                Production Routing
                            </div>


                            <div
                                class="
                                    mt-0.5
                                    text-xs
                                    text-gray-500
                                "
                            >
                                {{ totalOperations }} Operations
                            </div>

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
                                    Duration
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


                <!-- Revision Notes -->

                <div
                    v-if="currentVersion?.revision_notes"
                    class="
                        rounded-xl
                        border
                        border-gray-100
                        bg-white
                        px-4
                        py-3
                    "
                >

                    <div class="text-xs font-medium text-gray-500">
                        Revision Notes
                    </div>


                    <div
                        class="
                            mt-1
                            whitespace-pre-line
                            text-sm
                            text-gray-700
                        "
                    >
                        {{ currentVersion.revision_notes }}
                    </div>

                </div>

            </div>


            <!-- ===================================================== -->
            <!-- Version History Tab -->
            <!-- ===================================================== -->

            <div
                v-else-if="activeTab === 'versions'"
                class="space-y-4 p-4"
            >

                <div
                    class="
                        overflow-hidden
                        rounded-xl
                        border
                        border-gray-100
                        bg-white
                    "
                >

                    <div
                        class="
                            border-b
                            border-gray-100
                            px-4
                            py-3
                        "
                    >

                        <div class="text-sm font-semibold text-gray-900">
                            BOM Version History
                        </div>


                        <div class="mt-0.5 text-xs text-gray-500">
                            Select a version to view its details.
                        </div>

                    </div>


                    <div
                        v-if="versions.length"
                        class="overflow-x-auto"
                    >

                        <DataTable>

                            <DataTableHead>

                                <DataTableHeaderCell width="100px">
                                    Version
                                </DataTableHeaderCell>


                                <DataTableHeaderCell
                                    width="140px"
                                    align="right"
                                >
                                    Output Qty
                                </DataTableHeaderCell>


                                <DataTableHeaderCell width="100px">
                                    Unit
                                </DataTableHeaderCell>


                                <DataTableHeaderCell width="120px">
                                    Status
                                </DataTableHeaderCell>


                                <DataTableHeaderCell width="140px">
                                    Effective From
                                </DataTableHeaderCell>


                                <DataTableHeaderCell width="140px">
                                    Effective Until
                                </DataTableHeaderCell>


                                <DataTableHeaderCell width="180px">
                                    Approved By
                                </DataTableHeaderCell>


                                <DataTableHeaderCell width="150px">
                                    Approved At
                                </DataTableHeaderCell>


                                <DataTableHeaderCell width="180px">
                                    Action
                                </DataTableHeaderCell>

                            </DataTableHead>


                            <DataTableBody>

                                <DataTableRow
                                    v-for="version in versions"
                                    :key="version.id"
                                >

                                    <DataTableCell>

                                        <button
                                            type="button"
                                            class="
                                                font-medium
                                                text-blue-600
                                                hover:text-blue-800
                                            "
                                            @click="selectVersion(version)"
                                        >
                                            {{ getVersionLabel(version) }}
                                        </button>

                                    </DataTableCell>


                                    <DataTableCell align="right">

                                        <span class="tabular-nums text-gray-900">
                                            {{ formatQuantity(version.output_qty) }}
                                        </span>

                                    </DataTableCell>


                                    <DataTableCell>

                                        <span class="text-sm text-gray-600">
                                            {{ version.output_unit?.name ?? '-' }}
                                        </span>

                                    </DataTableCell>


                                    <DataTableCell>

                                        <StatusBadge
                                            :status="version.status"
                                        />

                                    </DataTableCell>


                                    <DataTableCell>

                                        <span class="text-sm text-gray-600">
                                            {{
                                                version.effective_from
                                                    ? formatDate(version.effective_from)
                                                    : '-'
                                            }}
                                        </span>

                                    </DataTableCell>


                                    <DataTableCell>

                                        <span class="text-sm text-gray-600">
                                            {{
                                                version.effective_until
                                                    ? formatDate(version.effective_until)
                                                    : '-'
                                            }}
                                        </span>

                                    </DataTableCell>


                                    <DataTableCell>

                                        <span class="text-sm text-gray-600">
                                            {{ version.approver?.name ?? '-' }}
                                        </span>

                                    </DataTableCell>


                                    <DataTableCell>

                                        <span class="text-sm text-gray-600">
                                            {{
                                                version.approved_at
                                                    ? formatDate(version.approved_at)
                                                    : '-'
                                            }}
                                        </span>

                                    </DataTableCell>


                                    <DataTableCell>

                                        <div class="flex items-center gap-2">

                                            <BaseButton
                                                v-if="version.status === 'draft'"
                                                size="sm"
                                                @click="activateVersion(version)"
                                            >
                                                Activate
                                            </BaseButton>


                                            <span
                                                v-else
                                                class="text-xs text-gray-400"
                                            >
                                                -
                                            </span>

                                        </div>

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
                        No BOM versions found.
                    </div>

                </div>


                <!-- Selected Version Notes -->

                <div
                    v-if="currentVersion"
                    class="
                        rounded-xl
                        border
                        border-gray-100
                        bg-white
                        p-4
                    "
                >

                    <div class="text-sm font-semibold text-gray-900">
                        Selected Version Details
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
                                Created By
                            </div>

                            <div class="mt-1 text-sm font-medium text-gray-900">
                                {{ currentVersion.creator?.name ?? '-' }}
                            </div>

                        </div>


                        <div>

                            <div class="text-xs text-gray-500">
                                Updated By
                            </div>

                            <div class="mt-1 text-sm font-medium text-gray-900">
                                {{ currentVersion.updater?.name ?? '-' }}
                            </div>

                        </div>


                        <div
                            v-if="currentVersion.revision_notes"
                            class="sm:col-span-2"
                        >

                            <div class="text-xs text-gray-500">
                                Revision Notes
                            </div>

                            <div
                                class="
                                    mt-1
                                    whitespace-pre-line
                                    text-sm
                                    text-gray-700
                                "
                            >
                                {{ currentVersion.revision_notes }}
                            </div>

                        </div>

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
                        BOM Record Information
                    </div>


                    <div
                        class="
                            mt-3
                            grid
                            grid-cols-1
                            gap-4
                            sm:grid-cols-3
                        "
                    >

                        <div>

                            <div class="text-xs text-gray-500">
                                Created By
                            </div>

                            <div class="mt-1 text-sm font-medium text-gray-900">
                                {{ bom.creator?.name ?? '-' }}
                            </div>

                        </div>


                        <div>

                            <div class="text-xs text-gray-500">
                                Updated By
                            </div>

                            <div class="mt-1 text-sm font-medium text-gray-900">
                                {{ bom.updater?.name ?? '-' }}
                            </div>

                        </div>


                        <div>

                            <div class="text-xs text-gray-500">
                                Deleted By
                            </div>

                            <div class="mt-1 text-sm font-medium text-gray-900">
                                {{ bom.deleter?.name ?? '-' }}
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</AppLayout>

</template>