/**
 * ==========================================================
 * Validation Utility
 * ==========================================================
 */

export function required(
    value
)
{

    return value !== null &&

        value !== undefined &&

        value !== ''

}

export function email(
    value
)
{

    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/

        .test(

            value

        )

}

export function numeric(
    value
)
{

    return !isNaN(

        value

    )

}

export function minLength(
    value,
    length
)
{

    return String(

        value

    ).length >= length

}

export function maxLength(
    value,
    length
)
{

    return String(

        value

    ).length <= length

}