/**
 * Focus first element.
 *
 * @param {string} selector
 */
export function focusFirst(selector = 'input, textarea, select')
{
    requestAnimationFrame(() => {

        const element = document.querySelector(selector)

        if (element) {
            element.focus()
        }

    })
}

/**
 * Focus element by id.
 *
 * @param {string} id
 */
export function focusById(id)
{
    requestAnimationFrame(() => {

        const element = document.getElementById(id)

        if (element) {
            element.focus()
        }

    })
}

/**
 * Scroll to first validation error.
 */
export function scrollToFirstError()
{
    requestAnimationFrame(() => {

        const element = document.querySelector('.is-invalid')

        if (!element) {
            return
        }

        element.scrollIntoView({

            behavior: 'smooth',

            block: 'center',

        })

        element.focus()

    })
}