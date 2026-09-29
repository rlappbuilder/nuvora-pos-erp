<script setup>
import { computed, ref, watch } from 'vue'

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },

    item: {
        type: Object,
        default: null,
    },
})

const emit = defineEmits([
    'close',
    'apply',
])

/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const discountType = ref('percentage')
const discountValue = ref('')

/*
|--------------------------------------------------------------------------
| Currency
|--------------------------------------------------------------------------
*/

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(Number(value ?? 0))
}

/*
|--------------------------------------------------------------------------
| Item Total
|--------------------------------------------------------------------------
*/

const grossTotal = computed(() => {
    if (!props.item) {
        return 0
    }

    return (
        Number(props.item.qty ?? 0) *
        Number(props.item.unit_price ?? 0)
    )
})

/*
|--------------------------------------------------------------------------
| Discount
|--------------------------------------------------------------------------
*/

const calculatedDiscount = computed(() => {
    const gross = grossTotal.value
    const value = Number(discountValue.value) || 0

    if (gross <= 0 || value <= 0) {
        return 0
    }

    if (discountType.value === 'percentage') {
        return Math.min(
            gross,
            gross * (value / 100)
        )
    }

    return Math.min(
        gross,
        value
    )
})

const finalTotal = computed(() => {
    return Math.max(
        0,
        grossTotal.value -
            calculatedDiscount.value
    )
})

/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

const isValid = computed(() => {
    const value =
        Number(discountValue.value) || 0

    if (value <= 0) {
        return false
    }

    if (
        discountType.value ===
        'percentage'
    ) {
        return value <= 100
    }

    return value <= grossTotal.value
})

/*
|--------------------------------------------------------------------------
| Reset
|--------------------------------------------------------------------------
*/

watch(
    () => props.show,
    (value) => {
        if (!value) {
            return
        }

        discountType.value =
            props.item?.discount_type ??
            'percentage'

        discountValue.value =
            props.item?.discount_value ??
            ''
    }
)

/*
|--------------------------------------------------------------------------
| Type
|--------------------------------------------------------------------------
*/

const setDiscountType = (type) => {
    discountType.value = type
    discountValue.value = ''
}

/*
|--------------------------------------------------------------------------
| Apply
|--------------------------------------------------------------------------
*/

const apply = () => {
    if (!isValid.value) {
        return
    }

    emit('apply', {
        type: discountType.value,
        value: Number(
            discountValue.value
        ),
    })
}

const close = () => {
    emit('close')
}
</script>

<template>
    <Teleport to="body">

        <div
            v-if="show"
            class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 p-4"
            @click.self="close"
        >

            <div
                class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl"
            >

                <!-- HEADER -->
                <div
                    class="flex items-center justify-between border-b border-slate-100 px-5 py-4"
                >

                    <div class="min-w-0">

                        <h2
                            class="text-base font-semibold text-slate-900"
                        >
                            Discount
                        </h2>

                        <p
                            class="mt-1 truncate text-sm text-slate-500"
                        >
                            {{ item?.product_name ?? 'Product' }}
                        </p>

                    </div>

                    <button
                        type="button"
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700"
                        @click="close"
                    >
                        ×
                    </button>

                </div>


                <!-- BODY -->
                <div class="space-y-5 p-5">

                    <!-- ITEM INFO -->
                    <div
                        class="rounded-xl bg-slate-50 px-4 py-3"
                    >

                        <div
                            class="flex items-center justify-between"
                        >
                            <span
                                class="text-xs text-slate-500"
                            >
                                Qty
                            </span>

                            <span
                                class="text-sm font-semibold text-slate-800"
                            >
                                {{ item?.qty ?? 0 }}
                            </span>
                        </div>

                        <div
                            class="mt-2 flex items-center justify-between"
                        >
                            <span
                                class="text-xs text-slate-500"
                            >
                                Unit Price
                            </span>

                            <span
                                class="text-sm font-semibold text-slate-800"
                            >
                                {{ formatCurrency(item?.unit_price) }}
                            </span>
                        </div>

                        <div
                            class="mt-2 flex items-center justify-between"
                        >
                            <span
                                class="text-xs text-slate-500"
                            >
                                Gross Total
                            </span>

                            <span
                                class="text-sm font-semibold text-slate-800"
                            >
                                {{ formatCurrency(grossTotal) }}
                            </span>
                        </div>

                    </div>


                    <!-- TYPE -->
                    <div>

                        <div
                            class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500"
                        >
                            Discount Type
                        </div>

                        <div
                            class="grid grid-cols-2 gap-2"
                        >

                            <button
                                type="button"
                                class="h-10 rounded-lg border text-sm font-semibold transition"
                                :class="
                                    discountType === 'percentage'
                                        ? 'border-blue-500 bg-blue-50 text-blue-600'
                                        : 'border-slate-200 bg-white text-slate-600 hover:border-blue-300'
                                "
                                @click="
                                    setDiscountType(
                                        'percentage'
                                    )
                                "
                            >
                                Percentage
                            </button>

                            <button
                                type="button"
                                class="h-10 rounded-lg border text-sm font-semibold transition"
                                :class="
                                    discountType === 'nominal'
                                        ? 'border-blue-500 bg-blue-50 text-blue-600'
                                        : 'border-slate-200 bg-white text-slate-600 hover:border-blue-300'
                                "
                                @click="
                                    setDiscountType(
                                        'nominal'
                                    )
                                "
                            >
                                Nominal
                            </button>

                        </div>

                    </div>


                    <!-- VALUE -->
                    <div>

                        <div
                            class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500"
                        >
                            Discount
                        </div>

                        <div class="relative">

                            <input
                                v-model="discountValue"
                                type="number"
                                min="0"
                                :max="
                                    discountType === 'percentage'
                                        ? 100
                                        : grossTotal
                                "
                                step="any"
                                autofocus
                                class="h-12 w-full rounded-lg border border-slate-200 bg-white px-4 pr-12 text-lg font-semibold text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                :placeholder="
                                    discountType === 'percentage'
                                        ? '0'
                                        : '0'
                                "
                                @keyup.enter="apply"
                            />

                            <span
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-sm font-semibold text-slate-400"
                            >
                                {{
                                    discountType === 'percentage'
                                        ? '%'
                                        : 'Rp'
                                }}
                            </span>

                        </div>

                        <div
                            v-if="
                                discountType === 'percentage' &&
                                Number(discountValue) > 100
                            "
                            class="mt-1 text-xs font-medium text-red-500"
                        >
                            Maximum discount is 100%.
                        </div>

                        <div
                            v-else-if="
                                discountType === 'nominal' &&
                                Number(discountValue) > grossTotal
                            "
                            class="mt-1 text-xs font-medium text-red-500"
                        >
                            Discount cannot exceed the gross total.
                        </div>

                    </div>


                    <!-- SUMMARY -->
                    <div
                        class="border-t border-slate-100 pt-4"
                    >

                        <div
                            class="space-y-2 text-sm"
                        >

                            <div
                                class="flex justify-between text-slate-500"
                            >
                                <span>Gross Total</span>

                                <span>
                                    {{ formatCurrency(grossTotal) }}
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
                                        calculatedDiscount > 0
                                            ? `-${formatCurrency(calculatedDiscount)}`
                                            : formatCurrency(0)
                                    }}
                                </span>
                            </div>

                            <div
                                class="my-2 border-t border-slate-100"
                            />

                            <div
                                class="flex justify-between"
                            >
                                <span
                                    class="font-semibold text-slate-800"
                                >
                                    Final Total
                                </span>

                                <span
                                    class="text-lg font-bold text-blue-600"
                                >
                                    {{ formatCurrency(finalTotal) }}
                                </span>
                            </div>

                        </div>

                    </div>

                </div>


                <!-- FOOTER -->
                <div
                    class="flex items-center justify-end gap-2 border-t border-slate-100 px-5 py-3"
                >

                    <button
                        type="button"
                        class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100"
                        @click="close"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        :disabled="!isValid"
                        class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-slate-300"
                        @click="apply"
                    >
                        Apply
                    </button>

                </div>

            </div>

        </div>

    </Teleport>
</template>