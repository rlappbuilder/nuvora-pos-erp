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

    productAttributeValue: {

        type: Object,

        required: true,

    },

})

function back()
{
    router.visit(
        route('product-attribute-values.index')
    )
}

function edit()
{
    router.visit(
        route(
            'product-attributes-values.edit',
            props.productAttributeValue.id
        )
    )
}
function print()
{
    router.get(

        route(

            'product-attribute-values.print',

            props.productAttributeValue.id

        )

    )
}
</script>
<template>

<Head />

<AppLayout>

    <PageHeader
    icon="📂"
    title="Product Attribute Value Detail"
    subtitle="View product attribute value information."
    />

    <ActionBar />

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

        <Card
            icon="📂"
            title="General Information"
            description="Basic information about this prduct attribut value."
        >
            <!-- General Information -->
             <DetailRow
                    label="Code"
                    :value="productAttributeValue.code"
                />

                <DetailRow
                    label="Value"
                    :value="productAttributeValue.value"
                />

                <DetailRow
                    label="Display Display Value"
                    :value="productAttributeValue.display_value"
                />

                <DetailRow
                    label="Color Code"
                    :value="productAttributeValue.color_code"
                />
         
          
                <DetailRow
                    label="Sort Order"
                    :value="productAttributeValue.sort_order"
                />
           
              <DetailRow label="Status">
                <span
                    :class="[
                        'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold',
                        productAttributeValue.is_active
                            ? 'bg-green-100 text-green-700'
                            : 'bg-red-100 text-red-700'
                    ]"
                >
                    {{ productAttributeValue.is_active ? '🟢 Active' : '🔴 Inactive' }}
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
                :value="productAttributeValue.creator?.name ?? '-'"
            />

            <DetailRow
                label="Created At"
                :value="formatDate(productAttributeValue.created_at)"
            />

            <DetailRow
                label="Updated By"
                :value="productAttributeValue.updater?.name ?? '-'"
            />

            <DetailRow
                label="Updated At"
                :value="formatDate(productAttributeValue.updated_at)"
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
                {{ productAttributeValue.description || '-' }}
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