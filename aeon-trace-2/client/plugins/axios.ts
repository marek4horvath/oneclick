import type { AxiosError, AxiosInstance, AxiosResponse } from 'axios'
import axios from 'axios'
import { useAuthStore } from '../stores/auth'

export default defineNuxtPlugin(() => {
    const authStore = useAuthStore()
    const accessToken = authStore.accessToken

    const backendUrl = ref<string | undefined>(import.meta.env.VITE_APP_API_URL)

    const axiosInstance = axios.create({
        baseURL: `${backendUrl.value}/api/`,
        headers: {
            'Content-Type': 'application/ld+json',
            "Accept": 'application/ld+json',
            "Authorization": `Bearer ${accessToken}`,
        },
    })

    interface AxiosInstanceExtended extends AxiosInstance {
        deleteRequest(url: string): Promise<AxiosResponse>
    }

    (axiosInstance as AxiosInstanceExtended).deleteRequest = function (url: string): Promise<AxiosResponse> {
        return new Promise((resolve, reject) => {
            useNuxtApp().$swal.fire({
                title: 'Confirm delete',
                text: 'Do you wish to delete this record?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#65c09e',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, cancel!',
            }).then(result => {
                if (result.isConfirmed) {
                    return this.delete(url)
                }

                return null
            }).then((response: any) => {
                resolve(response)
            }).catch((error: AxiosError) => {
                reject(error)
            })
        })
    }

    axiosInstance.interceptors.request.use(config => {
        if (config.headers.Authorization === 'Bearer null') {
            config.headers.Authorization = `Bearer ${authStore.accessToken}`
        }

        return config
    }, (error: AxiosError) => {
        return Promise.reject(error)
    })

    axiosInstance.interceptors.response.use(response => {
        return response
    }, (error: AxiosError) => {
        if (error?.response?.status === 401) {
            const refreshToken = authStore.refreshToken

            if (refreshToken) {
                authStore.refreshToken(refreshToken)

                return axiosInstance(error.config)
            }

            if (error.response.data.message === 'Expired JWT Token') {
                authStore.logout()
                navigateTo('/login')
            }

            if (error.response.data.message === 'Invalid credentials.') {
                authStore.logout()
                navigateTo('/login')
            }
        }

        // Send error to global event bus for handling
        useNuxtApp().$event('axiosError', error)
        console.error(error)

        return null
    })

    return {
        provide: {
            axios: axiosInstance,
        },
    }
})
