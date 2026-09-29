<script setup>
import { computed, ref, watch } from 'vue'

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },

    customers: {
        type: Array,
        default: () => [],
    },
})

const emit = defineEmits([
    'close',
    'select',
    'clear',
])

const search = ref('')

const filteredCustomers = computed(() => {
    const keyword = search.value
        .trim()
        .toLowerCase()

    if (!keyword) {
        return props.customers
    }

    return props.customers.filter((customer) => {
      const values = [
        customer.name,
        customer.customer_code,
        customer.phone,
        customer.address,
        customer.city,
    ]
        return values.some((value) =>
            String(value ?? '')
                .toLowerCase()
                .includes(keyword)
        )
    })
})

const selectCustomer = (customer) => {
    emit('select', customer)
}

const clearCustomer = () => {
    emit('clear')
}

const close = () => {
    emit('close')
}

watch(
    () => props.show,
    (value) => {
        if (value) {
            search.value = ''
        }
    }
)
</script>

<template>
    <Teleport to="body">

        <div
            v-if="show"
            class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 p-4"
            @click.self="close"
        >

            <div
                class="flex w-full max-w-lg flex-col overflow-hidden rounded-2xl bg-white shadow-2xl"
            >

                <!-- HEADER -->
                <div
                    class="flex items-center justify-between border-b border-slate-100 px-5 py-4"
                >

                    <div>
                        <h2
                            class="text-base font-semibold text-slate-900"
                        >
                            Select Customer
                        </h2>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            Choose customer for this transaction.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700"
                        @click="close"
                    >
                        ×
                    </button>

                </div>


                <!-- SEARCH -->
                <div class="border-b border-slate-100 p-4">

                    <div class="relative">

                        <svg
                            class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"
                            />
                        </svg>

                        <input
                            v-model="search"
                            type="text"
                            autocomplete="off"
                            placeholder="Search customer..."
                            class="h-10 w-full rounded-lg border border-slate-200 bg-slate-50 pl-9 pr-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100"
                        />

                    </div>

                </div>


                <!-- CUSTOMER LIST -->
                <div
                    class="max-h-[55vh] overflow-y-auto p-4"
                >

                    <!-- WALK-IN -->
                    <button
                        type="button"
                        class="mb-2 flex w-full items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3 text-left transition hover:border-blue-300 hover:bg-blue-50"
                        @click="clearCustomer"
                    >

                        <div>

                            <div
                                class="text-sm font-semibold text-slate-900"
                            >
                                Walk-in Customer
                            </div>

                            <div
                                class="mt-0.5 text-xs text-slate-500"
                            >
                                No customer selected
                            </div>

                        </div>

                        <span
                            class="text-xs font-semibold text-slate-400"
                        >
                            Default
                        </span>

                    </button>


                    <!-- CUSTOMERS -->
                    <div class="space-y-2">

                        <button
                            v-for="customer in filteredCustomers"
                            :key="customer.id"
                            type="button"
                            class="flex w-full items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3 text-left transition hover:border-blue-300 hover:bg-blue-50"
                            @click="selectCustomer(customer)"
                        >

<div class="min-w-0">

    <!-- NAME -->
    <div
        class="truncate text-sm font-semibold text-slate-900"
    >
        {{ customer.name }}
    </div>

    <!-- ADDRESS -->
    <div
        v-if="customer.address || customer.city"
        class="mt-1 line-clamp-2 text-xs text-slate-500"
    >
        {{ customer.address ?? '' }}

        <span
            v-if="
                customer.address &&
                customer.city
            "
        >
            , 
        </span>

        {{ customer.city ?? '' }}
    </div>

    <!-- PHONE -->
    <div
        v-if="customer.phone"
        class="mt-1 text-xs text-slate-500"
    >
        {{ customer.phone }}
    </div>

</div>

                        </button>

                    </div>


                    <!-- EMPTY -->
                    <div
                        v-if="!filteredCustomers.length"
                        class="py-10 text-center"
                    >

                        <div
                            class="text-sm font-semibold text-slate-700"
                        >
                            No customers found
                        </div>

                        <div
                            class="mt-1 text-xs text-slate-400"
                        >
                            Try another customer name, code, or phone.
                        </div>

                    </div>

                </div>


                <!-- FOOTER -->
                <div
                    class="flex justify-end border-t border-slate-100 px-5 py-3"
                >

                    <button
                        type="button"
                        class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100"
                        @click="close"
                    >
                        Close
                    </button>

                </div>

            </div>

        </div>

    </Teleport>
</template>