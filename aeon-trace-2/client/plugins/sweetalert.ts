import Swal from 'sweetalert2'

export default defineNuxtPlugin((nuxtApp: any) => {
    const fireAlert = async (title: string, text: string, icon: 'error' | 'success' | 'warning' | 'info' | 'question') => {
        const t = await nuxtApp.$i18n.t

        Swal.fire({
            toast: true,
            timer: 3000,
            position: 'top-end',
            showConfirmButton: false,
            animation: false,
            icon,
            title: t(title),
            text: t(text),
        })
    }

    return {
        provide: {
            swal: Swal,
            alert: fireAlert,
        },
    }
})
