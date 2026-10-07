
<script setup>
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from 'vue'

import { router, usePage } from '@inertiajs/vue3'
import Swal from 'sweetalert2'

import ProductSearchModal from './ProductSearchModal.vue'
import CustomerModal from '../Transaction/CustomerModal.vue'
import DiscountModal from '../Transaction/DiscountModal.vue'
import PaymentModal from '../Transaction/PaymentModal.vue'
import VariantModal from '../Transaction/VariantModal.vue'

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

const transactionDate = ref(
    new Date().toISOString().slice(0, 10)
)

const selectedProduct = ref(null)

const selectedCustomer = ref(null)

const selectedPriceType = ref(
    props.priceTypes?.[0]?.id ?? ''
)

const cart = ref([])

const discountModalItem = ref(null)

const showCustomerModal = ref(false)

const showPaymentModal = ref(false)

const showProductSearchModal = ref(false)

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

const formattedTransactionDate = computed(() => {
    if (!transactionDate.value) {
        return '-'
    }

    const date = new Date(
        `${transactionDate.value}T00:00:00`
    )

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    }).format(date)
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
            product.product_name,
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
        (total, item) =>
            total + Number(item.qty),
        0
    )
})

const subtotal = computed(() => {
    return cart.value.reduce(
        (total, item) =>
            total + Number(item.subtotal),
        0
    )
})

const discountTotal = computed(() => {
    return cart.value.reduce(
        (total, item) =>
            total + Number(item.discount_amount),
        0
    )
})

const grandTotal = computed(() => {
    return Math.max(
        0,
        subtotal.value
    )
})


/*
|--------------------------------------------------------------------------
| Product Search Modal
|--------------------------------------------------------------------------
*/

const openProductSearch = () => {
    if (!sessionOpen.value) {
        return
    }

    showProductSearchModal.value = true
}

const handleSearchInput = () => {
    if (!sessionOpen.value) {
        return
    }

    showProductSearchModal.value = true
}

const closeProductSearch = () => {
    showProductSearchModal.value = false
}

const selectProductFromSearch = (product) => {

    if (!product || !sessionOpen.value) {
        return
    }

    addProduct(product)

    showProductSearchModal.value = false
}


/*
|--------------------------------------------------------------------------
| Barcode
|--------------------------------------------------------------------------
*/

const handleBarcode = () => {

    if (!sessionOpen.value) {
        return
    }

    const keyword = search.value
        .trim()
        .toLowerCase()

    if (!keyword) {
        openProductSearch()
        return
    }

    /*
    |--------------------------------------------------------------------------
    | Exact Barcode Match
    |--------------------------------------------------------------------------
    */

    const exactBarcodeProducts =
        props.products.filter((product) => {

            const variants =
                Array.isArray(product.variants)
                    ? product.variants
                    : []

            return variants.some((variant) => {
                return String(
                    variant.barcode ?? ''
                ).toLowerCase() === keyword
            })
        })

    if (exactBarcodeProducts.length === 1) {

        const product =
            exactBarcodeProducts[0]

        const variants =
            Array.isArray(product.variants)
                ? product.variants
                : []

        const matchingVariant =
            variants.find(
                (variant) =>
                    String(
                        variant.barcode ?? ''
                    ).toLowerCase() === keyword
            )

        if (matchingVariant) {

            addVariantToCart(
                product,
                matchingVariant
            )

            search.value = ''

            return
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Search Result
    |--------------------------------------------------------------------------
    */

    showProductSearchModal.value = true
}


/*
|--------------------------------------------------------------------------
| Add Product
|--------------------------------------------------------------------------
*/

const addProduct = (product) => {

    if (!sessionOpen.value) {
        return
    }

    const variants =
        Array.isArray(product.variants)
            ? product.variants
            : []

    if (variants.length > 1) {

        selectedProductForVariant.value =
            product

        showVariantModal.value = true

        return
    }

    const variant =
        variants[0] ?? product

    addVariantToCart(
        product,
        variant
    )
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
        Swal.fire({
            icon: 'warning',
            title: 'Product Tidak Valid',
            text: 'Variant, unit, atau price type belum tersedia.',
            confirmButtonText: 'OK',
        })

        return
    }


    /*
    |--------------------------------------------------------------------------
    | Existing Cart Item
    |--------------------------------------------------------------------------
    */

    const existing =
        cart.value.find((item) => {

            return (
                Number(
                    item.product_variant_id
                ) === Number(variantId)

                &&

                Number(
                    item.unit_id
                ) === Number(unitId)
            )
        })


    /*
    |--------------------------------------------------------------------------
    | Existing
    |--------------------------------------------------------------------------
    */

    if (existing) {

        existing.qty += 1

        recalculateItem(existing)

        selectedProduct.value =
            existing
    }


    /*
    |--------------------------------------------------------------------------
    | New
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


        const productName =
            product.product_name ??
            product.name ??
            ''


        const variantName =
            variant.variant_name ??
            variant.name ??
            ''


        const displayVariantName =
            variantName &&
            variantName !== 'Default' &&
            variantName !== productName
                ? variantName
                : null


        const item = {

            product_variant_id:
                variantId,

            unit_id:
                unitId,

            price_type_id:
                priceTypeId,

            product_name:
                productName,

            variant_name:
                displayVariantName,

            sku:
                variant.variant_sku ??
                variant.sku ??
                product.sku ??
                '',

            barcode:
                variant.barcode ??
                product.barcode ??
                '',

            unit_name:
                variant.unit_name ??
                product.unit_name ??
                '',

            image:
                variant.image ??
                product.image ??
                getProductImage(product),

            qty:
                1,

            unit_price:
                unitPrice,

            discount_type:
                'percentage',

            discount_value:
                0,

            discount_amount:
                0,

            subtotal:
                unitPrice,
        }


        cart.value.push(item)

        selectedProduct.value =
            item
    }


    /*
    |--------------------------------------------------------------------------
    | Close Modals
    |--------------------------------------------------------------------------
    */

    showProductSearchModal.value =
        false

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


    nextTick(() => {
        searchInput.value?.focus()
    })
}


/*
|--------------------------------------------------------------------------
| Variant
|--------------------------------------------------------------------------
*/

const selectVariant = (variant) => {

    const product =
        selectedProductForVariant.value

    if (!product || !variant) {
        return
    }

    addVariantToCart(
        product,
        variant
    )
}

const closeVariantModal = () => {

    showVariantModal.value = false

    selectedProductForVariant.value =
        null

    nextTick(() => {
        searchInput.value?.focus()
    })
}


/*
|--------------------------------------------------------------------------
| Recalculate
|--------------------------------------------------------------------------
*/

const recalculateItem = (item) => {

    const gross =
        Number(item.qty) *
        Number(item.unit_price)

    let discount = 0

    if (
        item.discount_type ===
        'percentage'
    ) {

        discount =
            gross *
            (
                Number(
                    item.discount_value
                ) / 100
            )

    } else {

        discount =
            Number(
                item.discount_value
            )
    }


    discount = Math.min(
        Math.max(0, discount),
        gross
    )


    item.discount_amount =
        discount

    item.subtotal =
        Math.max(
            0,
            gross - discount
        )
}


/*
|--------------------------------------------------------------------------
| Quantity
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

    const index =
        cart.value.indexOf(item)

    if (index !== -1) {
        cart.value.splice(index, 1)
    }

    if (
        selectedProduct.value ===
        item
    ) {
        selectedProduct.value = null
    }
}


/*
|--------------------------------------------------------------------------
| Discount
|--------------------------------------------------------------------------
*/

const openDiscount = (item) => {

    if (!item) {
        return
    }

    discountModalItem.value =
        item
}

const closeDiscount = () => {

    discountModalItem.value =
        null
}

const applyDiscount = ({
    type,
    value,
}) => {

    if (!discountModalItem.value) {
        return
    }

    discountModalItem.value.discount_type =
        type

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

    selectedCustomer.value =
        customer

    closeCustomer()
}

const clearCustomer = () => {

    selectedCustomer.value =
        null

    closeCustomer()
}


/*
|--------------------------------------------------------------------------
| Payment
|--------------------------------------------------------------------------
*/

const openPayment = () => {

    if (
        !cart.value.length ||
        !sessionOpen.value ||
        !transactionDate.value
    ) {
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

    if (!transactionDate.value) {
        return
    }

    processing.value = true


    /*
    |--------------------------------------------------------------------------
    | Receipt Snapshot
    |--------------------------------------------------------------------------
    */

    const receiptItems =
        cart.value.map((item) => ({
            product_name:
                item.product_name,

            variant_name:
                item.variant_name,

            sku:
                item.sku,

            unit_name:
                item.unit_name,

            qty:
                Number(item.qty),

            unit_price:
                Number(item.unit_price),

            discount_amount:
                Number(item.discount_amount),

            subtotal:
                Number(item.subtotal),
        }))


    const receiptCustomer =
        selectedCustomer.value?.name ??
        'Walk-in Customer'


    /*
    |--------------------------------------------------------------------------
    | Submit
    |--------------------------------------------------------------------------
    */

    router.post(
        route(
            'pos.backdate-sales.store'
        ),
        {

            transaction_date:
                transactionDate.value,

            customer_id:
                selectedCustomer.value?.id ??
                null,

            discount_amount:
                0,

            note:
                null,

            details:
                cart.value.map((item) => ({
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

            payments:
                payment.payments,
        },
        {

            preserveScroll: true,

            onSuccess: (page) => {

                showPaymentModal.value =
                    false

                const sale =
                    page.props.flash
                        ?.pos_sale_success

                if (!sale) {
                    return
                }

                showPaymentSuccess(
                    sale,
                    receiptItems,
                    receiptCustomer,
                    payment
                )
            },

            onFinish: () => {

                processing.value =
                    false
            },
        }
    )
}


/*
|--------------------------------------------------------------------------
| Payment Success
|--------------------------------------------------------------------------
*/

const showPaymentSuccess = (
    sale,
    receiptItems,
    receiptCustomer,
    payment
) => {

    Swal.fire({

        icon: 'success',

        title: 'Payment Successful',

        html: `
            <div class="text-left">

                <div class="mb-4 rounded-lg bg-slate-50 p-3">

                    <div class="text-xs text-slate-500">
                        Invoice
                    </div>

                    <div class="mt-1 text-sm font-semibold text-slate-900">
                        ${sale.sale_number}
                    </div>

                </div>

                <div class="mb-4 rounded-lg bg-blue-50 p-3">

                    <div class="text-xs text-slate-500">
                        Transaction Date
                    </div>

                    <div class="mt-1 text-sm font-semibold text-slate-900">
                        ${formatReceiptDate(sale.sale_date)}
                    </div>

                </div>

                <div class="space-y-2 text-sm">

                    <div class="flex justify-between gap-4">

                        <span class="text-slate-500">
                            Total
                        </span>

                        <span class="font-semibold text-slate-900">
                            ${formatCurrency(sale.grand_total)}
                        </span>

                    </div>

                    <div class="flex justify-between gap-4">

                        <span class="text-slate-500">
                            Paid
                        </span>

                        <span class="font-semibold text-slate-900">
                            ${formatCurrency(sale.paid_amount)}
                        </span>

                    </div>

                    <div class="flex justify-between gap-4">

                        <span class="text-slate-500">
                            Change
                        </span>

                        <span class="font-semibold text-emerald-600">
                            ${formatCurrency(sale.change_amount)}
                        </span>

                    </div>

                </div>

            </div>
        `,

        showDenyButton: true,

        showCancelButton: false,

        confirmButtonText:
            'New Transaction',

        denyButtonText:
            'Print Receipt',

        reverseButtons: true,

        allowOutsideClick: false,

        allowEscapeKey: false,

    }).then((result) => {

        const restorePosAccessibility =
            () => {

                document
                    .activeElement
                    ?.blur()

                const app =
                    document.getElementById(
                        'app'
                    )

                if (app) {
                    app.removeAttribute(
                        'aria-hidden'
                    )
                }
            }


        if (result.isDenied) {

            startNewTransaction()

            printReceipt(
                sale,
                receiptItems,
                receiptCustomer,
                payment
            )

            return
        }


        if (result.isConfirmed) {

            restorePosAccessibility()

            startNewTransaction()
        }

    })
}


/*
|--------------------------------------------------------------------------
| Receipt Date
|--------------------------------------------------------------------------
*/

const formatReceiptDate = (value) => {

    if (!value) {
        return '-'
    }

    const date =
        new Date(value)

    return new Intl.DateTimeFormat(
        'id-ID',
        {
            day: '2-digit',
            month: 'long',
            year: 'numeric',
        }
    ).format(date)
}


/*
|--------------------------------------------------------------------------
| Print Receipt Date / Time
|--------------------------------------------------------------------------
*/

const formatTransactionDateTime = (
    value
) => {

    if (!value) {
        return '-'
    }

    const date =
        new Date(value)

    return new Intl.DateTimeFormat(
        'id-ID',
        {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false,
        }
    ).format(date)
}


/*
|--------------------------------------------------------------------------
| Print Receipt
|--------------------------------------------------------------------------
*/

const printReceipt = (
    sale,
    items,
    customer,
    payment
) => {

    const receiptWindow =
        window.open(
            '',
            '_blank',
            'width=420,height=700'
        )

    if (!receiptWindow) {

        Swal.fire({
            icon: 'warning',
            title: 'Print Blocked',
            text: 'Browser memblokir popup print. Silakan izinkan popup untuk Nuvora POS.',
            confirmButtonText: 'OK',
        })

        return
    }


    const itemRows =
        items
            .map((item) => {

                const itemName = [
                    item.product_name,

                    item.variant_name &&
                    item.variant_name !== 'Default'
                        ? item.variant_name
                        : null,
                ]
                    .filter(Boolean)
                    .join(' ')


                return `
                    <tr>
                        <td>
                            ${itemName}

                            <br>

                            <small>
                                ${item.qty}
                                x
                                ${formatCurrency(item.unit_price)}
                            </small>
                        </td>

                        <td class="right">
                            ${formatCurrency(item.subtotal)}
                        </td>
                    </tr>
                `
            })
            .join('')


    const paymentRows =
        payment.payments
            .map((item) => {

                return `
                    <tr>

                        <td>
                            ${item.payment_method}
                        </td>

                        <td class="right">
                            ${formatCurrency(item.amount)}
                        </td>

                    </tr>
                `
            })
            .join('')


    receiptWindow.document.write(`
        <!DOCTYPE html>

        <html>

        <head>

            <title>
                ${sale.sale_number}
            </title>

            <style>

                * {
                    box-sizing: border-box;
                }

                body {
                    margin: 0;
                    padding: 20px;
                    font-family: Arial, sans-serif;
                    color: #111827;
                    font-size: 12px;
                }

                .receipt {
                    width: 100%;
                    max-width: 360px;
                    margin: 0 auto;
                }

                .center {
                    text-align: center;
                }

                .brand {
                    font-size: 20px;
                    font-weight: 700;
                    letter-spacing: 0.5px;
                }

                .muted {
                    color: #6b7280;
                }

                .divider {
                    border-top: 1px dashed #9ca3af;
                    margin: 12px 0;
                }

                table {
                    width: 100%;
                    border-collapse: collapse;
                }

                td {
                    padding: 4px 0;
                    vertical-align: top;
                }

                .right {
                    text-align: right;
                    white-space: nowrap;
                }

                .total {
                    font-size: 15px;
                    font-weight: 700;
                }

                .footer {
                    margin-top: 20px;
                    text-align: center;
                }

                @media print {

                    body {
                        padding: 0;
                    }

                    .receipt {
                        max-width: none;
                    }

                }

            </style>

        </head>


        <body>

            <div class="receipt">

                <div class="center">

                    <div class="brand">
                        Ralisa Homedress
                    </div>

                    <div class="muted">
                        ${sale.sale_number}
                    </div>

                    <div class="muted">
                        Transaction:
                        ${formatReceiptDate(sale.sale_date)}
                    </div>

                    <div class="muted">
                        ${customer}
                    </div>

                </div>


                <div class="divider"></div>


                <table>

                    <tbody>
                        ${itemRows}
                    </tbody>

                </table>


                <div class="divider"></div>


                <table>

                    <tr>

                        <td>
                            Subtotal
                        </td>

                        <td class="right">
                            ${formatCurrency(sale.subtotal)}
                        </td>

                    </tr>


                    <tr>

                        <td>
                            Discount
                        </td>

                        <td class="right">
                            ${formatCurrency(sale.discount_amount)}
                        </td>

                    </tr>


                    <tr>

                        <td class="total">
                            Total
                        </td>

                        <td class="right total">
                            ${formatCurrency(sale.grand_total)}
                        </td>

                    </tr>

                </table>


                <div class="divider"></div>


                <table>

                    <tbody>
                        ${paymentRows}
                    </tbody>

                </table>


                <table>

                    <tr>

                        <td>
                            Paid
                        </td>

                        <td class="right">
                            ${formatCurrency(sale.paid_amount)}
                        </td>

                    </tr>


                    <tr>

                        <td>
                            Change
                        </td>

                        <td class="right">
                            ${formatCurrency(sale.change_amount)}
                        </td>

                    </tr>

                </table>


                <div class="footer">

                    Terima kasih telah berbelanja.

                    <br>

                    <span class="muted">
                        Ralisa Homedress
                        081268986925
                    </span>

                </div>

            </div>


            <script>

                window.onload = function () {
                    window.print()
                }

                window.onafterprint = function () {

                    if (
                        window.opener &&
                        !window.opener.closed
                    ) {

                        window.opener.location.reload()
                    }

                    window.close()
                }

            <\/script>

        </body>

        </html>
    `)

    receiptWindow.document.close()
}


/*
|--------------------------------------------------------------------------
| Start New Transaction
|--------------------------------------------------------------------------
*/

const startNewTransaction = () => {

    processing.value =
        false

    showPaymentModal.value =
        false

    showProductSearchModal.value =
        false

    cart.value = []

    selectedCustomer.value =
        null

    selectedProduct.value =
        null

    selectedProductForVariant.value =
        null

    search.value = ''

    nextTick(() => {
        searchInput.value?.focus()
    })
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

    selectedProduct.value =
        null
}


/*
|--------------------------------------------------------------------------
| Keyboard Shortcuts
|--------------------------------------------------------------------------
*/

const handleKeyboard = (event) => {

    const target =
        event.target

    const isTyping =
        target instanceof
            HTMLInputElement ||

        target instanceof
            HTMLTextAreaElement ||

        target instanceof
            HTMLSelectElement


    /*
    |--------------------------------------------------------------------------
    | F2
    |--------------------------------------------------------------------------
    */

    if (event.key === 'F2') {

        event.preventDefault()

        searchInput.value?.focus()

        openProductSearch()

        return
    }


    /*
    |--------------------------------------------------------------------------
    | F4
    |--------------------------------------------------------------------------
    */

    if (event.key === 'F4') {

        event.preventDefault()

        openCustomer()

        return
    }


    /*
    |--------------------------------------------------------------------------
    | F8
    |--------------------------------------------------------------------------
    */

    if (event.key === 'F8') {

        event.preventDefault()

        if (selectedProduct.value) {

            openDiscount(
                selectedProduct.value
            )
        }

        return
    }


    /*
    |--------------------------------------------------------------------------
    | F9
    |--------------------------------------------------------------------------
    */

    if (event.key === 'F9') {

        event.preventDefault()

        openPayment()

        return
    }


    /*
    |--------------------------------------------------------------------------
    | Escape
    |--------------------------------------------------------------------------
    */

    if (event.key === 'Escape') {

        if (
            showPaymentModal.value
        ) {

            closePayment()

            return
        }

        if (
            showCustomerModal.value
        ) {

            closeCustomer()

            return
        }

        if (
            discountModalItem.value
        ) {

            closeDiscount()

            return
        }

        if (
            showVariantModal.value
        ) {

            closeVariantModal()

            return
        }

        if (
            showProductSearchModal.value
        ) {

            closeProductSearch()

            return
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

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
| Watch Transaction Date
|--------------------------------------------------------------------------
*/

watch(
    transactionDate,
    (value) => {

        if (!value) {
            return
        }

        const selected =
            new Date(
                `${value}T00:00:00`
            )

        const today =
            new Date()

        today.setHours(
            0,
            0,
            0,
            0
        )

        if (selected > today) {

            transactionDate.value =
                today
                    .toISOString()
                    .slice(0, 10)

            Swal.fire({
                icon: 'warning',
                title: 'Tanggal Tidak Valid',
                text: 'Tanggal transaksi tidak boleh melebihi hari ini.',
                confirmButtonText: 'OK',
            })
        }
    }
)


/*
|--------------------------------------------------------------------------
| Print Finished
|--------------------------------------------------------------------------
*/

const handlePrintFinished = (event) => {

    if (
        event.origin !==
            window.location.origin ||

        event.data?.type !==
            'nuvora-pos-print-finished'
    ) {
        return
    }

    startNewTransaction()

    window.location.reload()
}


/*
|--------------------------------------------------------------------------
| Lifecycle
|--------------------------------------------------------------------------
*/

onMounted(() => {

    clockTimer =
        setInterval(() => {

            currentTime.value =
                new Date()

        }, 1000)


    window.addEventListener(
        'keydown',
        handleKeyboard
    )

    window.addEventListener(
        'message',
        handlePrintFinished
    )


    nextTick(() => {

        searchInput.value?.focus()

    })
})


onBeforeUnmount(() => {

    if (clockTimer) {

        clearInterval(
            clockTimer
        )
    }


    window.removeEventListener(
        'keydown',
        handleKeyboard
    )

    window.removeEventListener(
        'message',
        handlePrintFinished
    )
})
</script>
<template>
    <div class="min-h-screen bg-slate-50">

        <!-- HEADER -->
        <header class="border-b border-slate-200 bg-white">
            <div
                class="flex h-16 items-center justify-between px-6"
            >

                <!-- BRAND / BRANCH -->
                <div class="flex items-center gap-5">

                    <div>
                        <div class="flex items-center gap-2">

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
                            Input Penjualan Lama
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


                <!-- USER / TIME / SESSION -->
                <div class="flex items-center gap-6">

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

                    <!-- TRANSACTION DATE -->
                    <div
                        class="rounded-xl border border-slate-200 bg-white p-4"
                    >

                        <div
                            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                        >

                            <div>

                                <div
                                    class="text-[11px] font-semibold uppercase tracking-wide text-slate-400"
                                >
                                    Tanggal Transaksi
                                </div>

                                <div
                                    class="mt-1 text-lg font-bold text-slate-900"
                                >
                                    {{ formattedTransactionDate }}
                                </div>

                            </div>


                            <div
                                class="flex items-center gap-2"
                            >

                                <input
                                    v-model="transactionDate"
                                    type="date"
                                    class="h-10 rounded-lg border border-slate-200 bg-slate-50 px-3 text-sm font-medium text-slate-800 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100"
                                />

                            </div>

                        </div>

                    </div>


                    <!-- SEARCH -->
                    <div
                        class="mt-4 rounded-xl border border-slate-200 bg-white p-4"
                    >

                        <div
                            class="flex items-center gap-3"
                        >

                            <div
                                class="relative flex-1"
                            >

                                <!-- SEARCH ICON -->
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
                                        d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0Z"
                                    />
                                </svg>

                                <input
                                    ref="searchInput"
                                    v-model="search"
                                    type="text"
                                    autocomplete="off"
                                    placeholder="Scan barcode atau cari product..."
                                    class="h-12 w-full rounded-lg border border-slate-200 bg-slate-50 pl-10 pr-20 text-sm font-medium text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100"
                                    @click="openProductSearch"
                                    @keydown.enter.prevent="handleBarcode"
                                />


                                <!-- F2 -->
                                <div
                                    class="absolute right-3 top-1/2 -translate-y-1/2 rounded-md bg-white px-2 py-1 text-[11px] font-semibold text-slate-400 shadow-sm ring-1 ring-slate-200"
                                >
                                    F2
                                </div>

                            </div>


                            <!-- CUSTOMER -->
                            <button
                                type="button"
                                class="hidden h-12 rounded-lg border border-slate-200 px-4 text-sm font-medium text-slate-600 transition hover:border-blue-300 hover:text-blue-600 md:block"
                                @click="openCustomer"
                            >
                                Customer
                            </button>

                        </div>

                    </div>


                    <!-- TRANSACTION TABLE -->
                    <div
                        class="mt-4 flex min-h-0 flex-1 flex-col overflow-hidden rounded-xl border border-slate-200 bg-white"
                    >

                        <!-- TABLE HEADER -->
                        <div
                            class="flex items-center justify-between border-b border-slate-200 px-4 py-4"
                        >

                            <div>

                                <h2
                                    class="text-sm font-semibold text-slate-900"
                                >
                                    Transaction
                                </h2>

                                <p
                                    class="mt-0.5 text-xs text-slate-500"
                                >
                                    Daftar produk yang dibeli
                                </p>

                            </div>


                            <div
                                class="text-xs text-slate-500"
                            >
                                {{ cartCount }} item(s)
                            </div>

                        </div>


                        <!-- TABLE -->
                        <div
                            class="min-h-0 flex-1 overflow-auto"
                        >

                            <table
                                v-if="cart.length"
                                class="w-full min-w-[620px] text-sm"
                            >

                                <thead
                                    class="sticky top-0 z-10 border-b border-slate-200 bg-slate-50"
                                >

                                    <tr>

                                        <th
                                            class="w-14 px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-wide text-slate-400"
                                        >
                                            No
                                        </th>

                                        <th
                                            class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-400"
                                        >
                                            Product
                                        </th>

                                        <th
                                            class="w-32 px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-wide text-slate-400"
                                        >
                                            Qty
                                        </th>

                                        <th
                                            class="w-32 px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wide text-slate-400"
                                        >
                                            Disc
                                        </th>

                                        <th
                                            class="w-36 px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wide text-slate-400"
                                        >
                                            Total
                                        </th>

                                    </tr>

                                </thead>


                                <tbody
                                    class="divide-y divide-slate-100"
                                >

                                    <tr
                                        v-for="(item, index) in cart"
                                        :key="`${item.product_variant_id}-${item.unit_id}`"
                                        class="cursor-pointer transition hover:bg-slate-50"
                                        :class="{
                                            'bg-blue-50/70':
                                                selectedProduct === item
                                        }"
                                        @click="
                                            selectedProduct = item
                                        "
                                    >

                                        <!-- NO -->
                                        <td
                                            class="px-4 py-4 text-center text-xs font-medium text-slate-400"
                                        >
                                            {{ index + 1 }}
                                        </td>


                                        <!-- PRODUCT -->
                                        <td
                                            class="px-4 py-4"
                                        >

                                            <div
                                                class="font-semibold text-slate-800"
                                            >
                                                {{
                                                    item.product_name
                                                }}

                                                <span
                                                    v-if="
                                                        item.variant_name &&
                                                        item.variant_name !== 'Default'
                                                    "
                                                    class="font-normal text-slate-500"
                                                >
                                                    {{ item.variant_name }}
                                                </span>
                                            </div>

                                        </td>


                                        <!-- QTY -->
                                        <td
                                            class="px-4 py-4"
                                        >

                                            <div
                                                class="flex items-center justify-center"
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
                                                        class="w-10 text-center text-xs font-semibold text-slate-800"
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

                                            </div>

                                        </td>


                                        <!-- DISCOUNT -->
                                        <td
                                            class="px-4 py-4 text-right"
                                        >

                                            <button
                                                type="button"
                                                class="text-xs font-medium transition"
                                                :class="
                                                    item.discount_amount > 0
                                                        ? 'text-orange-600 hover:text-orange-700'
                                                        : 'text-slate-400 hover:text-orange-500'
                                                "
                                                @click.stop="
                                                    openDiscount(item)
                                                "
                                            >

                                                {{
                                                    item.discount_amount > 0
                                                        ? `-${formatCurrency(item.discount_amount)}`
                                                        : 'Discount'
                                                }}

                                            </button>

                                        </td>


                                        <!-- TOTAL -->
                                        <td
                                            class="px-4 py-4 text-right"
                                        >

                                            <div
                                                class="font-semibold text-slate-900"
                                            >
                                                {{
                                                    formatCurrency(
                                                        item.subtotal
                                                    )
                                                }}
                                            </div>

                                        </td>

                                    </tr>

                                </tbody>

                            </table>


                            <!-- EMPTY -->
                            <div
                                v-else
                                class="flex min-h-[380px] items-center justify-center px-6"
                            >

                                <div class="text-center">

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
                                        Transaction is empty
                                    </div>


                                    <div
                                        class="mt-1 text-xs text-slate-400"
                                    >
                                        Scan barcode atau cari product untuk memulai transaksi.
                                    </div>

                                </div>

                            </div>

                        </div>


                        <!-- TABLE FOOTER -->
                        <div
                            v-if="cart.length"
                            class="flex items-center justify-between border-t border-slate-200 bg-slate-50/70 px-4 py-3"
                        >

                            <div
                                class="text-xs text-slate-500"
                            >
                                <span class="font-semibold text-slate-700">
                                    F8
                                </span>
                                untuk discount
                            </div>


                            <button
                                type="button"
                                class="text-xs font-semibold text-red-500 hover:text-red-600"
                                @click="clearCart"
                            >
                                Clear Transaction
                            </button>

                        </div>

                    </div>

                </section>


                <!-- RIGHT / CURRENT SALE -->
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


                        <!-- TRANSACTION DATE SUMMARY -->
                        <div
                            class="border-b border-slate-100 bg-blue-50/50 px-4 py-3"
                        >

                            <div
                                class="text-[11px] font-medium uppercase tracking-wide text-slate-400"
                            >
                                Transaction Date
                            </div>

                            <div
                                class="mt-1 text-sm font-bold text-slate-800"
                            >
                                {{ formattedTransactionDate }}
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

                                    <span>
                                        Subtotal
                                    </span>

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

                                    <span>
                                        Discount
                                    </span>

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
                                    !sessionOpen ||
                                    !transactionDate
                                "
                                class="mt-4 flex h-12 w-full items-center justify-between rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-slate-300"
                                @click="openPayment"
                            >

                                <span>
                                    PAYMENT
                                </span>

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


        <!-- PRODUCT SEARCH MODAL -->
        <ProductSearchModal
            :show="showProductSearchModal"
            :products="filteredProducts"
            :search="search"
            @close="closeProductSearch"
            @select="selectProductFromSearch"
        />


        <!-- VARIANT MODAL -->
        <VariantModal
            :show="showVariantModal"
            :product="selectedProductForVariant"
            @close="closeVariantModal"
            @select="selectVariant"
        />


        <!-- CUSTOMER MODAL -->
        <CustomerModal
            :show="showCustomerModal"
            :customers="customers"
            @close="closeCustomer"
            @select="selectCustomer"
            @clear="clearCustomer"
        />


        <!-- DISCOUNT MODAL -->
        <DiscountModal
            :show="!!discountModalItem"
            :item="discountModalItem"
            @close="closeDiscount"
            @apply="applyDiscount"
        />


        <!-- PAYMENT MODAL -->
        <PaymentModal
            :show="showPaymentModal"
            :total="grandTotal"
            :processing="processing"
            @close="closePayment"
            @submit="submitPayment"
        />

    </div>
</template>