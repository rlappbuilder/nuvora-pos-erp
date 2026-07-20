<script setup>
import { ref } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

import {
    ChevronDownIcon,
    UserCircleIcon,
    Cog6ToothIcon,
    ArrowRightStartOnRectangleIcon,
    KeyIcon,
} from '@heroicons/vue/24/outline'

const page = usePage()

const open = ref(false)

const toggle = () => {
    open.value = !open.value
}
</script>

<template>

<div class="relative">

    <button
        @click="toggle"
        class="flex items-center gap-3"
    >

        <div
            class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-600 font-semibold text-white"
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
        class="absolute right-0 mt-3 w-64 rounded-xl border bg-white shadow-xl"
    >

        <div class="border-b p-4">

            <p class="font-semibold">

                {{ page.props.auth.user.name }}

            </p>

            <p class="text-sm text-gray-500">

                {{ page.props.auth.user.email }}

            </p>

        </div>

        <Link
            href="#"
            class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50"
        >

            <UserCircleIcon class="h-5 w-5"/>

            My Profile

        </Link>

        <Link
            href="#"
            class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50"
        >

            <Cog6ToothIcon class="h-5 w-5"/>

            Settings

        </Link>

        <Link
            href="#"
            class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50"
        >

            <KeyIcon class="h-5 w-5"/>

            Change Password

        </Link>

        <div class="border-t">

            <Link
                method="post"
                :href="route('logout')"
                as="button"
                class="flex w-full items-center gap-3 px-4 py-3 text-red-600 hover:bg-red-50"
            >

                <ArrowRightStartOnRectangleIcon class="h-5 w-5"/>

                Logout

            </Link>

        </div>

    </div>

</div>

</template>