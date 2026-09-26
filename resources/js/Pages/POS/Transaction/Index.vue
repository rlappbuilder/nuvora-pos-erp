<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import VariantModal from './VariantModal.vue'
const page = usePage()

const props = defineProps({
    activeSession: {
        type: Object,
        default: null,
    },

    customers: {
        type: Array,
        default: () => [],
    },

    priceTypes: {
        type: Array,
        default: () => [],
    },

    products: {
        type: Array,
        default: () => [],
    },
})

/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const searchInput = ref(null)
const search = ref('')
const selectedProduct = ref(null)

const selectedCustomer = ref(null)

const selectedPriceType = ref(
    props.priceTypes?.[0]?.id ?? ''
)

const cart = ref([])

const discountModalItem = ref(null)
const showCustomerModal = ref(false)
const showPaymentModal = ref(false)

const processing = ref(false)

const currentTime = ref(new Date())
const showVariantModal = ref(false)

const selectedProductForVariant = ref(null)
let clockTimer = null

/*
|--------------------------------------------------------------------------
| Session
|--------------------------------------------------------------------------
*/

const authUser = computed(() => {
    return page.props.auth?.user ?? null
})

const currentBranch = computed(() => {
    return page.props.auth?.current_branch ?? null
})

const activeSession = computed(() => {
    return props.activeSession ?? null
})

const sessionOpen = computed(() => {
    return activeSession.value?.status === 'open'
})

/*
|--------------------------------------------------------------------------
| Date / Time
|--------------------------------------------------------------------------
*/

const formattedDate = computed(() => {
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    }).format(currentTime.value)
})

const formattedTime = computed(() => {
    return new Intl.DateTimeFormat('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
    }).format(currentTime.value)
})

/*
|--------------------------------------------------------------------------
| Currency
|--------------------------------------------------------------------------
*/

const formatCurrency = (value) => {
    const number = Number(value ?? 0)

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(number)
}

/*
|--------------------------------------------------------------------------
| Product Initials
|--------------------------------------------------------------------------
*/

const getInitials = (name) => {
    if (!name) {
        return '?'
    }

    const words = String(name)
        .trim()
        .split(/\s+/)
        .filter(Boolean)

    if (words.length === 1) {
        return words[0]
            .substring(0, 2)
            .toUpperCase()
    }

    return (
        words[0].charAt(0) +
        words[1].charAt(0)
    ).toUpperCase()
}

/*
|--------------------------------------------------------------------------
| Product Image
|--------------------------------------------------------------------------
*/

const getProductImage = (product) => {
    return (
        product?.primary_image_url ??
        product?.primary_image ??
        product?.image_url ??
        product?.image ??
        null
    )
}

/*
|--------------------------------------------------------------------------
| Product Search
|--------------------------------------------------------------------------
*/

const filteredProducts = computed(() => {
    const keyword = search.value
        .trim()
        .toLowerCase()

    if (!keyword) {
        return props.products
    }

    return props.products.filter((product) => {
        const values = [
            product.name,
            product.sku,
            product.barcode,
            product.variant_name,
            product.variant_sku,
        ]

        return values.some((value) =>
            String(value ?? '')
                .toLowerCase()
                .includes(keyword)
        )
    })
})

/*
|--------------------------------------------------------------------------
| Cart
|--------------------------------------------------------------------------
*/

const cartCount = computed(() => {
    return cart.value.reduce(
        (total, item) => total + Number(item.qty),
        0
    )
})

const subtotal = computed(() => {
    return cart.value.reduce(
        (total, item) => total + Number(item.subtotal),
        0
    )
})

const discountTotal = computed(() => {
    return cart.value.reduce(
        (total, item) => total + Number(item.discount_amount),
        0
    )
})

const grandTotal = computed(() => {
    return Math.max(
        0,
        subtotal.value
    )
})

const addProduct = (product) => {
    if (!sessionOpen.value) {
        return
    }

    const variants = Array.isArray(product.variants)
        ? product.variants
        : []

    if (variants.length > 1) {
        selectedProductForVariant.value = product
        showVariantModal.value = true
        return
    }

    const variant = variants[0] ?? product

    addVariantToCart(product, variant)
}
/*
|--------------------------------------------------------------------------
| Add Variant To Cart
|--------------------------------------------------------------------------
*/

const addVariantToCart = (
    product,
    variant
) => {

    /*
    |--------------------------------------------------------------------------
    | Variant Identity
    |--------------------------------------------------------------------------
    */

    const variantId =
        variant.variant_id ??
        variant.product_variant_id ??
        variant.id

    const unitId =
        variant.unit_id ??
        product.unit_id ??
        product.default_unit_id

    const priceTypeId =
        variant.price_type_id ??
        product.price_type_id ??
        selectedPriceType.value


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if (
        !variantId ||
        !unitId ||
        !priceTypeId
    ) {
        return
    }


    /*
    |--------------------------------------------------------------------------
    | Existing Cart Item
    |--------------------------------------------------------------------------
    */

    const existing = cart.value.find(
        (item) => {

            return (
                Number(
                    item.product_variant_id
                ) === Number(variantId)

                &&

                Number(
                    item.unit_id
                ) === Number(unitId)
            )

        }
    )


    /*
    |--------------------------------------------------------------------------
    | Existing Item
    |--------------------------------------------------------------------------
    */

    if (existing) {

        existing.qty += 1

        recalculateItem(
            existing
        )

        selectedProduct.value =
            existing

    }


    /*
    |--------------------------------------------------------------------------
    | New Item
    |--------------------------------------------------------------------------
    */

    else {

        const unitPrice =
            Number(
                variant.selling_price ??
                variant.price ??
                product.selling_price ??
                product.price ??
                0
            )


        /*
        |--------------------------------------------------------------------------
        | Display Names
        |--------------------------------------------------------------------------
        */

        const productName =
            product.product_name ??
            product.name ??
            ''


        const variantName =
            variant.variant_name ??
            variant.name ??
            ''


        /*
        |--------------------------------------------------------------------------
        | Default Variant Handling
        |--------------------------------------------------------------------------
        */

        const displayVariantName =
            variantName &&
            variantName !== 'Default' &&
            variantName !== productName
                ? variantName
                : null


        /*
        |--------------------------------------------------------------------------
        | Cart Item
        |--------------------------------------------------------------------------
        */

        const item = {

            product_variant_id:
                variantId,

            unit_id:
                unitId,

            price_type_id:
                priceTypeId,


            /*
            |--------------------------------------------------------------------------
            | Product
            |--------------------------------------------------------------------------
            */

            product_name:
                productName,


            /*
            |--------------------------------------------------------------------------
            | Variant
            |--------------------------------------------------------------------------
            */

            variant_name:
                displayVariantName,


            /*
            |--------------------------------------------------------------------------
            | Identification
            |--------------------------------------------------------------------------
            */

            sku:
                variant.variant_sku ??
                variant.sku ??
                product.sku ??
                '',

            barcode:
                variant.barcode ??
                product.barcode ??
                '',


            /*
            |--------------------------------------------------------------------------
            | Unit
            |--------------------------------------------------------------------------
            */

            unit_name:
                variant.unit_name ??
                product.unit_name ??
                '',


            /*
            |--------------------------------------------------------------------------
            | Image
            |--------------------------------------------------------------------------
            */

            image:
                variant.image ??
                product.image ??
                getProductImage(product),


            /*
            |--------------------------------------------------------------------------
            | Quantity
            |--------------------------------------------------------------------------
            */

            qty:
                1,


            /*
            |--------------------------------------------------------------------------
            | Price
            |--------------------------------------------------------------------------
            */

            unit_price:
                unitPrice,


            /*
            |--------------------------------------------------------------------------
            | Discount
            |--------------------------------------------------------------------------
            */

            discount_type:
                'percentage',

            discount_value:
                0,

            discount_amount:
                0,


            /*
            |--------------------------------------------------------------------------
            | Subtotal
            |--------------------------------------------------------------------------
            */

            subtotal:
                unitPrice,

        }


        cart.value.push(
            item
        )

        selectedProduct.value =
            item
    }


    /*
    |--------------------------------------------------------------------------
    | Close Variant Modal
    |--------------------------------------------------------------------------
    */

    showVariantModal.value =
        false

    selectedProductForVariant.value =
        null


    /*
    |--------------------------------------------------------------------------
    | Reset Search
    |--------------------------------------------------------------------------
    */

    search.value = ''


    /*
    |--------------------------------------------------------------------------
    | Focus Search
    |--------------------------------------------------------------------------
    */

    nextTick(() => {

        searchInput.value?.focus()

    })
}
   const selectVariant = (variant) => {
    const product = selectedProductForVariant.value

    if (!product || !variant) {
        return
    }

    addVariantToCart(product, variant)
}
const closeVariantModal = () => {

    showVariantModal.value = false

    selectedProductForVariant.value = null

    nextTick(() => {
        searchInput.value?.focus()
    })
}
/*
|--------------------------------------------------------------------------
| Recalculate Item
|--------------------------------------------------------------------------
*/

const recalculateItem = (item) => {
    const gross =
        Number(item.qty) *
        Number(item.unit_price)

    let discount = 0

    if (item.discount_type === 'percentage') {
        discount =
            gross *
            (Number(item.discount_value) / 100)
    } else {
        discount =
            Number(item.discount_value)
    }

    discount = Math.min(
        Math.max(0, discount),
        gross
    )

    item.discount_amount = discount
    item.subtotal = Math.max(
        0,
        gross - discount
    )
}

/*
|--------------------------------------------------------------------------
| Cart Quantity
|--------------------------------------------------------------------------
*/

const increaseQty = (item) => {
    item.qty += 1

    recalculateItem(item)
}

const decreaseQty = (item) => {
    if (item.qty <= 1) {
        removeItem(item)
        return
    }

    item.qty -= 1

    recalculateItem(item)
}

const removeItem = (item) => {
    const index = cart.value.indexOf(item)

    if (index !== -1) {
        cart.value.splice(index, 1)
    }

    if (selectedProduct.value === item) {
        selectedProduct.value = null
    }
}

/*
|--------------------------------------------------------------------------
| Discount
|--------------------------------------------------------------------------
*/

const openDiscount = (item) => {
    discountModalItem.value = item
}

const closeDiscount = () => {
    discountModalItem.value = null
}

const applyDiscount = ({
    type,
    value,
}) => {
    if (!discountModalItem.value) {
        return
    }

    discountModalItem.value.discount_type = type
    discountModalItem.value.discount_value =
        Number(value) || 0

    recalculateItem(
        discountModalItem.value
    )

    closeDiscount()
}

/*
|--------------------------------------------------------------------------
| Customer
|--------------------------------------------------------------------------
*/

const customerName = computed(() => {
    return (
        selectedCustomer.value?.name ??
        'Walk-in Customer'
    )
})

const openCustomer = () => {
    showCustomerModal.value = true
}

const closeCustomer = () => {
    showCustomerModal.value = false
}

const selectCustomer = (customer) => {
    selectedCustomer.value = customer

    closeCustomer()
}

/*
|--------------------------------------------------------------------------
| Payment
|--------------------------------------------------------------------------
*/

const openPayment = () => {
    if (!cart.value.length) {
        return
    }

    showPaymentModal.value = true
}

const closePayment = () => {
    if (processing.value) {
        return
    }

    showPaymentModal.value = false
}

/*
|--------------------------------------------------------------------------
| Payment Submit
|--------------------------------------------------------------------------
*/

const submitPayment = (payment) => {
    if (processing.value) {
        return
    }

    processing.value = true

    router.post(
        route('pos.transactions.store'),
        {
            customer_id:
                selectedCustomer.value?.id ??
                null,

            discount_amount: 0,

            note: null,

            details: cart.value.map((item) => ({
                product_variant_id:
                    item.product_variant_id,

                unit_id:
                    item.unit_id,

                price_type_id:
                    item.price_type_id,

                qty:
                    item.qty,

                discount_amount:
                    item.discount_amount,
            })),

            payments: payment.payments,
        },
        {
            preserveScroll: true,

            onSuccess: () => {
                cart.value = []

                selectedCustomer.value = null
                selectedProduct.value = null

                showPaymentModal.value = false

                search.value = ''

                nextTick(() => {
                    searchInput.value?.focus()
                })
            },

            onFinish: () => {
                processing.value = false
            },
        }
    )
}

/*
|--------------------------------------------------------------------------
| Clear Cart
|--------------------------------------------------------------------------
*/

const clearCart = () => {
    if (!cart.value.length) {
        return
    }

    cart.value = []
    selectedProduct.value = null
}

/*
|--------------------------------------------------------------------------
| Keyboard Shortcuts
|--------------------------------------------------------------------------
*/

const handleKeyboard = (event) => {
    const target = event.target

    const isTyping =
        target instanceof HTMLInputElement ||
        target instanceof HTMLTextAreaElement ||
        target instanceof HTMLSelectElement

    if (event.key === 'F2') {
        event.preventDefault()

        searchInput.value?.focus()

        return
    }

    if (event.key === 'F4') {
        event.preventDefault()

        openCustomer()

        return
    }

    if (event.key === 'F8') {
        event.preventDefault()

        if (selectedProduct.value) {
            openDiscount(
                selectedProduct.value
            )
        }

        return
    }

    if (event.key === 'F9') {
        event.preventDefault()

        openPayment()

        return
    }

    if (event.key === 'Escape') {
        if (showPaymentModal.value) {
            closePayment()
            return
        }

        if (showCustomerModal.value) {
            closeCustomer()
            return
        }

        if (discountModalItem.value) {
            closeDiscount()
            return
        }
    }

    if (
        event.key === 'Delete' &&
        !isTyping &&
        selectedProduct.value
    ) {
        event.preventDefault()

        removeItem(
            selectedProduct.value
        )
    }
}

/*
|--------------------------------------------------------------------------
| Lifecycle
|--------------------------------------------------------------------------
*/

onMounted(() => {
    clockTimer = setInterval(() => {
        currentTime.value = new Date()
    }, 1000)

    window.addEventListener(
        'keydown',
        handleKeyboard
    )

    nextTick(() => {
        searchInput.value?.focus()
    })
})

onBeforeUnmount(() => {
    if (clockTimer) {
        clearInterval(clockTimer)
    }

    window.removeEventListener(
        'keydown',
        handleKeyboard
    )
})
</script>
<template>
    <div class="min-h-screen bg-slate-50">

        <!-- HEADER -->
        <header
            class="border-b border-slate-200 bg-white"
        >
            <div
                class="flex h-16 items-center justify-between px-6"
            >
                <div class="flex items-center gap-5">

                    <div>
                        <div
                            class="flex items-center gap-2"
                        >
                            <span
                                class="text-lg font-bold tracking-tight text-slate-900"
                            >
                                NUVORA
                            </span>

                            <span
                                class="text-lg font-semibold text-blue-600"
                            >
                                POS
                            </span>
                        </div>

                        <div
                            class="mt-0.5 text-xs text-slate-500"
                        >
                            Point of Sale
                        </div>
                    </div>

                    <div
                        class="h-8 w-px bg-slate-200"
                    />

                    <div>
                        <div
                            class="text-sm font-semibold text-slate-800"
                        >
                            {{ currentBranch?.name ?? 'Branch' }}
                        </div>

                        <div
                            class="text-xs text-slate-500"
                        >
                            {{ currentBranch?.code ?? '-' }}
                        </div>
                    </div>
                </div>

                <div
                    class="flex items-center gap-6"
                >

                    <div
                        class="hidden text-right sm:block"
                    >
                        <div
                            class="text-sm font-medium text-slate-800"
                        >
                            {{ authUser?.name ?? 'Cashier' }}
                        </div>

                        <div
                            class="text-xs text-slate-500"
                        >
                            Cashier
                        </div>
                    </div>

                    <div class="text-right">
                        <div
                            class="text-sm font-semibold text-slate-800"
                        >
                            {{ formattedDate }}
                        </div>

                        <div
                            class="text-xs tabular-nums text-slate-500"
                        >
                            {{ formattedTime }}
                        </div>
                    </div>

                    <div
                        class="flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5"
                    >
                        <span
                            class="h-2 w-2 rounded-full bg-emerald-500"
                        />

                        <span
                            class="text-xs font-semibold text-emerald-700"
                        >
                            SESSION OPEN
                        </span>
                    </div>

                </div>
            </div>
        </header>


        <!-- MAIN -->
        <main class="p-5">

            <div
                class="mx-auto grid max-w-[1600px] grid-cols-12 gap-5"
            >

                <!-- LEFT -->
                <section
                    class="col-span-12 flex min-h-[calc(100vh-130px)] flex-col lg:col-span-8"
                >

                    <!-- SEARCH -->
                    <div
                        class="rounded-xl border border-slate-200 bg-white p-4"
                    >
                        <div
                            class="flex items-center gap-3"
                        >

                            <div
                                class="relative flex-1"
                            >
                                <svg
                                    class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"
                                    />
                                </svg>

                                <input
                                    ref="searchInput"
                                    v-model="search"
                                    type="text"
                                    autocomplete="off"
                                    placeholder="Scan barcode or search product..."
                                    class="h-12 w-full rounded-lg border border-slate-200 bg-slate-50 pl-10 pr-20 text-sm font-medium text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100"
                                />

                                <div
                                    class="absolute right-3 top-1/2 -translate-y-1/2 rounded-md bg-white px-2 py-1 text-[11px] font-semibold text-slate-400 shadow-sm ring-1 ring-slate-200"
                                >
                                    F2
                                </div>
                            </div>

                            <button
                                type="button"
                                class="hidden h-12 rounded-lg border border-slate-200 px-4 text-sm font-medium text-slate-600 transition hover:border-blue-300 hover:text-blue-600 md:block"
                                @click="openCustomer"
                            >
                                Customer
                            </button>

                        </div>
                    </div>


                    <!-- PRODUCT AREA -->
                    <div class="mt-4 flex-1">

                        <div
                            class="mb-3 flex items-center justify-between"
                        >
                            <div>
                                <h2
                                    class="text-sm font-semibold text-slate-900"
                                >
                                    Products
                                </h2>

                                <p
                                    class="mt-0.5 text-xs text-slate-500"
                                >
                                    Select product to add to cart
                                </p>
                            </div>

                            <div
                                class="text-xs text-slate-500"
                            >
                                {{ filteredProducts.length }} products
                            </div>
                        </div>


                       <!-- PRODUCT CARDS -->
<div
    v-if="filteredProducts.length"
    class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-4"
>
    <button
        v-for="product in filteredProducts"
        :key="
            product.variant_id ??
            product.product_variant_id ??
            product.id
        "
        type="button"
        class="group overflow-hidden rounded-xl border border-slate-200 bg-white text-left transition hover:-translate-y-0.5 hover:border-blue-300 hover:shadow-md"
        @click="addProduct(product)"
    >

        <!-- IMAGE -->
        <div
            class="flex aspect-[4/3] items-center justify-center overflow-hidden bg-slate-100"
        >

            <img
                v-if="getProductImage(product)"
                :src="getProductImage(product)"
                :alt="product.product_name ?? product.name"
                class="h-full w-full object-cover transition duration-200 group-hover:scale-[1.02]"
            />

            <div
                v-else
                class="flex h-full w-full items-center justify-center bg-blue-50"
            >
                <span
                    class="text-2xl font-bold tracking-tight text-blue-600"
                >
                    {{
                        getInitials(
                            product.product_name ??
                            product.name
                        )
                    }}
                </span>
            </div>

        </div>


        <!-- INFO -->
        <div class="p-3">

            <!-- PRODUCT NAME -->
            <div
                class="truncate text-sm font-semibold text-slate-900"
            >
                {{
                    product.product_name ??
                    product.name
                }}
            </div>

            <!-- VARIANT -->
            <div
                v-if="
                    product.variant_name &&
                    product.variant_name !== 'Default' &&
                    product.variant_name !== product.product_name
                "
                class="mt-0.5 truncate text-xs text-slate-500"
            >
                {{ product.variant_name }}
            </div>

            <!-- SKU -->
            <div
                class="mt-1 truncate text-xs text-slate-500"
            >
                {{
                    product.variant_sku ??
                    product.sku ??
                    '-'
                }}
            </div>

            <!-- PRICE + STOCK -->
            <div
                class="mt-3 flex items-end justify-between gap-2"
            >

                <div
                    class="text-sm font-bold text-blue-600"
                >
                    {{
                        formatCurrency(
                            product.selling_price ??
                            product.price ??
                            0
                        )
                    }}
                </div>

                <div
                    class="text-[11px] font-medium text-slate-400"
                >
                    Stock
                    {{
                        product.available_stock ??
                        product.available_qty ??
                        0
                    }}
                </div>

            </div>

        </div>

    </button>
</div>


                        <!-- EMPTY -->
                        <div
                            v-else
                            class="flex min-h-[300px] items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white"
                        >
                            <div
                                class="text-center"
                            >
                                <div
                                    class="text-sm font-semibold text-slate-700"
                                >
                                    No products found
                                </div>

                                <div
                                    class="mt-1 text-xs text-slate-400"
                                >
                                    Try another product name, SKU, or barcode.
                                </div>
                            </div>
                        </div>

                    </div>

                </section>


                <!-- RIGHT / CART -->
                <aside
                    class="col-span-12 lg:col-span-4"
                >

                    <div
                        class="sticky top-5 flex max-h-[calc(100vh-130px)] flex-col overflow-hidden rounded-xl border border-slate-200 bg-white"
                    >

                        <!-- CART HEADER -->
                        <div
                            class="border-b border-slate-200 px-4 py-4"
                        >
                            <div
                                class="flex items-center justify-between"
                            >
                                <div>
                                    <h2
                                        class="text-sm font-semibold text-slate-900"
                                    >
                                        Current Sale
                                    </h2>

                                    <div
                                        class="mt-1 text-xs text-slate-500"
                                    >
                                        {{ cartCount }} item(s)
                                    </div>
                                </div>

                                <button
                                    v-if="cart.length"
                                    type="button"
                                    class="text-xs font-semibold text-red-500 hover:text-red-600"
                                    @click="clearCart"
                                >
                                    Clear
                                </button>
                            </div>
                        </div>


                        <!-- CUSTOMER -->
                        <button
                            type="button"
                            class="flex items-center justify-between border-b border-slate-100 px-4 py-3 text-left hover:bg-slate-50"
                            @click="openCustomer"
                        >
                            <div>
                                <div
                                    class="text-[11px] font-medium uppercase tracking-wide text-slate-400"
                                >
                                    Customer
                                </div>

                                <div
                                    class="mt-1 text-sm font-semibold text-slate-800"
                                >
                                    {{ customerName }}
                                </div>
                            </div>

                            <span
                                class="text-xs font-semibold text-blue-600"
                            >
                                Change
                            </span>
                        </button>


                        <!-- CART ITEMS -->
                        <div
                            class="min-h-0 flex-1 overflow-y-auto"
                        >

                            <div
                                v-if="cart.length"
                                class="divide-y divide-slate-100"
                            >

                                <div
                                    v-for="item in cart"
                                    :key="
                                        `${item.product_variant_id}-${item.unit_id}`
                                    "
                                    class="cursor-pointer px-4 py-4 transition hover:bg-slate-50"
                                    :class="{
                                        'bg-blue-50/70':
                                            selectedProduct === item,
                                    }"
                                    @click="
                                        selectedProduct = item
                                    "
                                >

                                    <div
                                        class="flex gap-3"
                                    >

                                        <!-- MINI IMAGE -->
                                     <div
                                        class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-slate-100"
                                    >
                                        <img
                                            v-if="item.image"
                                            :src="item.image"
                                            :alt="item.product_name"
                                            class="h-full w-full object-cover"
                                        />

                                        <span
                                            v-else
                                            class="text-xs font-bold text-blue-600"
                                        >
                                            {{
                                                getInitials(
                                                    item.product_name
                                                )
                                            }}
                                        </span>
                                    </div>


                                        <div
                                            class="min-w-0 flex-1"
                                        >

                                            <div
                                                class="flex items-start justify-between gap-2"
                                            >
                                               <div
                                                    class="min-w-0"
                                                >
                                                   <div class="truncate text-sm font-semibold text-slate-800">
                                                    {{
                                                        item.product_name
                                                        + (
                                                            item.variant_name &&
                                                            item.variant_name !== 'Default'
                                                                ? ` ${item.variant_name}`
                                                                : ''
                                                        )
                                                    }}
                                                </div>

                                                    <div
                                                        class="mt-0.5 text-xs text-slate-400"
                                                    >
                                                        {{
                                                            item.sku ||
                                                            '-'
                                                        }}

                                                        <span v-if="item.unit_name">
                                                            · {{ item.unit_name }}
                                                        </span>
                                                    </div>
                                                </div>

                                                <div
                                                    class="whitespace-nowrap text-sm font-semibold text-slate-900"
                                                >
                                                    {{
                                                        formatCurrency(
                                                            item.subtotal
                                                        )
                                                    }}
                                                </div>
                                            </div>


                                            <div
                                                class="mt-3 flex items-center justify-between"
                                            >

                                                <div
                                                    class="flex items-center rounded-lg border border-slate-200 bg-white"
                                                >
                                                    <button
                                                        type="button"
                                                        class="flex h-8 w-8 items-center justify-center text-slate-500 hover:text-blue-600"
                                                        @click.stop="
                                                            decreaseQty(item)
                                                        "
                                                    >
                                                        −
                                                    </button>

                                                    <span
                                                        class="w-8 text-center text-xs font-semibold text-slate-800"
                                                    >
                                                        {{ item.qty }}
                                                    </span>

                                                    <button
                                                        type="button"
                                                        class="flex h-8 w-8 items-center justify-center text-slate-500 hover:text-blue-600"
                                                        @click.stop="
                                                            increaseQty(item)
                                                        "
                                                    >
                                                        +
                                                    </button>
                                                </div>


                                                <button
                                                    type="button"
                                                    class="text-xs font-medium text-slate-400 hover:text-orange-500"
                                                    @click.stop="
                                                        openDiscount(item)
                                                    "
                                                >
                                                    {{
                                                        item.discount_amount > 0
                                                            ? `Discount ${
                                                                item.discount_type === 'percentage'
                                                                    ? item.discount_value + '%'
                                                                    : formatCurrency(item.discount_value)
                                                            }`
                                                            : 'Discount'
                                                    }}
                                                </button>

                                            </div>


                                            <div
                                                v-if="
                                                    item.discount_amount > 0
                                                "
                                                class="mt-1 text-right text-xs font-medium text-orange-600"
                                            >
                                                -{{
                                                    formatCurrency(
                                                        item.discount_amount
                                                    )
                                                }}
                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>


                            <div
                                v-else
                                class="flex min-h-[280px] items-center justify-center px-6"
                            >
                                <div
                                    class="text-center"
                                >
                                    <div
                                        class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400"
                                    >
                                        <svg
                                            class="h-6 w-6"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13 5.4 5M7 13l-2 2a1 1 0 0 0 .7 1.7h11.8M10 21h.01M18 21h.01"
                                            />
                                        </svg>
                                    </div>

                                    <div
                                        class="mt-3 text-sm font-semibold text-slate-700"
                                    >
                                        Cart is empty
                                    </div>

                                    <div
                                        class="mt-1 text-xs text-slate-400"
                                    >
                                        Scan or select a product to start a sale.
                                    </div>
                                </div>
                            </div>

                        </div>


                        <!-- SUMMARY -->
                        <div
                            class="border-t border-slate-200 bg-slate-50/70 p-4"
                        >

                            <div
                                class="space-y-2 text-sm"
                            >

                                <div
                                    class="flex justify-between text-slate-500"
                                >
                                    <span>Subtotal</span>

                                    <span
                                        class="font-medium text-slate-700"
                                    >
                                        {{
                                            formatCurrency(
                                                subtotal
                                            )
                                        }}
                                    </span>
                                </div>

                                <div
                                    class="flex justify-between text-slate-500"
                                >
                                    <span>Discount</span>

                                    <span
                                        class="font-medium text-orange-600"
                                    >
                                        {{
                                            discountTotal > 0
                                                ? `-${formatCurrency(discountTotal)}`
                                                : formatCurrency(0)
                                        }}
                                    </span>
                                </div>

                                <div
                                    class="my-3 border-t border-slate-200"
                                />

                                <div
                                    class="flex items-end justify-between"
                                >
                                    <span
                                        class="text-sm font-semibold text-slate-800"
                                    >
                                        TOTAL
                                    </span>

                                    <span
                                        class="text-2xl font-bold tracking-tight text-blue-600"
                                    >
                                        {{
                                            formatCurrency(
                                                grandTotal
                                            )
                                        }}
                                    </span>
                                </div>

                            </div>


                            <!-- PAYMENT -->
                            <button
                                type="button"
                                :disabled="
                                    !cart.length ||
                                    !sessionOpen
                                "
                                class="mt-4 flex h-12 w-full items-center justify-between rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-slate-300"
                                @click="openPayment"
                            >
                                <span>PAYMENT</span>

                                <span
                                    class="rounded bg-white/15 px-2 py-1 text-[11px]"
                                >
                                    F9
                                </span>
                            </button>

                        </div>

                    </div>

                </aside>

            </div>

        </main>


        <!--
        |--------------------------------------------------------------------------
        | MODALS
        |--------------------------------------------------------------------------
        |
        | Components akan kita pasang setelah file component-nya dibuat:
        |
        | Transaction/Components/PaymentModal.vue
        | Transaction/Components/CustomerModal.vue
        | Transaction/Components/DiscountModal.vue
        |
        -->

    </div>
    <VariantModal
    :show="showVariantModal"
    :product="selectedProductForVariant"
    @close="closeVariantModal"
    @select="selectVariant"
/>
</template>