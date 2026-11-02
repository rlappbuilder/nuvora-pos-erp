import { ref, computed } from 'vue'

const sidebarCollapsed = ref(
    JSON.parse(localStorage.getItem('sidebarCollapsed') ?? 'false')
)

const mobileSidebarOpen = ref(false)
const activeFlyout = ref(null)

export function useLayout() {

    const sidebarWidth = computed(() =>
        sidebarCollapsed.value ? 'w-20' : 'w-72'
    )

    const toggleSidebar = () => {
        sidebarCollapsed.value = !sidebarCollapsed.value

        localStorage.setItem(
            'sidebarCollapsed',
            JSON.stringify(sidebarCollapsed.value)
        )

        activeFlyout.value = null
    }

    const openMobileSidebar = () => {
        mobileSidebarOpen.value = true
    }

    const closeMobileSidebar = () => {
        mobileSidebarOpen.value = false
        activeFlyout.value = null
    }

    const toggleMobileSidebar = () => {
        mobileSidebarOpen.value = !mobileSidebarOpen.value

        if (!mobileSidebarOpen.value) {
            activeFlyout.value = null
        }
    }

    const toggleFlyout = (title) => {
        activeFlyout.value =
            activeFlyout.value === title ? null : title
    }

    const closeFlyout = () => {
        activeFlyout.value = null
    }

    return {
        sidebarCollapsed,
        mobileSidebarOpen,
        activeFlyout,
        sidebarWidth,

        toggleSidebar,

        openMobileSidebar,
        closeMobileSidebar,
        toggleMobileSidebar,

        toggleFlyout,
        closeFlyout,
    }
}