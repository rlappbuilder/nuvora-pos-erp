<script setup>


import { Head, router } from '@inertiajs/vue3'

import PageHeader from '@/Components/Layout/PageHeader.vue'
import Card from '@/Components/Layout/Card.vue'
import ActionBar from '@/Components/Layout/ActionBar.vue'

import ButtonGroup from '@/Components/Button/ButtonGroup.vue'
import BaseButton from '@/Components/Button/BaseButton.vue'

import DetailRow from '@/Components/Display/DetailRow.vue'

import { formatDate } from '@/Utils'

import {
    ClipboardDocumentListIcon,
    ArrowLeftIcon,
    PencilSquareIcon,
    PrinterIcon,
} from '@heroicons/vue/24/outline'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({

    chartOfAccount: {

        type: Object,

        required: true,

    },

})

function back()
{
    router.visit(
        route('chart-of-accounts.index')
    )
}

function edit()
{
    router.visit(
        route(
            'chart-of-accounts.edit',
            props.chartOfAccount.id
        )
    )
}

function print()
{
    router.get(
        route(
            'chart-of-accounts.print',
            props.chartOfAccount.id
        )
    )
}

</script>
<template>

<Head title="Chart Of Account Detail" />

<AppLayout>

<div
    class="
        mx-auto
        max-w-7xl
        space-y-6
        px-6
        py-6
    "
>

<PageHeader
    title="Chart Of Account Detail"
    subtitle="View chart of account information."
>

    <template #icon>

        <ClipboardDocumentListIcon
            class="h-6 w-6"
        />

    </template>

</PageHeader>


<div
    class="
        grid
        grid-cols-1
        gap-6
        xl:grid-cols-2
    "
>

    <!-- General Information -->

    <Card
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
            label="Parent Account"
            :value="
                chartOfAccount.parent?.name ??
                '-'
            "
        />

    </Card>

    <!-- Accounting Information -->

    <Card
        title="Accounting Information"
    >

        <DetailRow
            label="Account Category"
            :value="
                chartOfAccount.account_category?.name ??
                '-'
            "
        />

        <DetailRow
            label="Normal Balance"
            :value="chartOfAccount.normal_balance"
        />

        <DetailRow
            label="Opening Balance"
            :value="chartOfAccount.opening_balance"
        />

        <DetailRow
            label="Level"
            :value="chartOfAccount.level"
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

    </Card>

    <!-- Organization -->

    <Card
        title="Organization"
    >

        <DetailRow
            label="Company"
            :value="
                chartOfAccount.company?.company_name ??
                '-'
            "
        />

    </Card>

    <!-- Description -->

    <Card
        title="Description"
    >

        <DetailRow
            label="Description"
            :value="
                chartOfAccount.description ??
                '-'
            "
        />

    </Card>

    <!-- Audit Information -->

    <Card
        title="Audit Information"
        class="xl:col-span-2"
    >

        <DetailRow
            label="Created By"
            :value="
                chartOfAccount.creator?.name ??
                '-'
            "
        />

        <DetailRow
            label="Created At"
            :value="
                formatDate(
                    chartOfAccount.created_at
                )
            "
        />

        <DetailRow
            label="Updated By"
            :value="
                chartOfAccount.updater?.name ??
                '-'
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

    </Card>

</div>
<ActionBar bordered>

    <ButtonGroup>

        <BaseButton
            variant="secondary"
            @click="back"
        >

            <ArrowLeftIcon
                class="mr-2 h-5 w-5"
            />

            Back

        </BaseButton>

        <BaseButton
            variant="secondary"
            @click="print"
        >

            <PrinterIcon
                class="mr-2 h-5 w-5"
            />

            Print

        </BaseButton>

        <BaseButton
            @click="edit"
        >

            <PencilSquareIcon
                class="mr-2 h-5 w-5"
            />

            Edit

        </BaseButton>

    </ButtonGroup>

</ActionBar>
</div>

</AppLayout>

</template>