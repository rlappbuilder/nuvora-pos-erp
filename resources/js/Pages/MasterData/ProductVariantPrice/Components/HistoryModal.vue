<script setup>

import { ref, watch,computed, } from 'vue'
import axios from 'axios'
import BaseModalLayout from '@/Components/Modal/BaseModalLayout.vue'
import LoadingOverlay from '@/Components/Feedback/LoadingOverlay.vue'
import Modal from '@/Components/Modal.vue'
import BaseButton from '@/Components/Button/BaseButton.vue'
import { formatCurrency,} from '@/Utils/currency'

import { CubeIcon,} from '@heroicons/vue/24/outline'

import {

    formatDate,

} from '@/Utils/date'
const props = defineProps({

    show: Boolean,

    productVariantPrice: Object,

})

const emit = defineEmits([

    'close',

])

const histories = ref([])

const loading = ref(false)
const calculateMargin = (

    purchase,

    selling

) => {

    purchase = Number(
        purchase || 0
    )

    selling = Number(
        selling || 0
    )

    if (purchase <= 0) {

        return '-'

    }

    return (

        (

            (selling - purchase)

            / purchase

        ) * 100

    ).toFixed(2) + '%'

}
watch(

    () => props.show,

    async (show) => {

        if (

            !show ||

            !props.productVariantPrice

        ) {

            return

        }

        loading.value = true

        try {

            const {

                data,

            } = await axios.get(

                route(

                    'product-variant-prices.history',

                    props.productVariantPrice.id

                )

            )

            histories.value = data

        }

        catch (

            error

        ) {

            console.error(error)

        }

        finally {

            loading.value = false

        }

    },

    {

        immediate: true,

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

        title="Price History"

        subtitle="Product Variant Price History"

    >
        <template #icon>

            <CubeIcon
                class="h-6 w-6 text-indigo-600"
            />

        </template>
        <template #content>

            <LoadingOverlay
                :show="loading"
            />
            
            <!-- start div-->
           <div

                class="
                    max-h-[520px]
                    space-y-4
                    overflow-y-auto
                    px-2
                "

            >
    
                    <!-- information-->
                         <div
                            v-if="histories.length"
                            class="
                                mb-4
                                rounded-xl
                                border
                                border-indigo-100
                                bg-indigo-50
                                p-4
                            "
                        >

                            <div
                                class="
                                    mb-3
                                    text-lg
                                    font-semibold
                                    text-indigo-700
                                "
                            >

                                Product Information

                            </div>

                            <div
                                class="
                                    grid
                                    grid-cols-2
                                    gap-3
                                    text-sm
                                "
                            >

                                <div>

                                    <span class="font-medium">
                                        Product
                                    </span>

                                    <div>

                                        {{ histories[0].variant.product.name }}

                                    </div>

                                </div>

                                <div>

                                    <span class="font-medium">
                                        Variant
                                    </span>

                                    <div>

                                        {{ histories[0].variant.name }}

                                    </div>

                                </div>

                                <div>

                                    <span class="font-medium">
                                        Branch
                                    </span>

                                    <div>

                                        {{ histories[0].branch.name }}

                                    </div>

                                </div>

                                <div>

                                    <span class="font-medium">
                                        Unit
                                    </span>

                                    <div>

                                        {{ histories[0].unit.name }}

                                    </div>

                                </div>

                                <div>

                                    <span class="font-medium">
                                        Price Type
                                    </span>

                                    <div>

                                        {{ histories[0].price_type.name }}

                                    </div>

                                </div>

                                <div>

                                    <span class="font-medium">
                                        Total History
                                    </span>

                                    <div>

                                        {{ histories.length }}

                                    </div>

                                </div>

                            </div>

                        </div>
                        <!-- end information-->
                <div

                    v-if="

                        !loading &&

                        histories.length === 0

                    "

                    class="

                        py-10

                        text-center

                        text-gray-500

                    "

                >

                    No Price History

                </div>

                <div

                    v-for="(history, index) in histories"

                    :key="history.id"

                    class="

                        rounded-xl

                        border

                        border-gray-200

                        bg-white

                        p-4

                        shadow-sm

                    "

                >
                <!-- curren-->
                <div
                    class="flex items-center gap-2"
                >

                    <span
                        class="font-semibold"
                    >

                        {{ formatDate(history.effective_from) }}

                    </span>

                    <span
                        v-if="index === 0"
                        class="
                            rounded-full
                            bg-green-100
                            px-2
                            py-0.5
                            text-xs
                            font-semibold
                            text-green-700
                        "
                    >

                        Current

                    </span>

                </div>     
            <!-- end current-->
                    <div

                        class="

                            flex

                            items-center

                            justify-between

                            border-b

                            pb-3

                        "

                    >
                    

                        <div>


                            <div

                                class="

                                    text-xs

                                    text-gray-500

                                "

                            >

                                Effective Date

                            </div>

                        </div>
                        
                        <span

                            class="

                                rounded-full

                                bg-indigo-100

                                px-3

                                py-1

                                text-xs

                                font-semibold

                                text-indigo-700

                            "

                        >

                            {{ history.price_type.name }}

                        </span>

                    </div>
                        
                    <div

                        class="

                            mt-4

                            grid

                            grid-cols-2

                            gap-y-3

                            text-sm

                        "

                    >

                        <span>

                            Purchase Price

                        </span>

                        <span class="text-right">

                            {{ formatCurrency(

                                history.last_purchase_price

                            ) }}

                        </span>

                        <span>

                            Selling Price

                        </span>

                        <span class="text-right">

                            {{ formatCurrency(

                                history.selling_price

                            ) }}

                        </span>

                        <span>

                            Margin

                        </span>

                        <span

                            class="

                                text-right

                                font-semibold

                                text-green-600

                            "

                        >

                            {{

                                calculateMargin(

                                    history.last_purchase_price,

                                    history.selling_price

                                )

                            }}

                        </span>

                        <span>

                            Effective Until

                        </span>

                        <span class="text-right">

                            {{

                                history.effective_until

                                    ? formatDate(

                                        history.effective_until

                                    )

                                    : '-'

                            }}

                        </span>

                    </div>

                </div>

            </div>
            <!-- end -->
        </template>

        <template #footer>

            <div class="flex justify-end">

                <BaseButton

                    variant="secondary"

                    @click="emit('close')"

                >

                    Close

                </BaseButton>

            </div>

        </template>

    </BaseModalLayout>

</Modal>

</template>