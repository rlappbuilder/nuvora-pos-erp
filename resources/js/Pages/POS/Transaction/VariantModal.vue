<script setup>

import { computed } from 'vue'

const props = defineProps({

    show: {
        type: Boolean,
        default: false,
    },

    product: {
        type: Object,
        default: null,
    },

})

const emit = defineEmits([
    'close',
    'select',
])


/*
|--------------------------------------------------------------------------
| Variants
|--------------------------------------------------------------------------
*/

const variants = computed(() => {

    return Array.isArray(
        props.product?.variants
    )
        ? props.product.variants
        : []

})


/*
|--------------------------------------------------------------------------
| Currency
|--------------------------------------------------------------------------
*/

const formatCurrency = (value) => {

    return new Intl.NumberFormat(
        'id-ID',
        {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }
    ).format(
        Number(value ?? 0)
    )

}


/*
|--------------------------------------------------------------------------
| Select Variant
|--------------------------------------------------------------------------
*/

const selectVariant = (variant) => {

    emit(
        'select',
        variant
    )

}


/*
|--------------------------------------------------------------------------
| Close
|--------------------------------------------------------------------------
*/

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
                class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl"
            >

                <!-- HEADER -->

                <div
                    class="flex items-center justify-between border-b border-slate-100 px-5 py-4"
                >

                    <div>

                        <h2
                            class="text-base font-semibold text-slate-900"
                        >
                            Pilih Variant
                        </h2>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            {{
                                product?.product_name ??
                                product?.name ??
                                'Product'
                            }}
                        </p>

                    </div>


                    <button
                        type="button"
                        class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700"
                        @click="close"
                    >
                        ×
                    </button>

                </div>


                <!-- VARIANTS -->

                <div
                    class="max-h-[60vh] overflow-y-auto p-4"
                >

                    <div
                        class="space-y-2"
                    >

                        <button
                            v-for="variant in variants"
                            :key="
                                variant.variant_id ??
                                variant.product_variant_id ??
                                variant.id
                            "
                            type="button"
                            class="flex w-full items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3 text-left transition hover:border-blue-300 hover:bg-blue-50"
                            @click="
                                selectVariant(
                                    variant
                                )
                            "
                        >

                            <div
                                class="min-w-0"
                            >

                                <div class="truncate text-sm font-semibold text-slate-900">
                                    {{
                                        variant.variant_name &&
                                        variant.variant_name !== 'Default'
                                            ? `${variant.product_name ?? product?.product_name ?? ''} ${variant.variant_name}`
                                            : (
                                                variant.product_name ??
                                                product?.product_name ??
                                                'Product'
                                            )
                                    }}
                                </div>

                                <div class="mt-1 text-xs text-slate-500">
                                    {{ variant.sku ?? '-' }}

                                    <span v-if="variant.unit_name">
                                        · {{ variant.unit_name }}
                                    </span>
                                </div>

                                <div class="mt-1 text-xs font-medium text-slate-500">
                                    Stock: {{ variant.available_stock ?? 0 }}
                                </div>

                            </div>


                            <div
                                class="ml-4 shrink-0 text-sm font-bold text-blue-600"
                            >
                                {{
                                    formatCurrency(
                                        variant.selling_price
                                    )
                                }}
                            </div>

                        </button>

                    </div>

                </div>


                <!-- FOOTER -->

                <div
                    class="flex justify-end border-t border-slate-100 px-5 py-3"
                >

                    <button
                        type="button"
                        class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100"
                        @click="close"
                    >
                        Tutup
                    </button>

                </div>

            </div>

        </div>

    </Teleport>

</template>