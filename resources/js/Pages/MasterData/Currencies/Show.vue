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
} from '@/Utils/formatter'
import {
    formatDate,
    formatCurrency,
} from '@/Utils'


const props = defineProps({

    currency: {

        type: Object,

        required: true,

    },

})

function back()
{
    router.visit(
        route('currencies.index')
    )
}

function edit()
{
    router.visit(
        route(
            'currencies.edit',
            props.currency.id
        )
    )
}
function print()
{
    router.get(

        route(

            'currencies.print',

            props.currency.id

        )

    )
}
</script>
<template>

<Head />

<AppLayout>

    <PageHeader
    icon="📂"
    title="Currency Detail"
    subtitle="View currency information."
    />

    <ActionBar />

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

        <Card
            icon="📂"
            title="General Information"
            description="Basic information about this currency."
        >
            <!-- General Information -->
             <DetailRow
                    label="Code"
                    :value="currency.code"
                />

                <DetailRow
                    label="Name"
                    :value="currency.name"
                />

                <DetailRow
                    label="Symbol"
                    :value="currency.symbol"
                />

                <DetailRow
                    label="Decimal Places"
                    :value="currency.decimal_places"
                />

                <DetailRow
                    label="Exchange Rate"
                    :value="formatDecimal(currency.exchange_rate)"
                />

                <DetailRow
                    label="Base Currency"
                    :value="currency.is_base_currency ? 'Yes' : 'No'"
                    :icon="currency.is_base_currency ? '✅' : '❌'"
                />
           
                <DetailRow
                    label="Status"
                    :value="currency.is_active ? 'Active' : 'Inactive'"
                    :icon="currency.is_active ? '🟢' : '🔴'"
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
                :value="currency.created_by?.name ?? '-'"
            />

            <DetailRow
                label="Created At"
                :value="formatDate(currency.created_at)"
            />

            <DetailRow
                label="Updated By"
                :value="currency.updated_by?.name ?? '-'"
            />

            <DetailRow
                label="Updated At"
                :value="formatDate(currency.updated_at)"
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
                {{ currency.description || '-' }}
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