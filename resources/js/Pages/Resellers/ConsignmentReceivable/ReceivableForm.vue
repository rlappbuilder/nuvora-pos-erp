<script setup>

import FormField from '@/Components/Form/FormField.vue'
import FormInput from '@/Components/Form/FormInput.vue'
import FormTextarea from '@/Components/Form/FormTextarea.vue'
import BaseButton from '@/Components/Button/BaseButton.vue'
import SearchableSelect from '@/Components/Form/SearchableSelect.vue'

import {
    success,
    error,
    formatDate,
} from '@/Utils'

import {
    PlusIcon,
    TrashIcon,
} from '@heroicons/vue/24/outline'

import FlatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'

import Swal from 'sweetalert2'

import {
    computed,
    onMounted,
    watch,
} from 'vue'


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    form: {
        type: Object,
        required: true,
    },

    branches: {
        type: Array,
        default: () => [],
    },

    resellers: {
        type: Array,
        default: () => [],
    },

    paymentAccounts: {
        type: Array,
        default: () => [],
    },

    settlements: {
        type: Array,
        default: () => [],
    },

})


const form = props.form


/*
|--------------------------------------------------------------------------
| Selected Reseller
|--------------------------------------------------------------------------
*/

const selectedReseller = computed(() => {

    return props.resellers.find(

        reseller =>

            Number(
                reseller.id
            ) ===
            Number(
                form.reseller_id
            )

    ) ?? null

})


/*
|--------------------------------------------------------------------------
| Emits
|--------------------------------------------------------------------------
*/

const emit = defineEmits([
    'submit',
    'cancel',
])


/*
|--------------------------------------------------------------------------
| Date Configuration
|--------------------------------------------------------------------------
*/

const dateConfig = {

    dateFormat:
        'Y-m-d',

    allowInput:
        true,

}


/*
|--------------------------------------------------------------------------
| Default Date
|--------------------------------------------------------------------------
*/

const getToday = () => {

    const date =
        new Date()


    const year =
        date.getFullYear()


    const month =
        String(
            date.getMonth() + 1
        ).padStart(
            2,
            '0'
        )


    const day =
        String(
            date.getDate()
        ).padStart(
            2,
            '0'
        )


    return `${year}-${month}-${day}`

}


/*
|--------------------------------------------------------------------------
| Initialize Date
|--------------------------------------------------------------------------
*/

onMounted(() => {

    if (
        !form.payment_date
    ) {

        form.payment_date =
            getToday()

    }

})


/*
|--------------------------------------------------------------------------
| Empty Detail
|--------------------------------------------------------------------------
*/

const createEmptyDetail = () => ({

    settlement_header_id:
        null,

    settlement_amount:
        0,

    previous_paid_amount:
        0,

    previous_outstanding_amount:
        0,

    payment_amount:
        0,

    remarks:
        '',

})


/*
|--------------------------------------------------------------------------
| Reset Details
|--------------------------------------------------------------------------
*/

const resetDetails = () => {

    form.details = [
        createEmptyDetail()
    ]

}


/*
|--------------------------------------------------------------------------
| Add Detail
|--------------------------------------------------------------------------
*/

const addDetail = () => {

    form.details.push(
        createEmptyDetail()
    )

}


/*
|--------------------------------------------------------------------------
| Remove Detail
|--------------------------------------------------------------------------
*/

const removeDetail = (
    index
) => {

    if (
        form.details.length <= 1
    ) {

        return

    }


    form.details.splice(
        index,
        1
    )

}


/*
|--------------------------------------------------------------------------
| Reseller Changed
|--------------------------------------------------------------------------
*/

watch(

    () => form.reseller_id,

    (
        newReseller,
        oldReseller
    ) => {

        if (
            newReseller === oldReseller
        ) {

            return

        }


        /*
        |--------------------------------------------------------------------------
        | Clear Existing Settlement Rows
        |--------------------------------------------------------------------------
        */

        resetDetails()


        if (
            !newReseller
        ) {

            return

        }


        /*
        |--------------------------------------------------------------------------
        | Check Outstanding Receivable
        |--------------------------------------------------------------------------
        */

        const hasReceivable =
            props.settlements.some(

                settlement =>

                    Number(
                        settlement.reseller_id
                    ) ===
                    Number(
                        newReseller
                    )

                    &&

                    Number(
                        settlement.outstanding_amount || 0
                    ) > 0

            )


        if (
            hasReceivable
        ) {

            return

        }


        /*
        |--------------------------------------------------------------------------
        | No Receivable
        |--------------------------------------------------------------------------
        */

        Swal.fire({

            icon:
                'info',

            title:
                'Reseller Has No Receivable',

            text:
                'This reseller currently has no outstanding receivable.',

            confirmButtonText:
                'OK',

            buttonsStyling:
                false,

            customClass: {

                popup:
                    'rounded-2xl',

                confirmButton:
                    'rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-medium text-white',

            },

        })

    }

)


/*
|--------------------------------------------------------------------------
| Available Settlements
|--------------------------------------------------------------------------
*/

const availableSettlements = (
    currentDetail
) => {

    if (
        !form.reseller_id
    ) {

        return []

    }


    const currentSettlementId =
        Number(
            currentDetail.settlement_header_id
        )


    const selectedIds =
        form.details
            .map(
                detail =>
                    Number(
                        detail.settlement_header_id
                    )
            )
            .filter(
                id =>
                    id > 0
                    &&
                    id !== currentSettlementId
            )


    return props.settlements.filter(

        settlement => {

            /*
            |--------------------------------------------------------------------------
            | Reseller
            |--------------------------------------------------------------------------
            */

            if (
                Number(
                    settlement.reseller_id
                ) !==
                Number(
                    form.reseller_id
                )
            ) {

                return false

            }


            /*
            |--------------------------------------------------------------------------
            | Outstanding
            |--------------------------------------------------------------------------
            */

            if (
                Number(
                    settlement.outstanding_amount || 0
                ) <= 0
            ) {

                return false

            }


            /*
            |--------------------------------------------------------------------------
            | Current Selected Settlement
            |--------------------------------------------------------------------------
            */

            if (
                Number(
                    settlement.id
                ) ===
                currentSettlementId
            ) {

                return true

            }


            /*
            |--------------------------------------------------------------------------
            | Prevent Duplicate
            |--------------------------------------------------------------------------
            */

            return !selectedIds.includes(
                Number(
                    settlement.id
                )
            )

        }

    )

}


/*
|--------------------------------------------------------------------------
| Get Settlement
|--------------------------------------------------------------------------
*/

const getSettlement = (
    settlementId
) => {

    return props.settlements.find(

        settlement =>

            Number(
                settlement.id
            ) ===
            Number(
                settlementId
            )

    )

}


/*
|--------------------------------------------------------------------------
| Settlement Changed
|--------------------------------------------------------------------------
*/

const changeSettlement = (
    detail
) => {

    if (
        !detail.settlement_header_id
    ) {

        detail.settlement_amount =
            0

        detail.previous_paid_amount =
            0

        detail.previous_outstanding_amount =
            0

        detail.payment_amount =
            0

        return

    }


    const settlement =
        getSettlement(
            detail.settlement_header_id
        )


    if (
        !settlement
    ) {

        detail.settlement_header_id =
            null

        detail.settlement_amount =
            0

        detail.previous_paid_amount =
            0

        detail.previous_outstanding_amount =
            0

        detail.payment_amount =
            0

        return

    }


    /*
    |--------------------------------------------------------------------------
    | Prevent Duplicate Settlement
    |--------------------------------------------------------------------------
    */

    const duplicate =
        form.details.some(

            item =>

                item !== detail

                &&

                Number(
                    item.settlement_header_id
                ) ===
                Number(
                    detail.settlement_header_id
                )

        )


    if (
        duplicate
    ) {

        Swal.fire({

            icon:
                'warning',

            title:
                'Settlement Already Exist',

            text:
                'This settlement has already been added to the payment.',

            confirmButtonText:
                'OK',

            buttonsStyling:
                false,

            customClass: {

                popup:
                    'rounded-2xl',

                confirmButton:
                    'rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-medium text-white',

            },

        }).then(() => {

            const index =
                form.details.indexOf(
                    detail
                )


            if (
                index !== -1
            ) {

                form.details.splice(
                    index,
                    1
                )

            }

        })


        return

    }


    /*
    |--------------------------------------------------------------------------
    | Snapshot Settlement
    |--------------------------------------------------------------------------
    */

    detail.settlement_amount =
        Number(
            settlement.receivable_amount || 0
        )


    detail.previous_paid_amount =
        Number(
            settlement.previous_paid_amount || 0
        )


    detail.previous_outstanding_amount =
        Number(
            settlement.outstanding_amount
            ??
            (
                detail.settlement_amount -
                detail.previous_paid_amount
            )
        )


    /*
    |--------------------------------------------------------------------------
    | Default Payment
    |--------------------------------------------------------------------------
    */

    detail.payment_amount =
        detail.previous_outstanding_amount


    calculateDetail(
        detail
    )

}


/*
|--------------------------------------------------------------------------
| Calculate Detail
|--------------------------------------------------------------------------
*/

const calculateDetail = (
    detail
) => {

    const outstanding =
        Number(
            detail.previous_outstanding_amount || 0
        )


    let payment =
        Number(
            detail.payment_amount || 0
        )


    if (
        payment < 0
    ) {

        payment = 0

    }


    if (
        payment > outstanding
    ) {

        payment =
            outstanding

    }


    detail.payment_amount =
        payment

}


/*
|--------------------------------------------------------------------------
| Settlement Number
|--------------------------------------------------------------------------
*/

const getSettlementNumber = (
    detail
) => {

    const settlement =
        getSettlement(
            detail.settlement_header_id
        )


    return (
        settlement?.settlement_number
        ??
        '-'
    )

}


/*
|--------------------------------------------------------------------------
| Settlement Date
|--------------------------------------------------------------------------
*/

const getSettlementDate = (
    detail
) => {

    const settlement =
        getSettlement(
            detail.settlement_header_id
        )


    return settlement?.settlement_date
        ? formatDate(
            settlement.settlement_date
        )
        : '-'

}


/*
|--------------------------------------------------------------------------
| Settlement Period
|--------------------------------------------------------------------------
*/

const getSettlementPeriod = (
    detail
) => {

    const settlement =
        getSettlement(
            detail.settlement_header_id
        )


    if (
        !settlement?.period_from ||
        !settlement?.period_to
    ) {

        return '-'

    }


    return (
        formatDate(
            settlement.period_from
        )
        +
        ' - '
        +
        formatDate(
            settlement.period_to
        )
    )

}


/*
|--------------------------------------------------------------------------
| Can Add Settlement
|--------------------------------------------------------------------------
*/

const canAddSettlement = computed(() => {

    if (
        !form.reseller_id
    ) {

        return false

    }


    const selectedIds =
        form.details
            .map(
                detail =>
                    Number(
                        detail.settlement_header_id
                    )
            )
            .filter(
                id =>
                    id > 0
            )


    return props.settlements.some(

        settlement =>

            Number(
                settlement.reseller_id
            ) ===
            Number(
                form.reseller_id
            )

            &&

            Number(
                settlement.outstanding_amount || 0
            ) > 0

            &&

            !selectedIds.includes(
                Number(
                    settlement.id
                )
            )

    )

})


/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

const totalItems =
    computed(() => {

        return form.details.length

    })


const totalPayment =
    computed(() => {

        return form.details.reduce(

            (
                total,
                detail
            ) =>

                total +
                Number(
                    detail.payment_amount || 0
                ),

            0

        )

    })


const paymentExceedsOutstanding =
    computed(() => {

        return form.details.some(

            detail =>

                Number(
                    detail.payment_amount || 0
                ) >
                Number(
                    detail.previous_outstanding_amount || 0
                )

        )

    })


/*
|--------------------------------------------------------------------------
| Currency
|--------------------------------------------------------------------------
*/

const formatCurrency = (
    value
) => {

    return new Intl.NumberFormat(
        'id-ID',
        {
            style:
                'currency',

            currency:
                'IDR',

            minimumFractionDigits:
                0,

            maximumFractionDigits:
                0,
        }
    ).format(
        Number(
            value || 0
        )
    )

}


/*
|--------------------------------------------------------------------------
| Number
|--------------------------------------------------------------------------
*/

const formatNumber = (
    value
) => {

    return new Intl.NumberFormat(
        'id-ID',
        {
            minimumFractionDigits:
                0,

            maximumFractionDigits:
                2,
        }
    ).format(
        Number(
            value || 0
        )
    )

}


/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/

const handleSubmit = () => {

    if (
        paymentExceedsOutstanding.value
    ) {

        return

    }


    emit('submit')

}

</script>
<template>

    <form
        @submit.prevent="handleSubmit"
        class="space-y-6"
    >

        <!-- ========================================================= -->
        <!-- Header -->
        <!-- ========================================================= -->

        <div
            class="
                rounded-xl
                border
                border-gray-200
                bg-white
                px-5
                py-4
            "
        >

            <div
                class="
                    flex
                    flex-col
                    gap-1
                    sm:flex-row
                    sm:items-center
                    sm:justify-between
                "
            >

                <div>

                    <h1
                        class="
                            text-lg
                            font-semibold
                            tracking-tight
                            text-gray-900
                        "
                    >
                        Consignment Receivable
                    </h1>

                    <p
                        class="
                            text-sm
                            text-gray-500
                        "
                    >
                        Record payment for reseller receivables.
                    </p>

                </div>


                <div
                    class="
                        text-left
                        sm:text-right
                    "
                >

                    <div
                        class="
                            text-[11px]
                            font-medium
                            uppercase
                            tracking-wider
                            text-gray-400
                        "
                    >
                        Receivable No.
                    </div>

                    <div
                        class="
                            mt-0.5
                            text-base
                            font-semibold
                            text-gray-800
                        "
                    >
                        {{ form.number || 'Auto Generated' }}
                    </div>

                </div>

            </div>

        </div>


    <!-- ========================================================= -->
<!-- Transaction Information -->
<!-- ========================================================= -->

<div
    class="
        rounded-xl
        border
        border-gray-200
        bg-white
        p-5
    "
>

    <div
        class="
            grid
            grid-cols-1
            gap-x-8
            gap-y-5
            lg:grid-cols-3
        "
    >

        <!-- ===================================================== -->
        <!-- Reseller -->
        <!-- ===================================================== -->

        <FormField
            label="Reseller"
            required
            :error="
                form.errors.reseller_id
            "
        >

            <SearchableSelect
                v-model="
                    form.reseller_id
                "
                :options="
                    resellers
                "
                label="label"
                value-key="id"
                placeholder="Select reseller"
            />

        </FormField>


        <!-- ===================================================== -->
        <!-- Contact Person -->
        <!-- ===================================================== -->

        <FormField
            label="Contact Person"
        >

            <div
                class="
                    flex
                    min-h-[38px]
                    items-center
                    rounded-lg
                    border
                    border-gray-200
                    bg-gray-50
                    px-3
                    py-2
                    text-sm
                    text-gray-700
                "
            >

                {{
                    selectedReseller?.contact_person
                    || '-'
                }}

            </div>

        </FormField>


        <!-- ===================================================== -->
        <!-- Branch -->
        <!-- ===================================================== -->

        <FormField
            label="Branch"
            required
            :error="
                form.errors.branch_id
            "
        >

            <SearchableSelect
                v-model="
                    form.branch_id
                "
                :options="
                    branches
                "
                label="label"
                value-key="id"
                placeholder="Select branch"
            />

        </FormField>


        <!-- ===================================================== -->
        <!-- Payment Date -->
        <!-- ===================================================== -->

        <FormField
            label="Payment Date"
            required
            :error="
                form.errors.payment_date
            "
        >

            <FlatPickr
                v-model="
                    form.payment_date
                "
                :config="
                    dateConfig
                "
                class="
                    w-full
                    rounded-lg
                    border
                    border-gray-300
                    bg-white
                    px-3
                    py-2
                    text-sm
                    text-gray-800
                    outline-none
                    transition
                    focus:border-blue-500
                    focus:ring-1
                    focus:ring-blue-500
                "
            />

        </FormField>


        <!-- ===================================================== -->
        <!-- Payment Method -->
        <!-- ===================================================== -->

        <FormField
            label="Payment Method"
            required
            :error="
                form.errors.payment_method
            "
        >

            <SearchableSelect
                v-model="
                    form.payment_method
                "
                :options="[
                    {
                        value: 'Cash',
                        label: 'Cash',
                    },
                    {
                        value: 'Bank Transfer',
                        label: 'Bank Transfer',
                    },
                ]"
                label="label"
                value-key="value"
                placeholder="Select payment method"
            />

        </FormField>


        <!-- ===================================================== -->
        <!-- Payment Account -->
        <!-- ===================================================== -->

        <FormField
            label="Payment Account"
            required
            :error="
                form.errors.payment_account_id
            "
        >

            <SearchableSelect
                v-model="
                    form.payment_account_id
                "
                :options="
                    paymentAccounts
                "
                label="label"
                value-key="id"
                placeholder="Select cash / bank account"
            />

        </FormField>

    </div>

</div>


        <!-- ========================================================= -->
        <!-- Settlement Payments -->
        <!-- ========================================================= -->

        <div
            class="
                rounded-xl
                border
                border-gray-200
                bg-white
                p-5
            "
        >

            <div
                class="
                    mb-4
                    flex
                    flex-col
                    gap-1
                    sm:flex-row
                    sm:items-center
                    sm:justify-between
                "
            >

                <div>

                    <h2
                        class="
                            text-sm
                            font-semibold
                            text-gray-900
                        "
                    >
                        Settlement Payments
                    </h2>

                    <p
                        class="
                            text-xs
                            text-gray-500
                        "
                    >
                        Select posted settlements and record their payment.
                    </p>

                </div>


                <div
                    class="
                        text-xs
                        text-gray-500
                    "
                >

                    {{ totalItems }} settlements

                </div>

            </div>


            <!-- No reseller -->

            <div
                v-if="
                    !form.reseller_id
                "
                class="
                    rounded-lg
                    border
                    border-dashed
                    border-gray-300
                    bg-gray-50
                    px-5
                    py-10
                    text-center
                "
            >

                <div
                    class="
                        text-sm
                        font-medium
                        text-gray-600
                    "
                >
                    Select a reseller first
                </div>

                <div
                    class="
                        mt-1
                        text-xs
                        text-gray-400
                    "
                >
                    Available receivable settlements will appear here.
                </div>

            </div>
            <!-- Details -->

            <div
                v-else
                class="
                    overflow-x-auto
                    rounded-lg
                    border
                    border-gray-200
                "
            >

                <div
                    class="
                        min-w-[1120px]
                    "
                >

                    <!-- Table Header -->

                    <div
                        class="
                            grid
                            grid-cols-[minmax(180px,1.7fr)_150px_140px_140px_140px_150px_55px]
                            items-center
                            gap-2
                            border-b
                            border-gray-200
                            bg-gray-50
                            px-3
                            py-2.5
                            text-[11px]
                            font-semibold
                            uppercase
                            tracking-wide
                            text-gray-500
                        "
                    >

                        <div>Settlement</div>

                        <div>Date / Period</div>

                        <div class="text-right">
                            Settlement Amount
                        </div>

                        <div class="text-right">
                            Previous Paid
                        </div>

                        <div class="text-right">
                            Outstanding
                        </div>

                        <div class="text-right">
                            Payment
                        </div>

                        <div></div>

                    </div>


                    <!-- Rows -->

                    <div
                        v-for="(
                            detail,
                            index
                        ) in form.details"

                        :key="index"

                        class="
                            grid
                            grid-cols-[minmax(180px,1.7fr)_150px_140px_140px_140px_150px_55px]
                            items-start
                            gap-2
                            border-b
                            border-gray-100
                            px-3
                            py-3
                            last:border-b-0
                        "
                    >

                        <!-- Settlement -->

                        <FormField
                            label="Settlement"
                            required
                            :error="
                                form.errors[
                                    `details.${index}.settlement_header_id`
                                ]
                            "
                        >

                            <SearchableSelect
                                v-model="
                                    detail.settlement_header_id
                                "
                                :options="
                                    availableSettlements(detail)
                                "
                                label="settlement_number"
                                value-key="id"
                                placeholder="Select settlement"
                                @update:modelValue="
                                    changeSettlement(detail)
                                "
                            />

                        </FormField>


                        <!-- Date / Period -->

                        <FormField
                            label="Date / Period"
                        >

                            <div
                                class="
                                    min-h-[38px]
                                    rounded-lg
                                    border
                                    border-gray-200
                                    bg-gray-50
                                    px-2
                                    py-1.5
                                "
                            >

                               <div class="text-sm text-gray-700">
                                    {{ getSettlementDate(detail) }}
                                </div>


                               <div class="text-xs text-gray-500">
                                {{ getSettlementPeriod(detail) }}
                            </div>

                            </div>

                        </FormField>


                        <!-- Settlement Amount -->

                        <FormField
                            label="Amount"
                        >

                            <div
                                class="
                                    flex
                                    min-h-[38px]
                                    items-center
                                    justify-end
                                    rounded-lg
                                    border
                                    border-gray-200
                                    bg-gray-50
                                    px-2
                                    text-sm
                                    font-medium
                                    tabular-nums
                                    text-gray-700
                                "
                            >

                                {{
                                    formatCurrency(
                                        detail.settlement_amount
                                    )
                                }}

                            </div>

                        </FormField>


                        <!-- Previous Paid -->

                        <FormField
                            label="Previous Paid"
                        >

                            <div
                                class="
                                    flex
                                    min-h-[38px]
                                    items-center
                                    justify-end
                                    rounded-lg
                                    border
                                    border-gray-200
                                    bg-gray-50
                                    px-2
                                    text-sm
                                    tabular-nums
                                    text-gray-700
                                "
                            >

                                {{
                                    formatCurrency(
                                        detail.previous_paid_amount
                                    )
                                }}

                            </div>

                        </FormField>


                        <!-- Outstanding -->

                        <FormField
                            label="Outstanding"
                        >

                            <div
                                class="
                                    flex
                                    min-h-[38px]
                                    items-center
                                    justify-end
                                    rounded-lg
                                    border
                                    border-gray-200
                                    bg-gray-50
                                    px-2
                                    text-sm
                                    font-semibold
                                    tabular-nums
                                    text-gray-800
                                "
                            >

                                {{
                                    formatCurrency(
                                        detail.previous_outstanding_amount
                                    )
                                }}

                            </div>

                        </FormField>


                        <!-- Payment -->

                        <FormField
                            label="Payment"
                            required
                            :error="
                                form.errors[
                                    `details.${index}.payment_amount`
                                ]
                            "
                        >

                            <FormInput
                                v-model="
                                    detail.payment_amount
                                "
                                type="number"
                                min="0"
                                step="0.01"
                                placeholder="0"
                                @input="
                                    calculateDetail(detail)
                                "
                            />

                        </FormField>


                        <!-- Remove -->

                        <div
                            class="
                                flex
                                min-h-[38px]
                                items-center
                                justify-center
                            "
                        >

                            <button
                                v-if="
                                    form.details.length > 1
                                "
                                type="button"
                                class="
                                    inline-flex
                                    h-9
                                    w-9
                                    items-center
                                    justify-center
                                    rounded-lg
                                    text-gray-400
                                    transition
                                    hover:bg-red-50
                                    hover:text-red-600
                                    focus:outline-none
                                    focus:ring-2
                                    focus:ring-red-500
                                "
                                title="Remove settlement"
                                @click="
                                    removeDetail(index)
                                "
                            >

                                <TrashIcon
                                    class="h-4.5 w-4.5"
                                />

                            </button>

                        </div>

                    </div>

                </div>

            </div>


            <!-- Add Settlement -->

            <div
                v-if="
                    canAddSettlement
                "
                class="
                    mt-4
                    flex
                    justify-start
                "
            >

                <BaseButton
                    type="button"
                    variant="secondary"
                    @click="addDetail"
                >

                    <PlusIcon
                        class="mr-1.5 h-4 w-4"
                    />

                    Add Settlement

                </BaseButton>

            </div>

        </div>
        <!-- ========================================================= -->
        <!-- Bottom -->
        <!-- ========================================================= -->

        <div
            class="
                grid
                grid-cols-1
                gap-6
                lg:grid-cols-[1fr_380px]
            "
        >

            <!-- Remarks -->

            <div
                class="
                    rounded-xl
                    border
                    border-gray-200
                    bg-white
                    p-5
                "
            >

                <h2
                    class="
                        mb-3
                        text-sm
                        font-semibold
                        text-gray-900
                    "
                >
                    Remarks
                </h2>


                <FormTextarea
                    v-model="
                        form.remarks
                    "
                    :rows="5"
                    placeholder="Add remarks or notes..."
                />


                <p
                    v-if="
                        form.errors.remarks
                    "
                    class="
                        mt-1
                        text-xs
                        text-red-600
                    "
                >
                    {{ form.errors.remarks }}
                </p>

            </div>


            <!-- Payment Summary -->

            <div
                class="
                    rounded-xl
                    border
                    border-gray-200
                    bg-white
                    p-5
                "
            >

                <h2
                    class="
                        mb-4
                        text-sm
                        font-semibold
                        text-gray-900
                    "
                >
                    Payment Summary
                </h2>


                <!-- Settlements -->

                <div
                    class="
                        flex
                        justify-between
                        py-1.5
                        text-sm
                    "
                >

                    <span class="text-gray-500">
                        Total Settlements
                    </span>

                    <span
                        class="
                            font-medium
                            text-gray-800
                        "
                    >
                        {{ totalItems }}
                    </span>

                </div>


                <!-- Payment -->

                <div
                    class="
                        mt-2
                        flex
                        justify-between
                        border-t
                        border-gray-100
                        pt-4
                    "
                >

                    <span
                        class="
                            text-sm
                            font-medium
                            text-gray-600
                        "
                    >
                        Total Payment
                    </span>

                    <span
                        class="
                            text-lg
                            font-semibold
                            text-gray-900
                        "
                    >
                        {{ formatCurrency(
                            totalPayment
                        ) }}
                    </span>

                </div>


                <!-- Validation -->

                <div
                    v-if="
                        paymentExceedsOutstanding
                    "
                    class="
                        mt-4
                        rounded-lg
                        border
                        border-red-200
                        bg-red-50
                        px-3
                        py-2
                        text-xs
                        text-red-600
                    "
                >

                    Payment cannot be greater than
                    the outstanding amount.

                </div>


                <!-- Error -->

                <div
                    v-if="
                        form.errors.details
                    "
                    class="
                        mt-4
                        rounded-lg
                        border
                        border-red-200
                        bg-red-50
                        px-3
                        py-2
                        text-xs
                        text-red-600
                    "
                >

                    {{ form.errors.details }}

                </div>

            </div>

        </div>


        <!-- ========================================================= -->
        <!-- Actions -->
        <!-- ========================================================= -->

        <div
            class="
                flex
                flex-col-reverse
                gap-3
                border-t
                border-gray-200
                pt-5
                sm:flex-row
                sm:justify-end
            "
        >

            <BaseButton
                type="button"
                variant="secondary"
                :disabled="
                    form.processing
                "
                @click="
                    emit('cancel')
                "
            >

                Cancel

            </BaseButton>


            <BaseButton
                type="submit"
                :loading="
                    form.processing
                "
                :disabled="
                    paymentExceedsOutstanding ||
                    !form.reseller_id ||
                    !form.branch_id ||
                    !form.payment_method ||
                    !form.payment_account_id
                "
            >

                Save

            </BaseButton>

        </div>

    </form>

</template>