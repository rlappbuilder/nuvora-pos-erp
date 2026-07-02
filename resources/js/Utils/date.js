/**
 * ==========================================================
 * NUVORA ERP
 * Date Utility
 * Version : 1.0
 * ==========================================================
 */

/**
 * Format Date
 */

export function formatDate(
    value,
    locale = 'id-ID'
)
{

    if (

        !value

    ) {

        return ''

    }

    return new Intl.DateTimeFormat(

        locale,

        {

            year: 'numeric',

            month: 'short',

            day: '2-digit'

        }

    ).format(

        new Date(

            value

        )

    )

}

/**
 * Format Date Time
 */

export function formatDateTime(
    value,
    locale = 'id-ID'
)
{

    if (

        !value

    ) {

        return ''

    }

    return new Intl.DateTimeFormat(

        locale,

        {

            year: 'numeric',

            month: 'short',

            day: '2-digit',

            hour: '2-digit',

            minute: '2-digit'

        }

    ).format(

        new Date(

            value

        )

    )

}

/**
 * Today
 */

export function today()
{

    return new Date()

        .toISOString()

        .slice(

            0,

            10

        )

}

/**
 * Now
 */

export function now()
{

    return new Date()

        .toISOString()

}

/**
 * Is Today
 */

export function isToday(
    value
)
{

    return today() === value

}