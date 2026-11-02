<script setup>
import { computed, ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

import {
    MagnifyingGlassIcon,
    Squares2X2Icon,
    ListBulletIcon,
    FunnelIcon,
    ShoppingCartIcon,
    CurrencyDollarIcon,
    ShoppingBagIcon,
    UsersIcon,
    ClipboardDocumentListIcon,
    MegaphoneIcon,
    CubeIcon,
    Cog6ToothIcon,
    ClockIcon,
    ChevronRightIcon,
    ChevronLeftIcon,
    ArrowDownTrayIcon,
    EllipsisHorizontalIcon,
    DocumentPlusIcon,
    XMarkIcon,
    DocumentChartBarIcon,
} from '@heroicons/vue/24/outline'

/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({
    reports: {
        type: Array,
        default: () => [],
    },

    categories: {
        type: Array,
        default: () => [],
    },

    recentRuns: {
        type: Object,
        default: () => ({
            data: [],
            links: [],
            current_page: 1,
            last_page: 1,
            total: 0,
        }),
    },

    branches: {
        type: Array,
        default: () => [],
    },

    filters: {
        type: Object,
        default: () => ({
            search: '',
            category: '',
            branch_id: null,
        }),
    },

    permissions: {
        type: Object,
        default: () => ({
            view: false,
            viewAllBranches: false,
        }),
    },

    currentBranch: {
        type: Object,
        default: null,
    },
})

/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const search = ref(props.filters.search ?? '')
const selectedCategory = ref(props.filters.category ?? '')
const selectedBranch = ref(
    props.filters.branch_id === null
        ? ''
        : String(props.filters.branch_id)
)

const viewMode = ref('grid')
const showFilters = ref(false)

let searchTimeout = null

/*
|--------------------------------------------------------------------------
| Category Icons
|--------------------------------------------------------------------------
*/

const categoryIcons = {
    Sales: ShoppingCartIcon,
    Finance: CurrencyDollarIcon,
    Purchase: ShoppingBagIcon,
    CRM: UsersIcon,
    Project: ClipboardDocumentListIcon,
    Marketing: MegaphoneIcon,
    Inventory: CubeIcon,
    HR: Cog6ToothIcon,
}

const categoryColors = {
    Sales: {
        background: 'bg-emerald-50',
        icon: 'text-emerald-600',
        badge: 'bg-emerald-50 text-emerald-700',
    },

    Finance: {
        background: 'bg-purple-50',
        icon: 'text-purple-600',
        badge: 'bg-purple-50 text-purple-700',
    },

    Purchase: {
        background: 'bg-orange-50',
        icon: 'text-orange-600',
        badge: 'bg-orange-50 text-orange-700',
    },

    CRM: {
        background: 'bg-blue-50',
        icon: 'text-blue-600',
        badge: 'bg-blue-50 text-blue-700',
    },

    Project: {
        background: 'bg-amber-50',
        icon: 'text-amber-600',
        badge: 'bg-amber-50 text-amber-700',
    },

    Marketing: {
        background: 'bg-rose-50',
        icon: 'text-rose-600',
        badge: 'bg-rose-50 text-rose-700',
    },

    Inventory: {
        background: 'bg-teal-50',
        icon: 'text-teal-600',
        badge: 'bg-teal-50 text-teal-700',
    },

    HR: {
        background: 'bg-slate-100',
        icon: 'text-slate-600',
        badge: 'bg-slate-100 text-slate-700',
    },
}

const getCategoryIcon = (category) => {
    return categoryIcons[category] ?? DocumentChartBarIcon
}

const getCategoryColor = (category) => {
    return categoryColors[category] ?? {
        background: 'bg-blue-50',
        icon: 'text-blue-600',
        badge: 'bg-blue-50 text-blue-700',
    }
}

/*
|--------------------------------------------------------------------------
| Computed
|--------------------------------------------------------------------------
*/

const reportCount = computed(() => {
    return props.reports.length
})

const hasActiveFilter = computed(() => {
    return Boolean(
        search.value ||
        selectedCategory.value ||
        selectedBranch.value
    )
})

const currentBranchName = computed(() => {
    if (!selectedBranch.value) {
        return 'All Branches'
    }

    const branch = props.branches.find(
        item => String(item.id) === String(selectedBranch.value)
    )

    return branch?.name ?? 'Selected Branch'
})

/*
|--------------------------------------------------------------------------
| Apply Filters
|--------------------------------------------------------------------------
*/

const applyFilters = () => {
    router.get(
        route('reports.index'),
        {
            search: search.value || undefined,
            category: selectedCategory.value || undefined,
            branch_id: selectedBranch.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    )
}

/*
|--------------------------------------------------------------------------
| Search
|--------------------------------------------------------------------------
*/

watch(search, () => {
    clearTimeout(searchTimeout)

    searchTimeout = setTimeout(() => {
        applyFilters()
    }, 400)
})

/*
|--------------------------------------------------------------------------
| Category Filter
|--------------------------------------------------------------------------
*/

const selectCategory = (category) => {
    selectedCategory.value =
        selectedCategory.value === category
            ? ''
            : category

    applyFilters()
}

/*
|--------------------------------------------------------------------------
| Branch Filter
|--------------------------------------------------------------------------
*/

const changeBranch = () => {
    applyFilters()
}

/*
|--------------------------------------------------------------------------
| Reset Filters
|--------------------------------------------------------------------------
*/

const resetFilters = () => {
    search.value = ''
    selectedCategory.value = ''
    selectedBranch.value = ''

    applyFilters()
}

/*
|--------------------------------------------------------------------------
| Recently Run Reports
|--------------------------------------------------------------------------
*/

const recentRunData = computed(() => {
    return props.recentRuns?.data ?? []
})

const formatDate = (date) => {
    if (!date) return '-'

    return new Intl.DateTimeFormat('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(date))
}

const formatLabel = (format) => {
    if (!format) return '-'

    return String(format).toUpperCase()
}

const getStatusClass = (status) => {
    const classes = {
        completed: 'bg-emerald-50 text-emerald-700',
        processing: 'bg-blue-50 text-blue-700',
        failed: 'bg-red-50 text-red-700',
    }

    return classes[status] ?? 'bg-slate-100 text-slate-600'
}
/*
|--------------------------------------------------------------------------
| Open Report
|--------------------------------------------------------------------------
*/

const openReport = (report) => {
    if (!report.route_name) {
        console.error('Report route is not defined:', report.code)
        return
    }

    router.visit(route(report.route_name))
}
const getStatusLabel = (status) => {
    const labels = {
        completed: 'Completed',
        processing: 'Processing',
        failed: 'Failed',
    }

    return labels[status] ?? status
}

/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/

const goToPage = (url) => {
    if (!url) return

    router.get(
        url,
        {},
        {
            preserveState: true,
            preserveScroll: true,
        }
    )
}

/*
|--------------------------------------------------------------------------
| Cleanup
|--------------------------------------------------------------------------
*/

const cleanupSearch = () => {
    clearTimeout(searchTimeout)
}
</script>

<template>
    <AppLayout title="Report Catalog">

        <div class="space-y-6">

            <!-- ===================================================== -->
            <!-- HEADER -->
            <!-- ===================================================== -->

            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >

                <div>
                    <h1 class="text-2xl font-bold text-slate-800">
                        Report Catalog
                    </h1>

                    <p class="mt-1 text-sm text-slate-500">
                        Browse and run reports across all modules.
                    </p>

                    <div
                        v-if="currentBranch"
                        class="mt-2 flex items-center gap-2 text-xs text-slate-500"
                    >
                        <span
                            class="h-2 w-2 rounded-full bg-emerald-500"
                        ></span>

                        Current Branch:
                        <span class="font-semibold text-slate-700">
                            {{ currentBranch.name }}
                        </span>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">

                    <button
                        type="button"
                        disabled
                        title="Coming soon"
                        class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-400 shadow-sm cursor-not-allowed"
                    >
                        <DocumentPlusIcon class="h-4 w-4" />

                        Request a Report
                    </button>

                    <button
                        type="button"
                        disabled
                        title="Coming soon"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm opacity-70 cursor-not-allowed"
                    >
                        <span class="text-lg leading-none">+</span>

                        Create Custom Report
                    </button>

                </div>

            </div>


            <!-- ===================================================== -->
            <!-- TOOLBAR -->
            <!-- ===================================================== -->

            <div
                class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between"
            >

                <div class="relative w-full lg:max-w-md">

                    <MagnifyingGlassIcon
                        class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400"
                    />

                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search reports..."
                        class="w-full rounded-lg border border-slate-200 bg-white py-2.5 pl-10 pr-10 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                    />

                    <button
                        v-if="search"
                        type="button"
                        @click="search = ''"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                    >
                        <XMarkIcon class="h-4 w-4" />
                    </button>

                </div>


                <div class="flex flex-wrap items-center gap-2">

                    <!-- View Mode -->

                    <div
                        class="flex rounded-lg border border-slate-200 bg-white p-1"
                    >

                        <button
                            type="button"
                            @click="viewMode = 'grid'"
                            :class="viewMode === 'grid'
                                ? 'bg-blue-50 text-blue-600'
                                : 'text-slate-400 hover:text-slate-700'"
                            class="rounded-md p-2 transition"
                            title="Grid View"
                        >
                            <Squares2X2Icon class="h-5 w-5" />
                        </button>

                        <button
                            type="button"
                            @click="viewMode = 'list'"
                            :class="viewMode === 'list'
                                ? 'bg-blue-50 text-blue-600'
                                : 'text-slate-400 hover:text-slate-700'"
                            class="rounded-md p-2 transition"
                            title="List View"
                        >
                            <ListBulletIcon class="h-5 w-5" />
                        </button>

                    </div>


                    <!-- Filter Toggle -->

                    <button
                        type="button"
                        @click="showFilters = !showFilters"
                        class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50"
                    >
                        <FunnelIcon class="h-4 w-4" />

                        Filter
                    </button>

                </div>

            </div>


            <!-- ===================================================== -->
            <!-- FILTER PANEL -->
            <!-- ===================================================== -->

            <div
                v-if="showFilters"
                class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
            >

                <div class="grid gap-4 md:grid-cols-2">

                    <!-- Category -->

                    <div>
                        <label
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Category
                        </label>

                        <select
                            v-model="selectedCategory"
                            @change="applyFilters"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-500"
                        >
                            <option value="">
                                All Categories
                            </option>

                            <option
                                v-for="category in categories"
                                :key="category.name"
                                :value="category.name"
                            >
                                {{ category.name }}
                            </option>

                        </select>
                    </div>


                    <!-- Branch -->

                    <div>
                        <label
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Branch
                        </label>

                        <select
                            v-model="selectedBranch"
                            @change="changeBranch"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-500"
                        >

                            <option
                                v-if="permissions.viewAllBranches"
                                value=""
                            >
                                All Branches
                            </option>

                            <option
                                v-for="branch in branches"
                                :key="branch.id"
                                :value="String(branch.id)"
                            >
                                {{ branch.name }}
                            </option>

                        </select>
                    </div>

                </div>

                <div class="mt-4 flex items-center justify-between">

                    <button
                        type="button"
                        @click="resetFilters"
                        class="text-sm font-medium text-slate-500 hover:text-blue-600"
                    >
                        Reset Filters
                    </button>

                    <span class="text-xs text-slate-400">
                        {{ currentBranchName }}
                    </span>

                </div>

            </div>


            <!-- ===================================================== -->
            <!-- CATEGORY HEADER -->
            <!-- ===================================================== -->

            <div class="flex items-center justify-between">

                <div>
                    <h2 class="text-base font-bold text-slate-800">
                        Report Categories
                    </h2>

                    <p class="mt-1 text-xs text-slate-500">
                        Explore reports by business module.
                    </p>
                </div>

                <span class="text-xs font-medium text-slate-500">
                    {{ categories.length }} Categories
                </span>

            </div>


            <!-- ===================================================== -->
            <!-- CATEGORY GRID -->
            <!-- ===================================================== -->

            <div
                v-if="viewMode === 'grid'"
                class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4"
            >

                <div
                    v-for="category in categories"
                    :key="category.name"
                    class="group rounded-xl border bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                    :class="selectedCategory === category.name
                        ? 'border-blue-400 ring-2 ring-blue-100'
                        : 'border-slate-200'"
                >

                    <div class="flex flex-col items-center text-center">

                        <!-- Icon -->

                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-full"
                            :class="getCategoryColor(category.name).background"
                        >
                            <component
                                :is="getCategoryIcon(category.name)"
                                class="h-7 w-7"
                                :class="getCategoryColor(category.name).icon"
                            />
                        </div>


                        <!-- Name -->

                        <h3 class="mt-3 text-base font-bold text-slate-800">
                            {{ category.name }}
                        </h3>


                        <!-- Description -->

                        <p
                            class="mt-2 min-h-[40px] text-xs leading-5 text-slate-500"
                        >
                            {{ category.description }}
                        </p>


                        <!-- Count -->

                        <span
                            class="mt-3 rounded-full px-3 py-1 text-xs font-semibold"
                            :class="getCategoryColor(category.name).badge"
                        >
                            {{ category.count }} reports
                        </span>

                    </div>


                    <!-- View Reports -->

                    <button
                        type="button"
                        @click="selectCategory(category.name)"
                        class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-600"
                    >
                        {{
                            selectedCategory === category.name
                                ? 'Clear Filter'
                                : 'View Reports'
                        }}

                        <ChevronRightIcon class="h-4 w-4" />
                    </button>

                </div>

            </div>


            <!-- ===================================================== -->
            <!-- CATEGORY LIST -->
            <!-- ===================================================== -->

            <div
                v-else
                class="overflow-hidden rounded-xl border border-slate-200 bg-white"
            >

                <button
                    v-for="category in categories"
                    :key="category.name"
                    type="button"
                    @click="selectCategory(category.name)"
                    class="flex w-full items-center justify-between gap-4 border-b border-slate-100 px-5 py-4 text-left transition last:border-0 hover:bg-slate-50"
                    :class="selectedCategory === category.name
                        ? 'bg-blue-50'
                        : ''"
                >

                    <div class="flex min-w-0 items-center gap-3">

                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg"
                            :class="getCategoryColor(category.name).background"
                        >
                            <component
                                :is="getCategoryIcon(category.name)"
                                class="h-5 w-5"
                                :class="getCategoryColor(category.name).icon"
                            />
                        </div>

                        <div class="min-w-0">

                            <h3 class="text-sm font-semibold text-slate-800">
                                {{ category.name }}
                            </h3>

                            <p class="mt-1 text-xs text-slate-500">
                                {{ category.description }}
                            </p>

                        </div>

                    </div>

                    <div class="flex shrink-0 items-center gap-3">

                        <span
                            class="rounded-full px-3 py-1 text-xs font-semibold"
                            :class="getCategoryColor(category.name).badge"
                        >
                            {{ category.count }} reports
                        </span>

                        <ChevronRightIcon
                            class="h-4 w-4 text-slate-400"
                        />

                    </div>

                </button>

            </div>


            <!-- ===================================================== -->
            <!-- REPORT SEARCH RESULTS -->
            <!-- ===================================================== -->

            <div
                v-if="hasActiveFilter"
                class="overflow-hidden rounded-xl border border-slate-200 bg-white"
            >

                <div
                    class="flex flex-col gap-2 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                >

                    <div>
                        <h2 class="font-bold text-slate-800">
                            Available Reports
                        </h2>

                        <p class="mt-1 text-xs text-slate-500">
                            {{ reportCount }} reports found.
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="resetFilters"
                        class="text-left text-xs font-semibold text-blue-600 hover:text-blue-700 sm:text-right"
                    >
                        Clear Filters
                    </button>

                </div>


                <div
                    v-if="reports.length"
                    class="divide-y divide-slate-100"
                >

                    <div
    v-for="report in reports"
    :key="report.id"
    class="flex items-center justify-between gap-3 px-5 py-4 transition hover:bg-slate-50"
>
    <!-- Report Information -->

    <div class="flex min-w-0 items-center gap-3">

        <div
            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-50"
        >
            <DocumentChartBarIcon
                class="h-5 w-5 text-blue-600"
            />
        </div>

        <div class="min-w-0">

            <h3 class="truncate text-sm font-semibold text-slate-800">
                {{ report.name }}
            </h3>

            <p class="mt-1 text-xs text-slate-500">
                {{ report.description }}
            </p>

            <!-- Available Formats -->

            <div class="mt-2 flex flex-wrap gap-1">

                <span
                    v-for="format in report.available_formats ?? []"
                    :key="format"
                    class="rounded bg-slate-100 px-2 py-1 text-[10px] font-medium uppercase text-slate-600"
                >
                    {{ format }}
                </span>

            </div>

        </div>

    </div>


    <!-- Actions -->

    <div class="flex shrink-0 items-center gap-3">

        <span
            class="hidden rounded-full px-3 py-1 text-xs font-semibold sm:inline-flex"
            :class="getCategoryColor(report.category).badge"
        >
            {{ report.category }}
        </span>

        <button
            v-if="report.route_name"
            type="button"
            @click="openReport(report)"
            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-blue-700"
        >
            View

            <ChevronRightIcon class="h-4 w-4" />
        </button>

    </div>

</div>

                </div>


                <div
                    v-else
                    class="px-5 py-10 text-center"
                >
                    <MagnifyingGlassIcon
                        class="mx-auto h-10 w-10 text-slate-300"
                    />

                    <p class="mt-3 text-sm font-semibold text-slate-700">
                        No reports found
                    </p>

                    <p class="mt-1 text-xs text-slate-500">
                        Try another search keyword or category.
                    </p>
                </div>

            </div>


            <!-- ===================================================== -->
            <!-- RECENTLY RUN REPORTS -->
            <!-- ===================================================== -->

            <div
                class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
            >

                <!-- Header -->

                <div
                    class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                >

                    <div class="flex items-center gap-2">

                        <ClockIcon class="h-5 w-5 text-slate-400" />

                        <div>
                            <h2 class="text-base font-bold text-slate-800">
                                Recently Run Reports
                            </h2>

                            <p class="mt-1 text-xs text-slate-500">
                                Your recent report execution history.
                            </p>
                        </div>

                    </div>

                    <span class="text-xs font-medium text-slate-500">
                        {{ recentRuns.total ?? 0 }} records
                    </span>

                </div>


                <!-- Table -->

                <div class="overflow-x-auto">

                    <table class="w-full min-w-[850px] text-left">

                        <thead class="bg-slate-50">

                            <tr class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">

                                <th class="px-5 py-3">
                                    Report Name
                                </th>

                                <th class="px-4 py-3">
                                    Category
                                </th>

                                <th class="px-4 py-3">
                                    Run By
                                </th>

                                <th class="px-4 py-3">
                                    Run Date
                                </th>

                                <th class="px-4 py-3">
                                    Format
                                </th>

                                <th class="px-4 py-3">
                                    Status
                                </th>

                                <th class="px-4 py-3 text-center">
                                    Actions
                                </th>

                            </tr>

                        </thead>


                        <tbody
                            v-if="recentRunData.length"
                            class="divide-y divide-slate-100"
                        >

                            <tr
                                v-for="run in recentRunData"
                                :key="run.id"
                                class="text-xs transition hover:bg-slate-50"
                            >

                                <!-- Report Name -->

                                <td class="px-5 py-4">

                                    <span class="font-semibold text-blue-600">
                                        {{ run.report?.name ?? 'Unknown Report' }}
                                    </span>

                                </td>


                                <!-- Category -->

                                <td class="px-4 py-4">

                                    <span
                                        v-if="run.report"
                                        class="rounded-full px-3 py-1 text-[10px] font-semibold"
                                        :class="getCategoryColor(run.report.category).badge"
                                    >
                                        {{ run.report.category }}
                                    </span>

                                    <span v-else>-</span>

                                </td>


                                <!-- User -->

                                <td class="px-4 py-4 text-slate-600">
                                    {{ run.user?.name ?? 'Unknown User' }}
                                </td>


                                <!-- Date -->

                                <td class="whitespace-nowrap px-4 py-4 text-slate-500">
                                    {{ formatDate(run.started_at) }}
                                </td>


                                <!-- Format -->

                                <td class="px-4 py-4">

                                    <span
                                        class="font-semibold text-slate-600"
                                    >
                                        {{ formatLabel(run.format) }}
                                    </span>

                                </td>


                                <!-- Status -->

                                <td class="px-4 py-4">

                                    <span
                                        class="rounded-full px-3 py-1 text-[10px] font-semibold"
                                        :class="getStatusClass(run.status)"
                                    >
                                        {{ getStatusLabel(run.status) }}
                                    </span>

                                </td>


                                <!-- Actions -->

                                <td class="px-4 py-4 text-center">

                                    <div class="flex items-center justify-center gap-2">

                                        <button
                                            type="button"
                                            disabled
                                            title="Download will be available when report download is implemented"
                                            class="rounded-md p-1.5 text-slate-300 cursor-not-allowed"
                                        >
                                            <ArrowDownTrayIcon
                                                class="h-4 w-4"
                                            />
                                        </button>

                                        <button
                                            type="button"
                                            disabled
                                            title="More actions coming soon"
                                            class="rounded-md p-1.5 text-slate-300 cursor-not-allowed"
                                        >
                                            <EllipsisHorizontalIcon
                                                class="h-5 w-5"
                                            />
                                        </button>

                                    </div>

                                </td>

                            </tr>

                        </tbody>

                    </table>


                    <!-- Empty State -->

                    <div
                        v-if="!recentRunData.length"
                        class="px-5 py-14 text-center"
                    >

                        <div
                            class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-slate-100"
                        >
                            <ClockIcon class="h-7 w-7 text-slate-400" />
                        </div>

                        <h3 class="mt-4 text-sm font-bold text-slate-700">
                            No Report History Yet
                        </h3>

                        <p class="mx-auto mt-1 max-w-sm text-xs leading-5 text-slate-500">
                            Reports you run will appear here with their execution date, format, and status.
                        </p>

                    </div>

                </div>


                <!-- ================================================= -->
                <!-- PAGINATION -->
                <!-- ================================================= -->

                <div
                    v-if="recentRuns.last_page > 1"
                    class="flex flex-col gap-3 border-t border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                >

                    <p class="text-xs text-slate-500">
                        Showing
                        {{ recentRuns.from ?? 0 }}
                        to
                        {{ recentRuns.to ?? 0 }}
                        of
                        {{ recentRuns.total ?? 0 }}
                        records
                    </p>

                    <div class="flex items-center justify-end gap-1">

                        <button
                            v-for="(link, index) in recentRuns.links"
                            :key="index"
                            type="button"
                            @click="goToPage(link.url)"
                            :disabled="!link.url"
                            v-html="link.label"
                            class="min-w-9 rounded-lg border px-3 py-2 text-xs transition"
                            :class="[
                                link.active
                                    ? 'border-blue-600 bg-blue-600 text-white'
                                    : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50',
                                !link.url
                                    ? 'cursor-not-allowed opacity-40'
                                    : ''
                            ]"
                        />

                    </div>

                </div>

            </div>

        </div>

    </AppLayout>
</template>