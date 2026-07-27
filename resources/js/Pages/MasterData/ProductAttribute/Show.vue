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

    productAttribute: {

        type: Object,

        required: true,

    },

})

function back()
{
    router.visit(
        route('product-attributes.index')
    )
}

function edit()
{
    router.visit(
        route(
            'product-attributes.edit',
            props.productAttribute.id
        )
    )
}
function print()
{
    router.get(

        route(

            'product-attributes.print',

            props.productAttribute.id

        )

    )
}
</script>
<template>

<Head />

<AppLayout>

    <PageHeader
    icon="📂"
    title="Product Attribute Detail"
    subtitle="View product attribute information."
    />

    <ActionBar />

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

        <Card
            icon="📂"
            title="General Information"
            description="Basic information about this prduct attribut."
        >
            <!-- General Information -->
             <DetailRow
                    label="Code"
                    :value="productAttribute.code"
                />

                <DetailRow
                    label="Name"
                    :value="productAttribute.name"
                />

                <DetailRow
                    label="Display Name"
                    :value="productAttribute.display_name"
                />

                <DetailRow
                    label="Input Type"
                    :value="productAttribute.input_type"
                />
               <DetailRow label="Required">
                    <span
                        :class="[
                            'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold',
                            productAttribute.is_required
                                ? 'bg-green-100 text-green-700'
                                : 'bg-red-100 text-red-700'
                        ]"
                    >
                        {{ productAttribute.is_required ? '✓ Yes' : '✕ No' }}
                    </span>
                </DetailRow>
             <DetailRow label="Variant">
                <span
                    :class="[
                        'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold',
                        productAttribute.is_variant
                            ? 'bg-green-100 text-green-700'
                            : 'bg-red-100 text-red-700'
                    ]"
                >
                    {{ productAttribute.is_variant ? '✓ Yes' : '✕ No' }}
                </span>
            </DetailRow>
                <DetailRow
                    label="Sort Order"
                    :value="productAttribute.sort_order"
                />
           
              <DetailRow label="Status">
                <span
                    :class="[
                        'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold',
                        productAttribute.is_active
                            ? 'bg-green-100 text-green-700'
                            : 'bg-red-100 text-red-700'
                    ]"
                >
                    {{ productAttribute.is_active ? '🟢 Active' : '🔴 Inactive' }}
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
                :value="productAttribute.creator?.name ?? '-'"
            />

            <DetailRow
                label="Created At"
                :value="formatDate(productAttribute.created_at)"
            />

            <DetailRow
                label="Updated By"
                :value="productAttribute.updater?.name ?? '-'"
            />

            <DetailRow
                label="Updated At"
                :value="formatDate(productAttribute.updated_at)"
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
                {{ productAttribute.description || '-' }}
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