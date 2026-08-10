<script setup>
defineOptions({
    inheritAttrs: false,
})
import { ref, onMounted, onBeforeUnmount } from 'vue'
import BaseButton from '@/Components/Button/BaseButton.vue'
import { CheckCircleIcon, ChevronDownIcon } from '@heroicons/vue/24/outline'
const open = ref(false)
const dropdown = ref(null)

const props = defineProps({

    count: {
        type: Number,
        default: 0,
    },

    disabled: {
        type: Boolean,
        default: false,
    },

    actions: {
        type: Array,
        default: () => [
            'delete',
            'activate',
            'deactivate',
        ],
    },

})
const emit = defineEmits([
    'delete',
    'activate',
    'deactivate',
    'export',
])

function toggle()
{
    if (props.disabled) return

    open.value = !open.value
}

function close()
{
    open.value = false
}

function clickOutside(e)
{
    if (
        dropdown.value &&
        !dropdown.value.contains(e.target)
    ) {
        close()
    }
}

onMounted(() => {

    document.addEventListener(
        'click',
        clickOutside
    )

})

onBeforeUnmount(() => {

    document.removeEventListener(
        'click',
        clickOutside
    )

})
function handleExport()
{
    emit('export')

    close()
}

function handleDelete()
{
    emit('delete')

    close()
}

function handleActivate()
{
    emit('activate')

    close()
}

function handleDeactivate()
{
    emit('deactivate')

    close()
}
</script>

<template>

<!--<div
    ref="dropdown"
    class="relative w-full lg:w-auto"
>
-->
<div
    ref="dropdown"
    class="relative w-full lg:w-auto"
    :class="$attrs.class"
>

        <BaseButton
    variant="secondary"
    class="w-full lg:w-36"
    :disabled="props.disabled"
    @click.stop="toggle"
>
    Bulk ({{ props.count }})
</BaseButton>

 <div
    v-if="open"
    class="
        absolute
        right-0
        z-50
        mt-2
        w-56
        overflow-hidden
        rounded-xl
        border
        border-gray-200
        bg-white
        shadow-xl
    "
>

    <!-- Export -->

    <button
        v-if="actions.includes('export')"
        type="button"
        class="
            w-full
            px-4
            py-3
            text-left
            transition
            hover:bg-gray-50
        "
        @click="handleExport"
    >
        📤 Export Selected
    </button>


    <!-- Delete -->

    <button
        v-if="actions.includes('delete')"
        type="button"
        class="
            w-full
            px-4
            py-3
            text-left
            text-red-600
            transition
            hover:bg-red-50
        "
        @click="handleDelete"
    >
        🗑 Delete Selected
    </button>


    <!-- Activate -->

    <button
        v-if="actions.includes('activate')"
        type="button"
        class="
            w-full
            px-4
            py-3
            text-left
            transition
            hover:bg-green-50
        "
        @click="handleActivate"
    >
        ✅ Activate
    </button>


    <!-- Deactivate -->

    <button
        v-if="actions.includes('deactivate')"
        type="button"
        class="
            w-full
            px-4
            py-3
            text-left
            transition
            hover:bg-yellow-50
        "
        @click="handleDeactivate"
    >
        🚫 Deactivate
    </button>

</div>

</div>

</template>