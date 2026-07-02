<script setup>

import { computed } from 'vue'

const props = defineProps({

    title: {

        type: String,

        default: ''

    },

    description: {

        type: String,

        default: ''

    },

    icon: {

        type: String,

        default: ''

    },

    padding: {

        type: String,

        default: 'md'

    },

    shadow: {

        type: Boolean,

        default: true

    }

})

const paddingClass = computed(() => {

    switch (props.padding) {

        case 'sm':

            return 'p-4'

        case 'lg':

            return 'p-8'

        default:

            return 'p-6'

    }

})

const cardClass = computed(() => [

    'overflow-visible',

    'rounded-2xl',

    'border',

    'border-gray-200',

    'bg-white',

    props.shadow ? 'shadow-sm' : ''

])

</script>

    <template>

<div

    :class="cardClass"

>

    <!-- ===================================================== -->
    <!-- Header -->
    <!-- ===================================================== -->

    <div

        v-if="

            title ||

            description ||

            $slots.header ||

            $slots.actions

        "

        class="
            border-b
            border-gray-100
            bg-gray-50
            px-6
            py-5
        "

    >

        <div

            class="
                flex
                flex-col
                gap-4
                lg:flex-row
                lg:items-start
                lg:justify-between
            "

        >

            <!-- Left -->

            <div

                class="
                    flex
                    items-start
                    gap-4
                "

            >

                <!-- Icon -->

                <div

                    v-if="icon"

                    class="
                        flex
                        h-10
                        w-10
                        items-center
                        justify-center
                        rounded-xl
                        bg-indigo-50
                        text-xl
                    "

                >

                    {{ icon }}

                </div>

                <!-- Title -->

                <div>

                    <h3

                        v-if="title"

                        class="
                            text-lg
                            font-semibold
                            text-gray-900
                        "

                    >

                        {{ title }}

                    </h3>

                    <p

                        v-if="description"

                        class="
                            mt-1
                            text-sm
                            text-gray-500
                        "

                    >

                        {{ description }}

                    </p>

                </div>

            </div>

            <!-- Actions -->

            <div

                v-if="$slots.actions"

                class="
                    flex
                    items-center
                    gap-2
                "

            >

                <slot

                    name="actions"

                />

            </div>

        </div>

    </div>

    <!-- ===================================================== -->
    <!-- Body -->
    <!-- ===================================================== -->

    <div

        :class="paddingClass"

    >

        <slot />

    </div>

    <!-- ===================================================== -->
    <!-- Footer -->
    <!-- ===================================================== -->

    <div

        v-if="$slots.footer"

        class="
            border-t
            border-gray-100
            bg-gray-50
            px-6
            py-4
        "

    >

        <slot

            name="footer"

        />

    </div>

</div>

</template>