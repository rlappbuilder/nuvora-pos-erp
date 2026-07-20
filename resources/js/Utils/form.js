/**
 * Reset form except specific fields.
 *
 * @param {Object} form
 * @param {Array} except
 */
export function resetExcept(form, except = [])
{
    const values = { ...form.data() }

    form.reset()

    Object.keys(values).forEach((key) => {

        if (except.includes(key)) {
            form[key] = values[key]
        }

    })

    form.clearErrors()
}

/**
 * Focus first input.
 *
 * @param {String} selector
 */
