<script setup>
import { computed, nextTick, ref, watch } from 'vue'

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },

    products: {
        type: Array,
        default: () => [],
    },

    search: {
        type: String,
        default: '',
    },
})

const emit = defineEmits([
    'close',
    'select',
])

const searchInput = ref(null)

const localSearch = ref(props.search ?? '')

watch(
    () => props.search,
    (value) => {
        localSearch.value = value ?? ''
    }
)

watch(
    () => props.show,
    async (show) => {
        if (show) {
            await nextTick()
            searchInput.value?.focus()
        }
    }
)

const filteredProducts = computed(() => {
    const keyword = localSearch.value
        .trim()
        .toLowerCase()

    if (!keyword) {
        return props.products
    }

    return props.products.filter((product) => {
        const values = [
            product.product_name,
            product.name,
            product.sku,
            product.barcode,
        ]

        const variants = product.variants ?? []

        const variantMatch = variants.some((variant) => {
            return [
                variant.name,
                variant.sku,
                variant.barcode,
            ]
                .filter(Boolean)
                .some((value) =>
                    String(value)
                        .toLowerCase()
                        .includes(keyword)
                )
        })

        return (
            values
                .filter(Boolean)
                .some((value) =>
                    String(value)
                        .toLowerCase()
                        .includes(keyword)
                ) ||
            variantMatch
        )
    })
})

const productName = (product) => {
    return (
        product.product_name ??
        product.name ??
        '-'
    )
}

const productSku = (product) => {
    return (
        product.sku ??
        product.barcode ??
        '-'
    )
}

const productPrice = (product) => {
    if (product.selling_price !== undefined) {
        return product.selling_price
    }

    if (product.price !== undefined) {
        return product.price
    }

    if (product.variants?.length) {
        return (
            product.variants[0]?.selling_price ??
            product.variants[0]?.price ??
            0
        )
    }

    return 0
}

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(Number(value ?? 0))
}

const selectProduct = (product) => {
    emit('select', product)
}

const close = () => {
    emit('close')
}
</script>

<template>
    <Teleport to="body">
        <div
            v-if="show"
            class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 p-4"
            @click.self="close"
        >
            <div
                class="flex max-h-[85vh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl"
            >
                <!-- Header -->
                <div
                    class="flex items-center justify-between border-b px-5 py-4"
                >
                    <div>
                        <h2
                            class="text-lg font-semibold text-gray-900"
                        >
                            Pilih Produk
                        </h2>

                        <p
                            class="mt-0.5 text-sm text-gray-500"
                        >
                            Cari produk berdasarkan nama, SKU, atau barcode
                        </p>
                    </div>

                    <button
                        type="button"
                        class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700"
                        @click="close"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>

                <!-- Search -->
                <div class="border-b bg-gray-50 px-5 py-4">
                    <div class="relative">
                        <div
                            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5 text-gray-400"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"
                                />
                            </svg>
                        </div>

                        <input
                            ref="searchInput"
                            v-model="localSearch"
                            type="text"
                            autocomplete="off"
                            placeholder="Cari nama produk / SKU / barcode..."
                            class="w-full rounded-xl border border-gray-300 bg-white py-3 pl-10 pr-4 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                        />
                    </div>
                </div>

                <!-- Product List -->
                <div class="min-h-0 flex-1 overflow-y-auto">
                    <div
                        v-if="filteredProducts.length"
                        class="divide-y"
                    >
                        <button
                            v-for="product in filteredProducts"
                            :key="product.id"
                            type="button"
                            class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left transition hover:bg-blue-50"
                            @click="selectProduct(product)"
                        >
                            <div class="min-w-0 flex-1">
                                <div
                                    class="truncate font-medium text-gray-900"
                                >
                                    {{ productName(product) }}
                                </div>

                                <div
                                    class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500"
                                >
                                    <span>
                                        SKU:
                                        {{ productSku(product) }}
                                    </span>

                                    <span
                                        v-if="product.variants?.length"
                                    >
                                        {{
                                            product.variants.length
                                        }}
                                        variant
                                    </span>
                                </div>
                            </div>

                            <div
                                class="shrink-0 text-right"
                            >
                                <div
                                    class="font-semibold text-blue-600"
                                >
                                    {{ formatCurrency(productPrice(product)) }}
                                </div>

                                <div
                                    class="mt-1 text-xs text-gray-400"
                                >
                                    Pilih
                                </div>
                            </div>
                        </button>
                    </div>

                    <!-- Empty -->
                    <div
                        v-else
                        class="flex min-h-[280px] items-center justify-center px-5"
                    >
                        <div class="text-center">
                            <div
                                class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6 text-gray-400"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"
                                    />
                                </svg>
                            </div>

                            <p
                                class="font-medium text-gray-700"
                            >
                                Produk tidak ditemukan
                            </p>

                            <p
                                class="mt-1 text-sm text-gray-500"
                            >
                                Coba gunakan nama produk, SKU, atau barcode lain.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div
                    class="flex items-center justify-between border-t bg-gray-50 px-5 py-3"
                >
                    <span
                        class="text-sm text-gray-500"
                    >
                        {{ filteredProducts.length }} produk
                    </span>

                    <button
                        type="button"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100"
                        @click="close"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>