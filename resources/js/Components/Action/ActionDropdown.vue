<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'

import {
    useFloating,
    offset,
    flip,
    shift,
    autoUpdate,
    
} from '@floating-ui/vue'

import {
    EllipsisVerticalIcon,
    PencilSquareIcon,
    TrashIcon,
    EyeIcon,
    DocumentDuplicateIcon,
    ArrowDownTrayIcon,
    ClockIcon,
} from '@heroicons/vue/24/outline'

const emit = defineEmits([
    'view',
    'edit',
    'duplicate',
    'export',
    'delete',
    'history',
])
const EVENT_NAME = 'nuvora-action-dropdown-close'
const open = ref(false)

const reference = ref(null)
const floating = ref(null)

const { floatingStyles } = useFloating(
    reference,
    floating,
    {
        placement: 'bottom-end',
        whileElementsMounted: autoUpdate,
        middleware: [
            offset(6),
            flip(),
            shift({ padding: 8 }),
        ],
    }
)

function toggle()
{
    if (open.value) {

        open.value = false

        return

    }

    window.dispatchEvent(
        new CustomEvent(EVENT_NAME)
    )

    open.value = true
}


function close() {
    open.value = false
}
function handleCloseAll()
{
    open.value = false
}
function handleClickOutside(event) {
    if (
        reference.value &&
        floating.value &&
        !reference.value.contains(event.target) &&
        !floating.value.contains(event.target)
    ) {
        close()
    }
}

function handleEscape(event)
{
    if (
        event.key === 'Escape' &&
        open.value
    ) {
        event.preventDefault()

        close()
    }
}

onMounted(() => {
    document.addEventListener(
        'click',
        handleClickOutside
    )

    window.addEventListener(
        'keydown',
        handleEscape
    )

    window.addEventListener(
        EVENT_NAME,
        handleCloseAll
    )
})

onBeforeUnmount(() => {
    document.removeEventListener(
        'click',
        handleClickOutside
    )

    window.removeEventListener(
        'keydown',
        handleEscape
    )

    window.removeEventListener(
        EVENT_NAME,
        handleCloseAll
    )
})
</script>
<<template>

<div class="inline-block">

    <button
        ref="reference"
        @click.stop="toggle"
        class="
            rounded-lg
            p-2
            transition
            hover:bg-gray-100
        "
    >
        <EllipsisVerticalIcon
            class="h-5 w-5 text-gray-600"
        />
    </button>

    <Teleport to="body">

        <Transition
            enter-active-class="duration-150"
            leave-active-class="duration-100"
        >

            <div
                v-if="open"
                ref="floating"
                :style="floatingStyles"
                class="
                    z-50
                    w-52
                    overflow-hidden
                    rounded-xl
                    border
                    border-gray-200
                    bg-white
                    shadow-lg
                "
            >

                <button
                    class="
                        flex
                        w-full
                        items-center
                        gap-3
                        px-4
                        py-2.5
                        text-sm
                        text-gray-700
                        transition-colors
                        duration-150
                        hover:bg-indigo-50
                        hover:text-indigo-600
                    "
                    @click="$emit('view'); close()"
                >
                    <EyeIcon class="h-5 w-5" />
                    View
                </button>
                <button
                    class="
                        flex
                        w-full
                        items-center
                        gap-3
                        px-4
                        py-2.5
                        text-sm
                        text-gray-700
                        transition-colors
                        duration-150
                        hover:bg-indigo-50
                        hover:text-indigo-600
                    "
                    @click="$emit('history'); close()"
                >
                    <ClockIcon class="h-5 w-5" />
                    Price History
                </button>
                <button
                    class="
                        flex
                        w-full
                        items-center
                        gap-3
                        px-4
                        py-2.5
                        text-sm
                        text-gray-700
                        transition-colors
                        duration-150
                        hover:bg-indigo-50
                        hover:text-indigo-600
                    "
                    @click="$emit('edit'); close()"
                >
                    <PencilSquareIcon class="h-5 w-5" />
                    Edit
                </button>

                <button
                    class="
                        flex
                        w-full
                        items-center
                        gap-3
                        px-4
                        py-2.5
                        text-sm
                        text-gray-700
                        transition-colors
                        duration-150
                        hover:bg-indigo-50
                        hover:text-indigo-600
                    "
                    @click="$emit('duplicate'); close()"
                >
                    <DocumentDuplicateIcon class="h-5 w-5" />
                    Duplicate
                </button>

                <button
                    class="
                        flex
                        w-full
                        items-center
                        gap-3
                        px-4
                        py-2.5
                        text-sm
                        text-gray-700
                        transition-colors
                        duration-150
                        hover:bg-indigo-50
                        hover:text-indigo-600
                    "
                    @click="$emit('export'); close()"
                >
                    <ArrowDownTrayIcon class="h-5 w-5" />
                    Export
                </button>

                <div
                    class="
                        mx-2
                        my-1
                        border-t
                        border-gray-100
                    "
                ></div>

                <button
                    class="
                        flex
                        w-full
                        items-center
                        gap-3
                        px-4
                        py-2.5
                        text-sm
                        text-red-600
                        transition-colors
                        duration-150
                        hover:bg-red-50
                    "
                    @click="$emit('delete'); close()"
                >
                    <TrashIcon class="h-5 w-5" />
                    Delete
                </button>

            </div>

        </Transition>

    </Teleport>

</div>

</template>