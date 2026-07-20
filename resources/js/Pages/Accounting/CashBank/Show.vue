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

    cashBank: {

        type: Object,

        required: true,

    },

})

function back()
{
    router.visit(
        route('cash-banks.index')
    )
}

function edit()
{
    router.visit(
        route(
            'cash-banks.edit',
            props.cashBank.id
        )
    )
}
function print()
{
    router.get(

        route(

            'cash-banks.print',

            props.cashBank.id

        )

    )
}
</script>
<template>

<Head title="Cash & Bank Detail" />

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

    icon="🏦"

    title="Cash & Bank Detail"

    subtitle="View cash & bank information."

/>

<div
    class="
        grid
        grid-cols-1
        gap-6
        xl:grid-cols-2
    "
>
        <Card

            icon="🏦"

            title="General Information"

        >

            <DetailRow
                label="Code"
                :value="cashBank.code"
            />

            <DetailRow
                label="Status"
                :value="
                    cashBank.status
                        ? 'Active'
                        : 'Inactive'
                "
                :icon="
                    cashBank.status
                        ? '🟢'
                        : '🔴'
                "
            />

            <DetailRow
                label="Type"
                :value="cashBank.type"
                :icon="
                    cashBank.type === 'Bank'
                        ? '🏦'
                        : '💵'
                "
            />

        </Card>
        <Card

            icon="🏢"

            title="Organization"

        >

            <DetailRow

                label="Company"

                :value="cashBank.company?.company_name"

            />

            <DetailRow

                label="Branch"

                :value="cashBank.branch?.name"

            />

        </Card>
        <Card

            v-if="cashBank.type === 'Bank'"

            icon="🏦"

            title="Bank Information"

        >

            <DetailRow
                label="Bank Name"
                :value="cashBank.bank_name"
            />

            <DetailRow
                label="Bank Branch"
                :value="cashBank.bank_branch"
            />

            <DetailRow
                label="Account Number"
                :value="cashBank.account_number"
            />

            <DetailRow
                label="Account Holder"
                :value="cashBank.account_holder"
            />

        </Card>

        <Card

            icon="📒"

            title="Accounting"

        >

        <DetailRow

            label="Opening Balance"

            :value="formatCurrency(
                cashBank.opening_balance
            )"

        />

        <DetailRow

            label="Current Balance"

            :value="formatCurrency(
                cashBank.current_balance
            )"

        />

        </Card>
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

                {{

                    cashBank.description ||

                    '-'

                }}

            </div>

        </Card>
        <Card

            icon="👤"

            title="Audit Information"

        >

            <DetailRow

                label="Created By"

                :value="cashBank.creator?.name"

            />

            <DetailRow

                label="Updated By"

                :value="cashBank.updater?.name"

            />

                    <DetailRow
                    label="Created At"
                    :value="formatDate(cashBank.created_at)"
                />

                <DetailRow
                    label="Updated At"
                    :value="formatDate(cashBank.updated_at)"
                />

        </Card>
        <ActionBar bordered>

            

            <ButtonGroup>

                <BaseButton
                    variant="secondary"
                    @click="back"
                >
                    Back
                </BaseButton>

                <BaseButton
                    variant="secondary"
                    @click="print"
                >
                    Print
                </BaseButton>

                <BaseButton
                    @click="edit"
                >
                    Edit
                </BaseButton>

            </ButtonGroup>

        </ActionBar>

</div>
</div>

</AppLayout>

</template>