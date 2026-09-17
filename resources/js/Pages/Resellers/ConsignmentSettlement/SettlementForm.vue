<script setup>

import FormField from '@/Components/Form/FormField.vue'
import FormInput from '@/Components/Form/FormInput.vue'
import FormTextarea from '@/Components/Form/FormTextarea.vue'
import BaseButton from '@/Components/Button/BaseButton.vue'
import SearchableSelect from '@/Components/Form/SearchableSelect.vue'
import Swal from 'sweetalert2'
import {TrashIcon,PlusIcon,} from '@heroicons/vue/24/outline'
import FlatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'
import { computed, onMounted, watch } from 'vue'

const props = defineProps({


    form: {
        type: Object,
        required: true,
    },

    branches: {
        type: Array,
        default: () => [],
    },

    filteredWarehouses: {
        type: Array,
        default: () => [],
    },

    resellers: {
        type: Array,
        default: () => [],
    },

    resellerPrices: {
        type: Array,
        default: () => [],
    },

    filteredVariants: {
        type: Array,
        default: () => [],
    },

    consignmentStocks: {
    type: Array,
    default: () => [],
    },

})

console.log(
    'CREATE CONSIGNMENT STOCKS:',
    props.consignmentStocks
)
const form = props.form


/*
|--------------------------------------------------------------------------
| Emits
|--------------------------------------------------------------------------
*/

const emit = defineEmits([
    'submit',
    'cancel',
])


/*
|--------------------------------------------------------------------------
| Date Configuration
|--------------------------------------------------------------------------
*/

const dateConfig = {
    dateFormat: 'Y-m-d',
    allowInput: true,
}


/*
|--------------------------------------------------------------------------
| Default Dates
|--------------------------------------------------------------------------
*/

const getToday = () => {

    const date = new Date()

    const year =
        date.getFullYear()

    const month =
        String(
            date.getMonth() + 1
        ).padStart(
            2,
            '0'
        )

    const day =
        String(
            date.getDate()
        ).padStart(
            2,
            '0'
        )

    return `${year}-${month}-${day}`

}


const getFirstDayOfMonth = () => {

    const date = new Date()

    const year =
        date.getFullYear()

    const month =
        String(
            date.getMonth() + 1
        ).padStart(
            2,
            '0'
        )

    return `${year}-${month}-01`

}


/*
|--------------------------------------------------------------------------
| Initialize Dates
|--------------------------------------------------------------------------
*/

onMounted(() => {

    if (!form.settlement_date) {

        form.settlement_date =
            getToday()

    }


    if (!form.period_from) {

        form.period_from =
            getFirstDayOfMonth()

    }


    if (!form.period_to) {

        form.period_to =
            getToday()

    }

})


/*
|--------------------------------------------------------------------------
| Empty Detail
|--------------------------------------------------------------------------
*/

const createEmptyDetail = () => ({

    product_variant_id:
        null,

    unit_id:
        null,

    qty_sold:
        1,

    unit_price:
        0,

    total_amount:
        0,

})


/*
|--------------------------------------------------------------------------
| Add Detail
|--------------------------------------------------------------------------
*/

const addDetail = () => {

    form.details.push(
        createEmptyDetail()
    )

}


/*
|--------------------------------------------------------------------------
| Remove Detail
|--------------------------------------------------------------------------
*/

const removeDetail = (
    index
) => {

    if (
        form.details.length <= 1
    ) {

        return

    }

    form.details.splice(
        index,
        1
    )

}


/*
|--------------------------------------------------------------------------
| Available Variants
|--------------------------------------------------------------------------
*/

const availableVariants = computed(() => {

    if (!form.reseller_id) {

        return []

    }


    const productIds =
        props.resellerPrices
            .filter(
                price =>
                    Number(
                        price.reseller_id
                    ) ===
                    Number(
                        form.reseller_id
                    )
            )
            .map(
                price =>
                    Number(
                        price.product_id
                    )
            )


    return props.filteredVariants.filter(
        variant =>
            productIds.includes(
                Number(
                    variant.product_id
                )
            )
    )

})


/*
|--------------------------------------------------------------------------
| Get Variant
|--------------------------------------------------------------------------
*/

const getVariant = (
    variantId
) => {

    return availableVariants.value.find(
        variant =>
            Number(
                variant.id
            ) ===
            Number(
                variantId
            )
    )

}


/*
|--------------------------------------------------------------------------
| Variant Units
|--------------------------------------------------------------------------
*/

const getUnitsForVariant = (
    variantId
) => {

    if (!variantId) {

        return []

    }


    const variant =
        getVariant(
            variantId
        )


    return variant?.units ?? []

}


/*
|--------------------------------------------------------------------------
| Reseller Price
|--------------------------------------------------------------------------
*/

const getResellerPrice = (
    productId
) => {

    const price =
        props.resellerPrices.find(

            item =>

                Number(
                    item.reseller_id
                ) ===
                Number(
                    form.reseller_id
                )

                &&

                Number(
                    item.product_id
                ) ===
                Number(
                    productId
                )

        )


    return Number(
        price?.price || 0
    )

}
/*
|--------------------------------------------------------------------------
| Variant Changed
|--------------------------------------------------------------------------
*/

const changeVariant = (detail) => {

    if (
        !detail.product_variant_id
    ) {

        detail.unit_id =
            null

        detail.unit_price =
            0

        calculateDetail(
            detail
        )

        return

    }


    const variant =
        getVariant(
            detail.product_variant_id
        )


    if (!variant) {

        detail.product_variant_id =
            null

        detail.unit_id =
            null

        detail.unit_price =
            0

        calculateDetail(
            detail
        )

        return

    }


    /*
    |--------------------------------------------------------------------------
    | Prevent Duplicate Product
    |--------------------------------------------------------------------------
    */

    const duplicate =
        form.details.some(
            item =>
                item !== detail
                &&
                Number(
                    item.product_variant_id
                ) ===
                Number(
                    variant.id
                )
        )


    if (duplicate) {

        Swal.fire({

            icon: 'warning',

            title:
                'Product Already Added',

            text:
                'This product has already been added to the settlement.',

            confirmButtonText:
                'OK',

        }).then(() => {

            const index =
                form.details.indexOf(
                    detail
                )


            if (
                index !== -1
            ) {

                form.details.splice(
                    index,
                    1
                )

            }

        })

        return

    }


    /*
    |--------------------------------------------------------------------------
    | Resolve Default Unit
    |--------------------------------------------------------------------------
    */

    const units =
        variant.units ?? []


    const defaultUnit =
        units.find(
            unit =>
                unit.is_default
        )
        ??
        units[0]


    detail.unit_id =
        defaultUnit?.id ??
        null


    /*
    |--------------------------------------------------------------------------
    | Resolve Reseller Price
    |--------------------------------------------------------------------------
    */

    detail.unit_price =
        getResellerPrice(
            variant.product_id
        )


    /*
    |--------------------------------------------------------------------------
    | Recalculate Detail
    |--------------------------------------------------------------------------
    */

    calculateDetail(
        detail
    )

}
/*
|--------------------------------------------------------------------------
| Calculate Detail
|--------------------------------------------------------------------------
*/

const calculateDetail = (
    detail
) => {

    const qty =
        Number(
            detail.qty_sold || 0
        )


    const unitPrice =
        Number(
            detail.unit_price || 0
        )


    detail.total_amount =
        Number(
            (
                qty *
                unitPrice
            ).toFixed(2)
        )

}


/*
|--------------------------------------------------------------------------
| Product Name
|--------------------------------------------------------------------------
*/

const getProductName = (
    detail
) => {

    const variant =
        getVariant(
            detail.product_variant_id
        )


    return (
        variant?.product?.name ||
        variant?.name ||
        '-'
    )

}


/*
|--------------------------------------------------------------------------
| SKU
|--------------------------------------------------------------------------
*/
const getSku = (
    detail
) => {

    const variant =
        getVariant(
            detail.product_variant_id
        )

    return variant?.sku || '-'

}
/*
|--------------------------------------------------------------------------
| Stock
|--------------------------------------------------------------------------
|
| Current formData variants may not contain stock quantities.
| Backend remains the source of truth.
|--------------------------------------------------------------------------
*/
const getAvailableStock = (
    detail
) => {

    if (
        !detail.product_variant_id ||
        !detail.unit_id ||
        !form.reseller_id ||
        !form.branch_id ||
        !form.warehouse_id
    ) {

        return 0

    }


    const stock =
        props.consignmentStocks.find(

            item =>

                Number(
                    item.reseller_id
                ) ===
                Number(
                    form.reseller_id
                )

                &&

                Number(
                    item.branch_id
                ) ===
                Number(
                    form.branch_id
                )

                &&

                Number(
                    item.warehouse_id
                ) ===
                Number(
                    form.warehouse_id
                )

                &&

                Number(
                    item.product_variant_id
                ) ===
                Number(
                    detail.product_variant_id
                )

                &&

                Number(
                    item.unit_id
                ) ===
                Number(
                    detail.unit_id
                )

        )


    return Number(
        stock?.available_qty || 0
    )

}


/*
|--------------------------------------------------------------------------
| Detail Total
|--------------------------------------------------------------------------
*/

const getDetailTotal = (
    detail
) => {

    return Number(
        detail.qty_sold || 0
    ) *
    Number(
        detail.unit_price || 0
    )

}


/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

const totalItems = computed(() => {

    return form.details.length

})


const totalQuantity = computed(() => {

    return form.details.reduce(

        (
            total,
            detail
        ) =>

            total +
            Number(
                detail.qty_sold || 0
            ),

        0

    )

})


const totalValue = computed(() => {

    return form.details.reduce(

        (
            total,
            detail
        ) =>

            total +
            getDetailTotal(
                detail
            ),

        0

    )

})


/*
|--------------------------------------------------------------------------
| Payment
|--------------------------------------------------------------------------
*/

const paymentAmount = computed({

    get() {

        return Number(
            form.payment_amount || 0
        )

    },

    set(value) {

        form.payment_amount =
            Number(
                value || 0
            )

    },

})


const receivableAmount = computed(() => {

    const total =
        Number(
            totalValue.value || 0
        )


    const payment =
        Number(
            paymentAmount.value || 0
        )


    return Math.max(
        total - payment,
        0
    )

})


const paymentStatus = computed(() => {

    const total =
        Number(
            totalValue.value || 0
        )


    const payment =
        Number(
            paymentAmount.value || 0
        )


    if (
        payment >= total
    ) {

        return 'Paid'

    }


    return 'Receivable'

})


/*
|--------------------------------------------------------------------------
| Payment Validation
|--------------------------------------------------------------------------
*/

const paymentExceedsTotal = computed(() => {

    return (
        paymentAmount.value >
        totalValue.value
    )

})


/*
|--------------------------------------------------------------------------
| Watch Payment
|--------------------------------------------------------------------------
*/

watch(
    totalValue,
    (value) => {

        if (
            paymentAmount.value >
            Number(value || 0)
        ) {

            form.payment_amount =
                Number(value || 0)

        }

    }
)


/*
|--------------------------------------------------------------------------
| Currency
|--------------------------------------------------------------------------
*/

const formatCurrency = (
    value
) => {

    return new Intl.NumberFormat(
        'id-ID',
        {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }
    ).format(
        Number(
            value || 0
        )
    )

}


/*
|--------------------------------------------------------------------------
| Number
|--------------------------------------------------------------------------
*/

const formatNumber = (
    value
) => {

    return new Intl.NumberFormat(
        'id-ID',
        {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2,
        }
    ).format(
        Number(
            value || 0
        )
    )

}


/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/

const handleSubmit = () => {

    if (
        paymentExceedsTotal.value
    ) {

        return

    }


    emit('submit')

}

</script>


<template>

    <form
        @submit.prevent="handleSubmit"
        class="space-y-6"
    >

        <!-- ========================================================= -->
        <!-- Header -->
        <!-- ========================================================= -->

        <div
            class="
                rounded-xl
                border
                border-gray-200
                bg-white
                px-5
                py-4
            "
        >

            <div
                class="
                    flex
                    flex-col
                    gap-1
                    sm:flex-row
                    sm:items-center
                    sm:justify-between
                "
            >

                <div>

                    <h1
                        class="
                            text-lg
                            font-semibold
                            tracking-tight
                            text-gray-900
                        "
                    >
                        Consignment Settlement
                    </h1>

                    <p
                        class="
                            text-sm
                            text-gray-500
                        "
                    >
                        Record reseller sales and settlement payment.
                    </p>

                </div>


                <div
                    class="
                        text-left
                        sm:text-right
                    "
                >

                    <div
                        class="
                            text-[11px]
                            font-medium
                            uppercase
                            tracking-wider
                            text-gray-400
                        "
                    >
                        Settlement No.
                    </div>

                    <div
                        class="
                            mt-0.5
                            text-base
                            font-semibold
                            text-gray-800
                        "
                    >
                        {{ form.settlement_number || 'Auto Generated' }}
                    </div>

                </div>

            </div>

        </div>


        <!-- ========================================================= -->
        <!-- Transaction Information -->
        <!-- ========================================================= -->

        <div
            class="
                rounded-xl
                border
                border-gray-200
                bg-white
                p-5
            "
        >

            <div
                class="
                    grid
                    grid-cols-1
                    gap-x-8
                    gap-y-5
                    lg:grid-cols-2
                "
            >

                <!-- ================================================= -->
                <!-- LEFT -->
                <!-- ================================================= -->

                <div class="space-y-5">

                    <!-- Reseller -->

                    <FormField
                        label="Reseller"
                        required
                        :error="
                            form.errors.reseller_id
                        "
                    >

                        <SearchableSelect
                            v-model="
                                form.reseller_id
                            "
                            :options="
                                resellers
                            "
                            label="label"
                            value-key="id"
                            placeholder="Select reseller"
                        />

                    </FormField>


                    <!-- Settlement Date -->

                    <FormField
                        label="Settlement Date"
                        required
                        :error="
                            form.errors.settlement_date
                        "
                    >

                        <FlatPickr
                            v-model="
                                form.settlement_date
                            "
                            :config="
                                dateConfig
                            "
                            class="
                                w-full
                                rounded-lg
                                border
                                border-gray-300
                                bg-white
                                px-3
                                py-2
                                text-sm
                                text-gray-800
                                outline-none
                                transition
                                focus:border-blue-500
                                focus:ring-1
                                focus:ring-blue-500
                            "
                        />

                    </FormField>


                    <!-- Settlement Period -->

                    <div>

                        <label
                            class="
                                mb-1.5
                                block
                                text-sm
                                font-medium
                                text-gray-700
                            "
                        >
                            Settlement Period
                        </label>


                        <div
                            class="
                                grid
                                grid-cols-1
                                gap-2
                                sm:grid-cols-2
                            "
                        >

                            <FormField
                                label="From"
                                :error="
                                    form.errors.period_from
                                "
                            >

                                <FlatPickr
                                    v-model="
                                        form.period_from
                                    "
                                    :config="
                                        dateConfig
                                    "
                                    class="
                                        w-full
                                        rounded-lg
                                        border
                                        border-gray-300
                                        bg-white
                                        px-3
                                        py-2
                                        text-sm
                                        text-gray-800
                                        outline-none
                                        transition
                                        focus:border-blue-500
                                        focus:ring-1
                                        focus:ring-blue-500
                                    "
                                />

                            </FormField>


                            <FormField
                                label="To"
                                :error="
                                    form.errors.period_to
                                "
                            >

                                <FlatPickr
                                    v-model="
                                        form.period_to
                                    "
                                    :config="
                                        dateConfig
                                    "
                                    class="
                                        w-full
                                        rounded-lg
                                        border
                                        border-gray-300
                                        bg-white
                                        px-3
                                        py-2
                                        text-sm
                                        text-gray-800
                                        outline-none
                                        transition
                                        focus:border-blue-500
                                        focus:ring-1
                                        focus:ring-blue-500
                                    "
                                />

                            </FormField>

                        </div>

                    </div>

                </div>


                <!-- ================================================= -->
                <!-- RIGHT -->
                <!-- ================================================= -->

                <div class="space-y-5">

                    <!-- Branch -->

                    <FormField
                        label="Branch"
                        required
                        :error="
                            form.errors.branch_id
                        "
                    >

                        <SearchableSelect
                            v-model="
                                form.branch_id
                            "
                            :options="
                                branches
                            "
                            label="label"
                            value-key="id"
                            placeholder="Select branch"
                        />

                    </FormField>


                    <!-- Warehouse -->

                    <FormField
                        label="Warehouse"
                        required
                        :error="
                            form.errors.warehouse_id
                        "
                    >

                        <SearchableSelect
                            v-model="
                                form.warehouse_id
                            "
                            :options="
                                filteredWarehouses
                            "
                            label="label"
                            value-key="id"
                            placeholder="Select warehouse"
                        />

                    </FormField>

                </div>

            </div>

        </div>


        <!-- ========================================================= -->
        <!-- Settlement Items -->
        <!-- ========================================================= -->

        <div
            class="
                rounded-xl
                border
                border-gray-200
                bg-white
                p-5
            "
        >

            <div
                class="
                    mb-4
                    flex
                    flex-col
                    gap-1
                    sm:flex-row
                    sm:items-center
                    sm:justify-between
                "
            >

                <div>

                    <h2
                        class="
                            text-sm
                            font-semibold
                            text-gray-900
                        "
                    >
                        Settlement Items
                    </h2>

                    <p
                        class="
                            text-xs
                            text-gray-500
                        "
                    >
                        Record products sold by the reseller.
                    </p>

                </div>


                <div
                    class="
                        text-xs
                        text-gray-500
                    "
                >

                    {{ totalItems }} items ·
                    {{ formatNumber(totalQuantity) }} qty

                </div>

            </div>


            <!-- No reseller -->

            <div
                v-if="
                    !form.reseller_id
                "
                class="
                    rounded-lg
                    border
                    border-dashed
                    border-gray-300
                    bg-gray-50
                    px-5
                    py-10
                    text-center
                "
            >

                <div
                    class="
                        text-sm
                        font-medium
                        text-gray-600
                    "
                >
                    Select a reseller first
                </div>

                <div
                    class="
                        mt-1
                        text-xs
                        text-gray-400
                    "
                >
                    Available consignment products will appear here.
                </div>

            </div>


            <!-- Details -->

            <div
                v-else
                class="
                    overflow-x-auto
                    rounded-lg
                    border
                    border-gray-200
                "
            >

                <div
                    class="
                        min-w-[1080px]
                    "
                >

                    <!-- Table Header -->

                    <div
                        class="
                            grid
                            grid-cols-[minmax(280px,2fr)_110px_100px_110px_130px_150px_55px]
                            items-center
                            gap-2
                            border-b
                            border-gray-200
                            bg-gray-50
                            px-3
                            py-2.5
                            text-[11px]
                            font-semibold
                            uppercase
                            tracking-wide
                            text-gray-500
                        "
                    >

                        <div>Product</div>
                        <div>Unit</div>
                        <div class="text-right">Stock</div>
                        <div class="text-right">Qty Sold</div>
                        <div class="text-right">Price</div>
                        <div class="text-right">Amount</div>
                        <div></div>

                    </div>


                    <!-- Rows -->

                    <div
                        v-for="(
                            detail,
                            index
                        ) in form.details"

                        :key="index"

                        class="
                            grid
                            grid-cols-[minmax(280px,2fr)_110px_100px_110px_130px_150px_55px]
                            items-start
                            gap-2
                            border-b
                            border-gray-100
                            px-3
                            py-3
                            last:border-b-0
                        "
                    >

                        <!-- Product -->

                        <FormField
                            label="Product"
                            required
                            :error="
                                form.errors[
                                    `details.${index}.product_variant_id`
                                ]
                            "
                        >

                            <SearchableSelect
                                v-model="
                                    detail.product_variant_id
                                "
                                :options="
                                    availableVariants
                                "
                                label="label"
                                value-key="id"
                                placeholder="Search product"
                                @update:modelValue="
                                    changeVariant(detail)
                                "
                            />

                            <div
                                v-if="
                                    detail.product_variant_id
                                "
                                class="
                                    mt-1
                                    text-[11px]
                                    text-gray-400
                                "
                            >
                                SKU:
                                {{ getSku(detail) }}
                            </div>

                        </FormField>


                        <!-- Unit -->

                        <FormField
                            label="Unit"
                            required
                            :error="
                                form.errors[
                                    `details.${index}.unit_id`
                                ]
                            "
                        >

                            <SearchableSelect
                                v-model="
                                    detail.unit_id
                                "
                                :options="
                                    getUnitsForVariant(
                                        detail.product_variant_id
                                    )
                                "
                                label="label"
                                value-key="id"
                                placeholder="Unit"
                            />

                        </FormField>


                        <!-- Stock -->

                        <FormField
                            label="Available"
                        >

                            <div
                                class="
                                    flex
                                    min-h-[38px]
                                    items-center
                                    justify-end
                                    rounded-lg
                                    border
                                    border-gray-200
                                    bg-gray-50
                                    px-2
                                    text-sm
                                    font-medium
                                    text-gray-700
                                "
                            >
                                {{ formatNumber(
                                    getAvailableStock(detail)
                                ) }}
                            </div>

                        </FormField>


                        <!-- Qty -->

                        <FormField
                            label="Qty"
                            required
                            :error="
                                form.errors[
                                    `details.${index}.qty_sold`
                                ]
                            "
                        >

                            <FormInput
                                v-model="
                                    detail.qty_sold
                                "
                                type="number"
                                min="0"
                                step="0.01"
                                placeholder="0"
                                @input="
                                    calculateDetail(detail)
                                "
                            />

                        </FormField>


                        <!-- Price -->

                        <FormField
                            label="Price"
                        >

                            <div
                                class="
                                    flex
                                    min-h-[38px]
                                    items-center
                                    justify-end
                                    rounded-lg
                                    border
                                    border-gray-200
                                    bg-gray-50
                                    px-2
                                    text-sm
                                    font-medium
                                    text-gray-700
                                    whitespace-nowrap
                                "
                            >

                                {{
                                    formatNumber(
                                        detail.unit_price
                                    )
                                }}

                            </div>

                        </FormField>


                        <!-- Amount -->

                        <FormField
                            label="Amount"
                        >

                            <div
                                class="
                                    flex
                                    min-h-[38px]
                                    items-center
                                    justify-end
                                    rounded-lg
                                    border
                                    border-gray-200
                                    bg-gray-50
                                    px-2
                                    text-sm
                                    font-semibold
                                    text-gray-800
                                    whitespace-nowrap
                                "
                            >

                                {{
                                    formatCurrency(
                                        detail.total_amount
                                    )
                                }}

                            </div>

                        </FormField>


                        <!-- Remove -->

                        <div
                            class="
                                flex
                                min-h-[38px]
                                items-center
                                justify-center
                            "
                        >

                            <button
                                v-if="
                                    form.details.length > 1
                                "
                                type="button"
                                class="
                                    inline-flex
                                    h-9
                                    w-9
                                    items-center
                                    justify-center
                                    rounded-lg
                                    text-gray-400
                                    transition
                                    hover:bg-red-50
                                    hover:text-red-600
                                    focus:outline-none
                                    focus:ring-2
                                    focus:ring-red-500
                                "
                                title="Remove item"
                                @click="
                                    removeDetail(index)
                                "
                            >

                                <TrashIcon
                                    class="h-4.5 w-4.5"
                                />

                            </button>

                        </div>

                    </div>

                </div>

            </div>


            <!-- Add Product -->

            <div
                v-if="
                    form.reseller_id
                "
                class="
                    mt-4
                    flex
                    justify-start
                "
            >

                <BaseButton
                    type="button"
                    variant="secondary"
                    @click="addDetail"
                >

                    <PlusIcon
                        class="mr-1.5 h-4 w-4"
                    />

                    Add Product

                </BaseButton>

            </div>

        </div>


        <!-- ========================================================= -->
        <!-- Bottom -->
        <!-- ========================================================= -->

        <div
            class="
                grid
                grid-cols-1
                gap-6
                lg:grid-cols-[1fr_380px]
            "
        >

            <!-- Remarks -->

            <div
                class="
                    rounded-xl
                    border
                    border-gray-200
                    bg-white
                    p-5
                "
            >

                <h2
                    class="
                        mb-3
                        text-sm
                        font-semibold
                        text-gray-900
                    "
                >
                    Remarks
                </h2>


                <FormTextarea
                    v-model="
                        form.remarks
                    "
                    :rows="5"
                    placeholder="Add remarks or notes..."
                />


                <p
                    v-if="
                        form.errors.remarks
                    "
                    class="
                        mt-1
                        text-xs
                        text-red-600
                    "
                >
                    {{ form.errors.remarks }}
                </p>

            </div>


            <!-- Summary -->

            <div
                class="
                    rounded-xl
                    border
                    border-gray-200
                    bg-white
                    p-5
                "
            >

                <h2
                    class="
                        mb-4
                        text-sm
                        font-semibold
                        text-gray-900
                    "
                >
                    Settlement Summary
                </h2>


                <!-- Items -->

                <div
                    class="
                        flex
                        justify-between
                        py-1.5
                        text-sm
                    "
                >

                    <span class="text-gray-500">
                        Total Items
                    </span>

                    <span
                        class="
                            font-medium
                            text-gray-800
                        "
                    >
                        {{ totalItems }}
                    </span>

                </div>


                <!-- Quantity -->

                <div
                    class="
                        flex
                        justify-between
                        py-1.5
                        text-sm
                    "
                >

                    <span class="text-gray-500">
                        Total Quantity
                    </span>

                    <span
                        class="
                            font-medium
                            text-gray-800
                        "
                    >
                        {{ formatNumber(
                            totalQuantity
                        ) }}
                    </span>

                </div>


                <!-- Total -->

                <div
                    class="
                        mt-2
                        flex
                        justify-between
                        border-t
                        border-gray-100
                        pt-4
                    "
                >

                    <span
                        class="
                            text-sm
                            font-medium
                            text-gray-600
                        "
                    >
                        Total
                    </span>

                    <span
                        class="
                            text-lg
                            font-semibold
                            text-gray-900
                        "
                    >
                        {{ formatCurrency(
                            totalValue
                        ) }}
                    </span>

                </div>


                <!-- Payment -->

                <div class="mt-5">

                    <FormField
                        label="Payment"
                        required
                        :error="
                            form.errors.payment_amount
                        "
                    >

                        <FormInput
                            v-model="
                                paymentAmount
                            "
                            type="number"
                            min="0"
                            step="0.01"
                            placeholder="0"
                        />

                    </FormField>

                </div>


                <!-- Payment exceeds -->

                <div
                    v-if="
                        paymentExceedsTotal
                    "
                    class="
                        mt-2
                        rounded-lg
                        border
                        border-red-200
                        bg-red-50
                        px-3
                        py-2
                        text-xs
                        text-red-600
                    "
                >

                    Payment cannot be greater than
                    settlement total.

                </div>


                <!-- Receivable -->

                <div
                    class="
                        mt-4
                        flex
                        justify-between
                        border-t
                        border-gray-100
                        pt-3
                        text-sm
                    "
                >

                    <span class="text-gray-500">
                        Receivable
                    </span>

                    <span
                        class="
                            font-semibold
                            text-gray-800
                        "
                    >
                        {{ formatCurrency(
                            receivableAmount
                        ) }}
                    </span>

                </div>


                <!-- Status -->

                <div
                    class="
                        mt-3
                        flex
                        items-center
                        justify-between
                    "
                >

                    <span
                        class="
                            text-sm
                            text-gray-500
                        "
                    >
                        Payment Status
                    </span>


                    <span
                        :class="
                            paymentStatus === 'Paid'
                                ? `
                                    inline-flex
                                    rounded-full
                                    bg-emerald-50
                                    px-2.5
                                    py-1
                                    text-xs
                                    font-medium
                                    text-emerald-700
                                `
                                : `
                                    inline-flex
                                    rounded-full
                                    bg-amber-50
                                    px-2.5
                                    py-1
                                    text-xs
                                    font-medium
                                    text-amber-700
                                `
                        "
                    >

                        {{
                            paymentStatus === 'Paid'
                                ? 'Paid'
                                : 'Receivable'
                        }}

                    </span>

                </div>


                <!-- Error -->

                <div
                    v-if="
                        form.errors.details
                    "
                    class="
                        mt-4
                        rounded-lg
                        border
                        border-red-200
                        bg-red-50
                        px-3
                        py-2
                        text-xs
                        text-red-600
                    "
                >

                    {{ form.errors.details }}

                </div>

            </div>

        </div>


        <!-- ========================================================= -->
        <!-- Actions -->
        <!-- ========================================================= -->

        <div
            class="
                flex
                flex-col-reverse
                gap-3
                border-t
                border-gray-200
                pt-5
                sm:flex-row
                sm:justify-end
            "
        >

            <BaseButton
                type="button"
                variant="secondary"
                :disabled="
                    form.processing
                "
                @click="
                    emit('cancel')
                "
            >

                Cancel

            </BaseButton>


            <BaseButton
                type="submit"
                :loading="
                    form.processing
                "
                :disabled="
                    paymentExceedsTotal ||
                    !form.reseller_id ||
                    !form.branch_id ||
                    !form.warehouse_id
                "
            >

                Save &amp; Post

            </BaseButton>

        </div>

    </form>

</template>