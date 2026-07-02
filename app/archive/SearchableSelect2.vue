<script setup>

import {

    ref,

    computed,

    watch,

    onMounted,

    onBeforeUnmount

}

from 'vue'

import ValidationError

from './ValidationError.vue'

import {

    inputClass,

    errorClass,

    labelClass

}

from './DesignSystem'

/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    modelValue: {

        type: [

            Number,

            String,

            null

        ],

        default: null

    },

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

    getLabel: {

        type: Function,

        default: null

    },

    getValue: {

        type: Function,

        default: null

    },

    placeholder: {

        type: String,

        default: 'Select...'

    },

    disabled: Boolean,

    readonly: Boolean,

    clearable: {

        type: Boolean,

        default: true

    },

    loading: Boolean,

    error: String

})

/*
|--------------------------------------------------------------------------
| Emits
|--------------------------------------------------------------------------
*/

const emit = defineEmits([

    'update:modelValue',

    'search'

])

/*
|--------------------------------------------------------------------------
| Refs
|--------------------------------------------------------------------------
*/

const wrapper = ref(null)

const inputRef = ref(null)

const open = ref(false)

const search = ref('')

const highlighted = ref(0)
/*
|--------------------------------------------------------------------------
| Helper
|--------------------------------------------------------------------------
*/

function optionLabel(item)
{

    if (

        !item

    ) {

        return ''

    }

    if (

        props.getLabel

    ) {

        return props.getLabel(item)

    }

    return item[props.label] ?? ''

}

function optionValue(item)
{

    if (

        !item

    ) {

        return null

    }

    if (

        props.getValue

    ) {

        return props.getValue(item)

    }

    return item[props.valueKey]

}
/*
|--------------------------------------------------------------------------
| Selected
|--------------------------------------------------------------------------
*/

const selected = computed(() => {

    return props.options.find(

        item =>

            optionValue(item) === props.modelValue

    ) || null

})

/*
|--------------------------------------------------------------------------
| Filter
|--------------------------------------------------------------------------
*/

const filteredOptions = computed(() => {

    if (

        !search.value

    ) {

        return props.options

    }

    return props.options.filter(

        item =>

            optionLabel(item)

                .toLowerCase()

                .includes(

                    search.value

                        .toLowerCase()

                )

    )

})
/*
|--------------------------------------------------------------------------
| Watch
|--------------------------------------------------------------------------
*/

watch(

    selected,

    value => {

        search.value =

            value

                ? optionLabel(value)

                : ''

    },

    {

        immediate: true

    }

)
/*
|--------------------------------------------------------------------------
| Methods
|--------------------------------------------------------------------------
*/

function openDropdown()
{

    if (

        props.disabled ||

        props.readonly

    ) {

        return

    }

    highlighted.value = 0

    open.value = true

}

function closeDropdown()
{

    open.value = false

}

function selectOption(item)
{

    emit(

        'update:modelValue',

        optionValue(item)

    )

    search.value = optionLabel(item)

    open.value = false

}

function clearSelection()
{

    if (

        !props.clearable

    ) {

        return

    }

    emit(

        'update:modelValue',

        null

    )

    search.value = ''

    open.value = false

}

function moveDown()
{

    if (

        !open.value

    ) {

        openDropdown()

        return

    }

    if (

        highlighted.value <

        filteredOptions.value.length - 1

    ) {

        highlighted.value++

    }

}

function moveUp()
{

    if (

        highlighted.value > 0

    ) {

        highlighted.value--

    }

}

function selectHighlighted()
{

    const item =

        filteredOptions.value[

            highlighted.value

        ]

    if (

        item

    ) {

        selectOption(

            item

        )

    }

}

function onEscape()
{

    closeDropdown()

}

function onInput(event)
{

    search.value =

        event.target.value

    open.value = true

    highlighted.value = 0

    emit(

        'search',

        search.value

    )

}
/*
|--------------------------------------------------------------------------
| Lifecycle
|--------------------------------------------------------------------------
*/

function handleClickOutside(event)
{

    if (

        wrapper.value &&

        !wrapper.value.contains(

            event.target

        )

    ) {

        closeDropdown()

    }

}

onMounted(

    () => {

        document.addEventListener(

            'click',

            handleClickOutside

        )

    }

)

onBeforeUnmount(

    () => {

        document.removeEventListener(

            'click',

            handleClickOutside

        )

    }

)
</script>
<template>

<div

    ref="wrapper"

    class="relative w-full"

>

    <!-- Input -->

    <div

        class="relative"

    >

        <input

            ref="inputRef"

            :value="search"

            type="text"

            autocomplete="off"

            :placeholder="placeholder"

            :disabled="disabled"

            :readonly="readonly"

            :class="[

                inputClass,

                error

                    ? errorClass

                    : ''

            ]"

            @focus="openDropdown"

            @input="onInput"

            @keydown.down.prevent="moveDown"

            @keydown.up.prevent="moveUp"

            @keydown.enter.prevent="selectHighlighted"

            @keydown.esc.prevent="onEscape"

        >

        <!-- Arrow -->

        <button

            type="button"

            tabindex="-1"

            class="
                absolute
                right-3
                top-1/2
                -translate-y-1/2
                text-gray-400
            "

            @click="

                open

                    ? closeDropdown()

                    : openDropdown()

            "

        >

            ▼

        </button>

            </div>
                <div

                v-if="open"

                class="
                    absolute
                    z-50
                    mt-2
                    max-h-64
                    w-full
                    overflow-y-auto
                    rounded-xl
                    border
                    border-gray-200
                    bg-white
                    shadow-xl
                "

            >
                    <div

                    v-if="loading"

                    class="
                        px-4
                        py-3
                        text-sm
                        text-gray-500
                    "

                >

                    Loading...

                </div>

                        <div

            v-else-if="

                filteredOptions.length === 0

            "

            class="
                px-4
                py-3
                text-sm
                text-gray-400
            "

        >

            No data found

        </div>
                <button

            v-for="(

                option,

                index

            )

            in

            filteredOptions"

            :key="

                optionValue(

                    option

                )

            "

            type="button"

            class="
                flex
                w-full
                items-center
                justify-between
                px-4
                py-2.5
                text-left
                text-sm
                transition
            "

            :class="[

                highlighted === index

                    ? 'bg-indigo-50'

                    : '',

                selected &&

                optionValue(option)

                ===

                optionValue(selected)

                    ? 'font-semibold text-indigo-600'

                    : ''

            ]"

            @mouseenter="

                highlighted = index

            "

            @click="

                selectOption(

                    option

                )

            "

        >

            <slot

                name="option"

                :option="option"

            >

                {{ optionLabel(option) }}

            </slot>

        </button>
        </div>
        <ValidationError

        :message="error"

    />

</div>

</template>