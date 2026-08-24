<script setup>

import {
    computed,
} from 'vue'

const props = defineProps({

    activities: {

        type: Array,

        default: () => [],

    },

})

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function formatDateTime(value)
{
    if (!value) {

        return '-'

    }

    return new Intl.DateTimeFormat(
        'id-ID',
        {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        }
    ).format(
        new Date(value)
    )
}


function actionLabel(action)
{
    const labels = {

        CREATED:
            'Created',

        UPDATED:
            'Updated',

        SUBMITTED:
            'Submitted',

        APPROVED:
            'Approved',

        REJECTED:
            'Rejected',

        RESUBMITTED:
            'Resubmitted',

        PARTIAL_RECEIVED:
            'Partial Received',

        FULLY_RECEIVED:
            'Fully Received',

        POSTED:
            'Posted',

        CANCELLED:
            'Cancelled',

        DELETED:
            'Deleted',

        SENT:
            'Sent',

        CONFIRMED:
            'Confirmed',

    }

    return labels[action]
        ?? action
}


function actionClass(action)
{
    const classes = {

        CREATED:
            'bg-sky-50 text-sky-700',

        UPDATED:
            'bg-indigo-50 text-indigo-700',

        SUBMITTED:
            'bg-blue-50 text-blue-700',

        APPROVED:
            'bg-emerald-50 text-emerald-700',

        REJECTED:
            'bg-red-50 text-red-700',

        RESUBMITTED:
            'bg-amber-50 text-amber-700',

        PARTIAL_RECEIVED:
            'bg-amber-50 text-amber-700',

        FULLY_RECEIVED:
            'bg-emerald-50 text-emerald-700',

        POSTED:
            'bg-emerald-50 text-emerald-700',

        CANCELLED:
            'bg-orange-50 text-orange-700',

        DELETED:
            'bg-gray-100 text-gray-700',

        SENT:
            'bg-blue-50 text-blue-700',

        CONFIRMED:
            'bg-emerald-50 text-emerald-700',

    }

    return classes[action]
        ?? 'bg-gray-100 text-gray-700'
}


/*
|--------------------------------------------------------------------------
| Latest First
|--------------------------------------------------------------------------
*/

const auditItems = computed(() => {

    return [
        ...props.activities,
    ].reverse()

})

</script>

<template>

<div>

    <!-- Empty -->

    <div
        v-if="!auditItems.length"
        class="
            rounded-xl
            border
            border-gray-200
            bg-gray-50
            px-5
            py-8
            text-center
            text-sm
            text-gray-500
        "
    >

        No audit trail found.

    </div>


    <!-- Audit -->

    <div
        v-else
        class="
            overflow-hidden
            rounded-xl
            border
            border-gray-200
        "
    >

        <!-- Header -->

        <div
            class="
                grid
                grid-cols-[120px_1fr_180px_160px]
                gap-4
                border-b
                border-gray-200
                bg-gray-50
                px-4
                py-3
                text-xs
                font-semibold
                uppercase
                tracking-wide
                text-gray-500
            "
        >

            <div>
                Action
            </div>

            <div>
                Description
            </div>

            <div>
                Performed By
            </div>

            <div>
                Date &amp; Time
            </div>

        </div>


        <!-- Rows -->

        <div
            v-for="activity in auditItems"
            :key="activity.id"
            class="
                grid
                grid-cols-[120px_1fr_180px_160px]
                gap-4
                border-b
                border-gray-100
                px-4
                py-4
                last:border-b-0
            "
        >

            <!-- Action -->

            <div>

                <span
                    class="
                        inline-flex
                        rounded-full
                        px-2.5
                        py-1
                        text-xs
                        font-semibold
                    "
                    :class="
                        actionClass(
                            activity.action
                        )
                    "
                >

                    {{
                        actionLabel(
                            activity.action
                        )
                    }}

                </span>

            </div>


            <!-- Description -->

            <div
                class="
                    min-w-0
                "
            >

                <div
                    class="
                        text-sm
                        text-gray-700
                    "
                >

                    {{
                        activity.description
                        ?? '-'
                    }}

                </div>


                <!-- Status -->

                <div
                    v-if="
                        activity.old_status
                        ||
                        activity.new_status
                    "
                    class="
                        mt-1
                        text-xs
                        text-gray-400
                    "
                >

                    <span
                        v-if="
                            activity.old_status
                        "
                    >
                        {{ activity.old_status }}
                    </span>

                    <span
                        v-if="
                            activity.old_status
                            &&
                            activity.new_status
                        "
                        class="mx-1"
                    >
                        →
                    </span>

                    <span
                        v-if="
                            activity.new_status
                        "
                    >
                        {{ activity.new_status }}
                    </span>

                </div>


                <!-- Reason -->

                <div
                    v-if="
                        activity.metadata?.reason
                    "
                    class="
                        mt-2
                        rounded-lg
                        bg-red-50
                        px-3
                        py-2
                        text-xs
                        text-red-700
                    "
                >

                    <span
                        class="font-semibold"
                    >
                        Reason:
                    </span>

                    {{
                        activity.metadata.reason
                    }}

                </div>

            </div>


            <!-- User -->

            <div
                class="
                    text-sm
                    text-gray-700
                "
            >

                {{
                    activity.performer?.name
                    ?? '-'
                }}

            </div>


            <!-- Date -->

            <div
                class="
                    text-sm
                    text-gray-500
                "
            >

                {{
                    formatDateTime(
                        activity.performed_at
                    )
                }}

            </div>

        </div>

    </div>

</div>

</template>