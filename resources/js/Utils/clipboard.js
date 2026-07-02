/**
 * ==========================================================
 * Clipboard Utility
 * ==========================================================
 */

export async function copy(

    text

)
{

    await navigator.clipboard

        .writeText(

            text

        )

}

export async function paste()
{

    return await navigator

        .clipboard

        .readText()

}