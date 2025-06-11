<script setup lang="ts">
import { getPageHeader } from '@/utils/meta'

import type { MessageBag } from '@/types/index'

const { t } = useI18n()
const { $listen, $alert } = useNuxtApp()
const authStore = useAuthStore()
const route = useRoute()
const googleAiKey = ref<string | undefined>(import.meta.env.VITE_APP_GOOGLE_API_KEY)

useHead({
    titleTemplate: getPageHeader(),
    script: [
        {
            src: `https://maps.googleapis.com/maps/api/js?key=${googleAiKey.value}&libraries=places&loading=async`,
            async: true,
            defer: true,
            loading: 'async',
        },
    ],
})

function isMessageBag(obj: any): obj is MessageBag {
    return 'title' in obj && 'message' in obj && 'type' in obj
}

onMounted(() => {
    $listen('axiosError', (error: any) => {
        let text = t('axios.error.unknownError')
        let title = t('axios.error.title.base')

        if (error.code === 'ERR_BAD_RESPONSE') {
            text = t('axios.error.badResponse')
        }

        // Cors for images
        if (error.code === 'ERR_NETWORK') {
            return
        }

        if (error.response) {
            if (error.response.data['hydra:description']?.includes('The product template cannot be deleted because it contains steps.')) {
                text = t('axios.error.deleteProduct')
            } else if (error.response.data['hydra:description'] === 'Expired password reset token') {
                text = t('axios.error.expiredToken')
            } else if (error.response.data['hydra:description']?.includes('email: The email')) {
                text = t('axios.error.usedEmail')
            } else if (error.response.data['hydra:description']?.includes('Company with given name already exists')) {
                text = t('axios.error.companyName')
            } else if (error.response.data['hydra:description']?.includes('foreign key constraint fails')) {
                text = t('axios.error.foreignKeyConstraintFails')
            } else {
                text = t(`axios.error.${error?.response?.data?.message}`)
            }
        }

        if (error?.config?.method) {
            title = t(`axios.error.title.${error.config.method}`)
        }

        $alert(title, text, 'error')
    })

    $listen('message', (messageBag: MessageBag) => {
        if (isMessageBag(messageBag)) {
            $alert(messageBag.title || 'Info', messageBag.message, messageBag.type)
        }
    })

    if (authStore.isLoggedIn) {
        if (route.name === 'login' || route.path === '/') {
            navigateTo('/dashboard')
        }

        const processStore = useProcessStore()

        // Fetch processes
        processStore.fetchProcesses()
    } else {
        // Allowing access to the DPP detail even for unregistered users
        if (route.path.startsWith('/detail-of-dpp/')) {
            return
        }

        if (route.name === 'password-reset') {
            return
        }

        if (route.name === 'first-login') {
            return
        }

        if (route.name === 'registration-company') {
            return
        }

        navigateTo('/login')
    }

    authStore.initAuth()
    useAuthSync()
})
</script>

<template>
    <NuxtPage />
</template>
