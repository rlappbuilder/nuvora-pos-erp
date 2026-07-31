<script setup>
import {

    ChevronUpIcon,

    ChevronDownIcon,

    ChevronUpDownIcon,

} from '@heroicons/vue/24/solid'

import { computed } from 'vue'
const emit = defineEmits([
    'sort'
])

const props = defineProps({

    align: {

        type: String,

        default: 'left',

    },

    width: {

        type: String,

        default: '',

    },

    sortable: {

        type: Boolean,

        default: false,

    },
    column: {

    type: String,

    default: '',

},

sort: {

    type: String,

    default: '',

},

direction: {

    type: String,

    default: 'asc',

},

})

const cellClass = computed(() => [

    'px-6',

    'py-4',

    'text-xs',

    'font-bold',

    'tracking-wider',

    'uppercase',

    'text-slate-700',

    'bg-slate-100',

    props.align === 'center'
        ? 'text-center'
        : '',

    props.align === 'right'
        ? 'text-right'
        : '',

])
function onSort()
{

    if (!props.sortable) {

        return

    }

    emit(

        'sort',

        props.column

    )

}
</script>

<template>

<th
    :class="[
        'group',
        cellClass,

        props.sortable

            ? 'cursor-pointer select-none hover:bg-gray-50 transition-colors duration-200'

            : '',

    ]"

    @click="onSort"
>

   <div
    class="
        flex
        items-center
        gap-2
    "
>

    <slot />

    <div
        class="
            flex
            w-5
            justify-center
        "
    >

        
          <ChevronUpIcon

            v-if="

                sortable &&

                sort === column &&

                direction === 'asc'

            "

            class="
                h-4
                w-4
                text-indigo-600
            "

        />

        <ChevronDownIcon

            v-else-if="

                sortable &&

                sort === column &&

                direction === 'desc'

            "

            class="
                    h-4
                    w-4
                    text-gray-300
                    transition-colors
                    duration-200
                    group-hover:text-gray-500
                "

        />

       <ChevronUpDownIcon

            v-else-if="sortable"

           class="
                    h-4
                    w-4
                    text-indigo-600
                    transition-all
                    duration-200
                "

        />
    </div>
</div>
</th>

</template>