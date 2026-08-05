<script setup>

import Modal from '@/Components/Modal.vue'
import BaseButton from '@/Components/Button/BaseButton.vue'
import SearchableSelect from '@/Components/Form/SearchableSelect.vue'
import BaseModalLayout from '@/Components/Modal/BaseModalLayout.vue'
import axios from 'axios'
import { useForm } from '@inertiajs/vue3'

import {
    CubeIcon,
} from '@heroicons/vue/24/outline'
import { ref, watch, computed } from 'vue'


const props = defineProps({

    show: Boolean,

    variants: {

        type: Array,

        default: () => [],

    },

    units: {

        type: Array,

        default: () => [],

    },

})

const emit = defineEmits([

    'close',

    'saved',

])

const form = useForm({

    product_variant_id: '',

    unit_id: '',

    conversion_factor: 1,

    is_base: false,

    is_default: false,

    is_active: true,

})

function submit()
{
    form.post(

        route('product-variant-units.store'),

        {

            preserveScroll: true,

            onSuccess: async () => {

                emit('saved')

                await loadAvailableUnits(
                    form.product_variant_id
                )

                form.unit_id = ''

                form.conversion_factor = 1

                form.is_base = false

                form.is_default = false

                form.is_active = true

            }

        }

    )
}
const noAvailableUnits = computed(() => {

    return (
        form.product_variant_id &&
        !loadingUnits.value &&
        unitOptions.value.length === 0
    )

})
const loadingUnits = ref(false)
const unitOptions = ref([])
async function loadAvailableUnits(variantId)
{
    form.unit_id = ''

    if (!variantId) {

        unitOptions.value = []

        return

    }

    loadingUnits.value = true

    try {

        const { data } = await axios.get(

            route(
                'product-variant-units.available-units',
                variantId
            )

        )

        unitOptions.value = data

    } finally {

        loadingUnits.value = false

    }
}
watch(
    () => props.show,
    (show) => {

        if (!show) {

            form.reset()

            unitOptions.value = []

            loadingUnits.value = false

        }

    }
)
watch(

    () => form.product_variant_id,

    loadAvailableUnits

)
</script>
<template>

<Modal
    :show="show"
    max-width="2xl"
    @close="emit('close')"
>

    <BaseModalLayout
        title="Create Product Variant Unit"
        subtitle="Add unit conversion for Product Variant."
    >

        <template #icon>

            <CubeIcon
                class="h-6 w-6 text-indigo-600"
            />

        </template>

        <template #content>

            <div class="space-y-5">
                <div>

                    <label
                        class="mb-2 block text-sm font-medium"
                    >

                        Product name

                    </label>
                <SearchableSelect
                    v-model="form.product_variant_id"
                    :options="variants"
                    label="label"
                    value-key="id"
                />
                </div>

                <div v-if="!noAvailableUnits">

                    <SearchableSelect
                        v-model="form.unit_id"
                        :options="unitOptions"
                        label="label"
                        value-key="id"
                        placeholder="Select Unit"
                    />

                </div>

                <div
                    v-else
                    class="
                        rounded-xl
                        border
                        border-amber-200
                        bg-amber-50
                        p-4
                    "
                >

                    <h3 class="font-semibold text-amber-800">

                        No Available Units

                    </h3>

                    <p class="mt-2 text-sm text-amber-700">

                        All units have already been assigned to this Product Variant.

                    </p>

                </div>

                <div>

                    <label
                        class="mb-2 block text-sm font-medium"
                    >

                        Conversion Factor

                    </label>

                    <input
                        v-model="form.conversion_factor"
                        type="number"
                        min="1"
                        class="w-full rounded-xl border border-gray-300 px-3 py-2"
                    />

                </div>

                <label class="flex items-center gap-2">

                    <input
                        v-model="form.is_base"
                        type="checkbox"
                    >

                    Base Unit

                </label>

                <label class="flex items-center gap-2">

                    <input
                        v-model="form.is_default"
                        type="checkbox"
                    >

                    Default Unit

                </label>

                <label class="flex items-center gap-2">

                    <input
                        v-model="form.is_active"
                        type="checkbox"
                    >

                    Active

                </label>

            </div>

        </template>

        <template #footer>

            <div
                class="flex justify-end gap-2"
            >

                <BaseButton
                    variant="secondary"
                    @click="emit('close')"
                >

                    Cancel

                </BaseButton>

                <BaseButton
                    @click="submit"
                >

                    Save

                </BaseButton>

            </div>

        </template>

    </BaseModalLayout>

</Modal>

</template>