<script setup>
import { computed } from 'vue'

import Logo from './Logo.vue'
import { useLayout } from '@/Composables/useLayout'

import navigation from '@/Config/navigation'
import SidebarItem from './SidebarItem.vue'

const {
    sidebarCollapsed,
    mobileSidebarOpen,
    closeMobileSidebar,
    sidebarWidth,
} = useLayout()

const sidebarClasses = computed(() => [
    'fixed inset-y-0 left-0 z-50 flex flex-col bg-white border-r border-gray-200 transition-all duration-300 ease-in-out',
    sidebarWidth.value,
    mobileSidebarOpen.value
        ? 'translate-x-0'
        : '-translate-x-full lg:translate-x-0',
])
</script>

<template>

    <!-- Mobile Overlay -->
    <div
        v-if="mobileSidebarOpen"
        class="fixed inset-0 z-40 bg-black/40 lg:hidden"
        @click="closeMobileSidebar"
    />

    <!-- Sidebar -->
    <aside :class="sidebarClasses">

        <Logo :collapsed="sidebarCollapsed" />

        <div class="flex-1 overflow-y-auto overflow-x-hidden py-4">

            <div
                v-if="!sidebarCollapsed"
                class="mb-2 px-4 text-xs font-semibold uppercase tracking-wider text-gray-400"
            >
                Menu
            </div>

            <nav class="space-y-1 px-3">

                <SidebarItem
                    v-for="item in navigation"
                    :key="item.title"
                    :item="item"
                />

            </nav>

        </div>

    </aside>

</template>