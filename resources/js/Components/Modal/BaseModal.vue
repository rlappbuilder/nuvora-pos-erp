<script setup>

import {

    computed,

} from 'vue'

const {

    show,

    title,

    size,

    closeOnOverlay,

    closeOnEsc,

} = defineProps({

    show: {
        type: Boolean,
        default: false,
    },

    title: {
        type: String,
        default: '',
    },

    size: {
        type: String,
        default: 'md',
    },

    closeOnOverlay: {
        type: Boolean,
        default: true,
    },

    closeOnEsc: {
        type: Boolean,
        default: true,
    },

})

const emit = defineEmits([

    'close',

])


const sizeClass = computed(() => {

    switch (size) {

        case 'sm':
            return 'max-w-md'

        case 'lg':
            return 'max-w-3xl'

        case 'xl':
            return 'max-w-5xl'

        default:
            return 'max-w-xl'

    }

})

function close()
{

    emit(

        'close'

    )

}
</script>
<template>

<Transition

    enter-active-class="duration-200 ease-out"

    enter-from-class="opacity-0 scale-95"

    enter-to-class="opacity-100 scale-100"

    leave-active-class="duration-150 ease-in"

    leave-from-class="opacity-100 scale-100"

    leave-to-class="opacity-0 scale-95"

>

<div

    v-if="show"

    class="
        fixed
        inset-0
        z-50
        flex
        items-center
        justify-center
        bg-black/40
        backdrop-blur-sm
        p-4
    "

    @click="

        closeOnOverlay

            && close()

    "

>
     <div

    :class="[

        sizeClass,

        'w-full',

        'max-h-[90vh]',

        'overflow-hidden',

        'rounded-2xl',

        'border',

        'border-gray-200',

        'bg-white',

        'shadow-xl',

    ]"

    @click.stop

>
             <div

                class="
                    flex
                    items-center
                    justify-between
                    border-b
                    border-gray-100
                    px-6
                    py-4
                "

            >

                <h2
                    class="
                        text-xl
                        font-semibold
                        tracking-tight
                        text-gray-900
                    "
                >
                    {{ title }}
                </h2>

                <button

                    type="button"

                    class="
                        rounded-lg
                        p-2
                        transition-all
                        duration-200
                        text-gray-400
                        hover:bg-gray-100
                        hover:text-gray-700
                        
                    "

                    @click="close"

                >

                    ✕

                </button>
           
            </div>
              <!-- body-->
                 <div

                    class="
                        max-h-[65vh]
                        overflow-y-auto
                        p-6
                    "

                >

                    <slot>

                        Modal Content

                    </slot>

                </div>
                <!-- end boy-->
                 <!-- footer-->
                 <div

                    v-if="$slots.footer"

                    class="
                        flex
                        items-center
                        justify-end
                        gap-3
                        border-t
                        border-gray-100
                        bg-gray-50
                        px-6
                        py-5
                    "

                >

                    <slot

                        name="footer"
                    />

                </div>
<!-- end footer-->
        </div>
</div>
 

</Transition>

</template>