/**
 * ==========================================================
 * Download Utility
 * ==========================================================
 */

export function download(

    url,

    filename

)
{

    const link =

        document.createElement(

            'a'

        )

    link.href = url

    link.download = filename

    link.click()

}