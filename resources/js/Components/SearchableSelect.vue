<script setup>

import {

    Combobox,
    ComboboxInput,
    ComboboxButton,
    ComboboxOptions,
    ComboboxOption,

} from '@headlessui/vue'

import {

    computed,
    ref

} from 'vue'

const props = defineProps({

    modelValue: [String, Number, Object],

    options: {

        type: Array,

        default: () => []

    },

    label: {

        type: String,

        default: 'name'

    },

    valueKey: {

        type: String,

        default: 'id'

    },

    placeholder: {

        type: String,

        default: 'Search...'

    }

})

const emit = defineEmits([

    'update:modelValue'

])

const query = ref('')

const filteredOptions = computed(() => {

    if (!query.value) {

        return props.options

    }

    return props.options.filter(

        option =>

            option[props.label]

                .toLowerCase()

                .includes(

                    query.value
                        .toLowerCase()

                )

    )

})

const selectedOption = computed({

    get() {

        return props.options.find(

            option =>

                option[
                    props.valueKey
                ] ===

                props.modelValue

        )

    },

    set(value) {

        emit(

            'update:modelValue',

            value

                ? value[
                    props.valueKey
                ]

                : null

        )

    }

})

</script>

<template>

<Combobox
    v-model="selectedOption"
>

    <div
        class="relative"
    >

        <ComboboxInput

            class="
                w-full
                rounded-lg
                border
                border-gray-300
                px-3
                py-2
            "

            :display-value="
                item =>
                item?.[label] || ''
            "

            :placeholder="
                placeholder
            "

            @change="
                query =
                $event.target.value
            "

        />

        <ComboboxButton

            class="
                absolute
                inset-y-0
                right-0
                flex
                items-center
                px-3
            "

        >

            ▼

        </ComboboxButton>

        <ComboboxOptions

            class="
                absolute
                z-50
                mt-1
                max-h-60
                w-full
                overflow-auto
                rounded-lg
                border
                bg-white
                shadow-lg
            "

        >

            <ComboboxOption

                v-for="
                    item
                    in
                    filteredOptions
                "

                :key="
                    item[valueKey]
                "

                :value="
                    item
                "

                v-slot="{
                    active,
                    selected
                }"

            >

                <li

                    :class="[
                        'cursor-pointer px-3 py-2',

                        active
                            ? 'bg-blue-100'
                            : ''
                    ]"

                >

                    {{
                        item[label]
                    }}

                </li>

            </ComboboxOption>

        </ComboboxOptions>

    </div>

</Combobox>

</template>