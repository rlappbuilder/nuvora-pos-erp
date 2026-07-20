<script setup>

import {

    Head,

    useForm,

    router,

} from '@inertiajs/vue3'

import {

    ref,

    computed,

    watch,

} from 'vue'


import FlatPickr

from 'vue-flatpickr-component'

import 'flatpickr/dist/flatpickr.css'

import AppLayout from '@/Layouts/AppLayout.vue'

import SearchableSelect

from '@/Components/Form/SearchableSelect.vue'

const props = defineProps({

    warehouses: Array,

    products: Array,

    issueNumber: String,

    issueTypes: Array,

})

const dateConfig = {

    dateFormat: 'Y-m-d',

}

const selectedProduct = ref(

    null

)



const form = useForm({

    issue_date:

        new Date()

        .toISOString()

        .slice(

            0,

            10

        ),

    warehouse_id: '',

    issue_type: '',

    reference_number: '',

    remarks: '',

    items: [],

})

const totalQty = computed(

    () =>

        form.items.reduce(

            (

                total,

                item

            ) =>

                total +

                Number(

                    item.qty

                ),

            0

        )

)

const totalValue = computed(

    () =>

        form.items.reduce(

            (

                total,

                item

            ) =>

                total +

                Number(

                    item.total_cost

                ),

            0

        )

)
const filteredProducts = computed(

    () =>

        props.products.filter(

            product =>

                Number(

                    product.warehouse_id

                )

                ===

                Number(

                    form.warehouse_id

                )

        )

)
const addItem = () => {

    if (

        !selectedProduct.value

    ) {

        return

    }

    const product = props.products.find(

        p =>

            p.id === Number(

                selectedProduct.value

            )

            &&

            p.warehouse_id === Number(

                form.warehouse_id

            )

    )

    if (

        !product

    ) {

        alert(

            'Product not found in selected warehouse.'

        )

        return

    }

    const exist = form.items.find(

        item =>

            item.product_id === product.id

    )

    if (

        exist

    ) {

        return

    }

    form.items.push({

        product_id: product.id,

        sku: product.sku,

        product_name: product.name,

        available_qty: Number(product.qty),

        qty: 1,

        unit_cost: Number(product.unit_cost),

        total_cost: Number(product.unit_cost),

        remarks: '',

    })

    selectedProduct.value = null

}

const removeItem = (

    index

) => {

    form.items.splice(

        index,

        1

    )

}

const updateTotal = (

    item

) => {

    item.total_cost =

        Number(

            item.qty

        )

        *

        Number(

            item.unit_cost

        )

}

const hasInvalidQty = () => {

    return form.items.some(

        item =>

            Number(

                item.qty

            )

            >

            Number(

                item.available_qty

            )

    )

}

const submit = () => {

    if (

        form.items.length

        ===

        0

    ) {

        alert(

            'Please add at least one product.'

        )

        return

    }

    if (

        hasInvalidQty()

    ) {

        alert(

            'Issue quantity cannot exceed available stock.'

        )

        return

    }

    form.post(

        route(

            'stock-issues.store'

        )

    )

}

</script>
<template>

<Head

    title="Create Stock Issue"

/>

<AppLayout>

    <template #header>

        <div>

            <h2

                class="
                    text-3xl
                    font-bold
                    text-gray-800
                "

            >

                Create Stock Issue

            </h2>

            <p

                class="
                    mt-1
                    text-sm
                    text-gray-500
                "

            >

                Create a new stock issue transaction.

            </p>

        </div>

    </template>

    <div

        class="
            py-8
        "

    >

        <div

            class="
                mx-auto
                max-w-7xl
                space-y-6
                px-6
            "

        >

            <!-- Summary -->

            <div

                class="
                    grid
                    gap-6
                    md:grid-cols-3
                "

            >

                <div

                    class="
                        rounded-3xl
                        bg-white
                        p-6
                        shadow-sm
                    "

                >

                    <p

                        class="
                            text-sm
                            text-gray-500
                        "

                    >

                        Products

                    </p>

                    <h3

                        class="
                            mt-2
                            text-3xl
                            font-bold
                            text-blue-600
                        "

                    >

                        {{ form.items.length }}

                    </h3>

                </div>

                <div

                    class="
                        rounded-3xl
                        bg-white
                        p-6
                        shadow-sm
                    "

                >

                    <p

                        class="
                            text-sm
                            text-gray-500
                        "

                    >

                        Total Qty

                    </p>

                    <h3

                        class="
                            mt-2
                            text-3xl
                            font-bold
                            text-green-600
                        "

                    >

                        {{ totalQty }}

                    </h3>

                </div>

                <div

                    class="
                        rounded-3xl
                        bg-white
                        p-6
                        shadow-sm
                    "

                >

                    <p

                        class="
                            text-sm
                            text-gray-500
                        "

                    >

                        Total Value

                    </p>

                    <h3

                        class="
                            mt-2
                            text-3xl
                            font-bold
                            text-indigo-600
                        "

                    >

                        Rp {{ totalValue.toLocaleString() }}

                    </h3>

                </div>

            </div>

            <!-- Issue Information -->

            <div

                class="
                    rounded-3xl
                    bg-white
                    p-8
                    shadow-sm
                "

            >

                <h3

                    class="
                        mb-6
                        text-xl
                        font-bold
                    "

                >

                    Issue Information

                </h3>

                <div

                    class="
                        grid
                        gap-6
                        md:grid-cols-2
                        lg:grid-cols-3
                    "

                >

                    <div>

                        <label

                            class="
                                mb-2
                                block
                                text-sm
                                font-medium
                            "

                        >

                            Issue Number

                        </label>

                        <input

                            :value="issueNumber"

                            disabled

                            class="
                                w-full
                                rounded-xl
                                border
                                bg-gray-100
                            "

                        >

                    </div>

                    <div>

                        <label

                            class="
                                mb-2
                                block
                                text-sm
                                font-medium
                            "

                        >

                            Issue Date

                        </label>

                        <FlatPickr

                            v-model="form.issue_date"

                            :config="dateConfig"

                            class="
                                w-full
                                rounded-xl
                                border
                            "

                        />

                    </div>

                    <div>

                        <label

                            class="
                                mb-2
                                block
                                text-sm
                                font-medium
                            "

                        >

                            Warehouse

                        </label>

                        <SearchableSelect

                            v-model="form.warehouse_id"

                            :options="warehouses"

                            placeholder="Select Warehouse"

                        />

                    </div>

                    <div>

                        <label

                            class="
                                mb-2
                                block
                                text-sm
                                font-medium
                            "

                        >

                            Issue Type

                        </label>

                        <SearchableSelect

                            v-model="form.issue_type"

                            :options="issueTypes"

                            placeholder="Select Issue Type"

                        />

                    </div>

                    <div>

                        <label

                            class="
                                mb-2
                                block
                                text-sm
                                font-medium
                            "

                        >

                            Reference Number

                        </label>

                        <input

                            v-model="form.reference_number"

                            class="
                                w-full
                                rounded-xl
                                border
                            "

                        >

                    </div>

                    <div

                        class="
                            md:col-span-2
                            lg:col-span-3
                        "

                    >

                        <label

                            class="
                                mb-2
                                block
                                text-sm
                                font-medium
                            "

                        >

                            Remarks

                        </label>

                        <textarea

                            v-model="form.remarks"

                            rows="3"

                            class="
                                w-full
                                rounded-xl
                                border
                            "

                        />

                    </div>

                </div>

            </div>
                        <!-- Issue Items -->

            <div

                class="
                    rounded-3xl
                    bg-white
                    p-8
                    shadow-sm
                "

            >

                <div

                    class="
                        mb-6
                        flex
                        items-center
                        justify-between
                    "

                >

                    <h3

                        class="
                            text-xl
                            font-bold
                        "

                    >

                        Issue Items

                    </h3>

                </div>

                <div

                    class="
                        mb-6
                        grid
                        gap-4
                        md:grid-cols-2
                    "

                >

                    <SearchableSelect

                        v-model="selectedProduct"

                        :options="filteredProducts"

                        placeholder="Search Product..."

                    />

                    <button

                        @click="addItem"

                        type="button"

                        class="
                            rounded-xl
                            bg-blue-600
                            px-6
                            py-3
                            font-semibold
                            text-white
                            hover:bg-blue-700
                        "

                    >

                        Add Product

                    </button>

                </div>

                <div

                    class="
                        overflow-x-auto
                    "

                >

                    <table

                        class="
                            min-w-full
                            divide-y
                            divide-gray-200
                        "

                    >

                        <thead

                            class="
                                bg-gray-50
                            "

                        >

                            <tr>

                                <th class="px-4 py-3 text-left">

                                    SKU

                                </th>

                                <th class="px-4 py-3 text-left">

                                    Product

                                </th>

                                <th class="px-4 py-3 text-center">

                                    Available

                                </th>

                                <th class="px-4 py-3 text-center">

                                    Qty

                                </th>

                                <th class="px-4 py-3 text-right">

                                    Unit Cost

                                </th>

                                <th class="px-4 py-3 text-right">

                                    Total

                                </th>

                                <th class="px-4 py-3 text-center">

                                    Action

                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            <tr

                                v-for="

                                    (

                                        item,

                                        index

                                    )

                                    in

                                    form.items

                                "

                                :key="

                                    index

                                "

                            >

                                <td

                                    class="
                                        px-4
                                        py-3
                                    "

                                >

                                    {{ item.sku }}

                                </td>

                                <td>

                                    {{ item.product_name }}

                                </td>

                                <td

                                    class="
                                        text-center
                                    "

                                >

                                    {{ item.available_qty }}

                                </td>

                                <td>

                                    <input

                                        v-model="item.qty"

                                        @input="updateTotal(item)"

                                        type="number"

                                        min="1"

                                        class="
                                            w-24
                                            rounded-lg
                                            border
                                        "

                                    >

                                </td>

                                <td

                                    class="
                                        text-right
                                    "

                                >

                                    {{ Number(item.unit_cost).toLocaleString() }}

                                </td>

                                <td

                                    class="
                                        text-right
                                    "

                                >

                                    {{ Number(item.total_cost).toLocaleString() }}

                                </td>

                                <td

                                    class="
                                        text-center
                                    "

                                >

                                    <button

                                        @click="removeItem(index)"

                                        type="button"

                                        class="
                                            rounded-lg
                                            bg-red-600
                                            px-3
                                            py-2
                                            text-white
                                            hover:bg-red-700
                                        "

                                    >

                                        Delete

                                    </button>

                                </td>

                            </tr>

                            <tr

                                v-if="

                                    form.items.length

                                    ===

                                    0

                                "

                            >

                                <td

                                    colspan="7"

                                    class="
                                        py-10
                                        text-center
                                        text-gray-500
                                    "

                                >

                                    No products selected.

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>
                        <!-- Footer -->

            <div

                class="
                    rounded-3xl
                    bg-white
                    p-8
                    shadow-sm
                "

            >

                <div

                    class="
                        flex
                        flex-col
                        gap-6
                        lg:flex-row
                        lg:items-center
                        lg:justify-between
                    "

                >

                    <div>

                        <h3

                            class="
                                text-lg
                                font-bold
                                text-gray-800
                            "

                        >

                            Transaction Summary

                        </h3>

                        <div

                            class="
                                mt-4
                                space-y-2
                                text-sm
                            "

                        >

                            <div

                                class="
                                    flex
                                    justify-between
                                    gap-12
                                "

                            >

                                <span>

                                    Total Products

                                </span>

                                <strong>

                                    {{ form.items.length }}

                                </strong>

                            </div>

                            <div

                                class="
                                    flex
                                    justify-between
                                    gap-12
                                "

                            >

                                <span>

                                    Total Qty

                                </span>

                                <strong>

                                    {{ totalQty }}

                                </strong>

                            </div>

                            <div

                                class="
                                    flex
                                    justify-between
                                    gap-12
                                "

                            >

                                <span>

                                    Total Value

                                </span>

                                <strong>

                                    Rp {{ totalValue.toLocaleString() }}

                                </strong>

                            </div>

                        </div>

                    </div>

                    <div

                        class="
                            flex
                            gap-3
                        "

                    >

                        <button

                            type="button"

                            @click="$inertia.visit(route('stock-issues.index'))"

                            class="
                                rounded-xl
                                border
                                border-gray-300
                                px-6
                                py-3
                                font-medium
                                text-gray-700
                                hover:bg-gray-100
                            "

                        >

                            Cancel

                        </button>

                        <button

                            type="button"

                            @click="submit"

                            :disabled="form.processing"

                            class="
                                rounded-xl
                                bg-blue-600
                                px-8
                                py-3
                                font-semibold
                                text-white
                                transition
                                hover:bg-blue-700
                                disabled:opacity-50
                            "

                        >

                            {{ form.processing ? 'Saving...' : 'Save Stock Issue' }}

                        </button>

                    </div>

                </div>

            </div>

        </div>

    </div>

</AppLayout>

</template>
            <!----- -->
     

    