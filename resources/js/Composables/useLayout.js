import { ref, computed } from 'vue'

const sidebarCollapsed = ref(
    JSON.parse(localStorage.getItem('sidebarCollapsed') ?? 'false')
)
const mobileSidebarOpen = ref(false)

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

}

    const openMobileSidebar = () => {
        mobileSidebarOpen.value = true
    }

    const closeMobileSidebar = () => {
        mobileSidebarOpen.value = false
    }

    const toggleMobileSidebar = () => {
        mobileSidebarOpen.value = !mobileSidebarOpen.value
    }

    return {
        sidebarCollapsed,
        mobileSidebarOpen,

        sidebarWidth,

        toggleSidebar,

        openMobileSidebar,
        closeMobileSidebar,
        toggleMobileSidebar,
    }
}