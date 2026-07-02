/**
 * ==========================================================
 * Storage Utility
 * ==========================================================
 */

export function setStorage(

    key,

    value

)
{

    localStorage.setItem(

        key,

        JSON.stringify(

            value

        )

    )

}

export function getStorage(

    key,

    defaultValue = null

)
{

    const value =

        localStorage.getItem(

            key

        )

    return value

        ? JSON.parse(

            value

        )

        : defaultValue

}

export function removeStorage(

    key

)
{

    localStorage.removeItem(

        key

    )

}

export function clearStorage()
{

    localStorage.clear()

}