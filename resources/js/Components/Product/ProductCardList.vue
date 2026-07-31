<script setup>
import ProductCard from '@/Components/Product/ProductCard.vue'

defineProps({
    products: {
        type: Object,
        required: true,
    },

    selected: {
        type: Array,
        default: () => [],
    },
})

const emit = defineEmits([
    'select',
    'view',
    'edit',
    'duplicate',
    'delete',
])
</script>

<template>
    <div
        v-if="products.data.length"
        class="space-y-3"
    >
        <ProductCard
            v-for="product in products.data"
            :key="product.id"
            :product="product"
            :selected="selected.includes(product.id)"
            @select="emit('select', $event)"
            @view="emit('view', $event)"
            @edit="emit('edit', $event)"
            @duplicate="emit('duplicate', $event)"
            @delete="emit('delete', $event)"
        />
    </div>

    <div
        v-else
        class="rounded-xl border border-dashed border-gray-300 bg-white py-16 text-center"
    >
        <div class="text-5xl mb-4">
            📦
        </div>

        <h3 class="text-lg font-semibold text-gray-900">
            No products found
        </h3>

        <p class="mt-2 text-sm text-gray-500">
            Try changing your search or filter.
        </p>
    </div>
</template>