<script setup>

import {
    Head,
    router,
} from '@inertiajs/vue3'

import PageHeader
    from '@/Components/Layout/PageHeader.vue'

import Card
    from '@/Components/Layout/Card.vue'

import ActionBar
    from '@/Components/Layout/ActionBar.vue'

import ButtonGroup
    from '@/Components/Button/ButtonGroup.vue'

import BaseButton
    from '@/Components/Button/BaseButton.vue'

import DetailRow
    from '@/Components/Display/DetailRow.vue'

import StatusBadge
    from '@/Components/Display/StatusBadge.vue'

import AppLayout
    from '@/Layouts/AppLayout.vue'

import {
    formatDate,
} from '@/Utils'


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    journal: {

        type: Object,

        required: true,

    },

})


/*
|--------------------------------------------------------------------------
| Navigation
|--------------------------------------------------------------------------
*/

function back()
{
    router.visit(

        route(
            'accounting-journals.index'
        )

    )
}


function edit()
{
    router.visit(

        route(
            'accounting-journals.edit',
            props.journal.id
        )

    )
}


/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function statusValue()
{
    return Boolean(
        props.journal.is_active
    )
        ? 'Active'
        : 'Inactive'
}


function accountRole(account)
{
    if (!account?.role) {

        return '-'

    }


    return account.role
        .replace(
            /_/g,
            ' '
        )
        .replace(
            /\b\w/g,
            letter =>
                letter.toUpperCase()
        )
}


function accountCode(account)
{
    return (
        account?.account?.code
        ?? '-'
    )
}


function accountName(account)
{
    return (
        account?.account?.name
        ?? '-'
    )
}


function isDefaultAccount(account)
{
    return Boolean(
        account?.is_default
    )
}
const statusOptions = [

    {
        value: '',
        label: 'All Status',
    },

    {
        value: 1,
        label: 'Active',
    },

    {
        value: 0,
        label: 'Inactive',
    },

]
</script>


<template>

    <Head
        title="Accounting Journal Detail"
    />


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

            <!-- ===================================================== -->
            <!-- Page Header -->
            <!-- ===================================================== -->

            <PageHeader

                icon="📒"

                title="Accounting Journal Detail"

                subtitle="View accounting journal information and account configuration."

            />


            <!-- ===================================================== -->
            <!-- Details -->
            <!-- ===================================================== -->

            <div
                class="
                    grid
                    grid-cols-1
                    gap-6
                    xl:grid-cols-2
                "
            >

                <!-- ================================================= -->
                <!-- Journal Information -->
                <!-- ================================================= -->

                <Card

                    icon="📒"

                    title="Journal Information"

                >

                    <DetailRow

                        label="Code"

                        :value="
                            journal.code
                            ?? '-'
                        "

                    />


                    <DetailRow

                        label="Journal Name"

                        :value="
                            journal.name
                            ?? '-'
                        "

                    />


                    <DetailRow

                        label="Type"

                        :value="
                            journal.type
                            ?? '-'
                        "

                    />


                                <DetailRow
                                    
                                    label="Status"
                                >

                                    <StatusBadge
                                        :status="journal.is_active"
                                    />

                                </DetailRow>

                </Card>


                <!-- ================================================= -->
                <!-- Organization -->
                <!-- ================================================= -->

                <Card

                    icon="🏢"

                    title="Organization"

                >

                    <DetailRow

                        label="Company"

                        :value="
                            journal.company
                                ?.company_name
                            ?? '-'
                        "

                    />

                </Card>

<!-- ================================================= -->
<!-- Account Configuration -->
<!-- ================================================= -->

<Card

    icon="📚"

    title="Account Configuration"

>

    <div
        v-if="journal.accounts?.length"
        class="space-y-3"
    >

        <div

            v-for="
                account
                in journal.accounts
            "

            :key="
                account.id
            "

            class="
                flex
                flex-col
                gap-2
                rounded-xl
                border
                border-gray-200
                bg-gray-50
                px-4
                py-3
                sm:flex-row
                sm:items-center
            "

        >

            <!-- Role -->

            <div
                class="
                    w-full
                    shrink-0
                    text-sm
                    font-medium
                    text-gray-500
                    sm:w-32
                "
            >

                Role

            </div>


            <!-- Role Value -->

            <div
                class="
                    min-w-0
                    flex-1
                    text-sm
                    font-semibold
                    capitalize
                    text-gray-900
                "
            >

                {{
                    accountRole(account)
                }}

            </div>


            <!-- Account -->

            <div
                class="
                    min-w-0
                    flex-1
                    text-sm
                    text-gray-700
                "
            >

                <span
                    class="font-semibold text-gray-900"
                >

                    {{
                        accountCode(account)
                    }}

                </span>

                <span
                    class="mx-1 text-gray-400"
                >
                    -
                </span>

                <span>

                    {{
                        accountName(account)
                    }}

                </span>

            </div>


            <!-- Default -->

            <div
                class="
                    shrink-0
                    text-sm
                "
            >

                <span
                    v-if="
                        isDefaultAccount(
                            account
                        )
                    "
                    class="
                        inline-flex
                        items-center
                        rounded-full
                        bg-blue-100
                        px-2.5
                        py-1
                        text-xs
                        font-medium
                        text-blue-700
                    "
                >

                    ⭐ Default

                </span>

            </div>

        </div>

    </div>


    <!-- Empty -->

    <div
        v-else
        class="
            rounded-xl
            border
            border-dashed
            border-gray-300
            bg-gray-50
            px-6
            py-8
            text-center
        "
    >

        <div
            class="
                text-sm
                font-medium
                text-gray-700
            "
        >

            No accounts configured

        </div>


        <div
            class="
                mt-1
                text-xs
                text-gray-500
            "
        >

            No chart of accounts have been assigned to this journal.

        </div>

    </div>

</Card>

                <!-- ================================================= -->
                <!-- Description -->
                <!-- ================================================= -->

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
                            journal.description
                            || '-'
                        }}

                    </div>

                </Card>


                <!-- ================================================= -->
                <!-- Audit Information -->
                <!-- ================================================= -->

                <Card

                    icon="👤"

                    title="Audit Information"

                >

                    <DetailRow

                        label="Created By"

                        :value="
                            journal.creator?.name
                            ?? '-'
                        "

                    />


                    <DetailRow

                        label="Updated By"

                        :value="
                            journal.updater?.name
                            ?? '-'
                        "

                    />


                    <DetailRow

                        label="Created At"

                        :value="
                            formatDate(
                                journal.created_at
                            )
                        "

                    />


                    <DetailRow

                        label="Updated At"

                        :value="
                            formatDate(
                                journal.updated_at
                            )
                        "

                    />

                </Card>


                <!-- ================================================= -->
                <!-- Actions -->
                <!-- ================================================= -->

                <ActionBar
                    bordered
                >

                    <ButtonGroup>

                        <BaseButton

                            variant="secondary"

                            @click="back"

                        >

                            Back

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