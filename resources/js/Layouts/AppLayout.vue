<script setup>
import Sidebar from '@/Components/Layout/Sidebar.vue'
import Topbar from '@/Components/Layout/Topbar.vue'
import Footer from '@/Components/Layout/Footer.vue'
import { useLayout } from '@/Composables/useLayout'

const {
    sidebarCollapsed,
} = useLayout()
</script>

<template>

    <div class="min-h-screen bg-gray-100">

        <Sidebar />

        <div
            :class="[
                'transition-all duration-300',
                sidebarCollapsed ? 'lg:ml-20' : 'lg:ml-72'
            ]"
        >

            <Topbar />

            <main
             class="min-h-[calc(100vh-64px)] overflow-x-auto p-4 md:p-6"
            >

                <slot />

            </main>

            <Footer />

        </div>
    
    </div>

</template>