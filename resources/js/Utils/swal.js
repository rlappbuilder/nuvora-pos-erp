
import Swal from 'sweetalert2'

export function success(message)
{
    Swal.fire({

        icon: 'success',

        title: 'Success',

        text: message,

        confirmButtonText: 'OK',

        confirmButtonColor: '#2563eb',

    })
}

export function error(message)
{
    Swal.fire({

        icon: 'error',

        title: 'Error',

        text: message,

        confirmButtonText: 'OK',

        confirmButtonColor: '#dc2626',

    })
}

export function warning(message)
{
    Swal.fire({

        icon: 'warning',

        title: 'Warning',

        text: message,

        confirmButtonText: 'OK',

        confirmButtonColor: '#d97706',

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