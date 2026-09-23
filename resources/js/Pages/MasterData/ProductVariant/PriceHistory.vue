<script setup>

import {
    ref,
    computed,
    watch,
} from 'vue'

const props = defineProps({

    show: {
        type: Boolean,
        default: false,
    },

    variant: {
        type: Object,
        default: null,
    },

})

const emit = defineEmits([
    'close',
])

const history = ref([])

const loading = ref(false)

const error = ref(null)


const product = computed(() => {

    return history.value?.[0]?.variant?.product
        ?? props.variant?.product
        ?? null

})


const variantName = computed(() => {

    return history.value?.[0]?.variant?.name
        ?? props.variant?.name
        ?? '-'

})


const variantSku = computed(() => {

    return history.value?.[0]?.variant?.sku
        ?? props.variant?.sku
        ?? '-'

})


const fetchHistory = async () => {

    if (!props.variant?.id) {
        return
    }

    loading.value = true

    error.value = null

    history.value = []

    try {

        const response = await fetch(

            route(
                'product-variants.price-history',
                props.variant.id
            ),

            {
                method: 'GET',

                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },

            }

        )

        if (!response.ok) {

            throw new Error(
                `HTTP ${response.status}`
            )

        }

        history.value = await response.json()

    } catch (e) {

        console.error(
            'PRODUCT VARIANT PRICE HISTORY ERROR:',
            e
        )

        error.value =
            'Gagal mengambil price history.'

    } finally {

        loading.value = false

    }

}


const close = () => {

    emit('close')

}


const handleOverlayClick = () => {

    close()

}


const handleKeydown = (event) => {

    if (
        event.key === 'Escape'
        && props.show
    ) {

        close()

    }

}


const formatDate = (value) => {

    if (!value) {
        return '-'
    }

    const date = new Date(value)

    if (Number.isNaN(date.getTime())) {
        return value
    }

    return new Intl.DateTimeFormat(
        'id-ID',
        {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        }
    ).format(date)

}


const formatNumber = (value) => {

    return new Intl.NumberFormat(
        'id-ID',
        {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2,
        }
    ).format(
        Number(value ?? 0)
    )

}


const statusClass = (item) => {

    return item.is_active

        ? `
            bg-emerald-50
            text-emerald-700
            ring-1
            ring-inset
            ring-emerald-600/20
        `

        : `
            bg-gray-100
            text-gray-600
            ring-1
            ring-inset
            ring-gray-500/10
        `

}


watch(

    () => props.show,

    (value) => {

        if (value) {

            document.addEventListener(
                'keydown',
                handleKeydown
            )

            fetchHistory()

        } else {

            document.removeEventListener(
                'keydown',
                handleKeydown
            )

        }

    }

)

</script><template><Transition
    enter-active-class="duration-200 ease-out"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="duration-150 ease-in"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
>

    <div
        v-if="show"
        class="
            fixed
            inset-0
            z-[100]
            flex
            items-center
            justify-center
            bg-slate-950/50
            p-4
            backdrop-blur-[2px]
        "
        @click="handleOverlayClick"
    >

        <!-- Modal -->

        <div
            class="
                flex
                w-full
                max-w-[1400px]
                max-h-[92vh]
                flex-col
                overflow-hidden
                rounded-2xl
                border
                border-slate-200
                bg-white
                shadow-2xl
            "
            @click.stop
        >

            <!-- ================================================== -->
            <!-- HEADER -->
            <!-- ================================================== -->

            <div
                class="
                    sticky
                    top-0
                    z-30
                    shrink-0
                    border-b
                    border-slate-200
                    bg-white
                "
            >

                <div
                    class="
                        flex
                        items-center
                        justify-between
                        px-6
                        py-4
                    "
                >

                    <!-- Title -->

                    <div
                        class="
                            flex
                            min-w-0
                            items-center
                            gap-3
                        "
                    >

                        <div
                            class="
                                flex
                                h-10
                                w-10
                                shrink-0
                                items-center
                                justify-center
                                rounded-xl
                                bg-blue-50
                                text-blue-600
                            "
                        >

                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M3 3v18h18"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M7 16l4-5 3 3 5-7"
                                />
                            </svg>

                        </div>


                        <div class="min-w-0">

                            <h2
                                class="
                                    truncate
                                    text-lg
                                    font-semibold
                                    tracking-tight
                                    text-slate-900
                                "
                            >
                                Price History
                            </h2>

                            <p
                                class="
                                    mt-0.5
                                    text-xs
                                    text-slate-500
                                "
                            >
                                Riwayat harga Product Variant
                            </p>

                        </div>

                    </div>


                    <!-- Close -->

                    <button
                        type="button"
                        class="
                            ml-4
                            flex
                            h-9
                            w-9
                            shrink-0
                            items-center
                            justify-center
                            rounded-lg
                            text-slate-400
                            transition
                            hover:bg-slate-100
                            hover:text-slate-700
                            focus:outline-none
                            focus:ring-2
                            focus:ring-blue-500/20
                        "
                        aria-label="Close"
                        @click="close"
                    >

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M6 6l12 12M18 6L6 18"
                            />
                        </svg>

                    </button>

                </div>


                <!-- Variant Summary -->

                <div
                    class="
                        border-t
                        border-slate-100
                        bg-slate-50/70
                        px-6
                        py-3
                    "
                >

                    <div
                        class="
                            flex
                            flex-wrap
                            items-center
                            gap-x-8
                            gap-y-2
                        "
                    >

                        <div>

                            <div
                                class="
                                    text-[10px]
                                    font-semibold
                                    uppercase
                                    tracking-wider
                                    text-slate-400
                                "
                            >
                                Product
                            </div>

                            <div
                                class="
                                    mt-0.5
                                    text-sm
                                    font-medium
                                    text-slate-800
                                "
                            >
                                {{ product?.name ?? '-' }}

                                <span
                                    v-if="product?.code"
                                    class="text-slate-400"
                                >
                                    · {{ product.code }}
                                </span>

                            </div>

                        </div>


                        <div class="hidden h-7 w-px bg-slate-200 md:block"></div>


                        <div>

                            <div
                                class="
                                    text-[10px]
                                    font-semibold
                                    uppercase
                                    tracking-wider
                                    text-slate-400
                                "
                            >
                                Variant
                            </div>

                            <div
                                class="
                                    mt-0.5
                                    text-sm
                                    font-medium
                                    text-slate-800
                                "
                            >
                                {{ variantName }}

                                <span
                                    v-if="variantSku !== '-'"
                                    class="text-slate-400"
                                >
                                    · {{ variantSku }}
                                </span>

                            </div>

                        </div>


                        <div
                            v-if="history.length"
                            class="
                                ml-auto
                                rounded-full
                                bg-blue-50
                                px-3
                                py-1.5
                                text-xs
                                font-semibold
                                text-blue-700
                            "
                        >
                            {{ history.length }} record
                        </div>

                    </div>

                </div>

            </div>


            <!-- ================================================== -->
            <!-- CONTENT -->
            <!-- ================================================== -->

            <div
                class="
                    min-h-0
                    flex-1
                    overflow-auto
                    bg-white
                "
            >

                <!-- Loading -->

                <div
                    v-if="loading"
                    class="
                        flex
                        min-h-[360px]
                        items-center
                        justify-center
                    "
                >

                    <div
                        class="
                            flex
                            flex-col
                            items-center
                            gap-3
                        "
                    >

                        <div
                            class="
                                h-8
                                w-8
                                animate-spin
                                rounded-full
                                border-2
                                border-slate-200
                                border-t-blue-600
                            "
                        ></div>

                        <span
                            class="
                                text-sm
                                text-slate-500
                            "
                        >
                            Loading price history...
                        </span>

                    </div>

                </div>


                <!-- Error -->

                <div
                    v-else-if="error"
                    class="p-6"
                >

                    <div
                        class="
                            rounded-xl
                            border
                            border-red-200
                            bg-red-50
                            px-4
                            py-4
                            text-sm
                            text-red-700
                        "
                    >

                        <div
                            class="
                                font-semibold
                            "
                        >
                            Unable to load price history
                        </div>

                        <div class="mt-1">
                            {{ error }}
                        </div>

                    </div>

                </div>


                <!-- Empty -->

                <div
                    v-else-if="!history.length"
                    class="
                        flex
                        min-h-[360px]
                        items-center
                        justify-center
                        px-6
                    "
                >

                    <div
                        class="
                            text-center
                        "
                    >

                        <div
                            class="
                                mx-auto
                                flex
                                h-12
                                w-12
                                items-center
                                justify-center
                                rounded-xl
                                bg-slate-100
                                text-slate-400
                            "
                        >

                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-6 w-6"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 8v4l2.5 2.5"
                                />
                                <circle
                                    cx="12"
                                    cy="12"
                                    r="9"
                                />
                            </svg>

                        </div>

                        <div
                            class="
                                mt-4
                                text-sm
                                font-semibold
                                text-slate-700
                            "
                        >
                            Belum ada price history
                        </div>

                        <div
                            class="
                                mt-1
                                text-sm
                                text-slate-500
                            "
                        >
                            Belum terdapat riwayat harga untuk
                            variant ini.
                        </div>

                    </div>

                </div>


                <!-- History Table -->

                <div
                    v-else
                    class="min-w-full"
                >

                    <table
                        class="
                            min-w-[1100px]
                            w-full
                            border-collapse
                        "
                    >

                        <thead
                            class="
                                sticky
                                top-0
                                z-20
                                bg-slate-50
                            "
                        >

                            <tr
                                class="
                                    border-b
                                    border-slate-200
                                "
                            >

                                <th
                                    class="
                                        whitespace-nowrap
                                        px-6
                                        py-3
                                        text-left
                                        text-[11px]
                                        font-semibold
                                        uppercase
                                        tracking-wider
                                        text-slate-500
                                    "
                                >
                                    Effective From
                                </th>

                                <th
                                    class="
                                        whitespace-nowrap
                                        px-6
                                        py-3
                                        text-left
                                        text-[11px]
                                        font-semibold
                                        uppercase
                                        tracking-wider
                                        text-slate-500
                                    "
                                >
                                    Effective Until
                                </th>

                                <th
                                    class="
                                        whitespace-nowrap
                                        px-6
                                        py-3
                                        text-left
                                        text-[11px]
                                        font-semibold
                                        uppercase
                                        tracking-wider
                                        text-slate-500
                                    "
                                >
                                    Branch
                                </th>

                                <th
                                    class="
                                        whitespace-nowrap
                                        px-6
                                        py-3
                                        text-left
                                        text-[11px]
                                        font-semibold
                                        uppercase
                                        tracking-wider
                                        text-slate-500
                                    "
                                >
                                    Unit
                                </th>

                                <th
                                    class="
                                        whitespace-nowrap
                                        px-6
                                        py-3
                                        text-left
                                        text-[11px]
                                        font-semibold
                                        uppercase
                                        tracking-wider
                                        text-slate-500
                                    "
                                >
                                    Price Type
                                </th>

                                <th
                                    class="
                                        whitespace-nowrap
                                        px-6
                                        py-3
                                        text-right
                                        text-[11px]
                                        font-semibold
                                        uppercase
                                        tracking-wider
                                        text-slate-500
                                    "
                                >
                                    Last Purchase
                                </th>

                                <th
                                    class="
                                        whitespace-nowrap
                                        px-6
                                        py-3
                                        text-right
                                        text-[11px]
                                        font-semibold
                                        uppercase
                                        tracking-wider
                                        text-slate-500
                                    "
                                >
                                    Selling Price
                                </th>

                                <th
                                    class="
                                        whitespace-nowrap
                                        px-6
                                        py-3
                                        text-center
                                        text-[11px]
                                        font-semibold
                                        uppercase
                                        tracking-wider
                                        text-slate-500
                                    "
                                >
                                    Status
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <tr
                                v-for="item in history"
                                :key="item.id"
                                class="
                                    border-b
                                    border-slate-100
                                    transition
                                    last:border-b-0
                                    hover:bg-slate-50/70
                                "
                            >

                                <td
                                    class="
                                        whitespace-nowrap
                                        px-6
                                        py-3.5
                                        text-sm
                                        font-medium
                                        text-slate-800
                                    "
                                >
                                    {{ formatDate(item.effective_from) }}
                                </td>


                                <td
                                    class="
                                        whitespace-nowrap
                                        px-6
                                        py-3.5
                                        text-sm
                                        text-slate-600
                                    "
                                >

                                    <span
                                        v-if="item.effective_until"
                                    >
                                        {{
                                            formatDate(
                                                item.effective_until
                                            )
                                        }}
                                    </span>

                                    <span
                                        v-else
                                        class="
                                            inline-flex
                                            rounded-md
                                            bg-blue-50
                                            px-2
                                            py-1
                                            text-[11px]
                                            font-semibold
                                            text-blue-700
                                        "
                                    >
                                        Current
                                    </span>

                                </td>


                                <td
                                    class="
                                        whitespace-nowrap
                                        px-6
                                        py-3.5
                                        text-sm
                                        text-slate-700
                                    "
                                >
                                    {{ item.branch?.name ?? '-' }}
                                </td>


                                <td
                                    class="
                                        whitespace-nowrap
                                        px-6
                                        py-3.5
                                        text-sm
                                        text-slate-700
                                    "
                                >
                                    {{ item.unit?.name ?? '-' }}
                                </td>


                                <td
                                    class="
                                        whitespace-nowrap
                                        px-6
                                        py-3.5
                                        text-sm
                                        text-slate-700
                                    "
                                >
                                    {{ item.price_type?.name ?? '-' }}
                                </td>


                                <td
                                    class="
                                        whitespace-nowrap
                                        px-6
                                        py-3.5
                                        text-right
                                        text-sm
                                        tabular-nums
                                        text-slate-700
                                    "
                                >
                                    {{ formatNumber(item.last_purchase_price) }}
                                </td>


                                <td
                                    class="
                                        whitespace-nowrap
                                        px-6
                                        py-3.5
                                        text-right
                                        text-sm
                                        font-semibold
                                        tabular-nums
                                        text-slate-900
                                    "
                                >
                                    {{ formatNumber(item.selling_price) }}
                                </td>


                                <td
                                    class="
                                        whitespace-nowrap
                                        px-6
                                        py-3.5
                                        text-center
                                    "
                                >

                                    <span
                                        class="
                                            inline-flex
                                            rounded-full
                                            px-2.5
                                            py-1
                                            text-[11px]
                                            font-semibold
                                        "
                                        :class="statusClass(item)"
                                    >
                                        {{
                                            item.is_active
                                                ? 'Active'
                                                : 'Inactive'
                                        }}
                                    </span>

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>


            <!-- ================================================== -->
            <!-- FOOTER -->
            <!-- ================================================== -->

            <div
                class="
                    sticky
                    bottom-0
                    z-30
                    flex
                    shrink-0
                    items-center
                    justify-between
                    border-t
                    border-slate-200
                    bg-white
                    px-6
                    py-3
                "
            >

                <div
                    class="
                        text-xs
                        text-slate-400
                    "
                >
                    Price history · Product Variant
                </div>


                <button
                    type="button"
                    class="
                        rounded-lg
                        border
                        border-slate-300
                        bg-white
                        px-4
                        py-2
                        text-sm
                        font-medium
                        text-slate-700
                        shadow-sm
                        transition
                        hover:bg-slate-50
                        hover:text-slate-900
                        focus:outline-none
                        focus:ring-2
                        focus:ring-blue-500/20
                    "
                    @click="close"
                >
                    Close
                </button>

            </div>

        </div>

    </div>

</Transition>

</template>