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

        REJECTED:
            'Rejected',

        RESUBMITTED:
            'Resubmitted',

        POSTED:
            'Posted',

        DELETED:
            'Deleted',

    }

    return labels[action]
        ?? action

}

function actionIcon(action)
{
    const icons = {

        CREATED:
            '＋',

        UPDATED:
            '✎',

        REJECTED:
            '×',

        RESUBMITTED:
            '↻',

        POSTED:
            '✓',

        DELETED:
            '🗑',

    }

    return icons[action]
        ?? '•'

}

function actionColor(action)
{
    const colors = {

        CREATED:
            'bg-sky-500',

        UPDATED:
            'bg-indigo-500',

        REJECTED:
            'bg-red-500',

        RESUBMITTED:
            'bg-amber-500',

        POSTED:
            'bg-emerald-500',

        DELETED:
            'bg-gray-500',

    }

    return colors[action]
        ?? 'bg-gray-400'

}

function actionBadge(action)
{
    const colors = {

        CREATED:
            'bg-sky-50 text-sky-700',

        UPDATED:
            'bg-indigo-50 text-indigo-700',

        REJECTED:
            'bg-red-50 text-red-700',

        RESUBMITTED:
            'bg-amber-50 text-amber-700',

        POSTED:
            'bg-emerald-50 text-emerald-700',

        DELETED:
            'bg-gray-100 text-gray-700',

    }

    return colors[action]
        ?? 'bg-gray-100 text-gray-700'

}

</script>

<template>

<div>

    <!-- Empty -->

    <div
        v-if="!activities.length"
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

        No workflow activity found.

    </div>


    <!-- Timeline -->

    <div
        v-else
        class="relative"
    >

        <div
            v-for="(
                activity,
                index
            ) in activities"

            :key="activity.id"

            class="
                relative
                flex
                gap-4
                pb-7
                last:pb-0
            "
        >

            <!-- Connector -->

            <div
                v-if="
                    index <
                    activities.length - 1
                "
                class="
                    absolute
                    left-4
                    top-9
                    h-full
                    w-px
                    bg-gray-200
                "
            ></div>


            <!-- Icon -->

            <div
                class="
                    relative
                    z-10
                    flex
                    h-8
                    w-8
                    shrink-0
                    items-center
                    justify-center
                    rounded-full
                    text-sm
                    font-bold
                    text-white
                "
                :class="
                    actionColor(
                        activity.action
                    )
                "
            >

                {{
                    actionIcon(
                        activity.action
                    )
                }}

            </div>


            <!-- Content -->

            <div
                class="
                    min-w-0
                    flex-1
                "
            >

                <div
                    class="
                        flex
                        flex-wrap
                        items-center
                        gap-2
                    "
                >

                    <h4
                        class="
                            text-sm
                            font-semibold
                            text-gray-900
                        "
                    >
                        {{
                            actionLabel(
                                activity.action
                            )
                        }}
                    </h4>


                    <span
                        class="
                            rounded-full
                            px-2.5
                            py-0.5
                            text-xs
                            font-semibold
                        "
                        :class="
                            actionBadge(
                                activity.action
                            )
                        "
                    >
                        {{
                            activity.action
                        }}
                    </span>

                </div>


                <!-- Status Change -->

                <div
                    v-if="
                        activity.old_status
                        ||
                        activity.new_status
                    "
                    class="
                        mt-1
                        text-xs
                        text-gray-500
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
                        class="
                            font-medium
                            text-gray-700
                        "
                    >
                        {{ activity.new_status }}
                    </span>

                </div>


                <!-- Description -->

                <p
                    v-if="
                        activity.description
                    "
                    class="
                        mt-2
                        text-sm
                        text-gray-600
                    "
                >

                    {{ activity.description }}

                </p>


                <!-- Rejection Reason -->

                <div
                    v-if="
                        activity.metadata?.reason
                    "
                    class="
                        mt-3
                        rounded-lg
                        border
                        border-red-100
                        bg-red-50
                        px-3
                        py-2
                    "
                >

                    <div
                        class="
                            text-xs
                            font-semibold
                            text-red-600
                        "
                    >
                        Reason
                    </div>

                    <div
                        class="
                            mt-1
                            text-sm
                            text-red-800
                        "
                    >
                        {{
                            activity.metadata.reason
                        }}
                    </div>

                </div>


                <!-- Footer -->

                <div
                    class="
                        mt-2
                        flex
                        flex-wrap
                        gap-x-3
                        gap-y-1
                        text-xs
                        text-gray-400
                    "
                >

                    <span>
                        {{
                            activity.performer?.name
                            ?? '-'
                        }}
                    </span>

                    <span>
                        •
                    </span>

                    <span>
                        {{
                            formatDateTime(
                                activity.performed_at
                            )
                        }}
                    </span>

                </div>

            </div>

        </div>

    </div>

</div>

</template>