<script setup>
import { computed, ref } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { ChevronDownIcon } from '@heroicons/vue/24/outline'
import { useLayout } from '@/Composables/useLayout'

const props = defineProps({
    item: {
        type: Object,
        required: true,
    },
})

const page = usePage()

// const { sidebarCollapsed } = useLayout()
const {
    sidebarCollapsed,
    closeMobileSidebar,
} = useLayout()

const expanded = ref(
    props.item.children?.some(child => child && route().current(child.route)) ?? false
)

const hasChildren = computed(() => {
    return Array.isArray(props.item.children)
})

const isActive = computed(() => {
    if (!props.item.route) return false

    return route().current(props.item.route)
})

const toggle = () => {
    if (!sidebarCollapsed.value && hasChildren.value) {
        expanded.value = !expanded.value
    }
}
const navigate = () => {
    closeMobileSidebar()
}
</script>

<template>

    <div class="mb-1">

        <!-- Menu -->

        <button
            v-if="hasChildren"
            @click="toggle"
            class="flex items-center justify-between w-full px-4 py-3 rounded-lg hover:bg-gray-100 transition"
        >

            <div class="flex items-center">

                <component
                    :is="item.icon"
                    class="w-5 h-5 flex-shrink-0"
                />

                <span
                    v-if="!sidebarCollapsed"
                    class="ml-3 text-sm"
                >
                    {{ item.title }}
                </span>

            </div>

            <ChevronDownIcon
                v-if="!sidebarCollapsed"
                class="w-4 h-4 transition"
                :class="{ 'rotate-180': expanded }"
            />

        </button>

        <Link
            v-else-if="item.route"
            :href="route(item.route)"
             @click="navigate"
            class="flex items-center px-4 py-3 rounded-lg transition"
        >

            <component
                :is="item.icon"
                class="w-5 h-5 flex-shrink-0"
            />

            <span
                v-if="!sidebarCollapsed"
                class="ml-3 text-sm"
            >
                {{ item.title }}
            </span>

        </Link>
        <div
            v-else
            class="flex items-center px-4 py-3 rounded-lg text-gray-400 cursor-not-allowed"
        >
            <component
                v-if="item.icon"
                :is="item.icon"
                class="w-5 h-5 flex-shrink-0"
            />

            <span
                v-if="!sidebarCollapsed"
                class="ml-3 text-sm"
            >
                {{ item.title }}
            </span>
        </div>
        <!-- Submenu -->

        <div
            v-if="expanded && !sidebarCollapsed"
            class="mt-1 ml-6 border-l border-gray-200"
        >

           <Link
                v-for="child in item.children.filter(Boolean)"
                :key="child.title"
                :href="route(child.route)"
                 @click="navigate"
                class="block rounded-lg px-4 py-2 text-sm transition"
                :class="route().current(child.route)
                    ? 'bg-blue-50 text-blue-600 font-medium'
                    : 'hover:bg-gray-100'"
            >
                {{ child.title }}
            </Link>

        </div>

    </div>

</template>