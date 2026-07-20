/**
 * ==========================================================
 * NUVORA ERP
 * Formatter Utility
 * ==========================================================
 */



export function formatNumber(
    value,
    locale = 'id-ID'
)
{

    if (

        value === null ||

        value === undefined ||

        value === ''

    ) {

        return ''

    }

    return new Intl.NumberFormat(

        locale

    ).format(

        Number(

            value

        )

    )

}

export function unformatNumber(
    value
)
{

    if (

        value === null ||

        value === undefined

    ) {

        return ''

    }

    return String(

        value

    )

        .replace(

            /[^0-9,-]/g,

            ''

        )

        .replace(

            /\./g,

            ''

        )

        .replace(

            ',',

            '.'

        )

}

export function formatPercent(
    value,
    decimals = 2
)
{

    if (

        value === null ||

        value === undefined

    ) {

        return ''

    }

    return Number(

        value

    ).toFixed(

        decimals

    ) + '%'

}
