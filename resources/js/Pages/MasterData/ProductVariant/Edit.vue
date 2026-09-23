<script setup>

import {
    Head,
    useForm,
    router,
} from '@inertiajs/vue3'

import AppLayout
    from '@/Layouts/AppLayout.vue'

import BaseButton
    from '@/Components/Button/BaseButton.vue'

import StatusBadge
    from '@/Components/Display/StatusBadge.vue'


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    title: {
        type: String,
        default: 'Edit Product Variant',
    },

    variant: {
        type: Object,
        required: true,
    },

})

console.log(
    'EDIT VARIANT:',
    JSON.stringify(
        props.variant,
        null,
        2
    )
)
/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/

const form = useForm({

    barcode:
        props.variant.barcode ?? '',

    name:
        props.variant.name ?? '',

    is_default:
        Boolean(
            props.variant.is_default
        ),

    is_active:
        Boolean(
            props.variant.is_active
        ),

    sort_order:
        props.variant.sort_order ?? 0,

})


/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/

function submit()
{

    form.put(
        route(
            'product-variants.update',
            props.variant.id
        ),
        {

            preserveScroll: true,

        }
    )

}


/*
|--------------------------------------------------------------------------
| Cancel
|--------------------------------------------------------------------------
*/

function cancel()
{

    router.get(
        route(
            'product-variants.index'
        )
    )

}

</script>


<template>

<AppLayout>

    <Head
        :title="title"
    />


    <div
        class="
            min-h-full
            bg-gray-50
            px-4
            py-6
            sm:px-6
            lg:px-8
        "
    >

        <div
            class="
                mx-auto
                max-w-5xl
            "
        >

            <!-- ===================================================== -->
            <!-- Header -->
            <!-- ===================================================== -->

            <div
                class="
                    mb-6
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
                        Edit Product Variant
                    </h1>


                    <p
                        class="
                            mt-1
                            text-sm
                            text-gray-500
                        "
                    >
                        Update Product Variant information.
                    </p>

                </div>

            </div>


            <!-- ===================================================== -->
            <!-- Product Information -->
            <!-- ===================================================== -->

            <div
                class="
                    mb-4
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
                        mb-4
                        text-sm
                        font-semibold
                        text-gray-900
                    "
                >
                    Product Information
                </div>


                <div
                    class="
                        grid
                        grid-cols-1
                        gap-4
                        sm:grid-cols-2
                    "
                >

                    <!-- Product -->

                    <div>

                        <div
                            class="
                                text-xs
                                text-gray-500
                            "
                        >
                            Product
                        </div>

                        <div
                            class="
                                mt-1
                                text-sm
                                font-medium
                                text-gray-900
                            "
                        >
                            {{
                                variant.product?.name
                                ?? '-'
                            }}
                        </div>

                    </div>


                    <!-- Product Code -->

                    <div>

                        <div
                            class="
                                text-xs
                                text-gray-500
                            "
                        >
                            Product Code
                        </div>

                        <div
                            class="
                                mt-1
                                text-sm
                                font-medium
                                text-gray-900
                            "
                        >
                            {{
                                variant.product?.code
                                ?? '-'
                            }}
                        </div>

                    </div>


                    <!-- SKU -->

                    <div>

                        <div
                            class="
                                text-xs
                                text-gray-500
                            "
                        >
                            SKU
                        </div>

                        <div
                            class="
                                mt-1
                                text-sm
                                font-medium
                                text-gray-900
                            "
                        >
                            {{
                                variant.sku
                                ?? '-'
                            }}
                        </div>

                    </div>


                    <!-- Variant -->

                    <div>

                        <div
                            class="
                                text-xs
                                text-gray-500
                            "
                        >
                            Variant
                        </div>

                        <div
                            class="
                                mt-1
                                text-sm
                                font-medium
                                text-gray-900
                            "
                        >

                            <template
                                v-if="
                                    variant.values?.length
                                "
                            >

                                <span
                                    v-for="
                                        value in
                                        variant.values
                                    "
                                    :key="
                                        value.id
                                    "
                                    class="
                                        mr-1
                                        inline-flex
                                        items-center
                                        rounded-md
                                        bg-slate-50
                                        px-2.5
                                        py-1
                                        text-xs
                                        font-medium
                                        text-slate-700
                                    "
                                >

                                    {{
                                        value.attribute
                                            ?.display_name
                                        ??
                                        value.attribute
                                            ?.name
                                        ??
                                        '-'
                                    }}

                                    <span
                                        class="
                                            mx-1
                                            text-gray-400
                                        "
                                    >
                                        :
                                    </span>

                                    {{
                                        value.attribute_value
                                            ?.display_value
                                        ??
                                        value.attribute_value
                                            ?.value
                                        ??
                                        '-'
                                    }}

                                </span>

                            </template>


                            <span
                                v-else
                                class="text-gray-500"
                            >
                                Default
                            </span>

                        </div>

                    </div>

                </div>

            </div>


            <!-- ===================================================== -->
            <!-- Variant Information -->
            <!-- ===================================================== -->

            <div
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
                        mb-5
                        text-sm
                        font-semibold
                        text-gray-900
                    "
                >
                    Variant Information
                </div>


                <div
                    class="
                        grid
                        grid-cols-1
                        gap-5
                        sm:grid-cols-2
                    "
                >

                    <!-- Variant Name -->

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
                            Variant Name
                        </label>

                        <input
                            v-model="form.name"
                            type="text"
                            class="
                                w-full
                                rounded-lg
                                border
                                border-gray-300
                                px-3
                                py-2.5
                                text-sm
                                focus:border-blue-500
                                focus:outline-none
                                focus:ring-1
                                focus:ring-blue-500
                            "
                        />

                        <p
                            v-if="
                                form.errors.name
                            "
                            class="
                                mt-1
                                text-xs
                                text-red-600
                            "
                        >
                            {{
                                form.errors.name
                            }}
                        </p>

                    </div>


                    <!-- Barcode -->

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
                            Barcode
                        </label>

                        <input
                            v-model="form.barcode"
                            type="text"
                            class="
                                w-full
                                rounded-lg
                                border
                                border-gray-300
                                px-3
                                py-2.5
                                text-sm
                                focus:border-blue-500
                                focus:outline-none
                                focus:ring-1
                                focus:ring-blue-500
                            "
                        />

                        <p
                            v-if="
                                form.errors.barcode
                            "
                            class="
                                mt-1
                                text-xs
                                text-red-600
                            "
                        >
                            {{
                                form.errors.barcode
                            }}
                        </p>

                    </div>


                    <!-- Sort Order -->

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
                            Sort Order
                        </label>

                        <input
                            v-model.number="
                                form.sort_order
                            "
                            type="number"
                            min="0"
                            class="
                                w-full
                                rounded-lg
                                border
                                border-gray-300
                                px-3
                                py-2.5
                                text-sm
                                focus:border-blue-500
                                focus:outline-none
                                focus:ring-1
                                focus:ring-blue-500
                            "
                        />

                        <p
                            v-if="
                                form.errors.sort_order
                            "
                            class="
                                mt-1
                                text-xs
                                text-red-600
                            "
                        >
                            {{
                                form.errors.sort_order
                            }}
                        </p>

                    </div>


                    <!-- Status -->

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
                            Status
                        </label>

                        <select
                            v-model="
                                form.is_active
                            "
                            class="
                                w-full
                                rounded-lg
                                border
                                border-gray-300
                                bg-white
                                px-3
                                py-2.5
                                text-sm
                                focus:border-blue-500
                                focus:outline-none
                                focus:ring-1
                                focus:ring-blue-500
                            "
                        >

                            <option
                                :value="true"
                            >
                                Active
                            </option>

                            <option
                                :value="false"
                            >
                                Inactive
                            </option>

                        </select>

                        <p
                            v-if="
                                form.errors.is_active
                            "
                            class="
                                mt-1
                                text-xs
                                text-red-600
                            "
                        >
                            {{
                                form.errors.is_active
                            }}
                        </p>

                    </div>


                    <!-- Default Variant -->

                    <div
                        class="
                            sm:col-span-2
                        "
                    >

                        <label
                            class="
                                inline-flex
                                cursor-pointer
                                items-center
                                gap-3
                            "
                        >

                            <input
                                v-model="
                                    form.is_default
                                "
                                type="checkbox"
                                class="
                                    h-4
                                    w-4
                                    rounded
                                    border-gray-300
                                    text-blue-600
                                    focus:ring-blue-500
                                "
                            />

                            <span>

                                <span
                                    class="
                                        block
                                        text-sm
                                        font-medium
                                        text-gray-700
                                    "
                                >
                                    Default Variant
                                </span>

                                <span
                                    class="
                                        block
                                        text-xs
                                        text-gray-500
                                    "
                                >
                                    Set this variant as the
                                    default variant for the product.
                                </span>

                            </span>

                        </label>

                        <p
                            v-if="
                                form.errors.is_default
                            "
                            class="
                                mt-1
                                text-xs
                                text-red-600
                            "
                        >
                            {{
                                form.errors.is_default
                            }}
                        </p>

                    </div>

                </div>


                <!-- ================================================= -->
                <!-- Actions -->
                <!-- ================================================= -->

                <div
                    class="
                        mt-6
                        flex
                        flex-col-reverse
                        gap-2
                        border-t
                        border-gray-100
                        pt-5
                        sm:flex-row
                        sm:justify-end
                    "
                >

                    <BaseButton
                        variant="secondary"
                        :disabled="form.processing"
                        @click="cancel"
                    >
                        Cancel
                    </BaseButton>


                    <BaseButton
                        :disabled="form.processing"
                        @click="submit"
                    >
                        {{
                            form.processing
                                ? 'Saving...'
                                : 'Save Changes'
                        }}
                    </BaseButton>

                </div>

            </div>

        </div>

    </div>

</AppLayout>
</template>