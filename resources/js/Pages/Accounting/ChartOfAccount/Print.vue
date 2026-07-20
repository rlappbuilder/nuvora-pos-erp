<script setup>

import PrintLayout from '@/Components/Layout/PrintLayout.vue'
import Card from '@/Components/Layout/Card.vue'
import DetailRow from '@/Components/Display/DetailRow.vue'
import PrintSection from '@/Components/Print/PrintSection.vue'
import PrintSignature from '@/Components/Print/PrintSignature.vue'
import { formatDate } from '@/Utils'
import { Head } from '@inertiajs/vue3'

import { onMounted } from 'vue'
const props = defineProps({

    chartOfAccount: {

        type: Object,

        required: true,

    },

})

onMounted(() => {

    window.print()

})

</script>

<template>

<Head title="Print Chart Of Account" />

<PrintLayout

    :company="chartOfAccount.company"

    title="Chart Of Account"

    subtitle="Account Detail Report"

    orientation="portrait"

    :document-number="chartOfAccount.code"

    :document-date="formatDate(chartOfAccount.created_at)"

    printed-by="Administrator"

    :back-url="route(
        'chart-of-accounts.show',
        chartOfAccount.id
    )"

>

    <div
        class="
            grid
            grid-cols-1
            gap-6
            mt-8
        "
    >

       <PrintSection
            title="General Information"
        >

            <DetailRow
                label="Code"
                :value="chartOfAccount.code"
            />

            <DetailRow
                label="Name"
                :value="chartOfAccount.name"
            />

            <DetailRow
                label="Parent"
                :value="
                    chartOfAccount.parent?.name ??
                    '-'
                "
            />

       </PrintSection>

        <PrintSection
            title="Accounting Information"
        >

            <DetailRow
                label="Category"
                :value="
                    chartOfAccount.account_category?.name
                "
            />

            <DetailRow
                label="Normal Balance"
                :value="
                    chartOfAccount.normal_balance
                "
            />

            <DetailRow
                label="Opening Balance"
                :value="
                    chartOfAccount.opening_balance
                "
            />

            <DetailRow
                label="Header"
                :value="
                    chartOfAccount.is_header
                        ? 'Yes'
                        : 'No'
                "
            />

            <DetailRow
                label="Posting"
                :value="
                    chartOfAccount.is_posting
                        ? 'Yes'
                        : 'No'
                "
            />

            <DetailRow
                label="Status"
                :value="
                    chartOfAccount.status
                        ? 'Active'
                        : 'Inactive'
                "
            />

        </PrintSection>

        <PrintSection
            title="Description"
        >

            <DetailRow
                label="Description"
                :value="
                    chartOfAccount.description ??
                    '-'
                "
            />

        </PrintSection>
        
        <PrintSection
            title="Audit Information"
        >

            <DetailRow
                label="Created At"
                :value="
                    formatDate(
                        chartOfAccount.created_at
                    )
                "
            />

            <DetailRow
                label="Updated At"
                :value="
                    formatDate(
                        chartOfAccount.updated_at
                    )
                "
            />

        </PrintSection>
        <PrintSignature />
    </div>

</PrintLayout>

</template>