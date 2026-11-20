<script setup>
import { useForm, router } from '@inertiajs/vue3'
import Form from './Form.vue'

const props = defineProps({
    title: {
        type: String,
        default: 'Edit Bill of Materials',
    },
    mode: {
        type: String,
        default: 'edit',
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
        required: true,
    },
    draftVersion: {
        type: Object,
        required: true,
    },
})

const version = props.draftVersion

const form = useForm({
    branch_id: props.bom.branch_id,
    product_variant_id: props.bom.product_variant_id,
    name: props.bom.name ?? '',
    bom_type: props.bom.bom_type ?? 'standard',
    description: props.bom.description ?? '',

    output_qty: version.output_qty ?? 1,
    output_unit_id: version.output_unit_id ?? null,
    effective_from: version.effective_from ?? null,
    effective_until: version.effective_until ?? null,
    revision_notes: version.revision_notes ?? '',

    operations: (version.operations ?? []).map(operation => ({
        production_operation_id: operation.production_operation_id,
        sequence: operation.sequence,
        planned_duration_minutes: operation.planned_duration_minutes,
        instructions: operation.instructions ?? '',
    })),

    materials: (version.materials ?? []).map(material => ({
        component_variant_id: material.component_variant_id,
        unit_id: material.unit_id,
        qty: material.qty,
        scrap_percent: material.scrap_percent ?? 0,

        // Database menyimpan production_bom_operation_id.
        // Form menggunakan operation_sequence.
        operation_sequence:
            material.production_bom_operation?.sequence ?? null,

        sequence: material.sequence,
        notes: material.notes ?? '',
    })),
})

const submit = () => {
    form.put(route('production-boms.update', props.bom.id), {
        preserveScroll: true,
    })
}

const cancel = () => {
    router.visit(route('production-boms.show', props.bom.id))
}
</script>

<template>
   <Form
    :form="form"
    :preview-code="previewCode"
    :branches="branches"
    :output-variants="outputVariants"
    :material-variants="materialVariants"
    :variant-units="variantUnits"
    :production-operations="productionOperations"
    :bom="bom"
    :mode="mode"
    @submit="submit"
    @cancel="cancel"
/>
</template>