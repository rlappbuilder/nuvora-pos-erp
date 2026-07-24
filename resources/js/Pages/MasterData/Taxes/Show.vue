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
    formatDate,
    formatCurrency,
} from '@/Utils'


const props = defineProps({

    tax: {

        type: Object,

        required: true,

    },

})

function back()
{
    router.visit(
        route('taxes.index')
    )
}

function edit()
{
    router.visit(
        route(
            'taxes.edit',
            props.tax.id
        )
    )
}
function print()
{
    router.get(

        route(

            'taxes.print',

            props.tax.id

        )

    )
}
</script>
<template>

<Head />

<AppLayout>

    <PageHeader
    icon="📂"
    title="Tax Detail"
    subtitle="View tax information."
    />

    <ActionBar />

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

        <Card
            icon="📂"
            title="General Information"
            description="Basic information about this tax."
        >
            <!-- General Information -->
             <DetailRow
                    label="Code"
                    :value="tax.code"
                />

                <DetailRow
                    label="Name"
                    :value="tax.name"
                />

                <DetailRow
                    label="Type"
                    :value="tax.type"
                />

                <DetailRow
                    label="Rate"
                    :value="
                        tax.type === 'Percentage'
                            ? `${tax.rate}%`
                            : formatCurrency(tax.rate)
                    "
                />

                <DetailRow
                    label="Default Tax"
                    :value="tax.is_default ? 'Yes' : 'No'"
                    :icon="tax.is_default ? '✅' : '❌'"
                />

                <DetailRow
                    label="Status"
                    :value="tax.is_active ? 'Active' : 'Inactive'"
                    :icon="tax.is_active ? '🟢' : '🔴'"
                />
        </Card>

        <Card
            icon="⚙️"
            title="System Information"
            description="Audit information."
        >
            <!-- System Information -->
             <DetailRow
                label="Created By"
                :value="tax.created_by?.name ?? '-'"
            />

            <DetailRow
                label="Created At"
                :value="formatDate(tax.created_at)"
            />

            <DetailRow
                label="Updated By"
                :value="tax.updated_by?.name ?? '-'"
            />

            <DetailRow
                label="Updated At"
                :value="formatDate(tax.updated_at)"
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
                {{ tax.description || '-' }}
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