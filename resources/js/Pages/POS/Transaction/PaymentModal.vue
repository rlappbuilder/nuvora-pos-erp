<script setup>
import { computed, ref, watch } from 'vue'
import { onBeforeUnmount, onMounted } from 'vue'

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },

    total: {
        type: Number,
        default: 0,
    },

    processing: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits([
    'close',
    'submit',
])

/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const payments = ref([])

const showAddPayment = ref(false)

/*
|--------------------------------------------------------------------------
| Currency
|--------------------------------------------------------------------------
*/

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(Number(value ?? 0))
}

/*
|--------------------------------------------------------------------------
| Payment Methods
|--------------------------------------------------------------------------
*/

const paymentMethods = [
    {
        value: 'cash',
        label: 'Cash',
    },
    {
        value: 'qris',
        label: 'QRIS',
    },
    {
        value: 'debit_card',
        label: 'Debit Card',
    },
    {
        value: 'transfer',
        label: 'Transfer',
    },
    {
        value: 'e_wallet',
        label: 'E-Wallet',
    },
]

/*
|--------------------------------------------------------------------------
| Calculations
|--------------------------------------------------------------------------
*/

const paidAmount = computed(() => {
    return payments.value.reduce(
        (total, payment) =>
            total + Number(payment.amount || 0),
        0
    )
})

const remainingAmount = computed(() => {
    return Math.max(
        0,
        Number(props.total) - paidAmount.value
    )
})

const changeAmount = computed(() => {
    if (payments.value.length !== 1) {
        return 0
    }

    const payment = payments.value[0]

    if (payment.payment_method !== 'cash') {
        return 0
    }

    return Math.max(
        0,
        Number(payment.amount || 0) -
            Number(props.total)
    )
})

const isSingleCashPayment = computed(() => {
    return (
        payments.value.length === 1 &&
        payments.value[0]?.payment_method === 'cash'
    )
})

const isPaidEnough = computed(() => {
    return (
        Number(props.total) > 0 &&
        paidAmount.value >= Number(props.total)
    )
})

/*
|--------------------------------------------------------------------------
| Available Payment Methods
|--------------------------------------------------------------------------
*/

const usedPaymentMethods = computed(() => {
    return payments.value.map(
        payment => payment.payment_method
    )
})

const availablePaymentMethods = computed(() => {
    return paymentMethods.filter(
        method =>
            !usedPaymentMethods.value.includes(
                method.value
            )
    )
})

/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

const isValid = computed(() => {
    if (Number(props.total) <= 0) {
        return false
    }

    if (!payments.value.length) {
        return false
    }

    if (remainingAmount.value > 0) {
        return false
    }

    /*
    |--------------------------------------------------------------------------
    | Validate every payment
    |--------------------------------------------------------------------------
    */

    for (const payment of payments.value) {
        const amount = Number(payment.amount || 0)

        if (amount <= 0) {
            return false
        }

        if (
            payment.payment_method !== 'cash' &&
            !String(
                payment.reference_no ?? ''
            ).trim()
        ) {
            return false
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Split payment cannot create change
    |--------------------------------------------------------------------------
    */

    if (
        payments.value.length > 1 &&
        paidAmount.value > Number(props.total)
    ) {
        return false
    }

    return true
})

/*
|--------------------------------------------------------------------------
| Payment Helpers
|--------------------------------------------------------------------------
*/

const getPaymentMethodLabel = (method) => {
    return (
        paymentMethods.find(
            item => item.value === method
        )?.label ?? method
    )
}

const getPayment = (index) => {
    return payments.value[index]
}

/*
|--------------------------------------------------------------------------
| Reset / Initialize
|--------------------------------------------------------------------------
*/

const initialize = () => {
    payments.value = [
        {
            payment_method: 'cash',
            amount: props.total
                ? String(props.total)
                : '',
            reference_no: '',
            note: null,
        },
    ]

    showAddPayment.value = false
}

watch(
    () => props.show,
    (value) => {
        if (value) {
            initialize()
        }
    }
)

/*
|--------------------------------------------------------------------------
| Payment Method
|--------------------------------------------------------------------------
*/

const addPayment = (method) => {
    if (props.processing) {
        return
    }

    if (
        usedPaymentMethods.value.includes(
            method
        )
    ) {
        return
    }

    const amount = remainingAmount.value

    payments.value.push({
        payment_method: method,
        amount: amount > 0
            ? String(amount)
            : '',
        reference_no: '',
        note: null,
    })

    showAddPayment.value = false
}

/*
|--------------------------------------------------------------------------
| Remove Payment
|--------------------------------------------------------------------------
*/

const removePayment = (index) => {
    if (props.processing) {
        return
    }

    if (payments.value.length <= 1) {
        return
    }

    payments.value.splice(index, 1)
}

/*
|--------------------------------------------------------------------------
| Cash Shortcut
|--------------------------------------------------------------------------
*/

const setCashAmount = (
    index,
    amount
) => {
    if (props.processing) {
        return
    }

    const payment = getPayment(index)

    if (!payment) {
        return
    }

    if (
        payment.payment_method !== 'cash'
    ) {
        return
    }

    payment.amount = String(amount)
}

/*
|--------------------------------------------------------------------------
| Uang Pas
|--------------------------------------------------------------------------
*/

const setExactAmount = (index) => {
    setCashAmount(
        index,
        Number(props.total)
    )
}

/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/

const submit = () => {
    if (
        !isValid.value ||
        props.processing
    ) {
        return
    }

    emit('submit', {
        payments: payments.value.map(
            payment => ({
                payment_method:
                    payment.payment_method,

                amount:
                    Number(payment.amount || 0),

                reference_no:
                    String(
                        payment.reference_no ?? ''
                    ).trim() || null,

                note:
                    payment.note ?? null,
            })
        ),
    })
}

/*
|--------------------------------------------------------------------------
| Close
|--------------------------------------------------------------------------
*/

const close = () => {
    if (props.processing) {
        return
    }

    emit('close')
}

/*
|--------------------------------------------------------------------------
| Keyboard
|--------------------------------------------------------------------------
*/

const handleKeyboard = (event) => {
    if (!props.show) {
        return
    }

    if (event.key === 'Escape') {
        event.preventDefault()

        close()

        return
    }

    if (event.key === 'Enter') {
        /*
        |--------------------------------------------------------------------------
        | Don't submit while typing a reference number
        |--------------------------------------------------------------------------
        */

        if (
            event.target instanceof HTMLInputElement &&
            event.target.type === 'text'
        ) {
            return
        }

        event.preventDefault()

        submit()
    }
}

onMounted(() => {
    window.addEventListener(
        'keydown',
        handleKeyboard
    )
})

onBeforeUnmount(() => {
    window.removeEventListener(
        'keydown',
        handleKeyboard
    )
})
</script><template>
    <Teleport to="body">    <div
        v-if="show"
        class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 p-4"
        @click.self="close"
    >

        <div
            class="flex max-h-[90vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl"
        >

            <!-- HEADER -->
            <div
                class="flex items-center justify-between border-b border-slate-100 px-5 py-4"
            >

                <div>
                    <h2
                        class="text-base font-semibold text-slate-900"
                    >
                        Payment
                    </h2>

                    <p
                        class="mt-1 text-sm text-slate-500"
                    >
                        Complete payment for this transaction.
                    </p>
                </div>

                <button
                    type="button"
                    class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700"
                    :disabled="processing"
                    @click="close"
                >
                    ×
                </button>

            </div>


            <!-- BODY -->
            <div
                class="overflow-y-auto p-5"
            >

                <!-- TOTAL -->
                <div
                    class="rounded-xl bg-blue-50 px-5 py-4"
                >

                    <div
                        class="text-xs font-medium uppercase tracking-wide text-blue-600"
                    >
                        Total Payment
                    </div>

                    <div
                        class="mt-1 text-3xl font-bold tracking-tight text-blue-700"
                    >
                        {{ formatCurrency(total) }}
                    </div>

                </div>


                <!-- PAYMENTS -->
                <div class="mt-5">

                    <div
                        class="mb-3 flex items-center justify-between"
                    >

                        <div>
                            <div
                                class="text-sm font-semibold text-slate-900"
                            >
                                Payments
                            </div>

                            <div
                                class="mt-1 text-xs text-slate-500"
                            >
                                Add one or more payment methods.
                            </div>
                        </div>

                        <button
                            v-if="
                                availablePaymentMethods.length &&
                                !showAddPayment
                            "
                            type="button"
                            :disabled="processing"
                            class="rounded-lg border border-blue-200 px-3 py-2 text-xs font-semibold text-blue-600 hover:border-blue-400 hover:bg-blue-50 disabled:cursor-not-allowed disabled:opacity-50"
                            @click="
                                showAddPayment = true
                            "
                        >
                            + Add Payment
                        </button>

                    </div>


                    <!-- ADD PAYMENT METHOD -->
                    <div
                        v-if="showAddPayment"
                        class="mb-4 rounded-xl border border-blue-100 bg-blue-50 p-3"
                    >

                        <div
                            class="mb-2 text-xs font-semibold uppercase tracking-wide text-blue-600"
                        >
                            Select Payment Method
                        </div>

                        <div
                            class="grid grid-cols-2 gap-2 sm:grid-cols-4"
                        >

                            <button
                                v-for="
                                    method in availablePaymentMethods
                                "
                                :key="method.value"
                                type="button"
                                :disabled="processing"
                                class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:border-blue-400 hover:text-blue-600"
                                @click="
                                    addPayment(
                                        method.value
                                    )
                                "
                            >
                                {{ method.label }}
                            </button>

                        </div>

                        <button
                            type="button"
                            class="mt-2 text-xs font-medium text-slate-500 hover:text-slate-700"
                            @click="
                                showAddPayment = false
                            "
                        >
                            Cancel
                        </button>

                    </div>


                    <!-- PAYMENT ROWS -->
                    <div class="space-y-3">

                        <div
                            v-for="(
                                payment,
                                index
                            ) in payments"
                            :key="payment.payment_method"
                            class="rounded-xl border border-slate-200 bg-white p-4"
                        >

                            <!-- PAYMENT HEADER -->
                            <div
                                class="mb-3 flex items-center justify-between"
                            >

                                <div
                                    class="text-sm font-semibold text-slate-900"
                                >
                                    Payment {{ index + 1 }}
                                </div>

                                <button
                                    v-if="
                                        payments.length > 1
                                    "
                                    type="button"
                                    :disabled="processing"
                                    class="text-xs font-semibold text-red-500 hover:text-red-600"
                                    @click="
                                        removePayment(
                                            index
                                        )
                                    "
                                >
                                    Remove
                                </button>

                            </div>


                            <!-- METHOD -->
                            <div class="grid gap-4 md:grid-cols-2">

                                <div>

                                    <div
                                        class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500"
                                    >
                                        Payment Method
                                    </div>

                                    <div
                                        class="flex h-11 items-center rounded-lg border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-800"
                                    >
                                        {{
                                            getPaymentMethodLabel(
                                                payment.payment_method
                                            )
                                        }}
                                    </div>

                                </div>


                                <!-- AMOUNT -->
                                <div>

                                    <div
                                        class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500"
                                    >
                                        Amount
                                    </div>

                                    <input
                                        v-model="
                                            payment.amount
                                        "
                                        type="number"
                                        min="0"
                                        step="any"
                                        :disabled="processing"
                                        class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                    />

                                </div>

                            </div>


                            <!-- CASH SHORTCUTS -->
                            <div
                                v-if="
                                    payment.payment_method ===
                                    'cash'
                                "
                                class="mt-3"
                            >

                                <div
                                    class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500"
                                >
                                    Cash Shortcut
                                </div>

                                <div
                                    class="flex flex-wrap gap-2"
                                >

                                    <button
                                        type="button"
                                        :disabled="processing"
                                        class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:border-blue-300 hover:text-blue-600"
                                        @click="
                                            setExactAmount(
                                                index
                                            )
                                        "
                                    >
                                        Uang Pas
                                    </button>

                                    <button
                                        v-for="
                                            cashAmount in [
                                                50000,
                                                100000,
                                                200000,
                                                500000,
                                            ]
                                        "
                                        :key="cashAmount"
                                        type="button"
                                        :disabled="processing"
                                        class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:border-blue-300 hover:text-blue-600"
                                        @click="
                                            setCashAmount(
                                                index,
                                                cashAmount
                                            )
                                        "
                                    >
                                        {{
                                            formatCurrency(
                                                cashAmount
                                            )
                                        }}
                                    </button>

                                </div>

                            </div>


                            <!-- REFERENCE -->
                            <div
                                v-if="
                                    payment.payment_method !==
                                    'cash'
                                "
                                class="mt-3"
                            >

                                <div
                                    class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500"
                                >
                                    Reference Number
                                </div>

                                <input
                                    v-model="
                                        payment.reference_no
                                    "
                                    type="text"
                                    autocomplete="off"
                                    :disabled="processing"
                                    placeholder="Enter reference number..."
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                />

                            </div>

                        </div>

                    </div>

                </div>


                <!-- RESULT -->
                <div
                    class="mt-5 rounded-xl border border-slate-200 bg-slate-50 px-4 py-4"
                >

                    <div
                        class="flex justify-between text-sm"
                    >
                        <span class="text-slate-500">
                            Total
                        </span>

                        <span
                            class="font-semibold text-slate-800"
                        >
                            {{ formatCurrency(total) }}
                        </span>
                    </div>


                    <div
                        class="mt-2 flex justify-between text-sm"
                    >
                        <span class="text-slate-500">
                            Paid
                        </span>

                        <span
                            class="font-semibold text-slate-800"
                        >
                            {{ formatCurrency(paidAmount) }}
                        </span>
                    </div>


                    <div
                        class="my-3 border-t border-slate-200"
                    />


                    <!-- CHANGE -->
                    <div
                        v-if="isSingleCashPayment"
                        class="flex justify-between"
                    >

                        <span
                            class="text-sm font-semibold text-slate-800"
                        >
                            Change
                        </span>

                        <span
                            class="text-xl font-bold text-emerald-600"
                        >
                            {{ formatCurrency(changeAmount) }}
                        </span>

                    </div>


                    <!-- REMAINING -->
                    <div
                        v-else
                        class="flex justify-between"
                    >

                        <span
                            class="text-sm font-semibold text-slate-800"
                        >
                            Remaining
                        </span>

                        <span
                            class="text-xl font-bold"
                            :class="
                                remainingAmount > 0
                                    ? 'text-red-500'
                                    : 'text-emerald-600'
                            "
                        >
                            {{ formatCurrency(remainingAmount) }}
                        </span>

                    </div>

                </div>


                <!-- VALIDATION -->
                <div
                    v-if="
                        payments.length &&
                        !isValid
                    "
                    class="mt-3 text-xs font-medium text-red-500"
                >

                    <span
                        v-if="
                            remainingAmount > 0
                        "
                    >
                        Payment amount is insufficient.
                    </span>

                    <span
                        v-else-if="
                            payments.some(
                                payment =>
                                    payment.payment_method !==
                                        'cash' &&
                                    !String(
                                        payment.reference_no ??
                                            ''
                                    ).trim()
                            )
                        "
                    >
                        Reference number is required for non-cash payment.
                    </span>

                    <span
                        v-else-if="
                            payments.length > 1 &&
                            paidAmount > total
                        "
                    >
                        Split payment cannot exceed the total.
                    </span>

                </div>

            </div>
            <!-- END BODY -->


            <!-- FOOTER -->
            <div
                class="flex items-center justify-between border-t border-slate-100 px-5 py-3"
            >

                <div />

                <div class="flex gap-2">

                    <button
                        type="button"
                        :disabled="processing"
                        class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
                        @click="close"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        :disabled="
                            !isValid ||
                            processing
                        "
                        class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-slate-300"
                        @click="submit"
                    >
                        {{
                            processing
                                ? 'Processing...'
                                : 'Pay'
                        }}
                    </button>

                </div>

            </div>
            <!-- END FOOTER -->

        </div>

    </div>

</Teleport>

</template>