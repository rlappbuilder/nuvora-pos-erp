
import Swal from 'sweetalert2'
const Toast = Swal.mixin({

    toast: true,

    position: 'top-end',

    showConfirmButton: false,

    timer: 3000,

    timerProgressBar: true,

    didOpen: (toast) => {

        toast.onmouseenter = Swal.stopTimer

        toast.onmouseleave = Swal.resumeTimer

    },

})
export function success(message)
{
    Toast.fire({

        icon: 'success',

        title: message,

    })
}
export function loading(
    title = 'Loading...',
    text = ''
)
{
    Swal.fire({

        title,

        text,

        allowOutsideClick: false,

        allowEscapeKey: false,

        showConfirmButton: false,

        didOpen: () => {

            Swal.showLoading()

        },

    })
}
export function closeLoading()
{
    Swal.close()
}
export function error(message)
{
    Toast.fire({

        icon: 'error',

        title: message,

    })
}

export function warning(message)
{
    Toast.fire({

        icon: 'warning',

        title: message,

    })
}
export async function confirmRegenerate(
    variantsCount
)
{
    return Swal.fire({

        icon: 'warning',

        title: 'Regenerate Variants?',

        html: `
            This product already has
            <b>${variantsCount}</b> variant(s).<br><br>

            Regenerating will replace all existing variants.
        `,

        showCancelButton: true,

        confirmButtonText: 'Regenerate',

        cancelButtonText: 'Cancel',

        confirmButtonColor: '#2563eb',

        cancelButtonColor: '#6b7280',

    })
}
export function handleFlash(page)
{
    const flash = page.props.flash

    if (flash?.success) {
        success(flash.success)
    }

    if (flash?.error) {
        error(flash.error)
    }

    if (flash?.warning) {
        warning(flash.warning)
    }
}