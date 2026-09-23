<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'

import BaseButton from '@/Components/Button/BaseButton.vue'
import SearchableSelect from '@/Components/Form/SearchableSelect.vue'
import StatusBadge from '@/Components/Display/StatusBadge.vue'
import TablePagination from '@/Components/Table/TablePagination.vue'
import { LoadingOverlay } from '@/Components/Feedback'
import {ArrowPathIcon,ArrowDownTrayIcon, ChevronDownIcon, QrCodeIcon, PhotoIcon,
} from '@heroicons/vue/24/outline'
import {ref,reactive,computed,watch,onMounted,onUnmounted,} from 'vue'
import { router } from '@inertiajs/vue3'
import Swal from 'sweetalert2'


// ============================================================================
// Props
// ============================================================================

const props = defineProps({
    variants: {
        type: Object,
        default: () => ({
            data: [],
            current_page: 1,
            last_page: 1,
            per_page: 20,
            total: 0,
            from: 0,
            to: 0,
        }),
    },

    products: {
        type: Array,
        default: () => [],
    },

    statistics: {
        type: Object,
        default: () => ({}),
    },

    filters: {
        type: Object,
        default: () => ({}),
    },
})


// ============================================================================
// State
// ============================================================================

const filters = reactive({
    search: props.filters?.search ?? '',
    product_id: props.filters?.product_id ?? '',
    barcode_status: props.filters?.barcode_status ?? '',
    is_active: props.filters?.is_active ?? '',
    per_page: props.filters?.per_page ?? 20,
})

const selectedIds = ref([])

const bulkOpen = ref(false)

const isLoading = ref(false)

let searchTimer = null


// ============================================================================
// Computed
// ============================================================================

const rows = computed(() => {
    return props.variants?.data ?? []
})

const allVisibleSelected = computed(() => {
    if (!rows.value.length) {
        return false
    }

    return rows.value.every((item) =>
        selectedIds.value.includes(item.id)
    )
})

const someVisibleSelected = computed(() => {
    return (
        selectedIds.value.length > 0 &&
        !allVisibleSelected.value
    )
})

const selectedCount = computed(() => {
    return selectedIds.value.length
})


// ============================================================================
// Selection
// ============================================================================

const toggleRow = (id) => {
    const index = selectedIds.value.indexOf(id)

    if (index === -1) {
        selectedIds.value.push(id)
    } else {
        selectedIds.value.splice(index, 1)
    }
}

const isSelected = (id) => {
    return selectedIds.value.includes(id)
}

const toggleAll = () => {
    if (allVisibleSelected.value) {
        selectedIds.value = selectedIds.value.filter(
            (id) => !rows.value.some((row) => row.id === id)
        )

        return
    }

    const ids = rows.value.map((row) => row.id)

    selectedIds.value = [
        ...new Set([
            ...selectedIds.value,
            ...ids,
        ]),
    ]
}

const clearSelection = () => {
    selectedIds.value = []
}


// ============================================================================
// Filters
// ============================================================================

const visit = () => {
    isLoading.value = true

    router.get(
        route('barcodes.index'),
        {
            search: filters.search || undefined,
            product_id: filters.product_id || undefined,
            barcode_status:
                filters.barcode_status || undefined,
            is_active:
                filters.is_active !== ''
                    ? filters.is_active
                    : undefined,
            per_page: filters.per_page,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,

            onFinish: () => {
                isLoading.value = false
            },
        }
    )
}

const refresh = () => {
    clearSelection()

    filters.search = ''
    filters.product_id = ''
    filters.barcode_status = ''
    filters.is_active = ''
    filters.per_page = 20

    visit()
}

const debounceSearch = () => {
    clearTimeout(searchTimer)

    searchTimer = setTimeout(() => {
        visit()
    }, 400)
}

watch(
    () => filters.search,
    () => {
        debounceSearch()
    }
)

watch(
    () => filters.product_id,
    () => {
        visit()
    }
)

watch(
    () => filters.barcode_status,
    () => {
        visit()
    }
)

watch(
    () => filters.is_active,
    () => {
        visit()
    }
)

watch(
    () => filters.per_page,
    () => {
        visit()
    }
)


// ============================================================================
// Pagination
// ============================================================================

const changePage = (page) => {
    isLoading.value = true

    router.get(
        route('barcodes.index'),
        {
            ...filters,
            page,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,

            onFinish: () => {
                isLoading.value = false
            },
        }
    )
}


// ============================================================================
// Export
// ============================================================================

const exportData = () => {
    Swal.fire({
        icon: 'info',
        title: 'Export',
        text: 'Fitur export Barcode Management akan disiapkan pada tahap berikutnya.',
        confirmButtonText: 'OK',
    })
}


// ============================================================================
// Bulk Action
// ============================================================================

const toggleBulk = () => {
    if (!selectedCount.value) {
        Swal.fire({
            icon: 'info',
            title: 'No rows selected',
            text: 'Pilih minimal satu Product Variant terlebih dahulu.',
            confirmButtonText: 'OK',
        })

        return
    }

    bulkOpen.value = !bulkOpen.value
}

const closeBulk = () => {
    bulkOpen.value = false
}

const bulkGenerate = () => {
    closeBulk()

    Swal.fire({
        icon: 'info',
        title: 'Bulk Generate Barcode',
        text: 'Fungsi Bulk Generate Barcode akan diaktifkan pada tahap berikutnya.',
        confirmButtonText: 'OK',
    })
}


// ============================================================================
// Row Actions - placeholder tahap 1
// ============================================================================
const showViewModal = ref(false)

const viewData = ref(null)
const viewVariant = (variant) => {
    viewData.value = variant

    showViewModal.value = true
}
const closeViewModal = () => {
    showViewModal.value = false
    viewData.value = null
}
const showGenerateModal = ref(false)

const selectedVariant = ref(null)
const generateBarcode = (variant) => {
    selectedVariant.value = variant

    showGenerateModal.value = true
}
const closeGenerateModal = () => {
    showGenerateModal.value = false
    selectedVariant.value = null
}
const submitGenerateBarcode = () => {

    if (!selectedVariant.value) {
        return
    }

    const variantId = selectedVariant.value.id

    isLoading.value = true

    router.post(
        route(
            'barcodes.generate',
            variantId
        ),
        {},
        {
            preserveScroll: true,

            onSuccess: () => {

                closeGenerateModal()

                selectedIds.value =
                    selectedIds.value.filter(
                        (id) => id !== variantId
                    )

                Swal.fire({
                    icon: 'success',
                    title: 'Barcode Generated',
                    text: 'Barcode berhasil dibuat.',
                    confirmButtonText: 'OK',
                })
            },

            onError: () => {

                Swal.fire({
                    icon: 'error',
                    title: 'Generate Barcode Failed',
                    text: 'Gagal membuat barcode.',
                    confirmButtonText: 'OK',
                })
            },

            onFinish: () => {
                isLoading.value = false
            },
        }
    )
}

const editBarcode = (variant) => {
    Swal.fire({
        icon: 'info',
        title: 'Edit Barcode',
        text: `Edit Barcode untuk ${variant.name}. Modal akan dibuat pada tahap berikutnya.`,
        confirmButtonText: 'OK',
    })
}
const showPreviewModal = ref(false)
const closePreviewModal = () => {
    showPreviewModal.value = false
    previewData.value = null
}
const previewData = ref(null)

const previewLoading = ref(false)
const previewBarcode = async (variant) => {

    previewLoading.value = true

    try {

        const response = await fetch(
            route(
                'barcodes.preview',
                variant.id
            ),
            {
                headers: {
                    Accept: 'application/json',
                },
            }
        )

        const data = await response.json()

        if (!response.ok) {
            throw new Error(
                data.message ||
                'Gagal mengambil barcode.'
            )
        }

        previewData.value = data

        showPreviewModal.value = true

    } catch (error) {

        Swal.fire({
            icon: 'error',
            title: 'Preview Barcode Failed',
            text: error.message ||
                'Gagal mengambil data barcode.',
            confirmButtonText: 'OK',
        })

    } finally {

        previewLoading.value = false

    }
}
const printCopies = ref(1)
const printLabel = (data) => {

    if (!data?.svg || !data?.barcode) {
        Swal.fire({
            icon: 'error',
            title: 'Print Failed',
            text: 'Data barcode tidak tersedia.',
            confirmButtonText: 'OK',
        })

        return
    }

    const printWindow = window.open(
        '',
        '_blank',
        'width=800,height=600'
    )

    if (!printWindow) {
        Swal.fire({
            icon: 'warning',
            title: 'Popup Blocked',
            text: 'Izinkan popup browser untuk mencetak barcode.',
            confirmButtonText: 'OK',
        })

        return
    }

    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>

            <meta charset="UTF-8">

            <title>
                Barcode - ${data.barcode}
            </title>

            <style>

                @page {
                    margin: 0;
                }

                * {
                    box-sizing: border-box;
                }

                html,
                body {
                    margin: 0;
                    padding: 0;
                    background: white;
                }

                body {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    min-height: 100vh;
                    font-family:
                        Arial,
                        Helvetica,
                        sans-serif;
                }

                .label {
                    width: 80mm;
                    padding: 8mm 6mm;
                    text-align: center;
                }

                .brand {
                    margin-bottom: 3mm;
                    font-size: 12px;
                    font-weight: 700;
                }

                .product {
                    margin-bottom: 2mm;
                    font-size: 11px;
                    font-weight: 600;
                }

                .variant {
                    margin-bottom: 3mm;
                    font-size: 9px;
                    color: #555;
                }

                .barcode {
                    width: 100%;
                    overflow: hidden;
                }

                .barcode svg {
                    display: block;
                    width: 100% !important;
                    height: auto !important;
                    max-width: 100% !important;
                }

                .barcode-number {
                    margin-top: 2mm;
                    font-family: monospace;
                    font-size: 10px;
                    font-weight: 600;
                    letter-spacing: 1px;
                }

                .sku {
                    margin-top: 2mm;
                    font-size: 8px;
                    color: #666;
                }

                @media print {

                    body {
                        min-height: auto;
                    }

                    .label {
                        page-break-inside: avoid;
                    }

                }

            </style>

        </head>

        <body>

            <div class="label">

                <div class="brand">
                    Nuvora
                </div>

                <div class="product">
                    ${escapeHtml(data.product ?? '')}
                </div>

                <div class="variant">
                    ${escapeHtml(data.variant ?? '')}
                </div>

                <div class="barcode">
                    ${data.svg}
                </div>

                <div class="barcode-number">
                    ${escapeHtml(data.barcode)}
                </div>

                <div class="sku">
                    SKU: ${escapeHtml(data.sku ?? '')}
                </div>

            </div>

        </body>
        </html>
    `)

    printWindow.document.close()

    printWindow.focus()

    setTimeout(() => {

        printWindow.print()

        printWindow.close()

    }, 300)
}


// ============================================================================
// Helpers
// ============================================================================

const hasBarcode = (variant) => {
    return Boolean(
        variant?.barcode &&
        String(variant.barcode).trim() !== ''
    )
}

const variantValues = (variant) => {
    return variant?.values ?? []
}

const attributeName = (value) => {
    return (
        value?.attribute?.display_name ||
        value?.attribute?.name ||
        ''
    )
}

const attributeValue = (value) => {
    return (
        value?.attribute_value?.display_value ||
        value?.attribute_value?.value ||
        ''
    )
}

const productImage = (variant) => {
    return (
        variant?.product?.image_url ||
        variant?.product?.image ||
        variant?.product?.thumbnail ||
        null
    )
}

const formatBarcode = (barcode) => {
    if (!barcode) {
        return 'No Barcode'
    }

    return barcode
}

const escapeHtml = (value) => {

    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;')
}
// ============================================================================
// Outside click - bulk menu
// ============================================================================

const handleDocumentClick = (event) => {
    const target = event.target

    if (!target.closest('[data-bulk-menu]')) {
        closeBulk()
    }
}

onMounted(() => {
    document.addEventListener(
        'click',
        handleDocumentClick
    )
})

onUnmounted(() => {
    document.removeEventListener(
        'click',
        handleDocumentClick
    )

    clearTimeout(searchTimer)
})
const statusOptions = [
    {
        value: 1,
        label: 'Active',
    },
    {
        value: 0,
        label: 'Inactive',
    },
]

</script>


<template>

    <AppLayout>

        <div
            class="
                space-y-4
            "
        >

            <!-- ========================================================= -->
            <!-- Header -->
            <!-- ========================================================= -->

            <div
                class="
                    flex
                    flex-col
                    gap-3
                    sm:flex-row
                    sm:items-center
                    sm:justify-between
                "
            >

                <div>

                    <h1
                        class="
                            text-xl
                            font-semibold
                            text-gray-900
                        "
                    >
                        Barcode Management
                    </h1>

                    <p
                        class="
                            mt-1
                            text-sm
                            text-gray-500
                        "
                    >
                        Manage product variant barcodes for POS and label printing.
                    </p>

                </div>


                <div
                    class="
                        flex
                        items-center
                        gap-2
                    "
                >

                    <BaseButton
                        variant="secondary"
                        @click="refresh"
                    >
                        <template #icon>

                            <ArrowPathIcon
                                class="h-5 w-5"
                            />

                        </template>

                        Refresh
                    </BaseButton>


                    <BaseButton
                        variant="secondary"
                        @click="exportData"
                    >
                        <template #icon>

                            <ArrowDownTrayIcon
                                class="h-5 w-5"
                            />

                        </template>

                        Export
                    </BaseButton>

                </div>

            </div>


           <!-- ========================================================= -->
            <!-- Toolbar -->
            <!-- ========================================================= -->

            <div
                class="
                    flex
                    flex-col
                    gap-3
                    lg:flex-row
                    lg:items-center
                "
            >

                <!-- Filters -->

                <div
                    class="
                        flex
                        flex-1
                        flex-col
                        gap-2
                        sm:flex-row
                        sm:items-center
                    "
                >

                    <!-- Search -->

                    <div
                        class="
                            relative
                            w-full
                            sm:max-w-md
                        "
                    >

                        <input
                            v-model="filters.search"
                            type="text"
                            placeholder="Search Product, SKU, Barcode..."
                            class="
                                w-full
                                rounded-lg
                                border
                                border-gray-300
                                bg-white
                                px-3
                                py-2
                                text-sm
                                text-gray-900
                                outline-none
                                transition
                                placeholder:text-gray-400
                                focus:border-blue-500
                                focus:ring-2
                                focus:ring-blue-100
                            "
                        />

                    </div>


                    <!-- Barcode Status -->

                    <select
                        v-model="filters.barcode_status"
                        class="
                            w-full
                            rounded-lg
                            border
                            border-gray-300
                            bg-white
                            px-3
                            py-2
                            text-sm
                            text-gray-700
                            outline-none
                            focus:border-blue-500
                            focus:ring-2
                            focus:ring-blue-100
                            sm:w-48
                        "
                    >

                        <option value="">
                            All Barcode
                        </option>

                        <option value="with_barcode">
                            With Barcode
                        </option>

                        <option value="without_barcode">
                            Without Barcode
                        </option>

                    </select>


                    <!-- Status -->

                    <select
                        v-model="filters.is_active"
                        class="
                            w-full
                            rounded-lg
                            border
                            border-gray-300
                            bg-white
                            px-3
                            py-2
                            text-sm
                            text-gray-700
                            outline-none
                            focus:border-blue-500
                            focus:ring-2
                            focus:ring-blue-100
                            sm:w-36
                        "
                    >

                        <option value="">
                            All Status
                        </option>

                        <option value="1">
                            Active
                        </option>

                        <option value="0">
                            Inactive
                        </option>

                    </select>


                    <!-- Bulk Action -->

                    <div
                        class="
                            relative
                            shrink-0
                        "
                        data-bulk-menu
                    >

                        <BaseButton
                            variant="secondary"
                            :disabled="!selectedCount"
                            @click.stop="toggleBulk"
                        >

                            <template #icon>

                                <ChevronDownIcon
                                    class="h-5 w-5"
                                />

                            </template>

                            Bulk Action

                            <span
                                v-if="selectedCount"
                                class="
                                    ml-1
                                    rounded-full
                                    bg-blue-100
                                    px-2
                                    py-0.5
                                    text-xs
                                    font-medium
                                    text-blue-700
                                "
                            >
                                {{ selectedCount }}
                            </span>

                        </BaseButton>


                        <!-- Bulk Dropdown -->

                        <div
                            v-if="bulkOpen"
                            class="
                                absolute
                                left-0
                                z-30
                                mt-2
                                w-52
                                rounded-lg
                                border
                                border-gray-200
                                bg-white
                                py-1
                                shadow-lg
                            "
                        >

                            <button
                                type="button"
                                class="
                                    flex
                                    w-full
                                    items-center
                                    px-3
                                    py-2
                                    text-left
                                    text-sm
                                    text-gray-700
                                    transition
                                    hover:bg-gray-50
                                "
                                @click="bulkGenerate"
                            >
                                Generate Barcode
                            </button>

                        </div>

                    </div>

                </div>

            </div>


            <!-- ========================================================= -->
            <!-- Selection Info -->
            <!-- ========================================================= -->

            <div
                v-if="selectedCount"
                class="
                    flex
                    items-center
                    justify-between
                    rounded-lg
                    border
                    border-blue-100
                    bg-blue-50
                    px-3
                    py-2
                    text-sm
                "
            >

                <span class="text-blue-700">
                    {{ selectedCount }} row selected
                </span>

                <button
                    type="button"
                    class="
                        font-medium
                        text-blue-700
                        hover:text-blue-900
                    "
                    @click="clearSelection"
                >
                    Clear selection
                </button>

            </div>


            <!-- ========================================================= -->
            <!-- Table -->
            <!-- ========================================================= -->

            <div
                class="
                    overflow-hidden
                    rounded-xl
                    border
                    border-gray-200
                    bg-white
                "
            >

                <div
                    class="
                        max-h-[650px]
                        overflow-auto
                    "
                >

                    <table
                        class="
                            min-w-full
                            border-collapse
                        "
                    >

                        <!-- ================================================= -->
                        <!-- Table Head -->
                        <!-- ================================================= -->

                        <thead
                            class="
                                sticky
                                top-0
                                z-20
                                bg-white
                            "
                        >

                            <tr
                                class="
                                    border-b
                                    border-gray-200
                                "
                            >

                                <!-- Checkbox -->

                                <th
                                    class="
                                        w-12
                                        px-4
                                        py-3
                                        text-left
                                    "
                                >

                                    <input
                                        type="checkbox"
                                        :checked="allVisibleSelected"
                                        :indeterminate="someVisibleSelected"
                                        class="
                                            h-4
                                            w-4
                                            rounded
                                            border-gray-300
                                            text-blue-600
                                            focus:ring-blue-500
                                        "
                                        @change="toggleAll"
                                    />

                                </th>


                                <!-- Product -->

                                <th
                                    class="
                                        px-4
                                        py-3
                                        text-left
                                        text-xs
                                        font-semibold
                                        uppercase
                                        tracking-wide
                                        text-gray-500
                                    "
                                >
                                    Product
                                </th>


                                <!-- Variant -->

                                <th
                                    class="
                                        px-4
                                        py-3
                                        text-left
                                        text-xs
                                        font-semibold
                                        uppercase
                                        tracking-wide
                                        text-gray-500
                                    "
                                >
                                    Variant
                                </th>


                                <!-- Barcode -->

                                <th
                                    class="
                                        px-4
                                        py-3
                                        text-left
                                        text-xs
                                        font-semibold
                                        uppercase
                                        tracking-wide
                                        text-gray-500
                                    "
                                >
                                    Barcode
                                </th>


                                <!-- Status -->

                                <th
                                    class="
                                        px-4
                                        py-3
                                        text-left
                                        text-xs
                                        font-semibold
                                        uppercase
                                        tracking-wide
                                        text-gray-500
                                    "
                                >
                                    Status
                                </th>


                                <!-- Actions -->

                                <th
                                    class="
                                        w-20
                                        px-4
                                        py-3
                                        text-right
                                        text-xs
                                        font-semibold
                                        uppercase
                                        tracking-wide
                                        text-gray-500
                                    "
                                >
                                    Actions
                                </th>

                            </tr>

                        </thead>


                        <!-- ================================================= -->
                        <!-- Table Body -->
                        <!-- ================================================= -->

                        <tbody>

                            <tr
                                v-for="variant in rows"
                                :key="variant.id"
                                :class="[
                                    'group transition-colors',
                                    isSelected(variant.id)
                                        ? 'bg-[#2563EB] text-white'
                                        : 'bg-white hover:bg-gray-50',
                                ]"
                            >

                                <!-- Checkbox -->

                                <td
                                    class="px-4 py-4"
                                >

                                    <input
                                        type="checkbox"
                                        :checked="isSelected(variant.id)"
                                        class="
                                            h-4
                                            w-4
                                            rounded
                                            border-gray-300
                                            text-blue-600
                                            focus:ring-blue-500
                                        "
                                        @change="toggleRow(variant.id)"
                                    />

                                </td>


                                <!-- Product -->

                                <td
                                    class="
                                        px-4
                                        py-4
                                    "
                                >

                                    <div
                                        class="
                                            flex
                                            items-center
                                            gap-3
                                        "
                                    >

                                        <!-- Product Image -->

                                        <div
                                            :class="[
                                                'flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-lg',
                                                isSelected(variant.id)
                                                    ? 'bg-white/20'
                                                    : 'bg-gray-100',
                                            ]"
                                        >

                                            <img
                                                v-if="productImage(variant)"
                                                :src="productImage(variant)"
                                                :alt="variant.product?.name ?? ''"
                                                class="
                                                    h-full
                                                    w-full
                                                    object-cover
                                                "
                                            />

                                            <PhotoIcon
                                                v-else
                                                :class="[
                                                    'h-5 w-5',
                                                    isSelected(variant.id)
                                                        ? 'text-white/80'
                                                        : 'text-gray-400',
                                                ]"
                                            />

                                        </div>


                                        <div
                                            class="
                                                min-w-0
                                            "
                                        >

                                            <div
                                                :class="[
                                                    'truncate text-sm font-medium',
                                                    isSelected(variant.id)
                                                        ? 'text-white'
                                                        : 'text-gray-900',
                                                ]"
                                            >
                                                {{ variant.product?.name ?? '-' }}
                                            </div>


                                            <div
                                                :class="[
                                                    'mt-0.5 text-xs',
                                                    isSelected(variant.id)
                                                        ? 'text-white/75'
                                                        : 'text-gray-500',
                                                ]"
                                            >
                                                {{ variant.sku ?? '-' }}
                                            </div>

                                        </div>

                                    </div>

                                </td>


                                <!-- Variant -->

                                <td
                                    class="
                                        px-4
                                        py-4
                                    "
                                >

                                    <div
                                        v-if="variantValues(variant).length"
                                        class="
                                            flex
                                            flex-wrap
                                            gap-1.5
                                        "
                                    >

                                        <span
                                            v-for="value in variantValues(variant)"
                                            :key="value.id"
                                            :class="[
                                                'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium',
                                                isSelected(variant.id)
                                                    ? 'bg-white/15 text-white'
                                                    : 'bg-gray-100 text-gray-700',
                                            ]"
                                        >
                                            {{ attributeName(value) }}:
                                            {{ attributeValue(value) }}
                                        </span>

                                    </div>

                                    <span
                                        v-else
                                        :class="[
                                            'text-sm',
                                            isSelected(variant.id)
                                                ? 'text-white/70'
                                                : 'text-gray-400',
                                        ]"
                                    >
                                        -
                                    </span>

                                </td>


                                <!-- Barcode -->

                                <td
                                    class="
                                        px-4
                                        py-4
                                    "
                                >

                                    <div
                                        v-if="hasBarcode(variant)"
                                        class="
                                            flex
                                            items-center
                                            gap-2
                                        "
                                    >

                                        <QrCodeIcon
                                            :class="[
                                                'h-5 w-5',
                                                isSelected(variant.id)
                                                    ? 'text-white'
                                                    : 'text-gray-500',
                                            ]"
                                        />

                                        <span
                                            :class="[
                                                'font-mono text-sm',
                                                isSelected(variant.id)
                                                    ? 'text-white'
                                                    : 'text-gray-700',
                                            ]"
                                        >
                                            {{ formatBarcode(variant.barcode) }}
                                        </span>

                                    </div>


                                    <span
                                        v-else
                                        :class="[
                                            'text-sm',
                                            isSelected(variant.id)
                                                ? 'text-white/70'
                                                : 'text-gray-400',
                                        ]"
                                    >
                                        No Barcode
                                    </span>

                                </td>


                                <!-- Status -->

                                <td
                                    class="
                                        px-4
                                        py-4
                                    "
                                >

                                 <StatusBadge
                                    :status="variant.is_active"
                                />

                                </td>


                                <!-- Actions -->

                                <td
                                    class="
                                        px-4
                                        py-4
                                        text-right
                                    "
                                >

                                    <div
                                        class="
                                            flex
                                            items-center
                                            justify-end
                                            gap-1
                                        "
                                    >

                                        <button
                                            type="button"
                                            :class="[
                                                'rounded-md p-2 transition',
                                                isSelected(variant.id)
                                                    ? 'text-white hover:bg-white/15'
                                                    : 'text-gray-500 hover:bg-gray-100 hover:text-gray-700',
                                            ]"
                                            title="View"
                                            @click="viewVariant(variant)"
                                        >
                                            <QrCodeIcon
                                                class="h-5 w-5"
                                            />
                                        </button>


                                        <button
                                            v-if="!hasBarcode(variant)"
                                            type="button"
                                            :class="[
                                                'rounded-md px-2 py-1.5 text-xs font-medium transition',
                                                isSelected(variant.id)
                                                    ? 'bg-white text-blue-600 hover:bg-blue-50'
                                                    : 'bg-blue-50 text-blue-700 hover:bg-blue-100',
                                            ]"
                                            @click="generateBarcode(variant)"
                                        >
                                            Generate
                                        </button>


                                        <button
                                            v-else
                                            type="button"
                                            :class="[
                                                'rounded-md p-2 transition',
                                                isSelected(variant.id)
                                                    ? 'text-white hover:bg-white/15'
                                                    : 'text-gray-500 hover:bg-gray-100 hover:text-gray-700',
                                            ]"
                                            title="Preview"
                                            @click="previewBarcode(variant)"
                                        >
                                            <QrCodeIcon
                                                class="h-5 w-5"
                                            />
                                        </button>

                                    </div>

                                </td>

                            </tr>


                            <!-- ================================================= -->
                            <!-- Empty -->
                            <!-- ================================================= -->

                            <tr
                                v-if="!rows.length"
                            >

                                <td
                                    colspan="6"
                                    class="
                                        px-6
                                        py-16
                                        text-center
                                    "
                                >

                                    <div
                                        class="
                                            flex
                                            flex-col
                                            items-center
                                            justify-center
                                        "
                                    >

                                        <QrCodeIcon
                                            class="
                                                h-10
                                                w-10
                                                text-gray-300
                                            "
                                        />

                                        <p
                                            class="
                                                mt-3
                                                text-sm
                                                font-medium
                                                text-gray-700
                                            "
                                        >
                                            No barcode data found.
                                        </p>

                                        <p
                                            class="
                                                mt-1
                                                text-sm
                                                text-gray-500
                                            "
                                        >
                                            Try changing your search or filters.
                                        </p>

                                    </div>

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>


                <!-- ========================================================= -->
                <!-- Bottom Border / Pagination -->
                <!-- ========================================================= -->

                <div
                    class="
                        border-t
                        border-black-500
                        bg-white
                        px-4
                        py-3
                    "
                >

                    <TablePagination
                        :data="variants"
                        label="Variants"
                    />
                </div>

            </div>

        </div>


        <!-- ============================================================= -->
        <!-- Loading -->
        <!-- ============================================================= -->

        <LoadingOverlay
            :show="isLoading"
        />

    </AppLayout>
<!-- ============================================================= -->
<!-- Generate Barcode Modal -->
<!-- ============================================================= -->

<Teleport to="body">

    <div
        v-if="showGenerateModal"
        class="
            fixed
            inset-0
            z-[100]
            flex
            items-center
            justify-center
            bg-black/40
            px-4
        "
        @click.self="closeGenerateModal"
    >

        <div
            class="
                w-full
                max-w-lg
                overflow-hidden
                rounded-xl
                bg-white
                shadow-2xl
            "
            role="dialog"
            aria-modal="true"
        >

            <!-- Header -->

            <div
                class="
                    flex
                    items-center
                    justify-between
                    border-b
                    border-gray-200
                    px-6
                    py-4
                "
            >

                <div>

                    <h2
                        class="
                            text-lg
                            font-semibold
                            text-gray-900
                        "
                    >
                        Generate Barcode
                    </h2>

                    <p
                        class="
                            mt-1
                            text-sm
                            text-gray-500
                        "
                    >
                        Generate barcode for this product variant.
                    </p>

                </div>


                <button
                    type="button"
                    class="
                        rounded-lg
                        p-2
                        text-gray-400
                        transition
                        hover:bg-gray-100
                        hover:text-gray-600
                    "
                    @click="closeGenerateModal"
                >

                    <span class="sr-only">
                        Close
                    </span>

                    ×

                </button>

            </div>


            <!-- Body -->

            <div
                class="
                    space-y-5
                    px-6
                    py-6
                "
            >

                <!-- Product -->

                <div>

                    <div
                        class="
                            text-xs
                            font-medium
                            uppercase
                            tracking-wide
                            text-gray-500
                        "
                    >
                        Product
                    </div>

                    <div
                        class="
                            mt-1
                            text-sm
                            font-medium
                            text-gray-900
                        "
                    >
                        {{ selectedVariant?.product?.name ?? '-' }}
                    </div>

                </div>


                <!-- Variant -->

                <div>

                    <div
                        class="
                            text-xs
                            font-medium
                            uppercase
                            tracking-wide
                            text-gray-500
                        "
                    >
                        Variant
                    </div>

                    <div
                        class="
                            mt-2
                            flex
                            flex-wrap
                            gap-1.5
                        "
                    >

                        <span
                            v-for="value in variantValues(selectedVariant)"
                            :key="value.id"
                            class="
                                inline-flex
                                items-center
                                rounded-md
                                bg-gray-100
                                px-2
                                py-1
                                text-xs
                                font-medium
                                text-gray-700
                            "
                        >
                            {{ attributeName(value) }}:
                            {{ attributeValue(value) }}
                        </span>

                        <span
                            v-if="
                                !variantValues(selectedVariant).length
                            "
                            class="
                                text-sm
                                text-gray-500
                            "
                        >
                            {{ selectedVariant?.name ?? '-' }}
                        </span>

                    </div>

                </div>


                <!-- SKU -->

                <div>

                    <div
                        class="
                            text-xs
                            font-medium
                            uppercase
                            tracking-wide
                            text-gray-500
                        "
                    >
                        SKU
                    </div>

                    <div
                        class="
                            mt-1
                            font-mono
                            text-sm
                            text-gray-900
                        "
                    >
                        {{ selectedVariant?.sku ?? '-' }}
                    </div>

                </div>


                <!-- Information -->

                <div
                    class="
                        rounded-lg
                        bg-blue-50
                        px-4
                        py-3
                        text-sm
                        text-blue-700
                    "
                >
                    Barcode akan dibuat secara otomatis
                    berdasarkan Product Variant.
                </div>

            </div>


            <!-- Footer -->

            <div
                class="
                    flex
                    items-center
                    justify-end
                    gap-2
                    border-t
                    border-gray-200
                    px-6
                    py-4
                "
            >

                <BaseButton
                    variant="secondary"
                    @click="closeGenerateModal"
                >
                    Cancel
                </BaseButton>


                <BaseButton
                    @click="submitGenerateBarcode"
                >
                    Generate Barcode
                </BaseButton>

            </div>

        </div>

    </div>

</Teleport>
<!-- ============================================================= -->
<!-- Preview Barcode Modal -->
<!-- ============================================================= -->

<Teleport to="body">

    <div
        v-if="showPreviewModal"
        class="
            fixed
            inset-0
            z-[100]
            flex
            items-center
            justify-center
            bg-black/40
            px-4
            py-6
        "
        @click.self="closePreviewModal"
    >

        <div
            class="
                flex
                max-h-[90vh]
                w-full
                max-w-3xl
                flex-col
                overflow-hidden
                rounded-xl
                bg-white
                shadow-2xl
            "
            role="dialog"
            aria-modal="true"
        >

            <!-- ================================================= -->
            <!-- Header -->
            <!-- ================================================= -->

            <div
                class="
                    flex
                    shrink-0
                    items-center
                    justify-between
                    border-b
                    border-gray-200
                    px-6
                    py-4
                "
            >

                <div>

                    <h2
                        class="
                            text-lg
                            font-semibold
                            text-gray-900
                        "
                    >
                        Preview Barcode
                    </h2>

                    <p
                        class="
                            mt-1
                            text-sm
                            text-gray-500
                        "
                    >
                        Preview product variant barcode before printing.
                    </p>

                </div>


                <button
                    type="button"
                    class="
                        rounded-lg
                        p-2
                        text-gray-400
                        transition
                        hover:bg-gray-100
                        hover:text-gray-600
                    "
                    @click="closePreviewModal"
                >

                    <span class="sr-only">
                        Close
                    </span>

                    ×

                </button>

            </div>


            <!-- ================================================= -->
            <!-- Body -->
            <!-- ================================================= -->

            <div
                class="
                    min-h-0
                    flex-1
                    overflow-y-auto
                    px-6
                    py-6
                "
            >

                <div
                    class="
                        grid
                        gap-6
                        lg:grid-cols-[260px_minmax(0,1fr)]
                    "
                >

                    <!-- ========================================= -->
                    <!-- Information -->
                    <!-- ========================================= -->

                    <div
                        class="
                            flex
                            flex-col
                            justify-between
                            gap-6
                        "
                    >

                        <div class="space-y-5">

                            <!-- Product -->

                            <div>

                                <div
                                    class="
                                        text-xs
                                        font-medium
                                        uppercase
                                        tracking-wide
                                        text-gray-500
                                    "
                                >
                                    Product
                                </div>

                                <div
                                    class="
                                        mt-1
                                        text-sm
                                        font-medium
                                        leading-5
                                        text-gray-900
                                    "
                                >
                                    {{ previewData?.product ?? '-' }}
                                </div>

                            </div>


                            <!-- Variant -->

                            <div>

                                <div
                                    class="
                                        text-xs
                                        font-medium
                                        uppercase
                                        tracking-wide
                                        text-gray-500
                                    "
                                >
                                    Variant
                                </div>

                                <div
                                    class="
                                        mt-1
                                        text-sm
                                        leading-5
                                        text-gray-700
                                    "
                                >
                                    {{ previewData?.variant ?? '-' }}
                                </div>

                            </div>


                            <!-- SKU -->

                            <div>

                                <div
                                    class="
                                        text-xs
                                        font-medium
                                        uppercase
                                        tracking-wide
                                        text-gray-500
                                    "
                                >
                                    SKU
                                </div>

                                <div
                                    class="
                                        mt-1
                                        font-mono
                                        text-sm
                                        text-gray-700
                                    "
                                >
                                    {{ previewData?.sku ?? '-' }}
                                </div>

                            </div>


                            <!-- Barcode -->

                            <div>

                                <div
                                    class="
                                        text-xs
                                        font-medium
                                        uppercase
                                        tracking-wide
                                        text-gray-500
                                    "
                                >
                                    Barcode
                                </div>

                                <div
                                    class="
                                        mt-1
                                        font-mono
                                        text-sm
                                        font-semibold
                                        text-gray-900
                                    "
                                >
                                    {{ previewData?.barcode ?? '-' }}
                                </div>

                            </div>

                        </div>


                        <!-- ===================================== -->
                        <!-- Print Copies -->
                        <!-- ===================================== -->

                        <div
                            class="
                                border-t
                                border-gray-100
                                pt-5
                            "
                        >

                            <label
                                class="
                                    text-xs
                                    font-medium
                                    uppercase
                                    tracking-wide
                                    text-gray-500
                                "
                            >
                                Copies
                            </label>

                            <div
                                class="
                                    mt-2
                                    flex
                                    items-center
                                    gap-2
                                "
                            >

                                <input
                                    v-model.number="printCopies"
                                    type="number"
                                    min="1"
                                    max="10000"
                                    class="
                                        w-28
                                        rounded-lg
                                        border
                                        border-gray-300
                                        bg-white
                                        px-3
                                        py-2
                                        text-sm
                                        text-gray-900
                                        outline-none
                                        transition
                                        focus:border-blue-500
                                        focus:ring-2
                                        focus:ring-blue-100
                                    "
                                />

                                <span
                                    class="
                                        text-sm
                                        text-gray-500
                                    "
                                >
                                    labels
                                </span>

                            </div>

                            <p
                                class="
                                    mt-1.5
                                    text-xs
                                    leading-5
                                    text-gray-500
                                "
                            >
                                Print multiple labels in one print job.
                            </p>

                        </div>

                    </div>


                    <!-- ========================================= -->
                    <!-- Barcode Preview -->
                    <!-- ========================================= -->

                    <div
                        class="
                            flex
                            min-h-[280px]
                            items-center
                            justify-center
                            rounded-xl
                            border
                            border-gray-200
                            bg-gray-50
                            p-5
                            sm:p-6
                        "
                    >

                        <div
                            v-if="previewData?.svg"
                            class="
                                flex
                                w-full
                                flex-col
                                items-center
                                justify-center
                            "
                        >

                            <div
                                class="
                                    barcode-preview
                                    w-full
                                    max-w-xl
                                    overflow-hidden
                                "
                                v-html="previewData.svg"
                            />

                            <div
                                class="
                                    mt-4
                                    font-mono
                                    text-sm
                                    font-semibold
                                    tracking-wider
                                    text-gray-700
                                "
                            >
                                {{ previewData.barcode }}
                            </div>

                        </div>


                        <div
                            v-else
                            class="
                                text-sm
                                text-gray-500
                            "
                        >
                            Barcode preview unavailable.
                        </div>

                    </div>

                </div>

            </div>


            <!-- ================================================= -->
            <!-- Footer -->
            <!-- ================================================= -->

            <div
                class="
                    flex
                    shrink-0
                    flex-col-reverse
                    gap-2
                    border-t
                    border-gray-200
                    px-6
                    py-4
                    sm:flex-row
                    sm:items-center
                    sm:justify-end
                "
            >

                <BaseButton
                    variant="secondary"
                    @click="closePreviewModal"
                >
                    Close
                </BaseButton>


                <BaseButton
                    :disabled="!previewData?.barcode"
                    @click="printLabel(previewData)"
                >
                    Print {{ printCopies }} Labels
                </BaseButton>

            </div>

        </div>

    </div>

</Teleport>



<!-- ============================================================= -->
<!-- View Product Variant Modal -->
<!-- ============================================================= -->

<Teleport to="body">

    <div
        v-if="showViewModal"
        class="
            fixed
            inset-0
            z-[100]
            flex
            items-center
            justify-center
            bg-black/40
            px-4
            py-6
        "
        @click.self="closeViewModal"
    >

        <div
            class="
                flex
                max-h-[90vh]
                w-full
                max-w-2xl
                flex-col
                overflow-hidden
                rounded-xl
                bg-white
                shadow-2xl
            "
            role="dialog"
            aria-modal="true"
        >

            <!-- ===================================================== -->
            <!-- Header -->
            <!-- ===================================================== -->

            <div
                class="
                    flex
                    shrink-0
                    items-center
                    justify-between
                    border-b
                    border-gray-200
                    px-6
                    py-4
                "
            >

                <div>

                    <h2
                        class="
                            text-lg
                            font-semibold
                            text-gray-900
                        "
                    >
                        Product Variant
                    </h2>

                    <p
                        class="
                            mt-1
                            text-sm
                            text-gray-500
                        "
                    >
                        View product variant and barcode information.
                    </p>

                </div>


                <button
                    type="button"
                    class="
                        rounded-lg
                        p-2
                        text-gray-400
                        transition
                        hover:bg-gray-100
                        hover:text-gray-600
                    "
                    @click="closeViewModal"
                >

                    <span class="sr-only">
                        Close
                    </span>

                    ×

                </button>

            </div>


            <!-- ===================================================== -->
            <!-- Body -->
            <!-- ===================================================== -->

            <div
                class="
                    min-h-0
                    flex-1
                    overflow-y-auto
                    px-6
                    py-6
                "
            >

                <div
                    class="
                        space-y-6
                    "
                >

                    <!-- ================================================= -->
                    <!-- Product -->
                    <!-- ================================================= -->

                    <div
                        class="
                            flex
                            items-center
                            gap-4
                        "
                    >

                        <div
                            class="
                                flex
                                h-16
                                w-16
                                shrink-0
                                items-center
                                justify-center
                                overflow-hidden
                                rounded-xl
                                bg-gray-100
                            "
                        >

                            <img
                                v-if="productImage(viewData)"
                                :src="productImage(viewData)"
                                :alt="viewData?.product?.name ?? ''"
                                class="
                                    h-full
                                    w-full
                                    object-cover
                                "
                            />

                            <PhotoIcon
                                v-else
                                class="
                                    h-7
                                    w-7
                                    text-gray-400
                                "
                            />

                        </div>


                        <div
                            class="
                                min-w-0
                            "
                        >

                            <div
                                class="
                                    text-base
                                    font-semibold
                                    text-gray-900
                                "
                            >
                                {{ viewData?.product?.name ?? '-' }}
                            </div>

                            <div
                                class="
                                    mt-1
                                    text-sm
                                    text-gray-500
                                "
                            >
                                {{ viewData?.product?.code ?? '-' }}
                            </div>

                        </div>

                    </div>


                    <!-- ================================================= -->
                    <!-- Information -->
                    <!-- ================================================= -->

                    <div
                        class="
                            grid
                            gap-5
                            sm:grid-cols-2
                        "
                    >

                        <!-- Variant -->

                        <div>

                            <div
                                class="
                                    text-xs
                                    font-medium
                                    uppercase
                                    tracking-wide
                                    text-gray-500
                                "
                            >
                                Variant
                            </div>

                            <div
                                class="
                                    mt-2
                                    flex
                                    flex-wrap
                                    gap-1.5
                                "
                            >

                                <span
                                    v-for="value in variantValues(viewData)"
                                    :key="value.id"
                                    class="
                                        inline-flex
                                        items-center
                                        rounded-md
                                        bg-gray-100
                                        px-2
                                        py-1
                                        text-xs
                                        font-medium
                                        text-gray-700
                                    "
                                >
                                    {{ attributeName(value) }}:
                                    {{ attributeValue(value) }}
                                </span>

                                <span
                                    v-if="
                                        !variantValues(viewData).length
                                    "
                                    class="
                                        text-sm
                                        text-gray-700
                                    "
                                >
                                    {{ viewData?.name ?? '-' }}
                                </span>

                            </div>

                        </div>


                        <!-- SKU -->

                        <div>

                            <div
                                class="
                                    text-xs
                                    font-medium
                                    uppercase
                                    tracking-wide
                                    text-gray-500
                                "
                            >
                                SKU
                            </div>

                            <div
                                class="
                                    mt-2
                                    font-mono
                                    text-sm
                                    text-gray-900
                                "
                            >
                                {{ viewData?.sku ?? '-' }}
                            </div>

                        </div>


                        <!-- Barcode -->

                        <div>

                            <div
                                class="
                                    text-xs
                                    font-medium
                                    uppercase
                                    tracking-wide
                                    text-gray-500
                                "
                            >
                                Barcode
                            </div>

                            <div
                                class="
                                    mt-2
                                    font-mono
                                    text-sm
                                    font-semibold
                                    text-gray-900
                                "
                            >
                                {{ viewData?.barcode || 'No Barcode' }}
                            </div>

                        </div>


                        <!-- Status -->

                        <div>

                            <div
                                class="
                                    text-xs
                                    font-medium
                                    uppercase
                                    tracking-wide
                                    text-gray-500
                                "
                            >
                                Status
                            </div>

                            <div
                                class="
                                    mt-2
                                "
                            >

                                <StatusBadge
                                    :status="viewData?.is_active"
                                />

                            </div>

                        </div>

                    </div>


                    <!-- ================================================= -->
                    <!-- Dates -->
                    <!-- ================================================= -->

                    <div
                        class="
                            grid
                            gap-5
                            border-t
                            border-gray-100
                            pt-5
                            sm:grid-cols-2
                        "
                    >

                        <div>

                            <div
                                class="
                                    text-xs
                                    font-medium
                                    uppercase
                                    tracking-wide
                                    text-gray-500
                                "
                            >
                                Created
                            </div>

                            <div
                                class="
                                    mt-1
                                    text-sm
                                    text-gray-700
                                "
                            >
                                {{ viewData?.created_at_human ?? '-' }}
                            </div>

                        </div>


                        <div>

                            <div
                                class="
                                    text-xs
                                    font-medium
                                    uppercase
                                    tracking-wide
                                    text-gray-500
                                "
                            >
                                ID
                            </div>

                            <div
                                class="
                                    mt-1
                                    font-mono
                                    text-sm
                                    text-gray-700
                                "
                            >
                                #{{ viewData?.id ?? '-' }}
                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- ===================================================== -->
            <!-- Footer -->
            <!-- ===================================================== -->

            <div
                class="
                    flex
                    shrink-0
                    items-center
                    justify-end
                    border-t
                    border-gray-200
                    px-6
                    py-4
                "
            >

                <BaseButton
                    variant="secondary"
                    @click="closeViewModal"
                >
                    Close
                </BaseButton>

            </div>

        </div>

    </div>

</Teleport>
</template>
<style scoped>

.barcode-preview :deep(svg) {
    display: block;
    width: 100% !important;
    max-width: 100% !important;
    height: auto !important;
}

</style>