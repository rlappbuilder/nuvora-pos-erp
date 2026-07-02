/**
 * ============================================================
 * NUVORA ERP UI KIT
 * Design System v1.0
 * ------------------------------------------------------------
 * Jangan ubah style langsung di Component.
 * Semua Component WAJIB menggunakan token di file ini.
 * ============================================================
 */

/*
|--------------------------------------------------------------------------
| Border Radius
|--------------------------------------------------------------------------
*/

export const Radius = {

    sm: 'rounded-md',

    md: 'rounded-lg',

    lg: 'rounded-xl',

    xl: 'rounded-2xl',

}

/*
|--------------------------------------------------------------------------
| Input Size
|--------------------------------------------------------------------------
*/

export const InputSize = {

    sm: 'px-3 py-2 text-sm',

    md: 'px-4 py-2.5 text-sm',

    lg: 'px-5 py-3 text-base',

}

/*
|--------------------------------------------------------------------------
| Label
|--------------------------------------------------------------------------
*/

export const LabelClass =

`
mb-2
block
text-sm
font-medium
text-gray-700
`

/*
|--------------------------------------------------------------------------
| Hint
|--------------------------------------------------------------------------
*/

export const HintClass =

`
mt-2
text-xs
text-gray-500
`

/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

export const ErrorClass =

`
border-red-400
focus:border-red-500
focus:ring-red-100
`

export const SuccessClass =

`
border-green-400
focus:border-green-500
focus:ring-green-100
`

export const NormalClass =

`
border-gray-300
focus:border-indigo-500
focus:ring-indigo-100
`

/*
|--------------------------------------------------------------------------
| Input
|--------------------------------------------------------------------------
*/

export const InputBase =

`
w-full
border
bg-white
transition
focus:outline-none
focus:ring-2
disabled:bg-gray-100
disabled:text-gray-500
readonly:bg-gray-50
`

/*
|--------------------------------------------------------------------------
| Card
|--------------------------------------------------------------------------
*/

export const Card =

`
rounded-2xl
border
border-gray-200
bg-white
shadow-sm
`

export const CardHeader =

`
border-b
border-gray-200
bg-gray-50
px-6
py-4
`

export const CardBody =

`
p-6
`

/*
|--------------------------------------------------------------------------
| Button
|--------------------------------------------------------------------------
*/

export const ButtonPrimary =

`
rounded-xl
bg-indigo-600
px-4
py-2.5
text-white
hover:bg-indigo-700
transition
`

export const ButtonSecondary =

`
rounded-xl
border
border-gray-300
bg-white
px-4
py-2.5
hover:bg-gray-50
transition
`

/**
|--------------------------------------------------------------------------
| Ready To Use Token
|--------------------------------------------------------------------------
*/

export const inputClass =

`${

    InputBase

} ${

    Radius.lg

} ${

    InputSize.md

} ${

    NormalClass

}`

export const labelClass =

LabelClass

export const hintClass =

HintClass

export const errorClass =

ErrorClass

export const successClass =

SuccessClass

export const cardClass =

Card

export const cardHeaderClass =

CardHeader

export const cardBodyClass =

CardBody

export const buttonPrimaryClass =

ButtonPrimary

export const buttonSecondaryClass =

ButtonSecondary