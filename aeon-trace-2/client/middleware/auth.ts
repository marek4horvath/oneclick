import type { LoginResponse } from "~/types/api/auth"

export default defineNuxtRouteMiddleware(async () => {
    const { $axios, $alert, $event } = useNuxtApp()
    const authStore = useAuthStore()

    if (!authStore.isLoggedIn) {
        $alert('login.not_logged_in.title', 'login.not_logged_in.message', 'info')

        return navigateTo('/login')
    }

    $axios.post('/auth/refresh-token', {
        refresh_token: authStore.getRefreshToken,
    }).then(async (response: { data: LoginResponse }) => {
        if (!response.data.token || !response.data.refresh_token) {
            $alert('login.session_expired.title', 'login.session_expired.message', 'info')
            authStore.logout()
            navigateTo('/login')
        }

        authStore.setRefreshToken(response.data.refresh_token)
        authStore.setAccessToken(response.data.token)

        await authStore.refreshRoles()

        return response
    }).catch(error => {
        authStore.logout()

        // Send error to global event bus for handling
        $event('axiosError', error)
        navigateTo('/login')
    })
})
