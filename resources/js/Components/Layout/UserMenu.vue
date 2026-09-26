<script setup>
import { ref,computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'

import {
    ChevronDownIcon,
    ChevronRightIcon,
    UserCircleIcon,
    Cog6ToothIcon,
    ArrowRightStartOnRectangleIcon,
    KeyIcon,
    BuildingOffice2Icon,
    CheckIcon,
} from '@heroicons/vue/24/outline'

const page = usePage()

const open = ref(false)
const branchOpen = ref(false)

const toggle = () => {
    open.value = !open.value
}

const toggleBranch = () => {
    branchOpen.value = !branchOpen.value
}

const switchBranch = (branchId) => {
    if (
        currentBranch.value &&
        currentBranch.value.id === branchId
    ) {
        return
    }

    router.post(
        route('user.context.branch'),
        {
            branch_id: branchId,
        },
        {
            preserveScroll: true,

            onSuccess: () => {
                branchOpen.value = false
                open.value = false
            },
        }
    )
}
const currentBranch = computed(
    () => page.props.auth.current_branch
)

const branches = computed(
    () => page.props.auth.branches ?? []
)
</script>

<template>

<div class="relative">

    <button
        @click="toggle"
        class="flex items-center gap-3"
    >

        <div
            class="
                flex h-10 w-10
                items-center justify-center
                rounded-full
                bg-blue-600
                font-semibold
                text-white
            "
        >
            {{ page.props.auth.user.name.charAt(0) }}
        </div>

        <div class="hidden text-left lg:block">

            <p class="text-sm font-semibold">
                {{ page.props.auth.user.name }}
            </p>

            <p class="text-xs text-gray-500">
                {{ page.props.auth.user.email }}
            </p>

        </div>

        <ChevronDownIcon class="h-4 w-4"/>

    </button>

    <div
        v-if="open"
        class="
            absolute right-0 mt-3 w-72
            rounded-xl
            border
            bg-white
            shadow-xl
            z-50
        "
    >

        <!-- User Info -->
        <div class="border-b p-4">

            <p class="font-semibold">
                {{ page.props.auth.user.name }}
            </p>

            <p class="text-sm text-gray-500">
                {{ page.props.auth.user.email }}
            </p>

        </div>

        <!-- My Profile -->
        <Link
            href="#"
            class="
                flex items-center gap-3
                px-4 py-3
                hover:bg-gray-50
            "
        >

            <UserCircleIcon class="h-5 w-5"/>

            My Profile

        </Link>

        <!-- Current Branch -->
        <div class="border-b border-gray-100">

            <button
                type="button"
                @click="toggleBranch"
                class="
                    flex w-full
                    items-center justify-between
                    px-4 py-3
                    text-left
                    hover:bg-gray-50
                "
            >

                <div class="flex items-center gap-3">

                    <BuildingOffice2Icon
                        class="h-5 w-5"
                    />

                    <div>

                        <p class="text-sm font-medium">
                            Current Branch
                        </p>

                        <p
                            v-if="currentBranch"
                            class="text-xs text-gray-500"
                        >
                            {{ currentBranch.code }}
                            -
                            {{ currentBranch.name }}
                        </p>

                        <p
                            v-else
                            class="text-xs text-gray-400"
                        >
                            No branch selected
                        </p>

                    </div>

                </div>

                <ChevronRightIcon
                    class="h-4 w-4 transition-transform"
                    :class="{
                        'rotate-90': branchOpen
                    }"
                />

            </button>

            <!-- Branch List -->
            <div
                v-if="branchOpen"
                class="border-t bg-gray-50 py-1"
            >

                <button
                    v-for="branch in branches"
                    :key="branch.id"
                    type="button"
                    @click="switchBranch(branch.id)"
                    class="
                        flex w-full
                        items-center justify-between
                        px-4 py-2.5 pl-12
                        text-left
                        hover:bg-gray-100
                    "
                    :class="{
                        'bg-blue-50 text-blue-700':
                            currentBranch &&
                            currentBranch.id === branch.id
                    }"
                >

                    <div>

                        <p class="text-sm font-medium">
                            {{ branch.name }}
                        </p>

                        <p class="text-xs text-gray-500">
                            {{ branch.code }}
                        </p>

                    </div>

                    <CheckIcon
                        v-if="
                            currentBranch &&
                            currentBranch.id === branch.id
                        "
                        class="h-5 w-5 text-blue-600"
                    />

                </button>

                <div
                    v-if="branches.length === 0"
                    class="
                        px-4 py-3
                        pl-12
                        text-sm
                        text-gray-400
                    "
                >
                    No branch available.
                </div>

            </div>

        </div>

        <!-- Settings -->
        <Link
            href="#"
            class="
                flex items-center gap-3
                px-4 py-3
                hover:bg-gray-50
            "
        >

            <Cog6ToothIcon class="h-5 w-5"/>

            Settings

        </Link>

        <!-- Change Password -->
        <Link
            href="#"
            class="
                flex items-center gap-3
                px-4 py-3
                hover:bg-gray-50
            "
        >

            <KeyIcon class="h-5 w-5"/>

            Change Password

        </Link>

        <!-- Logout -->
        <div class="border-t">

            <Link
                method="post"
                :href="route('logout')"
                as="button"
                class="
                    flex w-full
                    items-center gap-3
                    px-4 py-3
                    text-red-600
                    hover:bg-red-50
                "
            >

                <ArrowRightStartOnRectangleIcon
                    class="h-5 w-5"
                />

                Logout

            </Link>

        </div>

    </div>

</div>

</template>