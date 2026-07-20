<script setup>


import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import BaseButton from '@/Components/Button/BaseButton.vue'
import PrintHeader from '@/Components/Print/PrintHeader.vue'
import PrintFooter from '@/Components/Print/PrintFooter.vue'
const props = defineProps({

    company: {

        type: Object,

        required: true,

    },

    title: {

        type: String,

        required: true,

    },

    subtitle: {

        type: String,

        default: '',

    },

    backUrl: {

        type: String,

        default: '',

    },

    orientation: {

    type: String,

    default: 'portrait',

    },

    documentNumber: {

    type: String,

    default: '',

},

documentDate: {

    type: String,

    default: '',

},

printedBy: {

    type: String,

    default: '',

},

})
const printedAt = computed(() => {

    return new Date().toLocaleString('id-ID', {

        dateStyle: 'full',

        timeStyle: 'short',

    })

})
function back()
{

    if (props.backUrl) {

        router.visit(props.backUrl)

    }

}

function printPage()
{

    window.print()

}
</script>

<template>

<div
    :class="[
        'min-h-screen',
        'bg-white',
        'text-gray-900',
        'p-10',
        orientation === 'landscape'
            ? 'print-landscape'
            : 'print-portrait'
    ]"
>
<!-- Toolbar -->

<div
    class="
        print-toolbar
        mb-6
        flex
        justify-between
        items-center
    "
>

    <BaseButton
        variant="secondary"
        @click="back"
    >

        ← Back

    </BaseButton>

    <BaseButton
        @click="printPage"
    >

        🖨 Print

    </BaseButton>

</div>
    <!-- Header -->

    

    
<PrintHeader

    :company="company"

    :title="title"

    :subtitle="subtitle"

/>
    <!-- Content -->

    <slot />
        <!-- Document Information -->

        <div
            v-if="documentNumber || documentDate"
            class="
                mb-8
                border
                rounded-lg
                p-4
            "
        >

            <div
                class="
                    grid
                    grid-cols-2
                    gap-4
                "
            >

                <div>

                    <strong>Document No</strong>

                    <div>

                        {{ documentNumber || '-' }}

                    </div>

                </div>

                <div>

                    <strong>Document Date</strong>

                    <div>

                        {{ documentDate || '-' }}

                    </div>

                </div>

            </div>

        </div>
    <!-- Footer -->
    <PrintFooter

        :printed-by="printedBy"

    />

</div>

</template>

<style>
@media print {

    @page {

        margin: 15mm;

    }

    body {

        background: white;

    }

    .print-toolbar {

        display: none !important;

    }

    .print-portrait {

        page: portrait;

    }

    .print-landscape {

        page: landscape;

    }

}

.print-portrait {

    page: portrait;

}

.print-landscape {

    page: landscape;

}

@page portrait {

    size: A4 portrait;

}

@page landscape {

    size: A4 landscape;

}
</style>