<script setup>

import {
    computed,
    onMounted,
    onUnmounted,
} from 'vue'


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

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


/*
|--------------------------------------------------------------------------
| Emits
|--------------------------------------------------------------------------
*/

const emit = defineEmits([
    'close',
])


/*
|--------------------------------------------------------------------------
| Modal Size
|--------------------------------------------------------------------------
*/

const sizeClass = computed(() => {

    switch (props.size) {

        case 'sm':
            return 'max-w-md'

        case 'lg':
            return 'max-w-3xl'

        case 'xl':
            return 'max-w-5xl'

        case '2xl':
            return 'max-w-7xl'

        case 'full':
            return 'max-w-[calc(100vw-2rem)]'

        default:
            return 'max-w-xl'

    }

})


/*
|--------------------------------------------------------------------------
| Close
|--------------------------------------------------------------------------
*/

function close()
{

    emit('close')

}


/*
|--------------------------------------------------------------------------
| Escape Key
|--------------------------------------------------------------------------
*/

function handleKeydown(event)
{

    if (
        event.key !== 'Escape'
    ) {

        return

    }

    if (
        !props.show ||
        !props.closeOnEsc
    ) {

        return

    }

    close()

}


onMounted(() => {

    window.addEventListener(
        'keydown',
        handleKeydown
    )

})


onUnmounted(() => {

    window.removeEventListener(
        'keydown',
        handleKeydown
    )

})

</script>


<template>

    <Teleport to="body">

        <Transition

            enter-active-class="
                transition
                duration-200
                ease-out
            "

            enter-from-class="
                opacity-0
            "

            enter-to-class="
                opacity-100
            "

            leave-active-class="
                transition
                duration-150
                ease-in
            "

            leave-from-class="
                opacity-100
            "

            leave-to-class="
                opacity-0
            "

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
                    bg-slate-950/45
                    p-4
                    backdrop-blur-[2px]
                "

                @click="
                    closeOnOverlay
                        && close()
                "

            >

                <!-- ================================================= -->
                <!-- Modal -->
                <!-- ================================================= -->

                <Transition

                    appear

                    enter-active-class="
                        transition
                        duration-200
                        ease-out
                    "

                    enter-from-class="
                        translate-y-2
                        scale-[0.98]
                        opacity-0
                    "

                    enter-to-class="
                        translate-y-0
                        scale-100
                        opacity-100
                    "

                    leave-active-class="
                        transition
                        duration-150
                        ease-in
                    "

                    leave-from-class="
                        translate-y-0
                        scale-100
                        opacity-100
                    "

                    leave-to-class="
                        translate-y-2
                        scale-[0.98]
                        opacity-0
                    "

                >

                    <div

                        :class="[

                            sizeClass,

                            'relative',

                            'flex',

                            'w-full',

                            'max-h-[calc(100vh-2rem)]',

                            'flex-col',

                            'overflow-hidden',

                            'rounded-2xl',

                            'border',

                            'border-slate-200',

                            'bg-white',

                            'shadow-2xl',

                        ]"

                        role="dialog"

                        aria-modal="true"

                        @click.stop

                    >

                        <!-- ========================================= -->
                        <!-- Header -->
                        <!-- ========================================= -->

                        <div

                            class="
                                flex
                                shrink-0
                                items-center
                                justify-between
                                border-b
                                border-slate-200
                                bg-white
                                px-6
                                py-4
                            "

                        >

                            <div
                                class="
                                    min-w-0
                                "
                            >

                                <h2
                                    class="
                                        truncate
                                        text-lg
                                        font-semibold
                                        tracking-tight
                                        text-slate-900
                                    "
                                >
                                    {{ title }}
                                </h2>

                                <div
                                    v-if="$slots.subtitle"
                                    class="
                                        mt-0.5
                                        text-sm
                                        text-slate-500
                                    "
                                >

                                    <slot
                                        name="subtitle"
                                    />

                                </div>

                            </div>


                            <!-- Close -->

                            <button

                                type="button"

                                class="
                                    ml-4
                                    inline-flex
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
                                    focus:ring-blue-500
                                    focus:ring-offset-1
                                "

                                aria-label="Close"

                                @click="close"

                            >

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                    class="h-5 w-5"
                                >

                                    <path
                                        fill-rule="evenodd"
                                        d="M4.22 4.22a.75.75 0 0 1 1.06 0L10 8.94l4.72-4.72a.75.75 0 1 1 1.06 1.06L11.06 10l4.72 4.72a.75.75 0 1 1-1.06 1.06L10 11.06l-4.72 4.72a.75.75 0 0 1-1.06-1.06L8.94 10 4.22 5.28a.75.75 0 0 1 0-1.06Z"
                                        clip-rule="evenodd"
                                    />

                                </svg>

                            </button>

                        </div>


                        <!-- ========================================= -->
                        <!-- Body -->
                        <!-- ========================================= -->

                        <div

                            class="
                                min-h-0
                                flex-1
                                overflow-y-auto
                                overscroll-contain
                                bg-white
                                px-6
                                py-6
                            "

                        >

                            <slot>

                                Modal Content

                            </slot>

                        </div>


                        <!-- ========================================= -->
                        <!-- Footer -->
                        <!-- ========================================= -->

                        <div

                            v-if="$slots.footer"

                            class="
                                flex
                                shrink-0
                                items-center
                                justify-end
                                gap-3
                                border-t
                                border-slate-200
                                bg-slate-50
                                px-6
                                py-4
                            "

                        >

                            <slot
                                name="footer"
                            />

                        </div>

                    </div>

                </Transition>

            </div>

        </Transition>

    </Teleport>

</template>