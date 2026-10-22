<script setup>
import { computed, nextTick, ref, watch } from 'vue'

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    stocks: {
        type: Array,
        default: () => [],
    },
})

const emit = defineEmits([
    'close',
    'select',
])

const search = ref('')
const searchInput = ref(null)

const getProductName = (stock) => {
    const variant = stock.variant ?? {}
    const product = variant.product ?? {}

    return product.name ?? variant.name ?? 'Unnamed Product'
}

const getVariantName = (stock) => {
    return stock.variant?.name ?? ''
}

const getSku = (stock) => {
    return stock.variant?.sku ?? '-'
}

const getBarcode = (stock) => {
    return stock.variant?.barcode ?? ''
}

const getUnitName = (stock) => {
    return stock.unit?.name ?? '-'
}

const getAvailableQty = (stock) => {
    return Number(
        stock.available_qty ??
        stock.on_hand_qty ??
        0
    )
}

const normalize = (value) => {
    return String(value ?? '').trim().toLowerCase()
}

const filteredStocks = computed(() => {
    const keyword = normalize(search.value)

    return props.stocks.filter((stock) => {
        if (getAvailableQty(stock) <= 0) {
            return false
        }

        if (!keyword) {
            return true
        }

        const searchableText = [
            getProductName(stock),
            getVariantName(stock),
            getSku(stock),
            getBarcode(stock),
        ]
            .map(normalize)
            .join(' ')

        return searchableText.includes(keyword)
    })
})

const formatQty = (value) => {
    return new Intl.NumberFormat('id-ID', {
        maximumFractionDigits: 4,
    }).format(Number(value ?? 0))
}

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(Number(value ?? 0))
}

const selectStock = (stock) => {
    if (getAvailableQty(stock) <= 0) {
        return
    }

    emit('select', stock)
    search.value = ''
}

const handleEnter = () => {
    const keyword = normalize(search.value)

    if (!keyword) {
        return
    }

    // Barcode atau SKU yang cocok persis dipilih langsung.
    const exactMatch = filteredStocks.value.find((stock) => {
        return (
            normalize(getBarcode(stock)) === keyword ||
            normalize(getSku(stock)) === keyword
        )
    })

    if (exactMatch) {
        selectStock(exactMatch)
        return
    }

    // Jika hanya ada satu hasil, pilih hasil tersebut.
    if (filteredStocks.value.length === 1) {
        selectStock(filteredStocks.value[0])
    }
}

const close = () => {
    search.value = ''
    emit('close')
}

watch(
    () => props.show,
    async (visible) => {
        if (visible) {
            search.value = ''

            await nextTick()

            searchInput.value?.focus()
        }
    }
)
</script>

<template>
    <Teleport to="body">
        <div
            v-if="show"
            class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/50 p-3 sm:p-6"
            @click.self="close"
            @keydown.esc.stop="close"
        >
            <div
                class="flex max-h-[85vh] w-full max-w-4xl flex-col overflow-hidden rounded-xl bg-white shadow-2xl"
                role="dialog"
                aria-modal="true"
                aria-label="Search products"
            >
                <!-- Header -->
                <div
                    class="flex items-center justify-between border-b border-gray-200 px-4 py-4 sm:px-6"
                >
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">
                            Search Products
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            Search by product name, SKU, or barcode.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="rounded-lg px-3 py-2 text-gray-500 hover:bg-gray-100 hover:text-gray-800"
                        aria-label="Close product search"
                        @click="close"
                    >
                        ✕
                    </button>
                </div>

                <!-- Search -->
                <div class="border-b border-gray-200 p-4 sm:px-6">
                    <input
                        ref="searchInput"
                        v-model="search"
                        type="text"
                        autocomplete="off"
                        placeholder="Search product name, barcode, or SKU..."
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                        @keydown.enter.prevent="handleEnter"
                    />

                    <div class="mt-2 text-xs text-gray-500">
                        {{ filteredStocks.length }} product
                        {{ filteredStocks.length === 1 ? 'found' : 'found' }}
                    </div>
                </div>

                <!-- Results -->
                <div class="min-h-0 flex-1 overflow-auto">
                    <div
                        v-if="!stocks.length"
                        class="px-6 py-12 text-center"
                    >
                        <p class="font-medium text-gray-700">
                            No stock available
                        </p>

                        <p class="mt-1 text-sm text-gray-500">
                            Select a source warehouse first.
                        </p>
                    </div>

                    <div
                        v-else-if="!filteredStocks.length"
                        class="px-6 py-12 text-center"
                    >
                        <p class="font-medium text-gray-700">
                            No product found
                        </p>

                        <p class="mt-1 text-sm text-gray-500">
                            Try another product name, SKU, or barcode.
                        </p>
                    </div>

                    <table
                        v-else
                        class="w-full min-w-[650px] text-left text-sm"
                    >
                        <thead class="sticky top-0 bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-4 py-3 sm:px-6">
                                    Product
                                </th>

                                <th class="px-4 py-3">
                                    SKU / Barcode
                                </th>

                                <th class="px-4 py-3">
                                    Unit
                                </th>

                                <th class="px-4 py-3 text-right">
                                    Available
                                </th>

                                <th class="px-4 py-3 text-right sm:pr-6">
                                    Unit Cost
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            <tr
                                v-for="stock in filteredStocks"
                                :key="`${stock.product_variant_id}-${stock.unit_id}`"
                                class="cursor-pointer transition hover:bg-blue-50"
                                @click="selectStock(stock)"
                            >
                                <td class="px-4 py-4 sm:px-6">
                                    <div class="font-medium text-gray-900">
                                        {{ getProductName(stock) }}
                                    </div>

                                    <div
                                        v-if="getVariantName(stock) && getVariantName(stock) !== getProductName(stock)"
                                        class="mt-1 text-xs text-gray-500"
                                    >
                                        {{ getVariantName(stock) }}
                                    </div>
                                </td>

                                <td class="px-4 py-4">
                                    <div class="text-gray-700">
                                        {{ getSku(stock) }}
                                    </div>

                                    <div
                                        v-if="getBarcode(stock)"
                                        class="mt-1 text-xs text-gray-500"
                                    >
                                        {{ getBarcode(stock) }}
                                    </div>
                                </td>

                                <td class="px-4 py-4 text-gray-700">
                                    {{ getUnitName(stock) }}
                                </td>

                                <td class="px-4 py-4 text-right font-medium text-gray-900">
                                    {{ formatQty(getAvailableQty(stock)) }}
                                </td>

                                <td class="px-4 py-4 text-right text-gray-700 sm:pr-6">
                                    {{ formatCurrency(stock.average_cost) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer -->
                <div
                    class="flex justify-end border-t border-gray-200 bg-gray-50 px-4 py-3 sm:px-6"
                >
                    <button
                        type="button"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100"
                        @click="close"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>