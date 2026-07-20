<script setup>
import { computed } from 'vue'
import Logo from './Logo.vue'
import { useLayout } from '@/Composables/useLayout'
import {
    HomeIcon,
} from '@heroicons/vue/24/outline'
import navigation from '@/Config/navigation'
import SidebarItem from './SidebarItem.vue'
import { usePage } from '@inertiajs/vue3'
import { hasPermission } from '@/Utils'
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

    <!-- Overlay Mobile -->
    <div
        v-if="mobileSidebarOpen"
        class="fixed inset-0 z-40 bg-black/40 lg:hidden"
        @click="closeMobileSidebar"
    />

    <!-- Sidebar -->
    <aside :class="sidebarClasses">

        <Logo :collapsed="sidebarCollapsed" />

        <div class="flex-1 overflow-y-auto py-4">

            <div class="px-4 mb-2 text-xs font-semibold uppercase text-gray-400">
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