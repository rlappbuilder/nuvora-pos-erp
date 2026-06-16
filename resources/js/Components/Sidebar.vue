<script setup>
import {

    LayoutDashboard,

    Database,

    Package,

    Warehouse,

    ShoppingCart,

    Users,

    Calculator,

    FileText,

    Settings,

    ChevronLeft,

    ChevronRight,

    ChevronDown,

    FolderOpen,

    Ruler,

    Palette,

    Maximize2



} from 'lucide-vue-next'

import { ref } from 'vue'

import { Link } from '@inertiajs/vue3'



const collapsed = ref(false)

const masterDataOpen = ref(true)

const toggleSidebar = () => {

    collapsed.value = !collapsed.value

}

</script>

<template>

    <aside

    :class="collapsed ? 'w-20' : 'w-64'"

    class="h-screen shrink-0 bg-white border-r border-gray-200 transition-all duration-300 flex flex-col"

>

        <!-- Logo -->

        <div

            class="h-16 flex items-center justify-between px-4 border-b"

        >

            <div

                v-if="!collapsed"

                class="font-bold text-xl text-blue-600"

            >

                NUVORA

            </div>

            <button

                @click="toggleSidebar"

                class="p-2 rounded-lg hover:bg-gray-100"

            >

                <ChevronLeft

                    v-if="!collapsed"

                    :size="18"

                />

                <ChevronRight

                    v-else

                    :size="18"

                />

            </button>

        </div>

        <!-- Menu -->

        <nav

            class="flex-1 overflow-y-auto p-3"

        >

            <!-- Dashboard -->

            <Link

                :href="route('dashboard')"

                class="mb-2 flex items-center gap-3 rounded-xl p-3 hover:bg-blue-50"

            >

                <LayoutDashboard :size="20" />

                <span v-if="!collapsed">

                    Dashboard

                </span>

            </Link>

            <!-- Master Data -->

            <button

                @click="masterDataOpen = !masterDataOpen"

                class="mb-2 flex w-full items-center justify-between rounded-xl p-3 hover:bg-blue-50"

            >

                <div

                    class="flex items-center gap-3"

                >

                    <Database :size="20" />

                    <span v-if="!collapsed">

                        Master Data

                    </span>

                </div>

                <ChevronDown

                    v-if="!collapsed"

                    :size="18"

                />

            </button>

            <div
    v-if="masterDataOpen && !collapsed"
    class="ml-6 mb-3 space-y-1"
>

    <Link
        :href="route('categories.index')"
        class="flex items-center gap-3 rounded-lg p-2 hover:bg-blue-50"
    >
        <FolderOpen :size="18" />
        <span>Categories</span>
    </Link>

    <Link
        :href="route('brands.index')"
        class="flex items-center gap-3 rounded-lg p-2 hover:bg-blue-50"
    >
        <Package :size="18" />
        <span>Brands</span>
    </Link>

    <Link
        :href="route('units.index')"
        class="flex items-center gap-3 rounded-lg p-2 hover:bg-blue-50"
    >
        <Ruler :size="18" />
        <span>Units</span>
    </Link>

    <Link
        :href="route('colors.index')"
        class="flex items-center gap-3 rounded-lg p-2 hover:bg-blue-50"
    >
        <FolderOpen :size="18" />
        <span>Colors</span>
    </Link>

    <Link
        :href="route('sizes.index')"
        class="flex items-center gap-3 rounded-lg p-2 hover:bg-blue-50"
    >
        <FolderOpen :size="18" />
        <span>Sizes</span>
    </Link>

    <Link
        href="#"
        class="flex items-center gap-3 rounded-lg p-2 hover:bg-blue-50"
    >
        <FolderOpen :size="18" />
        <span>Branches</span>
    </Link>

    <Link
        href="#"
        class="flex items-center gap-3 rounded-lg p-2 hover:bg-blue-50"
    >
        <Warehouse :size="18" />
        <span>Warehouses</span>
    </Link>

    <Link
        href="#"
        class="flex items-center gap-3 rounded-lg p-2 hover:bg-blue-50"
    >
        <FolderOpen :size="18" />
        <span>Companies</span>
    </Link>

    <Link
        href="#"
        class="flex items-center gap-3 rounded-lg p-2 hover:bg-blue-50"
    >
        <Users :size="18" />
        <span>Employees</span>
    </Link>

</div>

            <!-- Product -->

            <Link

                href="#"

                class="mb-2 flex items-center gap-3 rounded-xl p-3 hover:bg-blue-50"

            >

                <Package :size="20" />

                <span v-if="!collapsed">

                    Products

                </span>

            </Link>

            <!-- Inventory -->

            <Link

                href="#"

                class="mb-2 flex items-center gap-3 rounded-xl p-3 hover:bg-blue-50"

            >

                <Warehouse :size="20" />

                <span v-if="!collapsed">

                    Inventory

                </span>

            </Link>

            <!-- Sales -->

            <Link

                href="#"

                class="mb-2 flex items-center gap-3 rounded-xl p-3 hover:bg-blue-50"

            >

                <ShoppingCart :size="20" />

                <span v-if="!collapsed">

                    Sales

                </span>

            </Link>

            <!-- Reseller -->

            <Link

                href="#"

                class="mb-2 flex items-center gap-3 rounded-xl p-3 hover:bg-blue-50"

            >

                <Users :size="20" />

                <span v-if="!collapsed">

                    Reseller

                </span>

            </Link>

            <!-- Accounting -->

            <Link

                href="#"

                class="mb-2 flex items-center gap-3 rounded-xl p-3 hover:bg-blue-50"

            >

                <Calculator :size="20" />

                <span v-if="!collapsed">

                    Accounting

                </span>

            </Link>

            <!-- Reports -->

            <Link

                href="#"

                class="mb-2 flex items-center gap-3 rounded-xl p-3 hover:bg-blue-50"

            >

                <FileText :size="20" />

                <span v-if="!collapsed">

                    Reports

                </span>

            </Link>

            <!-- Settings -->

            <Link

                href="#"

                class="mb-2 flex items-center gap-3 rounded-xl p-3 hover:bg-blue-50"

            >

                <Settings :size="20" />

                <span v-if="!collapsed">

                    Settings

                </span>

            </Link>

        </nav>

    </aside>

</template>