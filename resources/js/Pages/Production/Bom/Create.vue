<script setup>
import { useForm, router } from '@inertiajs/vue3'
import Form from './Form.vue'

const props = defineProps({
    title: {
        type: String,
        default: 'Create Bill of Materials',
    },
    mode: {
        type: String,
        default: 'create',
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
})

const form = useForm({
    branch_id: null,
    product_variant_id: null,
    name: '',
    bom_type: 'standard',
    description: '',
    output_qty: 1,
    output_unit_id: null,
    effective_from: null,
    effective_until: null,
    revision_notes: '',
    operations: [],
    materials: [],
})

const submit = () => {
    form.post(route('production-boms.store'), {
        preserveScroll: true,
    })
}

const submitAndNew = () => {
    form.post(route('production-boms.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            form.clearErrors()
        },
    })
}

const cancel = () => {
    router.visit(route('production-boms.index'))
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