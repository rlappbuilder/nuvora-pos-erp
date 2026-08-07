<script setup>
import Modal from '@/Components/Modal.vue'
import BaseButton from '@/Components/Button/BaseButton.vue'
import BaseModalLayout from '@/Components/Modal/BaseModalLayout.vue'
import FormField from '@/Components/Form/FormField.vue'
import { DatePicker, CurrencyInput, SearchableSelect,} from '@/Components/Form'
import { formatCurrency,} from '@/Utils/currency'
import axios from 'axios'
import { useForm } from '@inertiajs/vue3'
import { success, error,} from '@/Utils'
import { CubeIcon,} from '@heroicons/vue/24/outline'
import { ref, watch, computed,nextTick,} from 'vue'

const props = defineProps({

    show: Boolean,

    branches: Array,

    products: Array,

    variants: Array,

    unitOptions: Array,

    priceTypes: Array,

    productVariantPrice: {

        type: Object,

        default: null,

    },

})

const emit = defineEmits([

    'close',

    'saved',

])

const form = useForm({

    branch_id: '',

    product_id: '',

    product_variant_id: '',

    unit_id: '',

    price_type_id: '',

    last_purchase_price: 0,

    selling_price: 0,

    effective_from: '',

    effective_until: '',

    is_active: true,

    description: '',

})
const duplicateInfo = ref(null)
const isLoadingEdit = ref(false)
const isEdit = computed(() => {

    return !!props.productVariantPrice

})

const disableSubmit = computed(() => {

    return form.processing

})

const filteredVariants = computed(() => {

    if (!form.product_id) {

        return props.variants

    }

    return props.variants.filter(

        variant =>

            variant.product_id == form.product_id

    )

})
const filteredUnits = computed(() => {

    console.log('VARIANTS =', props.variants)

    if (!form.product_variant_id) {

        return []

    }

    const variant = props.variants.find(

        item => item.id == form.product_variant_id

    )

    console.log('VARIANT =', variant)

    return variant?.units ?? []

})
function submit()
{
    if (isEdit.value) {

        form.put(

            route(

                'product-variant-prices.update',

                props.productVariantPrice.id

            ),

            {

                preserveScroll: true,

                onSuccess: () => {

                    success(
                        'Product Variant Price updated successfully.'
                    )

                    emit('saved')

                    emit('close')

                },

                onError: (errors) => {

                    console.log(errors)

                },

            }

        )

        return

    }

    form.post(

        route(
            'product-variant-prices.store'
        ),

        {

            preserveScroll: true,

            onSuccess: () => {

                success(
                    'Product Variant Price created successfully.'
                )

                emit('saved')

                emit('close')

            },

            onError: (errors) => {

                console.log(errors)

            },

        }

    )

}

function resetForm()
{
    form.reset()

    form.clearErrors()

    form.is_active = true

    form.last_purchase_price = 0

    form.selling_price = 0
}

watch(

    () => props.show,

    (show) => {

        if (!show) {

            resetForm()

        }

    }

)

watch(

    () => form.product_id,

    () => {

        if (

            isLoadingEdit.value

        ) {

            return

        }

        form.product_variant_id = ''

        form.unit_id = ''

        form.last_purchase_price = 0

        form.selling_price = 0

    }

)

watch(

    () => form.product_variant_id,

    () => {

        if (

            isLoadingEdit.value

        ) {

            return

        }

        form.unit_id = ''

        form.last_purchase_price = 0

        form.selling_price = 0

    }

)
watch(

    () => [

        form.branch_id,

        form.product_variant_id,

        form.unit_id,

    ],

    async ([

        branchId,

        variantId,

        unitId,

    ]) => {

        if (

            !branchId ||

            !variantId ||

            !unitId

        ) {

            form.last_purchase_price = 0

            return

        }

        try {

            const {

                data,

            } = await axios.get(

                route(

                    'product-variant-prices.latest'

                ),

                {

                    params: {

                        branch_id: branchId,

                        product_variant_id: variantId,

                        unit_id: unitId,

                    },

                }

            )

            form.last_purchase_price =

                data.last_purchase_price

        }

        catch (

            e

        ) {

            console.error(

                e

            )

            form.last_purchase_price = 0

        }

    }

)
watch(

    () => [

        form.branch_id,

        form.product_variant_id,

        form.unit_id,

        form.price_type_id,

    ],

    async ([

        branchId,

        variantId,

        unitId,

        priceTypeId,

    ]) => {

        duplicateInfo.value = null

        if (

            !branchId ||

            !variantId ||

            !unitId ||

            !priceTypeId

        ) {

            return

        }

        try {

            const {

                data,

            } = await axios.get(

                route(

                    'product-variant-prices.check'

                ),

                {

                    params: {

                        branch_id: branchId,

                        product_variant_id: variantId,

                        unit_id: unitId,

                        price_type_id: priceTypeId,

                    },

                }

            )

            duplicateInfo.value = data

        }

        catch (

            e

        ) {

            console.error(e)

        }

    }

)
watch(

    () => form.product_variant_id,

    (value) => {

        console.log('VARIANT ID =', value)

    }

)
watch(

    () => props.productVariantPrice,

    async (value) => {

        if (!value) {

            return

        }

        isLoadingEdit.value = true

        form.branch_id = value.branch_id

        form.product_id =
            value.variant?.product_id

        form.product_variant_id =
            value.product_variant_id

        form.unit_id =
            value.unit_id

        form.price_type_id =
            value.price_type_id

        form.last_purchase_price =
            value.last_purchase_price

        form.selling_price =
            value.selling_price

       form.effective_from =
    value.effective_from
        ? value.effective_from.slice(0, 10)
        : ''

form.effective_until =
    value.effective_until
        ? value.effective_until.slice(0, 10)
        : ''

        form.is_active =
            value.is_active

        form.description =
            value.description

        await nextTick()

        isLoadingEdit.value = false

    },

    {

        immediate: true,

    }

)

const margin = computed(() => {

    const purchase = Number(
        form.last_purchase_price || 0
    )

    const selling = Number(
        form.selling_price || 0
    )

    if (purchase <= 0) {

        return null

    }

    const value = (

        (selling - purchase)

        / purchase

    ) * 100

    return {

        percent: value,

        text: `${value.toFixed(2)} %`,

        color:

            value < 0

                ? 'text-red-600'

                : value < 15

                ? 'text-yellow-600'

                : 'text-green-600',

        badge:

            value < 0

                ? 'Loss'

                : value < 15

                ? 'Low Margin'

                : 'Good Margin',

    }

})
</script>
<template>

<Modal
    :show="show"
    max-width="2xl"
    @close="emit('close')"
>

    <BaseModalLayout
            :title="
                isEdit
                    ? 'Edit Product Variant Price'
                    : 'Create Product Variant Price'
            "
            :subtitle="
                isEdit
                    ? 'Update Price conversion for Product Variant Pricing.'
                    : 'Add Price conversion for Product Variant Pricing.'
            "
        >

        <template #icon>

            <CubeIcon
                class="h-6 w-6 text-indigo-600"
            />

        </template>

        <template #content>

    <div class="space-y-5 px-4">

        <!-- Product Variant -->
        <FormSection title="Product Information">

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                <FormField
                    label="Branch"
                    required
                    :error="form.errors.branch_id"
                >

                    <SearchableSelect
                        v-model="form.branch_id"
                        :options="branches"
                        label="label"
                        value-key="id"
                        placeholder="Select Branch"
                    />

                </FormField>

                <FormField
                    label="Product"
                    required
                    :error="form.errors.product_id"
                >

                    <SearchableSelect
                        v-model="form.product_id"
                        :options="products"
                        label="label"
                        value-key="id"
                        placeholder="Select Product"
                    />

                </FormField>

                <FormField
                    label="Variant"
                    required
                    :error="form.errors.product_variant_id"
                >

                    <SearchableSelect
                        v-model="form.product_variant_id"
                        @update:modelValue="value => console.log('UPDATE VARIANT', value)"
                        :options="filteredVariants"
                        label="label"
                        value-key="id"
                        placeholder="Select Variant"
                    />

                </FormField>

                <FormField
                    label="Unit"
                    required
                    :error="form.errors.unit_id"
                >
      

                    <SearchableSelect
                        v-model="form.unit_id"
                        :options="filteredUnits"
                        label="label"
                        value-key="id"
                        placeholder="Select Unit"
                    />

                </FormField>

                <FormField
                    label="Price Type"
                    required
                    :error="form.errors.price_type_id"
                >

                    <SearchableSelect
                        v-model="form.price_type_id"
                        :options="priceTypes"
                        label="label"
                        value-key="id"
                        placeholder="Select Price Type"
                    />

                </FormField>
                <!-- duplicate cheker-->
                    <div

                        v-if="duplicateInfo"

                        class="
                            mt-3
                            rounded-lg
                            border
                            p-3
                        "

                        :class="

                            duplicateInfo.exists

                                ? 'border-amber-300 bg-amber-50'

                                : 'border-green-300 bg-green-50'

                        "

                    >

                        <template v-if="duplicateInfo.exists && duplicateInfo.data">

                            <div class="font-semibold text-amber-700">

                                ⚠ Harga sudah tersedia

                            </div>

                            <div class="mt-1 text-sm">

                                Last Purchase :

                                {{ formatCurrency(

                                    duplicateInfo.data.last_purchase_price

                                ) }}

                            </div>

                            <div class="text-sm">

                                Selling :

                                {{ formatCurrency(

                                    duplicateInfo.data.selling_price

                                ) }}

                            </div>

                        </template>

                        <template v-else>

                            <div class="font-semibold text-green-700">

                                ✅ Belum ada harga untuk kombinasi ini.

                            </div>

                        </template>

                    </div>
                <!-- end duplikat cheker pesan-->

            </div>

        </FormSection>

            <FormSection title="Pricing Information">

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                <FormField
                    label="Last Purchase Price"
                    required
                    :error="form.errors.last_purchase_price"
                >

                    <CurrencyInput
                        v-model="form.last_purchase_price"
                    />

                </FormField>

                <FormField
                    label="Selling Price"
                    required
                    :error="form.errors.selling_price"
                >

                    <CurrencyInput
                        v-model="form.selling_price"
                    />

                </FormField>
                    <!-- message-->
                    <div
                        v-if="margin"
                        class="
                            mt-2
                            rounded-lg
                            border
                            bg-gray-50
                            px-3
                            py-2
                            text-sm
                        "
                    >

                        <div class="flex justify-between">

                            <span>

                                Margin

                            </span>

                            <span
                                :class="margin.color"
                                class="font-semibold"
                            >

                                {{ margin.text }}

                                • {{ margin.badge }}

                            </span>

                        </div>

                    </div>
                    <!-- end message-->
                <FormField
                    label="Effective From"
                    required
                    :error="form.errors.effective_from"
                >

                    <input
                        v-model="form.effective_from"
                        type="date"
                        class="
                            w-full
                            rounded-lg
                            border
                            border-gray-300
                            px-3
                            py-2
                            focus:border-indigo-500
                            focus:outline-none
                            focus:ring-2
                            focus:ring-indigo-200
                        "
                    />

                </FormField>

                <FormField
                    label="Effective Until"
                    :error="form.errors.effective_until"
                >

                    <input
                        v-model="form.effective_until"
                        type="date"
                        class="
                            w-full
                            rounded-lg
                            border
                            border-gray-300
                            px-3
                            py-2
                            focus:border-indigo-500
                            focus:outline-none
                            focus:ring-2
                            focus:ring-indigo-200
                        "
                    />

                    <p class="mt-1 text-xs text-gray-500">
                        Kosongkan jika harga masih berlaku.
                    </p>

                </FormField>

            </div>

        </FormSection>
        <!-- end pricing information-->
        
        
        <!-- description-->
       
        <!-- end description-->
    </div>

</template>

        <template #footer>

            <div
                class="flex justify-end gap-3 px-2"
            >

                <BaseButton
                    variant="secondary"
                    @click="emit('close')"
                >

                    Cancel

                </BaseButton>

               <BaseButton
                :loading="form.processing"
                :disabled="disableSubmit"
                @click="submit"
            >
                {{ isEdit ? 'Update' : 'Save' }}
            </BaseButton>

            </div>

        </template>

    </BaseModalLayout>

</Modal>

</template>