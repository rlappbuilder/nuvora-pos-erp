<script setup>

import Sidebar from '@/Components/Sidebar.vue'
import Topbar from '@/Components/Topbar.vue'

</script>

<template>

    <div
        class="flex h-screen overflow-hidden bg-slate-50"
    >

        <!-- Sidebar -->

        <Sidebar />

        <!-- Main -->

        <div
            class="flex flex-1 flex-col overflow-hidden"
        >

            <!-- Topbar -->

            <Topbar />

            <!-- Header -->

            <div

                v-if="$slots.header"

                class="border-b border-gray-200 bg-white px-6 py-4"

            >

                <slot name="header" />

            </div>

            <!-- Content -->

            <main

                class="flex-1 overflow-y-auto p-6"

            >

                <slot />

            </main>

        </div>

    </div>

</template>