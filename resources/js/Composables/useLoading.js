import {

    ref,

    onMounted,

    onUnmounted,

} from 'vue'

import {

    router,

} from '@inertiajs/vue3'

export function useLoading()
{

    const loading = ref(false)

    let removeStart

    let removeFinish

    onMounted(() => {

        removeStart = router.on(

            'start',

            () => {

                loading.value = true

            }

        )

        removeFinish = router.on(

            'finish',

            () => {

                loading.value = false

            }

        )

    })

    onUnmounted(() => {

        removeStart?.()

        removeFinish?.()

    })

    return {

        loading,

    }

}