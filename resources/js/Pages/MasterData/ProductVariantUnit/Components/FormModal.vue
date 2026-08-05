<script setup>

import Modal from '@/Components/Modal.vue'
import BaseButton from '@/Components/Button/BaseButton.vue'
import SearchableSelect from '@/Components/Form/SearchableSelect.vue'
import BaseModalLayout from '@/Components/Modal/BaseModalLayout.vue'
import FormCheckbox from '@/Components/Form/FormCheckbox.vue'
import FormField from '@/Components/Form/FormField.vue'
import FormInput from '@/Components/Form/FormInput.vue'
import axios from 'axios'
import { useForm } from '@inertiajs/vue3'
import {
    success,
    error,
} from '@/Utils'
import {
    CubeIcon,
} from '@heroicons/vue/24/outline'
import { ref, watch, computed } from 'vue'
const props = defineProps({

    show: Boolean,

    variants: Array,

    units: Array,

    productVariantUnit: {

        type: Object,

        default: null,

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
const disableConversionFactor = computed(() => {

    return form.is_base

})
const isEdit = computed(() => {

    return !!props.productVariantUnit

})

const disableSubmit = computed(() => {

    if (form.processing) {

        return true

    }

    if (isEdit.value) {

        return false

    }

    return noAvailableUnits.value

})


function submit()
{
    if (isEdit.value) {

        form.put(

            route(

                'product-variant-units.update',

                props.productVariantUnit.id

            ),

            {

                preserveScroll: true,
                onError: (errors) => {

                    console.log('Validation Errors:', errors)

                },

                onSuccess: () => {

                    success(
                        'Product Variant Unit updated successfully.'
                    )

                    emit('saved')

                    emit('close')

                },

            }

        )

        return

    }

            form.post(

            route('product-variant-units.store'),

            {

                preserveScroll: true,

                onError: (errors) => {

                    console.log('Validation Errors:', errors)

                },

                onSuccess: () => {

                    success(
                        'Product Variant Unit created successfully.'
                    )

                    emit('saved')

                    emit('close')

                },

            }

        )
}
function resetForm()
{
    form.reset()

    form.clearErrors()

    unitOptions.value = []

    loadingUnits.value = false
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
    if (!isEdit.value) {

        form.unit_id = ''

    }

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
            ),

            {
                params: {
                    current_unit: props.productVariantUnit?.unit_id
                }
            }

        )

        unitOptions.value = data

        if (
            isEdit.value &&
            props.productVariantUnit
        ) {

            form.unit_id =
                props.productVariantUnit.unit_id

        }

        form.clearErrors('unit_id')

    } finally {

        loadingUnits.value = false

    }
}
watch(
    () => props.show,
    (show) => {

        if (!show) {

            resetForm()

            unitOptions.value = []

        }

    }
)
watch(
    () => form.product_variant_id,
    async (variantId) => {

        if (!props.show) {

            return

        }

        await loadAvailableUnits(
            variantId
        )

    }
)
watch(

        () => props.productVariantUnit,

    (value) => {

    if (!value) return

    form.product_variant_id = value.product_variant_id
   // form.unit_id = value.unit_id
    form.conversion_factor = +value.conversion_factor
    form.is_base = value.is_base
    form.is_default = value.is_default
    form.is_active = value.is_active

},

    {

        immediate: true,

    }

)
watch(

    () => form.is_base,

    (value) => {

        if (value) {

            form.conversion_factor = 1

        }

    }

)
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
                    ? 'Edit Product Variant Unit'
                    : 'Create Product Variant Unit'
            "
            :subtitle="
                isEdit
                    ? 'Update unit conversion for Product Variant.'
                    : 'Add unit conversion for Product Variant.'
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
        <FormField
            label="Product Variant"
            required
        >

            <SearchableSelect
                v-model="form.product_variant_id"
                :options="variants"
                label="label"
                value-key="id"
                :disabled="isEdit"
                :error="form.errors.product_variant_id"
            />

        </FormField>

        <!-- Unit -->
        <div v-if="!noAvailableUnits">

            <FormField
                label="Unit"
                required
            >

                <SearchableSelect
                    v-model="form.unit_id"
                    :options="unitOptions"
                    label="label"
                    value-key="id"
                    :error="form.errors.unit_id"
                />

            </FormField>

        </div>

        <!-- Empty State -->
                <div
            v-else
            class="
                rounded-2xl
                border
                border-blue-200
                bg-blue-50
                p-5
            "
        >

            <div class="flex items-start gap-3">

                <div
                    class="
                        flex
                        h-10
                        w-10
                        items-center
                        justify-center
                        rounded-full
                        bg-blue-100
                    "
                >

                    📦

                </div>

                <div class="flex-1">

                    <h3
                        class="
                            text-base
                            font-semibold
                            text-blue-900
                        "
                    >

                        All Units Assigned

                    </h3>

                    <p
                        class="
                            mt-1
                            text-sm
                            text-blue-700
                        "
                    >

                        This Product Variant already has all available units assigned.

                    </p>

                    <p
                        class="
                            mt-2
                            text-xs
                            text-red-600
                        "
                    >

                        You can edit an existing unit to change the Conversion Factor,
                        Base Unit, or Default Unit !!.

                    </p>

                </div>

            </div>

        </div>

        <!-- Conversion -->
        <FormField
            label="Conversion Factor"
            required
            :error="form.errors.conversion_factor"
        >

            <FormInput
                v-model="form.conversion_factor"
                type="number"
                min="1"
                step="0.01"
                :disabled="disableConversionFactor"
            />
                <p
    class="mt-2 min-h-[20px] text-xs transition-opacity duration-200"
    :class="
        form.is_base
            ? 'opacity-100 text-blue-600'
            : 'opacity-0'
    "
>
    Base Unit always uses a Conversion Factor of 1.
</p>
        </FormField>
        <!-- checkbox-->
         <div class="space-y-3">

    <label class="flex items-center gap-3">

        <FormCheckbox
            v-model="form.is_base"
        />

        <span class="text-sm font-medium text-gray-700">

            Base Unit

        </span>

    </label>

    <label class="flex items-center gap-3">

        <FormCheckbox
            v-model="form.is_default"
        />

        <span class="text-sm font-medium text-gray-700">

            Default Unit

        </span>

    </label>

    <label class="flex items-center gap-3">

        <FormCheckbox
            v-model="form.is_active"
        />

        <span class="text-sm font-medium text-gray-700">

            Active

        </span>

    </label>

</div>
        <!-- end checbox-->
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
                    :disabled="disableSubmit"
                    :loading="form.processing"
                    :title="
                        noAvailableUnits && !isEdit
                            ? 'All units have already been assigned.'
                            : ''
                    "
                    @click="submit"
                >
                    {{ isEdit ? 'Update' : 'Save' }}
                </BaseButton>

            </div>

        </template>

    </BaseModalLayout>

</Modal>

</template>