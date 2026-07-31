<script setup>

import { router } from '@inertiajs/vue3'

const props = defineProps({

    data: {

        type: Object,

        required: true,

    },

     label: {
        type: String,
        default: 'Records',
    },

})

function visit(url)
{

    if (!url) return

    router.visit(

        url,

        {

            preserveScroll: true,

            preserveState: true,

        }

    )

}
function changePerPage(event)
{
    router.get(
        window.location.pathname,
        {
            ...Object.fromEntries(
                new URLSearchParams(window.location.search)
            ),

            per_page: event.target.value,

            page: 1,

        },
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        }
    )
}
</script>
<template>

<div
    class="
        flex
        flex-col
        gap-4
        border-t
        border-gray-200
        px-6
        py-4
        lg:flex-row
        lg:items-center
        lg:justify-between
    "
>

    <!-- Left -->

    <div
    class="
        flex
        flex-col
        gap-3

        lg:flex-row
        lg:items-center
        lg:justify-between
    "
>

    <!-- Info -->

    <div
        class="text-sm text-gray-600"
    >

        Showing

        <strong>{{ data.from ?? 0 }}</strong>

        -

        <strong>{{ data.to ?? 0 }}</strong>

        of

        <strong>{{ data.total }}</strong>

        {{ label }}

    </div>

    <!-- Rows -->

    <div
        class="
            flex
            items-center
            gap-2
        "
    >

        <span class="text-sm text-gray-600">

            Rows

        </span>

        <select

            class="
                rounded-lg
                border
                border-gray-300
                px-2
                py-1
            "

            @change="changePerPage"

            :value="data.per_page"

        >

            <option :value="10">10</option>

            <option :value="20">20</option>

            <option :value="50">50</option>

            <option :value="100">100</option>

        </select>

    </div>

</div>

    <!-- Right -->

    <div
        class="
            flex
            flex-wrap
            items-center
            gap-2
        "
    >

        <button

            v-for="link in data.links"

            :key="link.label"

            v-html="link.label"

            :disabled="!link.url"

            @click="visit(link.url)"

            class="
                rounded-lg
                border
                px-3
                py-2
                text-sm
                transition
            "

            :class="

                link.active

                    ? 'bg-indigo-600 border-indigo-600 text-white'

                    : 'border-gray-300 hover:bg-gray-50'

            "

        />

    </div>

</div>

</template>