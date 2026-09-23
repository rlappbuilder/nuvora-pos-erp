<script setup>

import {
    ref,
} from 'vue'

import {
    router,
} from '@inertiajs/vue3'

import AppLayout from '@/Layouts/AppLayout.vue'

import BaseButton from '@/Components/Button/BaseButton.vue'

import {
    CubeIcon,
} from '@heroicons/vue/24/outline'

import {
    formatDate,
    formatDecimal,
} from '@/Utils'


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    product: {

        type: Object,

        default: () => ({}),

    },

})


/*
|--------------------------------------------------------------------------
| Tabs
|--------------------------------------------------------------------------
*/

const activeTab = ref('detail')


/*
|--------------------------------------------------------------------------
| Actions
|--------------------------------------------------------------------------
*/

function back()
{

    router.get(
        route(
            'products.index'
        )
    )

}


function edit()
{

    router.get(
        route(
            'products.edit',
            props.product.id
        )
    )

}


function print()
{

    window.open(

        route(
            'products.print',
            props.product.id
        ),

        '_blank'

    )

}

</script><template><AppLayout><div
    class="space-y-4"
>

    <!-- ========================================================= -->
    <!-- Header -->
    <!-- ========================================================= -->

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
                Product
            </h1>


            <p
                class="
                    mt-1
                    text-sm
                    text-gray-500
                "
            >
                Product detail information.
            </p>

        </div>


        <div
            class="
                flex
                items-center
                gap-2
            "
        >

            <BaseButton
                variant="secondary"
                @click="back"
            >
                Back
            </BaseButton>


            <BaseButton
                variant="secondary"
                @click="edit"
            >
                Edit
            </BaseButton>


            <BaseButton
                @click="print"
            >
                Print
            </BaseButton>

        </div>

    </div>



    <!-- ========================================================= -->
    <!-- Summary -->
    <!-- ========================================================= -->

    <div
        class="
            overflow-hidden
            rounded-xl
            border
            border-gray-100
            bg-white
            shadow-sm
        "
    >

        <div
            class="
                grid
                grid-cols-1
                divide-y
                divide-gray-100
                sm:grid-cols-2
                sm:divide-x
                sm:divide-y-0
                lg:grid-cols-4
            "
        >

            <!-- Product -->

            <div class="px-4 py-4">

                <div
                    class="
                        text-xs
                        font-medium
                        text-gray-500
                    "
                >
                    Product
                </div>


                <div
                    class="
                        mt-1
                        font-semibold
                        text-gray-900
                    "
                >
                    {{
                        product.name
                        ?? '-'
                    }}
                </div>


                <div
                    class="
                        mt-0.5
                        text-xs
                        text-gray-500
                    "
                >
                    {{
                        product.code
                        ?? '-'
                    }}
                </div>

            </div>



            <!-- SKU -->

            <div class="px-4 py-4">

                <div
                    class="
                        text-xs
                        font-medium
                        text-gray-500
                    "
                >
                    SKU
                </div>


                <div
                    class="
                        mt-1
                        font-semibold
                        text-gray-900
                    "
                >
                    {{
                        product.sku
                        ?? '-'
                    }}
                </div>


                <div
                    class="
                        mt-0.5
                        text-xs
                        text-gray-500
                    "
                >
                    Product SKU
                </div>

            </div>



            <!-- Category / Brand -->

            <div class="px-4 py-4">

                <div
                    class="
                        text-xs
                        font-medium
                        text-gray-500
                    "
                >
                    Category / Brand
                </div>


                <div
                    class="
                        mt-1
                        font-semibold
                        text-gray-900
                    "
                >
                    {{
                        product.category?.name
                        ?? '-'
                    }}
                </div>


                <div
                    class="
                        mt-0.5
                        text-xs
                        text-gray-500
                    "
                >
                    {{
                        product.brand?.name
                        ?? '-'
                    }}
                </div>

            </div>



            <!-- Status -->

            <div class="px-4 py-4">

                <div
                    class="
                        text-xs
                        font-medium
                        text-gray-500
                    "
                >
                    Status
                </div>


                <div class="mt-2">

                    <span
                        :class="[
                            'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold',
                            product.is_active
                                ? 'bg-green-100 text-green-700'
                                : 'bg-red-100 text-red-700'
                        ]"
                    >
                        {{
                            product.is_active
                                ? 'Active'
                                : 'Inactive'
                        }}
                    </span>

                </div>

            </div>

        </div>

    </div>



    <!-- ========================================================= -->
    <!-- Tabs -->
    <!-- ========================================================= -->

    <div
        class="
            overflow-hidden
            rounded-xl
            border
            border-gray-100
            bg-white
            shadow-sm
        "
    >

        <!-- Tab Navigation -->

        <div
            class="
                flex
                overflow-x-auto
                border-b
                border-gray-100
            "
        >

            <button
                type="button"
                class="
                    whitespace-nowrap
                    border-b-2
                    px-5
                    py-3
                    text-sm
                    font-medium
                    transition
                "
                :class="
                    activeTab === 'detail'
                        ? `
                            border-blue-600
                            text-blue-600
                        `
                        : `
                            border-transparent
                            text-gray-500
                            hover:text-gray-700
                        `
                "
                @click="
                    activeTab = 'detail'
                "
            >
                Product Information
            </button>


            <button
                type="button"
                class="
                    whitespace-nowrap
                    border-b-2
                    px-5
                    py-3
                    text-sm
                    font-medium
                    transition
                "
                :class="
                    activeTab === 'system'
                        ? `
                            border-blue-600
                            text-blue-600
                        `
                        : `
                            border-transparent
                            text-gray-500
                            hover:text-gray-700
                        `
                "
                @click="
                    activeTab = 'system'
                "
            >
                System Information
            </button>

        </div>



        <!-- ===================================================== -->
        <!-- Product Information Tab -->
        <!-- ===================================================== -->

        <div
            v-if="
                activeTab === 'detail'
            "
            class="space-y-4 p-4"
        >

            <!-- Product Image -->

            <div
                class="
                    overflow-hidden
                    rounded-xl
                    border
                    border-gray-100
                    bg-white
                "
            >

                <div
                    class="
                        flex
                        flex-col
                        gap-4
                        p-4
                        sm:flex-row
                        sm:items-center
                    "
                >

                    <div
                        class="
                            flex
                            h-32
                            w-32
                            shrink-0
                            items-center
                            justify-center
                            overflow-hidden
                            rounded-xl
                            bg-gray-100
                        "
                    >

                        <img
                            v-if="
                                product.primary_image?.image
                            "
                            :src="
                                `/storage/${product.primary_image.image}`
                            "
                            :alt="
                                product.name
                            "
                            class="
                                h-full
                                w-full
                                object-cover
                            "
                        />


                        <CubeIcon
                            v-else
                            class="
                                h-16
                                w-16
                                text-gray-400
                            "
                        />

                    </div>


                    <div
                        class="min-w-0"
                    >

                        <div
                            class="
                                text-lg
                                font-semibold
                                text-gray-900
                            "
                        >
                            {{
                                product.name
                                ?? '-'
                            }}
                        </div>


                        <div
                            class="
                                mt-1
                                text-sm
                                text-gray-500
                            "
                        >
                            {{
                                product.code
                                ?? '-'
                            }}

                            <span
                                class="mx-1 text-gray-300"
                            >
                                •
                            </span>

                            {{
                                product.sku
                                ?? '-'
                            }}
                        </div>

                    </div>

                </div>

            </div>



            <!-- Basic Information -->

            <div
                class="
                    overflow-hidden
                    rounded-xl
                    border
                    border-gray-100
                    bg-white
                "
            >

                <div
                    class="
                        border-b
                        border-gray-100
                        px-4
                        py-3
                    "
                >

                    <div
                        class="
                            text-sm
                            font-semibold
                            text-gray-900
                        "
                    >
                        Basic Information
                    </div>


                    <div
                        class="
                            mt-0.5
                            text-xs
                            text-gray-500
                        "
                    >
                        Product master data.
                    </div>

                </div>


                <div
                    class="
                        grid
                        grid-cols-1
                        divide-y
                        divide-gray-100
                        sm:grid-cols-2
                        sm:divide-x
                        sm:divide-y-0
                    "
                >

                    <!-- Code -->

                    <div class="px-4 py-3">

                        <div
                            class="
                                text-xs
                                font-medium
                                text-gray-500
                            "
                        >
                            Code
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
                                product.code
                                ?? '-'
                            }}
                        </div>

                    </div>


                    <!-- SKU -->

                    <div class="px-4 py-3">

                        <div
                            class="
                                text-xs
                                font-medium
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
                                product.sku
                                ?? '-'
                            }}
                        </div>

                    </div>


                    <!-- Name -->

                    <div class="px-4 py-3">

                        <div
                            class="
                                text-xs
                                font-medium
                                text-gray-500
                            "
                        >
                            Name
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
                                product.name
                                ?? '-'
                            }}
                        </div>

                    </div>


                    <!-- Category -->

                    <div class="px-4 py-3">

                        <div
                            class="
                                text-xs
                                font-medium
                                text-gray-500
                            "
                        >
                            Category
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
                                product.category?.name
                                ?? '-'
                            }}
                        </div>

                    </div>


                    <!-- Brand -->

                    <div class="px-4 py-3">

                        <div
                            class="
                                text-xs
                                font-medium
                                text-gray-500
                            "
                        >
                            Brand
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
                                product.brand?.name
                                ?? '-'
                            }}
                        </div>

                    </div>


                    <!-- Unit -->

                    <div class="px-4 py-3">

                        <div
                            class="
                                text-xs
                                font-medium
                                text-gray-500
                            "
                        >
                            Unit
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
                                product.unit?.name
                                ?? '-'
                            }}
                        </div>

                    </div>


                    <!-- Product Type -->

                    <div class="px-4 py-3">

                        <div
                            class="
                                text-xs
                                font-medium
                                text-gray-500
                            "
                        >
                            Product Type
                        </div>


                        <div class="mt-1">

                            <span
                                :class="[
                                    'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold',
                                    product.product_type === 'PRODUCT'
                                        ? 'bg-blue-100 text-blue-700'
                                        : 'bg-purple-100 text-purple-700'
                                ]"
                            >
                                {{
                                    product.product_type
                                    ?? '-'
                                }}
                            </span>

                        </div>

                    </div>


                    <!-- Track Stock -->

                    <div class="px-4 py-3">

                        <div
                            class="
                                text-xs
                                font-medium
                                text-gray-500
                            "
                        >
                            Track Stock
                        </div>


                        <div class="mt-1">

                            <span
                                :class="[
                                    'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold',
                                    product.track_stock
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-red-100 text-red-700'
                                ]"
                            >
                                {{
                                    product.track_stock
                                        ? '✓ Yes'
                                        : '✕ No'
                                }}
                            </span>

                        </div>

                    </div>


                    <!-- Sellable -->

                    <div class="px-4 py-3">

                        <div
                            class="
                                text-xs
                                font-medium
                                text-gray-500
                            "
                        >
                            Sellable
                        </div>


                        <div class="mt-1">

                            <span
                                :class="[
                                    'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold',
                                    product.is_sellable
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-red-100 text-red-700'
                                ]"
                            >
                                {{
                                    product.is_sellable
                                        ? '✓ Yes'
                                        : '✕ No'
                                }}
                            </span>

                        </div>

                    </div>


                    <!-- Minimum Stock -->

                    <div class="px-4 py-3">

                        <div
                            class="
                                text-xs
                                font-medium
                                text-gray-500
                            "
                        >
                            Minimum Stock
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
                                formatDecimal(
                                    product.minimum_stock
                                )
                            }}
                        </div>

                    </div>


                    <!-- Status -->

                    <div class="px-4 py-3">

                        <div
                            class="
                                text-xs
                                font-medium
                                text-gray-500
                            "
                        >
                            Status
                        </div>


                        <div class="mt-1">

                            <span
                                :class="[
                                    'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold',
                                    product.is_active
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-red-100 text-red-700'
                                ]"
                            >
                                {{
                                    product.is_active
                                        ? 'Active'
                                        : 'Inactive'
                                }}
                            </span>

                        </div>

                    </div>

                </div>

            </div>



            <!-- Description -->

            <div
                class="
                    overflow-hidden
                    rounded-xl
                    border
                    border-gray-100
                    bg-white
                "
            >

                <div
                    class="
                        border-b
                        border-gray-100
                        px-4
                        py-3
                    "
                >

                    <div
                        class="
                            text-sm
                            font-semibold
                            text-gray-900
                        "
                    >
                        Description
                    </div>

                </div>


                <div
                    class="
                        whitespace-pre-line
                        px-4
                        py-4
                        text-sm
                        leading-7
                        text-gray-700
                    "
                >
                    {{
                        product.description
                        || '-'
                    }}
                </div>

            </div>

        </div>



        <!-- ===================================================== -->
        <!-- System Information Tab -->
        <!-- ===================================================== -->

        <div
            v-else-if="
                activeTab === 'system'
            "
            class="space-y-4 p-4"
        >

            <div
                class="
                    overflow-hidden
                    rounded-xl
                    border
                    border-gray-100
                    bg-white
                "
            >

                <div
                    class="
                        border-b
                        border-gray-100
                        px-4
                        py-3
                    "
                >

                    <div
                        class="
                            text-sm
                            font-semibold
                            text-gray-900
                        "
                    >
                        System Information
                    </div>


                    <div
                        class="
                            mt-0.5
                            text-xs
                            text-gray-500
                        "
                    >
                        Audit information.
                    </div>

                </div>


                <div
                    class="
                        grid
                        grid-cols-1
                        divide-y
                        divide-gray-100
                        sm:grid-cols-2
                        sm:divide-x
                        sm:divide-y-0
                    "
                >

                    <!-- Created By -->

                    <div class="px-4 py-4">

                        <div
                            class="
                                text-xs
                                font-medium
                                text-gray-500
                            "
                        >
                            Created By
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
                                product.creator?.name
                                ?? '-'
                            }}
                        </div>

                    </div>


                    <!-- Created At -->

                    <div class="px-4 py-4">

                        <div
                            class="
                                text-xs
                                font-medium
                                text-gray-500
                            "
                        >
                            Created At
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
                                formatDate(
                                    product.created_at
                                )
                            }}
                        </div>

                    </div>


                    <!-- Updated By -->

                    <div class="px-4 py-4">

                        <div
                            class="
                                text-xs
                                font-medium
                                text-gray-500
                            "
                        >
                            Updated By
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
                                product.updater?.name
                                ?? '-'
                            }}
                        </div>

                    </div>


                    <!-- Updated At -->

                    <div class="px-4 py-4">

                        <div
                            class="
                                text-xs
                                font-medium
                                text-gray-500
                            "
                        >
                            Updated At
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
                                formatDate(
                                    product.updated_at
                                )
                            }}
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

</AppLayout></template>