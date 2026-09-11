<script setup>

import { computed, ref } from 'vue'

import FormField from '@/Components/Form/FormField.vue'
import FormInput from '@/Components/Form/FormInput.vue'
import FormTextarea from '@/Components/Form/FormTextarea.vue'
import FormCheckbox from '@/Components/Form/FormCheckbox.vue'
import SearchableSelect from '@/Components/Form/SearchableSelect.vue'
import BaseButton from '@/Components/Button/BaseButton.vue'


const props = defineProps({

    form: {
        type: Object,
        required: true,
    },

    mode: {
        type: String,
        default: 'create',
    },

    products: {
        type: Array,
        default: () => [],
    },

})


const emit = defineEmits([
    'submit',
    'submitAndNew',
    'cancel',
])


const activeTab = ref('general')

const selectedProductId = ref(null)


const isCreate = computed(() =>
    props.mode === 'create'
)


const submitLabel = computed(() =>
    isCreate.value
        ? 'Save Reseller'
        : 'Update Reseller'
)


const prices = computed(() =>
    props.form.prices ?? []
)


const hasPrices = computed(() =>
    prices.value.length > 0
)


const availableProducts = computed(() => {

    const selectedIds = prices.value.map(
        price => Number(price.product_id)
    )

    return props.products.filter(
        product =>
            !selectedIds.includes(
                Number(product.id)
            )
    )

})


const productOptions = computed(() =>
    availableProducts.value.map(product => ({
        label: `${product.code} - ${product.name}`,
        value: product.id,
    }))
)


function addPrice()
{
    if (!selectedProductId.value) {

        return

    }


    const product = props.products.find(
        product =>
            Number(product.id) ===
            Number(selectedProductId.value)
    )


    if (!product) {

        return

    }


    props.form.prices.push({

        product_id: product.id,

        price: 0,

        product: product,

    })


    selectedProductId.value = null
}


function removePrice(index)
{
    props.form.prices.splice(
        index,
        1
    )
}


function submit()
{
    emit('submit')
}


function submitAndNew()
{
    emit('submitAndNew')
}


function cancel()
{
    emit('cancel')
}

</script>


<template>

    <div class="space-y-4">

        <!-- =====================================================
             Page Header
        ====================================================== -->

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
                    {{ mode === 'create'
                        ? 'Create Reseller'
                        : 'Edit Reseller'
                    }}
                </h1>

                <p
                    class="
                        mt-1
                        text-sm
                        text-gray-500
                    "
                >
                    {{ mode === 'create'
                        ? 'Create a new reseller and configure its consignment prices.'
                        : 'Update reseller information and configure its consignment prices.'
                    }}
                </p>

            </div>

        </div>


        <!-- =====================================================
             Form
        ====================================================== -->

        <form
            class="space-y-4"
            @submit.prevent="submit"
        >

            <!-- =================================================
                 Tabs
            ================================================== -->

            <div
                class="
                    border-b
                    border-gray-200
                "
            >

                <div class="flex gap-6">

                    <button
                        type="button"
                        class="
                            border-b-2
                            px-1
                            py-3
                            text-sm
                            font-medium
                        "
                        :class="
                            activeTab === 'general'
                                ? 'border-blue-600 text-blue-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700'
                        "
                        @click="activeTab = 'general'"
                    >
                        General Information
                    </button>


                    <button
                        type="button"
                        class="
                            border-b-2
                            px-1
                            py-3
                            text-sm
                            font-medium
                        "
                        :class="
                            activeTab === 'prices'
                                ? 'border-blue-600 text-blue-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700'
                        "
                        @click="activeTab = 'prices'"
                    >
                        Consignment Prices
                    </button>

                </div>

            </div>


            <!-- =================================================
                 General Information
            ================================================== -->

            <div
                v-if="activeTab === 'general'"
                class="
                    rounded-xl
                    border
                    border-gray-100
                    bg-white
                    p-5
                    shadow-sm
                "
            >

                <div class="mb-5">

                    <h2
                        class="
                            text-sm
                            font-semibold
                            text-gray-900
                        "
                    >
                        Reseller Information
                    </h2>

                    <p
                        class="
                            mt-1
                            text-xs
                            text-gray-500
                        "
                    >
                        Basic information about this reseller.
                    </p>

                </div>


                <div
                    class="
                        grid
                        grid-cols-1
                        gap-4
                        md:grid-cols-2
                    "
                >

                    <FormField
                        label="Reseller Code"
                    >

                        <FormInput
                            v-model="form.code"
                            readonly
                        />

                    </FormField>


                    <FormField
                        label="Name"
                        required
                        :error="form.errors.name"
                    >

                        <FormInput
                            v-model="form.name"
                            placeholder="Enter reseller name"
                        />

                    </FormField>


                    <FormField
                        label="Contact Person"
                        :error="form.errors.contact_person"
                    >

                        <FormInput
                            v-model="form.contact_person"
                            placeholder="Enter contact person"
                        />

                    </FormField>


                    <FormField
                        label="Phone"
                        :error="form.errors.phone"
                    >

                        <FormInput
                            v-model="form.phone"
                            placeholder="Enter phone number"
                        />

                    </FormField>


                    <FormField
                        label="Email"
                        :error="form.errors.email"
                    >

                        <FormInput
                            v-model="form.email"
                            type="email"
                            placeholder="Enter email address"
                        />

                    </FormField>


                    <FormField
                        label="City"
                        :error="form.errors.city"
                    >

                        <FormInput
                            v-model="form.city"
                            placeholder="Enter city"
                        />

                    </FormField>


                    <FormField
                        label="Tax Number"
                        :error="form.errors.tax_number"
                    >

                        <FormInput
                            v-model="form.tax_number"
                            placeholder="Enter tax number"
                        />

                    </FormField>


                    <FormField
                        label="Status"
                        :error="form.errors.status"
                    >

                        <SearchableSelect
                            v-model="form.status"
                            label="label"
                            value-key="value"
                            :options="[
                                {
                                    label: 'Active',
                                    value: true,
                                },
                                {
                                    label: 'Inactive',
                                    value: false,
                                },
                            ]"
                        />

                    </FormField>


                    <FormField
                        label="Address"
                        :error="form.errors.address"
                        class="md:col-span-2"
                    >

                        <FormTextarea
                            v-model="form.address"
                            :rows="3"
                            placeholder="Enter reseller address"
                        />

                    </FormField>

                </div>

            </div>


            <!-- =================================================
                 Consignment Prices
            ================================================== -->

            <div
                v-if="activeTab === 'prices'"
                class="
                    rounded-xl
                    border
                    border-gray-100
                    bg-white
                    p-5
                    shadow-sm
                "
            >

                <div
                    class="
                        flex
                        flex-col
                        gap-4
                        sm:flex-row
                        sm:items-end
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
                            Consignment Prices
                        </h2>

                        <p
                            class="
                                mt-1
                                text-xs
                                text-gray-500
                            "
                        >
                            Default Nuvora price used for reseller settlement.
                        </p>

                    </div>


                    <div
                        class="
                            flex
                            w-full
                            flex-col
                            gap-2
                            sm:w-auto
                            sm:flex-row
                        "
                    >

                        <div class="w-full sm:w-72">

                            <SearchableSelect
                                v-model="selectedProductId"
                                label="label"
                                value-key="value"
                                :options="productOptions"
                                placeholder="Select product"
                            />

                        </div>


                        <BaseButton
                            type="button"
                            :disabled="!selectedProductId"
                            @click="addPrice"
                        >
                            Add Product
                        </BaseButton>

                    </div>

                </div>


                <!-- Empty -->

                <div
                    v-if="!hasPrices"
                    class="
                        mt-5
                        flex
                        min-h-48
                        flex-col
                        items-center
                        justify-center
                        rounded-lg
                        border
                        border-dashed
                        border-gray-200
                        px-6
                        py-10
                        text-center
                    "
                >

                    <div
                        class="
                            text-sm
                            font-medium
                            text-gray-700
                        "
                    >
                        No products added
                    </div>

                    <p
                        class="
                            mt-1
                            max-w-sm
                            text-xs
                            text-gray-500
                        "
                    >
                        Select a product above to configure its
                        reseller settlement price.
                    </p>

                </div>


                <!-- Price Table -->

                <div
                    v-else
                    class="
                        mt-5
                        overflow-x-auto
                        rounded-lg
                        border
                        border-gray-100
                    "
                >

                    <table class="min-w-full">

                        <thead
                            class="
                                border-b
                                border-gray-100
                                bg-gray-50
                            "
                        >

                            <tr>

                                <th
                                    class="
                                        px-4
                                        py-3
                                        text-left
                                        text-xs
                                        font-medium
                                        text-gray-500
                                    "
                                >
                                    Product
                                </th>

                                <th
                                    class="
                                        px-4
                                        py-3
                                        text-left
                                        text-xs
                                        font-medium
                                        text-gray-500
                                    "
                                >
                                    Code
                                </th>

                                <th
                                    class="
                                        w-48
                                        px-4
                                        py-3
                                        text-right
                                        text-xs
                                        font-medium
                                        text-gray-500
                                    "
                                >
                                    Consignment Price
                                </th>

                                <th
                                    class="
                                        w-12
                                        px-4
                                        py-3
                                    "
                                >
                                </th>

                            </tr>

                        </thead>


                        <tbody
                            class="
                                divide-y
                                divide-gray-100
                            "
                        >

                            <tr
                                v-for="(
                                    price,
                                    index
                                ) in prices"
                                :key="
                                    price.product_id ??
                                    index
                                "
                            >

                                <td class="px-4 py-3">

                                    <div
                                        class="
                                            text-sm
                                            font-medium
                                            text-gray-900
                                        "
                                    >
                                        {{
                                            price.product?.name ??
                                            '-'
                                        }}
                                    </div>

                                </td>


                                <td class="px-4 py-3">

                                    <div
                                        class="
                                            text-sm
                                            text-gray-500
                                        "
                                    >
                                        {{
                                            price.product?.code ??
                                            '-'
                                        }}
                                    </div>

                                </td>


                                <td class="px-4 py-3">

                                    <input
                                        v-model="price.price"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        class="
                                            w-full
                                            rounded-lg
                                            border
                                            border-gray-200
                                            px-3
                                            py-2
                                            text-right
                                            text-sm
                                            outline-none
                                            focus:border-gray-400
                                            focus:ring-1
                                            focus:ring-gray-300
                                        "
                                    />

                                    <div
                                        v-if="
                                            form.errors[
                                                `prices.${index}.price`
                                            ]
                                        "
                                        class="
                                            mt-1
                                            text-xs
                                            text-red-600
                                        "
                                    >
                                        {{
                                            form.errors[
                                                `prices.${index}.price`
                                            ]
                                        }}
                                    </div>

                                </td>


                                <td
                                    class="
                                        px-4
                                        py-3
                                        text-center
                                    "
                                >

                                    <button
                                        type="button"
                                        class="
                                            text-lg
                                            leading-none
                                            text-gray-400
                                            transition
                                            hover:text-red-600
                                        "
                                        @click="
                                            removePrice(index)
                                        "
                                    >
                                        ×
                                    </button>

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>


                <div
                    v-if="form.errors.prices"
                    class="
                        mt-2
                        text-xs
                        text-red-600
                    "
                >
                    {{ form.errors.prices }}
                </div>

            </div>


            <!-- =================================================
                 Actions
            ================================================== -->

            <div
                class="
                    flex
                    flex-wrap
                    items-center
                    justify-end
                    gap-3
                    pt-4
                "
            >

                <BaseButton
                    type="button"
                    variant="secondary"
                    @click="cancel"
                >
                    Cancel
                </BaseButton>


                <BaseButton
                    v-if="isCreate"
                    type="button"
                    variant="success"
                    :loading="form.processing"
                    @click="submitAndNew"
                >
                    Save &amp; New
                </BaseButton>


                <BaseButton
                    type="submit"
                    :loading="form.processing"
                >
                    {{ submitLabel }}
                </BaseButton>

            </div>

        </form>

    </div>

</template>