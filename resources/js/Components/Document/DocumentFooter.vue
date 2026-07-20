<script setup>

defineProps({

    printedBy: String,

    printedAt: String,

    documentId: String,

})

</script>

<template>

<div

    class="
        mt-12
        border-t
        border-gray-300
        pt-5
        text-sm
        text-gray-500
    "

>

    <div class="grid grid-cols-2 gap-4">

        <div>

            <div>

                Printed By :
                <strong>{{ printedBy }}</strong>

            </div>

            <div>

                Printed At :
                {{ printedAt }}

            </div>

        </div>

        <div class="text-right">

            <div>

                Document ID :
                {{ documentId }}

            </div>

            <div>

                Nuvora ERP v1.0

            </div>

        </div>

    </div>

</div>

</template>