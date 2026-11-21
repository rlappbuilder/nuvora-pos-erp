<script setup>
import { useForm, router } from '@inertiajs/vue3'
import Form from './Form.vue'

const props = defineProps({
    title: {
        type: String,
        default: 'Edit Production Order',
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
        required: true,
    },
})

const form = useForm({
    branch_id: props.order.branch_id ?? null,

    production_bom_id: props.order.production_bom_id ?? null,
    production_bom_version_id: props.order.production_bom_version_id ?? null,

    source_warehouse_id: props.order.source_warehouse_id ?? null,
    destination_warehouse_id: props.order.destination_warehouse_id ?? null,

    order_date: props.order.order_date ?? null,
    planned_start_date: props.order.planned_start_date ?? null,
    planned_end_date: props.order.planned_end_date ?? null,

    planned_quantity: props.order.details?.planned_quantity ?? 1,

    description: props.order.description ?? '',
})

const submit = () => {
    form.put(route('production-orders.update', props.order.id), {
        preserveScroll: true,
    })
}

const cancel = () => {
    router.visit(route('production-orders.show', props.order.id))
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