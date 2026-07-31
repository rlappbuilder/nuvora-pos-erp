<script setup>

import AppLayout from '@/Layouts/AppLayout.vue'

import { Head, router } from '@inertiajs/vue3'

import PageHeader from '@/Components/Layout/PageHeader.vue'

import Card from '@/Components/Layout/Card.vue'

import ActionBar from '@/Components/Layout/ActionBar.vue'

import ButtonGroup from '@/Components/Button/ButtonGroup.vue'

import BaseButton from '@/Components/Button/BaseButton.vue'

import DetailRow from '@/Components/Display/DetailRow.vue'
import {
    formatDecimal,
} from '@/Utils'

import {
    formatDate,
    formatCurrency,
} from '@/Utils'


const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
})

function back()
{
    router.visit(
        route('products.index')
    )
}

function edit()
{
    router.visit(
        route(
            'products.edit',
            props.product.id
        )
    )
}
function print()
{
    router.get(

        route(

            'products.print',

            props.product.id

        )

    )
}
</script>
<template>

<Head />

<AppLayout>

    <PageHeader
                :breadcrumb="[
                    'Master Data',
                    'Product',
                    'Detail Product'
                ]"
                icon="📂"
                title="Product"
                subtitle="Manage your products."
            />

    <ActionBar />

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

        <Card
            icon="📂"
            title="General Information"
            description="Basic information about this prduct."
        >
            <!-- General Information -->
            <DetailRow
                label="Code"
                :value="product.code"
            />

            <DetailRow
                label="SKU"
                :value="product.sku"
            />

            <DetailRow
                label="Name"
                :value="product.name"
            />

            <DetailRow
                label="Category"
                :value="product.category?.name ?? '-'"
            />

            <DetailRow
                label="Brand"
                :value="product.brand?.name ?? '-'"
            />

            <DetailRow
                label="Unit"
                :value="product.unit?.name ?? '-'"
            />
            <DetailRow label="Product Type">

            <span
                :class="[
                    'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold',
                    product.product_type === 'PRODUCT'
                        ? 'bg-blue-100 text-blue-700'
                        : 'bg-purple-100 text-purple-700'
                ]"
            >
                {{ product.product_type }}
            </span>

        </DetailRow>
        <DetailRow label="Track Stock">

            <span
                :class="[
                    'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold',
                    product.track_stock
                        ? 'bg-green-100 text-green-700'
                        : 'bg-red-100 text-red-700'
                ]"
            >
                {{ product.track_stock ? '✓ Yes' : '✕ No' }}
            </span>

        </DetailRow>
        <DetailRow label="Sellable">

        <span
            :class="[
                'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold',
                product.is_sellable
                    ? 'bg-green-100 text-green-700'
                    : 'bg-red-100 text-red-700'
            ]"
        >
            {{ product.is_sellable ? '✓ Yes' : '✕ No' }}
        </span>

    </DetailRow>
    <DetailRow
        label="Minimum Stock"
        :value="formatDecimal(product.minimum_stock)"
    />
    <DetailRow label="Status">

        <span
            :class="[
                'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold',
                product.is_active
                    ? 'bg-green-100 text-green-700'
                    : 'bg-red-100 text-red-700'
            ]"
        >
            {{ product.is_active ? '🟢 Active' : '🔴 Inactive' }}
        </span>

    </DetailRow>
        </Card>

        <Card
            icon="⚙️"
            title="System Information"
            description="Audit information."
        >
            <!-- System Information -->
             <DetailRow
                label="Created By"
                :value="product.creator?.name ?? '-'"
            />

            <DetailRow
                label="Created At"
                :value="formatDate(product.created_at)"
            />

            <DetailRow
                label="Updated By"
                :value="product.updater?.name ?? '-'"
            />

            <DetailRow
                label="Updated At"
                :value="formatDate(product.updated_at)"
            />
        </Card>


    </div>

    
        <!-- Description -->
         <Card
            icon="📝"
            title="Description"
        >

            <div
                class="
                    whitespace-pre-line
                    text-sm
                    leading-7
                    text-gray-700
                "
            >
                {{ product.description || '-' }}
            </div>

        </Card>
        <ActionBar>

        <ButtonGroup>

            <BaseButton
                color="secondary"
                @click="back"
            >
                ← Back
            </BaseButton>

            <BaseButton
                color="warning"
                @click="edit"
            >
                ✏ Edit
            </BaseButton>

        </ButtonGroup>

    </ActionBar>
    

</AppLayout>

</template>