<script setup>
import Modal from '@/Components/Modal.vue'
import BaseButton from '@/Components/Button/BaseButton.vue'
import SearchableSelect from '@/Components/Form/SearchableSelect.vue'
import { ref, watch, computed } from 'vue'
import axios from 'axios'
import { LoadingOverlay } from '@/Components/Feedback'
import BaseModalLayout
from '@/Components/Modal/BaseModalLayout.vue'

import {
    CubeIcon
} from '@heroicons/vue/24/outline'
const props = defineProps({

    show: {
        type: Boolean,
        default: false,
    },

    products: {
        type: Array,
        default: () => [],
    },

})

const emit = defineEmits([
    'close',
    'preview',
    'generate',
])

const productId = ref('')

watch(
    () => props.show,
    (value) => {

        if (!value) {

            productId.value = ''

        }

    }
)

function preview()
{
    emit(
        'preview',
        productId.value
    )
}
const selectedProduct = computed(() => {

    return (props.products ?? []).find(
        item => Number(item.id) === Number(productId.value)
    ) ?? null

})
watch(selectedProduct, (value) => {

    console.log(value)

})
function generate()
{
    emit(
        'generate',
        productId.value
    )
}
const loading = ref(false)

const attributes = ref([])

const variants = ref([])

const summary = ref({

    attributes: 0,

    values: [],

    total: 0,

})
watch(productId, async (id) => {

    if (!id) {

        attributes.value = []

        variants.value = []

        summary.value = {

            attributes: 0,

            values: [],

            total: 0,

        }

        return

    }

    loading.value = true

    try {

        const { data } = await axios.get(

            route(
                'product-variants.preview',
                id
            )

        )
        console.log(data)
        attributes.value = data.attributes

        variants.value = data.variants

        summary.value = data.summary

    } finally {

        loading.value = false

    }

})

</script>
<template>

<Modal
    :show="show"
    max-width="2xl"
    @close="$emit('close')"
>

    <div class="
        p-6
        space-y-6
        min-h-[34rem]
        flex
        flex-col
    ">
        <div
            v-if="attributes.length"
            class="grid grid-cols-2 gap-3"
        >

            <div
                v-for="attribute in attributes"
                :key="attribute.id"
                class="rounded-xl border bg-slate-50 p-4"
            >

                <p class="text-sm font-semibold">

                    {{ attribute.name }}

                </p>

                <p class="mt-1 text-xs text-gray-500">

                    {{ attribute.values_count }} Values

                </p>

            </div>

        </div>
        <div
    class="
        -mx-6
        -mt-6
        px-6
        py-5
        mb-6
        bg-indigo-50
        border-b
        border-indigo-100
    "
>

    <h2
        class="
            text-xl
            font-bold
            text-indigo-900
        "
    >

        Generate Product Variant

    </h2>

    <p
        class="
            mt-1
            text-sm
            text-indigo-600
        "
    >

        Generate variant berdasarkan Attribute Product.

    </p>

</div>
        <SearchableSelect
            v-model="productId"
            :options="products"
            
            placeholder="Select Product"
        />
      
        <!-- product information card-->
            <div
                v-if="selectedProduct"
                class="rounded-xl border border-slate-200 bg-slate-50 p-4"
            >
                <h3 class="mb-3 text-sm font-semibold text-slate-700">
                    Product Information
                </h3>

                <div class="space-y-2 text-sm">

                    <div class="flex justify-between">
                        <span class="text-slate-500">Code</span>
                        <span class="font-medium">
                            {{ selectedProduct.code }}
                        </span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-slate-500">Current Variants</span>
                        <span class="font-semibold">
                            {{ selectedProduct.variants_count }}
                        </span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-slate-500">Status</span>

                        <span
                            :class="selectedProduct.has_variants
                                ? 'text-blue-600'
                                : 'text-emerald-600'"
                            class="font-semibold"
                        >
                            {{ selectedProduct.has_variants
                                ? 'Variant Exists'
                                : 'No Variant Yet'
                            }}
                        </span>
                    </div>

                </div>
            </div>
        <!-- end product information card-->
        <LoadingOverlay
            :show="loading"
            text="Loading Variant Preview..."
        />
        <div
             v-if="
                !attributes.length &&
                productId &&
                !selectedProduct?.has_variants
            "
            class="
                rounded-xl
                border
                border-blue-200
                bg-blue-50
                p-5
            "
        >

            <h3 class="font-semibold text-blue-900">

                No Variant Attribute

            </h3>

            <p class="mt-2 text-sm text-blue-700">

                This product has no Variant Attributes assigned.

                A Default Variant will be created automatically.

            </p>

        </div>
        <div
            v-if="variants.length"
            class="
                rounded-xl
                border
                p-4
                max-h-52
                overflow-y-auto
            "
        >

            <div
                class="
                    flex
                    flex-wrap
                    gap-2
                "
            >

                <span
                    v-for="variant in variants"
                    :key="variant.name"
                    class="
                        inline-flex
                        items-center
                        rounded-full
                        bg-blue-100
                        px-3
                        py-1
                        text-sm
                        font-medium
                        text-blue-700
                    "
                >

                    {{ variant.name }}

                </span>

            </div>

        </div>
        <!-- summary-->
        <div
            v-if="productId"
            class="
                rounded-xl
                bg-slate-50
                border
                p-4
            "
        >

            <h3
                class="
                    text-sm
                    font-semibold
                    text-slate-700
                    mb-3
                "
            >

                Summary

            </h3>

            <div
                class="
                    flex
                    justify-between
                    text-sm
                "
            >

                <span>Attributes</span>

                <span
                    class="
                        font-bold
                        text-blue-600
                    "
                >

                    {{ summary.attributes }}

                </span>

            </div>

            <div
                class="
                    flex
                    justify-between
                    text-sm
                    mt-2
                "
            >

                <span>Values</span>

                <span>

                    {{

                        summary.values.length

                            ? summary.values.join(' × ')

                            : '-'

                    }}

                </span>

            </div>

            <div
                class="
                    flex
                    justify-between
                    text-sm
                    mt-2
                    font-semibold
                "
            >

                <span>Variants</span>

                <span>

                    {{ summary.total }}

                </span>

            </div>

        </div>
        <!-- end summary-->
        <div
            class="
                flex
                justify-end
                gap-2
            "
        >

            <BaseButton
                variant="secondary"
                @click="$emit('close')"
            >

                Cancel

            </BaseButton>

            <BaseButton
                :disabled="loading || !productId"
                @click="generate"
            >

                {{

                    selectedProduct?.has_variants

                        ? `Regenerate ${summary.total} Variants`

                        : summary.total <= 1

                            ? 'Generate Default Variant'

                            : `Generate ${summary.total} Variants`

                }}

            </BaseButton>

        </div>

    </div>

</Modal>

</template>