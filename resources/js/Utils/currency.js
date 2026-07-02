/**
 * ==========================================================
 * NUVORA ERP
 * Currency Utility
 * Version : 1.0
 * ==========================================================
 */

/**
 * Format Currency
 */

export function formatCurrency(

    value,

    locale = 'id-ID',

    decimals = 0

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

        locale,

        {

            minimumFractionDigits:

                decimals,

            maximumFractionDigits:

                decimals

        }

    ).format(

        Number(

            value

        )

    )

}

/**
 * Parse Currency
 */

export function parseCurrency(

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

    )

}

/**
 * Currency Symbol
 */

export function getCurrencySymbol(

    currency = 'IDR'

)
{

    const symbols = {

        IDR: 'Rp',

        USD: '$',

        EUR: '€',

        SGD: 'S$',

        GBP: '£',

        JPY: '¥',

        MYR: 'RM',

        AUD: 'A$',

        CNY: '¥'

    }

    return symbols[

        currency

    ] || currency

}

/**
 * Currency Formatter
 */

export function currency(

    value,

    currencyCode = 'IDR',

    decimals = 0

)
{

    return `${

        getCurrencySymbol(

            currencyCode

        )

    } ${

        formatCurrency(

            value,

            'id-ID',

            decimals

        )

    }`

}

/**
 * Compact Currency
 */

export function compactCurrency(

    value,

    currencyCode = 'IDR'

)
{

    if (

        value === null ||

        value === undefined ||

        value === ''

    ) {

        return ''

    }

    return `${

        getCurrencySymbol(

            currencyCode

        )

    } ${

        new Intl.NumberFormat(

            'id-ID',

            {

                notation:

                    'compact',

                compactDisplay:

                    'short',

                maximumFractionDigits:

                    1

            }

        ).format(

            Number(

                value

            )

        )

    }`

}

/**
 * Is Currency
 */

export function isCurrency(

    value

)
{

    return !isNaN(

        parseCurrency(

            value

        )

    )

}

/**
 * Zero Currency
 */

export function zeroCurrency()

{

    return 0

}

/**
 * Empty Currency
 */

export function emptyCurrency()

{

    return ''

}