<script setup>
import BaseButton from '@/Components/Button/BaseButton.vue'
import DropdownButton from '@/Components/Dropdown/DropdownButton.vue'

import {
    MagnifyingGlassIcon,
    ArrowPathIcon,
    ArrowDownTrayIcon,
    PlusIcon,
    CheckCircleIcon,
    XCircleIcon,
    TrashIcon,
} from '@heroicons/vue/24/outline'


</script>
<template>

<div
    class="
        mb-6
        pb-4
        border-b
        border-gray-200
    "
>

    <div
        class="
            flex
            flex-col
            gap-4
            lg:flex-row
            lg:items-center
            lg:justify-between
        "
    >

        <!-- LEFT -->

        <div class="flex-1">

            
        </div>

        <!-- RIGHT -->

        <div
            class="
                flex
                flex-wrap
                items-center
                gap-2
            "
        >

            <!-- Refresh -->

            <!-- Export -->

           
            <!-- Bulk -->

           

            <!-- Create -->

           

        </div>

    </div>

    <!-- Filters -->

    <div
        class="
            mt-4
            flex
            flex-wrap
            gap-3
        "
    >

        <slot name="filters"/>

    </div>

</div>

</template>