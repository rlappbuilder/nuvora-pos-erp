<script setup>
import Card from '@/Components/Layout/Card.vue'
import StatusBadge from '@/Components/Display/StatusBadge.vue'


const props = defineProps({
    product: {
        type: Object,
        required: true,
    },

    selected: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits([
    'select',
    'view',
    'edit',
    'duplicate',
    'delete',
])
import {
    Eye,
    Pencil,
    Copy,
    EllipsisVertical,
} from 'lucide-vue-next'
</script>

<template>
    <Card
        class="border border-gray-200 hover:border-primary-500 hover:shadow-md transition-all duration-200 cursor-pointer"
        @click="emit('view', product)"
    >
        <div class="flex items-center gap-4 px-4 py-3">

            <!-- Checkbox -->
            <div class="w-10 flex justify-center">
                <input
                type="checkbox"
                :checked="selected"
                class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                @click.stop
                @change="emit('select', product)"
            >
            </div>

            <!-- Product -->
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <span class="text-xl">📦</span>

                    <span class="font-semibold text-gray-900 truncate">
                        {{ product.name }}
                    </span>
                </div>

                <div class="text-xs text-gray-500 mt-1">
                    {{ product.code }} • SKU : {{ product.sku }}
                </div>
            </div>

            <!-- Category -->
            <div class="w-40">
                {{ product.category?.name ?? '-' }}
            </div>

            <!-- Brand -->
            <div class="w-40">
                {{ product.brand?.name ?? '-' }}
            </div>

            <!-- Unit -->
            <div class="w-24">
                {{ product.unit?.name ?? '-' }}
            </div>

            <!-- Product Type -->
            <div class="w-28">
                <span
                    class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium"
                    :class="{
                        'bg-blue-100 text-blue-700': product.product_type === 'PRODUCT',
                        'bg-amber-100 text-amber-700': product.product_type === 'SERVICE',
                    }"
                >
                    {{ product.product_type === 'PRODUCT' ? 'Product' : 'Service' }}
                </span>
            </div>

            <!-- Status -->
            <div class="w-28">
                <StatusBadge
                    :status="product.is_active"
                />
            </div>

            <!-- Action -->
            
            <div
                class="w-36 flex items-center justify-end gap-1"
                @click.stop
            >
                <button
                    type="button"
                    class="rounded-lg p-2 hover:bg-gray-100 transition-colors"
                    title="View"
                    @click.stop="emit('view', product)"
                >
                    <Eye class="h-4 w-4" />
                </button>

                <button
                    type="button"
                    class="rounded-lg p-2 hover:bg-gray-100 transition-colors"
                    title="Edit"
                    @click.stop="emit('edit', product)"
                >
                    <Pencil class="h-4 w-4" />
                </button>

                <button
                    type="button"
                    class="rounded-lg p-2 hover:bg-gray-100 transition-colors"
                    title="Duplicate"
                    @click.stop="emit('duplicate', product)"
                >
                    <Copy class="h-4 w-4" />
                </button>

                <button
                    type="button"
                    class="rounded-lg p-2 hover:bg-gray-100 transition-colors"
                    title="More"
                >
                    <EllipsisVertical class="h-4 w-4" 
                    @click.stop="emit('delete', product)"
                    
                    />
                </button>
            </div>
            <!-- end action-->

        </div>
    </Card>
</template>