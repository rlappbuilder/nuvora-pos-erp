<script setup>

import { computed, toRefs } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import FormSection from '@/Components/Form/FormSection.vue'
import FormField from '@/Components/Form/FormField.vue'
import FormInput from '@/Components/Form/FormInput.vue'
import FormTextarea from '@/Components/Form/FormTextarea.vue'
import BaseButton from '@/Components/Button/BaseButton.vue'
import SearchableSelect from '@/Components/Form/SearchableSelect.vue'

import {
    TrashIcon,
    ArrowUpIcon,
    ArrowDownIcon,
} from '@heroicons/vue/24/outline'

import FlatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'

import Swal from 'sweetalert2'


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

      outputVariants: {
        type: Array,
        default: () => [],
    },

    materialVariants: {
        type: Array,
        default: () => [],
    },

    variantUnits: {
        type: Array,
        default: () => [],
    },

    productionOperations: {
        type: Array,
        default: () => [],
    },

    bom: {
        type: Object,
        default: null,
    },

    mode: {
        type: String,
        default: 'create',
    },

})


const form = props.form
const { bom, mode } = toRefs(props)

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
            label: 'Bill of Materials',
            route: 'production-boms.index',
        },
    ]


    if (props.mode === 'create') {

        items.push({
            label: 'Create BOM',
            route: null,
        })

    } else if (props.mode === 'edit') {

        items.push({
            label: props.bom?.code ?? 'BOM',
            route: 'production-boms.show',
        })

        items.push({
            label: 'Edit',
            route: null,
        })

    } else if (props.mode === 'revision') {

        items.push({
            label: props.bom?.code ?? 'BOM',
            route: 'production-boms.show',
        })

        items.push({
            label: 'New Revision',
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
| Empty Operation
|--------------------------------------------------------------------------
*/

const createEmptyOperation = () => ({

    production_operation_id:
        null,

    sequence:
        form.operations.length + 1,

    planned_duration_minutes:
        null,

    instructions:
        '',

})


/*
|--------------------------------------------------------------------------
| Empty Material
|--------------------------------------------------------------------------
*/

const createEmptyMaterial = () => ({

    component_variant_id:
        null,

    unit_id:
        null,

    qty:
        1,

    scrap_percent:
        0,

    operation_sequence:
        null,

    sequence:
        form.materials.length + 1,

    notes:
        '',

})


/*
|--------------------------------------------------------------------------
| Available Branches
|--------------------------------------------------------------------------
*/

const branchOptions = computed(() => {

    return props.branches.map(branch => ({

        ...branch,

        label:
            branch.label ??
            `${branch.code ?? ''} - ${branch.name ?? ''}`,

    }))

})

/*
|--------------------------------------------------------------------------
| Available Output Variants
|--------------------------------------------------------------------------
*/

const availableOutputVariants = computed(() => {
    return props.outputVariants.map(variant => ({
        ...variant,

        label:
            variant.label ??
            [
                variant.product_name ?? variant.product?.name,
                variant.name,
                variant.sku ? `(${variant.sku})` : null,
            ].filter(Boolean).join(' - '),
    }))
})

/*
|--------------------------------------------------------------------------
| Available Material Variants
|--------------------------------------------------------------------------
*/

const availableMaterialVariants = computed(() => {
    return props.materialVariants.map(variant => ({
        ...variant,

        label:
            variant.label ??
            [
                variant.product_name ?? variant.product?.name,
                variant.name,
                variant.sku ? `(${variant.sku})` : null,
            ].filter(Boolean).join(' - '),
    }))
})


/*
|--------------------------------------------------------------------------
| Available Production Operations
|--------------------------------------------------------------------------
*/

const operationOptions = computed(() => {

    return props.productionOperations.map(operation => ({

        ...operation,

        label:
            operation.label ??
            `${operation.code ?? ''} - ${operation.name ?? ''}`,

    }))

})


/*
|--------------------------------------------------------------------------
| Variant Units
|--------------------------------------------------------------------------
*/

const getUnitsForVariant = (
    variantId
) => {

    if (!variantId) {

        return []

    }


      const variant =
        availableOutputVariants.value.find(
            item => Number(item.id) === Number(variantId)
        ) ??
        availableMaterialVariants.value.find(
            item => Number(item.id) === Number(variantId)
        )


    if (variant?.units?.length) {

        return variant.units.map(unit => ({

            ...unit,

            label:
                unit.label ??
                unit.unit?.name ??
                unit.name ??
                '',

            value:
                unit.id ?? unit.unit_id,

        }))

    }


    return props.variantUnits

        .filter(

            item =>

                Number(item.product_variant_id) ===
                Number(variantId)

        )

        .map(item => ({

            ...item,

            label:
                item.label ??
                item.unit?.name ??
                item.unit_name ??
                item.name ??
                '',

            value:
                item.id ?? item.unit_id,

        }))

}


/*
|--------------------------------------------------------------------------
| Add Operation
|--------------------------------------------------------------------------
*/

const addOperation = () => {

    form.operations.push(
        createEmptyOperation()
    )

}


/*
|--------------------------------------------------------------------------
| Remove Operation
|--------------------------------------------------------------------------
*/

const removeOperation = (
    index
) => {

    if (form.operations.length <= 1) {

        return

    }


    form.operations.splice(
        index,
        1
    )


    reSequenceOperations()

}


/*
|--------------------------------------------------------------------------
| Re-sequence Operations
|--------------------------------------------------------------------------
*/

const reSequenceOperations = () => {

    form.operations.forEach(

        (operation, index) => {

            operation.sequence =
                index + 1

        }

    )

}


/*
|--------------------------------------------------------------------------
| Move Operation
|--------------------------------------------------------------------------
*/

const moveOperation = (
    index,
    direction
) => {

    const targetIndex =
        index + direction


    if (
        targetIndex < 0 ||
        targetIndex >= form.operations.length
    ) {

        return

    }


    const current =
        form.operations[index]


    form.operations[index] =
        form.operations[targetIndex]


    form.operations[targetIndex] =
        current


    reSequenceOperations()


    /*
    |--------------------------------------------------------------------------
    | Update Material Operation Sequence
    |--------------------------------------------------------------------------
    */

    form.materials.forEach(material => {

        if (
            material.operation_sequence !== null &&
            material.operation_sequence !== ''
        ) {

            const selectedOperation =
                form.operations.find(

                    operation =>

                        Number(operation.sequence) ===
                        Number(material.operation_sequence)

                )


            if (!selectedOperation) {

                material.operation_sequence = null

            }

        }

    })

}


/*
|--------------------------------------------------------------------------
| Add Material
|--------------------------------------------------------------------------
*/

const addMaterial = () => {

    form.materials.push(
        createEmptyMaterial()
    )

}


/*
|--------------------------------------------------------------------------
| Remove Material
|--------------------------------------------------------------------------
*/

const removeMaterial = (
    index
) => {

    if (form.materials.length <= 1) {

        return

    }


    form.materials.splice(
        index,
        1
    )


    reSequenceMaterials()

}


/*
|--------------------------------------------------------------------------
| Re-sequence Materials
|--------------------------------------------------------------------------
*/

const reSequenceMaterials = () => {

    form.materials.forEach(

        (material, index) => {

            material.sequence =
                index + 1

        }

    )

}


/*
|--------------------------------------------------------------------------
| Change Material Variant
|--------------------------------------------------------------------------
*/

const changeMaterialVariant = (
    material
) => {

    material.unit_id =
        null


    if (!material.component_variant_id) {

        return

    }


    /*
    |--------------------------------------------------------------------------
    | Prevent Duplicate Material
    |--------------------------------------------------------------------------
    */

    const duplicate =
        form.materials.some(

            item =>

                item !== material

                &&

                Number(item.component_variant_id) ===
                Number(material.component_variant_id)

        )


    if (duplicate) {

        Swal.fire({

            icon:
                'warning',

            title:
                'Material Already Exist',

            text:
                'This material has already been added to the BOM.',

            confirmButtonText:
                'OK',

            buttonsStyling:
                false,

            customClass: {

                popup:
                    'rounded-2xl',

                confirmButton:
                    'rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-medium text-white',

            },

        }).then(() => {

            material.component_variant_id =
                null

            material.unit_id =
                null

        })

        return

    }


    /*
    |--------------------------------------------------------------------------
    | Default Unit
    |--------------------------------------------------------------------------
    */

    const units =
        getUnitsForVariant(
            material.component_variant_id
        )


    const defaultUnit =
        units.find(
            unit => unit.is_default
        )


    if (defaultUnit) {

        material.unit_id =
            defaultUnit.value

    }

}


/*
|--------------------------------------------------------------------------
| Change Output Variant
|--------------------------------------------------------------------------
*/

const changeOutputVariant = () => {

    form.output_unit_id =
        null


    /*
    |--------------------------------------------------------------------------
    | Prevent Output As Its Own Material
    |--------------------------------------------------------------------------
    */

    const duplicatedMaterial =
        form.materials.some(

            material =>

                Number(material.component_variant_id) ===
                Number(form.product_variant_id)

        )


    if (duplicatedMaterial) {

        Swal.fire({

            icon:
                'warning',

            title:
                'Invalid BOM Composition',

            text:
                'Output product cannot also be used as its own material.',

            confirmButtonText:
                'OK',

            buttonsStyling:
                false,

            customClass: {

                popup:
                    'rounded-2xl',

                confirmButton:
                    'rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-medium text-white',

            },

        })


        form.materials.forEach(material => {

            if (
                Number(material.component_variant_id) ===
                Number(form.product_variant_id)
            ) {

                material.component_variant_id = null
                material.unit_id = null

            }

        })

    }

}


/*
|--------------------------------------------------------------------------
| Output Units
|--------------------------------------------------------------------------
*/

const outputUnits = computed(() => {

    return getUnitsForVariant(
        form.product_variant_id
    )

})


/*
|--------------------------------------------------------------------------
| Validate Quantity
|--------------------------------------------------------------------------
*/

const validateMaterialQuantity = (
    material
) => {

    const qty =
        Number(material.qty)


    if (
        Number.isNaN(qty) ||
        qty <= 0
    ) {

        Swal.fire({

            icon:
                'warning',

            title:
                'Invalid Quantity',

            text:
                'Material quantity must be greater than zero.',

            confirmButtonText:
                'OK',

            buttonsStyling:
                false,

            customClass: {

                popup:
                    'rounded-2xl',

                confirmButton:
                    'rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-medium text-white',

            },

        }).then(() => {

            material.qty =
                1

        })


        return false

    }


    material.qty =
        qty


    return true

}


/*
|--------------------------------------------------------------------------
| Validate Scrap Percentage
|--------------------------------------------------------------------------
*/

const validateScrapPercent = (
    material
) => {

    const scrap =
        Number(material.scrap_percent)


    if (
        Number.isNaN(scrap) ||
        scrap < 0 ||
        scrap >= 100
    ) {

        Swal.fire({

            icon:
                'warning',

            title:
                'Invalid Scrap Percentage',

            text:
                'Scrap percentage must be between 0 and less than 100.',

            confirmButtonText:
                'OK',

            buttonsStyling:
                false,

            customClass: {

                popup:
                    'rounded-2xl',

                confirmButton:
                    'rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-medium text-white',

            },

        }).then(() => {

            material.scrap_percent =
                0

        })


        return false

    }


    material.scrap_percent =
        scrap


    return true

}


/*
|--------------------------------------------------------------------------
| Gross Quantity
|--------------------------------------------------------------------------
*/

const getGrossQuantity = (
    material
) => {

    const qty =
        Number(material.qty || 0)


    const scrap =
        Number(material.scrap_percent || 0)


    if (
        qty <= 0 ||
        scrap < 0 ||
        scrap >= 100
    ) {

        return 0

    }


    return qty / (1 - scrap / 100)

}


/*
|--------------------------------------------------------------------------
| Operation Label
|--------------------------------------------------------------------------
*/

const getOperationLabel = (
    sequence
) => {

    if (
        sequence === null ||
        sequence === ''
    ) {

        return 'General Material'

    }


    const operation =
        form.operations.find(

            item =>

                Number(item.sequence) ===
                Number(sequence)

        )


    if (!operation) {

        return 'General Material'

    }


    const selected =
        operationOptions.value.find(

            item =>

                Number(item.id) ===
                Number(operation.production_operation_id)

        )


    return selected?.label ??
        `Operation ${sequence}`

}


/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

const totalMaterials =
    computed(() => {

        return form.materials.length

    })


const totalOperations =
    computed(() => {

        return form.operations.length

    })


const totalGrossQuantity =
    computed(() => {

        return form.materials.reduce(

            (total, material) =>

                total +
                getGrossQuantity(material),

            0

        )

    })


/*
|--------------------------------------------------------------------------
| Number Formatting
|--------------------------------------------------------------------------
*/

const formatNumber = (
    value
) => {

    return new Intl.NumberFormat(

        'id-ID',

        {

            minimumFractionDigits:
                0,

            maximumFractionDigits:
                4,

        }

    ).format(

        Number(value || 0)

    )

}


/*
|--------------------------------------------------------------------------
| Currency / Not Applicable
|--------------------------------------------------------------------------
*/

const formatDate = (
    value
) => {

    return value || '-'

}

</script>


<template>
<AppLayout>
    <form
        @submit.prevent="handleSubmit"
        class="space-y-6"
    >

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
                    :href="
                        item.route === 'production-boms.show'
                            ? route(item.route, bom.id)
                            : route(item.route)
                    "
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
        <!-- Page Header -->
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

                    {{
                        mode === 'create'
                            ? 'Create Bill of Materials'
                            : mode === 'revision'
                                ? 'Create BOM Revision'
                                : 'Edit Bill of Materials'
                    }}

                </h1>


                <p
                    class="
                        mt-1
                        text-sm
                        text-gray-500
                    "
                >

                    Define product output, production routing and material composition.

                </p>

            </div>


            <div
                class="
                    flex
                    items-center
                    gap-2
                "
            >

                <span
                    class="
                        text-xs
                        text-gray-500
                    "
                >

                    BOM Number

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

                    {{
                        bom?.code ??
                        previewCode ??
                        'Auto generated'
                    }}

                </span>

            </div>

        </div>


        <!-- ========================================================= -->
        <!-- BOM Information -->
        <!-- ========================================================= -->

        <FormSection
            icon="📋"
            title="BOM Information"
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
                />

            </FormField>


            <!-- BOM Type -->

            <FormField
                label="BOM Type"
                required
                :error="form.errors.bom_type"
            >

                <SearchableSelect
                    v-model="form.bom_type"
                    :options="[
                        {
                            id: 'standard',
                            label: 'Standard',
                        },
                        {
                            id: 'alternative',
                            label: 'Alternative',
                        },
                    ]"
                    label="label"
                    value-key="id"
                    placeholder="Select BOM Type"
                />

            </FormField>


            <!-- Output Product Variant -->

            <FormField
                label="Output Product Variant"
                required
                :error="form.errors.product_variant_id"
            >

               <SearchableSelect
                    v-model="form.product_variant_id"
                    :options="availableOutputVariants"
                    label="label"
                    value-key="id"
                    placeholder="Select Finished Goods"
                    @update:modelValue="changeOutputVariant"
                />

            </FormField>


            <!-- BOM Name -->

            <FormField
                label="BOM Name"
                required
                :error="form.errors.name"
            >

                <FormInput
                    v-model="form.name"
                    placeholder="Enter BOM name"
                />

            </FormField>


            <!-- Output Quantity -->

            <FormField
                label="Output Quantity"
                required
                :error="form.errors.output_qty"
            >

                <FormInput
                    v-model="form.output_qty"
                    type="number"
                    min="0.0001"
                    step="0.0001"
                    placeholder="1"
                />

            </FormField>


            <!-- Output Unit -->

            <FormField
                label="Output Unit"
                required
                :error="form.errors.output_unit_id"
            >

                <SearchableSelect
                    v-model="form.output_unit_id"
                    :options="outputUnits"
                    label="label"
                    value-key="value"
                    placeholder="Select Output Unit"
                />

            </FormField>


            <!-- Effective From -->

            <FormField
                label="Effective From"
                :error="form.errors.effective_from"
            >

                <FlatPickr
                    v-model="form.effective_from"
                    :config="{
                        dateFormat: 'Y-m-d',
                        allowInput: true,
                    }"
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


            <!-- Effective Until -->

            <FormField
                label="Effective Until"
                :error="form.errors.effective_until"
            >

                <FlatPickr
                    v-model="form.effective_until"
                    :config="{
                        dateFormat: 'Y-m-d',
                        allowInput: true,
                    }"
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
                        placeholder="Add BOM description or notes..."
                    />

                </FormField>

            </div>

        </FormSection>


        <!-- ========================================================= -->
        <!-- Production Routing -->
        <!-- ========================================================= -->

        <FormSection
            icon="⚙️"
            title="Production Routing"
            description="Define the sequence of production operations."
            :columns="1"
        >

            <div class="space-y-4">


                <!-- Routing Table -->

                <div
                    class="
                        overflow-x-auto
                        rounded-xl
                        border
                        border-gray-200
                    "
                >

                    <div class="min-w-[850px] p-3">


                        <!-- Table Header -->

                        <div
                            class="
                                grid
                                grid-cols-[65px_minmax(240px,2fr)_160px_minmax(180px,2fr)_70px]
                                items-center
                                gap-2
                                border-b
                                border-gray-200
                                px-2
                                pb-2
                                text-xs
                                font-semibold
                                text-gray-500
                            "
                        >

                            <div>
                                Sequence
                            </div>

                            <div>
                                Production Operation
                            </div>

                            <div>
                                Duration (Minutes)
                            </div>

                            <div>
                                Instructions
                            </div>

                            <div class="text-center">
                                Action
                            </div>

                        </div>


                        <!-- Routing Rows -->

                        <div
                            v-for="(
                                operation,
                                index
                            ) in form.operations"

                            :key="index"

                            class="
                                grid
                                grid-cols-[65px_minmax(240px,2fr)_160px_minmax(180px,2fr)_70px]
                                items-start
                                gap-2
                                border-b
                                border-gray-100
                                px-2
                                py-3
                                last:border-b-0
                            "
                        >

                            <!-- Sequence -->

                            <FormField
                                label=" "
                            >

                                <div
                                    class="
                                        flex
                                        min-h-[38px]
                                        items-center
                                        justify-center
                                        rounded-lg
                                        border
                                        border-gray-200
                                        bg-gray-50
                                        text-sm
                                        font-semibold
                                        text-gray-700
                                    "
                                >

                                    {{ operation.sequence }}

                                </div>

                            </FormField>


                            <!-- Operation -->

                            <FormField
                                label="Operation"
                                required
                                :error="
                                    form.errors[
                                        `operations.${index}.production_operation_id`
                                    ]
                                "
                            >

                                <SearchableSelect
                                    v-model="operation.production_operation_id"
                                    :options="operationOptions"
                                    label="label"
                                    value-key="id"
                                    placeholder="Select Operation"
                                />

                            </FormField>


                            <!-- Duration -->

                            <FormField
                                label="Duration"
                                :error="
                                    form.errors[
                                        `operations.${index}.planned_duration_minutes`
                                    ]
                                "
                            >

                                <FormInput
                                    v-model="operation.planned_duration_minutes"
                                    type="number"
                                    min="0"
                                    step="1"
                                    placeholder="Minutes"
                                />

                            </FormField>


                            <!-- Instructions -->

                            <FormField
                                label="Instructions"
                                :error="
                                    form.errors[
                                        `operations.${index}.instructions`
                                    ]
                                "
                            >

                                <FormInput
                                    v-model="operation.instructions"
                                    placeholder="Optional instructions"
                                />

                            </FormField>


                            <!-- Actions -->

                            <div
                                class="
                                    flex
                                    min-h-[38px]
                                    items-center
                                    justify-center
                                    gap-1
                                "
                            >

                                <button
                                    type="button"
                                    class="
                                        inline-flex
                                        h-8
                                        w-8
                                        items-center
                                        justify-center
                                        rounded-lg
                                        text-gray-500
                                        transition
                                        hover:bg-gray-100
                                        disabled:opacity-30
                                    "
                                    :disabled="index === 0"
                                    title="Move up"
                                    @click="moveOperation(index, -1)"
                                >

                                    <ArrowUpIcon class="h-4 w-4" />

                                </button>


                                <button
                                    type="button"
                                    class="
                                        inline-flex
                                        h-8
                                        w-8
                                        items-center
                                        justify-center
                                        rounded-lg
                                        text-gray-500
                                        transition
                                        hover:bg-gray-100
                                        disabled:opacity-30
                                    "
                                    :disabled="
                                        index === form.operations.length - 1
                                    "
                                    title="Move down"
                                    @click="moveOperation(index, 1)"
                                >

                                    <ArrowDownIcon class="h-4 w-4" />

                                </button>


                                <button
                                    type="button"
                                    class="
                                        inline-flex
                                        h-8
                                        w-8
                                        items-center
                                        justify-center
                                        rounded-lg
                                        text-red-600
                                        transition
                                        hover:bg-red-50
                                    "
                                    title="Remove operation"
                                    @click="removeOperation(index)"
                                >

                                    <TrashIcon class="h-4 w-4" />

                                </button>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- Empty Routing -->

                <div
                    v-if="form.operations.length === 0"
                    class="
                        rounded-xl
                        border
                        border-dashed
                        border-gray-300
                        px-4
                        py-8
                        text-center
                        text-sm
                        text-gray-500
                    "
                >

                    No production operations added yet.

                </div>


                <!-- Add Operation -->

                <div class="flex justify-start">

                    <BaseButton
                        type="button"
                        variant="secondary"
                        @click="addOperation"
                    >

                        + Add Operation

                    </BaseButton>

                </div>

            </div>

        </FormSection>


        <!-- ========================================================= -->
        <!-- Material Composition -->
        <!-- ========================================================= -->

        <FormSection
            icon="📦"
            title="Material Composition"
            description="Define materials and quantities required to produce the output."
            :columns="1"
        >

            <div class="space-y-4">


                <!-- Material Table -->

                <div
                    class="
                        overflow-x-auto
                        rounded-xl
                        border
                        border-gray-200
                    "
                >

                    <div class="min-w-[1150px] p-3">


                        <!-- Table Header -->

                        <div
                            class="
                                grid
                                grid-cols-[minmax(250px,2fr)_130px_110px_110px_120px_minmax(180px,1.5fr)_70px]
                                items-center
                                gap-2
                                border-b
                                border-gray-200
                                px-2
                                pb-2
                                text-xs
                                font-semibold
                                text-gray-500
                            "
                        >

                            <div>
                                Component Variant
                            </div>

                            <div>
                                Unit
                            </div>

                            <div>
                                Net Qty
                            </div>

                            <div>
                                Scrap (%)
                            </div>

                            <div class="text-right">
                                Gross Qty
                            </div>

                            <div>
                                Production Operation
                            </div>

                            <div class="text-center">
                                Action
                            </div>

                        </div>


                        <!-- Material Rows -->

                        <div
                            v-for="(
                                material,
                                index
                            ) in form.materials"

                            :key="index"

                            class="
                                grid
                                grid-cols-[minmax(250px,2fr)_130px_110px_110px_120px_minmax(180px,1.5fr)_70px]
                                items-start
                                gap-2
                                border-b
                                border-gray-100
                                px-2
                                py-3
                                last:border-b-0
                            "
                        >

                            <!-- Component Variant -->

                            <FormField
                                label="Product Variant"
                                required
                                :error="
                                    form.errors[
                                        `materials.${index}.component_variant_id`
                                    ]
                                "
                            >

                                <SearchableSelect
                                    v-model="material.component_variant_id"
                                    :options="availableMaterialVariants"
                                    label="label"
                                    value-key="id"
                                    placeholder="Select Raw Material"
                                    @update:modelValue="
                                        changeMaterialVariant(material)
                                    "
                                />

                            </FormField>


                            <!-- Unit -->

                            <FormField
                                label="Unit"
                                required
                                :error="
                                    form.errors[
                                        `materials.${index}.unit_id`
                                    ]
                                "
                            >

                                <SearchableSelect
                                    v-model="material.unit_id"
                                    :options="
                                        getUnitsForVariant(
                                            material.component_variant_id
                                        )
                                    "
                                    label="label"
                                    value-key="value"
                                    placeholder="Select Unit"
                                />

                            </FormField>


                            <!-- Net Quantity -->

                            <FormField
                                label="Net Qty"
                                required
                                :error="
                                    form.errors[
                                        `materials.${index}.qty`
                                    ]
                                "
                            >

                                <FormInput
                                    v-model="material.qty"
                                    type="number"
                                    min="0.0001"
                                    step="0.0001"
                                    placeholder="0"
                                    @change="
                                        validateMaterialQuantity(material)
                                    "
                                />

                            </FormField>


                            <!-- Scrap -->

                            <FormField
                                label="Scrap (%)"
                                :error="
                                    form.errors[
                                        `materials.${index}.scrap_percent`
                                    ]
                                "
                            >

                                <FormInput
                                    v-model="material.scrap_percent"
                                    type="number"
                                    min="0"
                                    max="99.99"
                                    step="0.01"
                                    placeholder="0"
                                    @change="
                                        validateScrapPercent(material)
                                    "
                                />

                            </FormField>


                            <!-- Gross Quantity -->

                            <FormField
                                label="Gross Qty"
                            >

                                <div
                                    class="
                                        flex
                                        min-h-[38px]
                                        items-center
                                        justify-end
                                        rounded-lg
                                        border
                                        border-gray-200
                                        bg-gray-50
                                        px-2
                                        text-sm
                                        font-semibold
                                        text-gray-800
                                        whitespace-nowrap
                                    "
                                >

                                    {{
                                        formatNumber(
                                            getGrossQuantity(material)
                                        )
                                    }}

                                </div>

                            </FormField>


                            <!-- Operation -->

                            <FormField
                                label="Production Operation"
                                :error="
                                    form.errors[
                                        `materials.${index}.operation_sequence`
                                    ]
                                "
                            >

                                <SearchableSelect
                                    v-model="material.operation_sequence"
                                    :options="[
                                        {
                                            id: null,
                                            label: 'General Material',
                                        },
                                        ...form.operations.map(operation => ({
                                            id: operation.sequence,
                                            label: getOperationLabel(
                                                operation.sequence
                                            ),
                                        })),
                                    ]"
                                    label="label"
                                    value-key="id"
                                    placeholder="Select Operation"
                                />

                            </FormField>


                            <!-- Remove -->

                            <div
                                class="
                                    flex
                                    min-h-[38px]
                                    items-center
                                    justify-center
                                "
                            >

                                <button
                                    v-if="form.materials.length > 1"
                                    type="button"
                                    class="
                                        inline-flex
                                        h-9
                                        w-9
                                        items-center
                                        justify-center
                                        rounded-lg
                                        text-red-600
                                        transition
                                        hover:bg-red-50
                                        hover:text-red-700
                                        focus:outline-none
                                        focus:ring-2
                                        focus:ring-red-500
                                        focus:ring-offset-1
                                    "
                                    title="Remove material"
                                    @click="removeMaterial(index)"
                                >

                                    <TrashIcon class="h-5 w-5" />

                                </button>

                            </div>


                            <!-- Material Notes -->

                            <div class="col-span-7">

                                <FormField
                                    label="Notes"
                                    :error="
                                        form.errors[
                                            `materials.${index}.notes`
                                        ]
                                    "
                                >

                                    <FormInput
                                        v-model="material.notes"
                                        placeholder="Optional material notes..."
                                    />

                                </FormField>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- Mobile Hint -->

                <div
                    class="
                        flex
                        items-center
                        gap-2
                        text-xs
                        text-gray-400
                        lg:hidden
                    "
                >

                    <span>←</span>

                    <span>
                        Geser tabel ke samping untuk melihat semua kolom
                    </span>

                    <span>→</span>

                </div>


                <!-- Empty Material -->

                <div
                    v-if="form.materials.length === 0"
                    class="
                        rounded-xl
                        border
                        border-dashed
                        border-gray-300
                        px-4
                        py-8
                        text-center
                        text-sm
                        text-gray-500
                    "
                >

                    No materials added yet.

                </div>


                <!-- Add Material -->

                <div class="flex justify-start">

                    <BaseButton
                        type="button"
                        variant="secondary"
                        @click="addMaterial"
                    >

                        + Add Material

                    </BaseButton>

                </div>

            </div>

        </FormSection>


        <!-- ========================================================= -->
        <!-- Summary -->
        <!-- ========================================================= -->

        <div
            class="
                mt-6
                flex
                justify-end
            "
        >

            <div
                class="
                    w-full
                    max-w-md
                    rounded-xl
                    border
                    bg-gray-50
                    p-5
                "
            >

                <div
                    class="
                        flex
                        justify-between
                        py-2
                        text-sm
                    "
                >

                    <span>
                        Total Production Operations
                    </span>

                    <span class="font-medium">
                        {{ totalOperations }}
                    </span>

                </div>


                <div
                    class="
                        flex
                        justify-between
                        py-2
                        text-sm
                    "
                >

                    <span>
                        Total Materials
                    </span>

                    <span class="font-medium">
                        {{ totalMaterials }}
                    </span>

                </div>


                <div
                    class="
                        flex
                        justify-between
                        border-t
                        pt-3
                        text-base
                        font-semibold
                    "
                >

                    <span>
                        Total Gross Material Quantity
                    </span>

                    <span>
                        {{ formatNumber(totalGrossQuantity) }}
                    </span>

                </div>

            </div>

        </div>


        <!-- ========================================================= -->
        <!-- Actions -->
        <!-- ========================================================= -->

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

                {{
                    mode === 'create'
                        ? 'Save'
                        : 'Update'
                }}

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