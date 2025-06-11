import { useAuthStore } from '@/stores/auth'

export function useAuthSync() {
    const authStore = useAuthStore()
    let intervalId: ReturnType<typeof setInterval> | null = null

    const startRoleSync = () => {
        intervalId = setInterval(() => {
            if (authStore.isLoggedIn) {
                authStore.refreshRoles().catch(err => {
                    console.error('[Auth Sync] Failed to refresh roles:', err)
                })
            }
        }, 10 * 60 * 1000) // 10 minutes
    }

    const stopRoleSync = () => {
        if (intervalId) {
            clearInterval(intervalId)
        }
    }

    onMounted(() => {
        if (authStore.isLoggedIn) {
            startRoleSync()
        }
    })

    onUnmounted(() => {
        stopRoleSync()
    })
}
