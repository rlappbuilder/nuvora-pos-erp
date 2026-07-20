import { router } from '@inertiajs/vue3'

import {

    success,

    error,

    resetExcept,

    focusFirst,

} from '@/Utils'

export function useCrudForm(form)
{

    const save = (url, options = {}) => {

        form.post(url, {

            preserveScroll: true,

            ...options,

        })

    }

    const update = (url, options = {}) => {

        form.put(url, {

            preserveScroll: true,

            ...options,

        })

    }

    const destroy = (url, options = {}) => {

        router.delete(url, {

            preserveScroll: true,

            ...options,

        })

    }

    return {

        save,

        update,

        destroy,

    }

}