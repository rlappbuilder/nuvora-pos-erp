<script setup>
import { computed, toRefs, watch } from 'vue'
import { Link } from '@inertiajs/vue3'

import AppLayout from '@/Layouts/AppLayout.vue'
import FormSection from '@/Components/Form/FormSection.vue'
import FormField from '@/Components/Form/FormField.vue'
import FormInput from '@/Components/Form/FormInput.vue'
import FormTextarea from '@/Components/Form/FormTextarea.vue'
import BaseButton from '@/Components/Button/BaseButton.vue'
import SearchableSelect from '@/Components/Form/SearchableSelect.vue'

import FlatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'

/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({
    form: {
        type: Object,
        required: true,
    },

    previewCode: {
        type: String,
        default: '',
    },

    branches: {
        type: Array,
        default: () => [],
    },

    warehouses: {
        type: Array,
        default: () => [],
    },

    productionBoms: {
        type: Array,
        default: () => [],
    },

    order: {
        type: Object,
        default: null,
    },

    mode: {
        type: String,
        default: 'create',
    },
})

const form = props.form
const { order, mode } = toRefs(props)

/*
|--------------------------------------------------------------------------
| Emits
|--------------------------------------------------------------------------
*/

const emit = defineEmits([
    'submit',
    'submitAndNew',
    'cancel',
])

/*
|--------------------------------------------------------------------------
| Breadcrumb
|--------------------------------------------------------------------------
*/

const breadcrumbs = computed(() => {
    const items = [
        {
            label: 'Production',
            route: null,
        },
        {
            label: 'Production Orders',
            route: 'production-orders.index',
        },
    ]

    if (props.mode === 'create') {
        items.push({
            label: 'Create Production Order',
            route: null,
        })
    } else {
        items.push({
            label: props.order?.number ?? 'Production Order',
            route: 'production-orders.show',
        })

        items.push({
            label: 'Edit',
            route: null,
        })
    }

    return items
})

/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/

const handleSubmit = () => {
    emit('submit')
}

const handleSubmitAndNew = () => {
    emit('submitAndNew')
}

/*
|--------------------------------------------------------------------------
| Branch Options
|--------------------------------------------------------------------------
*/

const branchOptions = computed(() => {
    return props.branches.map(branch => ({
        ...branch,
        label: branch.label ?? `${branch.code ?? ''} - ${branch.name ?? ''}`,
    }))
})

/*
|--------------------------------------------------------------------------
| Warehouse Options
|--------------------------------------------------------------------------
*/

const availableWarehouses = computed(() => {
    return props.warehouses
        .filter(warehouse => {
            return (
                !form.branch_id ||
                Number(warehouse.branch_id) === Number(form.branch_id)
            )
        })
        .map(warehouse => ({
            ...warehouse,
            label: warehouse.label ??
                `${warehouse.code ?? ''} - ${warehouse.name ?? ''}`,
        }))
})

/*
|--------------------------------------------------------------------------
| Production BOM Options
|--------------------------------------------------------------------------
*/

const availableBoms = computed(() => {
    return props.productionBoms
        .filter(bom => {
            return (
                !form.branch_id ||
                Number(bom.branch_id) === Number(form.branch_id)
            )
        })
        .map(bom => ({
            ...bom,
            label: bom.label ??
                `${bom.code ?? ''} - ${bom.name ?? ''}`,
        }))
})

/*
|--------------------------------------------------------------------------
| Selected BOM
|--------------------------------------------------------------------------
*/

const selectedBom = computed(() => {
    return props.productionBoms.find(
        bom => Number(bom.id) === Number(form.production_bom_id)
    ) ?? null
})

/*
|--------------------------------------------------------------------------
| BOM Version Options
|--------------------------------------------------------------------------
*/

const versionOptions = computed(() => {
    const versions = selectedBom.value?.versions ?? []

    return versions.map(version => ({
        ...version,
        label: version.label ??
            `Version ${version.version_number ?? version.version ?? version.id}`,
    }))
})

/*
|--------------------------------------------------------------------------
| Selected BOM Version
|--------------------------------------------------------------------------
*/

const selectedVersion = computed(() => {
    return versionOptions.value.find(
        version =>
            Number(version.id) === Number(form.production_bom_version_id)
    ) ?? null
})

/*
|--------------------------------------------------------------------------
| Output Information
|--------------------------------------------------------------------------
*/

const outputVariant = computed(() => {
    return (
        selectedBom.value?.product_variant ??
        selectedBom.value?.productVariant ??
        selectedVersion.value?.product_variant ??
        selectedVersion.value?.productVariant ??
        null
    )
})

const outputProductName = computed(() => {
    const variant = outputVariant.value

    return (
        variant?.product?.name ??
        variant?.product_name ??
        variant?.name ??
        '-'
    )
})

const outputSku = computed(() => {
    return outputVariant.value?.sku ?? '-'
})

const outputUnitName = computed(() => {
    const version = selectedVersion.value

    return (
        version?.output_unit?.name ??
        version?.outputUnit?.name ??
        version?.output_unit_name ??
        '-'
    )
})

/*
|--------------------------------------------------------------------------
| Change Branch
|--------------------------------------------------------------------------
*/

const changeBranch = () => {
    const selectedWarehouseIds = availableWarehouses.value.map(
        warehouse => Number(warehouse.id)
    )

    if (
        form.source_warehouse_id &&
        !selectedWarehouseIds.includes(Number(form.source_warehouse_id))
    ) {
        form.source_warehouse_id = null
    }

    if (
        form.destination_warehouse_id &&
        !selectedWarehouseIds.includes(Number(form.destination_warehouse_id))
    ) {
        form.destination_warehouse_id = null
    }

    const selectedBomIds = availableBoms.value.map(
        bom => Number(bom.id)
    )

    if (
        form.production_bom_id &&
        !selectedBomIds.includes(Number(form.production_bom_id))
    ) {
        form.production_bom_id = null
        form.production_bom_version_id = null
    }
}

/*
|--------------------------------------------------------------------------
| Change BOM
|--------------------------------------------------------------------------
*/

const changeBom = () => {
    form.production_bom_version_id = null
}

/*
|--------------------------------------------------------------------------
| Change BOM Version
|--------------------------------------------------------------------------
*/

const changeBomVersion = () => {
    // BOM Version dipilih sebagai sumber snapshot routing dan material.
    // Snapshot dibuat oleh ProductionOrderService ketika order disimpan.
}

/*
|--------------------------------------------------------------------------
| Date Configuration
|--------------------------------------------------------------------------
*/

const dateConfig = {
    dateFormat: 'Y-m-d',
    allowInput: true,
}

/*
|--------------------------------------------------------------------------
| Quantity Formatting
|--------------------------------------------------------------------------
*/

const formatQuantity = value => {
    if (value === null || value === undefined || value === '') {
        return '0'
    }

    return new Intl.NumberFormat('id-ID', {
        maximumFractionDigits: 4,
    }).format(Number(value))
}

/*
|--------------------------------------------------------------------------
| Watch Branch
|--------------------------------------------------------------------------
*/

watch(
    () => form.branch_id,
    (newValue, oldValue) => {
        if (newValue !== oldValue) {
            changeBranch()
        }
    }
)

/*
|--------------------------------------------------------------------------
| Watch BOM
|--------------------------------------------------------------------------
*/

watch(
    () => form.production_bom_id,
    (newValue, oldValue) => {
        if (newValue !== oldValue) {
            changeBom()
        }
    }
)

</script>

<template>
    <AppLayout>
        <form
            @submit.prevent="handleSubmit"
            class="space-y-6"
        >

            <!-- ===================================================== -->
            <!-- Breadcrumb -->
            <!-- ===================================================== -->

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
                        :href="
                            item.route === 'production-orders.show'
                                ? route(item.route, order.id)
                                : route(item.route)
                        "
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

            <!-- ===================================================== -->
            <!-- Page Header -->
            <!-- ===================================================== -->

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
                        {{
                            mode === 'create'
                                ? 'Create Production Order'
                                : 'Edit Production Order'
                        }}
                    </h1>

                    <p class="mt-1 text-sm text-gray-500">
                        Create and manage production orders based on an approved production BOM.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-500">
                        Order Number
                    </span>

                    <span
                        class="
                            rounded-lg
                            bg-gray-100
                            px-3
                            py-2
                            text-sm
                            font-semibold
                            text-gray-700
                        "
                    >
                        {{ order?.number ?? previewCode ?? 'Auto generated' }}
                    </span>
                </div>
            </div>

            <!-- ===================================================== -->
            <!-- Production Order Information -->
            <!-- ===================================================== -->

            <FormSection
                icon="📋"
                title="Production Order Information"
                description="Define the branch, production BOM, and production schedule."
                :columns="2"
            >

                <!-- Branch -->

                <FormField
                    label="Branch"
                    required
                    :error="form.errors.branch_id"
                >
                    <SearchableSelect
                        v-model="form.branch_id"
                        :options="branchOptions"
                        label="label"
                        value-key="id"
                        placeholder="Select Branch"
                        :disabled="mode === 'edit'"
                    />
                </FormField>

                <!-- Order Date -->

                <FormField
                    label="Order Date"
                    required
                    :error="form.errors.order_date"
                >
                    <FlatPickr
                        v-model="form.order_date"
                        :config="dateConfig"
                        class="
                            w-full
                            rounded-lg
                            border
                            border-gray-300
                            px-3
                            py-2
                            text-sm
                            focus:border-blue-500
                            focus:ring-blue-500
                        "
                    />
                </FormField>

                <!-- Production BOM -->

                <FormField
                    label="Production BOM"
                    required
                    :error="form.errors.production_bom_id"
                >
                    <SearchableSelect
                        v-model="form.production_bom_id"
                        :options="availableBoms"
                        label="label"
                        value-key="id"
                        placeholder="Select Production BOM"
                        :disabled="mode === 'edit'"
                    />
                </FormField>

                <!-- BOM Version -->

                <FormField
                    label="BOM Version"
                    required
                    :error="form.errors.production_bom_version_id"
                >
                    <SearchableSelect
                        v-model="form.production_bom_version_id"
                        :options="versionOptions"
                        label="label"
                        value-key="id"
                        placeholder="Select BOM Version"
                        :disabled="mode === 'edit' || !form.production_bom_id"
                    />
                </FormField>

                <!-- Planned Quantity -->

                <FormField
                    label="Planned Quantity"
                    required
                    :error="form.errors.planned_quantity"
                >
                    <FormInput
                        v-model="form.planned_quantity"
                        type="number"
                        min="0.000001"
                        step="0.000001"
                        placeholder="Enter planned quantity"
                    />
                </FormField>

                <!-- Planned Start Date -->

                <FormField
                    label="Planned Start Date"
                    required
                    :error="form.errors.planned_start_date"
                >
                    <FlatPickr
                        v-model="form.planned_start_date"
                        :config="dateConfig"
                        class="
                            w-full
                            rounded-lg
                            border
                            border-gray-300
                            px-3
                            py-2
                            text-sm
                            focus:border-blue-500
                            focus:ring-blue-500
                        "
                    />
                </FormField>

                <!-- Planned End Date -->

                <FormField
                    label="Planned End Date"
                    required
                    :error="form.errors.planned_end_date"
                >
                    <FlatPickr
                        v-model="form.planned_end_date"
                        :config="dateConfig"
                        class="
                            w-full
                            rounded-lg
                            border
                            border-gray-300
                            px-3
                            py-2
                            text-sm
                            focus:border-blue-500
                            focus:ring-blue-500
                        "
                    />
                </FormField>

                <!-- Description -->

                <div class="md:col-span-2">
                    <FormField
                        label="Description"
                        :error="form.errors.description"
                    >
                        <FormTextarea
                            v-model="form.description"
                            :rows="3"
                            placeholder="Add production order description or notes..."
                        />
                    </FormField>
                </div>

            </FormSection>

            <!-- ===================================================== -->
            <!-- Warehouse Configuration -->
            <!-- ===================================================== -->

            <FormSection
                icon="🏭"
                title="Warehouse Configuration"
                description="Define where raw materials are issued from and where finished goods will be received."
                :columns="2"
            >

                <!-- Source Warehouse -->

                <FormField
                    label="Source Warehouse"
                    required
                    :error="form.errors.source_warehouse_id"
                >
                    <SearchableSelect
                        v-model="form.source_warehouse_id"
                        :options="availableWarehouses"
                        label="label"
                        value-key="id"
                        placeholder="Select Source Warehouse"
                    />
                </FormField>

                <!-- Destination Warehouse -->

                <FormField
                    label="Destination Warehouse"
                    required
                    :error="form.errors.destination_warehouse_id"
                >
                    <SearchableSelect
                        v-model="form.destination_warehouse_id"
                        :options="availableWarehouses"
                        label="label"
                        value-key="id"
                        placeholder="Select Destination Warehouse"
                    />
                </FormField>

                <div class="md:col-span-2">
                    <p class="text-xs text-gray-500">
                        Material Issue will use the source warehouse. Accepted production results will be received into the destination warehouse.
                    </p>
                </div>

            </FormSection>

            <!-- ===================================================== -->
            <!-- Output Information -->
            <!-- ===================================================== -->

            <FormSection
                icon="📦"
                title="Production Output"
                description="Output information is automatically taken from the selected BOM version."
                :columns="2"
            >

                <FormField label="Output Product">
                    <div
                        class="
                            min-h-[42px]
                            rounded-lg
                            border
                            border-gray-200
                            bg-gray-50
                            px-3
                            py-2.5
                            text-sm
                            font-medium
                            text-gray-800
                        "
                    >
                        {{ outputProductName }}
                    </div>
                </FormField>

                <FormField label="Product SKU">
                    <div
                        class="
                            min-h-[42px]
                            rounded-lg
                            border
                            border-gray-200
                            bg-gray-50
                            px-3
                            py-2.5
                            text-sm
                            text-gray-700
                        "
                    >
                        {{ outputSku }}
                    </div>
                </FormField>

                <FormField label="Output Unit">
                    <div
                        class="
                            min-h-[42px]
                            rounded-lg
                            border
                            border-gray-200
                            bg-gray-50
                            px-3
                            py-2.5
                            text-sm
                            text-gray-700
                        "
                    >
                        {{ outputUnitName }}
                    </div>
                </FormField>

                <FormField label="Selected BOM Version">
                    <div
                        class="
                            min-h-[42px]
                            rounded-lg
                            border
                            border-gray-200
                            bg-gray-50
                            px-3
                            py-2.5
                            text-sm
                            text-gray-700
                        "
                    >
                        {{ selectedVersion?.label ?? '-' }}
                    </div>
                </FormField>

                <div
                    v-if="selectedBom && selectedVersion"
                    class="
                        md:col-span-2
                        rounded-xl
                        border
                        border-blue-100
                        bg-blue-50
                        p-4
                    "
                >
                    <p class="text-sm font-medium text-blue-900">
                        BOM Snapshot
                    </p>

                    <p class="mt-1 text-xs leading-relaxed text-blue-800">
                        The selected BOM version, routing, and material composition will be saved as a snapshot when this Production Order is first saved as Draft. Later changes to the BOM master will not automatically change this order.
                    </p>
                </div>

                <div
                    v-else
                    class="
                        md:col-span-2
                        rounded-xl
                        border
                        border-amber-200
                        bg-amber-50
                        p-4
                        text-sm
                        text-amber-800
                    "
                >
                    Select a Production BOM and its version to view the output information.
                </div>

            </FormSection>

            <!-- ===================================================== -->
            <!-- Actions -->
            <!-- ===================================================== -->

            <div
                class="
                    mt-8
                    flex
                    flex-col
                    justify-end
                    gap-3
                    sm:flex-row
                "
            >
                <BaseButton
                    type="button"
                    variant="secondary"
                    @click="emit('cancel')"
                >
                    Cancel
                </BaseButton>

                <BaseButton
                    type="submit"
                    :loading="form.processing"
                    :disabled="form.processing"
                >
                    {{ mode === 'create' ? 'Save Draft' : 'Update Draft' }}
                </BaseButton>

                <BaseButton
                    v-if="mode === 'create'"
                    type="button"
                    variant="success"
                    :loading="form.processing"
                    :disabled="form.processing"
                    @click="handleSubmitAndNew"
                >
                    Save &amp; New
                </BaseButton>
            </div>

        </form>
    </AppLayout>
</template>