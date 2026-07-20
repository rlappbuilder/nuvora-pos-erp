import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

import permissions from '@/Config/permissions'

import {
    hasPermission,
    hasAnyPermission,
    hasAllPermissions,
} from '@/Utils/permission'

export function usePermission() {

    const page = usePage()

    const userPermissions = computed(() => {
        return page.props.auth?.permissions ?? []
    })

    return {

        permissions,

        userPermissions,

        hasPermission(permission) {

            return hasPermission(
                userPermissions.value,
                permission
            )

        },

        hasAnyPermission(list) {

            return hasAnyPermission(
                userPermissions.value,
                list
            )

        },

        hasAllPermissions(list) {

            return hasAllPermissions(
                userPermissions.value,
                list
            )

        },

    }

}