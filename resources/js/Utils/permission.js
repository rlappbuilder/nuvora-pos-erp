/**
 * ==========================================================
 * Permission Utility
 * ==========================================================
 */

export function hasPermission(

    permissions,

    permission

)
{

    return permissions

        ?.includes(

            permission

        )

}

export function hasAnyPermission(

    permissions,

    list

)
{

    return list.some(

        item =>

            permissions.includes(

                item

            )

    )

}

export function hasAllPermissions(

    permissions,

    list

)
{

    return list.every(

        item =>

            permissions.includes(

                item

            )

    )

}