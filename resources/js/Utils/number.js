/**
 * ==========================================================
 * NUVORA ERP
 * Number Utility
 * ==========================================================
 */

/**
 * Format Number
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

/**
 * Parse Number
 */

export function parseNumber(

    value

)
{

    if (

        value === null ||

        value === undefined ||

        value === ''

    ) {

        return 0

    }

    return Number(

        String(

            value

        )

        .replace(

            /[^0-9.-]/g,

            ''

        )

    )

}

/**
 * Integer
 */

export function integer(

    value

)
{

    return parseInt(

        value || 0,

        10

    )

}

/**
 * Float
 */

export function decimal(

    value,

    precision = 2

)
{

    return Number(

        value || 0

    ).toFixed(

        precision

    )

}

/**
 * Compact Number
 */

export function compactNumber(

    value

)
{

    return new Intl.NumberFormat(

        'id-ID',

        {

            notation:

                'compact',

            compactDisplay:

                'short'

        }

    ).format(

        Number(

            value

        )

    )

}

/**
 * Clamp
 */

export function clamp(

    value,

    min,

    max

)
{

    return Math.min(

        Math.max(

            value,

            min

        ),

        max

    )

}