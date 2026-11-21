<script setup>
import { useForm, router } from '@inertiajs/vue3'
import Form from './Form.vue'

const props = defineProps({
    title: {
        type: String,
        default: 'Create Production Order',
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
})

const form = useForm({
    branch_id: null,
    production_bom_id: null,
    production_bom_version_id: null,
    source_warehouse_id: null,
    destination_warehouse_id: null,
    order_date: null,
    planned_start_date: null,
    planned_end_date: null,
    planned_quantity: 1,
    description: '',
})

const submit = () => {
    form.post(route('production-orders.store'), {
        preserveScroll: true,
    })
}

const submitAndNew = () => {
    form.post(route('production-orders.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            form.clearErrors()
        },
    })
}

const cancel = () => {
    router.visit(route('production-orders.index'))
}
</script>

<template>
    <Form
        :form="form"
        :preview-code="previewCode"
        :branches="branches"
        :warehouses="warehouses"
        :production-boms="productionBoms"
        :order="order"
        :mode="mode"
        @submit="submit"
        @cancel="cancel"
    />
</template>