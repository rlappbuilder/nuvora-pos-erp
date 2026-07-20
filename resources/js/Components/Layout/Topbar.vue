<script setup>
import {
    Bars3Icon,
    BellIcon,
    MagnifyingGlassIcon,
} from '@heroicons/vue/24/outline'

import { useLayout } from '@/Composables/useLayout'
import UserMenu from './UserMenu.vue'
const {
    sidebarCollapsed,
    toggleSidebar,
    toggleMobileSidebar,
} = useLayout()

const handleSidebarToggle = () => {
    if (window.innerWidth >= 1024) {
        toggleSidebar()
    } else {
        toggleMobileSidebar()
    }
}
</script>

<template>
    <header
        class="sticky top-0 z-30 flex items-center justify-between h-16 px-6 bg-white border-b border-gray-200"
    >
        <!-- Left -->
        <div class="flex items-center gap-4">

            <button
                class="p-2 rounded-lg hover:bg-gray-100 transition"
                @click="handleSidebarToggle"
            >
                <Bars3Icon class="w-6 h-6" />
            </button>

            <div class="hidden md:flex items-center gap-2 text-sm text-gray-500">
                <span>Dashboard</span>
            </div>

        </div>

        <!-- Right -->
        <div class="flex items-center gap-3">

            <button
                class="p-2 rounded-lg hover:bg-gray-100 transition"
            >
                <MagnifyingGlassIcon class="w-5 h-5" />
            </button>

            <button
                class="relative p-2 rounded-lg hover:bg-gray-100 transition"
            >
                <BellIcon class="w-5 h-5" />

                <span
                    class="absolute top-1 right-1 w-2 h-2 rounded-full bg-red-500"
                />
            </button>

            <UserMenu />

        </div>
    </header>
</template>