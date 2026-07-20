<script setup>
import StatsCard from '@/Components/Card/StatsCard.vue'

defineProps({

    summary: {

        type: Object,

        required: true,

    },

})

</script>

<template>

    <div
        class="
            grid
            grid-cols-1
            gap-4

            sm:grid-cols-2

            xl:grid-cols-5
        "
    >

        <StatsCard

            title="Total Accounts"

            :value="summary.total_accounts"

            icon="📚"

        />

        <StatsCard

            title="Header Accounts"

            :value="summary.header_accounts"

            icon="📂"

        />

        <StatsCard

            title="Posting Accounts"

            :value="summary.posting_accounts"

            icon="📝"

        />

        <StatsCard

            title="Active"

            :value="summary.active_accounts"

            icon="✅"

        />

        <StatsCard

            title="Inactive"

            :value="summary.inactive_accounts"

            icon="⛔"

        />

    </div>

</template>