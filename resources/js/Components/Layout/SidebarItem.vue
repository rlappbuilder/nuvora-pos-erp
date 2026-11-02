<script setup>
import {
    computed,
    ref,
    onMounted,
    onBeforeUnmount,
    nextTick,
} from 'vue'

import { Link } from '@inertiajs/vue3'

import {
    ChevronRightIcon,
} from '@heroicons/vue/24/outline'

import { useLayout } from '@/Composables/useLayout'

const props = defineProps({
    item: {
        type: Object,
        required: true,
    },
})

const {
    sidebarCollapsed,
    activeFlyout,
    toggleFlyout,
    closeFlyout,
    closeMobileSidebar,
} = useLayout()

const buttonRef = ref(null)
const panelRef = ref(null)

const flyoutStyle = ref({
    top: '0px',
    left: '0px',
})

const hasChildren = computed(() => {
    return Array.isArray(props.item.children) &&
        props.item.children.length > 0
})

const isFlyoutOpen = computed(() => {
    return activeFlyout.value === props.item.title
})

const isActive = computed(() => {
    if (!hasChildren.value && props.item.route) {
        return route().current(props.item.route)
    }

    return props.item.children?.some(child =>
        child?.route && route().current(child.route)
    ) ?? false
})

const updateFlyoutPosition = () => {
    if (!buttonRef.value || !panelRef.value) return

    const rect = buttonRef.value.getBoundingClientRect()
    const panelRect = panelRef.value.getBoundingClientRect()

    const panelWidth = panelRect.width
    const panelHeight = panelRect.height

    const viewportHeight = window.innerHeight
    const viewportWidth = window.innerWidth

    // Horizontal position
    const left = Math.min(
        rect.right,
        viewportWidth - panelWidth - 12
    )

    // Vertical position: align with parent menu
    let top = rect.top

    // Move upward only when panel exceeds viewport
    if (top + panelHeight > viewportHeight - 12) {
        top = viewportHeight - panelHeight - 12
    }

    // Prevent panel from going above viewport
    top = Math.max(12, top)

    flyoutStyle.value = {
        top: `${top}px`,
        left: `${left}px`,
    }
}

const handleToggle = async () => {
    if (!hasChildren.value) return

    toggleFlyout(props.item.title)

    await nextTick()

    if (isFlyoutOpen.value) {
        updateFlyoutPosition()
    }
}

const navigate = () => {
    closeFlyout()
    closeMobileSidebar()
}

const handleClickOutside = (event) => {
    if (!isFlyoutOpen.value) return

    const clickedButton = buttonRef.value?.contains(event.target)
    const clickedPanel = panelRef.value?.contains(event.target)

    if (!clickedButton && !clickedPanel) {
        closeFlyout()
    }
}

const handleEscape = (event) => {
    if (event.key === 'Escape') {
        closeFlyout()
    }
}

const handleResize = () => {
    if (isFlyoutOpen.value) {
        updateFlyoutPosition()
    }
}

onMounted(() => {
    document.addEventListener('pointerdown', handleClickOutside)
    document.addEventListener('keydown', handleEscape)
    window.addEventListener('resize', handleResize)
})

onBeforeUnmount(() => {
    document.removeEventListener('pointerdown', handleClickOutside)
    document.removeEventListener('keydown', handleEscape)
    window.removeEventListener('resize', handleResize)
})
</script>

<template>
    <div class="relative mb-1">

        <!-- Parent Menu -->
        <button
            v-if="hasChildren"
            ref="buttonRef"
            type="button"
            @click="handleToggle"
            :title="sidebarCollapsed ? item.title : undefined"
            class="
                flex w-full items-center justify-between
                rounded-lg px-4 py-3
                transition duration-200
            "
            :class="[
                isFlyoutOpen || isActive
                    ? 'bg-blue-50 text-blue-600'
                    : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'
            ]"
        >
            <div class="flex items-center min-w-0">

                <component
                    v-if="item.icon"
                    :is="item.icon"
                    class="h-5 w-5 flex-shrink-0"
                />

                <span
                    v-if="!sidebarCollapsed"
                    class="ml-3 truncate text-sm font-medium"
                >
                    {{ item.title }}
                </span>

            </div>

            <ChevronRightIcon
                v-if="!sidebarCollapsed"
                class="h-4 w-4 flex-shrink-0 transition-transform"
                :class="{
                    'rotate-90': isFlyoutOpen
                }"
            />

        </button>

        <!-- Single Menu -->
        <Link
            v-else-if="item.route"
            :href="route(item.route)"
            @click="navigate"
            :title="sidebarCollapsed ? item.title : undefined"
            class="
                flex items-center rounded-lg px-4 py-3
                transition duration-200
            "
            :class="[
                isActive
                    ? 'bg-blue-50 text-blue-600 font-medium'
                    : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'
            ]"
        >

            <component
                v-if="item.icon"
                :is="item.icon"
                class="h-5 w-5 flex-shrink-0"
            />

            <span
                v-if="!sidebarCollapsed"
                class="ml-3 truncate text-sm"
            >
                {{ item.title }}
            </span>

        </Link>

        <!-- Disabled Menu -->
        <div
            v-else
            :title="sidebarCollapsed ? item.title : undefined"
            class="
                flex items-center rounded-lg px-4 py-3
                text-gray-400 cursor-not-allowed
            "
        >

            <component
                v-if="item.icon"
                :is="item.icon"
                class="h-5 w-5 flex-shrink-0"
            />

            <span
                v-if="!sidebarCollapsed"
                class="ml-3 text-sm"
            >
                {{ item.title }}
            </span>

        </div>

        <!-- Flyout Submenu -->
        <Teleport to="body">

            <Transition
                enter-active-class="transition duration-150 ease-out"
                enter-from-class="opacity-0 translate-x-2"
                enter-to-class="opacity-100 translate-x-0"
                leave-active-class="transition duration-100 ease-in"
                leave-from-class="opacity-100 translate-x-0"
                leave-to-class="opacity-0 translate-x-2"
            >

                <div
                    v-if="isFlyoutOpen"
                    ref="panelRef"
                    :style="flyoutStyle"
                    class="
                        fixed z-[100]
                        w-64 max-h-[calc(100vh-24px)]
                        overflow-y-auto
                        rounded-xl
                        border border-gray-200
                        bg-white
                        shadow-xl
                    "
                >

                    <!-- Flyout Header -->
                    <div
                        class="
                            sticky top-0 z-10
                            border-b border-gray-100
                            bg-white px-4 py-3
                        "
                    >
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">
                            {{ item.title }}
                        </p>
                    </div>

                    <!-- Flyout Items -->
                    <div class="space-y-1 p-2">

                        <Link
                            v-for="child in item.children.filter(child => child && child.route)"
                            :key="child.title"
                            :href="route(child.route)"
                            @click="navigate"
                            class="
                                flex items-center gap-3
                                rounded-lg px-3 py-2.5
                                text-sm transition
                            "
                            :class="
                                route().current(child.route)
                                    ? 'bg-blue-50 text-blue-600 font-medium'
                                    : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'
                            "
                        >

                            <component
                                v-if="child.icon"
                                :is="child.icon"
                                class="h-4 w-4 flex-shrink-0"
                            />

                            <span class="truncate">
                                {{ child.title }}
                            </span>

                        </Link>

                    </div>

                </div>

            </Transition>

        </Teleport>

    </div>
</template>