<script setup>

defineProps({

    label: String,

    value: [String, Number],

})

</script>

<template>



         <div class="grid grid-cols-12 py-2">

            <div class="col-span-4 font-medium text-gray-600">

                {{ label }}

            </div>

            <div class="col-span-8 text-gray-900">

                {{ value }}

            </div>

         </div>



</template>